<?php
$isbn_pattern = "^(?=(?:\D*\d){10}(?:(?:\D*\d){3})?$)[\d-\s]+$";
$api_url = "https://openlibrary.org/isbn/";

if (isset($_POST["search"])) {
    /*
    Book information we are interested in:
        * Title
        * Author(s)
        * Cover ("https://covers.openlibrary.org/b/isbn/". $isbn ."-L.jpg" />)
        * ISBN-10
        * ISBN-13
    */

    # Remove non-numbers from search
    $isbn = preg_replace("/\D|_/", '', $_POST["isbn"]);
    # Check local database for existing entry

    # If fail, search OpenLibrary
    $url = $api_url . $isbn . ".json";
    $book = @file_get_contents($url); #yök

    if ($book !== false) {
        # Handle book data

    } else {
        #Display error: "No book matching the given ISBN was found. Please check your input or add the book to your collection manually.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Library</title>
</head>

<body>

    <?php include "header.html" ?>

    <form action="" method="POST">
        <label for="isbn">ISBN
            <input name="isbn" id="isbn" type="text" pattern=<?php echo $isbn_pattern ?> placeholder="9781234567890">
        </label>
        <input type="submit" name="search" value="Search">
    </form>

    <?php include "footer.html" ?>

</body>

</html>