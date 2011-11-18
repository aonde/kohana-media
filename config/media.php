<?php defined('SYSPATH') or die('No direct script access.');

// Turn on the minimization and building in PRODUCTION environment
$production = (Kohana::$environment === Kohana::PRODUCTION);

return array
(
	/**
	 * File caching flag
	 */
	'cache' => $production,

	 /**
	 * File merging flag
	 */
	'merge' => $production,

	'debug' => ! $production,

	 /**
	 * Delimiter
	 */
	'delimiter' => '--',

	 /**
	 * File filters (for example - minimizing)
	 */
	'filters' => array
	(
		'js'  => array(),
		'css' => array()
	),

	'warn_extensions' => array
	(
		'php', 'htaccess'
	),

	/**
	 * Directory to store media files
	 */
	'media_directory' => 'media',

	/**
	 * Maximal URL length
	 */
	'url_maxlength' => 256
);