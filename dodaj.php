<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</HEAD>
<BODY>
<?php
session_start();
$user = htmlentities ($_POST['user'], ENT_QUOTES, "UTF-8");
$pass = htmlentities ($_POST['pass'], ENT_QUOTES, "UTF-8");
$pass2 = htmlentities ($_POST['pass2'], ENT_QUOTES, "UTF-8");
$link = mysqli_connect("127.0.0.1","dawzursz_myspotify", "Dawidek7003$", "dawzursz_myspotify"); // połączenie z BD
if(!$link) { echo"Błąd: ". mysqli_connect_errno()." ".mysqli_connect_error(); } // obsługa błędu połączenia z BD
mysqli_query($link, "SET NAMES 'utf8'"); // ustawienie polskich znaków
// prosta walidacja
if ($user === '' || $pass === '' || $pass2 === '') {
    echo "Wypełnij wszystkie pola formularza.";
    mysqli_close($link);
    exit();
}
// walidacja loginu — tylko litery, cyfry, podkreślenie, myślnik
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $user)) {
    echo "Login zawiera niedozwolone znaki! Dozwolone: litery, cyfry, '_' i '-'.";
    exit();
}
$result = mysqli_query($link, "SELECT * FROM users WHERE username='$user'");
if (mysqli_num_rows($result) > 0) 
{
    echo "Użytkownik o podanym loginie już istnieje";
    mysqli_close($link);
    exit();
}
if($pass!==$pass2)
{
    echo "Hasła nie są identyczne. Spróbuj ponownie";
    mysqli_close($link);
    exit();
}
else
{
    mysqli_query($link, "INSERT INTO users (username, password) VALUES ('$user', '$pass'); ");
    // tworzenie katalogu macierzystego
    $usersRoot = __DIR__ . "/songs";
    if (!is_dir($usersRoot)) {
        mkdir($usersRoot, 0777, true);
    }
    $userDir = $usersRoot . "/" . $user;
    if (!is_dir($userDir)) {
        mkdir($userDir, 0777, true);
    }
    header('Location: index.php');
    exit();
}
?>
</BODY>
</HTML>