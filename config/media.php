<?php defined('SYSPATH') or die('No direct script access.');

// Turn on the minimization and building in PRODUCTION environment
$production = (Kohana::$environment === Kohana::PRODUCTION);

return array
(
	// Javascript processing
	'js' => array
	(
		'path'   => APPPATH.'media'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR,
		'type'   => 'js',
		'min'    => TRUE,
		'merge'  => TRUE,
		'cache'  => TRUE
	),

	// Css processing
	'css' => array
	(
		'path'   => APPPATH.'media'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR,
		'types'  => 'css',
		'min'    => TRUE,
		'merge'  => TRUE,
		'cache'  => TRUE
	),
);