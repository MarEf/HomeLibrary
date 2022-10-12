<?php
$book_lookup = __NAMESPACE__ . "/HomeLibrary/book_lookup.php";
$isbn_lookup = __NAMESPACE__ . "/HomeLibrary/isbn_lookup.php";
$book = __NAMESPACE__ . "/HomeLibrary/book.php";
$collection = __NAMESPACE__ . "/HomeLibrary/users/my_books.php";
$feedback = __NAMESPACE__ . "/HomeLibrary/feedback.php";
$login = __NAMESPACE__ . "/HomeLibrary/users/login.php";
$register = __NAMESPACE__ . "HomeLibrary/users/register.php";

?>

<header>
    <div class="menu" id="mainMenu">
        <div id="title-and-nav">
            <h1><a href="index.php">Home Library</a></h1>
            <a href="javascript:void(0);" class="icon" onclick="openMenu()">
                <i class="fa fa-bars menu-closed"></i>
                <i class="fa fa-xmark menu-opened"></i>
            </a>
        </div>
        <a class="nav" href="<?php echo $book_lookup ?>">Selaa kirjoja</a>
        <a class="nav" href="<?php echo $isbn_lookup ?>">Hae kirja</a>
        <a class="nav" href="<?php echo $book ?>">Uusi kirja</a>
        <a class="nav" href="<?php echo $collection ?>">Oma kokoelma</a>
        <a class="nav" href="<?php echo $feedback ?>">Anna palautetta</a>
        <a class="nav" href="<?php echo $login ?>">Kirjaudu Sisään</a>
    </div>
</header>