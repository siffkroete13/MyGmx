<?php

// ----------------------
// Nummern aus Text holen
// ----------------------
function extractNumbersFromString($text)
{
    $numbers = [];

    if (preg_match_all('/\d[\d\s]{4,}\d/', $text, $matches)) {
        foreach ($matches[0] as $raw) {
            $numbers[] = preg_replace('/\D/', '', $raw);
        }
    }

    return $numbers;
}


// ----------------------
// Hauptfunktion (Subject + Attachments)
// ----------------------
function extractNumbersFromMail($inbox, $email_number, $subject)
{
    $numbers = [];

    // 1. zuerst Betreff prüfen
    $numbers = extractNumbersFromString($subject);

    // 2. wenn KEINE Nummer → Anhänge prüfen
    if (empty($numbers)) {

        $structure = imap_fetchstructure($inbox, $email_number);

        if (!empty($structure->parts)) {

            foreach ($structure->parts as $part) {

                if (!empty($part->dparameters)) {

                    foreach ($part->dparameters as $param) {

                        if (strtolower($param->attribute) === 'filename') {

                            $filename = $param->value;

                            $numbers = array_merge(
                                $numbers,
                                extractNumbersFromString($filename)
                            );
                        }
                    }
                }
            }
        }
    }

    return array_unique($numbers);
}