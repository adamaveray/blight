<?php
namespace Blight;

class Template implements \Blight\Interfaces\Template {
	const TYPE_PHP	= 'php';
	const TYPE_TWIG	= 'twig';

	/** @var \Twig_Environment	The Twig environment to use across all templates */
	static protected $twig_environments	= array();


	/** @var \Blight\Interfaces\Blog */
	protected $blog;
	/** @var \Blight\Interfaces\Packages\Theme */
	protected $theme;
	protected $dir;
	protected $name;
	protected $filename;
	protected $type;

	/**
	 * Initialises the template and confirms the template file exists
	 *
	 * @param \Blight\Interfaces\Blog $blog
	 * @param string $name			The name of the template to use
	 * @param string $dir			The directory to look for templates in
	 * @throws \RuntimeException	Template cannot be found
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, \Blight\Interfaces\Packages\Theme $theme, $name, $dir = null){
		$this->blog		= $blog;
		$this->theme	= $theme;

		$this->dir	= (isset($dir) ? $dir : $theme->get_path_templates());

		$this->name	= $name;
		$this->locate_template($name);
	}

	/**
	 * Locates a template file with the given template name, and determines the template type
	 *
	 * @param string $name			The name of the current template
	 * @throws \RuntimeException	Template cannot be found
	 */
	protected function locate_template($name){
		$template	= $this->dir.$name;

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
	 * @return string				The rendered content from the template
	 * @throws \InvalidArgumentException	Invalid params provided
	 * @throws \RuntimeException			Template cannot be found
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
		$params['blog']		= $this->blog;
		$params['theme']	= $this->theme;
		$params['text']		= new TextProcessor($this->blog);

		extract($params);
		ob_start();
		include($this->dir.$this->filename);
		$output	= ob_get_clean();
		if($this->blog->get('minify_html', 'output', false)){
			$output	= $this->minify_html($output);
		}

		return $output;
	}

	/**
	 * Renders a Twig template
	 *
	 * @param array $params	An array of variables to be assigned to the local scope of the template
	 * @return string		The rendered content from the template
	 */
	protected function render_twig($params){
		$output	= $this->get_twig_environment($this->dir)->render($this->filename, $params);
		if($this->blog->get('minify_html', 'output', false)){
			$output	= $this->minify_html($output);
		}

		return $output;
	}

	/**
	 * Minifies the provided HTML by removing whitespace, etc
	 *
	 * @param string $html	The raw HTML to minify
	 * @return string		The minified HTML
	 */
	protected function minify_html($html){
		return \Minify_HTML::minify($html);
	}


	/**
	 * Retrieves the standardised Twig environment object, with the correct template and cache paths set
	 *
	 * @param string $dir	The template directory for the environment
	 * @return \Twig_Environment	The Twig environment object
	 */
	protected function get_twig_environment($dir){
		if(!isset(self::$twig_environments[$dir])){
			$loader	= new \Twig_Loader_Filesystem($dir);
			self::$twig_environments[$dir]	= new \Twig_Environment($loader);

			// Add globals
			self::$twig_environments[$dir]->addGlobal('blog', $this->blog);
			self::$twig_environments[$dir]->addGlobal('theme', $this->theme);

			// Set up functions
			self::$twig_environments[$dir]->addFunction(new \Twig_SimpleFunction('styles', array($this, 'get_styles'), array('is_safe' => array('html'))));
			self::$twig_environments[$dir]->addFunction(new \Twig_SimpleFunction('scripts', array($this, 'get_scripts'), array('is_safe' => array('html'))));

			// Set up filters
			$blog	= $this->blog;
			$text_processor	= new TextProcessor($this->blog);

			// Markdown filter
			self::$twig_environments[$dir]->addFilter(new \Twig_SimpleFilter('md', function($string, $filter_typography = true) use($blog, $text_processor){
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
			self::$twig_environments[$dir]->addFilter(new \Twig_SimpleFilter('typo', array($text_processor, 'process_typography'), array(
				'pre_escape'	=> 'html',
				'is_safe'		=> array('html')
			)));

			// Truncate filter
			self::$twig_environments[$dir]->addFilter(new \Twig_SimpleFilter('truncate', array($text_processor, 'truncate_html'), array(
				'is_safe'	=> array('html')
			)));
		}

		return self::$twig_environments[$dir];
	}


	/**
	 * @return string	The HTML for the styles
	 */
	public function get_styles(){
		$styles	= array();
		$this->blog->do_hook('render_styles', array(
			'theme'		=> $this->theme,
			'template'	=> $this,
			'name'		=> $this->name,
			'styles'	=> &$styles
		));

		return $this->build_styles_scripts($styles, 'link', 'href', true, 'style');
	}

	/**
	 * @return string	The HTML for the scripts
	 */
	public function get_scripts(){
		$scripts	= array();
		$this->blog->do_hook('render_scripts', array(
			'theme'		=> $this->theme,
			'template'	=> $this,
			'name'		=> $this->name,
			'scripts'	=> &$scripts
		));

		return $this->build_styles_scripts($scripts, 'script', 'src', false);
	}

	/**
	 * Builds the HTML tags for the provided scripts or styles
	 *
	 * @param array $items	The items to build tags for
	 * @param string $tag	The name of the external linking tag
	 * @param string $url_attr	The attribute the external tag uses to link to external items
	 * @param bool $self_closing	Whether the external tag should be self-closing (<tag />) or not (<tag></tag>)
	 * @param string|null $embedded_tag	A different tag to use for embedded items. If not set, defaults to the normal tag.
	 * @return string	The built HTML for the items
	 */
	protected function build_styles_scripts($items, $tag, $url_attr = 'href', $self_closing = true, $embedded_tag = null){
		if(!isset($embedded_tag)){
			$embedded_tag	= $tag;
		}

		$output	= array();
		foreach($items as $item){
			$external	= true;
			if(!is_array($item)){
				if(preg_match('#(((https?|ftp)://([\w-\d]+\.)+[\w-\d]+){0,1}(/[\w~,;\-\./?%&+\#=]*))#i', $item)){
					// External
					$item	= array(
						$url_attr	=> $item
					);
				} else {
					// Embedded
					$external	= false;
				}
			}

			if($external){
				$line	= '<'.$tag;
				foreach($item as $attr => $val){
					$line	.= ' '.$attr.'="'.$val.'"';
				}
				if($self_closing){
					$line	.= ' />';
				} else {
					$line	.= '></'.$tag.'>';
				}

			} else {
				// Embedded
				$line	= '<'.$embedded_tag.'>'.$item.'</'.$embedded_tag.'>';
			}

			$output[]	= $line;
		}

		return implode("\n", $output);
	}
};
