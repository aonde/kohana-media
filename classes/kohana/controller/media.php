<?php defined('SYSPATH') or die('No direct access allowed.');

class Kohana_Controller_Media extends Controller {

	/**
	 * Config array
	 *
	 * @var array
	 */
	protected $_config = 'media';

	/**
	 * Filename to load
	 *
	 * @var string
	 */
	protected $_file;

	public function before()
	{
		parent::before();

		// Attach configuration
		$this->_config = Kohana::$config->load($this->_config)
			->as_array();

		$this->_file = str_replace('..', '', $this->request->param('file'));
		$action = explode('/', $this->_file);

		if (is_array($action))
		{
			$action = $action[0];

			if (method_exists($this, 'action_'.$action))
			{
				$this->request->action($action);
			}
		}
	}

	public function action_plain()
	{
		$files = explode($this->_config['delimiter'], $this->_file);

		$source = '';

		foreach($files as $file)
		{
			$file = APPPATH.$this->_config['media_directory'].DIRECTORY_SEPARATOR.$file;

			if ( ! file_exists($file))
			{
				return $this->response
					->status(404);
			}

			$source .= $this->_minify($file);
		}

		if ($this->_config['cache'])
		{
			$this->_cache($this->_file, $source);
		}

		$this->response
			->headers('content-type', File::mime_by_ext(pathinfo($this->_file, PATHINFO_EXTENSION)))
			->body($source);
	}

	protected function _minify($filename)
	{
		$source   = file_get_contents($filename);

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		if (isset($this->_config['filters'][$extension]))
		{
			foreach($this->_config['filters'][$extension] as $filter)
			{
				$filter = 'Media_'.$filter;

				if(class_exists($filter))
				{
					$source = call_user_func($filter.'::filter', $source);
				}
			}
		}

		return $source;
	}

	protected function _cache($file, $source)
	{
		$this->_make_directory(pathinfo($file, PATHINFO_DIRNAME));

		file_put_contents(DOCROOT.$this->_config['public_directory'].DIRECTORY_SEPARATOR.$file, $source);
	}

	protected function _make_directory($dirname)
	{
		$dirs = explode('/', $dirname);

		$path = DOCROOT.$this->_config['public_directory'];

		foreach($dirs as $dir)
		{
			$path .= DIRECTORY_SEPARATOR.$dir;

			if ( ! is_dir($path))
			{
				mkdir($path);
			}
		}
	}

} // End Kohana_Controller_Media