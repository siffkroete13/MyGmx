<?php
echo function_exists('imap_open') ? 'IMAP OK' : 'IMAP FEHLT';
exit;
// ----------------------
// ENV LOADER
// ----------------------
function loadEnv($path)
{
    if (!file_exists($path)) {
        die(".env Datei fehlt");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) continue;

        list($key, $value) = explode('=', $line, 2);
        $_ENV[$key] = $value;
    }
}

loadEnv(__DIR__ . '/.env');


// ----------------------
// CONFIG
// ----------------------
$hostname = '{' . $_ENV['IMAP_HOST'] . ':' . $_ENV['IMAP_PORT'] . '/imap/' . $_ENV['IMAP_ENCRYPTION'] . '}Sent';

$username = $_ENV['IMAP_USER'];
$password = $_ENV['IMAP_PASSWORD'];

// deine Absender-Adressen
$myAddresses = [
    'kk@sva-bl.ch',
    'info@sva-bl.ch'
];


// ----------------------
// IMAP CONNECT
// ----------------------
$inbox = imap_open($hostname, $username, $password);

if (!$inbox) {
    die('IMAP Fehler: ' . imap_last_error());
}


// ----------------------
// EMAILS HOLEN
// ----------------------
$emails = imap_search($inbox, 'ALL');

$subjects = [];

if ($emails) {

    foreach ($emails as $email_number) {

        $overview = imap_fetch_overview($inbox, $email_number, 0);

        if (!isset($overview[0])) continue;

        $mail = $overview[0];

        $from = strtolower($mail->from ?? '');

        foreach ($myAddresses as $addr) {

            if (strpos($from, strtolower($addr)) !== false) {

                $subject = $mail->subject ?? '(kein Betreff)';
                $subject = imap_utf8($subject);

                $subjects[] = $subject;

                break;
            }
        }
    }
}


// ----------------------
// SORT + DEDUPE
// ----------------------
$subjects = array_unique($subjects);
sort($subjects);


// ----------------------
// OUTPUT
// ----------------------
foreach ($subjects as $s) {
    echo $s . PHP_EOL;
}

// optional speichern
file_put_contents('subjects.txt', implode(PHP_EOL, $subjects));


// ----------------------
imap_close($inbox);