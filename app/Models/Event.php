<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property Carbon $start_time
 * @property Carbon $end_time
 * @property string $registration_type
 * @property string $attendance_type
 * @property bool $evaluation_required
 * @property bool $certificate_enabled
 * @property int|null $max_participants
 * @property int|null $organizer_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $organizer
 * @property-read Collection<int, User> $participants
 * @property-read Collection<int, Attendance> $attendances
 * @property-read EvaluationForm|null $evaluationForm
 * @property-read CertificateTemplate|null $certificateTemplate
 * @property-read Collection<int, EventParticipant> $eventParticipants
 */
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'start_time',
        'end_time',
        'registration_start_date',
        'registration_end_date',
        'registration_type',
        'attendance_type',
        'evaluation_required',
        'certificate_enabled',
        'max_participants',
        'organizer_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'registration_start_date' => 'datetime',
            'registration_end_date' => 'datetime',
            'evaluation_required' => 'boolean',
            'certificate_enabled' => 'boolean',
            'max_participants' => 'integer',
        ];
    }

    /**
     * Determine if the event has reached its maximum participant capacity.
     */
    public function hasReachedCapacity(): bool
    {
        if (is_null($this->max_participants)) {
            return false;
        }

        return $this->eventParticipants()->where('status', 'confirmed')->count() >= $this->max_participants;
    }

    /**
     * The user who organizes this event.
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * The participants (users) registered for this event via pivot.
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_participants')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Attendance records for this event.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * The evaluation form associated with this event.
     */
    public function evaluationForm(): HasOne
    {
        return $this->hasOne(EvaluationForm::class);
    }

    /**
     * The certificate template for this event.
     */
    public function certificateTemplate(): HasOne
    {
        return $this->hasOne(CertificateTemplate::class);
    }

    /**
     * The EventParticipant pivot records for this event.
     */
    public function eventParticipants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    /**
     * The sessions associated with this event.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(EventSession::class);
    }
}
