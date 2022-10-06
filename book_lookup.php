<?php
# Browse books from the local database
include_once 'connect.php';

# Fetch languages from database
$query = "SELECT language_id, name_fin FROM languages";
$languages = $yhteys->query($query);
$results = "";

function list_languages($languages)
{
    while ($row = $languages->fetch_assoc()) {
        #title, description, release_year, rating
        echo "<option value='{$row["language_id"]}'>{$row['name_fin']}</option>";
    }
}

if (@$_POST['find_books']) {
    $title = "%{$_POST['title']}%";
    $isbn = "%{$_POST['isbn']}%";

    # Initialize query based on input
    switch (true) {
        case ($title !== "%%" && $isbn !== "%%"):
            $query = 'SELECT * FROM books
                      WHERE title  LIKE ? AND (isbn_10 LIKE ? OR isbn_13 = LIKE ?)';
            break;
        case ($title !== "%%"):
            $query = 'SELECT * FROM books
                      WHERE title LIKE ?';
            break;
        case ($isbn !== "%%"):
            $query = 'SELECT * FROM books
                      WHERE isbn_10 LIKE ? OR isbn_13 LIKE ?';
            break;
        default:
            $query = 'SELECT * FROM books';
            break;
    }

    # Fetch results
    try {
        $find_book = $yhteys->prepare($query);
        switch (true) {
            case ($title !== "%%" && $isbn !== "%%"):
                $find_book->bind_param("sss", $title, $isbn, $isbn);
                break;
            case ($title !== "%%"):
                $find_book->bind_param("s", $title);
                break;
            case ($isbn !== "%%"):
                $find_book->bind_param("ss", $isbn, $isbn);
                break;
        }

        $find_book->execute();
        $result = $find_book->get_result();

        if ($result->num_rows === 0) {
            $results = "<p>Hakua vastaavaa kirjaa ei löytynyt. Haluatko lisätä sen?</p>
                  <form action='isbn_lookup.php'>
                    <input type='submit' value='Lisää uusi kirja' />
                  </form>";
        } else {
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
            <th class='ws_only'>ISBN 10</th>
            <th class='ws_only'>ISBN 13</th>
            <th>Kuvaus</th>
            <th></th>
           </tr>
           ";


    while ($row = $result->fetch_assoc()) {
        # These can be so long, so let's trim them a little.
        $title = $row['title'];
        $blurb = $row['blurb'];

        if (strlen($title) > 50) {
            $title = substr($title, 0, 50) . "...";
        }
        if (strlen($blurb) > 50) {
            $blurb = substr($blurb, 0, 50) . "...";
        }

        $results .= "<tr>
                <td>$title</td>
                <td class='ws_only'>{$row['isbn_10']}</td>
                <td class='ws_only'>{$row['isbn_13']}</td>
                <td>$blurb</td>
                <td>
                    Collect 
                    Edit 
                    Delete
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
    <script src="script.js"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <title>Home Library</title>
</head>

<body>
    <?php include "header.html" ?>

    <div id="content">
        <form action="" method="POST">
            <label for="title"> Otsikko tai otsikon osa
                <input type="text" name="title" id="title">
            </label>
            <label for="isbn"> ISBN-numero tai sen osa
                <input name="isbn" id="isbn" type="text" placeholder="9781234567890">
            </label>
            <label for="language_id">Kieli
                <select name="language_id" id="language_id">
                    <option value="">Kirjan kieli</option>
                    <?php list_languages($languages) ?>
                </select>
            </label>
            <input type="submit" name="find_books" value="Hae kirjoja">
        </form>

        <div id="results">

            <?php
            echo $results;
            ?>

        </div>
    </div>

    <?php include "footer.html" ?>

</body>

</html>