<?php defined('SYSPATH') or die('No direct access allowed.');

class Kohana_Media {

	/**
	 * Instances array
	 *
	 * @var array
	 */
	protected static $_instances = array();

	/**
	 * Singleton method
	 *
	 * @param   string  $instance  Instance name
	 * @return  Media
	 */
	public static function instance($instance)
	{
		if (isset(self::$_instances[$instance]))
		{
			return self::$_instances[$instance];
		}

		$config = Kohana::$config->load('media')
			->as_array();

		/**
		 * It makes sense to handle only static CSS and JS files.
		 */
		if ( ! isset($config['filters'][$instance]))
		{
			throw new Kohana_Exception(__('Can\'t process instance :instance', array(
				':instance' => $instance
				)));
		}

		return new Media($config, $instance);
	}

	/**
	 * Config
	 *
	 * @var array
	 */
	protected $_config;

	/**
	 * Instance name
	 *
	 * @var string
	 */
	protected $_instance;

	/**
	 * Path to source files
	 *
	 * @var string
	 */
	protected $_path;

	public function __construct(array $config, $instance)
	{
		$this->_config   = $config;
		$this->_instance = $instance;

		$this->_path     = APPPATH.$config['media_directory'].DIRECTORY_SEPARATOR;
	}

	/**
	 * Priorities
	 */
	const PRIORITY_LOW      = 40;
	const PRIORITY_MEDIUM   = 30;
	const PRIORITY_HIGH     = 20;
	const PRIORITY_CRITICAL = 10;

	/**
	 * Files to minify
	 *
	 * @var array
	 */
	protected $_files = array();

		/**
	 * Last modification times data array
	 *
	 * @var array
	 */
	protected $_mtimes = array();

	/**
	 * External files array
	 *
	 * @var array
	 */
	protected $_externals = array();

	/**
	 * Add static file to minify
	 *
	 * @param    string   $file       Filename
	 * @param    integer  $priority   Order priority
	 * @return   Media
	 */
	public function add_file($filename, $priority = Media::PRIORITY_MEDIUM)
	{
		// Who knows
		$filename = $this->_clean_filename($filename);

		// Is it an external static file?
		if (strpos($filename, '://') !== FALSE)
		{
			$this->_externals[$priority][] = $filename;

			return $this;
		}

		if (is_file($this->_path.$filename) AND
			pathinfo($filename, PATHINFO_EXTENSION) == $this->_instance)
		{
			$this->_files[ (int) $priority][] = $filename;
			$this->_mtimes[$filename]         = filemtime($this->_path.$filename);
		}

		return $this;
	}

	/**
	 * Source array
	 *
	 * @var array
	 */
	protected $_source = array();

	/**
	 * Loads source code
	 *
	 * @param   string  $source    Source
	 * @param   int     $priority  Priority
	 * @return  Media
	 */
	public function add_source($source, $priority = Media::PRIORITY_MEDIUM)
	{
		$this->_source[ (int) $priority][] = $source;

		return $this;
	}

	/**
	 * Cleans all data
	 *
	 * @return Media
	 */
	public function clean()
	{
		$this->_files =
			$this->_externals =
			$this->_mtimes =
			$this->_source = array();

		return $this;
	}

	/**
	 * Renders data
	 *
	 * @return string
	 */
	public function html()
	{
		$content = '';

		$processor = 'Media_'.$this->_instance;

		if (sizeof($this->_externals) > 0)
		{
			ksort($this->_externals, SORT_NUMERIC);

			$this->_externals = call_user_func_array('array_merge', $this->_externals);

			$content .= call_user_func($processor.'::externals', $this->_externals);
		}

		if (sizeof($this->_files) > 0)
		{
			ksort($this->_files, SORT_NUMERIC);

			$this->_files = call_user_func_array('array_merge', $this->_files);

			$content .= call_user_func_array($processor.'::files', array($this->_files, $this->_mtimes));
		}

		if (sizeof($this->_source) > 0)
		{
			ksort($this->_source, SORT_NUMERIC);

			$this->_source = call_user_func_array('array_merge', $this->_source);

			$content .= call_user_func($processor.'::source', $this->_source);
		}

		$this->clean();

		return $content;
	}

	/**
	 * That method:
	 *   - cleans file path to protect against access
	 *     to files above the level of current media directory
	 *   - removes http:// from local URIs
	 *
	 * @param   string  $filename  Filename
	 * @return  string  Cleaned filename
	 */
	protected function _clean_filename($filename)
	{
		$find = array
		(
			'..',
			URL::site(NULL, TRUE)
		);

		$replace = '';

		return str_replace($find, $replace, $filename);
	}

	public function __toString()
	{
		return $this->html();
	}

} // End Kohana_Media