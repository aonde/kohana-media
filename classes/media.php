<?php defined('SYSPATH') or die('No direct access allowed.');

class Media extends Kohana_Media {
    
	/**
	 * Get a singleton Media instance.
	 *
	 *     $media = Media::instance('js');
	 *
	 * @param   string  instance name
	 * @return  Media
	 */
	public static function instance($name)
	{
		// v3.1 $config = Kohana::config('media');
         $config = Kohana::$config->load('media');
        

		if ( ! isset($config[$name]))
		{
			throw new Kohana_Exception('Couldn\'t find configuration for environment :environment; check configuration file :file', array(
				':environment' => $name,
				':file'        => 'media'.EXT
			));
		}

		$config   = $config[$name];

		$class    = 'Media_'.UTF8::ucfirst($name);
		$filename = UTF8::str_ireplace('_', DIRECTORY_SEPARATOR, UTF8::strtolower($name));
    
		if ( ! (Kohana::find_file('classes', 'media'.DIRECTORY_SEPARATOR.$filename)))
		{
			throw new Kohana_Exception('Couldn\'t find media driver :class for environment :environment', array(
				':environment' => $name,
				':class'       => $class
			));
		}

		return new $class($name, $config);
	}    
}