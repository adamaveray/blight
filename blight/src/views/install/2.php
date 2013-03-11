<?php include('inc/header.php');?>

<form method="post" action="<?php echo $target_url;?>">
	<fieldset>
		<ul>
			<li>
				<label for="input_site_name">Site Name</label>
				<input name="site_name" id="input_site_name" />
			</li>
			<li>
				<label for="input_site_url">Site URL</label>
				<input name="site_url" id="input_site_url" type="url" value="http://<?php echo $_SERVER['HTTP_HOST'];?>" />
			</li>
			<li>
				<label for="input_site_description">Site Description</label>
				<textarea name="site_description" id="input_site_description"></textarea>
			</li>
		</ul>
	</fieldset>
	<fieldset>
		<ul>
			<li>
				<label for="input_linkblog">Linkblog</label>
				<input name="linkblog" id="input_linkblog" type="checkbox" />
			</li>
			<li>
				<p>If not linkblog</p>
				<label for="input_linkblog_link_character">Link Character</label>
				<input name="linkblog_link_character" id="input_linkblog_link_character" value="→" />
			</li>
			<li>
				<p>If linkblog</p>
				<label for="input_linkblog_post_character">Post Character</label>
				<input name="linkblog_post_character" id="input_linkblog_post_character" value="★" />
			</li>
		</ul>
	</fieldset>

	<button type="submit">Continue</button>
</form>

<?php include('inc/footer.php');?>