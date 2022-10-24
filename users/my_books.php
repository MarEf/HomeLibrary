<?php
# Browse books from the local database
include_once '../connect.php';
if (!isset($_SESSION)) {
    session_start();
}

# Fetch languages from database
$query = "SELECT language_id, name_fin FROM languages";
$languages = $yhteys->query($query);
$results = "";

$book_id = "";
$title = "";
$authors = "";
$cover = "";
$isbn10 = "";
$isbn13 = "";
$blurb = "";
$language_id;
$user_id;

function list_languages($languages)
{
    while ($row = $languages->fetch_assoc()) {
        #title, description, release_year, rating
        echo "<option value='{$row["language_id"]}'>{$row['name_fin']}</option>";
    }
}

if (@$_POST['find_books']) {
    $title = "%{$_POST['title']}%";
    $isbn = "%" . preg_replace("/\W|_/", '', $_POST['isbn']) . "%";
    $user_id = $_SESSION['user_id'];

    # Initialize query based on input
    switch (true) {
        case ($title !== "%%" && $isbn !== "%%"):

            $query = "SELECT b.*,
                    GROUP_CONCAT(ba.author_id),
                    GROUP_CONCAT(a.name SEPARATOR ', ')
                  FROM books AS b
                  LEFT JOIN book_author AS ba
                    ON b.book_id = ba.book_id
                  LEFT JOIN authors AS a
                    ON ba.author_id = a.author_id
                  LEFT JOIN book_user AS bu
                    ON b.book_id = bu.book_id
                  WHERE title LIKE ? AND (isbn_10 LIKE ? OR isbn_13 LIKE ?) AND bu.user_id = ?
                  GROUP BY b.title";

            break;
        case ($title !== "%%"):

            $query = "SELECT b.*,
                    GROUP_CONCAT(ba.author_id),
                    GROUP_CONCAT(a.name SEPARATOR ', ')
                  FROM books AS b
                  LEFT JOIN book_author AS ba
                    ON b.book_id = ba.book_id
                  LEFT JOIN authors AS a
                    ON ba.author_id = a.author_id
                  LEFT JOIN book_user AS bu
                    ON b.book_id = bu.book_id
                  WHERE title LIKE ? AND bu.user_id = ?
                  GROUP BY b.title";
            break;
        case ($isbn !== "%%"):

            $query = "SELECT b.*,
                    GROUP_CONCAT(ba.author_id),
                    GROUP_CONCAT(a.name SEPARATOR ', ')
                  FROM books AS b
                  LEFT JOIN book_author AS ba
                    ON b.book_id = ba.book_id
                  LEFT JOIN authors AS a
                    ON ba.author_id = a.author_id
                  LEFT JOIN book_user AS bu
                    ON b.book_id = bu.book_id
                  WHERE isbn_10 LIKE ? OR isbn_13 LIKE ? AND bu.user_id = ?
                  GROUP BY b.title";

            break;
        default:
            $query = "SELECT b.*,
                    GROUP_CONCAT(ba.author_id),
                    GROUP_CONCAT(a.name SEPARATOR ', ')
                  FROM books AS b
                  LEFT JOIN book_author AS ba
                    ON b.book_id = ba.book_id
                  LEFT JOIN authors AS a
                    ON ba.author_id = a.author_id
                  LEFT JOIN book_user AS bu
                    ON b.book_id = bu.book_id 
                  WHERE bu.user_id = ?
                  GROUP BY b.title";

            break;
    }


    # Fetch results
    try {
        $find_book = $yhteys->prepare($query);
        switch (true) {
            case ($title !== "%%" && $isbn !== "%%"):
                $find_book->bind_param("sssi", $title, $isbn, $isbn, $user_id);
                break;
            case ($title !== "%%"):
                $find_book->bind_param("si", $title);
                break;
            case ($isbn !== "%%"):
                $find_book->bind_param("ssi", $isbn, $isbn, $user_id);
                break;
            default:
                $find_book->bind_param("i", $user_id);
        }

        $find_book->execute();
        $result = $find_book->get_result()->fetch_all(MYSQLI_ASSOC);

        if (count($result) === 0) {
            $results = "<p>Hakua vastaavaa kirjaa ei löytynyt.</p>";
        } else {
            #$book_array = set_book_array($result);
            $results = print_results($result);
        }
    } catch (Throwable $e) {
        echo "Jokin meni pieleen: <br> $e";
    }
}



function print_results($result)
{
    $results = "<table>
          <tr>
            <th>Otsikko</th>
            <th class='ws_only'>Kirjailija</th>
            <th class='ws_only'>ISBN 10</th>
            <th class='ws_only'>ISBN 13</th>
            <th>Kuvaus</th>
            <th></th>
           </tr>
           ";

    foreach ($result as $row) {
        # These can be so long, so let's trim them a little.
        $title_display = $row['title'];
        $blurb_display = $row['blurb'];
        global $book_form;

        global $book_id;
        global $title;
        global $authors;
        global $cover;
        global $isbn10;
        global $isbn13;
        global $blurb;
        global $language_id;
        global $user_id;

        $book_id = $row['book_id'];
        $title = $row['title'];
        $authors = $row['GROUP_CONCAT(a.name SEPARATOR \', \')'];
        $cover = $row['cover'];
        $isbn10 = $row['isbn_10'];
        $isbn13 = $row['isbn_13'];
        $blurb = $row['blurb'];
        $language_id = $row['language_id'];

        $book_form = "<form id='book_data' method='POST' action='../book.php'>
                        <input type='hidden' name='book_id' value='$book_id'>
                        <input type='hidden' name='title' value='$title'>
                        <input type='hidden' name='authors' value='$authors'>
                        <input type='hidden' name='cover' value='$cover'>
                        <input type='hidden' name='language_id' value='$language_id'>
                        <input type='hidden' name='isbn10' value='$isbn10'>
                        <input type='hidden' name='isbn13' value='$isbn13'>
                        <input type='hidden' name='blurb' value='$blurb'>
                        <input type='hidden' name='source' value='Local'>
                        <input type='hidden' name='from_post' value='true'>";

        if (strlen($title_display) > 100) {
            $title_display = substr($title_display, 0, 100) . "...";
        }
        if (strlen($blurb_display) > 100) {
            $blurb_display = substr($blurb_display, 0, 100) . "...";
        }

        $results .= "<tr>
                <td>$title_display</td>
                <td class='ws_only'>$authors</td>
                <td class='ws_only'>$isbn10</td>
                <td class='ws_only'>$isbn13</td>
                <td>$blurb_display</td>
                <td>
                    <form action='../book_handler.php' method='POST'>
                        <input type='hidden' name='book_id' value='$book_id'>
                        <input type='hidden' name='remove_book'>
                        <button type='submit'><i class='fa fa-ban'></i></i></i></button>
                    </form>
                    $book_form
                        <button type='submit'><i class='fa fa-edit'></i></button>
                    </form>
                </td>
             </tr>
             ";
    }
    $results .= "</table>";

    return $results;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="../script.js"></script>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <title>Omat kirjat</title>
</head>

<body>
    <?php include "../header.php" ?>

    <div id='content'>
        <h2>Omat kirjat</h2>

        <?php
        if (isset($_SESSION['loggedin'])) {
            $user_id = $_SESSION['user_id'];
            echo "
        <form action='' method='POST'>
            <label for='title'> Otsikko tai otsikon osa
                <input type='text' name='title' id='title'>
            </label>
            <label for='isbn'> ISBN-numero tai sen osa
                <input name='isbn' id='isbn' type='text' placeholder='9781234567890'>
            </label>
            <label for='language_id'>Kieli
                <select name='language_id' id='language_id'>
                    <option value=''>Kirjan kieli</option>
                    ";
            list_languages($languages);
            echo "</select>
            </label>
            <input type='submit' name='find_books' value='Hae kirjoja'>
        </form>
        
        <div id='results'>
        ";


            echo $results;


            echo "</div>";
        } else {
            echo "<p>Kirjaudu sisään selataksesi kokoelmaasi</p>";
        }
        ?>
    </div>
    <?php include "../footer.html" ?>
</body>

</html>