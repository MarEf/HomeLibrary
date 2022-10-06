<?php
# API for book handling
include_once 'connect.php';

# Add a book to the database
if (isset($_POST['add_book'])) {
    add_book($yhteys);
}

# Add a book to collection
if (isset($_POST['collect_book'])) {
    # collect_book($yhteys);
}

# Add a book to the database and to collection
if (isset($_POST['add_and_collect'])) {
    # add_book($yhteys);
    # collect_book($yhteys);
}

# Edit book
if (isset($_POST['edit_book'])) {
    update_book($yhteys);
}

# Delete book
if (isset($_POST['delete_book'])) {
    delete_book($yhteys);
}


function add_book($yhteys)
{
    $query = "INSERT INTO books (title, cover, language_id, isbn_10, isbn_13, blurb)
              VALUES (?, ?, ?, ?, ?, ?)";
    try {
        $add_book = $yhteys->prepare($query);
        $add_book->bind_param("ssisss", $_POST['title'], $_POST['cover'], $_POST['language_id'], $_POST['isbn10'], $_POST['isbn13'], $_POST['blurb']);
        $add_book->execute();
        $yhteys->close();
        header('Location: ' . 'isbn_lookup.php');
        die();
    } catch (Throwable $e) {
        echo "Kirjan lisääminen ei onnistunut.<br>";
        echo $e;
    }
}

function collect_book($yhteys)
{
    $query = "INSERT INTO book_user (book_id, user_id, owned)
              VALUES (?, ?, ?)";
    /*
    try {
    $collect_book = $yhteys->prepare($query);
    $collect_book->bind_param("iii", $_POST['book_id'], $_SESSION['user_id'], 1);
    $collect_book->execute();
    $yhteys->close();
    } catch (Throwable $e) {

    }
    */
}

function borrow_book($yhteys)
{
    $query = "INSERT INTO book_user (book_id, user_id, owned, borrowed_from, borrowed_to)
              VALUES (?, ?, ?, ?, ?)";
    /*
    try {
    $borrow_book = $yhteys->prepare($query);
    $borrow_book->bind_param("iiiii", $_POST['book_id'], $_POST['user_id'], 0, $_SESSION['user_id'], $_POST['user_id']);
    $borrow_book->execute();
    $yhteys->close();
    } catch (Throwable $e) {

    }
    */
}

function update_book($yhteys)
{
    $query = "UPDATE books
              SET title = ?, cover = ?, language_id = ?, isbn_10 = ?, isbn13 = ?, blurb = ?
              WHERE book_id = ?";
    try {
        $edit_book = $yhteys->prepare($query);
        $edit_book->bind_param("ssisssi", $_POST['title'], $_POST['cover'], $_POST['language_id'], $_POST['isbn10'], $_POST['isbn13'], $_POST['blurb'], $_POST['book_id']);
        $edit_book->execute();
        $yhteys->close();
    } catch (Throwable $e) {
    }
}

function delete_book($yhteys)
{
    $query = "DELETE FROM books
              WHERE book_id = ?";

    try {
        $delete = $yhteys->prepare($query);
        $delete->bind_params("i", $_POST['book_id']);
        $delete->execute();
        $yhteys->close();
    } catch (Throwable $e) {
    }
}
