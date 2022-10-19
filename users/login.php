<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="../script.js"></script>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <title>Kirjaudu sisään</title>
</head>

<body>
    <?php include "../header.php" ?>
    <div id="content">

        <h2>Kirjaudu sisään</h2>
        <form action="user_handler.php" method="POST">
            <label for="username">Käyttäjätunnus
                <input type="text" name="username" id="username">
            </label>
            <label for="password">
                <input type="password" name="password" id="password" required>
                <input type="checkbox" onclick="showPassword()">Näytä salasana
            </label>
            <input type="submit" name="login" value="Kirjaudu sisään">
        </form>
        <div>Unohdin salasanan</div>
        <div><a href="register.php">Luo uusi käyttäjätili</a></div>
    </div>
    <?php include "../footer.html" ?>
</body>

</html>