<?php defined('SYSPATH') or die('No direct script access.');

// Turn on the minimization and building in PRODUCTION environment
$production = (Kohana::$environment === Kohana::PRODUCTION);

return array
(
	// Javascript processing
	'js' => array
	(
		'path'   => APPPATH.'media'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR,
		'driver' => 'Media_Javascript',
		'types'  => array('js'),
		'min'    => TRUE,
		'merge'  => TRUE,
		'cache'  => Kohana::$caching
	),

	// Javascript processing
	'css' => array
	(
		'path'   => APPPATH.'media'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR,
		'driver' => 'Media_Css',
		'types'  => array('css'),
		'min'    => TRUE,
		'merge'  => TRUE,
		'cache'  => Kohana::$caching
	),
);