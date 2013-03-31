<?php
/** @var \Blight\Interfaces\TextProcessor $posts */
/** @var array $posts */
foreach($posts as $post){
	/** @var \Blight\Interfaces\Models\Post $post */
	?>
	<h1><?php echo $post->getTitle();?></h1>
	<?php
	echo $text->processMarkdown($post->getContent());
}
?>