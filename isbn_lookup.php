<?php
include_once 'connect.php';

$isbn_pattern = "^(?=(?:\D*\d){10}(?:(?:\D*\d){3})?$)[\d\-\s]+$|(?:\D*\d){9}[\d\-\s]*[xX]$";
$api_url = "https://openlibrary.org/api/books?bibkeys=ISBN:";
$data_format = "&jscmd=data&format=json";
$books = "SELECT * FROM books
          LEFT JOIN book_author
            ON books.book_id = book_author.book_id
          LEFT JOIN authors
            ON book_author.author_id = authors.author_id
          WHERE isbn_10 = ? OR isbn_13 = ?";

function fetchTitle($book)
{
    $title = "";
    if (@$book['title']) {
        $title = $book['title'];
    }
    return $title;
}

function fetchAuthors($book)
{
    $authors = [];
    if (@$book['authors']) {
        $author_array = $book['authors'];
        foreach ($author_array as $key => $author) {
            array_push($authors, $author_array[$key]['name']);
        }
    }
    return implode("@£$", $authors); # If an author has this set of characters in their pen name, I deny responsibility.
}

function fetchCover($book)
{
    $cover = "";
    if (@$book['cover']) {
        $cover = end($book['cover']);
    }
    return $cover;
}

function fetchISBN($book, $isbn_nro)
{
    $isbn = "";
    if (@$book['identifiers']['isbn_' . $isbn_nro]) {
        $isbn = $book['identifiers']['isbn_' . $isbn_nro][0];
    }
    return $isbn;
}

if (isset($_POST["search"])) {
    /*
    Book information we are interested in:
        * Title
        * Author(s)
        * Cover
        * ISBN-10
        * ISBN-13

    Test cases:
        * All data available: 9789022543207
        * Missing ISBN-10: 9780316270366
        * Multiple authors: 9780316304788
        * Missing cover and author: 9784048936941
        * Missing author: 9784048663168
    */

    # Initialize values;
    $book_id = "";
    $title = "";
    $authors = "";
    $cover = "";
    $isbn10 = "";
    $isbn13 = "";
    $source = "none";

    # Remove non-alphanumerics from search
    $isbn = preg_replace("/\W|_/", '', $_POST["isbn"]);
    # Check local database for existing entry

    global $books;
    $find_book = $yhteys->prepare($books);
    $find_book->bind_param("ss", $isbn, $isbn);
    $find_book->execute();
    $result = $find_book->get_result()->fetch_assoc();

    if ($result) {
        $book_id = $result['book_id'];
        $title = $result['title'];
        # $authors = $row['name']; // Disabled, until I figure out how to get this crap to work as intended
        $cover = $result['cover'];
        $isbn10 = $result['isbn_10'];
        $isbn13 = $result['isbn_13'];
        $blurb = $result['blurb'];
        $language_id = $result['language_id'];
        $source = "Local";

        echo "
        <form id='book_data' method='POST' action='book.php'>
            <input type='hidden' name='book_id' value='$book_id'>
            <input type='hidden' name='title' value='$title'>
            <input type='hidden' name='authors' value='$authors'>
            <input type='hidden' name='cover' value='$cover'>
            <input type='hidden' name='isbn10' value='$isbn10'>
            <input type='hidden' name='isbn13' value='$isbn13'>
            <input type='hidden' name='source' value='$source'>
            <input type='hidden' name='from_post' value='true'>
        </form>
        <script>
            document.querySelector('#book_data').submit();
        </script>
    ";
        die();
    }

    # If fail, search OpenLibrary
    $url = $api_url . $isbn . $data_format;

    # ALWAYS returns a JSON-string! On failure, the string is {}.
    $book = file_get_contents($url);

    # If a book exists on OpenLibrary, fetch its data.
    if ($book !== '{}') {
        # Handle book data
        # Convert JSON string to associative array
        $book = json_decode($book, true);
        $book = $book['ISBN:' . $isbn];

        # Fetch the data you're interested in
        $title = fetchTitle($book);
        $authors = fetchAuthors($book);
        $cover = fetchCover($book);
        $isbn10 = fetchISBN($book, 10);
        $isbn13 = fetchISBN($book, 13);
        $source = "OpenLibrary";
    }

    # Send book data to book page.
    # $from-post is a value used by book.php to detect that values were sent to it. It can be gibberish
    echo "
        <form id='book_data' method='POST' action='book.php'>
            <input type='hidden' name='book_id' value='$book_id'>
            <input type='hidden' name='title' value='$title'>
            <input type='hidden' name='authors' value='$authors'>
            <input type='hidden' name='cover' value='$cover'>
            <input type='hidden' name='isbn10' value='$isbn10'>
            <input type='hidden' name='isbn13' value='$isbn13'>
            <input type='hidden' name='source' value='$source'>
            <input type='hidden' name='from_post' value='true'>
        </form>
        <script>
            document.querySelector('#book_data').submit();
        </script>
    ";
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
    <title>Home Library</title>
</head>

<body>

    <?php include "header.php" ?>

    <div id="content">
        <form action="" method="POST">
            <label for="isbn">ISBN
                <input name="isbn" id="isbn" type="text" pattern=<?php echo $isbn_pattern ?> placeholder="9781234567890">
            </label>
            <input type="submit" name="search" value="Search">
        </form>
    </div>

    <?php include "footer.html" ?>

</body>

</html>