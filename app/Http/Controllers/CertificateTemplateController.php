<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\EvaluationResponse;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\User;
use Barryvdh\DomPDF\PDF;
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
                    $backgroundPath = 'public/'.$backgroundPath;
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
     * Generate and download a certificate PDF for the authenticated participant.
     *
     * Eligibility checks:
     * - Participant must be registered for this event
     * - Participant must have attended (Attendance record exists)
     * - If event requires evaluation, participant must have submitted it
     */
    public function download(Event $event): HttpResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

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
                    ->with('error', 'You must complete the evaluation to download your certificate.');
            }
        }

        $template = $event->certificateTemplate;

        if (! $event->certificate_enabled || $template === null || ! $template->background_path) {
            return redirect()->route('portal.events')
                ->with('error', 'No certificate template is configured for this event.');
        }

        $backgroundPath = storage_path('app/'.$template->background_path);
        if (! file_exists($backgroundPath)) {
            return redirect()->route('portal.events')
                ->with('error', 'Certificate background image not found.');
        }

        $manager = new ImageManager(new Driver);
        $image = $manager->decodePath($backgroundPath);

        $mapping = $template->dynamic_fields_mapping ?? [];
        $fontPath = 'C:\\Windows\\Fonts\\arial.ttf';

        foreach ($mapping as $key => $config) {
            $text = '';
            if ($key === 'participant_name') {
                $text = $user->name;
            }
            if ($key === 'event_title') {
                $text = $event->title;
            }
            if ($key === 'date') {
                $text = $event->start_time->format('F j, Y');
            }

            if (! $text) {
                continue;
            }

            $x = intval($config['x'] ?? 0);
            $y = intval($config['y'] ?? 0);
            $size = intval($config['size'] ?? 24);
            $color = $config['color'] ?? '#000000';

            $image->text($text, $x, $y, function ($font) use ($fontPath, $size, $color) {
                if (file_exists($fontPath)) {
                    $font->filename($fontPath);
                }
                $font->size($size);
                $font->color($color);
                $font->align('left');
            });
        }

        $filename = 'certificate_'.Str::slug($event->title).'_'.Str::slug($user->name).'.png';
        $encoded = $image->encodeUsingFileExtension('png');

        $relativePath = 'certificates/event_'.$event->id.'_user_'.$user->id.'.png';
        Storage::disk('public')->put($relativePath, $encoded->toString());

        Certificate::firstOrCreate(
            [
                'event_id' => $event->id,
                'user_id' => $user->id,
            ],
            [
                'certificate_number' => 'CERT-'.$event->id.'-'.$user->id.'-'.Str::upper(Str::random(6)),
                'issue_date' => now(),
                'file_path' => 'public/'.$relativePath,
            ]
        );

        return response($encoded->toString())
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }
}
