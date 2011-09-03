<?php defined('SYSPATH') or die('No direct access allowed.');

class Kohana_Media_Js implements Kohana_Media_Interface {

	/**
	 * Is JSmin loaded?
	 *
	 * @var boolean
	 */
	protected static $_loaded = FALSE;

	/**
	 * Minify JS code
	 *
	 * @param   string  $source  JS code
	 * @return  string  Minified JS code
	 */
	public static function filter($source)
	{
		if ( ! self::$_loaded)
		{
			require_once Kohana::find_file('vendor', 'jsmin/jsmin');

			self::$_loaded = TRUE;
		}

		return str_replace(array("\n", "\r"), '', JSMin::minify($source));
	}

	public static function externals(array $externals)
	{
		$content = '';

		foreach($externals as $external)
		{
			$content .= HTML::script($external);
		}

		return $content;
	}

	/**
	 * Generates tag to load media files into HTML page
	 *
	 * @param   array  $files  Files array
	 * @return  string  HTML data
	 */
	public static function files(array $files, array $mtimes)
	{
		$delimiter        = Kohana::$config->load('media')->delimiter;
		$media_directory = DOCROOT.Kohana::$config->load('media')->media_directory.DIRECTORY_SEPARATOR;

		if (Kohana::$config->load('media')->merge)
		{
			$file  = implode($delimiter, $files);
			$mtime = max($mtimes);

			if (is_file($media_directory.$file))
			{
				if (filemtime($media_directory.$file) < $mtime)
				{
					unlink($media_directory.$file);
				}
			}

			return HTML::script(Route::get('media')->uri(array('file' => $file, 'mtime' => $mtime)));
		}

		$content = '';

		foreach($files as $file)
		{
			$mtime = $mtimes[$file];

			if (is_file($media_directory.$file))
			{
				if (filemtime($media_directory.$file) < $mtime)
				{
					unlink($media_directory.$file);
				}
			}

			$content .= HTML::script(Route::get('media')->uri(array('file' => $file, 'mtime' => $mtime)));
		}

		return $content;
	}

	/**
	 * Generates tag with source
	 *
	 * @param   array  $source  Source array
	 * @return  string  HTML data
	 */
	public static function source(array $source)
	{
		$content = '';

		foreach($source as $s)
		{
			$content .= self::filter($s);
		}

		return '<script type="text/javascript">'.$content.'</script>';
	}

} // End Kohana_Media_Js