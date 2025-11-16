<?php
declare(strict_types=1);
session_start();
// Sprawdzenie logowania
if (!isset($_SESSION['loggedin'])) {
    header('Location: logowanie.php');
    exit();
}
$idu = $_SESSION['idu'];
$username = $_SESSION['username'];
$homeDir = $_SESSION['home_dir'];
// Połączenie z bazą
$conn = mysqli_connect("127.0.0.1", "dawzursz_myspotify", "Dawidek7003$", "dawzursz_myspotify");
if (!$conn) die("Błąd połączenia z bazą: " . mysqli_connect_error());
mysqli_set_charset($conn, "utf8");
// Pobranie id playlisty
$idpl = isset($_GET['idpl']) ? intval($_GET['idpl']) : 0;
// Pobranie informacji o playliście
$sqlCheck = "SELECT public, idu, name FROM playlistname WHERE idpl = ?";
$stmt = mysqli_prepare($conn, $sqlCheck);
mysqli_stmt_bind_param($stmt, "i", $idpl);
mysqli_stmt_execute($stmt);
$resultCheck = mysqli_stmt_get_result($stmt);
$playlist = mysqli_fetch_assoc($resultCheck);
mysqli_stmt_close($stmt);
if (!$playlist) {
    die("Playlista nie istnieje.");
}
// Sprawdzenie dostępu: publiczna = każdy zalogowany, prywatna = tylko właściciel
if ($playlist['public'] == 0 && $playlist['idu'] != $idu) {
    die("Brak dostępu do tej prywatnej playlisty.");
}
// Pobranie utworów w playliście
$sqlSongs = "
    SELECT s.title, s.musician, s.filename, u.username
    FROM playlistdatabase pd
    JOIN song s ON pd.ids = s.ids
    JOIN users u ON s.idu = u.idu
    WHERE pd.idpl = ?
";
$stmt = mysqli_prepare($conn, $sqlSongs);
mysqli_stmt_bind_param($stmt, "i", $idpl);
mysqli_stmt_execute($stmt);
$resultSongs = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Odtwarzanie playlisty: <?php echo htmlspecialchars($playlist['name']); ?></title>
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
        <div class="container-fluid">
            <h2>Odtwarzanie playlisty: <?php echo htmlspecialchars($playlist['name']); ?></h2>
            <?php if (mysqli_num_rows($resultSongs) === 0): ?>
                <p>Playlista jest pusta.</p>
            <?php else: ?>
                <ul class="list-group mb-3">
                    <?php while ($song = mysqli_fetch_assoc($resultSongs)): ?>
                        <?php
                        // Ścieżka do pliku: katalog użytkownika
                        $filepath = 'songs/' . $song['username'] . '/' . $song['filename'];
                        if (!file_exists($filepath)) {
                            $fileExists = false;
                        } else {
                            $fileExists = true;
                        }
                        ?>
                        <li class="list-group-item">
                            <strong><?php echo htmlspecialchars($song['title']); ?></strong><br>
                            <?php if ($fileExists): ?>
                                <audio controls preload="none" style="width: 100%;">
                                    <source src="<?php echo $filepath; ?>" type="audio/mpeg">
                                    Twoja przeglądarka nie obsługuje odtwarzacza audio.
                                </audio>
                            <?php else: ?>
                                <span class="text-danger">Plik audio nie istnieje.</span>
                            <?php endif; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php endif; ?>
            <a href="index.php" class="btn btn-secondary">Powrót</a>
        </div>
    </section>
</main>
<?php require_once 'footer.php'; ?>
</body>
</html>
