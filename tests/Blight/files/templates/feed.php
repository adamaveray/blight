<?php
// Not a real RSS feed...

/** @var \Blight\Interfaces\TextProcessor $posts */
/** @var array $posts */
foreach($posts as $post){
	/** @var \Blight\Interfaces\Post $post */
	?>
	<h1><?php echo $post->get_title();?></h1>
	<?php
	echo $text->process_markdown($post->get_content());
}
?>