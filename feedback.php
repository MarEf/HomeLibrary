<?php
include_once 'connect.php';

if (isset($_POST['feedback'])) {
    $query = "INSERT INTO feedbacks (user_id, topic, message)
              VALUES (?, ?, ?)";
    try {
        $add_feedback = $yhteys->prepare($query);
        $add_feedback->bind_param("iss", $_SESSION['user_id'], $_POST['topic'], $_POST['message']);
        $add_feedback->execute();
    } catch (Throwable $e) {
        echo "Palautteen anto ei onnistunut.<br>";
        echo $e;
    }
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
        <h2>Anna palautetta</h2>
        <p>
            Pidätkö sovelluksesta? Jäikö jokin askarruttamaan? Voit jättää palautetta alla olevalla lomakkeella.
        </p>
        <p>
            ÄLÄ YRITÄ ANTAA OIKEASTI PALAUTETTA! Lomake ei tällä hetkellä toimi.
        </p>

        <form action="" id="feedback-form" method="POST">
            <input type="hidden" name="user_id" value=<?php echo $_SESSION['user_id'] ?>>
            <label for="topic">Palautteen aihe
                <input type="text" id="topic" name="topic" maxlength="255" required>
            </label>
            <label for="feedback">Palaute
                <textarea name="feedback" id="message" rows="15" maxlength="5000" required></textarea>
            </label>
            <input type="submit" name="feedback" value="Lähetä palaute">
        </form>
    </div>

    <?php include "footer.html" ?>
</body>

</html>