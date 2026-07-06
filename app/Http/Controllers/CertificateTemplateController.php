<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
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

        if (! $event->certificate_enabled || $template === null) {
            return redirect()->route('portal.events')
                ->with('error', 'No certificate template is configured for this event.');
        }

        $backgroundUrl = $template->background_path
            ? Storage::url(str_replace('public/', '', $template->background_path))
            : null;

        $html = $this->buildCertificateHtml(
            participantName: $user->name,
            eventTitle: $event->title,
            eventDate: $event->start_time->format('F j, Y'),
            backgroundUrl: $backgroundUrl,
            templateName: $template->name,
        );

        /** @var PDF $pdf */
        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($html);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'certificate_'.Str::slug($event->title).'_'.Str::slug($user->name).'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Build the HTML for the certificate PDF.
     */
    private function buildCertificateHtml(
        string $participantName,
        string $eventTitle,
        string $eventDate,
        ?string $backgroundUrl,
        string $templateName,
    ): string {
        $backgroundStyle = $backgroundUrl
            ? "background-image: url('{$backgroundUrl}'); background-size: cover; background-position: center;"
            : 'background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);';

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    font-family: 'Georgia', serif;
                }
                .certificate {
                    width: 297mm;
                    height: 210mm;
                    {$backgroundStyle}
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    text-align: center;
                    color: #fff;
                    position: relative;
                }
                .overlay {
                    position: absolute;
                    top: 0; left: 0; right: 0; bottom: 0;
                    background: rgba(0,0,0,0.35);
                }
                .content {
                    position: relative;
                    z-index: 1;
                    padding: 40px;
                }
                .border-outer {
                    border: 4px solid rgba(255,215,0,0.8);
                    padding: 30px 50px;
                }
                .border-inner {
                    border: 1px solid rgba(255,215,0,0.5);
                    padding: 20px 40px;
                }
                h1 {
                    font-size: 14pt;
                    letter-spacing: 8px;
                    text-transform: uppercase;
                    margin: 0 0 10px 0;
                    color: #ffd700;
                }
                .subtitle {
                    font-size: 10pt;
                    letter-spacing: 4px;
                    margin: 0 0 20px 0;
                }
                .presented-to {
                    font-size: 9pt;
                    letter-spacing: 2px;
                    margin: 10px 0 5px 0;
                    text-transform: uppercase;
                }
                .name {
                    font-size: 28pt;
                    font-weight: bold;
                    margin: 8px 0;
                    color: #ffd700;
                    font-style: italic;
                }
                .event-label {
                    font-size: 9pt;
                    letter-spacing: 2px;
                    margin: 15px 0 5px 0;
                    text-transform: uppercase;
                }
                .event-title {
                    font-size: 14pt;
                    font-weight: bold;
                    margin: 5px 0;
                }
                .date {
                    font-size: 10pt;
                    margin-top: 15px;
                    color: rgba(255,255,255,0.8);
                }
                .quartz {
                    margin-top: 20px;
                    font-size: 8pt;
                    letter-spacing: 4px;
                    text-transform: uppercase;
                    color: rgba(255,255,255,0.6);
                }
            </style>
        </head>
        <body>
            <div class="certificate">
                <div class="overlay"></div>
                <div class="content">
                    <div class="border-outer">
                        <div class="border-inner">
                            <h1>Certificate of Participation</h1>
                            <p class="subtitle">{$templateName}</p>
                            <p class="presented-to">This is to certify that</p>
                            <div class="name">{$participantName}</div>
                            <p class="event-label">has successfully participated in</p>
                            <div class="event-title">{$eventTitle}</div>
                            <p class="date">held on {$eventDate}</p>
                            <p class="quartz">QUARTZ Event Management System</p>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
}
