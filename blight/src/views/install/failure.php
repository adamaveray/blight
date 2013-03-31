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

<?php
$files	= array(
	'config'	=> 'config',
	'authors'	=> 'authors',
	'htaccess'	=> '.htaccess'
);

foreach($files as $file => $name){
	if(isset(${'file_'.$file})){?>
		<section>
			<p>The <?php echo $name;?> file could not be created. Please create the following file:</p>

			<code class="filename file_title"><?php echo ${'file_'.$file.'_path';?></code>
			<pre><code><?php echo htmlspecialchars(${'file_'.$file});?></code></pre>
		</section>
	<?php }
}
?>

<a class="continue retry" href="<?php echo $target_url;?>">Retry</a>

<?php include('inc/footer.php');?>