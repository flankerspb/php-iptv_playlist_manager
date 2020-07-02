<?php

class HtmlHelper
{
	public static function label(string $for, string $name) : string
	{
		return "<label for='{$for}' class='control-label'>{$name}</label>";
	}
	
	
	public static function input(string $type, string $name, ?string $value = '', ?string $disabled = '') : string
	{
		$html = '';
		
		switch($type)
		{
			case 'hidden':
				$html = "<input type='hidden' name='{$name}' value='{$value}' >";
				break;
			case 'text':
				$html = "<input type='text' name='{$name}' id='{$name}' value='{$value}' class='form-control' >";
				break;
			case 'checkbox':
				$disabled = $value ? '' : $disabled;
				$html = "<input type='checkbox' name='{$name}' id='{$name}' {$value} {$disabled} >";
				break;
		}
		
		
		return $html;
	}
	
	
	public static function select(string $name, array $values, string $value) : string
	{
		$result = [];
		$result[] = "<select class='form-control' name='{$name}'>";
		
		foreach($values as $k => $v)
		{
			$selected = ($value == $k) ? ' selected' : '';
			
			$result[] = "<option value='{$k}'{$selected}>{$v}</option>";
		}
		
		$result[] = '</select>';
		
		return implode('', $result);
	}
}
