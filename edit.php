<?php

use HtmlHelper as HTML;

session_start();

require 'src/_autoload.php';

$list = null;

if(isset($_GET['name']) && $_GET['name'])
{
	$list = Playlist::load($_GET['name']);
	
	if(!$list)
	{
		header('Location: ' . dirname($_SERVER['SCRIPT_NAME']));
		exit();
	}
}
else
{
	header('Location: ' . dirname($_SERVER['SCRIPT_NAME']));
	exit();
}

if(count($_POST) && isset($_POST['name']) && $_POST['name'] == $_GET['name'])
{
	$list->save($_POST);
	
	header('Location: ' . $_SERVER['REQUEST_URI']);
	exit();
}

$preset = M3UGenerator::getPreset($list->getPreset());

$groups = $list->getGroups();
$groups_flip = array_flip($groups);

$messages = Informer::getAllMessages();

?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title>Playlist editor :: IPTV</title>
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
		<h1>Playlist editor</h1>
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
		
		<h3>Playlist: "<?php echo $list->name; ?>"</h3>
		
		<form method="post">
			<input type="hidden" name="name" value="<?php echo $list->name; ?>">
			<div>
				<table class="table table-condensed">
					<tbody>
						<tr>
							<td><label>Source file</label></td>
							<td>
								<?php  echo HTML::input('hidden', 'source_file', $list->getSourceFile()); ?>
								<span><?php echo $list->getSourceFile(); ?></span>
							</td>
						</tr>
						<tr>
							<td><label>Link</label></td>
							<td><?php echo PLAYLISTS_URI . '/' . $list->name . '.m3u'; ?></td>
						</tr>
						<tr>
							<td><label>Source channels</label></td>
							<td>
								<?php echo $list->getCountChannels('source'); ?>
							</td>
						</tr>
						<tr>
							<td><label>New channels</label></td>
							<td>
								<?php echo $list->getCountChannels('new'); ?>
							</td>
						</tr>
						<tr>
							<td><label>Missing channels</label></td>
							<td>
								<?php echo $list->getCountChannels('missing'); ?>
							</td>
						</tr>
						<tr>
							<td><label>ON channels</label></td>
							<td>
								<?php echo $list->getCountChannels('on'); ?>
							</td>
						</tr>
						<tr>
							<td><label>OFF channels</label></td>
							<td>
								<?php echo $list->getCountChannels('off'); ?>
							</td>
						</tr>
						<tr>
							<td><label>Preset</label></td>
							<td>
								<?php echo HTML::select('preset', M3UGenerator::PRESETS, $preset); ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<hr>
			<table class="table table-condensed">
				<thead>
					<tr>
						<th>Param</th>
						<th>Source param</th>
						<th>Playlist param</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($list->getParams() as $key => $value) : ?>
						<tr>
							<td><label><?php echo $key; ?>:</label></td>
							<td><?php echo $list->getSourceParams()[$key] ?? '-----'; ?></td>
							<td><?php echo HTML::input('text', 'params['.$key.']', $value); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			
			<ul class="nav nav-tabs nav-centered">
				<li role="navigation" class="active" data-group="-1"><a>All</a></li>
				<?php foreach($groups as $key => $value) : ?>
				<li role="navigation" class="sortable" data-group="<?php echo $key; ?>"><a>
					<span class="glyphicon glyphicon-menu-hamburger"></span>
					<?php echo $value ? $value : 'Ungrouped'; ?>
					<?php  echo HTML::input('hidden', 'group_names['.$key.']', $value); ?>
				</a></li>
				<?php endforeach; ?>
			</ul>
			<table class="table table-striped table-hover table-condensed" id="channels">
				<thead>
					<tr>
							<th>Sort</th>
							<th>On</th>
							<th>Name</th>
							<th>group-title</th>
							<th>tvg-name</th>
							<th>tvg-id</th>
							<th>tvg-logo</th>
					</tr>
				</thead>
				<tbody class="sortable">
					<?php foreach($list->getChannelsList() as $i => $channel) : 
						
						$group_id = ($groups_flip[$channel['attribs']['group-title']] ?? 0);
						
						$row_class = 'group-'.$group_id;
						
						switch($channel['status'])
						{
							case 'on':
								// $row_class = '';
								$checked = 'checked';
								$disabled = '';
								break;
							case 'new':
								$row_class .= ' info text-info';
								$checked = '';
								$disabled = '';
								break;
							case 'off':
								$row_class .= ' muted text-muted';
								$checked = '';
								$disabled = '';
								break;
							case 'missing':
								$row_class .= isset($channel['state']) ? ' danger text-danger' : ' warning text-muted';
								$checked = isset($channel['state']) ? 'checked' : '';
								$disabled = isset($channel['state']) ? '' : 'disabled';
								break;
							default:
								$row_class .= ' warning text-warning group-';
								$checked = '';
								$disabled = 'disabled';
								break;
						}
						
						$t_name = 'channels['.$i.']';
					?>
						<tr class="<?php echo $row_class; ?>">
							<td class="handle">
								<span class="glyphicon glyphicon-menu-hamburger"></span>
							</td>
							<td>
								<?php echo HTML::input('checkbox', $t_name.'[state]', $checked, $disabled); ?>
								<?php echo HTML::input('hidden', $t_name.'[status]', $channel['status']); ?>
							</td>
							<td data-toggle="tooltip" title="<?php echo $channel['src']; ?>">
								<?php echo HTML::input('hidden', $t_name.'[src]', $channel['src']); ?>
								<?php echo HTML::input('hidden', $t_name.'[source_name]', $channel['source_name']); ?>
								
								<?php echo HTML::label($t_name.'[state]', $channel['source_name']); ?>
								<br>
								<?php echo HTML::input('text', $t_name.'[name]', $channel['name']); ?>
							</td>
							
							<?php
								foreach($channel['attribs'] as $k => $v)
								{
									echo '<td>';
									echo '<span  class="attr-label">';
									echo $channel['source_attribs'][$k] ?? '-----';
									echo '</span>';
									echo HTML::input('text', $t_name.'[attribs]['.$k.']', $v);
									echo '</td>';
									
								}
							?>
							
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			
			<p>
				<input class="btn btn-primary" type="submit" value="Save"/>
			</p>
		</form>
	</center>
</body>
</html>