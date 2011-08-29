<?php defined('SYSPATH') or die('No direct access allowed.');

class Controller_Media extends Controller {

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

		$this->_file = $this->request->param('file');
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
			$file = APPPATH.$this->_config['media_directory'].DIRECTORY_SEPARATOR.str_replace('..', '', $file);

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

	/**
	 * Is JSmin loaded?
	 *
	 * @var boolean
	 */
	protected $_jsmin = FALSE;

	/**
	 * Minify JS source
	 *
	 * @param   string  $source  JS source
	 * @return  string  Minified source
	 */
	public function filter_jsmin($source)
	{
		if ( ! $this->_jsmin)
		{
			require_once Kohana::find_file('vendor', 'jsmin/cssmin');

			$this->_jsmin = TRUE;
		}

		return JSMin::minify($source);
	}

	/**
	 * Is CSSmin loaded?
	 *
	 * @var boolean
	 */
	protected $_cssmin = FALSE;

	/**
	 * Minify CSS source
	 *
	 * @param   string  $source  CSS source
	 * @return  string  Minified source
	 */
	public function filter_cssmin($source)
	{
		if ( ! $this->_cssmin)
		{
			require_once Kohana::find_file('vendor', 'cssmin/cssmin');

			$this->_cssmin = TRUE;
		}

		return CssMin::minify($source);
	}

	protected function _minify($filename)
	{
		$source   = file_get_contents($filename);

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		if (isset($this->_config['filters'][$extension]))
		{
			foreach($this->_config['filters'][$extension] as $filter)
			{
				$filter = 'filter_'.$filter;

				if (method_exists($this, $filter))
				{
					$source = call_user_func(array($this, $filter), $source);
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
} // End Controller_Media