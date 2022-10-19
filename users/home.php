<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="../script.js"></script>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <title>Oma tili</title>
</head>

<body>
    <?php include "../header.php" ?>
    <div id="content">
        <h2>Oma tili</h2>

        <?php

        if (isset($_SESSION['loggedin'])) {
            echo "<h3>Tervetuloa {$_SESSION['username']}!</h3>
                  <div>
                    Mitä haluat tehdä tänään?
                  </div>
                  
                  <div>

                  <form action='my_books.php' method='POST'>
                    <input type='submit' value='Oma kokoelma' />
                  </form>
                  <form action='password.php' method='POST'>
                    <input type='submit' value='Vaihda salasana' />
                  </form>
                  <form action='password.php' method='POST'>
                    <input type='submit' value='Vaihda salasana' />
                  </form>
                  <form action='user_handler.php' method='POST'>
                    <input type='submit' name='delete_user' value='Poista käyttäjätili' />
                  </form>
                  
                  </div>";
        } else {
            echo "Kirjaudu sisään ennen kuin voit tarkastella käyttäjätietoja.";
        }
        ?>



    </div>
    <?php include "../footer.html" ?>
</body>

</html>