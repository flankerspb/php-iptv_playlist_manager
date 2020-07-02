<?php

class PlaylistManager
{
	public static function createPlaylist(array $data) : bool
	{
		$source = self::getPlaylistSource($data['source_file']);
		
		if(!$source)
		{
			return false;
		}
		
		try
		{
			if(preg_match("/[^a-zA-Z0-9\-_]+/", $data['name']))
			{
				throw new Exception('Name contains invalid chars. Only allowed: a-z, A-Z, 0-9, -, _');
			}
			
			$m3u_file = self::getFile($data['name'], 'm3u');
			$json_file = self::getFile($data['name'], 'json');
			
			if(is_file($m3u_file) && is_file($json_file))
			{
				throw new Exception('Playlist "' . $data['name'] . '" already exists!');
			}
			// else(is_file($m3u_file) || is_file($json_file))
			// {
			// }
			else
			{
				$json = Playlist::SETTINGS;
				$json['source_file'] = $data['source_file'];
				
				$isCreateJSON = self::saveJSON($data['name'], $json);
				$isCreateM3U = self::saveM3U($data['name'], $source);
				
				if(!$isCreateJSON || !$isCreateM3U)
				{
					if(is_file($json_file)) unlink($json_file);
					if(is_file($m3u_file)) unlink($m3u_file);
					
					throw new Exception('Creating "' . $data['name'] . '" playlist Error!');
				}
			}
		}
		catch(Exception $e)
		{
			
			Informer::addError($e->getMessage());
			
			return false;
		}
		
		Informer::addInfo('Created playlist: ' . $data['name']);
		
		return true;
	}
	
	
	public static function removePlaylist(array $data) : void
	{
		if(preg_match("/[^a-zA-Z0-9\-_]+/", $data['name']))
		{
			Informer::addError('Wrong playlist name!');
			
			return;
		}
		
		$json_file = self::getFile($data['name'], 'json');
		$m3u_file = self::getFile($data['name'], 'm3u');
		
		if(is_file($json_file)) unlink($json_file);
		if(is_file($m3u_file)) unlink($m3u_file);
		
		Informer::addInfo('Playlist deleted: ' . $data['name']);
	}
	
	
	public static function saveJson(string $name, array $json) : bool
	{
		$file = self::getFile($name, 'json');
		
		$str = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		
		return file_put_contents($file, $str);
	}
	
	public static function saveM3u(string $name, array $data) : bool
	{
		$file = self::getFile($name, 'm3u');
		
		return file_put_contents($file, implode(PHP_EOL, $data));
	}
	
	
	public static function getFile(string $name, string $ext) : string
	{
		return PLAYLISTS_PATH . '/' . $name . '.' . $ext;
	}
	
	
	public static function getPlaylists() : array
	{
		$list = [];
		
		$list_item = [
			'uri' => null,
			'm3u_file' => null,
			'json_file' => null,
			'source_file' => null,
			'source_exists' => null,
			'preset' => 'default',
			// 'channels' => null,
		];
		
		foreach(glob(PLAYLISTS_PATH . '/' . '*.m3u') as $file)
		{
			$pathinfo = pathinfo($file);
			
			$item = $list_item;
			$item['uri'] = PLAYLISTS_URI . '/' . $pathinfo['basename'];
			$item['m3u_file'] = $file;
			
			
			$list[$pathinfo['filename']] = $item;
		}
		
		foreach(glob(PLAYLISTS_PATH . '/' . '*.json') as $file)
		{
			$name = pathinfo($file, PATHINFO_FILENAME);
			
			if(!isset($list[$name]))
			{
				$list[$name] = $list_item;
			}
			
			$settings = self::getPlaylistSettings($file);
			
			$source_exists = self::getPlaylistSource($settings['source_file']) ? true : false;
			
			$list[$name]['json_file'] = $file;
			$list[$name]['source_file'] = $settings['source_file'];
			$list[$name]['source_exists'] = $source_exists;
			$list[$name]['preset'] = $settings['preset'] ?? 'default';
		}
		
		ksort($list, SORT_NATURAL);
		
		return $list;
	}
	
	
	public static function getPlaylistSettings(string $file) : array
	{
		return json_decode(file_get_contents($file), true);
	}
	
	
	public static function getPlaylistSource(string $file) : ?array
	{
		$source = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		
		try
		{
			if($source)
			{
				if(strpos($source[0], '#EXTM3U') === 0)
				{
					return $source;
				}
				else
				{
					throw new Exception('Wrong source file format');
				}
			}
			else
			{
				throw new Exception('Failed to open source playlist: ' . $file);
			}
		}
		catch(Exception $e)
		{
			Informer::addError($e->getMessage());
			
			return null;
		}
	}
}
