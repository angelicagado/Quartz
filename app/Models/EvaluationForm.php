<?php

namespace App\Models;

use Database\Factories\EvaluationFormFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $event_id
 * @property string $title
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Event $event
 * @property-read Collection<int, EvaluationQuestion> $questions
 */
class EvaluationForm extends Model
{
    /** @use HasFactory<EvaluationFormFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'title',
        'description',
    ];

    /**
     * The event this evaluation form belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * The questions in this evaluation form.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(EvaluationQuestion::class);
    }
}
