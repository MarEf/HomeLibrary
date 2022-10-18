<?php
include_once '../connect.php';
$verified = "";

if (isset($_GET['token'])) {
    $verification_token = $_GET['token'];
    $query = "UPDATE user
              SET verified = true
              WHERE verification_token = ? AND verification_expiry > NOW()";

    $verify = $yhteys->prepare($query);
    $verify->bind_param("s", $verification_token);
    $verify->execute();

    if ($verify->affected_rows !== 0) {
        $verified = "Käyttäjätilin luominen on suoritettu loppuun onnistuneesti. Kirjaudu sisään käyttääksesi kirjastoa. Tervetuloa!";
    } else {
        $verified = "Käyttämäsi linkki on joko väärin tai vanhentunut.";
    }
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
    <title>Tilin vahvistus</title>
</head>

<body>
    <?php include "../header.php" ?>
    <div id="content">
        <h2>Tilin vahvistus</h2>
        <?php echo $verified ?>
    </div>
    <?php include "../footer.html" ?>

</body>

</html>