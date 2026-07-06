<?php

namespace App\Models;

use Database\Factories\EvaluationQuestionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $evaluation_form_id
 * @property string $question_text
 * @property string $question_type
 * @property array<int, string>|null $options
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read EvaluationForm $evaluationForm
 * @property-read Collection<int, EvaluationResponse> $responses
 */
class EvaluationQuestion extends Model
{
    /** @use HasFactory<EvaluationQuestionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'evaluation_form_id',
        'question_text',
        'question_type',
        'options',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }

    /**
     * The evaluation form this question belongs to.
     */
    public function evaluationForm(): BelongsTo
    {
        return $this->belongsTo(EvaluationForm::class);
    }

    /**
     * The responses for this question.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(EvaluationResponse::class);
    }
}
