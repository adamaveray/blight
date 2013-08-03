Performance
===========

The site will only rebuild pages when it detects changes. This means that updating a single post won't cause the whole site to rebuild. To trigger a full rebuild of all files, even those unchanged, delete the entire `blight/` directory in the `cache/` directory.

Since the site's theme is fundamental to the site, any changes to the theme will trigger a full site rebuild. Similarly, changes to the `config.json` or `authors.json` files will also trigger a full rebuild, and therefore should be modified conservatively.
