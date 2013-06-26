Authoring
=========

New posts should be added to the _drafts_ or _posts_ directories, as defined in the [config file](Config.md). The filename will become the post's URL slug, so a post with the filename `test-post.md` will become `2013/02/test-post`.

A new post should be formatted as follows:

	Post Title
	==========
	Header:	Value

	Content
	
The title and content are standard Markdown. The headers section under the title allows you to set a number of options and metadata for your post MultiMarkdown-style. Parameters are case- and space-insensitive, so `Location Name` and `location-name` are equivalent.


## Drafts

Posts saved to the _drafts_ directory will have preview HTML pages generated, but will not be listed on the site itself.

When drafts are ready to be published, add the line `Publish Now` to the header block, and the post will be moved to the published posts directory and added to the site on next rebuild. Alternatively, move the post to the `_publish/` directory within the drafts directory, and it will also be published on rebuild.

To schedule a post for a future date, add a line `Publish At` to the header block, with the date to publish the post after. On the next site rebuild after that time, the post will be published. _If automatic monitoring of the site is not set up (such as with the run script below) the post will not be published until the site is rebuilt manually._


## Pages

Simple pages in the same format as posts can be saved to the `pages/` directory, and will have a separate page generated in the public site area, but will not be listed along with posts. Subdirectories will be followed.


-----


## Special Headers

The following headers have special meanings, and are used throughout the system. With the exception of the _Date_ header, all are optional.

- **Date**: The publication date of the post. The time portion is optional but recommended.

	`Date: 2013-01-01 12:00:00`

- **Date Updated**: The date the last updates were made to the post. If not set, defaults to the post file's modification time.

	`Date Updated: 2013-01-01 12:00:00`

- **Link**: Allows you to create linked posts, where the main link for the article in both the article lists and RSS feed links to the URL provided, while the permalink links to the post itself.

	`Link: http://www.example.com/`

- **Author**: The name of the post author. If not set, the author will default to the site author. This author should be defined in the [Authors file](Authors.md).

	`Author: Sam Pell`

- **Tags**: A comma-separated list of tags to group the post under. Tags should be written in a human-readable format, as URL-friendly versions will be generated automatically.

	`Tags: Example Tag, Other Tag`

- **Category**/**Categories**: A category or categories to group the post under. Similar to _tags_, it should be written in a human-readable format, as URL-friendly versions will be generated automatically.

	`Category: General`

	`Categories: General, Special`

- **Summary**: A summary of the post's content, in a single line of plaintext.

	`Summary: A look back on the history of blogging, and the digital soapbox`

- **RSS Only**: Only display the post in RSS feeds. It will not appear on any rendered HTML files, but will still have a standalone post HTML file generated.

	`RSS Only`


## Publishing

As the site is static, it must be rebuilt to show changes.

The best and most advanced way to rebuild the site is to run the `run.sh` script from the terminal. If `inotify` is installed on the server, the script will monitor the site directories, and automatically rebuild the site when any post or page is changed, including drafts.

A site rebuild can also be triggered from the command line, by navigating to the directory the system is installed to, and running the command `php Blight.phar`. The optional flag `-v` outputs additional information as the site is built.

Alternatively, an easy way to reload the site is to manually enter `index.php` into your address bar. Loading the `index.php` file causes the site to be rebuilt. Once the site is built, however, the generated home page will override requests to the index file. Deleting the entire `{www}/_blogs/` directory will also cause home page requests to again go through the PHP file, causing the site to rebuild on the next request. To disable this feature, delete the `index.php` file in the web directory.

Once the pages are built, the page will automatically reload showing the generated static home page. Rebuilding the site should take only a few seconds.


## Updating

When published, original post Markdown files will be organised into date folders, such as `posts/2013/02/2013-01-01-post.md`. To make changes to a previous post, just locate the post in these date folders, make any changes, and trigger a rebuild of the site.


## Media

Any additional files accompanying posts, such as images, should be stored in the `assets/` directory. These files will then be available at the web root.

eg:	`assets/img/photo.jpg` → `http://www.example.com/img/photo.jpg`
