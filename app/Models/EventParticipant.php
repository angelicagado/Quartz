<?php

namespace App\Models;

use Database\Factories\EventParticipantFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $event_id
 * @property int $user_id
 * @property string $status
 * @property string|null $qr_code_path
 * @property string|null $qr_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $qr_code_url
 * @property-read Event $event
 * @property-read User $user
 */
class EventParticipant extends Model
{
    /** @use HasFactory<EventParticipantFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'qr_code_path',
        'qr_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    /**
     * The event this participant belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * The user who is registered as a participant.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the public URL for the QR code.
     */
    protected function qrCodeUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->qr_code_path
                ? Storage::url(str_replace('public/', '', $this->qr_code_path))
                : null
        );
    }
}
