<?php
include_once 'connect.php';
$author_pattern = "^[^,]+$";

if (isset($_POST['name'])) {
}

switch (true) {
    case isset($_POST['edit_author']);
        $query = "UPDATE authors 
                  SET name = ?
                  WHERE author_id = ?";
        try {
            $delete = $yhteys->prepare($query);
            $delete->bind_param("si", $_POST['name'], $_POST['author_id']);
            $delete->execute();
            $yhteys->close();
        } catch (Throwable $t) {
            echo "Kirjailijan muokkaus epäonnistui.";
        }
        header('Location: ' . 'authors.php');
        die();
        break;

    case isset($_POST['delete_author']):
        global $yhteys;
        $query = "DELETE FROM authors 
                  WHERE author_id = ?";
        try {
            $delete = $yhteys->prepare($query);
            $delete->bind_param("i", $_POST['author_id']);
            $delete->execute();
            $yhteys->close();
        } catch (Throwable $e) {
            echo "Kirjailijan poisto epäonnistui: " . $e;
        }
        die();
        break;
}
