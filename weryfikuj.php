<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</HEAD>
<BODY>
<?php
session_start();
// sprawdzenie blokady brute-force
if (isset($_SESSION['register_block_time'])) {
    $diff = time() - $_SESSION['register_block_time'];
    if ($diff < 60) { // blokada 1 minuta
        $remain = 60 - $diff;
        echo "Logowanie zablokowane po nieudanej próbie. Spróbuj za $remain sekund.";
        exit();
    } else {
        unset($_SESSION['register_block_time']); // minęła blokada, resetujemy
    }
}
$user = htmlentities ($_POST['user'], ENT_QUOTES, "UTF-8"); // rozbrojenie potencjalnej bomby w zmiennej $user
$pass = htmlentities ($_POST['pass'], ENT_QUOTES, "UTF-8"); // rozbrojenie potencjalnej bomby w zmiennej $pas
$link = mysqli_connect("127.0.0.1","dawzursz_myspotify", "Dawidek7003$", "dawzursz_myspotify"); // połączenie z BD – wpisać swoje dane
if(!$link) { echo"Błąd: ". mysqli_connect_errno()." ".mysqli_connect_error(); } // obsługa błędu połączenia z BD
mysqli_query($link, "SET NAMES 'utf8'"); // ustawienie polskich znaków
$result = mysqli_query($link, "SELECT * FROM users WHERE username='$user'"); // wiersza, w którym login=login z formularza
$rekord = mysqli_fetch_array($result); // wiersza z BD, struktura zmiennej jak w BD
if(!$rekord)
{
    $_SESSION['register_block_time'] = time();
    mysqli_close($link);
    echo "Niepoprawny login lub hasło!";
}
else
{
    // Uzytkownik istnieje i sprawdzamy haslo
    if($rekord['password']==$pass)
    {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $user;
        $_SESSION['idu'] = $rekord['idu'];
        // ustawiamy katalog macierzysty
        $_SESSION['home_dir'] = __DIR__ . "/songs/" . $user;
        header('Location: index.php');
    }
    else
    // Haslo uzytkownika niepoprawne
    {
        $_SESSION['register_block_time'] = time();
        echo "Niepoprawny login lub hasło!";
    }
}
?>
</BODY>
</HTML>