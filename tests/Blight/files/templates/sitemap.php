<?php
// Not a real XML sitemap...

/** @var array $pages */
foreach($pages as $page){
	/** @var \Blight\Interfaces\Page $page */
	?>
	<p><a href="<?php echo $page->get_permalink();?>"><?php echo $page->get_title();?></a></p>
	<?php
}
?>