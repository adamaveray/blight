Blight
======

Blight is (another) Markdown-powered static blogging engine. It is a simple, light blog app with easy setup and administration.


## Installation

Copy the `config.ini` file and `blight/` directory alongside your website files directory. Then, copy the `index.php` file to the location you want the blog to be accessible on your site. Edit the `index.php` file ensuring the path in the `require()` line points to the `blight/` directory. Visiting the `index.php` page will set up the rest of the engine.

After installation, your directory structure should look like the following:

	config.ini

	blight/
	
	blog-data/
		posts/
		templates/
	
	www/
		.htaccess
		index.php
		
The paths to these directories are stored in the `config.ini` file, so to change the directory structure, simply update this file.

The application will need write access to the `posts/`, `templates/`, and the web directory.


## Authoring

New posts should be added to the `posts/` directory. The filename will become the post's URL slug, so for example a post with the file `test-post.md` will become `2013/02/test-post.md`.

A new post should be formatted as follows:

	Post Title
	==========
	Header:	Value
	
	Content
	
The title and content are standard Markdown. The headers section under the title allows you to set a number of options and metadata for your post MultiMarkdown-style. Simply include the name of the header, a colon, spaces or tabs, and the value for that option. Parameters are case-insensitive.


### Special Headers

- **Link**: Allows you to create linked posts, where the main link for the article in both the article lists and RSS feed links to the URL provided, while the permalink links to the post itself.

	`Link: http://www.example.com/`

- **Tags**: A comma-separated list of tags to group the post under. Tags should be written in a human-readable format, as URL-friendly versions will be generated automatically.

	`Tags: Example Tag, Other Tag`

- **Category**: A category to group the post under. Similar to _tags_, it should be written in a human-readable format, as a URL-friendly versions will be generated automatically.

	`Category: General`


### Publishing

Loading the `index.php` file causes the site to be rebuilt. Once the site is built, however, the generated home page will override requests to the index file.

The simplest way to reload the site is to manually enter `index.php` into your address bar.

Alternatively, deleting the entire `{WWW}/blogs/` directory will cause home page requests to again go through the PHP file.

Once the pages are built, the page will automatically reload showing the generated static home page. Rebuilding the site should take only a few seconds.


### Updating

When published, original post Markdown files will be organised into date folders, such as `posts/2013/02/2013-02-02-post.md`. To make changes to a previous post, just locate the post in these date folders, make any changes, and trigger a rebuild of the site.


## Templates

Templates are written in regular PHP, and are contained in the `blog-data/templates/` directory. All templates must exist for the site to generate correctly. 

The following variables are available to all templates:

- **$blog**: An instance of the Blog class, providing access to site-wide URLs and other config settings.
- **$text**: An instance of the TextProcessor class, for converting raw posts to Markdown, etc
- **$archives**: An array of the years posts exist for

Specific templates exist for individual pages, and have specific variables available to them:

### Archive

Archives are posts grouped by year. The following variables are available to archive pages:

- **$year**: The year for the current archive
- **$posts**: An array containing posts for the current page

Additionally, if pagination is enabled in the `config.ini` file, the following variables are available:

- **$pagination**: An array containing the pagination details. If this array is not set, pagination is disabled
	- **$pagination['current']**: The current page number (1-indexed)
	- **$pagination['pages']**: An array containing each page's details in the format `page_no => 'url'`


## Config

Additional fine-tuning of the site's behaviour can be made in the `config.ini` file. The file is [PHP configuration file](http://www.php.net/manual/en/function.parse-ini-file.php) formatted, with sections.

### Ungrouped

- **name**: The blog's name, used in the RSS feed and available in templates
- **url**: The URL to the blog, including any directories if appropriate
- **description**: The blog's description, used in the RSS feed and available in templates

### Paths

- **posts**: The path to the posts directory
- **templates**: The path to the templates directory
- **web**: The path to output rendered files to

### Limits

- **page**: The maximum number of posts shown per page. On archive pages, extra posts will paginate.
- **home**: The number of posts to show on the home page. If not set, defaults to **page**
- **feed**: The number of posts to include in the RSS feed. If not set, defaults to **page**

### Linkblog

- **linkblog**: Whether to treat the blog as a linkblog (linked post titles are displayed normally, non-linked post
                titles are prefixed with a glyph)
- **link_character**: The glyph to prefix linked posts with when the _linkblog_ option is disabled
- **post_character**: The glyph to prefix non-linked posts with when the _linkblog_ option is enabled


## Planned Features

- Drafts
- Basic web admin
- Hooks
