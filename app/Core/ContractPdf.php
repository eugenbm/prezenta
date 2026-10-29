<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Completează cele două câmpuri dintr-un contract de voluntariat PDF existent,
 * fără dependențe externe (fără Composer/biblioteci), potrivit pentru hosting
 * partajat:
 *   1. numele voluntarului la punctul 1.2;
 *   2. semnătura (desenată cu degetul, trimisă ca imagine JPEG) în dreptul
 *      coloanei „VOLUNTAR".
 *
 * Șabloanele originale rămân neschimbate — se aplică o actualizare incrementală
 * (incremental update) la finalul fișierului PDF: se adaugă obiectele noi
 * (font, fluxuri de desen, imaginea semnăturii) și se rescriu doar obiectele
 * paginii 1 și ale ultimei pagini pentru a le referi. Astfel diacriticele și
 * aspectul original al contractului sunt păstrate intacte.
 */
final class ContractPdf
{
    private const PAGE_H = 792.0;

    // Poziția numelui la punctul 1.2 (origine jos-stânga, aceeași în toate șabloanele).
    private const NAME_X = 145.0;
    private const NAME_Y = 472.0;
    private const NAME_SIZE = 12.0;

    // Caseta semnăturii în coloana „VOLUNTAR" (X/lățime/înălțime constante; Y diferă).
    private const SIG_X = 405.0;
    private const SIG_W = 155.0;
    private const SIG_H = 58.0;

    /**
     * Pentru fiecare tip de contract: [fișier șablon, Y (jos-stânga) al semnăturii].
     * Tipul coincide cu rolul voluntarului din aplicație.
     */
    private const TEMPLATES = [
        'applicant' => ['contract_aspirant.pdf', 238.0],
        'salvator'  => ['contract_salvator_montan.pdf', 106.0],
        'formator'  => ['contract_formator.pdf', 226.0],
    ];

    public static function supports(string $type): bool
    {
        return isset(self::TEMPLATES[$type]);
    }

    /** Directorul cu șabloanele de contract (în afara rădăcinii web). */
    public static function templateDir(): string
    {
        return dirname(__DIR__, 2) . '/contracte';
    }

    /** Calea șablonului (necompletat) pentru un tip de contract, sau null. */
    public static function templatePath(string $type): ?string
    {
        if (!isset(self::TEMPLATES[$type])) {
            return null;
        }
        return self::templateDir() . '/' . self::TEMPLATES[$type][0];
    }

    /**
     * Generează PDF-ul completat.
     *
     * @param string $type applicant|salvator|formator
     * @param string $name numele voluntarului (punctul 1.2)
     * @param string $jpeg semnătura ca imagine JPEG (binar)
     */
    public static function fill(string $type, string $name, string $jpeg): string
    {
        if (!isset(self::TEMPLATES[$type])) {
            throw new \RuntimeException("Tip de contract necunoscut: {$type}");
        }
        [$file, $sigYb] = self::TEMPLATES[$type];
        $path = self::templateDir() . '/' . $file;
        $data = @file_get_contents($path);
        if ($data === false) {
            throw new \RuntimeException("Șablonul de contract lipsește: {$path}");
        }
        return self::overlay($data, $name, $jpeg, $sigYb);
    }

    private static function overlay(string $data, string $name, string $jpeg, float $sigYb): string
    {
        // startxref precedent (ultima apariție).
        if (!preg_match_all('~startxref\s+(\d+)\s+%%EOF~', $data, $m)) {
            throw new \RuntimeException('PDF invalid: startxref lipsă.');
        }
        $prevStartxref = (int) end($m[1]);

        // Trailer-ul final.
        $trailerPos = strrpos($data, 'trailer');
        if ($trailerPos === false) {
            throw new \RuntimeException('PDF invalid: trailer lipsă.');
        }
        $trailer = substr($data, $trailerPos);
        if (!preg_match('~/Size\s+(\d+)~', $trailer, $sm)) {
            throw new \RuntimeException('PDF invalid: /Size lipsă.');
        }
        $size = (int) $sm[1];
        $root = preg_match('~/Root\s+(\d+)\s+0\s+R~', $trailer, $rm) ? $rm[1] : '1';
        $info = preg_match('~/Info\s+(\d+)\s+0\s+R~', $trailer, $im) ? $im[1] : null;
        $id = preg_match('~/ID\s*\[[^\]]*\]~', $trailer, $idm) ? $idm[0] : null;

        // Arborele de pagini: prima și ultima pagină.
        $pagesObj = self::findObject($data, 2);
        if (!preg_match('~/Kids\s*\[([^\]]*)\]~', $pagesObj, $km)) {
            throw new \RuntimeException('PDF invalid: /Kids lipsă.');
        }
        preg_match_all('~(\d+)\s+0\s+R~', $km[1], $kids);
        $firstPage = (int) $kids[1][0];
        $lastPage = (int) end($kids[1]);

        $p1 = self::findObject($data, $firstPage);
        $pl = self::findObject($data, $lastPage);
        if (!preg_match('~/Contents\s+(\d+)\s+0\s+R~', $p1, $c1m)
            || !preg_match('~/Contents\s+(\d+)\s+0\s+R~', $pl, $clm)) {
            throw new \RuntimeException('PDF invalid: /Contents lipsă.');
        }
        $c1 = (int) $c1m[1];
        $cl = (int) $clm[1];

        [$iw, $ih, $icomp] = self::jpegInfo($jpeg);
        $cs = $icomp === 1 ? '/DeviceGray' : ($icomp === 4 ? '/DeviceCMYK' : '/DeviceRGB');

        $fontN = $size;
        $txtN = $size + 1;
        $imgN = $size + 2;
        $imgcN = $size + 3;
        $newSize = $size + 4;

        $txtStream = 'q BT /CF1 ' . self::num(self::NAME_SIZE) . ' Tf 0 0 0 rg '
            . self::num(self::NAME_X) . ' ' . self::num(self::NAME_Y) . ' Td ('
            . self::pdfString($name) . ') Tj ET Q';
        $imgcStream = 'q ' . self::num(self::SIG_W) . ' 0 0 ' . self::num(self::SIG_H) . ' '
            . self::num(self::SIG_X) . ' ' . self::num($sigYb) . ' cm /SIG Do Q';

        // Rescrie dicționarul paginii 1: adaugă fontul propriu + fluxul cu numele.
        $p1mod = self::innerDict($p1);
        $p1mod = self::strReplaceOnce($p1mod, '/Font <<', '/Font <</CF1 ' . $fontN . ' 0 R ');
        $p1mod = self::strReplaceOnce($p1mod, '/Contents ' . $c1 . ' 0 R', '/Contents [' . $c1 . ' 0 R ' . $txtN . ' 0 R]');

        // Rescrie dicționarul ultimei pagini: adaugă imaginea semnăturii + fluxul de desen.
        $plmod = self::innerDict($pl);
        $plmod = self::strReplaceOnce($plmod, '/Resources <<', '/Resources <</XObject <</SIG ' . $imgN . ' 0 R>> ');
        $plmod = self::strReplaceOnce($plmod, '/Contents ' . $cl . ' 0 R', '/Contents [' . $cl . ' 0 R ' . $imgcN . ' 0 R]');

        // --- Actualizare incrementală, adăugată la finalul fișierului ---------
        $out = $data;
        $last = substr($out, -1);
        if ($last !== "\n" && $last !== "\r") {
            $out .= "\r\n";
        }

        $offsets = [];
        $append = static function (int $num, string $body) use (&$out, &$offsets): void {
            $offsets[$num] = strlen($out);
            $out .= $num . " 0 obj\r" . $body . "\rendobj\r";
        };

        $append($fontN, '<</Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding>>');
        $append($txtN, '<</Length ' . strlen($txtStream) . ">>\rstream\r" . $txtStream . "\rendstream");
        $imgHdr = '<</Type /XObject /Subtype /Image /Width ' . $iw . ' /Height ' . $ih
            . ' /ColorSpace ' . $cs . ' /BitsPerComponent 8 /Filter /DCTDecode /Length ' . strlen($jpeg) . ">>\rstream\r";
        $append($imgN, $imgHdr . $jpeg . "\rendstream");
        $append($imgcN, '<</Length ' . strlen($imgcStream) . ">>\rstream\r" . $imgcStream . "\rendstream");

        // Obiectele de pagină rescrise păstrează numerele originale.
        $offsets[$firstPage] = strlen($out);
        $out .= $firstPage . " 0 obj\r" . $p1mod . "\rendobj\r";
        $offsets[$lastPage] = strlen($out);
        $out .= $lastPage . " 0 obj\r" . $plmod . "\rendobj\r";

        // Tabel xref nou (doar obiectele modificate/adăugate), grupat pe secțiuni contigue.
        $xrefPos = strlen($out);
        ksort($offsets);
        $groups = [];
        $prev = null;
        foreach ($offsets as $n => $_off) {
            if ($prev !== null && $n === $prev + 1) {
                $groups[count($groups) - 1][] = $n;
            } else {
                $groups[] = [$n];
            }
            $prev = $n;
        }
        $xref = "xref\r\n";
        foreach ($groups as $g) {
            $xref .= $g[0] . ' ' . count($g) . "\r\n";
            foreach ($g as $n) {
                $xref .= sprintf("%010d 00000 n\r\n", $offsets[$n]);
            }
        }
        $out .= $xref;

        $tr = "trailer\r<</Size " . $newSize . ' /Root ' . $root . ' 0 R';
        if ($info !== null) {
            $tr .= ' /Info ' . $info . ' 0 R';
        }
        $tr .= ' /Prev ' . $prevStartxref;
        if ($id !== null) {
            $tr .= ' ' . $id;
        }
        $tr .= ">>\rstartxref\r" . $xrefPos . "\r\n%%EOF";
        $out .= $tr;

        return $out;
    }

    /** Extrage textul obiectului „N 0 obj ... endobj". */
    private static function findObject(string $data, int $num): string
    {
        if (!preg_match('~(?<![0-9])' . $num . '\s+0\s+obj~', $data, $m, PREG_OFFSET_CAPTURE)) {
            throw new \RuntimeException("Obiect PDF {$num} negăsit.");
        }
        $start = $m[0][1];
        $end = strpos($data, 'endobj', $start);
        if ($end === false) {
            throw new \RuntimeException("endobj lipsă pentru obiectul {$num}.");
        }
        return substr($data, $start, $end - $start + 6);
    }

    /** Păstrează doar dicționarul „<<...>>" dintr-un obiect complet. */
    private static function innerDict(string $obj): string
    {
        $obj = preg_replace('~^\s*\d+\s+0\s+obj~', '', $obj, 1);
        $obj = preg_replace('~endobj\s*$~', '', (string) $obj, 1);
        return trim((string) $obj);
    }

    private static function strReplaceOnce(string $haystack, string $search, string $replace): string
    {
        $pos = strpos($haystack, $search);
        if ($pos === false) {
            throw new \RuntimeException("Secvență negăsită în obiectul PDF: {$search}");
        }
        return substr_replace($haystack, $replace, $pos, strlen($search));
    }

    /** UTF-8 -> CP1252 (WinAnsi) + escapare pentru literal de șir PDF. */
    private static function pdfString(string $s): string
    {
        $r = @iconv('UTF-8', 'CP1252//TRANSLIT//IGNORE', $s);
        if ($r !== false) {
            $s = $r;
        }
        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ''], $s);
    }

    private static function num(float $f): string
    {
        return rtrim(rtrim(sprintf('%.2f', $f), '0'), '.');
    }

    /**
     * Dimensiunile și numărul de componente de culoare ale unui JPEG (din marker-ul SOF).
     * @return array{0:int,1:int,2:int} [lățime, înălțime, componente]
     */
    private static function jpegInfo(string $b): array
    {
        $len = strlen($b);
        if ($len < 4 || substr($b, 0, 2) !== "\xFF\xD8") {
            throw new \RuntimeException('Semnătura nu este o imagine JPEG validă.');
        }
        $i = 2;
        while ($i + 9 < $len) {
            if ($b[$i] !== "\xFF") {
                $i++;
                continue;
            }
            $marker = ord($b[$i + 1]);
            // Markere fără segment de date.
            if ($marker === 0xD8 || $marker === 0xD9 || $marker === 0x01 || ($marker >= 0xD0 && $marker <= 0xD7)) {
                $i += 2;
                continue;
            }
            $segLen = (ord($b[$i + 2]) << 8) | ord($b[$i + 3]);
            // Markere SOF (fără DHT=C4, JPG=C8, DAC=CC).
            if ($marker >= 0xC0 && $marker <= 0xCF && $marker !== 0xC4 && $marker !== 0xC8 && $marker !== 0xCC) {
                $h = (ord($b[$i + 5]) << 8) | ord($b[$i + 6]);
                $w = (ord($b[$i + 7]) << 8) | ord($b[$i + 8]);
                $comp = ord($b[$i + 9]);
                return [$w, $h, $comp];
            }
            $i += 2 + $segLen;
        }
        throw new \RuntimeException('JPEG invalid: marker SOF negăsit.');
    }
}
