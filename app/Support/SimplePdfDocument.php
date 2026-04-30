<?php

namespace App\Support;

class SimplePdfDocument
{
    public static function make(string $title, string $subtitle, string $html, string $footerLabel = ''): string
    {
        $lines = self::wrapLines(self::htmlToText($html));
        $pages = [];
        $page = [];
        $y = 745;
        $lineHeight = 14;

        foreach ($lines as $line) {
            if ($y < 65) {
                $pages[] = $page;
                $page = [];
                $y = 745;
            }
            $page[] = [$line, $y];
            $y -= $line === '' ? 8 : $lineHeight;
        }

        if ($page || empty($pages)) {
            $pages[] = $page;
        }

        return self::buildPdf($title, $subtitle, $pages, $footerLabel ?: $title);
    }

    private static function htmlToText(string $html): string
    {
        $html = preg_replace('/<\s*br\s*\/?>/i', "\n", $html);
        $html = preg_replace('/<\s*\/?\s*(h1|h2|h3|h4|p|div|section|article|ol|ul|pre)[^>]*>/i', "\n", $html);
        $html = preg_replace('/<\s*li[^>]*>/i', "\n- ", $html);
        $html = preg_replace('/<\s*\/\s*li\s*>/i', '', $html);
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    private static function wrapLines(string $text): array
    {
        $result = [];
        $paragraphs = preg_split('/\n/', $text) ?: [];

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                $result[] = '';
                continue;
            }

            $paragraph = self::normalizeText($paragraph);
            $words = preg_split('/\s+/', $paragraph) ?: [];
            $line = '';
            $max = 96;

            foreach ($words as $word) {
                $candidate = $line === '' ? $word : $line.' '.$word;
                if (mb_strlen($candidate) > $max) {
                    if ($line !== '') {
                        $result[] = $line;
                    }
                    $line = $word;
                } else {
                    $line = $candidate;
                }
            }

            if ($line !== '') {
                $result[] = $line;
            }
        }

        return $result;
    }

    private static function normalizeText(string $text): string
    {
        $search = ['←', '×', '÷', '≈', '≥', '≤', '→', '–', '—', '“', '”', '’'];
        $replace = ['<-', 'x', '/', '~', '>=', '<=', '->', '-', '-', '"', '"', "'"];
        return str_replace($search, $replace, $text);
    }

    private static function buildPdf(string $title, string $subtitle, array $pages, string $footerLabel): string
    {
        $objects = [];
        $pagesKids = [];

        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[] = '__PAGES__';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';

        foreach ($pages as $index => $lines) {
            $pageNo = $index + 1;
            $content = self::pageStream($title, $subtitle, $lines, $footerLabel, $pageNo);
            $contentObjNo = count($objects) + 2;
            $pageObjNo = count($objects) + 1;
            $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents '.$contentObjNo.' 0 R >>';
            $objects[] = '<< /Length '.strlen($content).' >>' . "\nstream\n" . $content . "\nendstream";
            $pagesKids[] = $pageObjNo.' 0 R';
        }

        $objects[1] = '<< /Type /Pages /Kids ['.implode(' ', $pagesKids).'] /Count '.count($pages).' >>';

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];

        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $num = $i + 1;
            $pdf .= $num." 0 obj\n".$object."\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i])."\n";
        }
        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF";

        return $pdf;
    }

    private static function pageStream(string $title, string $subtitle, array $lines, string $footerLabel, int $pageNo): string
    {
        $stream = "0.12 0.25 0.69 rg\n0 800 595 42 re f\n";
        $stream .= self::text(38, 818, 12, self::safe($title), true, '1 1 1 rg');
        $stream .= self::text(38, 802, 9, self::safe($subtitle), false, '1 1 1 rg');
        $stream .= "0.89 0.93 1 rg\n38 770 519 18 re f\n";
        $stream .= self::text(48, 775, 8, 'Document PDF - TIMAH ACADEMY', false, '0.1 0.16 0.3 rg');

        foreach ($lines as [$line, $y]) {
            if ($line === '') {
                continue;
            }
            $isHeading = preg_match('/^(TD-|CORRIG|Exercice|Correction|Document|Th.me|Introduction|Classe|Mati.re|Num.ro)/iu', $line);
            $stream .= self::text(45, $y, $isHeading ? 10 : 8.4, self::safe($line), (bool) $isHeading, '0.05 0.09 0.16 rg');
        }

        $stream .= "0.8 0.84 0.9 RG\n38 42 m 557 42 l S\n";
        $stream .= self::text(210, 25, 8, self::safe($footerLabel.' | Page '.$pageNo), false, '0.28 0.33 0.41 rg');

        return $stream;
    }

    private static function text(int $x, int $y, float $size, string $text, bool $bold = false, string $color = '0 0 0 rg'): string
    {
        return $color."\nBT /".($bold ? 'F2' : 'F1').' '.$size.' Tf '.$x.' '.$y.' Td ('.self::escape($text).") Tj ET\n";
    }

    private static function safe(string $text): string
    {
        $encoded = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        return $encoded === false ? $text : $encoded;
    }

    private static function escape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
