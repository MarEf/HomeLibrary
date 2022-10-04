<?php
$isbn10_pattern = "^(?:\D*\d){10}$|^(?:\D*\d){9}[\d\-\s]*[xX]$";
$isbn13_pattern = "^(?:\D*\d){13}[\d\-\s]*$";

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

        <form action="" method="POST">
            <label for="title">Otsikko
                <input type="text" name="title" id="title" value="<?php echo $_POST['title'] ?>" required>
            </label>
            <label for="authors">Kirjailija(t)
                <input type="text" name="authors" id="authors" value="<?php echo $_POST['authors'] ?>" required>
            </label>
            <label for="language">Kieli
                <select name="language" id="language">
                    <option value="">Valitse kieli</option>
                    <option value="">Englanti</option>
                    <option value="">Suomi</option>
                    <option value="">Japani</option>
                    <option value="">Venäjä</option>
                </select>
            </label>
            <label for="cover">Linkki kansikuvaan:
                <input type="text" name="cover" id="cover" value="<?php echo $_POST['cover'] ?>">
            </label>
            <label for="isbn10">ISBN-10
                <input type="text" name="isbn10" id="isbn10" pattern=<?php echo $isbn10_pattern ?> value="<?php echo $_POST['isbn10'] ?>">
            </label>
            <label for="isbn13">ISBN-13
                <input type="text" name="isbn13" id="isbn13" pattern=<?php echo $isbn13_pattern ?> value="<?php echo $_POST['isbn13'] ?>">
            </label>
            <label for="blurb">Kuvaus/Takakansiteksti
                <textarea name="blurb" id="blurb" cols="30" rows="10"></textarea>
            </label>
            <?php
            if ($_POST['source'] != "Local") {
                echo "
                <input type='hidden' name='book_id' value=$book_id>
                <input type='button' name='add_book' value='Lisää uusi kirja'>";
            } else {
                echo '<input type="button" name="update_book" value="Päivitä kirja">';
            }

            ?>
        </form>

    </div>
    <?php include "footer.html" ?>
</body>

</html>