<?php

namespace App\Support;

use App\Models\Course;
use Symfony\Component\Process\Process;

class CourseDocumentExtractor
{
    public function extract(Course $course, string $path): array
    {
        $extension = strtolower(pathinfo($course->document_name ?: $course->document_path ?: '', PATHINFO_EXTENSION));

        if ($extension === 'pdf') {
            $html = $this->pdfToHtml($path);
            if (trim(strip_tags($html)) !== '' || str_contains($html, '<img')) {
                return ['html' => $html, 'text' => trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? ''), 'mode' => 'pdf_html'];
            }
        }

        $text = $this->text($course, $path);

        return ['html' => $this->html($text), 'text' => trim(preg_replace('/\s+/u', ' ', $text) ?? $text), 'mode' => 'text'];
    }

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
                $html[] = '<p>' . implode('<br>', array_map('e', $lines)) . '</p>';
            }
        }

        return implode("\n", $html);
    }

    private function pdfToHtml(string $path): string
    {
        if (!$this->commandExists('pdftohtml')) {
            return '';
        }

        $dir = sys_get_temp_dir() . '/timah-pdf-html-' . uniqid('', true);
        @mkdir($dir, 0775, true);
        $base = $dir . '/document';

        $process = new Process(['pdftohtml', '-c', '-s', '-noframes', '-enc', 'UTF-8', $path, $base]);
        $process->setTimeout(180);
        $process->run();

        $htmlFile = $base . '.html';
        if (!is_file($htmlFile)) {
            $this->cleanDir($dir);
            return '';
        }

        $html = (string) file_get_contents($htmlFile);
        $html = $this->inlineLocalImages($html, $dir);
        $html = $this->extractBody($html);
        $html = $this->cleanPdfHtml($html);
        $this->cleanDir($dir);

        return $html;
    }

    private function inlineLocalImages(string $html, string $dir): string
    {
        return preg_replace_callback('/<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>/i', function ($match) use ($dir) {
            $src = html_entity_decode($match[1], ENT_QUOTES, 'UTF-8');
            if (str_starts_with($src, 'data:') || preg_match('/^https?:\/\//i', $src)) {
                return $match[0];
            }
            $file = realpath($dir . '/' . ltrim($src, '/'));
            if (!$file || !str_starts_with($file, realpath($dir)) || !is_file($file)) {
                return $match[0];
            }
            $mime = mime_content_type($file) ?: 'image/png';
            $data = base64_encode((string) file_get_contents($file));
            return str_replace($match[1], 'data:' . $mime . ';base64,' . $data, $match[0]);
        }, $html) ?? $html;
    }

    private function extractBody(string $html): string
    {
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $match)) {
            return $match[1];
        }
        return $html;
    }

    private function cleanPdfHtml(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/<meta\b[^>]*>/i', '', $html) ?? $html;
        $html = preg_replace('/<link\b[^>]*>/i', '', $html) ?? $html;
        $html = preg_replace('/<title\b[^>]*>.*?<\/title>/is', '', $html) ?? $html;
        $html = preg_replace('/position\s*:\s*absolute/i', 'position:absolute', $html) ?? $html;
        return '<div class="course-pdf-extracted">' . trim($html) . '</div>';
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
        $this->cleanDir($dir);

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

    private function cleanDir(string $dir): void
    {
        foreach (glob($dir . '/*') ?: [] as $file) {
            is_dir($file) ? $this->cleanDir($file) : @unlink($file);
        }
        @rmdir($dir);
    }

    private function commandExists(string $command): bool
    {
        $process = Process::fromShellCommandline('command -v ' . escapeshellarg($command));
        $process->setTimeout(10);
        $process->run();

        return $process->isSuccessful() && trim($process->getOutput()) !== '';
    }
}
