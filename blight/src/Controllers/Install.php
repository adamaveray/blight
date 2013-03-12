<?php
namespace Blight\Controllers;

class Install {
	protected $root_path;
	protected $app_path;
	protected $web_path;
	protected $template_dir;
	
	protected $url_base	= 'index.php?/install/';

	protected $config_file;

	public function __construct($root_path, $app_path, $web_path, $file){
		session_start();

		$this->root_path	= $root_path;
		$this->app_path		= $app_path;
		$this->web_path		= $web_path;
		$this->template_dir	= $this->app_path.'src/views/install/';

		$this->config_file	= $file;
	}

	public function setup(){
		$dir	= $this->app_path.'src/views/';
		$files	= array(
			'_common/js/jquery-1.9.1.min.js'	=> 'js/jquery-1.9.1.min.js',
			'install/js/install.js'		=> 'js/install.js',
			'install/css/install.css'	=> 'css/install.css'
		);

		foreach($files as $source => $target){
			$source	= $dir.$source;
			$target	= $this->web_path.$target;
			$target_dir	= dirname($target);

			if(!is_dir($target_dir)){
				mkdir($target_dir);
			}
			file_put_contents($target, file_get_contents($source));
		}
	}

	public function teardown(){
	}

	protected function session_set($name, $value){
		$name	= explode('/', $name);
		$levels	= count($name);
		$array	= &$_SESSION;

		for($i = 0; $i < $levels; $i++){
			$level	= $name[$i];
			if($i == $levels-1){
				$array[$level]	= $value;
			} else {
				if(!isset($array[$level])){
					$array[$level]	= array();
				}

				$array	= &$array[$level];
			}
		}
	}

	public function get_page($uri){
		$fragments	= explode('/', $uri);
		array_shift($fragments);
		array_shift($fragments);
		array_shift($fragments);


		if(!isset($fragments[0]) || $fragments[0] == ''){
			// Start
			$this->setup();

			$_SESSION	= array();
			$this->page_step_start();
			return;
		}

		switch($fragments[0]){
			case '1':
				if($_SERVER['REQUEST_METHOD'] == 'POST'){
					$this->process_step_1($_POST);
				} else {
					$this->page_step_1();
				}
				break;

			case '2':
				if($_SERVER['REQUEST_METHOD'] == 'POST'){
					$this->process_step_2($_POST);
				} else {
					$this->page_step_2();
				}
				break;

			case '3':
				if($_SERVER['REQUEST_METHOD'] == 'POST'){
					$this->process_step_3($_POST);
				} else {
					$this->page_step_3();
				}
				break;

			case 'end':
				$this->page_step_end();
				break;
		}
	}

	protected function redirect($location){
		$location	= $this->url_base.$location;
		header('HTTP/1.1 302 Found');
		header('Location: '.$location);
		exit;
	}

	protected function valid_redirect($errors, $valid_url, $invalid_url){
		if(count($errors) === 0){
			$_SESSION['install_errors']	= null;
			$this->redirect($valid_url);
		} else {
			$_SESSION['install_errors']	= $errors;
			$this->redirect($invalid_url);
		}
	}

	protected function render_view($path, $params){
		$params['errors']	= isset($_SESSION['install_errors']) ? $_SESSION['install_errors'] : array();

		extract($params);
		ob_start();
		include($this->template_dir.$path);
		return ob_get_clean();
	}

	public function page_step_start(){
		echo $this->render_view('start.php', array(
			'title'			=> 'Install Blight',
			'target_url'	=> $this->url_base.'1'
		));
	}

	public function page_step_end(){
		// Setup finished - save config
		$result	= $this->run_install($_SESSION);
		if(!$result){
			// Could not save setup
			$this->redirect('failure');
		}

		// Site setup

		session_destroy();

		echo $this->render_view('end.php', array(
			'title'		=> 'Blight Installed',
			'prev_url'	=> $this->url_base.'3'
		));
	}

	public function page_step_1(){
		echo $this->render_view('1.php', array(
			'title'			=> 'About You',
			'target_url'	=> $this->url_base.'1',
			'prev_url'		=> '/'
		));
	}

	protected function process_step_1($data){
		$errors	= array();

		if(isset($data['author_name']) && trim($data['author_name']) != ''){
			$this->session_set('author/name', $data['author_name']);
		} else {
			$errors['author_name']	= true;
		}

		if(isset($data['author_email']) && filter_var($data['author_email'], \FILTER_VALIDATE_EMAIL)){
			$this->session_set('author/email', $data['author_email']);
		} else {
			$errors['author_email']	= true;
		}

		$this->valid_redirect($errors, '2', '1');
	}

	public function page_step_2(){
		echo $this->render_view('2.php', array(
			'title'			=> 'About Your Site',
			'target_url'	=> $this->url_base.'2',
			'prev_url'		=> $this->url_base.'1'
		));
	}

	protected function process_step_2($data){
		$errors	= array();

		if(isset($data['site_name']) && trim($data['site_name']) != ''){
			$this->session_set('site/name', $data['site_name']);
		} else {
			$errors['site_name']	= true;
		}

		if(isset($data['site_url'])){
			$url	= rtrim($data['site_url'], '/').'/';
			if(!preg_match('/^https?:\/\//', $url)){
				$url	= 'http://'.$url;
			}
		}

		if(isset($data['site_url']) && filter_var($data['site_url'], \FILTER_VALIDATE_URL)){
			$this->session_set('site/url', $url);
		} else {
			$errors['site_url']	= true;
		}

		if(isset($data['site_description'])){
			// Not required
			$this->session_set('site/description', $data['site_description']);
		}


		$linkblog	= false;
		if(isset($data['linkblog'])){
			$linkblog	= (bool)$data['linkblog'];
		}
		$this->session_set('linkblog/linkblog', $linkblog);

		if($linkblog){
			if(isset($data['linkblog_post_character'])){
				$this->session_set('linkblog/post_character', $data['linkblog_post_character']);
			}
		} else {
			if(isset($data['linkblog_link_character'])){
				$this->session_set('linkblog/link_character', $data['linkblog_link_character']);
			}
		}

		$this->valid_redirect($errors, '3', '2');
	}

	public function page_step_3(){
		echo $this->render_view('3.php', array(
			'title'			=> 'Paths',
			'target_url'	=> $this->url_base.'3',
			'prev_url'		=> $this->url_base.'2'
		));
	}

	protected function process_step_3($data){
		$errors	= array();

		$prefix	= 'path_';
		$paths	= array(
			'pages'			=> 'pages',
			'posts'			=> 'posts',
			'drafts'		=> 'drafts',
			'templates'		=> 'templates',
			'web'			=> 'web',
			'drafts_web'	=> 'drafts-web',
			'cache'			=> 'cache',
		);
		foreach($paths as $key => $name){
			$key	= $prefix.$key;

			$path	= rtrim($data[$key], '/');
			if(!is_dir($this->root_path.$path)){
				$result	= mkdir($this->root_path.$path, 0777, true);
				if(!$result){
					$errors[$key]	= 'Cannot create directory';
					continue;
				}
			}
			if(!is_writeable($this->root_path.$path)){
				$result	= chmod($this->root_path.$path, 0777);
				if(!$result){
					$errors[$key]	= 'Cannot write to directory';
					continue;
				}
			}

			$this->session_set('paths/'.$name, $path.'/');
		}


		$this->valid_redirect($errors, 'end', '3');
	}

	protected function copy_dir($source, $target){
		$source	= rtrim($source, '/');
		$target	= rtrim($target, '/');
		if(!is_dir($source)){
			throw new \RuntimeException('Source directory not found');
		}
		if(!is_dir($target)){
			$result	= mkdir($target, 0777, true);
			if(!$result){
				throw new \RuntimeException('Target directory cannot be created');
			}
		}

		$files	= glob($source.'/*');
		foreach($files as $file){
			$basename	= basename($file);
			if(is_dir($file)){
				$this->copy_dir($file, $target.'/'.$basename);
			} else {
				file_put_contents($target.'/'.$basename, file_get_contents($file));
			}
		}
	}

	protected function run_install($config){
		// Make directories
		foreach($config['paths'] as $dir){
			$dir	= $this->root_path.$dir;
			if(!is_dir($dir)){
				mkdir($dir, 0777, true);
			}
		}

		$template_dir	= $this->root_path.$config['paths']['templates'];

		if(count(glob($template_dir.'*')) === 0){
			// Set up default templates
			$this->copy_dir($this->root_path.'default-templates/', $template_dir);
		}

		// Copy .htaccess
		$htaccess	= file_get_contents($this->app_path.'src/default.htaccess');

		$web_dir		= explode('/', rtrim($config['paths']['web'],'/'));
		$htaccess_dir	= $this->root_path.implode('/', array_slice($web_dir, 0, -1));

		$common_www_dirs	= array('www', 'public_html', 'htdocs');
		foreach($common_www_dirs as $dir){
			if($web_dir[0] === $dir){
				// Found - replace only instance at start
				$web_dir	= array_slice($web_dir, 1);
				break;
			}
		}
		$web_dir	= implode('/', $web_dir);
		$htaccess	= str_replace('{%WEB_PATH%}', rtrim($web_dir, '/'), $htaccess);

		file_put_contents(rtrim($htaccess_dir,'/').'/.htaccess', $htaccess);

		// Write config file
		file_put_contents($this->config_file, $this->build_setup($_SESSION));

		return true;
	}

	protected function build_setup($config){
		$parser	= new \Blight\Config();
		return $parser->serialize($config);
	}
};
