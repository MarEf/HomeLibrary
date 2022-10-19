<?php

include_once 'connect.php';
if (!isset($_SESSION)) {
    session_start();
}

$isbn10_pattern = "^(?:\D*\d){10}$|^(?:\D*\d){9}[\d\-\s]*[xX]$";
$isbn13_pattern = "^(?:\D*\d){13}[\d\-\s]*$";

# Commas disallowed from author fields, because it breaks everything
$author_pattern = "^[^,]+$";

# Set all variables to empty string.
$book_id = "";
$title = "";
$authors = [];
$cover = "";
$isbn10 = "";
$isbn13 = "";
$source = "none";
$blurb = "";

# Set variables from POST.
if (isset($_POST['from_post'])) {
    $book_id = $_POST['book_id'];
    $title = $_POST['title'];
    $authors = explode(", ", $_POST['authors']);
    $cover = $_POST['cover'];
    $isbn10 = $_POST['isbn10'];
    $isbn13 = $_POST['isbn13'];
    $source = $_POST['source'];
    if (isset($_POST['blurb'])) {
        $blurb = $_POST['blurb'];
    }
    if (isset($_POST['language_id'])) {
        $default_language = $_POST['language_id'];
    }
}




function list_languages()
{
    global $default_language;
    global $yhteys;

    # Fetch languages from database
    $query = "SELECT language_id, name_fin FROM languages";
    $languages = $yhteys->query($query);

    while ($row = $languages->fetch_assoc()) {
        if ($row['language_id'] === $default_language) {
            echo "<option selected value='{$row["language_id"]}'>{$row['name_fin']}</option>";
        } else {
            echo "<option value='{$row["language_id"]}'>{$row['name_fin']}</option>";
        }
    }
}

function get_authors()
{
    global $yhteys;

    $query = "SELECT * FROM authors";
    $authors = $yhteys->query($query);

    while ($row = $authors->fetch_assoc()) {
        echo "<option value='{$row['name']}'></option>";
    }
}

function prefill_authors()
{
    global $authors;
    global $author_pattern;
    $author_count = 1;
    $enabled = "";

    if (!isset($_SESSION['loggedin'])) {
        $enabled = "disabled";
    }

    if (isset($_POST['authors'])) {

        foreach ($authors as $author) {
            if ($author_count == 1) {
                echo "<span class='author' id='author-block1'>
                        <input list='authors' name='author[]' id='author1' pattern='$author_pattern' value='$author' required $enabled>
                        <i class='far fa-minus-square remove inactive'></i>
                      </span>";
            } else {
                echo "<span class='author' id='author-block$author_count'>
                        <input list='authors' name='author[]' id='author$author_count' pattern='$author_pattern' value='$author' required $enabled>
                        <i class='far fa-minus-square remove'></i>
                      </span>";
            }
            $author_count++;
        }
    } else {
        echo "<span class='author' id='author-block1'>
            <input list='authors' name='author[]' id='author1' pattern='$author_pattern' required $enabled>
            <i class='far fa-minus-square remove inactive'></i>
          </span>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="script.js"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <title>Book</title>
</head>

<body>
    <?php include "header.php" ?>
    <div id="content">
        <?php
        if ($source == 'Local') {
            echo '<h2>Muokkaa kirjan tietoja</h2>';
        } else {
            echo '<h2>Uusi kirja</h2>';
        }

        ?>

        <form action="book_handler.php" method="POST">
            <label for="title">Otsikko
                <input type="text" name="title" id="title" value="<?php echo $title ?>" <?php echo (!isset($_SESSION['loggedin'])) ? "disabled" : ''; ?> required>
            </label>
            <label for="author" id="author-list">Kirjailija(t)
                <?php prefill_authors() ?>
                <?php if (isset($_SESSION['loggedin'])) {
                    echo "<i class='far fa-plus-square add' onclick='addAuthorField()'></i>";
                }
                ?>
            </label>
            <datalist id="authors">
                <?php get_authors() ?>
            </datalist>
            <label for="language_id">Kieli
                <select name="language_id" id="language_id" <?php echo (!isset($_SESSION['loggedin'])) ? "disabled" : ''; ?> required>
                    <option value="">Valitse kieli</option>
                    <?php list_languages() ?>
                </select>
            </label>
            <label for="cover">Linkki kansikuvaan (vapaaehtoinen)
                <input type="text" name="cover" id="cover" value="<?php echo $cover ?>" <?php echo (!isset($_SESSION['loggedin'])) ? "disabled" : ''; ?>>
            </label>
            <label for="isbn10">ISBN-10 (vapaaehtoinen)
                <input type="text" name="isbn10" id="isbn10" pattern=<?php echo $isbn10_pattern ?> value="<?php echo $isbn10 ?>" <?php echo (!isset($_SESSION['loggedin'])) ? "disabled" : ''; ?>>
            </label>
            <label for="isbn13">ISBN-13 (vapaaehtoinen)
                <input type="text" name="isbn13" id="isbn13" pattern=<?php echo $isbn13_pattern ?> value="<?php echo $isbn13 ?>" <?php echo (!isset($_SESSION['loggedin'])) ? "disabled" : ''; ?>>
            </label>
            <label for="blurb">Kuvaus/Takakansiteksti (vapaaehtoinen)
                <textarea name="blurb" id="blurb" cols="30" rows="10" <?php echo (!isset($_SESSION['loggedin'])) ? "disabled" : ''; ?>><?php echo $blurb ?></textarea>
            </label>
            <?php
            if (isset($_SESSION['loggedin'])) {
                if ($source != "Local") {
                    echo "
                <input type='hidden' name='book_id' value=$book_id>
                <input type='submit' name='add_book' value='Lisää uusi kirja'>
                <input type='submit' name='add_and_collect' value='Lisää uusi kirja ja lisää se kokoelmaan'>";
                } else {
                    echo "<input type='hidden' name='book_id' value=$book_id>";
                    echo '<input type="submit" name="collect_book" value="Lisää kokoelmaan">';
                    echo '<input type="submit" name="update_book" value="Päivitä kirja">';
                    echo '<input type="submit" name="delete_book" value="Poista kirja">';
                }
            } else {
                echo "<p>Kirjaudu sisään muokataksesi kirjan tietoja.</p>";
            }
            ?>
        </form>

    </div>
    <?php include "footer.html" ?>
</body>

</html>