<?php
# API for handling users
include_once '../connect.php';
include_once 'mailer.php';

switch (true) {
    case isset($_POST['register']):
        register();
        header('Location: ' . 'home.php');
        die();
        break;
    case isset($_POST['login']):
        login();
        header('Location: ' . 'home.php');
        die();
        break;
    case isset($_POST['logout']):
        logout();
        header('Location: ' . '../index.php');
        die();
        break;
    case isset($_POST['update_user']):
        update_user();
        header('Location: ' . 'home.php');
        die();
        break;
    case isset($_POST['recover_password']):
        recover_password();
        header('Location: ' . '../index.php');
        die();
        break;
    case isset($_POST['delete_user']):
        delete_user();
        header('Location: ' . '../index.php');
        die();
        break;
    default:
        echo "Olet joko nero tai kömpelö, koska yllä oli kaikki käyttötapaukset...<br>";
        break;
}

function register()
{
    if (check_username()) {
        global $yhteys;
        $success = false;

        $query = 'INSERT INTO user (username, password, email, verification_token, verification_expiry)
              VALUES (?, ?, ?, ?, ?)';

        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        echo "Salasana kryptattu.<br>";

        $verification_token = "" . rand() . rand();
        echo "Vahvistuskoodi: $verification_token<br>";

        // 24 hours
        $verification_expiry = date('Y-m-d H:i:s', time() + 86400);
        echo "Yritetään luoda käyttäjätiliä.<br>";
        try {
            $register = $yhteys->prepare($query);
            $register->bind_param("sssss", $_POST['username'], $password, $_POST['email'], $verification_token, $verification_expiry);
            $success = $register->execute();
            echo "Käyttäjätilin luonti onnistui.<br>";
        } catch (Throwable $e) {
            echo "Käyttäjätunnuksen luominen ei onnistunut.<br>
                  $e";

            echo "<form id='alert' method='POST' action='register.php'>
                    <input type='hidden' name='alert' value='Käyttäjätunnuksen luominen ei onnistunut. Yritä hetken kuluttua uudelleen.'>
                  </form>
                  <script>
                    document.querySelector('#alert').submit();
                  </script>";
            exit;
        }

        if ($success) {
            try {
                echo "Lähetetään sähköpostivahvistus.<br>";
                // Send email verification
                $mail = "<h1>Tervetuloa käyttämään kotikirjastoa!</h1><br>
                <p>
                    <a href='http://" . $_SERVER['SERVER_ADDR'] . "HomeLibrary/users/verify.php?token=$verification_token'>Vahvista käyttäjätili klikkaamalla tästä.</a><br>
                </p>
                <p>Mikäli et luonut käyttäjätiliä, jätä tämä viesti huomiotta.</p>";

                //No HTML
                $alt_mail = "Tervetuloa käyttämään kotikirjastoa!\n\n
                Vahvista käyttäjätilin luominen oheisella linkillä: http://" . $_SERVER['SERVER_ADDR'] . "HomeLibrary/users/verify.php?token=$verification_token \n\n
                Mikäli et luonut käyttäjätiliä, jätä tämä viesti huomiotta.";

                sendmail($_POST['email'], $_POST['username'], "Account Verification", $mail, $alt_mail);
            } catch (Throwable $e) {
                // Sending email failed
                // Remove user from database
                // Inform user
                $query = "DELETE FROM user
                          WHERE username = ?";
                $delete = $yhteys->prepare($query);
                $delete->bind_param("s", $_POST['username']);
                $delete->execute();

                echo "<form id='alert' method='POST' action='register.php'>
                        <input type='hidden' name='alert' value='Käyttäjätunnuksen luominen ei onnistunut. Yritä hetken kuluttua uudelleen.'>
                      </form>
                      <script>
                        document.querySelector('#alert').submit();
                      </script>";
            }
        }
    } else {
        // Inform user that the username is already taken
        echo "<form id='alert' method='POST' action='register.php'>
                <input type='hidden' name='alert' value='Käyttäjätunnus on varattu. Kokeile uudelleen toisella käyttäjätunnuksella.'>
              </form>
              <script>
                document.querySelector('#alert').submit();
              </script>";
    }
}

function check_username()
{
    global $yhteys;
    $query = "SELECT username FROM user
              WHERE username = ?";

    echo "Tarkistetaan käyttäjätunnusta.<br>";

    $check_username = $yhteys->prepare($query);
    $check_username->bind_param("s", $_POST['username']);
    $check_username->execute();
    $result = $check_username->get_result();

    if ($result->num_rows === 0) {
        echo "Käyttäjätunnus on vapaa. Tili voidaan luoda.<br>";
        return true; // Username does not exist
    } else {
        echo "Käyttäjätunnus on varattu. Tilin luonti keskeytetään.<br>";
        return false; // Username exists
    }
}

function login()
{
    global $yhteys;
    $username = $password = "";

    if (empty(trim($_POST["username"]))) {
        // NO USERNAME ERROR
        echo "<form id='alert' method='POST' action='login.php'>
                <input type='hidden' name='alert' value='Et antanut käyttäjätunnusta'>
              </form>
              <script>
                document.querySelector('#alert').submit();
              </script>";
        exit;
    } else {
        $username = trim($_POST["username"]);
    }

    //Tarkista, ettei salasana ole tyhjä
    if (empty(trim($_POST["password"]))) {
        // NO PASSWORD ERROR
        echo "<form id='alert' method='POST' action='login.php'>
                <input type='hidden' name='alert' value='Et antanut salasanaa'>
              </form>
              <script>
                document.querySelector('#alert').submit();
              </script>";
        exit;
    } else {
        $password = trim($_POST["password"]);
    }

    $query = "SELECT user_id, username, password
              FROM user 
              WHERE username=?";

    $login = $yhteys->prepare($query);
    $login->bind_param("s", $username);

    if ($login->execute()) {
        $result = $login->bind_result($user_id, $username, $password_hash);
        if ($login->fetch()) {
            if (password_verify($password, $password_hash)) {
                session_start();
                $_SESSION["loggedin"] = true;
                $_SESSION["username"] = $username;
                $_SESSION['user_id'] = $user_id;
            } else {
                echo "<form id='alert' method='POST' action='login.php'>
                        <input type='hidden' name='alert' value='Virheellinen käyttäjätunnus tai salasana'>
                      </form>
                      <script>
                        document.querySelector('#alert').submit();
                      </script>";
                exit;
            }
        } else {
            // LOGIN FAIL
            echo "<form id='alert' method='POST' action='login.php'>
                    <input type='hidden' name='alert' value='Virheellinen käyttäjätunnus tai salasana'>
                  </form>
                  <script>
                    document.querySelector('#alert').submit();
                  </script>";
            exit;
        }
    } else {
        // LOGIN FAIL
        echo "<form id='alert' method='POST' action='login.php'>
                <input type='hidden' name='alert' value='Virheellinen käyttäjätunnus tai salasana'>
              </form>
              <script>
                document.querySelector('#alert').submit();
              </script>";
        exit;
    }
}

function logout()
{
    session_start();
    $_SESSION = array();
    session_destroy();
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
