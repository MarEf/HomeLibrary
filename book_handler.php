<?php
# API for book handling
include_once 'connect.php';
if (!isset($_SESSION)) {
    session_start();
}

check_data_format();
$book_id = "";

switch (true) {
    case isset($_POST['add_book']):
        echo "Lisää kirja<br>";
        add_book();
        header('Location: ' . 'isbn_lookup.php');
        die();
        break;
    case isset($_POST['collect_book']):
        echo "Lisää kokoelmaan<br>";
        collect_book($_POST['book_id']); # Currently doesn't work due to database error.
        header('Location: ' . 'users/my_books.php');
        die();
        break;
    case isset($_POST['add_and_collect']):
        echo "Luo ja lisää kokoelmaan<br>";
        add_book();
        collect_book($book_id);
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
    $author_pattern = "^[^,]+$|^$";
    $isbn10_pattern = "^(?:\D*\d){10}$|^(?:\D*\d){9}[\d\-\s]*[xX]$";
    $isbn13_pattern = "^(?:\D*\d){13}[\d\-\s]*$";

    # On error, reroute to book.php

    #ISBN-10 is of the right format
    if ($_POST['isbn10']) {
        preg_match("/$isbn10_pattern/", $_POST['isbn10'], $isbn10_match, PREG_UNMATCHED_AS_NULL);
        if (!$isbn10_match) {
            echo "<form id='alert' method='POST' action='book.php'>
                    <input type='hidden' name='alert' value='ISBN-10-numeron tulee olla oikeassa formaatissa.<br>Sallittuja formaatteja ovat:<br> - 10 numeroa<br> - 9 numeroa ja X-kirjain'>
                  </form>
                  <script>
                    document.querySelector('#alert').submit();
                  </script>";
            die();
        }
    }

    # ISBN-13 is of the right format
    if ($_POST['isbn13']) {
        preg_match("/$isbn13_pattern/", $_POST['isbn13'], $isbn13_match, PREG_UNMATCHED_AS_NULL);
        if (!$isbn13_match) {
            echo "<form id='alert' method='POST' action='book.php'>
                    <input type='hidden' name='alert' value='ISBN-13-numeron tulee sisältää 13 numeroa'>
                  </form>
                  <script>
                    document.querySelector('#alert').submit();
                  </script>";
            die();
        }
    }

    # Title does not exist when it should
    if (isset($_POST['add_book']) || isset($_POST['update_book'])) {
        if (!isset($_POST['title'])) {
            echo "<form id='alert' method='POST' action='book.php'>
                    <input type='hidden' name='alert' value='Otsikko puuttuu'>
                      </form>
                  <script>
                    document.querySelector('#alert').submit();
                  </script>";
            die();
        }
        # Author is of the right format
        if (isset($_POST['author'])) {
            $authors = $_POST['author'];
            foreach ($authors as $author) {
                preg_match("/$author_pattern/", $author, $author_match, PREG_UNMATCHED_AS_NULL);
                if (!$author_match) {
                    echo "<form id='alert' method='POST' action='book.php'>
                            <input type='hidden' name='alert' value='Kirjailijan nimi ei saa sisältää pilkkua (,)'>
                          </form>
                          <script>
                            document.querySelector('#alert').submit();
                          </script>";
                    die();
                }
            }
        } else {
            # At least one author must exist.
            echo "<form id='alert' method='POST' action='book.php'>
                    <input type='hidden' name='alert' value='Kirjailija puuttuu'>
                  </form>
                  <script>
                    document.querySelector('#alert').submit();
                  </script>";
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
                    echo "<form id='alert' method='POST' action='book.php'>
                            <input type='hidden' name='alert' value='Kieltä ei ole valittu tai valittua kieltä ei ole järjestelmässä.'>
                          </form>
                          <script>
                            document.querySelector('#alert').submit();
                          </script>";
                    die();
                }
            } catch (Throwable $e) {
                # If language_id is not int, binding parameters will fail and lead us here
                echo "<form id='alert' method='POST' action='book.php'>
                        <input type='hidden' name='alert' value='Kieli on väärässä formaatissa. En tiedä, miten onnistuit tässä...'>
                      </form>
                      <script>
                        document.querySelector('#alert').submit();
                      </script>";
                die();
            }
        } else {
            echo "<form id='alert' method='POST' action='book.php'>
                    <input type='hidden' name='alert' value='Kieltä ei ole valittu tai valittua kieltä ei ole järjestelmässä.'>
                  </form>
                  <script>
                    document.querySelector('#alert').submit();
                  </script>";
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

    // Set empty ISBN-values to NULL to avoid problems with unique empty strings
    if ($isbn10 == "") {
        $isbn10 = NULL;
    } else if ($isbn13 == "") {
        $isbn13 = NULL;
    }

    try {
        $add_book = $yhteys->prepare($query);
        $add_book->bind_param("ssisss", $_POST['title'], $_POST['cover'], $_POST['language_id'], $isbn10, $isbn13, $_POST['blurb']);
        $add_book->execute();
    } catch (Throwable $e) {
        echo "Kirjan lisääminen ei onnistunut.<br>";
        echo $e;

        echo "<form id='alert' method='POST' action='book.php'>
                <input type='hidden' name='alert' value='Kirjan lisääminen ei onnistunut. Yritä hetken kuluttua uudelleen.<br>Mikäli ongelma jatkuu, ota yhteyttä ylläpitoon.'>
              </form>
              <script>
                document.querySelector('#alert').submit();
              </script>";
        die();
    }

    // Fetch last altered autoincrement value.
    global $book_id;
    $book_id = $add_book->insert_id;

    if (isset($_POST['author'])) {
        add_authors($book_id);
    }
}

function add_authors($book_id)
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
        $bind->bind_param("ii", $book_id, $author_id[0]);
        $bind->execute();
        echo "Added author to book.<br>";
    }
}


function collect_book($book_id)
{
    global $yhteys;
    $owned = 1;
    $query = "INSERT INTO book_user (book_id, user_id, owned)
              VALUES (?, ?, ?)";

    try {
        $collect_book = $yhteys->prepare($query);
        $collect_book->bind_param("iii", $book_id, $_SESSION['user_id'], $owned);
        $collect_book->execute();
    } catch (Throwable $e) {
        echo "Book ID: $book_id<br>
              User ID: {$_SESSION['user_id']}<br>";
        echo "Failed to add book to collection.<br>$e";
        echo "<form id='alert' method='POST' action='book.php'>
                <input type='hidden' name='alert' value='Kirjan lisääminen kokoelmaan ei onnistunut. Kokeile myöhemmin uudelleen. Jos ongelma jatkuu, ota yhteyttä ylläpitoon.'>
              </form>
              <script>
                document.querySelector('#alert').submit();
              </script>";
        die();
    }
}

function remove_from_collection()
{
    global $yhteys;
}

function borrow_book()
{
    global $yhteys;
    // TO BE IMPLEMENTED
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

        // Why does this work?
        if (isset($_POST['author'])) {
            add_authors($_POST['book_id']);
        }
    } catch (Throwable $e) {
        echo "Päivitys epäonnistui: " . $e;
        echo "<form id='alert' method='POST' action='book.php'>
                <input type='hidden' name='alert' value='Kirjan tietojen päivitys. Yritä hetken kuluttua uudelleen.\nMikäli ongelma jatkuu, ota yhteyttä ylläpitoon.'>
              </form>
              <script>
                document.querySelector('#alert').submit();
              </script>";
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
        echo "<form id='alert' method='POST' action='book.php'>
                <input type='hidden' name='alert' value='Kirjan poistaminen tietokannasta ei onnistunut. Yritä hetken kuluttua uudelleen.\nMikäli ongelma jatkuu, ota yhteyttä ylläpitoon.'>
              </form>
              <script>
                document.querySelector('#alert').submit();
              </script>";
    }
}

echo "En tiedä, miten onnistuit tässä, mutta saavutit kirjakäsittelijän koodin lopun. Sinun ei pitäisi olla täällä.<br>";
