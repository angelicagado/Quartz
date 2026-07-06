<?php

namespace App\Models;

use Database\Factories\CertificateTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $event_id
 * @property string $name
 * @property string|null $background_path
 * @property array<string, mixed>|null $dynamic_fields_mapping
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Event|null $event
 */
class CertificateTemplate extends Model
{
    /** @use HasFactory<CertificateTemplateFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'name',
        'background_path',
        'dynamic_fields_mapping',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dynamic_fields_mapping' => 'array',
        ];
    }

    /**
     * The event this certificate template belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
