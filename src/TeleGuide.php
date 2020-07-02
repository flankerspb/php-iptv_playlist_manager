<?php
// http://wiki.xmltv.org/index.php/XMLTVFormat
// https://iptvx.one/viewtopic.php?p=2239#p2239

// https://epg.it999.ru/
// https://iptvx.one/viewtopic.php?f=12&t=4

// http://www.teleguide.info/download/new3/xmltv.xml.gz
// http://epg.it999.ru/epg.xml.gz
// https://iptvx.one/EPG

class TeleGuide
{
	protected $source_file;
	
	protected $channels = [];
	
	function __construct(string $file)
	{
		$this->source_file = $file;
		
		$this->parseSource();
	}
	
	function getChannels()
	{
		return $this->channels;
	}
	
	function parseSource()
	{
		$xml = null;
		
		$handle = @gzopen($this->source_file, 'r');
		
		if($handle === false)
		{
			//TODO Message
			
			return;
		}
		
		$string = stream_get_contents($handle);
		
		gzclose($handle);
		
		if(!$string)
		{
			//TODO Message
			
			return;
		}
		
		$xml = @simplexml_load_string($string);
		
		if($xml && $xml->getName() == 'tv')
		{
			foreach($xml->channel as $channel)
			{
				$names = [];
				
				foreach($channel->{'display-name'} as $value)
				{
					$names[] = (string)$value;
				}
				
				$this->channels[] = [
					'id' => (string)$channel['id'],
					'name' => $names,
					'icon' => (string)$channel->icon['src'],
				];
			}
		}
		else
		{
			//TODO Message
		}
	}
}
