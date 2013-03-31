<?php
function input_error($inputName, $errors){
	if(isset($errors[$inputName]) && $errors[$inputName]){
		echo ' class="invalid"';
	}
}
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo isset($title) ? $title : 'Install Blight';?></title>

    <meta name="viewport" content="width=device-width,initial-scale=1" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<link rel="stylesheet" href="css/install.css" />
</head>
<body<?php if(isset($page_id)){ echo ' id="page_'.$page_id.'"'; }?>>
<div id="main" role="main">
	<header>
		<h1>Blight</h1>
	</header>

	<?php if(isset($title)){?>
    <h2><?php echo $title;?></h2>
	<?php }?>
