<?php
session_start();
// Sprawdzamy, czy użytkownik jest zalogowany
if (!isset($_SESSION['loggedin'])) {
    header('Location: logowanie.php');
    exit();
}
// Połączenie z bazą
$host = "127.0.0.1";
$user = "dawzursz_myspotify";
$pass = "Dawidek7003$";
$db   = "dawzursz_myspotify";
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Błąd połączenia z bazą: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");
// Dane z sesji
$homeDir = $_SESSION['home_dir'];
$idu     = $_SESSION['idu'];
// Dane z formularza
$title     = $_POST['title'] ?? '';
$musician  = $_POST['musician'] ?? '';
$lyrics    = $_POST['lyrics'] ?? '';
$idmt      = $_POST['idmt'] ?? '';

if ($title == "" || $musician == "" || $idmt == "") {
    die("Brakuje danych wymaganych do dodania utworu!");
}
// Obsługa uploadu pliku
$currentDir = isset($_POST['current_dir']) ? $_POST['current_dir'] : $homeDir;
$currentDir = realpath($currentDir);
// Zabezpieczenie – nie wolno wyjść poza katalog użytkownika
if ($currentDir === false || strpos($currentDir, realpath($homeDir)) !== 0) {
    die("Nieprawidłowy katalog!");
}
// Tworzenie katalogu, jeśli nie istnieje
if (!is_dir($currentDir)) {
    mkdir($currentDir, 0755, true);
}
// Nazwa pliku
$filename = basename($_FILES["fileToUpload"]["name"]);
$targetFile = $currentDir . DIRECTORY_SEPARATOR . $filename;
// Walidacja pliku
$allowed = ["mp3"];
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    die("Dozwolone są tylko pliki MP3!");
}
if ($_FILES["fileToUpload"]["error"] !== UPLOAD_ERR_OK) {
    die("Błąd uploadu: " . $_FILES["fileToUpload"]["error"]);
}
// Przeniesienie pliku
if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile)) {
    die("Nie udało się zapisać pliku na serwerze.");
}
// Zapisujemy tylko nazwę pliku (reszta — ścieżki — są na podstawie username)
$dbFilename = $filename;
// Wstawienie rekordu do tabeli song
$sql = "INSERT INTO song (title, musician, lyrics, filename, idu, idmt)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssssii", 
    $title, 
    $musician, 
    $lyrics, 
    $dbFilename, 
    $idu, 
    $idmt
);
if (mysqli_stmt_execute($stmt)) {
    header("Location: index.php");
    exit();
} else {
    echo "Błąd podczas zapisu do bazy: " . mysqli_error($conn);
}
?>
