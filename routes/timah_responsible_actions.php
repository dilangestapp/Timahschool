<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::post('/responsabilites/notes/{note}/status', function ($note) {
    if (!auth()->check()) {
        abort(403);
    }

    if (!Schema::hasTable('pedagogical_responsibilities') || !Schema::hasTable('pedagogical_supervision_notes')) {
        abort(404);
    }

    $data = request()->validate([
        'status' => ['required', 'string', 'in:open,follow_up,resolved'],
    ]);

    $responsibilityIds = DB::table('pedagogical_responsibilities')
        ->where('user_id', auth()->id())
        ->where('is_active', true)
        ->pluck('id')
        ->all();

    $allowed = DB::table('pedagogical_supervision_notes')
        ->where('id', $note)
        ->where(function ($query) use ($responsibilityIds) {
            $query->whereIn('responsibility_id', $responsibilityIds)
                ->orWhere('author_id', auth()->id());
        })
        ->exists();

    if (!$allowed) {
        abort(403);
    }

    DB::table('pedagogical_supervision_notes')
        ->where('id', $note)
        ->update([
            'status' => $data['status'],
            'resolved_at' => $data['status'] === 'resolved' ? now() : null,
            'updated_at' => now(),
        ]);

    return back()->with('success', $data['status'] === 'resolved' ? 'Note marquée comme traitée.' : 'Statut de la note mis à jour.');
})->middleware(['auth', 'no.cache'])->name('responsibilities.notes.status');
