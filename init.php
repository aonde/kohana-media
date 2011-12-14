<?php defined('SYSPATH') or die('No direct script access.');

Route::set('media', 'media/<environment>/<file>(?<mtime>)', array('file' => '.+', 'mtime' => '%d'))
	->defaults(array(
		'controller' => 'media',
		'action'     => 'index'
	));
