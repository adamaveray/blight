Hooks
=====

_See the [Plugins documentation](Plugins.md) for how to implement hooks._

_Parameters marked as 'editable' can be modified within the callback to change the value outside the plugin._


Publishing
----------

- ### willPublishPost

	Called before publishing a new post

	#### Params

	- \Blight\Interfaces\Models\Post **post**: The post being published


- ### didPublishPost

	Called after publishing a new post

	#### Params

	- \Blight\Interfaces\Models\Post **post**: The post being published

	#### Notes

	- The URL the post was published to can be accessed through the `$post->getPermalink()` method


Output
------

- ### postBodyRaw

	Called when building a post's body, before processing Markdown and typography

	#### Params

	- \Blight\Interfaces\Models\Post **post**: The post being rendered
	- bool **isSummary**: Whether the body content requested is the post's summary
	- string **content**: _(editable)_ The raw Markdown content of the post


- ### postBodyProcessed

	Called when building a post's body, after processing Markdown and typography

	#### Params

	- \Blight\Interfaces\Models\Post **post**: The post being rendered
	- bool **isSummary**: Whether the body content requested is the post's summary
	- string **content**: _(editable)_ The processed HTML content of the post


- ### pageBodyRaw

	Called when building a page's body, before processing Markdown and typography

	#### Params

	- \Blight\Interfaces\Models\Page **page**: The page being rendered
	- string **content**: _(editable)_ The raw Markdown content of the page


- ### pageBodyProcessed

	Called when building a page's body, after processing Markdown and typography

	#### Params

	- \Blight\Interfaces\Models\Page **page**: The page being rendered
	- string **content**: _(editable)_ The processed HTML content of the page


- ### feedPost

	Called when building a post's content in an RSS feed

	#### Params

	- string **feed_type**: The feed format type – either `'atom'` or `'rss'`
	- \Blight\Interfaces\Models\Post **post**: The post being rendered
	- string **title**: _(editable)_ The title of the post
	- string **link**: _(editable)_ The URL for the post to link to. For linked posts, this defaults to the external link.
	- \stdClass **author**: _(editable)_ The author of the post. An object with the properties `name`, `url` and `email`
	- \DateTime **date_published**: _(editable)_ The publish date of the post
	- \DateTime **date_updated**: _(editable)_ The last-updated date of the post
	- string **guid**: _(editable)_ Either the RSS GUID or Atom ID for the post. Defaults to the post's permalink.
	- bool **guid_is_permalink**: _(editable)_
	- string **content**: _(editable)_ The post's raw Markdown content
	- string **summary**: _(editable)_ The post's plaintext summary, or null if not set
	- string **append**: _(editable)_ A string to append to the post content. Defaults to a permalink for linked posts.
	- bool **process_content**: _(editable)_ Whether to process the content as Markdown


- ### renderStyles

	Allows adding styles to rendered pages

	#### Params

	- \Blight\Interfaces\Models\Packages\Theme **theme**: The current theme
	- \Blight\Interfaces\Models\Template **template**: The current template
	- string **name**: The name of the template being rendered
	- array **styles**: _(editable)_ An array of styles

		**External:**

			$styles[]	= '/path/to/styles.css';
			$styles[]	= array('href' => '/path/to/styles.css', 'media' => 'print');

		**Embedded:**

			$styles[]	= 'body { background: #fff; }';


- ### renderScripts

	Allows adding scripts to rendered pages

	#### Params

	- \Blight\Interfaces\Models\Packages\Theme **theme**: The current theme
	- \Blight\Interfaces\Models\Template **template**: The current template
	- string **name**: The name of the template being rendered
	- array **scripts**: _(editable)_ An array of scripts

		**External:**

			$styles[]	= '/path/to/scripts.js';
			$styles[]	= array('src' => '/path/to/scripts.js', 'async' => 'async');

		**Embedded:**

			$styles[]	= 'alert("Scripts");';


- ### processTypography

	Called when applying typographical fixes and helper classes to a block of HTML

	#### Params

	- string **html**: _(editable)_ The HTML being processed
