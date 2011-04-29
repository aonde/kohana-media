<?php defined('SYSPATH') or die('No direct access allowed.');

class Controller_Media extends Controller {

	public function action_index($environment)
	{
		// Attach configuration
		$config = Kohana::config('media');

		// Get the file path from the request
		$file = $this->request->param('file');

		if ( ! isset($config[$environment]))
		{
			return $this->response->status(404);
		}

		$config = $config[$environment];

		$files = explode(Media::$delimiter, $file);

		if ( ! is_array($files))
		{
			$files = array($files);
		}

		$media = Media::instance($environment);

		// Set the proper headers to allow caching
		$this->response->headers('content-type', File::mime_by_ext(pathinfo($file, PATHINFO_EXTENSION)));

		// Send the file content as the response
		$this->response->body($media->minify_files($file));
	}

} // End Controller_Media