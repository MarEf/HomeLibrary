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
        <p>Tervetuloa <?php echo $_SESSION['username'] ?>!</p>
        <p>Yksilöintitunnuksesi on <?php echo $_SESSION['user_id'] ?></p>
    </div>
    <?php include "../footer.html" ?>
</body>

</html>