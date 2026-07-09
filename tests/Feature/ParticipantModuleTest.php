<?php

use App\Actions\Fortify\CreateNewUser;
use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\EvaluationForm;
use App\Models\EvaluationQuestion;
use App\Models\EvaluationResponse;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['super_admin', 'admin', 'event_organizer', 'participant'] as $role) {
        Role::findOrCreate($role);
    }
});

function participantUser(): User
{
    return User::factory()->create()->assignRole('participant');
}

test('self-registration assigns the participant role', function () {
    $user = app(CreateNewUser::class)->create([
        'name' => 'New Attendee',
        'email' => 'attendee@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect($user->hasRole('participant'))->toBeTrue();
});

test('the browse page lists open, upcoming events', function () {
    $user = participantUser();

    $open = Event::factory()->create(['registration_type' => 'open', 'end_time' => now()->addWeek()]);
    Event::factory()->create(['registration_type' => 'closed', 'end_time' => now()->addWeek()]);

    $this->actingAs($user)
        ->get(route('portal.events'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Portal/Events')
            ->has('events.data', 1)
            ->where('events.data.0.id', $open->id)
        );
});

test('the event details page renders for a participant', function () {
    $user = participantUser();
    $event = Event::factory()->create(['registration_type' => 'open']);

    $this->actingAs($user)
        ->get(route('portal.events.show', $event))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Portal/EventShow')
            ->where('event.id', $event->id)
            ->where('event.is_registered', false)
        );
});

test('a participant can register for an open event and gets a QR code', function () {
    Storage::fake('public');

    $user = participantUser();
    $event = Event::factory()->create([
        'registration_type' => 'open',
        'end_time' => now()->addWeek(),
    ]);

    $this->actingAs($user)
        ->post(route('portal.register', $event))
        ->assertRedirect(route('portal.qr', $event));

    $participant = EventParticipant::query()
        ->where('event_id', $event->id)
        ->where('user_id', $user->id)
        ->first();

    expect($participant)->not->toBeNull()
        ->and($participant->status)->toBe('registered')
        ->and($participant->qr_code_path)->not->toBeNull();

    Storage::disk('public')->assertExists(
        str_replace('public/', '', $participant->qr_code_path)
    );
});

test('a participant cannot register twice for the same event', function () {
    Storage::fake('public');

    $user = participantUser();
    $event = Event::factory()->create(['registration_type' => 'open', 'end_time' => now()->addWeek()]);

    EventParticipant::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->post(route('portal.register', $event))
        ->assertSessionHas('error');

    expect(EventParticipant::where('event_id', $event->id)->where('user_id', $user->id)->count())->toBe(1);
});

test('a participant cannot register for a closed event', function () {
    $user = participantUser();
    $event = Event::factory()->create(['registration_type' => 'closed', 'end_time' => now()->addWeek()]);

    $this->actingAs($user)
        ->post(route('portal.register', $event))
        ->assertSessionHas('error');

    expect(EventParticipant::where('event_id', $event->id)->count())->toBe(0);
});

test('submitting an evaluation stores responses and completes an attended participant', function () {
    $user = participantUser();
    $event = Event::factory()->create();

    $participant = EventParticipant::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => 'attended',
    ]);

    $form = EvaluationForm::create(['event_id' => $event->id, 'title' => 'Feedback']);
    $question = EvaluationQuestion::create([
        'evaluation_form_id' => $form->id,
        'question_text' => 'How was it?',
        'question_type' => 'rating',
    ]);

    $this->actingAs($user)
        ->post(route('portal.evaluation.submit', $event), [
            'responses' => [
                ['question_id' => $question->id, 'response_rating' => 5],
            ],
        ])
        ->assertRedirect(route('portal.events'));

    expect(EvaluationResponse::where('evaluation_question_id', $question->id)->where('user_id', $user->id)->exists())->toBeTrue()
        ->and($participant->fresh()->status)->toBe('completed');
});

test('certificate download is blocked without attendance', function () {
    $user = participantUser();
    $event = Event::factory()->create(['certificate_enabled' => true]);

    EventParticipant::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => 'registered',
    ]);

    $this->actingAs($user)
        ->get(route('portal.certificate.download', $event))
        ->assertRedirect(route('portal.events'));

    expect(Certificate::count())->toBe(0);
});

test('certificate download is blocked when a required evaluation is unsubmitted', function () {
    $user = participantUser();
    $event = Event::factory()->create([
        'certificate_enabled' => true,
        'evaluation_required' => true,
    ]);

    EventParticipant::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => 'attended',
    ]);
    Attendance::create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'scan_type' => 'one-time',
        'scanned_at' => now(),
    ]);

    $form = EvaluationForm::create(['event_id' => $event->id, 'title' => 'Feedback']);
    EvaluationQuestion::create([
        'evaluation_form_id' => $form->id,
        'question_text' => 'Rate the event',
        'question_type' => 'rating',
    ]);

    $this->actingAs($user)
        ->get(route('portal.certificate.download', $event))
        ->assertRedirect(route('portal.evaluation.show', $event));

    expect(Certificate::count())->toBe(0);
});

test('an eligible participant downloads and is issued a persisted certificate', function () {
    $user = participantUser();
    $event = Event::factory()->create([
        'certificate_enabled' => true,
        'evaluation_required' => false,
    ]);

    EventParticipant::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => 'attended',
    ]);
    Attendance::create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'scan_type' => 'one-time',
        'scanned_at' => now(),
    ]);

    // A real background image is required because the controller reads it from disk.
    $backgroundDir = storage_path('app/public/certificate_backgrounds');
    if (! is_dir($backgroundDir)) {
        mkdir($backgroundDir, 0777, true);
    }
    $backgroundFile = $backgroundDir.'/test_bg.png';
    $image = imagecreatetruecolor(600, 400);
    imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
    imagepng($image, $backgroundFile);
    imagedestroy($image);

    CertificateTemplate::create([
        'event_id' => $event->id,
        'name' => 'Default',
        'background_path' => 'public/certificate_backgrounds/test_bg.png',
        'dynamic_fields_mapping' => [
            'participant_name' => ['x' => 100, 'y' => 200, 'size' => 24, 'color' => '#000000'],
        ],
    ]);

    $response = $this->actingAs($user)->get(route('portal.certificate.download', $event));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/png');

    expect(Certificate::where('event_id', $event->id)->where('user_id', $user->id)->exists())->toBeTrue();

    // Clean up generated artifacts.
    @unlink($backgroundFile);
    @unlink(storage_path('app/public/certificates/event_'.$event->id.'_user_'.$user->id.'.png'));
});

test('the my-events page renders for a participant', function () {
    $user = participantUser();
    $event = Event::factory()->create();
    EventParticipant::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('portal.my-events'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Portal/MyEvents')
            ->has('events', 1)
        );
});

test('the certificates page renders for a participant', function () {
    $user = participantUser();

    $this->actingAs($user)
        ->get(route('portal.certificates'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Portal/Certificates')
            ->has('certificates', 0)
        );
});
