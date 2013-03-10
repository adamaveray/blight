<?php
$setup	= function(\Blight\Interfaces\Blog $blog){
	$file_system	= $blog->get_file_system();

	// Create posts directory
	$file_system->create_dir($blog->get_path_posts());
	$file_system->create_dir($blog->get_path_drafts());

	$template_dir	= $blog->get_path_templates();
	if(!is_dir($template_dir) || count(glob($template_dir.'*')) === 0){
		// Set up default template
		$file_system->copy_dir($blog->get_path_root('default-templates/'), $template_dir);
	}

	// Copy .htaccess
	$htaccess	= $file_system->load_file($blog->get_path_app('src/default.htaccess'));

	$web_dir		= explode('/', str_replace($blog->get_path_root(), '', $blog->get_path_www()));
	$htaccess_dir	= implode('/', array_slice($web_dir, 0, -1));

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
	$file_system->create_file(rtrim($htaccess_dir,'/').'/.htaccess', $htaccess);


	// Update config with domain
	$path		= $blog->get_path_root('config.ini');
	$content	= $file_system->load_file($path);
	$find	= 'http://www.example.com/';
	if(isset($_SERVER['SERVER_NAME']) && strstr($content, $find) !== false){
		$host	= 'http://'.$_SERVER['SERVER_NAME'].'/';
		$file_system->create_file($path, str_replace($find, $host, $content));
	}
};

$setup($blog);
