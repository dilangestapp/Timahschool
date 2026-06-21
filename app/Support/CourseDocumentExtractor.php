<?php

namespace App\Support;

use App\Models\Course;
use Symfony\Component\Process\Process;

class CourseDocumentExtractor
{
    public function text(Course $course, string $path): string
    {
        $extension = strtolower(pathinfo($course->document_name ?: $course->document_path ?: '', PATHINFO_EXTENSION));

        return match ($extension) {
            'txt' => (string) @file_get_contents($path),
            'rtf' => $this->rtf((string) @file_get_contents($path)),
            'docx' => $this->docx($path),
            'odt' => $this->odt($path),
            'pdf' => $this->pdf($path),
            default => $this->office($path),
        };
    }

    public function html(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", trim($text));
        $blocks = preg_split('/\n{2,}/', $text) ?: [$text];
        $html = [];

        foreach ($blocks as $block) {
            $lines = array_filter(array_map('trim', explode("\n", $block)), fn ($line) => $line !== '');
            if ($lines) {
                $html[] = '<p>' . e(implode('<br>', $lines)) . '</p>';
            }
        }

        return implode("\n", $html);
    }

    private function pdf(string $path): string
    {
        if (!$this->commandExists('pdftotext')) {
            return '';
        }

        $tmp = tempnam(sys_get_temp_dir(), 'timah-pdf-');
        @unlink($tmp);
        $process = new Process(['pdftotext', '-layout', '-enc', 'UTF-8', $path, $tmp]);
        $process->setTimeout(120);
        $process->run();

        $text = is_file($tmp) ? (string) file_get_contents($tmp) : '';
        @unlink($tmp);

        return $text;
    }

    private function office(string $path): string
    {
        $binary = $this->commandExists('libreoffice') ? 'libreoffice' : ($this->commandExists('soffice') ? 'soffice' : null);
        if (!$binary) {
            return '';
        }

        $dir = sys_get_temp_dir() . '/timah-office-' . uniqid('', true);
        @mkdir($dir, 0775, true);
        $process = new Process([$binary, '--headless', '--convert-to', 'txt', '--outdir', $dir, $path]);
        $process->setTimeout(180);
        $process->run();

        $files = glob($dir . '/*.txt') ?: [];
        $text = $files ? (string) file_get_contents($files[0]) : '';
        foreach ($files as $file) {
            @unlink($file);
        }
        @rmdir($dir);

        return $text;
    }

    private function docx(string $path): string
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }
        $xml = (string) $zip->getFromName('word/document.xml');
        $zip->close();

        return $this->xml($xml);
    }

    private function odt(string $path): string
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }
        $xml = (string) $zip->getFromName('content.xml');
        $zip->close();

        return $this->xml($xml);
    }

    private function xml(string $xml): string
    {
        $xml = preg_replace('/<[^>]*(p|br|tr|table-row)[^>]*>/i', "\n", $xml) ?? $xml;
        $text = html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
        return trim(preg_replace('/[ \t]+/u', ' ', $text) ?? $text);
    }

    private function rtf(string $rtf): string
    {
        $text = preg_replace('/\\par[d]?/i', "\n", $rtf) ?? $rtf;
        $text = preg_replace('/\\[a-z]+-?\d* ?/i', '', $text) ?? $text;
        return trim(str_replace(['{', '}'], '', $text));
    }

    private function commandExists(string $command): bool
    {
        $process = Process::fromShellCommandline('command -v ' . escapeshellarg($command));
        $process->setTimeout(10);
        $process->run();

        return $process->isSuccessful() && trim($process->getOutput()) !== '';
    }
}
