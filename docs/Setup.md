Installing Blight
=================

Copy the `Blight.phar` file alongside your web directory. Then, copy the `index.php` file to the location you want the blog to be accessible on your site. Edit the `index.php` file ensuring the path in the `require()` line points to the `Blight.phar` file. Visiting the `index.php` page will walk through setting up the rest of the site.

After installation, your directory structure should look like the following:

	config.json
	authors.json

	Blight.phar

	blog-data/
		drafts/
			_publish/
		pages/
		posts/
		assets/
		plugins/
		themes/
		data/

	www/
		.htaccess
		index.php

	cache/

The paths to these directories are stored in the `config.json` file, so to change the directory structure, simply update this file.
