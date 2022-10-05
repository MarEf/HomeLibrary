<?php
$isbn10_pattern = "^(?:\D*\d){10}$|^(?:\D*\d){9}[\d\-\s]*[xX]$";
$isbn13_pattern = "^(?:\D*\d){13}[\d\-\s]*$";

# Set all variables to empty string.
$book_id = "";
$title = "";
$authors = "";
$cover = "";
$isbn_10 = "";
$isbn_13 = "";

# Set variables from POST.

if (isset($_POST['isbn_result'])) {
    $book_id = $_POST['book_id'];
    $title = $_POST['title'];
    $authors = $_POST['authors'];
    $cover = $_POST['cover'];
    $isbn10 = $_POST['isbn10'];
    $isbn13 = $_POST['isbn13'];
}

#This page does not function correctly yet.
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Document</title>
</head>

<body>
    <?php include "header.html" ?>
    <div id="content">

        <form action="books.php" method="POST">
            <label for="title">Otsikko
                <input type="text" name="title" id="title" value="<?php echo $title ?>" required>
            </label>
            <label for="authors">Kirjailija(t)
                <input type="text" name="authors" id="authors" value="<?php echo $authors ?>" required>
            </label>
            <label for="language">Kieli
                <select name="language" id="language">
                    <option value="">Valitse kieli</option>
                    <option value="1">Englanti</option>
                    <option value="2">Suomi</option>
                    <option value="3">Japani</option>
                    <option value="4">Venäjä</option>
                </select>
            </label>
            <label for="cover">Linkki kansikuvaan:
                <input type="text" name="cover" id="cover" value="<?php echo $cover ?>">
            </label>
            <label for="isbn10">ISBN-10
                <input type="text" name="isbn10" id="isbn10" pattern=<?php echo $isbn10_pattern ?> value="<?php echo $isbn10 ?>">
            </label>
            <label for="isbn13">ISBN-13
                <input type="text" name="isbn13" id="isbn13" pattern=<?php echo $isbn13_pattern ?> value="<?php echo $isbn13 ?>">
            </label>
            <label for="blurb">Kuvaus/Takakansiteksti
                <textarea name="blurb" id="blurb" cols="30" rows="10"></textarea>
            </label>
            <?php
            if ($_POST['source'] != "Local") {
                echo "
                <input type='hidden' name='book_id' value=$book_id>
                <input type='submit' name='add_book' value='Lisää uusi kirja'>
                <input type='submit' name='add_and_collect' value='Lisää uusi kirja ja lisää se kokoelmaan'>";
            } else {
                echo '<input type="submit" name="collect" value="Lisää kokoelmaan">';
                echo '<input type="submit" name="update_book" value="Päivitä kirja">';
                echo '<input type="submit" name="delete_book" value="Poista kirja">';
            }

            ?>
        </form>

    </div>
    <?php include "footer.html" ?>
</body>

</html>