<?php
# API for book handling
include_once 'connect.php';

switch (true) {
    case isset($_POST['add_book']):
        echo "Lisää kirja<br>";
        add_book();
        break;
    case isset($_POST['collect_book']):
        echo "Lisää kokoelmaan<br>";
        collect_book();
        break;
    case isset($_POST['add_and_collect']):
        echo "Luo ja lisää kokoelmaan<br>";
        # add_book();
        # collect_book();
        break;
    case isset($_POST['update_book']):
        echo "Päivitä kirja<br>";
        update_book();
        break;
    case isset($_POST['delete_book']):
        echo "Poista kirja<br>";
        delete_book();
        break;
    default:
        echo "Olet joko nero tai kömpelö, koska yllä oli kaikki käyttötapaukset...<br>";
        break;
}


function add_book()
{
    global $yhteys;
    $query = "INSERT INTO books (title, cover, language_id, isbn_10, isbn_13, blurb)
              VALUES (?, ?, ?, ?, ?, ?)";
    $isbn10 = preg_replace("/\W|_/", '', $_POST['isbn10']);
    $isbn13 = preg_replace("/\W|_/", '', $_POST['isbn13']);

    try {
        $add_book = $yhteys->prepare($query);
        $add_book->bind_param("ssisss", $_POST['title'], $_POST['cover'], $_POST['language_id'], $isbn10, $isbn13, $_POST['blurb']);
        $add_book->execute();
    } catch (Throwable $e) {
        echo "Kirjan lisääminen ei onnistunut.<br>";
        echo $e;
    }

    var_dump($_POST['author']);

    if (isset($_POST['author'])) {
        add_authors();
    }

    header('Location: ' . 'isbn_lookup.php');
    die();
}

function add_authors()
{
    echo "Adding authors<br>";
    global $yhteys;
    $authors = $_POST['author'];

    // Prepare queries
    $query = "SELECT author_id FROM authors
              WHERE name = ?";
    $find_author = $yhteys->prepare($query);
    $query = "INSERT INTO authors (name)
              VALUES (?)";
    $create_author = $yhteys->prepare($query);
    $query = "INSERT INTO book_author (book_id, author_id)
                  VALUES (?, ?)";
    $bind = $yhteys->prepare($query);

    // Check if an author by name is already in the system.
    foreach ($authors as $author) {
        echo "Checking if an author by name $author exists<br>";
        $find_author->bind_param("s", $author);
        $find_author->execute();
        $result = $find_author->get_result();

        if ($result->num_rows === 0) {
            // If the author does not exist, create an entry.
            echo "Author not found<br>";
            $create_author->bind_param("s", $author);
            $create_author->execute();
            $find_author->execute();
            $result = $find_author->get_result();
            echo "Author added to database<br>";
        }

        $author_id = $result->fetch_row();
        echo "Author ID: $author_id[0]<br>";
        // Bind book to author
        echo "Adding author to book<br>";
        $bind->bind_param("ii", $_POST['book_id'], $author_id[0]);
        $bind->execute();
        echo "Added author to book.<br>";
    }
}


function collect_book()
{
    global $yhteys;
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

function borrow_book()
{
    global $yhteys;
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

function update_book()
{
    global $yhteys;
    $query = "UPDATE books 
              SET title = ?, cover = ?, language_id = ?, isbn_10 = ?, isbn_13 = ?, blurb = ?
              WHERE book_id = ?";
    $isbn10 = preg_replace("/\W|_/", '', $_POST['isbn10']);
    $isbn13 = preg_replace("/\W|_/", '', $_POST['isbn13']);

    try {
        $edit_book = $yhteys->prepare($query);
        $edit_book->bind_param("ssisssi", $_POST['title'], $_POST['cover'], $_POST['language_id'], $isbn10, $isbn13, $_POST['blurb'], $_POST['book_id']);
        $edit_book->execute();

        echo "Yritetään päivittää kirja {$_POST['title']}, jonka ID on {$_POST['book_id']}<br>";
        var_dump($_POST['author']);

        if (isset($_POST['author'])) {
            add_authors();
        }
        header('Location: ' . 'book_lookup.php');
        die();
    } catch (Throwable $e) {
        echo "Päivitys epäonnistui: " . $e;
    }
}

function delete_book()
{
    global $yhteys;
    $query = "DELETE FROM books 
              WHERE book_id = ?";
    try {
        $delete = $yhteys->prepare($query);
        $delete->bind_param("i", $_POST['book_id']);
        $delete->execute();
        $yhteys->close();
        header('Location: ' . 'book_lookup.php');
        die();
    } catch (Throwable $e) {
        echo "Kirjan poisto epäonnistui: " . $e;
    }
}

echo "En tiedä, miten onnistuit tässä, mutta saavutit kirjakäsittelijän koodin lopun. Sinun ei pitäisi olla täällä.<br>";
