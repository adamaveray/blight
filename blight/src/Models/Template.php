<?php
namespace Blight\Models;

class Template implements \Blight\Interfaces\Models\Template {
	const TYPE_PHP	= 'php';
	const TYPE_TWIG	= 'twig';

	/** @var \Twig_Environment	The Twig environment to use across all templates */
	static protected $twigEnvironments	= array();


	/** @var \Blight\Interfaces\Blog */
	protected $blog;
	/** @var \Blight\Interfaces\Models\Packages\Theme */
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
	public function __construct(\Blight\Interfaces\Blog $blog, \Blight\Interfaces\Models\Packages\Theme $theme, $name, $dir = null){
		$this->blog		= $blog;
		$this->theme	= $theme;

		$this->dir	= (isset($dir) ? $dir : $theme->getPathTemplates());

		$this->name	= $name;
		$this->locateTemplate($name);
	}

	/**
	 * Locates a template file with the given template name, and determines the template type
	 *
	 * @param string $name			The name of the current template
	 * @throws \RuntimeException	Template cannot be found
	 */
	protected function locateTemplate($name){
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
				$output	= $this->renderPHP($params);
				break;

			case self::TYPE_TWIG:
				$output	= $this->renderTwig($params);
				break;

			default:
				// Should never happen - constructor/locateTemplate() will prevent this
				throw new \RuntimeException('Unknown template type rendered');
				break;
		}

		if($this->blog->get('minify_html', 'output', false)){
			$textProcessor	= new \Blight\TextProcessor($this->blog);
			$output	= $textProcessor->minifyHTML($output);
		}
		return $output;
	}


	/**
	 * Renders a standard PHP template file
	 *
	 * @param array $params	An array of variables to be assigned to the local scope of the template
	 * @return string		The rendered content from the template
	 */
	protected function renderPHP($params){
		$params['blog']		= $this->blog;
		$params['theme']	= $this->theme;
		$params['text']		= new \Blight\TextProcessor($this->blog);

		extract($params);
		ob_start();
		include($this->dir.$this->filename);
		return ob_get_clean();
	}

	/**
	 * Renders a Twig template
	 *
	 * @param array $params	An array of variables to be assigned to the local scope of the template
	 * @return string		The rendered content from the template
	 */
	protected function renderTwig($params){
		return $this->getTwigEnvironment($this->dir)->render($this->filename, $params);
	}


	/**
	 * Retrieves the standardised Twig environment object, with the correct template and cache paths set
	 *
	 * @param string $dir	The template directory for the environment
	 * @return \Twig_Environment	The Twig environment object
	 */
	protected function getTwigEnvironment($dir){
		if(!isset(self::$twigEnvironments[$dir])){
			$loader	= new \Twig_Loader_Filesystem($dir);
			$twig	= new \Twig_Environment($loader, array(
				'debug'	=> $this->blog->isDebug(),
				'cache' => ($this->blog->get('cache_twig', 'output', false) ? $this->blog->getPathCache('twig/') : null)
			));
			if($this->blog->isDebug()){
				$twig->addExtension(new \Twig_Extension_Debug());
			}
			$twig->getExtension('core')->setTimezone($this->blog->get('timezone', 'site', 'UTC'));

			// Add globals
			$twig->addGlobal('blog', $this->blog);
			$twig->addGlobal('theme', $this->theme);

			// Set up functions
			$twig->addFunction(new \Twig_SimpleFunction('styles', array($this, 'getStyles'), array('is_safe' => array('html'))));
			$twig->addFunction(new \Twig_SimpleFunction('scripts', array($this, 'getScripts'), array('is_safe' => array('html'))));

			// Set up filters
			$blog	= $this->blog;
			$textProcessor	= new \Blight\TextProcessor($this->blog);

			// Markdown filter
			$twig->addFilter(new \Twig_SimpleFilter('md', function($string, $filterTypography = true) use($blog, $textProcessor){
				$filters	= array(
					'markdown'		=> true,
					'typography'	=> true
				);
				if(!$filterTypography){
					$filters['typography']	= false;
				}
				return $textProcessor->process($string, $filters);
			}, array(
				'is_safe' => array('html')
			)));

			// Typography filter
			$twig->addFilter(new \Twig_SimpleFilter('typo', array($textProcessor, 'processTypography'), array(
				'pre_escape'	=> 'html',
				'is_safe'		=> array('html')
			)));

			// Truncate filter
			$twig->addFilter(new \Twig_SimpleFilter('truncate', array($textProcessor, 'truncateHTML'), array(
				'is_safe'	=> array('html')
			)));

			self::$twigEnvironments[$dir]	= $twig;
		}

		return self::$twigEnvironments[$dir];
	}


	/**
	 * @return string	The HTML for the styles
	 */
	public function getStyles(){
		$styles	= array();
		$this->blog->doHook('render_styles', array(
			'theme'		=> $this->theme,
			'template'	=> $this,
			'name'		=> $this->name,
			'styles'	=> &$styles
		));

		return $this->buildStylesScripts($styles, 'link', 'href', true, 'style');
	}

	/**
	 * @return string	The HTML for the scripts
	 */
	public function getScripts(){
		$scripts	= array();
		$this->blog->doHook('render_scripts', array(
			'theme'		=> $this->theme,
			'template'	=> $this,
			'name'		=> $this->name,
			'scripts'	=> &$scripts
		));

		return $this->buildStylesScripts($scripts, 'script', 'src', false);
	}

	/**
	 * Builds the HTML tags for the provided scripts or styles
	 *
	 * @param array $items	The items to build tags for
	 * @param string $tag	The name of the external linking tag
	 * @param string $urlAttr	The attribute the external tag uses to link to external items
	 * @param bool $selfClosing	Whether the external tag should be self-closing (<tag />) or not (<tag></tag>)
	 * @param string|null $embeddedTag	A different tag to use for embedded items. If not set, defaults to the normal tag.
	 * @return string	The built HTML for the items
	 */
	protected function buildStylesScripts($items, $tag, $urlAttr = 'href', $selfClosing = true, $embeddedTag = null){
		if(!isset($embeddedTag)){
			$embeddedTag	= $tag;
		}

		$output	= array();
		foreach($items as $item){
			$external	= true;
			if(!is_array($item)){
				if(preg_match('#(((https?|ftp)://([\w-\d]+\.)+[\w-\d]+){0,1}(/[\w~,;\-\./?%&+\#=]*))#i', $item)){
					// External
					$item	= array(
						$urlAttr	=> $item
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
				if($selfClosing){
					$line	.= ' />';
				} else {
					$line	.= '></'.$tag.'>';
				}

			} else {
				// Embedded
				$line	= '<'.$embeddedTag.'>'.$item.'</'.$embeddedTag.'>';
			}

			$output[]	= $line;
		}

		return implode("\n", $output);
	}
};
