<?php defined('SYSPATH') or die('No direct script access.');

Route::set('media', 'media/<environment>/<file>', array('file' => '.*'))
	->defaults(array(
		'controller' => 'media',
		'action'     => 'index'
	));
