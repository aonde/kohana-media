<?php defined('SYSPATH') or die('No direct script access.');

$public_directory = Kohana::$config->load('media')->public_directory;

Route::set('media', $public_directory.'/<file>(?<mtime>)', array('file' => '.+', 'mtime' => '%d'))
	->defaults(array(
		'controller' => 'media',
		'action'     => 'plain'
	));
