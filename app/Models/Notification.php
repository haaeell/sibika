<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    public const TYPE_SCORE_EDIT_REQUESTED = 'score_edit_requested';
    public const TYPE_SCORE_EDIT_APPROVED = 'score_edit_approved';
    public const TYPE_SCORE_EDIT_REJECTED = 'score_edit_rejected';

    protected $fillable = ['user_id', 'type', 'title', 'message', 'url', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
