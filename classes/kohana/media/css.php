<?php defined('SYSPATH') or die('No direct access allowed.');

class Kohana_Media_Css extends Media {

	private $_cssmin = FALSE;

	protected function _tag_file($filename, array $attributes = NULL)
	{
		return HTML::style($filename, $attributes);
	}

	protected function _tag_source($text, array $attributes = NULL)
	{
		$attributes['type'] = 'text/css';

		return '<style'.HTML::attributes($attributes).'>'.$text.'</style>';
	}

	protected function _minify($text)
	{
		if ($this->_cssmin === FALSE)
		{
			require_once Kohana::find_file('vendor', 'cssmin/cssmin');
		}

		return CssMin::minify($text);
	}

} // End Kohana_Media_Css