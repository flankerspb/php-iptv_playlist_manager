<?php

class Informer
{
	//Add Messages
	
	private static function addMessage($text, $type)
	{
		$_SESSION['pls_informer'][$type][] = $text;
	}
	
	public static function addInfo($text)
	{
		self::addMessage($text, 'info');
	}
	
	public static function addSuccess($text)
	{
		self::addMessage($text, 'success');
	}
	
	public static function addWarning($text)
	{
		self::addMessage($text, 'warning');
	}
	
	public static function addError($text)
	{
		self::addMessage($text, 'danger');
	}
	
	//Get Messages
	
	private static function getMessages($type)
	{
		$result = $_SESSION['pls_informer'][$type] ?? [];
		
		unset($_SESSION['pls_informer'][$type]);
		
		return $result ?? [];
	}
	
	public static function getAllMessages()
	{
		$result = $_SESSION['pls_informer'] ?? [];
		
		unset($_SESSION['pls_informer']);
		
		return $result;
	}
	
	public static function getInfo($type)
	{
		self::get('info');
	}
	
	public static function getSuccess($type)
	{
		self::get('success');
	}
	
	public static function getWarning($type)
	{
		self::get('warning');
	}
	
	public static function getError($type)
	{
		self::get('error');
	}
	
	//Count Messages
	
	public static function countMessages($type = null)
	{
		if($type)
		{
			return count($_SESSION['pls_informer'][$type]);
		}
		else
		{
			return count($_SESSION['pls_informer'], COUNT_RECURSIVE);
		}
	}
}
