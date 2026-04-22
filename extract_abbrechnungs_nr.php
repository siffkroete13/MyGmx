<?php

// ----------------------
// Datei laden
// ----------------------
function loadSubjects($filePath)
{
    if (!file_exists($filePath)) {
        die("subjects.txt nicht gefunden");
    }

    return file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}


// ----------------------
// Text normalisieren
// ----------------------
function normalizeText($text)
{
    $text = mb_strtolower($text);
    $text = str_replace(['ü','ü'], 'u', $text);

    return $text;
}


// ----------------------
// Prüfen ob Rückerstattung
// ----------------------
function isRueckerstattung($text)
{
    $normalized = normalizeText($text);
    return strpos($normalized, 'ruckerstattung') !== false;
}


// ----------------------
// Nummern extrahieren
// ----------------------
function extractNumbersFromText($text)
{
    $numbers = [];

    if (preg_match_all('/[\d][\d\s\']{4,}[\d]/', $text, $matches)) {

        foreach ($matches[0] as $raw) {

            $clean = preg_replace('/\D/', '', $raw);

            if (strlen($clean) >= 6) {
                $numbers[] = $clean;
            }
        }
    }

    return $numbers;
}


// ----------------------
// Hauptfunktion
// ----------------------
function extractAbrechnungsNummern($subjects)
{
    $results = [];

    foreach ($subjects as $subject) {

        if (!isRueckerstattung($subject)) {
            continue;
        }

        $numbers = extractNumbersFromText($subject);

        $results = array_merge($results, $numbers);
    }

    $results = array_unique($results);
    sort($results);

    return $results;
}


// ----------------------
// Ausgabe
// ----------------------
function outputNumbers($numbers)
{
    echo "<pre>";

    if (empty($numbers)) {
        echo "Keine Nummern gefunden\n";
        return;
    }

    foreach ($numbers as $n) {
        echo $n . "\n";
    }
}


// ----------------------
// Speichern
// ----------------------
function saveNumbers($filePath, $numbers)
{
    file_put_contents($filePath, implode(PHP_EOL, $numbers));
}


function saveNumbersAutoPath($numbers)
{
    // Lokal auch speichern


    $baseDir = 'C:/Users/Iwan/Documents/Buerokratie';
    $year = date('Y');

    $targetDir = $baseDir . '/' . $year . '/abrechnungs_nr';

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $filePath = $targetDir . '/abrechnungs_nr.txt';

    file_put_contents($filePath, implode(PHP_EOL, $numbers));

    return $filePath;
}

function saveLocal($numbers)
{
    // Lokal auch speichern
    $targetDir = __DIR__;

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $filePath = $targetDir . '/abrechnungs_nr.txt';

    file_put_contents($filePath, implode(PHP_EOL, $numbers));

    return $filePath;
}


// ----------------------
// MAIN
// ----------------------

$file = __DIR__ . '/subjects.txt';

$subjects = loadSubjects($file);

$numbers = extractAbrechnungsNummern($subjects);

outputNumbers($numbers);

$path = saveLocal($numbers);
echo "\nGespeichert unter:\n" . $path;

echo '<p>und</p>';

$path = saveNumbersAutoPath($numbers);
echo "\nGespeichert unter:\n" . $path;