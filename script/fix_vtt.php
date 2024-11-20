<?php
function addCarriageReturnBeforeNumbers($inputFile, $outputFile) {
    // Leggi il contenuto del file di input
    $content = file_get_contents($inputFile);
    
    // Esplodi il contenuto in righe
    $lines = explode("\n", $content);
    
    // Array per contenere le righe modificate
    $modifiedLines = [];
    
    // Itera attraverso le righe e aggiungi una linea vuota prima di ogni numero intero
    foreach ($lines as $index => $line) {
        if ($line === "WEBVTT") {
            $modifiedLines[] = $line;
            $modifiedLines[] = ""; // Aggiungi una linea vuota dopo "WEBVTT"
        } elseif (is_numeric(trim($line)) && ($index == 0 || !is_numeric(trim($lines[$index - 1])))) {
            $modifiedLines[] = ""; // Aggiungi una linea vuota prima di ogni numero intero
            $modifiedLines[] = $line;
        } else {
            $modifiedLines[] = $line;
        }
    }

    // Rimuovi eventuali doppie linee vuote
    $finalLines = [];
    $previousLineEmpty = false;

    foreach ($modifiedLines as $line) {
        if (trim($line) === "") {
            if (!$previousLineEmpty) {
                $finalLines[] = $line;
                $previousLineEmpty = true;
            }
        } else {
            $finalLines[] = $line;
            $previousLineEmpty = false;
        }
    }

    // Sostituisci le righe modificate con quelle finali
    $modifiedLines = $finalLines;
    
    // Unisci le righe modificate in una stringa
    $modifiedContent = implode("\n", $modifiedLines);
    
    // Scrivi il contenuto modificato nel file di output
    file_put_contents($outputFile, $modifiedContent);
}

// Specifica il percorso del file di input e di output
$inputFile = '../storage/subtitiles_demo_673dec0a7279b_en_subtitles.vtt';
$outputFile = 'modified_subtitles.vtt';

// Chiama la funzione per modificare il file
addCarriageReturnBeforeNumbers($inputFile, $outputFile);

echo "Il file è stato modificato e salvato come $outputFile\n";
?>