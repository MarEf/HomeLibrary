<?php
# API for handling users
include_once 'connect.php';

switch (true) {
    case isset($_POST['register']):
        register();
        break;
    case isset($_POST['login']):
        login();
        break;
    case isset($_POST['update_user']):
        update_user();
        break;
    case isset($_POST['recover_password']):
        recover_password();
        break;
    case isset($_POST['delete_user']):
        delete_user();
        break;
    default:
        echo "Olet joko nero tai kömpelö, koska yllä oli kaikki käyttötapaukset...<br>";
        break;
}

function register()
{
    if (check_username()) {
        global $yhteys;
        $query = 'INSERT INTO user (username, email, password, verification_expiry)
              VALUES (?, ?, ?, ?)';

        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        # Generate activation token
        $verification_token = "";

        // 24 hours
        $verification_expiry = date('Y-m-d H:i:s', time() + 86400);
        try {
            $register = $yhteys->prepare($query);
            $register->bind_param("sss", $_POST['username'], $_POST['email'], $password, $verification_expiry);
            $register->execute();
        } catch (Throwable $e) {
            echo "Käyttäjätunnuksen luominen ei onnistunut.<br>
                  $e";
        }

        try {
            // Send email verification
        } catch (Throwable $e) {
            // Sending email failed
        }
    } else {
        // Inform user that the username is already taken
    }
}

function check_username()
{
    global $yhteys;
    $query = "SELECT username FROM user
              WHERE username = ?";

    $check_username = $yhteys->prepare($query);
    $check_username->bind_param("s", $_POST['username']);
    $result = $check_username->execute();

    if (!$result) {
        return true; // Username does not exist
    } else {
        return false; // Username exists
    }
}

function login()
{
    global $yhteys;
    if (empty(trim($_POST["email"]))) {
        // NO EMAIL ERROR
        exit;
    } else {
        $email = trim($_POST["email"]);
    }

    //Tarkista, ettei salasana ole tyhjä
    if (empty(trim($_POST["password"]))) {
        // NO PASSWORD ERROR
        exit;
    } else {
        $password = trim($_POST["password"]);
    }

    $query = "SELECT username, password 
              FROM users 
              WHERE username=?";

    $login = $yhteys->prepare($query);
    $login->bind_param("s", $email);

    if ($login->execute()) {
        $result = $login->bind_result($email, $password_hash);
        if ($login->fetch()) {
            if (password_verify($password, $password_hash)) {
                session_start();
                $_SESSION["loggedin"] = true;

                //MUUTA TÄMÄ UUDELLEENOHJAAMAAN EDELLISELLE SIVULLE
                header("location: index.php");
            } else {
                // LOGIN FAIL
            }
        } else {
            // LOGIN FAIL
        }
    } else {
        // LOGIN FAIL
    }
}

function update_user()
{
    global $yhteys;
}

function recover_password()
{
    global $yhteys;
}

function delete_user()
{
    global $yhteys;
}

function send_email()
{
}
