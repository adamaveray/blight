Release Notes
=============

## 0.9

_3 Aug 2013_

- [Caching](Performance.md)
- Sequential, year-ignorant pagination
- 404 page generation
- Template fallbacks/hierarchies
- Post summary generation
- Post image retrieval
- Debug mode
- Unified build script


## 0.8

_25 Jun 2013_

- Scheduled posts
- Folder watching and command line building
- Timezone support
- Logging
- Hooks in Themes
- Organised documentation


## 0.7

_31 Mar 2013_

- [Themes](THEMES.md)
- [Authors](AUTHORS.md)
- Switched to [Composer](http://getcomposer.org) dependency management
- Added support for user asset files
- Added single-process locking
- Added Atom feed format support
- Added post summary support
- Added post date modified/updated support
- Updated coding to match [PSR-1 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)


## 0.6

_16 Mar 2013_

- [Plugins](PLUGINS.md)
- Added draft publishing directory
- Added RSS-only posts support
- Added RSS feeds for categories and posts
- Added optional .txt file parsing
- Added config setting to render linked posts to separate directory


## 0.5

_12 Mar 2013_

- Added setup installer
- Distributed as [Phar](http://www.php.net/manual/en/intro.phar.php)
- Added support for pages
- Added optional minification of rendered pages
- Added generated sitemaps


## 0.4

_10 Mar 2013_

- Added support for draft posts
- Added unit tests
- Created separate pagination class


## 0.3

_5 Mar 2013_

- Added support for [Twig](http://twig.sensiolabs.org/) templates
- Switched default templates to use Twig
- Added support for rebuilding site through command line
- Set rendered files web path from config in .htaccesss when setting up site
- Added support for valueless/name-only metadata in posts


## 0.2

_2 Mar 2013_

- Added support for tags and categories
- Added option to enable linkblog-style blog (prefixes non-linked-posts with a glyph)
- Moved link-post-related glyphs to config file
- Made rendered files output path configurable


## 0.1

_3 Feb 2013_

- Initial release