<?php
$page_id	= 'site_settings';
include('inc/header.php');?>

<form method="post" action="<?php echo $target_url;?>">
	<fieldset>
		<ul>
			<li>
				<label for="input_site_name">Site Name</label>
				<input name="site_name" id="input_site_name" required="required"<?php input_error('site_name', $errors);?> />
			</li>
			<li>
				<label for="input_site_url">Site URL</label>
				<input name="site_url" id="input_site_url" type="url" value="http://<?php echo $_SERVER['HTTP_HOST'];?>" required="required"<?php input_error('site_url', $errors);?> />
			</li>
			<li class="full textarea">
				<label for="input_site_description">Site Description</label>
				<textarea name="site_description" id="input_site_description"<?php input_error('site_description', $errors);?>></textarea>
			</li>
		</ul>
	</fieldset>
	<fieldset id="linkblog_options">
		<ul>
			<li class="full checkbox">
                <input name="linkblog" id="input_linkblog" type="checkbox" />
                <label for="input_linkblog">Is Linkblog</label>

				<p class="description">Linkblogs feature linked posts as the majority of posts.</p>
			</li>
			<li class="linkblog_enabled_options single">
                <label for="input_linkblog_link_character">Link Character</label>
                <input name="linkblog_link_character" id="input_linkblog_link_character" value="→"<?php input_error('linkblog_link_character', $errors);?> />

                <p class="description">This character will be added before linked post titles</p>
			</li>
			<li class="linkblog_disabled_options single">
                <label for="input_linkblog_post_character">Post Character</label>
                <input name="linkblog_post_character" id="input_linkblog_post_character" value="★"<?php input_error('linkblog_post_character', $errors);?> />

                <p class="description">This character will be added before non-linked post titles</p>
			</li>
		</ul>
	</fieldset>

	<button class="continue" type="submit">Continue</button>
</form>

<?php include('inc/footer.php');?>