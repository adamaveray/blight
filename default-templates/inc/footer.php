	<?php if(isset($pagination)){ ?>
	<footer>
		<ol class="pagination">
			<?php foreach($pagination['pages'] as $page => $url){
				$current	= ($page == $pagination['current']);?>
			<li<?php if($current){?> class="current"<?php }?>>
				<?php if(!$current){?><a href="<?php echo $url;?>"><?php }?><?php echo $page;?><?php if($current){?></a><?php }?>
			</li>
			<?php }?>
		</ol>
	</footer>
	<?php } ?>

	<aside>
		<?php if(!empty($archives)){?>
		<nav class="archives">
			<h2>Archives</h2>
			<ol>
				<?php foreach($archives as $year){?>
				<li>
					<a href="<?php echo $blog->get_url('archive/'.$year);?>"><?php echo $year;?></a>
				</li>
				<?php }?>
			</ol>
		</nav>
		<?php }?>

		<?php if(!empty($categories)){?>
		<nav class="categories">
			<h2>Categories</h2>
			<ol>
				<?php foreach($categories as $category){?>
				<li>
					<a href="<?php echo $category->get_url();?>"><?php echo $category->get_name();?></a>
				</li>
				<?php }?>
			</ol>
		</nav>
		<?php }?>
	</aside>
</div>

<footer role="contentinfo">
	<p class="copyright">Copyright <?php echo date('Y');?> – All Rights Reserved</p>
	<p><a href="<?php echo $blog->get_feed_url();?>">RSS</a></p>
</footer>
</body>
</html>