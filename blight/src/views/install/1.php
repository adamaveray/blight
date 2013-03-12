<?php
$page_id	= 'user_settings';
include('inc/header.php');?>

<form method="post" action="<?php echo $target_url;?>">
	<ul>
		<li>
			<label for="input_author_name">Your Name</label>
			<input name="author_name" id="input_author_name" required="required" />
		</li>
		<li>
			<label for="input_author_email">Your Email</label>
			<input name="author_email" id="input_author_email" type="email" required="required" />
		</li>
	</ul>

	<button class="continue" type="submit">Continue</button>
</form>

<?php include('inc/footer.php');?>