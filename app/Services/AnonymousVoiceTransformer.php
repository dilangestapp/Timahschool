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

        $targetRelativePath = $directory . '/anonymized/' . pathinfo($sourceRelativePath, PATHINFO_FILENAME) . '-anonymous.m4a';
        $targetAbsolutePath = Storage::disk('local')->path($targetRelativePath);

        Storage::disk('local')->makeDirectory(dirname($targetRelativePath));

        $filter = implode(',', [
            'asetrate=44100*0.90',
            'atempo=1.111111',
            'highpass=f=140',
            'lowpass=f=3000',
            'afftdn=nf=-20',
            'acompressor=threshold=-18dB:ratio=2.2:attack=20:release=250',
        ]);

        $process = new Process([
            'ffmpeg',
            '-y',
            '-i',
            $sourceAbsolutePath,
            '-vn',
            '-af',
            $filter,
            '-c:a',
            'aac',
            '-b:a',
            '96k',
            $targetAbsolutePath,
        ]);

        $process->setTimeout(120);
        $process->run();

        if (!$process->isSuccessful() || !is_file($targetAbsolutePath)) {
            Log::warning('Anonymous voice transformation failed. Falling back to original audio.', [
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
            'name' => 'voice-anonyme-' . now()->format('Ymd-His') . '.m4a',
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
