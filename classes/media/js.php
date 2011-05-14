<?php defined('SYSPATH') or die('No direct access allowed.');

class Media_JS extends Media {

	private $_jsmin = FALSE;

	protected function _tag_file($filename, array $attributes = NULL)
	{
		return HTML::script($filename, $attributes);
	}

	protected function _tag_source($text, array $attributes = NULL)
	{
		$attributes['type'] = 'text/javascript';

		return '<script'.HTML::attributes($attributes).'>'.$text.'</script>';
	}

	protected function _minify($text)
	{
		if ($this->_config['min'] == FALSE) return $text;

		if ($this->_jsmin === FALSE)
		{
			require_once Kohana::find_file('vendor', 'jsmin/jsmin');
		}

		return JSMin::minify($text);
	}

} // End Media_JS