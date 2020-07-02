<?php

class Playlist
{
	const SETTINGS = [
		'source_file' => '',
		'preset' => 'default',
		'params' => [
			'url-tvg' => null,
			// 'tvg-logo' => null,
			'tvg-shift' => null,
			'cache' => null,
			'deinterlace' => null,
			// 'aspect-ratio' => null,
			// 'croppadd' => null,
		],
		'channels' => [],
	];
	
	const CHANNEL_ATTRIBS = [
		'group-title' => null, // ??? group-name vs group-title
		'tvg-name' => null,
		'tvg-id' => null,
		'tvg-logo' => null,
		// 'channel-id' => null,
		// 'radio' => null,
		// 'tvg-shift' => null,
		// 'aspect-ratio' => null,
	];
	
	// const CHANNEL_PARAMS = [
		// 'EXTGRP' => null,
		// 'EXTVLCOPT' => null,
	// ];
	
	const CHANNEL = [
		'status' => null,
		'source_name' => null,
		'name' => null,
		'src' => null,
		'source_attribs' => self::CHANNEL_ATTRIBS,
		'attribs' => self::CHANNEL_ATTRIBS,
		// 'source_params' => [],
		// 'params' => self::CHANNEL_PARAMS,
	];
	
	public $name;
	
	protected $settings;
	
	protected $params = [];
	protected $channels = [];
	protected $channel_groups = [];
	
	protected $source_params = [];
	protected $source_channels = [];
	protected $source_channel_groups = [];
	
	protected $count_channels = [
		'source' => 0,
		'new' => 0,
		'on' => 0,
		'off' => 0,
		'missing' => 0,
	];
	
	public static function load(string $name) : ?self
	{
		$playlist_file = PlaylistManager::getFile($name, 'm3u');
		$settings_file = PlaylistManager::getFile($name, 'json');
		
		if(is_file($playlist_file) && is_file($settings_file))
		{
			return new Playlist($name);
		}
		
		return null;
	}
	
	protected function __construct(string $name)
	{
		$this->name = $name;
		
		$this->playlist_file = PlaylistManager::getFile($name, 'm3u');
		$this->settings_file = PlaylistManager::getFile($name, 'json');
		$this->settings = PlaylistManager::getPlaylistSettings($this->settings_file);
		
		$this->parseSource();
		
		$this->bindPlaylist();
		$this->bindParams();
	}
	
	
	public function getSourceFile() : string
	{
		return $this->settings['source_file'];
	}
	
	public function getPreset() : string
	{
		return $this->settings['preset'] ?? 'default';
	}
	
	public function getSourceParams() : array
	{
		return $this->source_params;
	}
	
	public function getParams() : array
	{
		return $this->params;
	}
	
	public function getGroups() : array
	{
		return $this->channel_groups;
	}
	
	public function getChannelsList() : array
	{
		return $this->channels;
	}
	
	public function getCountChannels(string $type) : int
	{
		return $this->count_channels[$type] ?? 0;
	}
	
	
	protected function bindParams() : void
	{
		$this->params = array_merge(self::SETTINGS['params'], $this->settings['params']);
		
		$this->params = self::bindData($this->source_params, $this->params);
	}
	
	
	protected function parseSource() : void
	{
		$source = PlaylistManager::getPlaylistSource($this->settings['source_file']);
		
		if(!$source)
		{
			return;
		}
		
		//parse params
		$params =[];
		
		preg_match_all('/(?P<prop>[-a-z]+)=\"(?P<val>[^"]+)"|,(?P<name>.*)$/', $source[0], $attribs);
		
		foreach($attribs[0] as $k => $v)
		{
			if($attribs['prop'][$k])
			{
				$params[$attribs['prop'][$k]] = $attribs['val'][$k];
			}
			else if($attribs['name'][$k])
			{
				$params['name'] = $attribs['name'][$k];
			}
		}
		
		$this->source_params = array_merge(self::SETTINGS['params'], $params);
		
		
		//parse channels
		$channel_name = '';
		
		$count = count($source);
		
		for($i = 1; $i < $count; $i++)
		{
			if($source[$i][0] == '#')
			{
				$split = strpos($source[$i], ':');
				
				$key = substr($source[$i], 1, $split - 1);
				$value = str_replace('#'.$key.':', '', $source[$i]);
				
				switch($key)
				{
					case 'EXTINF':
						
						$this->count_channels['source'] += 1;
						
						preg_match_all('/(?P<attr>[-a-z]+)=\"(?P<val>[^"]+)"|,(?P<name>.*)$/', $source[$i], $attribs);
						
						$source_attribs = [];
						
						foreach($attribs[0] as $k => $v)
						{
							if($attribs['attr'][$k])
							{
								$source_attribs[$attribs['attr'][$k]] = $attribs['val'][$k];
							}
							else if($attribs['name'][$k])
							{
								$channel_name = $this->getSourceChannelName($attribs['name'][$k]);
							}
						}
						
						$this->source_channels[$channel_name]['source_name'] = $channel_name;
						$this->source_channels[$channel_name]['source_attribs'] = $source_attribs;
						
						$group = $source_attribs['group-title'] ?? '';
						
						if(!in_array($group, $this->source_channel_groups))
						{
							$this->source_channel_groups[] = $group;
						}
						
						break;
						
					case 'PLAYLIST':
						break;
					
					case 'EXTVLCOPT':
						
						// $attr = explode('=', $value);
						// $this->source_channels[$channel_name]['props'][$attr[0]] = $attr[1];
						
						// break;
					
					case 'EXTGRP':
					case 'EXTALB':
					case 'EXTART':
					case 'EXTGENRE':
					case 'EXTM3A':
					case 'EXTBYT':
					case 'EXTBIN':
					case 'EXTENC':
						
						// $this->source_channels[$channel_name]['source_params'][$key] = $value;
						
						break;
				}
			}
			else
			{
				$this->source_channels[$channel_name]['src'] = $source[$i];
			}
		}
	}
	
	
	protected function getSourceChannelName(string $name) : string
	{
		static $i = 1;
		
		$new_name = ($i == 1) ? $name : $name . " [{$i}]";
		
		if(array_key_exists($new_name, $this->source_channels))
		{
			$i++;
			return $this->getSourceChannelName($name);
		}
		else
		{
			$i = 1;
			return $new_name;
		}
	}
	
	
	protected function bindPlaylist() : void
	{
		$source_channels = $this->source_channels;
		
		//existing channels
		foreach($this->settings['channels'] as $key => &$value)
		{
			//update channel structure
			$value = self::bindData(self::CHANNEL, $value);
			
			// channel is ok
			if(array_key_exists($key, $source_channels))
			{
				//update channel
				$value = self::bindData($value, $source_channels[$key]);
				
				if($value['status'] == 'missing')
				{
					$value['status'] = $value['state'] ?? 'off';
				}
				
				unset($source_channels[$key]);
			}
			// channel is missing
			else
			{
				$value['status'] = 'missing';
				$value['src'] = null;
			}
			
			$this->count_channels[$value['status']] += 1;
			
			$group = $value['attribs']['group-title'] ?? '';
			
			if(!in_array($group, $this->channel_groups))
			{
				$this->channel_groups[] = $group;
			}
		}
		
		//new channels
		foreach($source_channels as &$value)
		{
			$this->count_channels['new'] += 1;
			
			//update channel structure
			$value = self::bindData(self::CHANNEL, $value);
			
			$value['status'] = 'new';
			$value['name'] = trim($value['source_name']);
			$value['attribs'] = $value['source_attribs'];
		}
		
		$this->channels = array_values(array_merge($source_channels, $this->settings['channels']));
	}
	
	
	public function save(array $data) : void
	{
		// delete unuseble data
		unset($data['name']);
		unset($data['groups']);
		
		// needed for sorting channels by groups
		$groups = array_fill_keys($data['group_names'], []);
		
		// delete unuseble data
		unset($data['group_names']);
		
		$list_on = $groups;
		$list_off = $groups;
		
		array_walk_recursive($data, function(&$value, $key)
		{
			if($key != 'source_name') //keep channel key
			{
				$value = trim($value);
			}
		});
		
		foreach($data['channels'] as $key => $channel)
		{
			$channel['name'] = $channel['name'] ? $channel['name'] : trim($channel['source_name']);
			
			$group = $channel['attribs']['group-title'] ?? '';
			
			$key = $channel['source_name'];
			
			// Channel is ON
			if(isset($channel['state']))
			{
				if($channel['status'] != 'missing')
				{
					$channel['status'] = 'on';
				}
				
				$list_on[$group][$key] = $channel;
			}
			else
			{
				//Ignore if channel is OFF and MISSING
				
				if($channel['status'] != 'missing')
				{
					$channel['status'] = 'off';
					$list_off[$group][$key] = $channel;
				}
			}
		}
		
		$list_on = array_merge(...array_values($list_on));
		$list_off = array_merge(...array_values($list_off));
		
		$data['channels'] = array_merge($list_on, $list_off);
		
		PlaylistManager::saveJson($this->name, $data);
		
		PlaylistManager::saveM3u($this->name, M3UGenerator::run($data));
	}
	
	
	protected static function bindData(array $array1, array $array2) : array
	{
		$merged = $array1;
		
		foreach($array2 as $key => $value)
		{
			if(is_array($value) && isset($merged[$key]) && is_array($merged[$key]))
			{
				$merged[$key] = self::bindData($merged[$key], $value);
			}
			else
			{
				$merged[$key] = $value ?? $merged[$key]; //replace if value is null (not set yet)
			}
		}
		return $merged;
	}
}
