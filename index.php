<?php

$hostname = '{imap.gmx.net:993/imap/ssl}INBOX';
$username = 'dein@gmx.ch';
$password = 'deinpasswort';

// deine 2 Absender-Adressen
$myAddresses = [
    'adresse1@gmx.ch',
    'adresse2@gmx.ch'
];

$inbox = imap_open($hostname, $username, $password);

if (!$inbox) {
    die('IMAP Fehler: ' . imap_last_error());
}

// ALLE Mails holen
$emails = imap_search($inbox, 'ALL');

$subjects = [];

if ($emails) {
    foreach ($emails as $email_number) {
        $overview = imap_fetch_overview($inbox, $email_number, 0);

        if (!isset($overview[0])) continue;

        $mail = $overview[0];

        // FROM prüfen
        $from = strtolower($mail->from ?? '');

        foreach ($myAddresses as $addr) {
            if (strpos($from, strtolower($addr)) !== false) {

                $subject = $mail->subject ?? '(kein Betreff)';
                
                // optional: Encoding fix
                $subject = imap_utf8($subject);

                $subjects[] = $subject;

                break;
            }
        }
    }
}

// optional: sortieren
sort($subjects);

// Ausgabe
foreach ($subjects as $s) {
    echo $s . PHP_EOL;
}

// optional: in Datei speichern
file_put_contents('subjects.txt', implode(PHP_EOL, $subjects));

imap_close($inbox);