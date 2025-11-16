<?php declare(strict_types=1); 
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: logowanie.php');
    exit();
}
$idu = $_SESSION['idu'];
$homeDir  = $_SESSION['home_dir'];
// Połączenie z bazą
$conn = mysqli_connect("127.0.0.1", "dawzursz_myspotify", "Dawidek7003$", "dawzursz_myspotify");
if (!$conn) die("Błąd połączenia z bazą: " . mysqli_connect_error());
mysqli_set_charset($conn, "utf8");
// Pobranie listy piosenek zalogowanego użytkownika
$sqlSongs = "SELECT ids, title, musician, filename FROM song WHERE idu = ?";
$stmt = mysqli_prepare($conn, $sqlSongs);
mysqli_stmt_bind_param($stmt, "i", $idu);
mysqli_stmt_execute($stmt);
$resultSongs = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
// Pobranie playlist: publiczne dla wszystkich + własne prywatne
$sqlPlaylists = "
    SELECT p.idpl, p.idu, p.name, p.public, p.datetime, u.username 
    FROM playlistname p
    JOIN users u ON p.idu = u.idu
    WHERE p.public = 1 OR p.idu = ?
    ORDER BY p.datetime DESC
";
$stmt = mysqli_prepare($conn, $sqlPlaylists);
mysqli_stmt_bind_param($stmt, "i", $idu);
mysqli_stmt_execute($stmt);
$resultPlaylists = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Żurek</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="twoj_css.css">
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="twoj_js.js"></script> 
</head>
<body onload="myLoadHeader()">
<div id='myHeader'></div>	
<main> 
	<section class="sekcja1">	
		<div class="container-fluid">

			<h4>Twoje piosenki</h4>
			<a href="select.php" class="btn btn-primary mb-3">Dodaj piosenkę</a>
			<table class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Tytuł</th>
						<th>Wykonawca</th>
						<th>Plik</th>
						<th>Odtwórz</th>
					</tr>
				</thead>
				<tbody>
					<?php while ($row = mysqli_fetch_assoc($resultSongs)): ?>
					<tr>
						<td><?php echo htmlspecialchars($row['title']); ?></td>
						<td><?php echo htmlspecialchars($row['musician']); ?></td>
						<td><?php echo htmlspecialchars($row['filename']); ?></td>
						<td>
							<a href="audio.php?ids=<?php echo $row['ids']; ?>" class="btn btn-success btn-sm">Odtwórz</a>
						</td>
					</tr>
					<?php endwhile; ?>
				</tbody>
			</table>
			<hr>
			<h4>Playlisty</h4>
			<a href="create_playlist.php" class="btn btn-primary mb-3">Utwórz playlistę</a>
			<table class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Nazwa playlisty</th>
						<th>Właściciel</th>
						<th>Publiczna</th>
						<th>Data utworzenia</th>
						<th>Akcje</th>
					</tr>
				</thead>
				<tbody>
					<?php while ($pl = mysqli_fetch_assoc($resultPlaylists)): ?>
					<tr>
						<td><?php echo htmlspecialchars($pl['name']); ?></td>
						<td><?php echo htmlspecialchars($pl['username']); ?></td>
						<td><?php echo $pl['public'] ? 'Tak' : 'Nie'; ?></td>
						<td><?php echo $pl['datetime']; ?></td>
						<td>
							<?php if ($pl['idu'] == $idu): ?>
								<a href="add_to_playlist.php?idpl=<?php echo $pl['idpl']; ?>" class="btn btn-primary btn-sm mt-1">Dodaj utwory</a>
							<?php else: ?>
								<span class="text-muted"></span>
							<?php endif; ?>
							<a href="play_playlist.php?idpl=<?php echo $pl['idpl']; ?>" class="btn btn-primary btn-sm mt-1">Odtwórz</a>
						</td>
					</tr>
					<?php endwhile; ?>
				</tbody>
			</table>
		</div>	
	</section>
</main>	
<?php require_once 'footer.php'; ?>	
</body>
</html>
