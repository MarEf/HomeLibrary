<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="../script.js"></script>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />

    <title>Luo käyttäjätili</title>
</head>

<body>
    <?php include "../header.php" ?>
    <div id="content">

        <h2>Luo käyttäjätili</h2>

        <?php
        if (isset($_SESSION['loggedin'])) {
            echo "<p>Sinulla on jo käyttäjätili. Mikäli haluat luoda uuden tilin, sinun on ensin kirjauduttava ulos<p/>";
        } else {
            echo "
        <form action='user_handler.php' method='post'>
            <label for='username'>Käyttäjätunnus
                <input type='text' name='username' id='username' maxlength='255' required>
            </label>
            <label for='email'>Sähköpostiosoite
                <input type='email' name='email' id='email' pattern='^[\w._%-]+@[\w.-]+\.[a-z]{2,}$' maxlength='254' required>
            </label>
            <label for='password'>Salasana
                <input type='password' name='password' id='password' minlength='16' maxlength='255' required>
                <input type='checkbox' onclick='showPassword()'>Näytä salasana
            </label>
            <input type='submit' name='register' value='Luo käyttäjätili'>
        </form>";
        }
        ?>

    </div>
    <?php include "../footer.html" ?>
</body>

</html>