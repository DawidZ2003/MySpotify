<?php declare(strict_types=1);
session_start();
// Sprawdzenie logowania
if (!isset($_SESSION['loggedin'])) {
    header('Location: logowanie.php');
    exit();
}
// Połączenie z bazą danych
$host = "127.0.0.1";
$user = "dawzursz_myspotify";
$pass = "Dawidek7003$";
$db   = "dawzursz_myspotify";
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) die("Błąd połączenia z bazą: " . mysqli_connect_error());
mysqli_set_charset($conn, "utf8");
// Pobranie ID utworu
if (!isset($_GET['ids'])) {
    die("Brak ID utworu!");
}
$ids = (int)$_GET['ids'];
// Pobranie rekordu z bazy
$sql = "SELECT title, filename, idu FROM song WHERE ids = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $ids);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $title, $filename, $idu);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
// Sprawdzenie, czy utwór należy do zalogowanego użytkownika
if (!$filename || $idu != $_SESSION['idu']) {
    die("Brak dostępu do tego utworu!");
}
// Ścieżka do pliku względem katalogu użytkownika
$relativePath = "songs/" . $_SESSION['username'] . "/" . $filename;
// Sprawdzenie czy plik istnieje
if (!file_exists($relativePath)) {
    die("Plik nie istnieje na serwerze!");
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">  
    <title>Odtwarzanie: <?php echo htmlspecialchars($title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="twoj_css.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="twoj_js.js"></script>
</head>
<body onload="myLoadHeader()">
    <div id="myHeader"></div>
    <main>
        <section class="sekcja1">
            <div class="container-fluid mt-4">
                <h2>Odtwarzanie utworu:</h2>
                <h3><?php echo htmlspecialchars($title); ?></h3>
                <audio controls style="width:100%;">
                    <source src="<?php echo $relativePath; ?>" type="audio/mpeg">
                    Twoja przeglądarka nie wspiera odtwarzania MP3.
                </audio>
                <br><br>
                <a href="index.php" class="btn btn-primary">Powrót do strony głównej</a>
            </div>
        </section>
    </main>
    <?php require_once 'footer.php'; ?>
</body>
</html>