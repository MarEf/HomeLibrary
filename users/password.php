<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaihda salasana</title>
</head>

<body>
    <?php include "../header.php" ?>
    <div class='content'>
        <h2>Vaihda salasana</h2>
        <p>Tätä ominaisuutte ei ole vielä toteutettu.</p>

        <form>
            <label for='old_password'>Vanha salasana
                <input type='password' id='old_password' name='old_password' required>
            </label>
            <label for='new_password'>Uusi salasana
                <input type='password' name='new_password' id='new_password' required>
            </label>
            <input type='submit' value='Vaihda salasana'>
        </form>
    </div>
    <?php include "../footer.php" ?>
</body>

</html>