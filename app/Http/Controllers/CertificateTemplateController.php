<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\EvaluationResponse;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf as DomPDF;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class CertificateTemplateController extends Controller
{
    /**
     * Create or update the certificate template for the specified event.
     */
    public function store(Request $request, Event $event): RedirectResponse
    {
        \Illuminate\Support\Facades\Log::info('Store Certificate Request:', $request->all());

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'background_image' => ['nullable', 'image', 'max:5120'],
            'dynamic_fields_mapping' => ['nullable', 'array'],
        ]);

        $backgroundPath = null;

        if ($request->hasFile('background_image')) {
            $file = $request->file('background_image');

            if ($file !== null) {
                // Remove old background if it exists
                $existingTemplate = $event->certificateTemplate;
                if ($existingTemplate?->background_path) {
                    Storage::delete($existingTemplate->background_path);
                }

                $backgroundPath = $file->store('certificate_backgrounds', 'public');

                if ($backgroundPath !== false) {
                    $backgroundPath = 'public/' . $backgroundPath;
                } else {
                    $backgroundPath = null;
                }
            }
        }

        $data = [
            'event_id' => $event->id,
            'name' => $request->string('name')->toString(),
            'dynamic_fields_mapping' => $request->array('dynamic_fields_mapping'),
        ];

        if ($backgroundPath !== null) {
            $data['background_path'] = $backgroundPath;
        }

        CertificateTemplate::updateOrCreate(
            ['event_id' => $event->id],
            $data
        );

        return redirect()->route('events.show', $event)
            ->with('success', 'Certificate template saved successfully.');
    }

    /**
     * Delete the certificate template for the specified event.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $template = $event->certificateTemplate;

        if ($template !== null) {
            if ($template->background_path) {
                Storage::delete($template->background_path);
            }
            $template->delete();
        }

        return redirect()->route('events.show', $event)
            ->with('success', 'Certificate template deleted successfully.');
    }

    /**
     * Generate the base certificate image for the authenticated participant.
     */
    private function generateCertificateImage(Event $event, User $user)
    {
        $participant = EventParticipant::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($participant === null) {
            return redirect()->route('portal.events')
                ->with('error', 'You are not registered for this event.');
        }

        $hasAttended = Attendance::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->exists();

        if (! $hasAttended) {
            return redirect()->route('portal.events')
                ->with('error', 'You must attend the event to download a certificate.');
        }

        if ($event->evaluation_required) {
            $form = $event->evaluationForm()->with('questions')->first();

            $hasSubmittedEvaluation = false;

            if ($form !== null && $form->questions->isNotEmpty()) {
                $hasSubmittedEvaluation = EvaluationResponse::query()
                    ->whereIn('evaluation_question_id', $form->questions->pluck('id'))
                    ->where('user_id', $user->id)
                    ->exists();
            }

            if (! $hasSubmittedEvaluation) {
                return redirect()->route('portal.evaluation.show', $event)
                    ->with('error', 'You must complete the evaluation to get your certificate.');
            }
        }

        $template = $event->certificateTemplate;

        if (! $event->certificate_enabled || $template === null || ! $template->background_path) {
            return redirect()->route('portal.events')
                ->with('error', 'No certificate template is configured for this event.');
        }

        $backgroundPath = storage_path('app/' . $template->background_path);
        if (! file_exists($backgroundPath)) {
            return redirect()->route('portal.events')
                ->with('error', 'Certificate background image not found.');
        }

        $manager = new ImageManager(new Driver);
        $image = $manager->decodePath($backgroundPath);

        $mapping = $template->dynamic_fields_mapping ?? [];
        $fontPath = 'C:\\Windows\\Fonts\\arial.ttf';

        $imgWidth = $image->width();
        $imgHeight = $image->height();

        foreach ($mapping as $key => $config) {
            $text = '';
            if ($key === 'participant_name') {
                $text = $user->name;
            }
            if ($key === 'event_title') {
                $text = $event->title;
            }
            if ($key === 'date') {
                $text = $event->start_time ? $event->start_time->format('F j, Y') : '';
            }

            if (! $text) {
                continue;
            }

            // Coordinates are saved as percentages from the frontend builder
            $xPercent = floatval($config['x'] ?? 0);
            $yPercent = floatval($config['y'] ?? 0);

            $x = intval(($xPercent / 100) * $imgWidth);
            $y = intval(($yPercent / 100) * $imgHeight);

            $size = intval($config['size'] ?? 24);
            $color = $config['color'] ?? '#000000';

            $image->text($text, $x, $y, function ($font) use ($fontPath, $size, $color) {
                if (file_exists($fontPath)) {
                    $font->filename($fontPath);
                }
                $font->size($size);
                $font->color($color);
                $font->align('center');
                $font->valign('middle');
            });
        }

        return $image;
    }

    /**
     * View the certificate directly in the browser.
     */
    public function view(Event $event): HttpResponse|RedirectResponse
    {
        $result = $this->generateCertificateImage($event, Auth::user());
        if ($result instanceof RedirectResponse) {
            return $result;
        }

        $encoded = $result->toPng();

        return response($encoded->toString())
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'inline; filename="certificate.png"');
    }

    /**
     * Download the certificate as a PNG image.
     */
    public function download(Event $event): HttpResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->generateCertificateImage($event, $user);

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        $filename = 'certificate_' . Str::slug($event->title) . '_' . Str::slug($user->name) . '.png';
        $encoded = $result->toPng();

        return response($encoded->toString())
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Download the certificate as a PDF document.
     */
    public function downloadPdf(Event $event): HttpResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->generateCertificateImage($event, $user);

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        // Convert the image to base64 to embed in the PDF
        $base64 = base64_encode($result->toPng()->toString());
        $imgSrc = 'data:image/png;base64,' . $base64;

        $html = '<style>
            @page { margin: 0px; size: landscape; }
            body { margin: 0px; padding: 0px; }
            img { width: 100%; height: 100%; object-fit: cover; }
        </style>';
        $html .= '<body><img src="' . $imgSrc . '" /></body>';

        $pdf = DomPDF::loadHTML($html)->setPaper('a4', 'landscape');
        $filename = 'certificate_' . Str::slug($event->title) . '_' . Str::slug($user->name) . '.pdf';

        return response($pdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
