Configuration
=============

Control and management of the site's behaviour can be made in the `config.json` file. All attributes should be entered.

## Site

- **name**: The blog's name, used in the RSS feed and available in templates
- **url**: The URL to the blog, including any directories if appropriate
- **description**: The blog's description, used in the RSS feed and available in templates
- **timezone**: The timezone posts are written in (from [this list](http://php.net/manual/en/timezones.php))


## Author

The default author for the blog, for posts without an author manually entered. The value must be the name of one of the authors stored in [the authors file](AUTHORS.md).


## Theme

- **name**: The name of the theme to render the site with


## Paths

- **pages**: The path to the page source files directory
- **posts**: The path to the posts directory
- **drafts**: The path to the drafts directory
- **themes**: The path to the themes directory
- **web**: The path to output rendered files to
- **drafts_web**: The path to output rendered draft post files to
- **cache**: The path various cache files can be written to
- **log**: The path to write the system log it. If not set, logging will not occur.


## Limits

- **page**: The maximum number of posts shown per page. On archive pages, extra posts will paginate.
- **home**: The number of posts to show on the home page. If not set, defaults to **page**
- **feed**: The number of posts to include in the RSS feed. If not set, defaults to **page**


## Linkblog

- **linkblog**: Whether to treat the blog as a linkblog (linked post titles are displayed normally, non-linked post
                titles are prefixed with a glyph)
- **link_character**: The glyph to prefix linked posts with when the **linkblog** option is disabled
- **post_character**: The glyph to prefix non-linked posts with when the **linkblog** option is enabled
- **link_directory**: An optional directory to put linked posts in (eg: `2013/02/post` → `linked/2013/02/post`)


## Posts

- **default_extension**: The primary file extension to use for posts
- **allow_txt**: Whether to process Markdown post files with the file extension `.txt`


## Output

- **generate_hypenation**: Whether to insert soft hyphens into page content during the typography filtering
- **minify_html**: Whether to minify rendered HTML files, by removing whitespace, etc, reducing file size
- **feed_format**: The format to build feeds, of either `atom` or `rss`
- **cache_twig**: Whether to cache compiled Twig tempates


## Debug

`true` enables debug settings in the site, including the `dump` function in Twig templates.
