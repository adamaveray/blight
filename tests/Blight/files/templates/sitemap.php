<?php
// Not a real XML sitemap...

/** @var array $pages */
foreach($pages as $page){
	?>
	<p><a href="<?php echo $page['url'];?>"><?php echo $page['name'];?></a></p>
	<?php
}
?>