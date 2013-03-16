<?php
namespace Blight\Controllers;

class Install {
	const COOKIE_NAME	= 'blightinstall';
	const DRAFT_PUBLISH_DIR	= '_publish';

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
		setcookie(self::COOKIE_NAME, null, time()-3600, '/');

		$root_dir	= rtrim($this->web_path,'/').'/';
		$dirs	= array(
			'js',
			'css'
		);

		foreach($dirs as $dir){
			$this->delete_dir($root_dir.$dir);
		}
	}

	protected function session_set_config($name, $value){
		$name	= explode('/', $name);
		$levels	= count($name);
		if(!isset($_SESSION['config'])){
			$_SESSION['config']	= array();
		}
		$array	= &$_SESSION['config'];

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
			unset($_SESSION['install_errors']);
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

	protected function copy_dir($source, $target){
		$source	= rtrim($source, '/');
		$target	= rtrim($target, '/');
		if(!is_dir($source)){
			throw new \RuntimeException('Source directory not found');
		}
		if(!is_dir($target)){
			$result	= mkdir($target, 0755, true);
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

		return true;
	}

	protected function delete_dir($dir){
		$dir	= rtrim($dir, '/');
		if(!is_dir($dir)){
			return;
		}

		$files	= glob($dir.'/*');
		foreach($files as $file){
			if(is_dir($file)){
				$this->delete_dir($file);
			} else {
				unlink($file);
			}
		}

		$files	= array('.DS_Store','.htaccess');
		foreach($files as $file){
			if(is_file($dir.'/'.$file)){
				unlink($dir.'/'.$file);
			}
		}

		rmdir($dir);
	}

	protected function run_install($config){
		$feedback	= array();

		// Make directories
		$paths	= array(
			'pages'			=> 'blog-data/pages',
			'posts'			=> 'blog-data/posts',
			'drafts'		=> 'blog-data/drafts',
			'templates'		=> 'blog-data/templates',
			'plugins'		=> 'blog-data/plugins',
			'web'			=> 'www/_blog',
			'drafts-web'	=> 'www/_drafts',
			'cache'			=> 'cache',
		);
		if(!isset($config['paths'])){
			$config['paths']	= array();
		}
		foreach($paths as $config_name => $dir){
			$config['paths'][$config_name]	= $dir.'/';

			$dir	= $this->root_path.$dir;
			if(!is_dir($dir)){
				$result	= mkdir($dir, 0755, true);
				if(!$result){
					// Cannot create directory
					if(!isset($feedback['paths'])){
						$feedback['paths']	= array();
					}
					$feedback['paths'][]	= $config['paths'][$config_name];
				}
			} elseif(!is_writable($dir)){
				$result	= chmod($dir, 0755);
				if(!$result){
					// Cannot write to directory
					$feedback['paths'][]	= $config['paths'][$config_name];
				}
			}
		}

		// Create drafts publish dir
		$drafts_dir	= $this->root_path.$config['paths']['drafts'];
		mkdir($drafts_dir.self::DRAFT_PUBLISH_DIR, 0755, true);

		$template_dir	= $this->root_path.$config['paths']['templates'];

		if(count(glob($template_dir.'*')) === 0){
			// Set up default templates
			try {
				$result	= $this->copy_dir($this->root_path.'default-templates/', $template_dir);
			} catch(\Exception $e){
				$result	= false;
			}
			if(!$result){
				return false;
			}
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
		$htaccess_path	= rtrim($htaccess_dir,'/').'/.htaccess';

		$result	= file_put_contents($htaccess_path, $htaccess);
		if(!$result){
			// Cannot write .htaccess
			$feedback['file_htaccess']		= $htaccess;
			$feedback['file_htaccess_path']	= $htaccess_path;
		}

		if(count($feedback) > 0){
			return $feedback;
		}

		if(!isset($config['output'])){
			$config['output']	= array();
		}
		$config['output']	= array_merge(array(
			'minify_html'	=> false
		), $config['output']);

		if(!isset($config['posts'])){
			$config['posts']	= array();
		}
		$config['posts']	= array_merge(array(
			'default_extension'	=> 'md',
			'allow_txt'	=> false
		), $config['posts']);


		// Write config file
		$config_text	= $this->build_setup($config);
		$result	= file_put_contents($this->config_file, $config_text);
		if(!$result){
			$feedback['file_config']		= $config_text;
			$feedback['file_config_path']	= $this->config_file;
			return $feedback;
		}

		return true;
	}

	protected function build_setup($config){
		$parser	= new \Blight\Config();
		return $parser->serialize($config);
	}


	// Start
	public function page_step_start(){
		echo $this->render_view('start.php', array(
			'title'			=> 'Install Blight',
			'target_url'	=> $this->url_base.'1'
		));
	}

	// Step 1
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
			$this->session_set_config('author/name', $data['author_name']);
		} else {
			$errors['author_name']	= true;
		}

		if(isset($data['author_email']) && filter_var($data['author_email'], \FILTER_VALIDATE_EMAIL)){
			$this->session_set_config('author/email', $data['author_email']);
		} else {
			$errors['author_email']	= true;
		}

		$this->valid_redirect($errors, '2', '1');
	}

	// Step 2
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
			$this->session_set_config('site/name', $data['site_name']);
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
			$this->session_set_config('site/url', $url);
		} else {
			$errors['site_url']	= true;
		}

		if(isset($data['site_description'])){
			// Not required
			$this->session_set_config('site/description', $data['site_description']);
		}


		$linkblog	= false;
		if(isset($data['linkblog'])){
			$linkblog	= (bool)$data['linkblog'];
		}
		$this->session_set_config('linkblog/linkblog', $linkblog);

		if($linkblog){
			if(isset($data['linkblog_post_character'])){
				$this->session_set_config('linkblog/post_character', $data['linkblog_post_character']);
			}
		} else {
			if(isset($data['linkblog_link_character'])){
				$this->session_set_config('linkblog/link_character', $data['linkblog_link_character']);
			}
		}
		$this->session_set_config('linkblog/link_directory', null);

		$this->valid_redirect($errors, 'end', '2');
	}

	// End
	public function page_step_end(){
		setcookie(self::COOKIE_NAME, '1', null, '/');

		// Setup finished - save config
		$result	= $this->run_install($_SESSION['config']);
		if($result !== true){
			// Could not save setup
			$this->page_step_end_failure($result);
			return;
		}

		// Site setup

		session_destroy();

		echo $this->render_view('end.php', array(
			'title'		=> 'Blight Installed',
			'prev_url'	=> $this->url_base.'3'
		));
	}

	// End - Failure
	public function page_step_end_failure($params){
		echo $this->render_view('failure.php', array_merge(array(
			'title'			=> 'Cannot Install Automatically',
			'target_url'	=> $this->url_base.'end',
			'prev_url'		=> $this->url_base.'2'
		), $params));
	}
};
