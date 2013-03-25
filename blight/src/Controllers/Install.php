<?php
namespace Blight\Controllers;

class Install {
	const COOKIE_NAME	= 'blightinstall';
	const DRAFT_PUBLISH_DIR	= '_publish';
	const DEFAULT_THEME		= 'Basic';

	protected $rootPath;
	protected $appPath;
	protected $webPath;
	protected $templatesDir;
	
	protected $urlBase	= 'index.php?/install/';

	protected $configFile;

	public function __construct($rootPath, $appPath, $webPath, $file){
		session_start();

		$this->rootPath	= $rootPath;
		$this->appPath	= $appPath;
		$this->webPath	= $webPath;
		$this->templatesDir	= $this->appPath.'src/views/install/';

		$this->configFile	= $file;
	}

	public function setup(){
		$dir	= $this->appPath.'src/views/';
		$files	= array(
			'_common/js/jquery-1.9.1.min.js'	=> 'js/jquery-1.9.1.min.js',
			'install/js/install.js'		=> 'js/install.js',
			'install/css/install.css'	=> 'css/install.css'
		);

		foreach($files as $source => $target){
			$source	= $dir.$source;
			$target	= $this->webPath.$target;
			$targetDir	= dirname($target);

			if(!is_dir($targetDir)){
				mkdir($targetDir);
			}
			file_put_contents($target, file_get_contents($source));
		}
	}

	public function teardown(){
		setcookie(self::COOKIE_NAME, null, time()-3600, '/');

		$rootDir	= rtrim($this->webPath,'/').'/';
		$dirs	= array(
			'js',
			'css'
		);

		foreach($dirs as $dir){
			$this->deleteDir($rootDir.$dir);
		}
	}

	protected function sessonSetConfig($name, $value){
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

	public function getPage($uri){
		$fragments	= explode('/', $uri);
		array_shift($fragments);
		array_shift($fragments);
		array_shift($fragments);


		if(!isset($fragments[0]) || $fragments[0] == ''){
			// Start
			$this->setup();

			$_SESSION	= array();
			$this->pageStepStart();
			return;
		}

		switch($fragments[0]){
			case '1':
				if($_SERVER['REQUEST_METHOD'] == 'POST'){
					$this->processStep1($_POST);
				} else {
					$this->pageStep1();
				}
				break;

			case '2':
				if($_SERVER['REQUEST_METHOD'] == 'POST'){
					$this->processStep2($_POST);
				} else {
					$this->pageStep2();
				}
				break;

			case '3':
				if($_SERVER['REQUEST_METHOD'] == 'POST'){
					$this->processStep3($_POST);
				} else {
					$this->pageStep3();
				}
				break;

			case 'end':
				$this->pageStepEnd();
				break;
		}
	}

	protected function redirect($location){
		$location	= $this->urlBase.$location;
		header('HTTP/1.1 302 Found');
		header('Location: '.$location);
		exit;
	}

	protected function validRedirect($errors, $validURL, $invalidURL){
		if(count($errors) === 0){
			$_SESSION['install_errors']	= null;
			unset($_SESSION['install_errors']);
			$this->redirect($validURL);
		} else {
			$_SESSION['install_errors']	= $errors;
			$this->redirect($invalidURL);
		}
	}

	protected function renderView($path, $params){
		$params['errors']	= isset($_SESSION['install_errors']) ? $_SESSION['install_errors'] : array();

		extract($params);
		ob_start();
		include($this->templatesDir.$path);
		return ob_get_clean();
	}

	protected function copyDir($source, $target){
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
				$this->copyDir($file, $target.'/'.$basename);
			} else {
				file_put_contents($target.'/'.$basename, file_get_contents($file));
			}
		}

		return true;
	}

	protected function deleteDir($dir){
		$dir	= rtrim($dir, '/');
		if(!is_dir($dir)){
			return;
		}

		$files	= glob($dir.'/*');
		foreach($files as $file){
			if(is_dir($file)){
				$this->deleteDir($file);
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

	protected function runInstall($config){
		$feedback	= array();

		// Make directories
		$paths	= array(
			'pages'			=> 'blog-data/pages',
			'posts'			=> 'blog-data/posts',
			'drafts'		=> 'blog-data/drafts',
			'themes'		=> 'blog-data/themes',
			'plugins'		=> 'blog-data/plugins',
			'assets'		=> 'blog-data/assets',
			'web'			=> 'www/_blog',
			'drafts-web'	=> 'www/_drafts',
			'cache'			=> 'cache',
		);
		if(!isset($config['paths'])){
			$config['paths']	= array();
		}
		foreach($paths as $configName => $dir){
			$config['paths'][$configName]	= $dir.'/';

			$dir	= $this->rootPath.$dir;
			if(!is_dir($dir)){
				$result	= mkdir($dir, 0755, true);
				if(!$result){
					// Cannot create directory
					if(!isset($feedback['paths'])){
						$feedback['paths']	= array();
					}
					$feedback['paths'][]	= $config['paths'][$configName];
				}
			} elseif(!is_writable($dir)){
				$result	= chmod($dir, 0755);
				if(!$result){
					// Cannot write to directory
					$feedback['paths'][]	= $config['paths'][$configName];
				}
			}
		}

		// Create drafts publish dir
		$draftsDir	= $this->rootPath.$config['paths']['drafts'];
		mkdir($draftsDir.self::DRAFT_PUBLISH_DIR, 0755, true);

		$themesDir	= $this->rootPath.$config['paths']['themes'];

		if(count(glob($themesDir.'*')) === 0){
			// Set up default themes
			try {
				$this->copyDefaultTheme($this->rootPath.'default-theme/'.self::DEFAULT_THEME.'/', $themesDir);
			} catch(\Exception $e){
				$result	= false;
			}
			if(!$result){
				return false;
			}
		}

		// Copy .htaccess
		$htaccess	= file_get_contents($this->appPath.'src/default.htaccess');

		$webDir		= explode('/', rtrim($config['paths']['web'],'/'));
		$htaccessDir	= $this->rootPath.implode('/', array_slice($webDir, 0, -1));

		$commonWebDirs	= array('www', 'public_html', 'htdocs');
		foreach($commonWebDirs as $dir){
			if($webDir[0] === $dir){
				// Found - replace only instance at start
				$webDir	= array_slice($webDir, 1);
				break;
			}
		}
		$webDir		= implode('/', $webDir);
		$htaccess	= str_replace('{%WEB_PATH%}', rtrim($webDir, '/'), $htaccess);
		$htaccessPath	= rtrim($htaccessDir,'/').'/.htaccess';

		$result	= file_put_contents($htaccessPath, $htaccess);
		if(!$result){
			// Cannot write .htaccess
			$feedback['file_htaccess']		= $htaccess;
			$feedback['file_htaccess_path']	= $htaccessPath;
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


		if(!isset($config['theme'])){
			$config['theme']	= array();
		}
		$config['theme']	= array_merge(array(
			'name'	=> self::DEFAULT_THEME
		), $config['theme']);

		// Write config file
		$configText	= $this->buildSetup($config);
		$result	= file_put_contents($this->configFile, $configText);
		if(!$result){
			$feedback['file_config']		= $configText;
			$feedback['file_config_path']	= $this->configFile;
			return $feedback;
		}

		return true;
	}

	protected function buildSetup($config){
		$parser	= new \Blight\Config();
		return $parser->serialize($config);
	}

	protected function copyDefaultTheme($source, $target_dir){
		$name	= basename($source).'.phar';
		try {
			$phar = new \Phar(rtrim($target_dir, '/').'/'.$name, 0, $name);
			$phar->buildFromDirectory($source);
			$phar->setStub('<?php __HALT_COMPILER();');
			unset($phar);

		} catch(\Exception $e){
			return false;
		}

		return true;
	}


	// Start
	public function pageStepStart(){
		echo $this->renderView('start.php', array(
			'title'			=> 'Install Blight',
			'target_url'	=> $this->urlBase.'1'
		));
	}

	// Step 1
	public function pageStep1(){
		echo $this->renderView('1.php', array(
			'title'			=> 'About You',
			'target_url'	=> $this->urlBase.'1',
			'prev_url'		=> '/'
		));
	}

	protected function processStep1($data){
		$errors	= array();

		if(isset($data['author_name']) && trim($data['author_name']) != ''){
			$this->sessonSetConfig('author/name', $data['author_name']);
		} else {
			$errors['author_name']	= true;
		}

		if(isset($data['author_email']) && filter_var($data['author_email'], \FILTER_VALIDATE_EMAIL)){
			$this->sessonSetConfig('author/email', $data['author_email']);
		} else {
			$errors['author_email']	= true;
		}

		$this->validRedirect($errors, '2', '1');
	}

	// Step 2
	public function pageStep2(){
		echo $this->renderView('2.php', array(
			'title'			=> 'About Your Site',
			'target_url'	=> $this->urlBase.'2',
			'prev_url'		=> $this->urlBase.'1'
		));
	}

	protected function processStep2($data){
		$errors	= array();

		if(isset($data['site_name']) && trim($data['site_name']) != ''){
			$this->sessonSetConfig('site/name', $data['site_name']);
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
			$this->sessonSetConfig('site/url', $url);
		} else {
			$errors['site_url']	= true;
		}

		if(isset($data['site_description'])){
			// Not required
			$this->sessonSetConfig('site/description', $data['site_description']);
		}


		$linkblog	= false;
		if(isset($data['linkblog'])){
			$linkblog	= (bool)$data['linkblog'];
		}
		$this->sessonSetConfig('linkblog/linkblog', $linkblog);

		if($linkblog){
			if(isset($data['linkblog_post_character'])){
				$this->sessonSetConfig('linkblog/post_character', $data['linkblog_post_character']);
			}
		} else {
			if(isset($data['linkblog_link_character'])){
				$this->sessonSetConfig('linkblog/link_character', $data['linkblog_link_character']);
			}
		}
		$this->sessonSetConfig('linkblog/link_directory', null);

		$this->validRedirect($errors, 'end', '2');
	}

	// End
	public function pageStepEnd(){
		setcookie(self::COOKIE_NAME, '1', null, '/');

		// Setup finished - save config
		$result	= $this->runInstall($_SESSION['config']);
		if($result !== true){
			// Could not save setup
			$this->pageStepEndFailure($result);
			return;
		}

		// Site setup

		session_destroy();

		echo $this->renderView('end.php', array(
			'title'		=> 'Blight Installed',
			'prev_url'	=> $this->urlBase.'3'
		));
	}

	// End - Failure
	public function pageStepEndFailure($params){
		echo $this->renderView('failure.php', array_merge(array(
			'title'			=> 'Cannot Install Automatically',
			'target_url'	=> $this->urlBase.'end',
			'prev_url'		=> $this->urlBase.'2'
		), $params));
	}
};
