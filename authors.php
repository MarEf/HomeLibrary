<?php
if (!isset($_SESSION)) {
    session_start();
}

include_once 'connect.php';
$author_pattern = "^[^,]+$";

if (isset($_POST['find_authors'])) {
    $name = "%{$_POST['name']}%";
    $query = "SELECT * FROM authors
              WHERE name LIKE ?";
} else {
    $query = "SELECT * FROM authors";
}


try {
    $authors = $yhteys->prepare($query);

    if (isset($_POST['find_authors'])) {
        $authors->bind_param("s", $name);
    }


    $authors->execute();
    $result = $authors->get_result()->fetch_all(MYSQLI_ASSOC);

    if (count($result) === 0) {
        $results = "Hakua vastaavaa kirjailijaa ei löytynyt.";
    } else {
        $results = print_results($result);
    }
} catch (Throwable $e) {
    echo "Jokin meni pieleen. </br>$e";
}


function print_results($sql_result)
{
    $result = "";
    global $author_pattern;

    foreach ($sql_result as $row) {
        $name = $row['name'];
        $id = $row['author_id'];

        if (isset($_SESSION['loggedin'])) {
            $result .= "<form class='author' action='author_handler.php' method='POST'>
                        <input type='hidden' name='author_id' id='id' value='$id'>
                        <input type='text' name='name' id='name' pattern='$author_pattern' value='$name' required>

                        <button name='edit_author' type='submit'><i class='fa fa-save'></i></i></button>
                        <button name='delete_author' type='submit'><i class='fas fa-trash-alt'></i></button>
                    </form>
                    ";
        } else {
            $result .= "<form class='author'>
                        <input type='text' name='name' id='name' pattern='$author_pattern' value='$name' required disabled>
                    </form>
                    ";
        }
    }

    return $result;
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
    <title>Authors</title>
</head>

<body>
    <?php include "header.php" ?>

    <div id="content">

        <h2>Kirjailijat</h2>

        <form action="" method="POST">
            <label for="name">
                <input type="text" name="name" id="name" required>
            </label>
            <input type="submit" name="find_authors" value="Rajaa nimellä">
        </form>
        <form action="">
            <input type="submit" name="find_authors" value="Näytä kaikki">
        </form>

        <?php echo $results ?>

    </div>

    <?php include "footer.html" ?>
</body>

</html>