<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEvaluationFormRequest;
use App\Http\Requests\SubmitEvaluationRequest;
use App\Models\EvaluationForm;
use App\Models\EvaluationQuestion;
use App\Models\EvaluationResponse;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class EvaluationFormController extends Controller
{
    /**
     * Create an evaluation form with questions for the specified event.
     */
    public function store(StoreEvaluationFormRequest $request, Event $event): RedirectResponse
    {
        DB::transaction(function () use ($request, $event): void {
            /** @var EvaluationForm $form */
            $form = $event->evaluationForm()->updateOrCreate(
                ['event_id' => $event->id],
                [
                    'title' => $request->string('title')->toString(),
                    'description' => $request->string('description')->toString(),
                ]
            );

            // Remove old questions if updating
            $form->questions()->delete();

            /** @var array<int, array{question_text: string, question_type: string, options: array<int, string>|null}> $questions */
            $questions = $request->array('questions');

            foreach ($questions as $questionData) {
                $form->questions()->create([
                    'question_text' => $questionData['question_text'],
                    'question_type' => $questionData['question_type'],
                    'options' => $questionData['options'] ?? null,
                ]);
            }
        });

        return redirect()->route('events.show', $event)
            ->with('success', 'Evaluation form saved successfully.');
    }

    /**
     * Show the evaluation form for a participant to fill in.
     */
    public function showForParticipant(Event $event): Response|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $form = $event->evaluationForm()->with('questions')->first();

        if ($form === null) {
            return redirect()->route('portal.events')
                ->with('error', 'No evaluation form found for this event.');
        }

        $alreadySubmitted = EvaluationResponse::query()
            ->whereIn(
                'evaluation_question_id',
                $form->questions->pluck('id')
            )
            ->where('user_id', $user->id)
            ->exists();

        return Inertia::render('Portal/Evaluation', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
            ],
            'form' => [
                'id' => $form->id,
                'title' => $form->title,
                'description' => $form->description,
                'questions' => $form->questions->map(fn (EvaluationQuestion $q) => [
                    'id' => $q->id,
                    'question_text' => $q->question_text,
                    'question_type' => $q->question_type,
                    'options' => $q->options,
                ]),
            ],
            'alreadySubmitted' => $alreadySubmitted,
        ]);
    }

    /**
     * Save evaluation responses for the authenticated participant.
     */
    public function submit(SubmitEvaluationRequest $request, Event $event): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var array<int, array{question_id: int, response_text: string|null, response_rating: int|null}> $responses */
        $responses = $request->array('responses');

        DB::transaction(function () use ($responses, $user): void {
            foreach ($responses as $responseData) {
                EvaluationResponse::updateOrCreate(
                    [
                        'evaluation_question_id' => $responseData['question_id'],
                        'user_id' => $user->id,
                    ],
                    [
                        'response_text' => $responseData['response_text'] ?? null,
                        'response_rating' => $responseData['response_rating'] ?? null,
                    ]
                );
            }
        });

        // Update participant status to completed if they've also attended
        $participant = EventParticipant::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($participant !== null && $participant->status === 'attended') {
            $participant->update(['status' => 'completed']);
        }

        return back()->with('success', 'Evaluation submitted successfully.');
    }
}
