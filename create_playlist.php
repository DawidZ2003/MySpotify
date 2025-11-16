<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: logowanie.php');
    exit();
}
$idu = $_SESSION['idu'];
// Obsługa formularza
if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $public = isset($_POST['public']) ? 1 : 0;
    // Połączenie z bazą danych
    $conn = mysqli_connect("127.0.0.1", "dawzursz_myspotify", "Dawidek7003$", "dawzursz_myspotify");
    if (!$conn) die("Błąd połączenia: " . mysqli_connect_error());
    mysqli_set_charset($conn, "utf8");
    $sql = "INSERT INTO playlistname (idu, name, public) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isi", $idu, $name, $public);
    mysqli_stmt_execute($stmt);
    $newPlaylistId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    header("Location: add_to_playlist.php?idpl=$newPlaylistId");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Utwórz nową playlistę</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
<h2>Utwórz nową playlistę</h2>
<form action="" method="post">
    <div class="mb-3">
        <label class="form-label">Nazwa playlisty:</label>
        <input type="text" name="name" class="form-control form-control-sm" required>
    </div>
    <div class="mb-3 form-check">
        <input type="checkbox" name="public" class="form-check-input" id="publicCheck">
        <label class="form-check-label" for="publicCheck">Publiczna</label>
    </div>
    <button type="submit" name="submit" class="btn btn-primary btn-sm">Utwórz playlistę</button>
    <a href="index.php" class="btn btn-secondary btn-sm ms-2">Powrót</a>
</form>
</body>
</html>