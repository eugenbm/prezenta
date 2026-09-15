<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Client SMTP minimal (fără dependențe externe), suficient pentru trimiterea
 * emailurilor tranzacționale prin contul dedicat de pe cPanel.
 * Suportă SMTPS (port 465, encryption='ssl') și STARTTLS (port 587, 'tls').
 */
final class Mailer
{
    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody): void
    {
        $cfg = (array) config('mail', []);
        $host = (string) ($cfg['host'] ?? '');
        $port = (int) ($cfg['port'] ?? 465);
        $encryption = (string) ($cfg['encryption'] ?? 'ssl');
        $username = (string) ($cfg['username'] ?? '');
        $password = (string) ($cfg['password'] ?? '');
        $fromEmail = (string) ($cfg['from_email'] ?? $username);
        $fromName = (string) ($cfg['from_name'] ?? '');

        if ($host === '' || $username === '' || $password === '') {
            throw new RuntimeException('Configurarea email (mail.host / username / password) este incompletă.');
        }

        $transport = ($encryption === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ]);

        $errno = 0;
        $errstr = '';
        $fp = @stream_socket_client($transport, $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $context);
        if (!$fp) {
            throw new RuntimeException("Nu s-a putut conecta la serverul SMTP {$host}:{$port} ({$errstr}).");
        }
        stream_set_timeout($fp, 20);

        try {
            self::expect($fp, [220]);
            $ehloHost = $_SERVER['SERVER_NAME'] ?? 'localhost';
            self::command($fp, 'EHLO ' . $ehloHost, [250]);

            if ($encryption === 'tls') {
                self::command($fp, 'STARTTLS', [220]);
                if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT)) {
                    throw new RuntimeException('Activarea criptării STARTTLS a eșuat.');
                }
                self::command($fp, 'EHLO ' . $ehloHost, [250]);
            }

            self::command($fp, 'AUTH LOGIN', [334]);
            self::command($fp, base64_encode($username), [334]);
            self::command($fp, base64_encode($password), [235]);

            self::command($fp, 'MAIL FROM:<' . $fromEmail . '>', [250]);
            self::command($fp, 'RCPT TO:<' . $toEmail . '>', [250, 251]);
            self::command($fp, 'DATA', [354]);

            $message = self::buildMessage($fromEmail, $fromName, $toEmail, $toName, $subject, $htmlBody);
            fwrite($fp, $message . "\r\n.\r\n");
            self::expect($fp, [250]);

            self::command($fp, 'QUIT', [221]);
        } finally {
            fclose($fp);
        }
    }

    /** @param resource $fp */
    private static function command($fp, string $cmd, array $expectedCodes): void
    {
        fwrite($fp, $cmd . "\r\n");
        self::expect($fp, $expectedCodes);
    }

    /** @param resource $fp */
    private static function expect($fp, array $expectedCodes): void
    {
        $response = self::readResponse($fp);
        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $expectedCodes, true)) {
            throw new RuntimeException('Răspuns SMTP neașteptat: ' . trim($response));
        }
    }

    /** @param resource $fp */
    private static function readResponse($fp): string
    {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            // Într-un răspuns multi-linie caracterul 4 este „-”; „ ” marchează ultima linie.
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        if ($data === '') {
            throw new RuntimeException('Conexiune SMTP întreruptă (niciun răspuns).');
        }
        return $data;
    }

    private static function buildMessage(string $fromEmail, string $fromName, string $toEmail, string $toName, string $subject, string $htmlBody): string
    {
        $domain = substr(strrchr($fromEmail, '@') ?: '@localhost', 1);
        $messageId = '<' . bin2hex(random_bytes(16)) . '@' . $domain . '>';

        $headers = [
            'From: ' . self::formatAddress($fromEmail, $fromName),
            'To: ' . self::formatAddress($toEmail, $toName),
            'Reply-To: ' . self::formatAddress($fromEmail, $fromName),
            'Subject: ' . self::encodeHeader($subject),
            'Message-ID: ' . $messageId,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            'Date: ' . date(DATE_RFC2822),
        ];

        // base64 elimină riscul liniilor care încep cu „.” (dot-stuffing) și al lungimii.
        $body = rtrim(chunk_split(base64_encode($htmlBody), 76, "\r\n"));

        return implode("\r\n", $headers) . "\r\n\r\n" . $body;
    }

    private static function formatAddress(string $email, string $name): string
    {
        return $name !== '' ? self::encodeHeader($name) . ' <' . $email . '>' : '<' . $email . '>';
    }

    private static function encodeHeader(string $value): string
    {
        if (preg_match('/[^\x20-\x7e]/', $value)) {
            return '=?UTF-8?B?' . base64_encode($value) . '?=';
        }
        return $value;
    }
}
