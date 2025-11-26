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
<style>
/* Aktywny utwór */
#playlist li.active {
    font-weight: bold;
    color: #007bff;
}
#playlist li {
    cursor: pointer;
    padding: 5px 0;
}
</style>
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
                <div class="audio-player">
                    <!-- Wyświetlanie nazwy aktualnego utworu -->
                    <div id="currentTrackName" class="mb-2"><strong>Teraz odtwarzany:</strong> </div>
                    <audio id="audio" controls style="width: 100%;">
                        Twoja przeglądarka nie obsługuje odtwarzacza audio.
                    </audio>
                    <h2>Utwory zawarte w playliście</h2>
                    <ul id="playlist" class="list-group mt-3">
                        <?php while ($song = mysqli_fetch_assoc($resultSongs)): ?>
                            <?php
                            $filepath = 'songs/' . $song['username'] . '/' . $song['filename'];
                            $fileExists = file_exists($filepath);
                            ?>
                            <li class="list-group-item" <?php if($fileExists) echo 'data-src="'.$filepath.'"'; ?> data-title="<?php echo htmlspecialchars($song['title']); ?>">
                                <strong><?php echo htmlspecialchars($song['title']); ?></strong>
                                <br><small><?php echo htmlspecialchars($song['musician']); ?></small>
                                <?php if (!$fileExists): ?>
                                    <span class="text-danger"> - Plik audio nie istnieje</span>
                                <?php endif; ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <script>
                var audio = document.getElementById('audio'); // Element audio
                var playlist = document.getElementById('playlist'); // Lista utworów
                var tracks = playlist.getElementsByTagName('li'); // Wszystkie elementy li
                var currentTrack = 0; // Indeks aktualnego utworu
                var currentTrackName = document.getElementById('currentTrackName'); // Element do wyświetlania nazwy utworu
                function playTrack(trackIndex) {
                    if (!tracks[trackIndex]) return;

                    if (tracks[currentTrack]) {
                        tracks[currentTrack].classList.remove('active'); // Usuń klasę 'active' z poprzedniego
                    }
                    currentTrack = trackIndex;
                    tracks[currentTrack].classList.add('active'); // Dodaj klasę 'active' do aktualnego
                    var trackSrc = tracks[currentTrack].getAttribute('data-src');
                    var trackTitle = tracks[currentTrack].getAttribute('data-title'); // Pobranie tytułu utworu
                    if(trackSrc){
                        audio.src = trackSrc;
                        audio.play();
                        // Aktualizacja wyświetlanej nazwy utworu
                        currentTrackName.innerHTML = "<strong>Teraz odtwarzany:</strong> " + trackTitle;
                    }
                }
                // Automatyczne przejście do następnego utworu
                audio.onended = function() {
                    if (currentTrack + 1 < tracks.length) {
                        playTrack(currentTrack + 1);
                    } else {
                        playTrack(0);
                    }
                };
                // Kliknięcie w utwór z listy
                playlist.addEventListener('click', function(e) {
                    var target = e.target;
                    while(target && target.nodeName !== 'LI') {
                        target = target.parentNode;
                    }
                    if(target && target.getAttribute('data-src')) {
                        var clickedIndex = Array.prototype.indexOf.call(tracks, target);
                        playTrack(clickedIndex);
                    }
                });
                // Start odtwarzania pierwszego utworu
                playTrack(0);
                </script>
            <?php endif; ?>
            <a href="index.php" class="btn btn-secondary mt-3">Powrót</a>
        </div>
    </section>
</main>
<?php require_once 'footer.php'; ?>
</body>
</html>