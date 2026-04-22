<?php

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
$hostname = '{' . $_ENV['IMAP_HOST'] . ':' . $_ENV['IMAP_PORT'] . '/imap/' . $_ENV['IMAP_ENCRYPTION'] . '}Gesendet';

$username = $_ENV['IMAP_USER'];
$password = $_ENV['IMAP_PASSWORD'];

$myAddresses = [
    'kk@sva-bl.ch',
    'info@sva-bl.ch'
];

$sinceDate = '1-Jan-2024';


// ----------------------
// subjects.txt vorbereiten
// ----------------------
$subjectsFile = __DIR__ . '/subjects.txt';

if (!file_exists($subjectsFile)) {
    file_put_contents($subjectsFile, '');
}


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
$emails = imap_search($inbox, 'SINCE "' . $sinceDate . '"');

$subjects = [];

if ($emails) {

    rsort($emails);

    // LIMIT (wichtig)
    $emails = array_slice($emails, 0, 300);

    foreach ($emails as $email_number) {

        $overview = imap_fetch_overview($inbox, $email_number, 0);

        if (!isset($overview[0])) continue;

        $mail = $overview[0];

        $to = strtolower($mail->to ?? '');

        foreach ($myAddresses as $addr) {

            if (strpos($to, strtolower($addr)) !== false) {

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
// SPEICHERN (DER EINZIGE ZWECK!)
// ----------------------
file_put_contents($subjectsFile, implode(PHP_EOL, $subjects));


// ----------------------
// OUTPUT
// ----------------------
echo "<pre>";
echo "Subjects gespeichert: " . count($subjects) . "\n";
echo "Datei: " . $subjectsFile . "\n";

imap_close($inbox);


echo '<p>';
    echo '<a href="extract_abbrechnungs_nr.php" target="_blank">Abbrechnungs-Nummern extrahieren aus E-Mail Subject</a>';
echo '</p>';


