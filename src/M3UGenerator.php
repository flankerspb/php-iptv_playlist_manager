<?php

class M3UGenerator
{
	const PRESETS = [
		'default' => 'default',
		'kodi1' => 'kodi 1',
		'kodi2' => 'kodi 2',
		'ssiptv' => 'ss-iptv',
	];
	
	
	public static function getPreset(string $preset) : string
	{
		return array_key_exists($preset, self::PRESETS) ? $preset : 'default';
	}
	
	
	public static function run(array $data) : array
	{
		$method = method_exists(__CLASS__, $data['preset']) ? $data['preset'] : 'default';
		
		$data['channels'] = array_filter($data['channels'], function($value)
		{
			return ($value['status'] == 'on' && $value['src']);
		});
		
		return self::$method($data);
	}
	
	
	private static function default(array $data) : array
	{
		$result = [];
		
		$result[] = '#EXTM3U ' . self::buildParamsString($data['params'], ' ', '=', '"');
		
		foreach($data['channels'] as $channel)
		{
			$result[] = '#EXTINF:-1 ' . self::buildParamsString($channel['attribs'], ' ', '=', '"'). ',' . $channel['name'];
			
			// if(count($channel['params']))
			// {
				// $result[] = self::buildParamsString($channel['params'], PHP_EOL, '=', '');
			// }
			
			$result[] = $channel['src'];
		}
		
		return $result;
	}
	
	
	private static function kodi1(array $data) : array
	{
		$result = [];
		
		$result[] = '#EXTM3U ' . self::buildParamsString($data['params'], ' ', '=', '"');
		
		foreach($data['channels'] as $channel)
		{
			$result[] = '#EXTINF:-1 ' . self::buildParamsString($channel['attribs'], ' ', '=', '"'). ',' . $channel['name'];
			
			// if(count($channel['params']))
			// {
				// $result[] = self::buildParamsString($channel['params'], PHP_EOL, '=', '');
			// }
			
			$result[] = $channel['src'] . '|User-agent=SmartSDK';
		}
		
		return $result;
	}
	
	
	private static function kodi2(array $data) : array
	{
		$result = [];
		
		$result[] = '#EXTM3U ' . self::buildParamsString($data['params'], ' ', '=', '"');
		
		foreach($data['channels'] as $channel)
		{
			$result[] = '#EXTINF:-1 ' . self::buildParamsString($channel['attribs'], ' ', '=', '"'). ',' . $channel['name'];
			
			// if(count($channel['params']))
			// {
				// $result[] = self::buildParamsString($channel['params'], PHP_EOL, '=', '');
			// }
			
			$result[] = '#EXTVLCOPT:http-user-agent=SmartSDK';
			$result[] = $channel['src'];
		}
		
		return $result;
	}
	
	
	private static function ssiptv(array $data) : array
	{
		return self::default($data);
	}
	
	
	public static function buildParamsString(array $array, string $out_glue, string $in_glue, string $wrap) : string
	{
		$array = array_filter($array);
		
		return implode($out_glue, array_map(
				function($key, $value) use ($in_glue, $wrap)
				{
					return $key.$in_glue.$wrap.$value.$wrap;
				},
				array_keys($array),
				$array
		));
	}
}
