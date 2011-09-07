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
		$files   = explode($this->_config['delimiter'], $this->_file);

		$sources = array();

		foreach($files as $file)
		{
			if ($file = $this->_check_path($file))
			{
				if ( ! in_array($file, $sources))
				{
					$sources[] = $file;
				}
			}
		}

		$this->_source($sources);
	}

	protected function _source(array $files, $optional_content = NULL)
	{
		if (sizeof($files) == 0)
		{
			return $this->response
				->status(404);
		}

		$source = '';

		foreach($files as $file)
		{
			$source .= $this->_minify($file);
		}

		if ($this->_config['cache'])
		{
			$this->_cache($this->_file, $source);
		}

		$this->response
			->headers('content-type', File::mime_by_ext(pathinfo($this->_file, PATHINFO_EXTENSION)))
			->body($optional_content.$source);
	}

	/**
	 * Directories array to find static media files
	 *
	 * @var array
	 */
	protected $_paths = FALSE;

	protected function _init_paths()
	{
		if (is_array($this->_paths))
		{
			return;
		}

		$this->_paths = array();

		$this->_add_path($this->_config['media_directory']);
	}

	protected function _add_path($path)
	{
		if ( ! $this->_paths)
		{
			$this->_init_paths();
		}

		$this->_paths[] = $path;

		return $this;
	}

	protected function _check_path($filename)
	{
		if ( ! $this->_paths)
		{
			$this->_init_paths();
		}

		$ext = pathinfo($filename, PATHINFO_EXTENSION);

		if (in_array($ext, $this->_config['warn_extensions']))
		{
			return FALSE;
		}

		foreach($this->_paths as $path)
		{
			foreach(Kohana::include_paths() as $include_path)
			{
				$dir = realpath($include_path.DIRECTORY_SEPARATOR.$path);

				if ($dir)
				{
					if (strpos($file = realpath($dir.DIRECTORY_SEPARATOR.$filename), $dir) === 0 AND is_file($file))
					{
						return $file;
					}
				}
			}
		}

		return FALSE;
	}

	protected function _minify($filename)
	{
		$source = file_get_contents($filename);

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

		if ($this->_config['debug'] AND in_array($extension, $this->_config['filters']))
		{
			return "\n\n/* ".$filename." */\n\n".$source."\n\n";
		}

		return $source;
	}

	protected function _cache($file, $source)
	{
		$this->_make_directory(pathinfo($file, PATHINFO_DIRNAME));

		file_put_contents(DOCROOT.$this->_config['media_directory'].DIRECTORY_SEPARATOR.$file, $source);
	}

	protected function _make_directory($dirname)
	{
		$dirs = explode('/', $dirname);

		$path = DOCROOT.$this->_config['media_directory'];

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