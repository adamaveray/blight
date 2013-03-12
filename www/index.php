<?php
$web_path	= __DIR__;
require('../Blight.phar');

// Redirect to generated pages
header('HTTP/1.1 302 Found');
header('Location: '.$_SERVER['REQUEST_URI']);
