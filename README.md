Blight
======

Blight is a Markdown-powered static blogging engine.


## Installation

Copy the `Blight.phar` file alongside your web directory. Then, copy the `index.php` file to the location you want the blog to be accessible on your site. Edit the `index.php` file ensuring the path in the `require()` line points to the `Blight.phar` file. Visiting the `index.php` page will walk through setting up the rest of the site.

After installation, your directory structure should look like the following:

	config.json

	Blight.phar
	
	blog-data/
		drafts/
			_publish/
		pages/
		posts/
		templates/
		plugins/
		assets/
	
	www/
		.htaccess
		index.php
		
The paths to these directories are stored in the `config.json` file, so to change the directory structure, simply update this file.


## Authoring

New posts should be added to the `drafts/` or `posts/` directories. The filename will become the post's URL slug, so for example a post with the file `test-post.md` will become `2013/02/test-post`.

A new post should be formatted as follows:

	Post Title
	==========
	Header:	Value
	
	Content
	
The title and content are standard Markdown. The headers section under the title allows you to set a number of options and metadata for your post MultiMarkdown-style. Parameters are case-insensitive.

### Drafts

Posts saved to the `drafts/` directory will have preview HTML pages generated, but will not be listed on the site itself.

When drafts are ready to be published, add a line `Publish Now` to the header block, and the post will be moved to the published posts directory and added to the site on next rebuild. Alternatively, move the post to the `_publish/` directory in the drafts directory, and it will be published on rebuild.

### Pages

Simple pages in the same format as posts can be saved to the `pages/` directory, and will have a separate page generated in the public site area, but will not be listed along with posts.


### Special Headers

- **Date**: The publication date of the post. The time portion is optional but recommended.

	`Date: 2013-01-01 12:00:00`

- **Link**: Allows you to create linked posts, where the main link for the article in both the article lists and RSS feed links to the URL provided, while the permalink links to the post itself.

	`Link: http://www.example.com/`

- **Tags**: A comma-separated list of tags to group the post under. Tags should be written in a human-readable format, as URL-friendly versions will be generated automatically.

	`Tags: Example Tag, Other Tag`

- **Category**: A category to group the post under. Similar to _tags_, it should be written in a human-readable format, as a URL-friendly versions will be generated automatically.

	`Category: General`

- **Summary**: A summary of the post's content, in a single line of plaintext

	`Summary: A look back on the history of blogging, and the digital soapbox`

- **RSS Only**: Only display the post in RSS feeds. It will not appear on any rendered HTML files, but will still have a standalone post HTML file generated.

	`RSS Only`


### Publishing

Loading the `index.php` file causes the site to be rebuilt. Once the site is built, however, the generated home page will override requests to the index file.

The simplest way to reload the site is to manually enter `index.php` into your address bar.

Alternatively, deleting the entire `{www}/_blogs/` directory will cause home page requests to again go through the PHP file.

A site rebuild can also be triggered from the command line, by navigating to the directory the system is installed to, and running the command `php Blight.phar`. The optional flag `-v` outputs additional information as the site is built.

Once the pages are built, the page will automatically reload showing the generated static home page. Rebuilding the site should take only a few seconds.


### Updating

When published, original post Markdown files will be organised into date folders, such as `posts/2013/02/2013-02-02-post.md`. To make changes to a previous post, just locate the post in these date folders, make any changes, and trigger a rebuild of the site.


### Media

Any additional files accompanying posts, such as images, should be stored in the `assets/` directory. These files will then be available at the web root.

eg:	`blog-data/assets/img/photo.jpg` → `http://www.example.com/img/photo.jpg`


## Themes

The blog's appearence can be customised through themes. View [the themes documentation](THEMES.md) for more information.


## Config

Additional fine-tuning of the site's behaviour can be made in the `config.json` file.

### Site

- **name**: The blog's name, used in the RSS feed and available in templates
- **url**: The URL to the blog, including any directories if appropriate
- **description**: The blog's description, used in the RSS feed and available in templates

### Theme

- **name**: The name of the theme to render the site with

### Paths

- **pages**: The path to the page source files directory
- **posts**: The path to the posts directory
- **drafts**: The path to the drafts directory
- **themes**: The path to the themes directory
- **web**: The path to output rendered files to
- **drafts_web**: The path to output rendered draft post files to
- **cache**: The path various cache files can be written to

### Limits

- **page**: The maximum number of posts shown per page. On archive pages, extra posts will paginate.
- **home**: The number of posts to show on the home page. If not set, defaults to **page**
- **feed**: The number of posts to include in the RSS feed. If not set, defaults to **page**

### Linkblog

- **linkblog**: Whether to treat the blog as a linkblog (linked post titles are displayed normally, non-linked post
                titles are prefixed with a glyph)
- **link_character**: The glyph to prefix linked posts with when the **linkblog** option is disabled
- **post_character**: The glyph to prefix non-linked posts with when the **linkblog** option is enabled
- **link_directory**: An optional directory to put linked posts in (eg: `2013/02/post` → `linked/2013/02/post`)

### Posts

- **default_extension**: The primary file extension to use for posts
- **allow_txt**: Whether to process Markdown post files with the file extension `.txt`

### Output

- **minify_html**: Whether to minify rendered HTML files, by removing whitespace, etc, reducing file size
- **feed_format**: The format to build feeds, of either `atom` or `rss`


## Plugins

Plugins can be installed to extend the platform. View [the plugins documentation](PLUGINS.md) for more information.


## Building

Dependencies are installed using [Composer](http://getcomposer.org). If you don't have Composer installed, run `curl -sS https://getcomposer.org/installer | php` from the terminal, then `php composer.phar install`. Building the Phar from the source files is then accomplished by running `php build.php`.

