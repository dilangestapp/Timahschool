<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class AnonymousVoiceTransformer
{
    public function store(UploadedFile $file, string $directory = 'teacher_messages'): array
    {
        $sourceRelativePath = $file->store($directory . '/source', 'local');
        $sourceAbsolutePath = Storage::disk('local')->path($sourceRelativePath);
        $originalName = $file->getClientOriginalName() ?: 'note-vocale.webm';

        if (!$this->ffmpegAvailable()) {
            return [
                'path' => $sourceRelativePath,
                'name' => $originalName,
                'transformed' => false,
            ];
        }

        $targetRelativePath = $directory . '/voice/' . pathinfo($sourceRelativePath, PATHINFO_FILENAME) . '-voice-naturelle.mp3';
        $targetAbsolutePath = Storage::disk('local')->path($targetRelativePath);

        Storage::disk('local')->makeDirectory(dirname($targetRelativePath));

        // Voix naturelle : pas de changement de hauteur, pas d'effet robot.
        // On nettoie légèrement et on augmente le volume pour une écoute claire.
        $filter = implode(',', [
            'highpass=f=80',
            'lowpass=f=12000',
            'afftdn=nf=-20',
            'dynaudnorm=f=150:g=12:p=0.95',
            'acompressor=threshold=-20dB:ratio=2.5:attack=8:release=120:makeup=6dB',
            'volume=1.8',
        ]);

        $process = new Process([
            'ffmpeg',
            '-y',
            '-i',
            $sourceAbsolutePath,
            '-vn',
            '-af',
            $filter,
            '-ac',
            '1',
            '-ar',
            '44100',
            '-c:a',
            'libmp3lame',
            '-b:a',
            '160k',
            $targetAbsolutePath,
        ]);

        $process->setTimeout(120);
        $process->run();

        if (!$process->isSuccessful() || !is_file($targetAbsolutePath) || filesize($targetAbsolutePath) === 0) {
            Log::warning('Voice enhancement failed. Falling back to original audio.', [
                'original' => $sourceRelativePath,
                'error' => $process->getErrorOutput(),
            ]);

            return [
                'path' => $sourceRelativePath,
                'name' => $originalName,
                'transformed' => false,
            ];
        }

        Storage::disk('local')->delete($sourceRelativePath);

        return [
            'path' => $targetRelativePath,
            'name' => 'voice-naturelle-' . now()->format('Ymd-His') . '.mp3',
            'transformed' => true,
        ];
    }

    protected function ffmpegAvailable(): bool
    {
        $process = new Process(['ffmpeg', '-version']);
        $process->setTimeout(15);
        $process->run();

        return $process->isSuccessful();
    }
}
