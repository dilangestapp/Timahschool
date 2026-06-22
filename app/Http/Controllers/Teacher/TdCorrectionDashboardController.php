<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TdAttempt;
use App\Models\TdSet;
use App\Models\TeacherAssignment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TdCorrectionDashboardController extends Controller
{
    public function index()
    {
        $assignments = $this->assignments();
        $tdSets = $this->tdSets($assignments);
        $attempts = $this->attempts($tdSets->pluck('id'));

        $rows = $tdSets->map(function ($td) use ($attempts) {
            $tdAttempts = $attempts->where('td_set_id', $td->id);
            $submitted = $tdAttempts->whereIn('status', [TdAttempt::STATUS_SUBMITTED, TdAttempt::STATUS_COMPLETED, TdAttempt::STATUS_CORRECTED, TdAttempt::STATUS_GRADED]);
            $corrected = $tdAttempts->whereIn('status', [TdAttempt::STATUS_CORRECTED, TdAttempt::STATUS_GRADED]);
            $late = $tdAttempts->whereIn('status', [TdAttempt::STATUS_EXPIRED, TdAttempt::STATUS_MISSED]);

            return [
                'td' => $td,
                'submitted' => $submitted->count(),
                'corrected' => $corrected->count(),
                'pending' => max(0, $submitted->count() - $corrected->count()),
                'late' => $late->count(),
                'opened' => $tdAttempts->count(),
            ];
        })->sortByDesc('pending')->values();

        return view('teacher.td.corrections.index', [
            'rows' => $rows,
            'stats' => [
                'td_total' => $tdSets->count(),
                'submitted' => $rows->sum('submitted'),
                'corrected' => $rows->sum('corrected'),
                'pending' => $rows->sum('pending'),
                'late' => $rows->sum('late'),
            ],
        ]);
    }

    protected function assignments(): Collection
    {
        if (!Schema::hasTable('teacher_assignments')) {
            return collect();
        }

        return TeacherAssignment::query()
            ->where('teacher_id', auth()->id())
            ->where('is_active', true)
            ->get();
    }

    protected function tdSets(Collection $assignments): Collection
    {
        if ($assignments->isEmpty() || !Schema::hasTable('td_sets')) return collect();

        return TdSet::query()
            ->with(['schoolClass', 'subject'])
            ->where('author_user_id', auth()->id())
            ->where(function ($query) use ($assignments) {
                foreach ($assignments as $assignment) {
                    $query->orWhere(function ($inner) use ($assignment) {
                        $inner->where('school_class_id', $assignment->school_class_id)->where('subject_id', $assignment->subject_id);
                    });
                }
            })
            ->latest()
            ->get();
    }

    protected function attempts(Collection $tdIds): Collection
    {
        if ($tdIds->isEmpty() || !Schema::hasTable('td_attempts')) return collect();
        return DB::table('td_attempts')->whereIn('td_set_id', $tdIds)->get();
    }
}
