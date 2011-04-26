<?php defined('SYSPATH') or die('No direct access allowed.');

class Kohana_Media_Minifier {

	/**
	 * Files to minify
	 *
	 * @var array
	 */
	protected $_files = array();

	/**
	 * @var array
	 */
	protected $_external = array();

	/**
	 * Inline codes to minify
	 *
	 * @var type
	 */
	protected $_source = array();

	/**
	 * Config object
	 *
	 * @var array
	 */
	protected $_config;

	/**
	 * @var string
	 */
	protected $_delimiter = '!!!!!';

	/**
	 * Class constructor
	 *
	 * @param array $config
	 */
	public function __construct($instance, array $config)
	{
		$this->_instance = $instance;
		$this->_config   = $config;
	}

	public function add_file($filename, array $attributes = NULL)
	{
		if (UTF8::stristr($filename, '://'))
		{
			$this->_external[$filename] = $attributes;

			return $this;
		}

		$this->_files[$filename] = $attributes;

		return $this;
	}

	public function add_source($text, array $attributes = NULL)
	{
		$this->_source[] = array
		(
			'text'       => $text,
			'attributes' => $attributes
		);

		return $this;
	}

	public function get_files()
	{
		$content = '';

		if ( (bool) $this->_config['merge'] === TRUE)
		{
			$files[implode($this->_delimiter, array_keys($this->_files))] = NULL;
		}
		else
		{
			$files = $this->_files;
		}

		foreach ($files as $file => $attributes)
		{
			$content .= $this->_tag_file(Route::get('media')->uri(array(
				'environment' => $this->_instance,
				'file'        => $file
			)), $attributes);
		}

		foreach ($this->_external as $file => $attributes)
		{
			$content .= $this->_tag_file($file, $attributes);
		}

		return $content;
	}

	public function get_source()
	{
		$content = '';

		foreach ($this->_source as $source)
		{
			$content .= $this->_tag_source($this->_minify($source['text']), $source['attributes']);
		}

		return $content;
	}

	public function get_all()
	{
		return $this->get_files().$this->get_source();
	}

	public function minify_files($filename)
	{
		$files = explode($this->_delimiter, $filename);

		if ( ! is_array($files))
		{
			$files = array($files);
		}

		$content = '';

		foreach ($files as $file)
		{
			if (($file = Kohana::find_file($this->_config['path'], $file, FALSE)) === TRUE)
			{
				$content .= file_get_contents($file);
			}
		}

		return $this->_minify($content);
	}

	protected function _tag_file($filename, array $attributes = NULL)
	{
		return $filename;
	}

	protected function _tag_source($text, array $attributes = NULL)
	{
		return $text;
	}

	protected function _minify($text)
	{
		return $text;
	}

	function __toString()
	{
		return $this->get_all();
	}

} // End Kohana_Media_Minifier