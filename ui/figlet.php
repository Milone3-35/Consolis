<?php

class FIGLET 
{
    public static string $fontPath;

    public static function draw(string $text, int $row, int $column)
    {    
        // --- 1. Datei-Prüfung ---
        if (!file_exists(self::$fontPath)) {
            throw new Exception("ERROR: File doesn't exist.");
        }
        if (!is_readable(self::$fontPath)) {
            throw new Exception("ERROR: File isn't readable.");
        }

        // --- 2. Datei öffnen ---
        $fontFile = fopen(self::$fontPath, "r");
        if (!$fontFile) {
            throw new Exception("ERROR: File couldn't be opened");
        }

        // --- 3. Header lesen & Metadaten extrahieren ---
        $header = fgets($fontFile);
        $headerParts = explode(' ', rtrim($header));

        $hardBlank = $headerParts[0][-1]; // Letztes Zeichen vom Signature-String
        $height    = (int)$headerParts[1]; // Höhe jedes Zeichens
        $width     = (int)$headerParts[3]; // Breite (nicht immer exakt)
        $commentLines = (int)$headerParts[5]; // Anzahl Kommentarzeilen

        // --- 4. Kommentarzeilen überspringen ---
        for ($i = 0; $i < $commentLines; $i++) {
            fgets($fontFile);
        }

        // --- 5. Zeichen aus Datei einlesen ---
        // FIGlet Fonts speichern Zeichen meist ab ASCII 32 (Leerzeichen) bis ca. 126 (~)
        $chars = [];
        while (true) {
            $charBuf = [];
            for ($i = 0; $i < $height; $i++) {
                $line = fgets($fontFile);
                if ($line === false) {
                    break 2; // Ende der Datei -> raus aus beiden Schleifen
                }
                // Entfernt Zeilenumbrüche
                $line = rtrim($line, "\n\r");
                // Entfernt Endmarkierungen (@ oder $)
                $line = rtrim($line, '@');
                // Hardblanks ersetzen durch echte Leerzeichen
                $line = str_replace($hardBlank, ' ', $line);
                $charBuf[] = $line;
            }
            if (!empty($charBuf)) {
                $chars[] = $charBuf;
            }
        }
        fclose($fontFile);

        // --- 6. Text in FIGlet-Zeilen umwandeln ---
        // Wir brauchen pro Buchstaben den richtigen Index im $chars-Array
        $asciiStart = 32; // FIGlet beginnt meist bei Leerzeichen (ASCII 32)
        $encoding = mb_detect_encoding($text);
        $length = mb_strlen($text, $encoding);

        // Wir bauen die Ausgabe zeilenweise zusammen
        for ($line = 0; $line < $height; $line++) {
            // Cursor setzen vor jeder Zeile
            echo "\033[" . ($row + $line) . ";" . $column . "H";

            for ($i = 0; $i < $length; $i++) {
                $codePoint = mb_ord($text[$i], $encoding);
                $index = $codePoint - $asciiStart;

                if (!isset($chars[$index])) {
                    $index = 0;
                }

                echo $chars[$index][$line];
            }
        }
    }
}
