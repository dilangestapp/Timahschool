<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Str;

class ExamCountdown
{
    public static function all(): array
    {
        return collect([
            [
                'key' => 'bac_esg_2026',
                'label' => 'Baccalauréat ESG 2026',
                'short_label' => 'Bac 2026',
                'audience' => 'Terminale A, C, D, CD, E, SH, TI',
                'starts_at' => '2026-05-25 07:30:00',
                'ends_at' => '2026-05-30 18:00:00',
                'badge' => 'Terminale',
                'color' => 'blue',
                'notes' => 'Épreuves écrites du Baccalauréat général.',
            ],
            [
                'key' => 'bepc_2026',
                'label' => 'BEPC / BEPC bilingue 2026',
                'short_label' => 'BEPC 2026',
                'audience' => 'Classe de 3e / 3ème',
                'starts_at' => '2026-06-01 07:30:00',
                'ends_at' => '2026-06-04 18:00:00',
                'badge' => '3e',
                'color' => 'green',
                'notes' => 'Épreuves écrites du BEPC et BEPC bilingue.',
            ],
            [
                'key' => 'probatoire_esg_2026',
                'label' => 'Probatoire ESG 2026',
                'short_label' => 'Probatoire 2026',
                'audience' => 'Première A, C, D, E, SH, TI',
                'starts_at' => '2026-06-08 07:30:00',
                'ends_at' => '2026-06-12 18:00:00',
                'badge' => 'Première',
                'color' => 'orange',
                'notes' => 'Épreuves écrites du Probatoire général.',
            ],
            [
                'key' => 'gce_2026',
                'label' => 'GCE O Level / A Level 2026',
                'short_label' => 'GCE 2026',
                'audience' => 'Sous-système anglophone',
                'starts_at' => '2026-06-02 07:30:00',
                'ends_at' => '2026-06-18 18:00:00',
                'badge' => 'GCE',
                'color' => 'purple',
                'notes' => 'Épreuves écrites GCE Ordinary Level et Advanced Level.',
            ],
        ])->map(fn ($exam) => self::decorate($exam))->all();
    }

    public static function forClass(?string $className): ?array
    {
        $normalized = self::normalize($className ?? '');

        if ($normalized === '') {
            return null;
        }

        if (Str::contains($normalized, ['terminale', 'terminale cd', 'terminal c', 'terminal d', 'tale', 'tlle'])) {
            return self::find('bac_esg_2026');
        }

        if (Str::contains($normalized, ['premiere', 'première', '1ere', '1ère'])) {
            return self::find('probatoire_esg_2026');
        }

        if (Str::contains($normalized, ['3eme', '3ème', 'troisieme', 'troisième', 'third form', 'form 5'])) {
            return self::find('bepc_2026');
        }

        if (Str::contains($normalized, ['gce', 'ordinary', 'advanced', 'lower sixth', 'upper sixth', 'form 5'])) {
            return self::find('gce_2026');
        }

        return null;
    }

    public static function find(string $key): ?array
    {
        return collect(self::all())->firstWhere('key', $key);
    }

    protected static function decorate(array $exam): array
    {
        $start = Carbon::parse($exam['starts_at']);
        $end = Carbon::parse($exam['ends_at']);
        $now = now();

        if ($now->lt($start)) {
            $status = 'upcoming';
            $target = $start;
            $headline = 'Démarre dans';
        } elseif ($now->between($start, $end)) {
            $status = 'running';
            $target = $end;
            $headline = 'En cours — fin dans';
        } else {
            $status = 'finished';
            $target = $end;
            $headline = 'Session terminée';
        }

        $seconds = max(0, $now->diffInSeconds($target, false));
        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $progress = 0;

        if ($now->gte($start) && $now->lte($end)) {
            $total = max(1, $start->diffInSeconds($end));
            $done = max(0, $start->diffInSeconds($now));
            $progress = min(100, (int) round(($done / $total) * 100));
        } elseif ($now->gt($end)) {
            $progress = 100;
        }

        return array_merge($exam, [
            'start_label' => $start->locale('fr')->translatedFormat('d F Y'),
            'end_label' => $end->locale('fr')->translatedFormat('d F Y'),
            'status' => $status,
            'headline' => $headline,
            'target_iso' => $target->toIso8601String(),
            'days' => $days,
            'hours' => $hours,
            'minutes' => $minutes,
            'progress' => $progress,
        ]);
    }

    protected static function normalize(string $value): string
    {
        $value = Str::lower(trim($value));
        $value = str_replace(['é', 'è', 'ê', 'ë'], 'e', $value);
        $value = str_replace(['à', 'â'], 'a', $value);
        $value = str_replace(['î', 'ï'], 'i', $value);
        $value = str_replace(['ô'], 'o', $value);
        $value = str_replace(['û', 'ù'], 'u', $value);

        return $value;
    }
}
