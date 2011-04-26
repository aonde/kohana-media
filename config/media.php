<?php defined('SYSPATH') or die('No direct script access.');

// Turn on the minimization and building in PRODUCTION environment
// $production = (Kohana::$environment === Kohana::PRODUCTION);

return array
(
	// Javascript processing
	'js' => array
	(
		// Path to the source folder
		'path'   => APPPATH.'media'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR,

		// Processing driver
		'driver' => 'Media_Javascript',

		// Minimization
		'min'    => TRUE,

		// Merging
		'merge'  => TRUE,

		// Caching
		'cache'  => FALSE
	),
	
	// Javascript processing
	'css' => array
	(
		// Path to the source folder
		'path'   => APPPATH.'media'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR,

		// Processing driver
		'driver' => 'Media_Css',

		// Minimization
		'min'    => TRUE,

		// Merging
		'merge'  => TRUE,

		// Caching
		'cache'  => FALSE
	),
);