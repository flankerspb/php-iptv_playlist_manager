<?php
//http://free-iptv.ru/iptv/0703tppz/pl_zabava.m3u
//http://iptv.my/test/pl_zabava.m3u

use HtmlHelper as HTML;

session_start();

require 'src/_autoload.php';

if(count($_GET))
{
	if(isset($_GET['action']))
	{
		$method = $_GET['action'] . 'Playlist';
		
		switch($method)
		{
			case 'createPlaylist' :
			case 'removePlaylist' :
				PlaylistManager::$method($_GET);
				break;
		}
	}
	
	header('Location: ' . dirname($_SERVER['SCRIPT_NAME']));
	exit();
}

$playlists = PlaylistManager::getPlaylists();

$messages = Informer::getAllMessages();

?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title>Playlist manager :: IPTV</title>
<meta name="description" content="">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.js"></script>
<link href="assets/style.css" rel="stylesheet">
<script src="assets/script.js"></script>
</head>
<body>
	<center>
		<h1>Playlist manager</h1>
		<hr>
		
		<?php if($messages) : ?>
			<div>
			<?php foreach($messages as $type => $msgs) : ?>
				<div class="alert alert-<?php echo $type; ?>">
					<?php foreach($msgs as  $msg)
					{
						echo '<p>' . $msg . '</p>';
					}
					?>
				</div>
			<?php endforeach; ?>
			</div>
		<?php endif; ?>
		
		<?php if(count($playlists)) : ?>
		<h3>Playlists</h3>
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
						<th>#</th>
						<th>Name</th>
						<th>Source</th>
						<th>Link</th>
						<th>Preset</th>
						<th>Edit</th>
						<th>Remove</th>
				</tr>
			</thead>
			<tbody>
			<?php 
						$i = 0;
						foreach($playlists as $name => $item) :
						$i++;
						$row_class = $item['source_exists'] ? '' : 'danger';
						?>
				<tr>
					<td><?php echo $i; ?>.</td>
					<td><b><?php echo $name; ?></b></td>
					<td class="<?php echo $row_class; ?>"><?php echo $item['source_file']; ?></td>
					<td><?php echo $item['uri']; ?></td>
					<td><?php echo $item['preset']; ?></td>
					<td><a class="btn btn-primary" href="/edit.php?name=<?php echo $name; ?>"><span class="glyphicon glyphicon-edit"></span></a></td>
					<td><a class="btn btn-danger" href="?action=remove&name=<?php echo $name; ?>"><span class="glyphicon glyphicon-remove"></span></a></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<hr>
		<?php endif; ?>
		<form class="form-inline" method="get">
			<h3>Create Playlist</h3>
			<input type="hidden" name="action" value="create">
			<input type="text" name="name" class="form-control" placeholder="name" required>
			<input type="text" name="source_file" class="form-control" placeholder="source" required>
			<input class="btn btn-primary" type="submit" value="Create"/>
		</form>
	</center>
</body>
</html>