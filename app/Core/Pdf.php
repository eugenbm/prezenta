<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Generator PDF minimal, fără dependențe externe (fără Composer/biblioteci),
 * potrivit pentru hosting partajat. Produce un document A4 cu tabel pe două
 * coloane (etichetă / valoare) și antete de secțiune, cu paginare automată.
 *
 * Notă: folosește fonturile standard PDF (Helvetica) cu codare WinAnsi
 * (CP1252), care nu conțin diacriticele românești cu virgulă/căciulă; textul
 * e transliterat automat la ASCII (ș→s, ă→a etc.) pentru un fișier valid pe
 * orice cititor PDF, fără a fi nevoie de fonturi încorporate.
 */
final class Pdf
{
    private const PAGE_W = 595.28;
    private const PAGE_H = 841.89;
    private const MARGIN = 36.0;
    private const GROUP_TITLE_W = 140.0;
    private const LABEL_FRACTION = 0.42;
    private const FONT = 9.0;
    private const LINE_H = 11.0;
    private const PAD_X = 4.0;
    private const PAD_Y = 3.0;

    /** @var string[] fluxul de conținut pentru fiecare pagină */
    private array $pages = [''];
    private int $cur = 0;
    private float $y;

    public function __construct()
    {
        $this->y = self::PAGE_H - self::MARGIN;
    }

    public function title(string $title, string $subtitle, array $metaLines): void
    {
        $x = self::MARGIN;
        $this->text($x, $this->y - 15, 15, true, $this->enc($title));
        $this->y -= 22;
        if ($subtitle !== '') {
            $this->text($x, $this->y - 10, 10, false, $this->enc($subtitle));
            $this->y -= 15;
        }
        foreach ($metaLines as $line) {
            $this->text($x, $this->y - 8, 8, false, $this->enc($line), 0.33, 0.33, 0.33);
            $this->y -= 11;
        }
        $this->y -= 3;
        $right = self::PAGE_W - self::MARGIN;
        $this->put(sprintf("0.52 0.09 0.13 RG\n1.2 w\n%.2f %.2f m %.2f %.2f l S\n", $x, $this->y, $right, $this->y));
        $this->y -= 8;
    }

    /**
     * O linie a tabelului, formată din una sau mai multe perechi etichetă/valoare
     * dispuse pe același rând (perechile împart egal lățimea disponibilă).
     * @param array<int, array{0:string,1:string}> $pairs
     */
    public function line(array $pairs): void
    {
        $cells = $this->buildCells($pairs, $this->contentWidth());
        $h = $this->measureCells($cells);
        $this->ensure($h);
        $this->drawCells(self::MARGIN, $this->y, $cells, $h);
        $this->y -= $h;
    }

    /** Rând simplu cu o singură pereche etichetă/valoare pe toată lățimea. */
    public function row(string $label, string $value): void
    {
        $this->line([[$label, $value]]);
    }

    /**
     * Bloc cu o celulă-titlu în stânga care se întinde pe toate sub-rândurile
     * din dreapta (ca în fișa-model). Fiecare sub-rând e o listă de perechi
     * etichetă/valoare afișate pe același rând.
     * @param array<int, array<int, array{0:string,1:string}>> $rows
     */
    public function group(string $title, array $rows): void
    {
        $x = self::MARGIN;
        $rightW = $this->contentWidth() - self::GROUP_TITLE_W;

        $cellsPerRow = [];
        $heights = [];
        foreach ($rows as $r) {
            $cells = $this->buildCells($r, $rightW);
            $cellsPerRow[] = $cells;
            $heights[] = $this->measureCells($cells);
        }
        $total = array_sum($heights);
        $this->ensure($total);
        $top = $this->y;

        $this->fillRect($x, $top - $total, self::GROUP_TITLE_W, $total, 0.90, 0.87, 0.85);
        $this->strokeRect($x, $top - $total, self::GROUP_TITLE_W, $total);
        $ty = $top - self::PAD_Y - self::FONT + 2;
        foreach ($this->wrap($this->enc($title), self::GROUP_TITLE_W - 2 * self::PAD_X) as $ln) {
            $this->text($x + self::PAD_X, $ty, self::FONT, true, $ln);
            $ty -= self::LINE_H;
        }

        $cy = $top;
        foreach ($cellsPerRow as $i => $cells) {
            $this->drawCells($x + self::GROUP_TITLE_W, $cy, $cells, $heights[$i]);
            $cy -= $heights[$i];
        }
        $this->y -= $total;
    }

    public function output(): string
    {
        $objs = [];
        $objs[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objs[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objs[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        $num = 5;
        $kids = [];
        foreach ($this->pages as $stream) {
            $contentNum = $num++;
            $pageNum = $num++;
            $objs[$contentNum] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";
            $objs[$pageNum] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2f %.2f] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>',
                self::PAGE_W,
                self::PAGE_H,
                $contentNum
            );
            $kids[] = "$pageNum 0 R";
        }
        $objs[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($this->pages) . ' >>';

        ksort($objs);
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objs as $n => $body) {
            $offsets[$n] = strlen($pdf);
            $pdf .= "$n 0 obj\n$body\nendobj\n";
        }
        $xrefPos = strlen($pdf);
        $count = count($objs) + 1;
        $pdf .= "xref\n0 $count\n0000000000 65535 f \n";
        for ($n = 1; $n < $count; $n++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$n]);
        }
        $pdf .= "trailer\n<< /Size $count /Root 1 0 R >>\nstartxref\n$xrefPos\n%%EOF";

        return $pdf;
    }

    private function contentWidth(): float
    {
        return self::PAGE_W - 2 * self::MARGIN;
    }

    /**
     * Construiește celulele unei linii din perechi etichetă/valoare. Fiecare
     * pereche primește o cotă egală din lățimea disponibilă, împărțită în
     * eticheta (fundal gri) și valoarea aferentă.
     * @param array<int, array{0:string,1:string}> $pairs
     * @return array<int, array{w:float,text:string,style:string}>
     */
    private function buildCells(array $pairs, float $availW): array
    {
        $n = max(1, count($pairs));
        $pairW = $availW / $n;
        $cells = [];
        foreach ($pairs as $pair) {
            $labelW = $pairW * self::LABEL_FRACTION;
            $cells[] = ['w' => $labelW, 'text' => (string) $pair[0], 'style' => 'label'];
            $cells[] = ['w' => $pairW - $labelW, 'text' => (string) $pair[1], 'style' => 'value'];
        }
        return $cells;
    }

    private function measureCells(array $cells): float
    {
        $max = 1;
        foreach ($cells as $c) {
            $lines = count($this->wrap($this->enc($c['text']), $c['w'] - 2 * self::PAD_X));
            if ($lines > $max) {
                $max = $lines;
            }
        }
        return $max * self::LINE_H + 2 * self::PAD_Y;
    }

    private function drawCells(float $x, float $top, array $cells, float $h): void
    {
        $cx = $x;
        foreach ($cells as $c) {
            $w = $c['w'];
            $bold = $c['style'] === 'label';
            if ($bold) {
                $this->fillRect($cx, $top - $h, $w, $h, 0.95, 0.93, 0.91);
            }
            $this->strokeRect($cx, $top - $h, $w, $h);
            $ty = $top - self::PAD_Y - self::FONT + 2;
            foreach ($this->wrap($this->enc($c['text']), $w - 2 * self::PAD_X) as $ln) {
                $this->text($cx + self::PAD_X, $ty, self::FONT, $bold, $ln);
                $ty -= self::LINE_H;
            }
            $cx += $w;
        }
    }

    private function put(string $s): void
    {
        $this->pages[$this->cur] .= $s;
    }

    private function ensure(float $needed): void
    {
        if ($this->y - $needed < self::MARGIN) {
            $this->pages[] = '';
            $this->cur = count($this->pages) - 1;
            $this->y = self::PAGE_H - self::MARGIN;
        }
    }

    private function fillRect(float $x, float $y, float $w, float $h, float $r, float $g, float $b): void
    {
        $this->put(sprintf("%.3f %.3f %.3f rg\n%.2f %.2f %.2f %.2f re\nf\n", $r, $g, $b, $x, $y, $w, $h));
    }

    private function strokeRect(float $x, float $y, float $w, float $h): void
    {
        $this->put(sprintf("0.6 0.6 0.6 RG\n0.4 w\n%.2f %.2f %.2f %.2f re\nS\n", $x, $y, $w, $h));
    }

    private function text(float $x, float $y, float $size, bool $bold, string $str, float $r = 0.0, float $g = 0.0, float $b = 0.0): void
    {
        $font = $bold ? '/F2' : '/F1';
        $this->put(sprintf(
            "BT %s %.2f Tf %.3f %.3f %.3f rg %.2f %.2f Td (%s) Tj ET\n",
            $font,
            $size,
            $r,
            $g,
            $b,
            $x,
            $y,
            $this->pesc($str)
        ));
    }

    /** UTF-8 -> CP1252 (WinAnsi), cu transliterare a diacriticelor lipsă. */
    private function enc(string $s): string
    {
        $r = @iconv('UTF-8', 'CP1252//TRANSLIT//IGNORE', $s);
        return $r === false ? $s : $r;
    }

    /** Escapare pentru literalii de tip șir dintr-un flux PDF. */
    private function pesc(string $s): string
    {
        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ''], $s);
    }

    /** Împarte un text (deja codat CP1252) pe rânduri care încap în lățimea dată. */
    private function wrap(string $text, float $maxWidth): array
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        if ($text === '') {
            return [''];
        }
        $out = [];
        $cur = '';
        foreach (explode(' ', $text) as $word) {
            $try = $cur === '' ? $word : $cur . ' ' . $word;
            if ($this->textWidth($try) <= $maxWidth || $cur === '') {
                if ($cur === '' && $this->textWidth($word) > $maxWidth) {
                    $chunks = $this->hardSplit($word, $maxWidth);
                    $cur = array_pop($chunks);
                    foreach ($chunks as $c) {
                        $out[] = $c;
                    }
                } else {
                    $cur = $try;
                }
            } else {
                $out[] = $cur;
                if ($this->textWidth($word) > $maxWidth) {
                    $chunks = $this->hardSplit($word, $maxWidth);
                    $cur = array_pop($chunks);
                    foreach ($chunks as $c) {
                        $out[] = $c;
                    }
                } else {
                    $cur = $word;
                }
            }
        }
        $out[] = $cur;
        return $out;
    }

    private function hardSplit(string $word, float $maxWidth): array
    {
        $res = [];
        $cur = '';
        $len = strlen($word);
        for ($i = 0; $i < $len; $i++) {
            $ch = $word[$i];
            if ($cur !== '' && $this->textWidth($cur . $ch) > $maxWidth) {
                $res[] = $cur;
                $cur = $ch;
            } else {
                $cur .= $ch;
            }
        }
        if ($cur !== '') {
            $res[] = $cur;
        }
        return $res ?: [''];
    }

    /** Aproximare a lățimii textului în Helvetica (suficientă pentru încadrare). */
    private function textWidth(string $s): float
    {
        return strlen($s) * self::FONT * 0.5;
    }
}
