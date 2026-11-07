<?php

namespace App\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounselingSession extends Model
{
    public static function historyFor(Student $student, array $filters): LengthAwarePaginator
    {
        return $student->counselingSessions()->with(['creator', 'editor'])
            ->select('counseling_sessions.*')
            ->selectSub(function ($query) {
                $query->from('counseling_sessions as earlier')->selectRaw('COUNT(*)')
                    ->whereColumn('earlier.student_id', 'counseling_sessions.student_id')
                    ->where(function ($query) {
                        $query->whereColumn('earlier.held_at', '<', 'counseling_sessions.held_at')
                            ->orWhere(function ($query) {
                                $query->whereColumn('earlier.held_at', 'counseling_sessions.held_at')
                                    ->whereColumn('earlier.id', '<=', 'counseling_sessions.id');
                            });
                    });
            }, 'meeting_number')
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('held_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('held_at', '<=', $date))
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where(function ($query) use ($term) {
                $query->where('topic', 'like', '%'.$term.'%')
                    ->orWhere('summary', 'like', '%'.$term.'%')
                    ->orWhere('outcome', 'like', '%'.$term.'%')
                    ->orWhere('follow_up', 'like', '%'.$term.'%');
            }))
            ->orderByDesc('held_at')->orderByDesc('id')->paginate(15)->withQueryString();
    }

    protected $fillable = [
        'student_id', 'created_by', 'updated_by', 'held_at', 'type', 'topic',
        'summary', 'outcome', 'follow_up', 'due_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return ['held_at' => 'datetime', 'due_at' => 'date', 'completed_at' => 'datetime'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
