<?php
$web_path	= __DIR__;
require('../blight/blight.php');

// Redirect to generated pages
header('HTTP/1.1 302 Found');
header('Location: '.$_SERVER['REQUEST_URI']);
