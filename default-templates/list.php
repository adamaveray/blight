<?php
/** @var \Blight\Blog $blog */
/** @var \Blight\TextProcessor $text */
/** @var \Blight\Post $post */

$page_title	= $year.' Archive';
?>
<?php include('inc/header.php');?>

<h1><?php echo $page_title;?></h1>
<ol class="posts_list">
	<?php foreach($posts as $post){?>
	<li>
		<article>
			<header>
				<a href="<?php echo $post->get_link();?>">
					<h2><?php echo $text->process_typography($post->get_title());?></h2>
				</a>

				<p>
					<time datetime="<?php echo $post->get_date()->format('c');?>" pubdate="pubdate"><?php echo $post->get_date()->format('F j, Y');?></time>
					•
					<a class="permalink" title="Permalink" href="<?php echo $post->get_permalink();?>">∞</a>
				</p>
			</header>

			<?php echo $text->process($post->get_content());?>
		</article>
	</li>
	<?php } ?>
</ol>

<?php include('inc/footer.php');?>