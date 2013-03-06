<?php
namespace Blight;

class Template {
	const TYPE_PHP	= 'php';
	const TYPE_TWIG	= 'twig';

	/** @var \Twig_Environment	The Twig environment to use across all templates */
	static protected $twig_environment;


	/** @var \Blight\Blog */
	protected $blog;
	protected $filename;
	protected $type;

	/**
	 * Initialises the template and confirms the template file exists
	 *
	 * @param \Blight\Blog $blog
	 * @param string $name			The name of the template to use
	 * @throws \RuntimeException	Template cannot be found
	 */
	public function __construct(\Blight\Blog $blog, $name){
		$this->blog	= $blog;

		$this->locate_template($name);
	}

	/**
	 * Locates a template file with the given template name, and determines the template type
	 *
	 * @param string $name			The name of the current template
	 * @throws \RuntimeException	Template cannot be found
	 */
	protected function locate_template($name){
		$template	= $this->blog->get_path_templates($name);

		if(file_exists($template.'.php')){
			$this->filename	= $name.'.php';
			$this->type		= self::TYPE_PHP;
		} elseif(file_exists($template.'.tpl.html')){
			$this->filename	= $name.'.tpl.html';
			$this->type		= self::TYPE_TWIG;
		}

		if(!isset($this->filename)){
			// No template found
			throw new \RuntimeException('Template "'.$name.'" not found');
		}
	}

	/**
	 * Builds a template file with the provided variables, and returns the generated HTML
	 *
	 * @param array|null $params	An array of variables to be assigned to the local scope of the template
	 * @return string	The rendered content from the template
	 * @throws \RuntimeException	Template cannot be found
	 */
	public function render($params = null){
		if(!isset($params)){
			$params	= array();
		}
		if(!is_array($params)){
			throw new \InvalidArgumentException('Params must be array');
		}

		switch($this->type){
			case self::TYPE_PHP:
				return $this->render_php($params);
				break;

			case self::TYPE_TWIG:
				return $this->render_twig($params);
				break;

			default:
				// Should never happen - constructor/locate_template() will prevent this
				throw new \RuntimeException('Unknown template type rendered');
				break;
		}
	}


	/**
	 * Renders a standard PHP template file
	 *
	 * @param array $params	An array of variables to be assigned to the local scope of the template
	 * @return string		The rendered content from the template
	 */
	protected function render_php($params){
		$params['text']	= new TextProcessor($this->blog);

		extract($params);
		ob_start();
		include($this->blog->get_path_templates($this->filename));
		return ob_get_clean();
	}

	/**
	 * Renders a Twig template
	 *
	 * @param array $params	An array of variables to be assigned to the local scope of the template
	 * @return string		The rendered content from the template
	 */
	protected function render_twig($params){
		return $this->get_twig_environment()->render($this->filename, $params);
	}


	/**
	 * Retrieves the standardised Twig environment object, with the correct template and cache paths set
	 *
	 * @return \Twig_Environment	The Twig environment object
	 */
	protected function get_twig_environment(){
		if(!isset(self::$twig_environment)){
			$loader	= new \Twig_Loader_Filesystem($this->blog->get_path_templates());
			self::$twig_environment	= new \Twig_Environment($loader, array(
				'cache' => $this->blog->get_path_cache('twig/')
			));

			// Set up filters
			$blog	= $this->blog;
			$text_processor	= new TextProcessor($this->blog);

			// Markdown filter
			self::$twig_environment->addFilter(new \Twig_SimpleFilter('md', function($string, $filter_typography = true) use($blog, $text_processor){
				$filters	= array(
					'markdown'		=> true,
					'typography'	=> true
				);
				if(!$filter_typography){
					$filters['typography']	= false;
				}
				return $text_processor->process($string, $filters);
			}, array(
				'is_safe' => array('html')
			)));

			// Typography filter
			self::$twig_environment->addFilter(new \Twig_SimpleFilter('typo', array($text_processor, 'process_typography'), array(
				'pre_escape'	=> 'html',
				'is_safe'		=> array('html')
			)));

			// Truncate filter
			self::$twig_environment->addFilter(new \Twig_SimpleFilter('truncate', array($text_processor, 'truncate_html')), array(
				'is_safe'	=> array('html')
			));
		}

		return self::$twig_environment;
	}
};
