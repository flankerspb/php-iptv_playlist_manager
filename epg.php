<?php
// test/xmltv_channels.xml.gz
// test/xmltv_channels.xml
// test/xmltv.xml

require 'src/_autoload.php';

$epg_src = $_POST['epg_src'] ?? '';

$channels = $epg_src ? (new TeleGuide($epg_src))->getChannels() : [];
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title>EPG Channels List :: IPTV</title>
<meta name="description" content="">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.js"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.js"></script>
<link href="assets/style.css" rel="stylesheet">
<script src="assets/script.js"></script>
</head>
<body>
	<center>
		<h1>EPG Channels List</h1>
		<hr>
			<form class="form-inline" method="post">
				<input type="text" name="epg_src" class="form-control" placeholder="EPG source file" value="<?php echo $epg_src; ?>" required>
				<input class="btn btn-primary" type="submit" value="View"/>
			</form>
		<hr>
		<div class="form-inline">
			<input class="form-control" id="search" type="text" placeholder="Search..">
		</div>
		<br>
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
						<th>Icon</th>
						<th>Display name</th>
						<th>ID</th>
				</tr>
			</thead>
			<tbody id="channels">
				<?php foreach($channels as $i => $channel) :  ?>
				<tr>
					<td>
					<?php if($channel['icon']) :  ?>
					<img src="<?php echo $channel['icon']; ?>" height="32"/>
					<?php endif ; ?>
					</td>
					<td><b><?php echo implode('<br>', $channel['name']); ?></b></td>
					<td><?php echo $channel['id']; ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</center>
</body>
<script>
$(document).ready(function(){
  $("#search").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#channels tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});
</script>
</html>