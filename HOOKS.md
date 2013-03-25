Hooks
=====

_Editable parameters for hooks can be modified from within the callback to change the value outside the parameter._

- ## will_publish_post

	Called before publishing a new post

	### Params

	- \Blight\Interfaces\Post **post**: The post being published


- ## did_publish_post

	Called after publishing a new post

	### Params

	- \Blight\Interfaces\Post **post**: The post being published

	### Notes

	- The URL the post was published to can be accessed through the `$post->getPermalink()` method


- ## feed_post

	Called when building a post's content in an RSS feed

	### Params

	- \Blight\Interfaces\Post **post**: The post being rendered
	- string **title**: _(editable)_ The title of the post
	- string **link**: _(editable)_ The URL for the post to link to. For linked posts, this defaults to the external link.
	- \DateTime **date_published**: _(editable)_ The publish date of the post
	- string **guid**: _(editable)_ The RSS GUID for the post. Defaults to the post's permalink.
	- bool **guid_is_permalink**: _(editable)_
	- string **content**: _(editable)_ The post's raw Markdown content
	- string **append**: _(editable)_ A string to append to the post content. Defaults to a permalink for linked posts.
	- bool **process_content**: _(editable)_


- ## render_styles

	Allows adding styles to rendered pages

	### Params

	- \Blight\Interfaces\Packages\Theme **theme**: The current theme
	- \Blight\Interfaces\Template **template**: The current template
	- string **name**: The name of the template being rendered
	- array **styles**: _(editable)_ An array of styles

		**External:**

			$styles[]	= '/path/to/styles.css';
			$styles[]	= array('href' => '/path/to/styles.css', 'media' => 'print');

		**Embedded:**

			$styles[]	= 'body { background: #fff; }';


- ## render_scripts

	Allows adding scripts to rendered pages

	### Params

	- \Blight\Interfaces\Packages\Theme **theme**: The current theme
	- \Blight\Interfaces\Template **template**: The current template
	- string **name**: The name of the template being rendered
	- array **scripts**: _(editable)_ An array of scripts

		**External:**

			$styles[]	= '/path/to/scripts.js';
			$styles[]	= array('src' => '/path/to/scripts.js', 'async' => 'async');

		**Embedded:**

			$styles[]	= 'alert("Scripts");';


