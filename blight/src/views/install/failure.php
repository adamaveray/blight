<?php include('inc/header.php');?>

<?php if(isset($directories)){?>
<section>
	<p>The following directories could not be written to. Please ensure they exist and are writable.</p>

	<ul>
	<?php foreach($directories as $directory){?>
		<li><code class="filename"><?php echo $directory;?></code></li>
	<?php }?>
	</ul>
</section>
<?php }?>

<?php if(isset($file_config)){?>
<section>
	<p>The config file could not be created. Please create the following file:</p>

	<code class="filename file_title"><?php echo $file_config_path;?></code>
	<pre><code><?php echo htmlspecialchars($file_config);?></code></pre>
</section>
<?php }?>

<?php if(isset($file_htaccess)){?>
<section>
	<p>The .htaccess file could not be created. Please create the following file:</p>

	<code class="filename file_title"><?php echo $file_htaccess_path;?></code>
	<pre><code><?php echo htmlspecialchars($file_htaccess);?></code></pre>
</section>
<?php }?>

<a class="continue retry" href="<?php echo $target_url;?>">Retry</a>

<?php include('inc/footer.php');?>