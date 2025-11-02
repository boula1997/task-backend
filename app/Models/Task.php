<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'assignee_id',
        'title',
        'description',
        'due_date',
        'priority',
        'is_completed',
    ];

    // Relationships
    public function creator() {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function assignee() {
        return $this->belongsTo(User::class, 'assignee_id');
    }
}
