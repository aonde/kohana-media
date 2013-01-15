<?php defined('SYSPATH') or die('No direct access allowed.');

interface Kohana_Media_Interface {

	/**
	 * Minify static source
	 *
	 * @param   string  $source  Source code
	 * @return  string  Minified source code
	 */
	public static function filter($source);

	/**
	 * Generates tag to load media files into HTML page
	 *
	 * @param   array  $files  Files array
	 * @return  string  HTML data
	 */
	public static function files(array $files, array $mtimes);

	/**
	 * Generates tag with source
	 *
	 * @param   array  $source  Source array
	 * @return  string  HTML data
	 */
	public static function source(array $source);

} // End Kohana_Media_Interface