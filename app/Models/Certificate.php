<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'certificate_number',
        'issue_date',
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'datetime',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
