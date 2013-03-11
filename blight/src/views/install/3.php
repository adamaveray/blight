<?php include('inc/header.php');?>

<form method="post" action="<?php echo $target_url;?>">
	<fieldset>
		<ul>
			<li>
				<label for="input_path_pages">Pages</label>
				<input name="path_pages" id="input_path_pages" value="blog-data/pages/" />
			</li>
			<li>
				<label for="input_path_posts">Posts</label>
				<input name="path_posts" id="input_path_posts" value="blog-data/posts/" />
			</li>
			<li>
				<label for="input_path_drafts">Drafts</label>
				<input name="path_drafts" id="input_path_drafts" value="blog-data/drafts/" />
			</li>
		</ul>
	</fieldset>
	<fieldset>
		<ul>
			<li>
				<label for="input_path_templates">Templates</label>
				<input name="path_templates" id="input_path_templates" value="blog-data/templates/" />
			</li>
		</ul>
	</fieldset>
	<fieldset>
		<ul>
			<li>
				<label for="input_path_web">Web</label>
				<input name="path_web" id="input_path_web" value="www/_blog/" />
			</li>
			<li>
				<label for="input_path_drafts_web">Web Drafts</label>
				<input name="path_drafts_web" id="input_path_drafts_web" value="www/_drafts/" />
			</li>
		</ul>
	</fieldset>
	<fieldset>
		<ul>
			<li>
				<label for="input_path_cache">Cache</label>
				<input name="path_cache" id="input_path_cache" value="cache/" />
			</li>
		</ul>
	</fieldset>

	<button type="submit">Continue</button>
</form>

<?php include('inc/footer.php');?>