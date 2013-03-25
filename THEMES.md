Themes
======

A theme follows the same base package format as a [plugin](PLUGIN.md), with additional files.

	ThemeName/
    	ThemeName.php
    	package.json
    	templates/
    	assets/


## Main Class

Theme main classes must extend the `\Blight\Packages\Theme` class.

### Overriding Templates

Individual templates can have additional processing performed before rendering by implementing the method `render{THEME}` in the main class.

	public function renderHome($params){
		 // Additional processing

		 // Render template
		 return $this->renderTemplate('home', $params);
	}


## Assets

Static resources the theme uses, such as images and stylesheets, should be stored in the `assets/` directory. These resources will be available at the root web directory for the site.


## Templates

The templates directory contains the templates that will be rendered into static pages.

Templates are written using [Twig](http://twig.sensiolabs.org), or alternatively can be written in regular PHP. All templates must exist in the plugin for the site to generate correctly.

The following variables are available to both PHP and Twig templates:

- **blog**: An instance of the Blog class, providing access to site-wide URLs and other config settings.
- **theme**: An instance of the theme itself
- **text**: An instance of the TextProcessor class, for converting raw posts to Markdown, etc
- **archives**: An array of the years posts exist for

Twig templates have the following filters available:

- **md**: Converts the provided Markdown text into HTML
- **typo**: Performs a number of typographical enhancements on the provided text, such as converting quotes to curly quotes
- **truncate**(length = 100, ending = '...'): Truncates the provided HTML to a certain length.

Specific templates exist for individual pages, and have specific variables available to them:

### List

The **list** template handles pages with a collection of posts, excluding the home page. Archive pages for each year, pages for each tag, and pages for each category are generated using this template.

The following variables are available to list pages:

- **posts**: An array containing posts for the current page

For each of the different listing types, the page's `Collection` object itself will also be provided:

- Year archive pages: **year**
- Tag pages: **tag**
- Collection pages: **collection**

Additionally, if pagination is enabled in the `config.ini` file, the following variables are available:

- **pagination**: A `Pagination` instance. If this parameter is not set, pagination is disabled

### Home

The **home** template functions the same as the **list** template, allowing a different page layout for the home page of the site.

### Post

The **post** template displays individual posts on separate pages, where their permalinks will point to.

The following variables are available to post pages:

- **post**: The Post instance for the current page
- **post_prev**: The previous/older post neighboring the current post, useful for adding next/prev post links. This value may not always be set.
- **post_next**: The next/newer post neighboring the current post. This value may not always be set.

### Page

The **page** template displays individual pages

The following variables are available to page-pages:

- **page**: The Page instance for the current page
