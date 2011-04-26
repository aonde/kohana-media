<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * @author Alexey Popov
 */
class Kohana_Media {

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
		$config = Kohana::config('media');

		if ( ! isset($config[$name]))
		{
			throw new Kohana_Exception('Could not find configuration for environment :name; check configuration file :file', array(
				':environment' => $name,
				':file'        => 'media'.EXT
			));
		}

		$config = $config[$name];

		return new $config['driver']($name, $config);
	}

} // End Media