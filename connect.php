<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/tunnukset.php';

// luo yhteys
$yhteys = new mysqli($db_server, $db_username, $db_password, $db);

// jos yhteyden muodostaminen ei onnistunut, keskeytä
if ($yhteys->connect_error) {
    die("Yhteyden muodostaminen epäonnistui: " . $yhteys->connect_error);
}
// aseta merkistökoodaus (muuten ääkköset sekoavat)
$yhteys->set_charset("utf8");
