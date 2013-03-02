<?php
/** @var \Blight\Blog $blog */
/** @var \Blight\TextProcessor $text */
/** @var \Blight\Post $post */

$page_title	= $post->get_title();
?>
<?php include('inc/header.php');?>

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

	<footer>
		<?php
		$tags	= $post->get_tags();
		if($tags){?>
			<p>
				<strong>Tags:</strong>
				<ul>
				<?php foreach($tags as $tag){?>
					<li>
						<a href="<?php echo $tag->get_url();?>"><?php echo $tag->get_name();?></a>
					</li>
				<?php }?>
				</ul>
			</p>
		<?php
		} ?>
	</footer>
</article>

<?php include('inc/footer.php');?>