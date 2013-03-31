<?php
// Not a real XML sitemap...

/** @var array $pages */
foreach($pages as $page){
	/** @var \Blight\Interfaces\Models\Page $page */
	?>
	<p><a href="<?php echo $page->getPermalink();?>"><?php echo $page->getTitle();?></a></p>
	<?php
}
?>