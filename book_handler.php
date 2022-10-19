<?php
# API for book handling
include_once 'connect.php';
if (!isset($_SESSION)) {
    session_start();
}

check_data_format();

switch (true) {
    case isset($_POST['add_book']):
        echo "Lisää kirja<br>";
        add_book();
        header('Location: ' . 'isbn_lookup.php');
        die();
        break;
    case isset($_POST['collect_book']):
        echo "Lisää kokoelmaan<br>";
        collect_book();
        #header('Location: ' . 'users/my_books.php');
        die();
        break;
    case isset($_POST['add_and_collect']):
        echo "Luo ja lisää kokoelmaan<br>";
        add_book();
        collect_book();
        header('Location: ' . 'users/my_books.php');
        die();
        break;
    case isset($_POST['update_book']):
        echo "Päivitä kirja<br>";
        update_book();
        header('Location: ' . 'book_lookup.php');
        die();
        break;
    case isset($_POST['delete_book']):
        echo "Poista kirja<br>";
        delete_book();
        header('Location: ' . 'book_lookup.php');
        die();
        break;
    case isset($_POST['uncollect']):
        echo "Nothing here yet";
        break;
    default:
        echo "Olet joko nero tai kömpelö, koska yllä oli kaikki käyttötapaukset...<br>";
        break;
}

function check_data_format()
{
    $author_pattern = "^[^,]+$";
    $isbn10_pattern = "^(?:\D*\d){10}$|^(?:\D*\d){9}[\d\-\s]*[xX]$";
    $isbn13_pattern = "^(?:\D*\d){13}[\d\-\s]*$";

    # On error, reroute to book.php

    #ISBN-10 is of the right format
    if (isset($_POST['isbn10'])) {
        preg_match("/$isbn10_pattern/", $_POST['isbn10'], $isbn10_match, PREG_UNMATCHED_AS_NULL);
        if (!$isbn10_match) {
            header('Location: ' . 'book.php');
            die();
        }
    }

    # ISBN-13 is of the right format
    if (isset($POST['isbn13'])) {
        preg_match("/$isbn13_pattern/", $_POST['isbn13'], $isbn13_match, PREG_UNMATCHED_AS_NULL);
        if (!$isbn13_match) {
            header('Location: ' . 'book.php');
            die();
        }
    }

    # Title does not exist when it should
    if (isset($_POST['add_book']) || isset($_POST['update_book'])) {
        if (!isset($_POST['title'])) {
            header('Location: ' . 'book.php');
            die();
        }
        # Author is of the right format
        if (isset($_POST['author'])) {
            $authors = $_POST['author'];
            foreach ($authors as $author) {
                preg_match("/$author_pattern/", $author, $author_match, PREG_UNMATCHED_AS_NULL);
                if (!$author_match) {
                    header('Location: ' . 'book.php');
                    die();
                }
            }
        } else {
            # At least one author must exist.
            header('Location: ' . 'book.php');
            die();
        }


        # Language must be selected, be an integer, and exist in the system
        if (isset($_POST['language_id'])) {
            global $yhteys;
            $language = $_POST['language_id'];
            $query = "SELECT * FROM languages
                  WHERE language_id = ?";
            try {
                $check_language = $yhteys->prepare($query);
                $check_language->bind_param("i", $language);
                $check_language->execute();
                if (!$check_language->get_result()) {
                    header('Location: ' . 'book.php');
                    die();
                }
            } catch (Throwable $e) {
                # If language_id is not int, binding parameters will fail and lead us here
                header('Location: ' . 'book.php');
                die();
            }
        } else {
            header('Location: ' . 'book.php');
            die();
        }
    }
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

    if (isset($_POST['author'])) {
        add_authors();
    }
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
        // If author did not exist and was added manually, this line fails with an error:
        // Fatal error: Uncaught mysqli_sql_exception: Cannot add or update a child row: a foreign key constraint fails...
        // However, it sometimes works fine. I don't know how to fix this.
        $bind->execute();
        echo "Added author to book.<br>";
    }
}


function collect_book()
{
    global $yhteys;
    $query = "INSERT INTO book_user (book_id, user_id, owned)
              VALUES (?, ?, 1)";

    try {
        $collect_book = $yhteys->prepare($query);
        $collect_book->bind_param("ii", $_POST['book_id'], $_SESSION['user_id']);
        // Fatal error: Uncaught mysqli_sql_exception: Cannot add or update a child row: a foreign key constraint fails...
        $collect_book->execute();
        $yhteys->close();
    } catch (Throwable $e) {
        echo "Failed to add book to collection.<br>$e";
    }
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

        if (isset($_POST['author'])) {
            add_authors();
        }
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
    } catch (Throwable $e) {
        echo "Kirjan poisto epäonnistui: " . $e;
    }
}

echo "En tiedä, miten onnistuit tässä, mutta saavutit kirjakäsittelijän koodin lopun. Sinun ei pitäisi olla täällä.<br>";
