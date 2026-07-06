<?php

namespace App\Models;

use Database\Factories\EvaluationResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $evaluation_question_id
 * @property int $user_id
 * @property string|null $response_text
 * @property int|null $response_rating
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read EvaluationQuestion $evaluationQuestion
 * @property-read User $user
 */
class EvaluationResponse extends Model
{
    /** @use HasFactory<EvaluationResponseFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'evaluation_question_id',
        'user_id',
        'response_text',
        'response_rating',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'response_rating' => 'integer',
        ];
    }

    /**
     * The question this response answers.
     */
    public function evaluationQuestion(): BelongsTo
    {
        return $this->belongsTo(EvaluationQuestion::class);
    }

    /**
     * The user who submitted this response.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
