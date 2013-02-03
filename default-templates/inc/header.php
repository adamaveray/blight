<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo (isset($page_title) ? $page_title.' • ' : '').$blog->get_name();?></title>

    <meta name="viewport" content="width=device-width,initial-scale=1" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

	<link type="application/rss+xml" rel="alternate" title="RSS Feed" href="<?php echo $blog->get_feed_url();?>"  />
</head>
<body>
<header role="banner">
	<h1><a href="<?php echo $blog->get_url();?>"><?php echo $blog->get_name();?></a></h1>

	<p class="description"><?php echo $blog->get_description();?></p>
</header>

<div id="main" role="main">