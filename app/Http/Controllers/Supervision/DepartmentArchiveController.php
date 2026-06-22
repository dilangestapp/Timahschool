<?php

namespace App\Http\Controllers\Supervision;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DepartmentArchiveController extends Controller
{
    public function classArchive(int $class)
    {
        abort_unless(auth()->check(), 403);
        abort_unless(Schema::hasTable('school_classes'), 404);

        if (Schema::hasColumn('school_classes', 'is_active')) {
            DB::table('school_classes')->where('id', $class)->update(['is_active' => false, 'updated_at' => now()]);
            return back()->with('warning', 'Classe désactivée pour éviter de casser les tableaux de bord et les contenus liés.');
        }

        return back()->with('error', 'Cette installation ne permet pas encore la désactivation des classes.');
    }

    public function subjectArchive(int $subject)
    {
        abort_unless(auth()->check(), 403);
        abort_unless(Schema::hasTable('subjects'), 404);

        if (Schema::hasColumn('subjects', 'is_active')) {
            DB::table('subjects')->where('id', $subject)->update(['is_active' => false, 'updated_at' => now()]);
            return back()->with('warning', 'Matière désactivée pour éviter de casser les cours, TD et tableaux de bord.');
        }

        return back()->with('error', 'Cette installation ne permet pas encore la désactivation des matières.');
    }
}
