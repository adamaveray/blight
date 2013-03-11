<?php include('inc/header.php');?>

<form method="post" action="<?php echo $target_url;?>">
	<ul>
		<li>
			<label for="input_author_name">Your Name</label>
			<input name="author_name" id="input_author_name" />
		</li>
		<li>
			<label for="input_author_email">Your Email</label>
			<input name="author_email" id="input_author_email" type="email" />
		</li>
	</ul>

	<button type="submit">Continue</button>
</form>

<?php include('inc/footer.php');?>