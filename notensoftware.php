<?php

/*
@author Leandro <leandro.garcia@bluewin.ch>
@copyright 2024 Leandro Garcia
@version 1.0.0
@license OpenGL
*/

// Pfad für die Notendatei
$notenDatei = "noten.txt";

// Funktionen für Dateioperationen
function notenVerwalten(string $fach, float $note = null, string $aktion = "hinzufuegen"): string
{
    global $notenDatei;
    $noten = file_exists($notenDatei) ? file($notenDatei, FILE_IGNORE_NEW_LINES) : [];

    foreach ($noten as &$eintrag) {
        list($aktuellesFach, $aktuelleNote) = explode(": ", $eintrag);
        if (strtolower(trim($aktuellesFach)) == strtolower(trim($fach))) {
            if ($aktion == "bearbeiten") {
                $eintrag = "$fach: $note";
                file_put_contents($notenDatei, implode("\n", $noten) . "\n");
                return "Die Note für $fach wurde erfolgreich bearbeitet.";
            } elseif ($aktion == "entfernen") {
                $noten = array_diff($noten, [$eintrag]);
                file_put_contents($notenDatei, implode("\n", $noten) . "\n");
                return "Die Note für $fach wurde erfolgreich entfernt.";
            } else {
                return "Eintrag bereits vorhanden.";
            }
        }
    }

    if ($aktion == "hinzufuegen") {
        file_put_contents($notenDatei, "$fach: $note\n", FILE_APPEND);
        return "Note für $fach erfolgreich gespeichert!";
    }
    return "Das Fach '$fach' wurde nicht gefunden.";
}

// Noten anzeigen
function notenAnzeigen(): void
{
    global $notenDatei;
    if (file_exists($notenDatei)) {
        echo "Deine Noten:\n" . implode("\n", file($notenDatei, FILE_IGNORE_NEW_LINES)) . "\n";
    } else {
        echo "Noch keine Noten gespeichert.\n";
    }
}

// Eingabeprüfung
function noteValidieren($eingabe): bool
{
    return preg_match("/^[1-6](\.\d+)?$/", $eingabe);
}

// Durchschnittsberechnung
function durchschnittBerechnen(): void
{
    global $notenDatei;
    if (!file_exists($notenDatei)) {
        echo "Noch keine Noten gespeichert.\n";
        return;
    }
    $noten = ["modul" => [], "fach" => []];
    foreach (file($notenDatei, FILE_IGNORE_NEW_LINES) as $eintrag) {
        list($fach, $note) = explode(": ", $eintrag);
        $noten[str_contains(strtolower($fach), "modul") ? "modul" : "fach"][] = (float)$note;
    }

    foreach (["modul" => "Module", "fach" => "Fächer"] as $typ => $label) {
        echo count($noten[$typ]) > 0
            ? "Durchschnitt für $label: " . round(array_sum($noten[$typ]) / count($noten[$typ]), 2) . "\n"
            : "Keine Noten für $label vorhanden.\n";
    }
}

// Noten Management
$fortfahren = true;
while ($fortfahren) {
    echo "\nWas möchtest du tun?\n1 - Neue Note hinzufügen\n2 - Alle Noten anzeigen\n3 - Note bearbeiten\n4 - Note entfernen\n5 - Beenden und Durchschnitt berechnen\n";
    $auswahl = readline("Deine Wahl: ");

    if (in_array($auswahl, [1, 3, 4])) {
        $fach = readline("Gib das Fach oder Modul ein: ");
        if ($auswahl == 1 || $auswahl == 3) {
            do {
                $notenEingabe = readline("Gib die Note ein (1.0 - 6.0): ");
                $korrekteNote = noteValidieren($notenEingabe);
                if (!$korrekteNote) {
                    echo "Ungültige Eingabe.\n";
                }
            } while (!$korrekteNote);
            echo notenVerwalten($fach, (float)$notenEingabe, $auswahl == 1 ? "hinzufuegen" : "bearbeiten") . "\n";
        } else {
            echo notenVerwalten($fach, null, "entfernen") . "\n";
        }
    } elseif ($auswahl == 2) {
        notenAnzeigen();
    } elseif ($auswahl == 5) {
        durchschnittBerechnen();
        $fortfahren = false;
    } else {
        echo "Ungültige Auswahl.\n";
    }
}
