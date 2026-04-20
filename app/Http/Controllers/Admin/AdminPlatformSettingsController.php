<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSetting;
use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminPlatformSettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit', [
            'general' => PlatformSetting::group('general'),
            'adminDashboard' => PlatformSetting::group('dashboard_admin'),
            'teacherDashboard' => PlatformSetting::group('dashboard_teacher'),
            'studentDashboard' => PlatformSetting::group('dashboard_student'),
            'homepage' => HomepageSetting::homepagePayload(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'general_platform_name' => ['required', 'string', 'max:120'],
            'general_platform_slogan' => ['nullable', 'string', 'max:255'],
            'general_support_email' => ['nullable', 'string', 'max:120'],
            'general_support_phone' => ['nullable', 'string', 'max:120'],
            'general_support_whatsapp' => ['nullable', 'string', 'max:120'],
            'general_footer_text' => ['nullable', 'string', 'max:400'],
            'general_primary_color' => ['nullable', 'string', 'max:20'],
            'general_secondary_color' => ['nullable', 'string', 'max:20'],
            'general_logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:4096'],
            'general_remove_logo' => ['nullable', 'boolean'],

            'admin_page_title' => ['required', 'string', 'max:150'],
            'admin_page_subtitle' => ['required', 'string', 'max:255'],
            'admin_modules_title' => ['required', 'string', 'max:150'],
            'admin_modules_text' => ['required', 'string', 'max:255'],
            'admin_decision_title' => ['required', 'string', 'max:150'],
            'admin_decision_text' => ['required', 'string', 'max:255'],
            'admin_indicators_title' => ['required', 'string', 'max:150'],
            'admin_indicators_text' => ['required', 'string', 'max:255'],
            'admin_recent_td_title' => ['required', 'string', 'max:150'],
            'admin_recent_messages_title' => ['required', 'string', 'max:150'],

            'teacher_page_title' => ['required', 'string', 'max:150'],
            'teacher_page_subtitle' => ['required', 'string', 'max:255'],
            'teacher_assignments_title' => ['required', 'string', 'max:150'],
            'teacher_assignments_button' => ['required', 'string', 'max:150'],
            'teacher_assignments_empty' => ['required', 'string', 'max:255'],
            'teacher_latest_td_title' => ['required', 'string', 'max:150'],
            'teacher_latest_td_empty' => ['required', 'string', 'max:255'],
            'teacher_latest_questions_title' => ['required', 'string', 'max:150'],
            'teacher_latest_questions_empty' => ['required', 'string', 'max:255'],

            'student_hero_badge' => ['required', 'string', 'max:150'],
            'student_hero_title' => ['required', 'string', 'max:180'],
            'student_hero_highlight' => ['required', 'string', 'max:180'],
            'student_hero_text' => ['required', 'string', 'max:500'],
            'student_workspace_title' => ['required', 'string', 'max:120'],
            'student_workspace_text' => ['required', 'string', 'max:255'],
            'student_goal_title' => ['required', 'string', 'max:120'],
            'student_goal_text' => ['required', 'string', 'max:255'],
            'student_progress_title' => ['required', 'string', 'max:150'],
            'student_progress_text' => ['required', 'string', 'max:255'],
            'student_activity_title' => ['required', 'string', 'max:150'],
            'student_activity_text' => ['required', 'string', 'max:255'],
            'student_td_title' => ['required', 'string', 'max:150'],
            'student_td_text' => ['required', 'string', 'max:255'],
            'student_refs_title' => ['required', 'string', 'max:150'],
            'student_refs_text' => ['required', 'string', 'max:255'],
            'student_advice_title' => ['required', 'string', 'max:150'],
            'student_advice_text' => ['required', 'string', 'max:255'],
            'student_advice_note' => ['required', 'string', 'max:255'],
            'student_shortcut_td_title' => ['required', 'string', 'max:150'],
            'student_shortcut_td_text' => ['required', 'string', 'max:255'],
            'student_shortcut_messages_title' => ['required', 'string', 'max:150'],
            'student_shortcut_messages_text' => ['required', 'string', 'max:255'],
        ]);

        $currentGeneral = PlatformSetting::group('general');
        $logoPath = $currentGeneral['logo_path'] ?? '';

        if ($request->boolean('general_remove_logo') && $logoPath) {
            $oldPublicFile = public_path($logoPath);
            $oldStorageFile = public_path('storage/' . ltrim($logoPath, '/'));

            if (file_exists($oldPublicFile)) {
                @unlink($oldPublicFile);
            }

            if (file_exists($oldStorageFile)) {
                @unlink($oldStorageFile);
            }

            $logoPath = '';
        }

        if ($request->hasFile('general_logo')) {
            if ($logoPath) {
                $oldPublicFile = public_path($logoPath);
                $oldStorageFile = public_path('storage/' . ltrim($logoPath, '/'));

                if (file_exists($oldPublicFile)) {
                    @unlink($oldPublicFile);
                }

                if (file_exists($oldStorageFile)) {
                    @unlink($oldStorageFile);
                }
            }

            $directory = public_path('uploads/branding');

            if (!is_dir($directory)) {
                @mkdir($directory, 0775, true);
            }

            $file = $request->file('general_logo');
            $filename = 'platform-logo-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $filename);

            $logoPath = 'uploads/branding/' . $filename;
        }

        PlatformSetting::putGroup('general', [
            'platform_name' => $validated['general_platform_name'],
            'platform_slogan' => $validated['general_platform_slogan'] ?? '',
            'support_email' => $validated['general_support_email'] ?? '',
            'support_phone' => $validated['general_support_phone'] ?? '',
            'support_whatsapp' => $validated['general_support_whatsapp'] ?? '',
            'footer_text' => $validated['general_footer_text'] ?? '',
            'primary_color' => $validated['general_primary_color'] ?? '#315efb',
            'secondary_color' => $validated['general_secondary_color'] ?? '#7c3aed',
            'logo_path' => $logoPath,
        ]);

        PlatformSetting::putGroup('dashboard_admin', [
            'page_title' => $validated['admin_page_title'],
            'page_subtitle' => $validated['admin_page_subtitle'],
            'modules_title' => $validated['admin_modules_title'],
            'modules_text' => $validated['admin_modules_text'],
            'decision_title' => $validated['admin_decision_title'],
            'decision_text' => $validated['admin_decision_text'],
            'indicators_title' => $validated['admin_indicators_title'],
            'indicators_text' => $validated['admin_indicators_text'],
            'recent_td_title' => $validated['admin_recent_td_title'],
            'recent_messages_title' => $validated['admin_recent_messages_title'],
        ]);

        PlatformSetting::putGroup('dashboard_teacher', [
            'page_title' => $validated['teacher_page_title'],
            'page_subtitle' => $validated['teacher_page_subtitle'],
            'assignments_title' => $validated['teacher_assignments_title'],
            'assignments_button' => $validated['teacher_assignments_button'],
            'assignments_empty' => $validated['teacher_assignments_empty'],
            'latest_td_title' => $validated['teacher_latest_td_title'],
            'latest_td_empty' => $validated['teacher_latest_td_empty'],
            'latest_questions_title' => $validated['teacher_latest_questions_title'],
            'latest_questions_empty' => $validated['teacher_latest_questions_empty'],
        ]);

        PlatformSetting::putGroup('dashboard_student', [
            'hero_badge' => $validated['student_hero_badge'],
            'hero_title' => $validated['student_hero_title'],
            'hero_highlight' => $validated['student_hero_highlight'],
            'hero_text' => $validated['student_hero_text'],
            'workspace_title' => $validated['student_workspace_title'],
            'workspace_text' => $validated['student_workspace_text'],
            'goal_title' => $validated['student_goal_title'],
            'goal_text' => $validated['student_goal_text'],
            'progress_title' => $validated['student_progress_title'],
            'progress_text' => $validated['student_progress_text'],
            'activity_title' => $validated['student_activity_title'],
            'activity_text' => $validated['student_activity_text'],
            'td_title' => $validated['student_td_title'],
            'td_text' => $validated['student_td_text'],
            'refs_title' => $validated['student_refs_title'],
            'refs_text' => $validated['student_refs_text'],
            'advice_title' => $validated['student_advice_title'],
            'advice_text' => $validated['student_advice_text'],
            'advice_note' => $validated['student_advice_note'],
            'shortcut_td_title' => $validated['student_shortcut_td_title'],
            'shortcut_td_text' => $validated['student_shortcut_td_text'],
            'shortcut_messages_title' => $validated['student_shortcut_messages_title'],
            'shortcut_messages_text' => $validated['student_shortcut_messages_text'],
        ]);

        return back()->with('success', 'Paramètres plateforme mis à jour avec succès.');
    }
}
