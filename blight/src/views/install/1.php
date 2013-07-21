<?php
$pageID	= 'user_settings';
include('inc/header.php');?>

<form method="post" action="<?php echo $targetURL;?>">
	<ul>
		<li>
			<label for="input_author_name">Your Name</label>
			<input name="author_name" id="input_author_name" required="required"<?php input_error('author_name', $errors);?> />
		</li>
		<li>
			<label for="input_author_email">Your Email</label>
			<input name="author_email" id="input_author_email" type="email" required="required"<?php input_error('author_email', $errors);?> />
		</li>
	</ul>

	<button class="continue" type="submit">Continue</button>
</form>

<?php include('inc/footer.php');?>