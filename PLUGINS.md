Plugins
=======

Blight supports plugins in a simple package format.

A basic package is structured as follows:

	PackageName/
		PackageName.php
		package.json
		...


## Main Class

The package can contain any number of supporting files, however must contain a PHP file with the same name as the package.

This file must contain a class of the same name, extending `\Blight\Packages\Package`. The file should implement whichever of the following interfaces are appropriate:

- `\Blight\Packages\Plugin`: A standard, generic plugin


### Package Execution

Packages cannot override the `__construct()` method, and instead should implement the `setup()` method, which will be called immediately after the class is instantiated.


### Example

	<?php
	namespace \Example;

	class ExamplePlugin extends \Blight\Packages\Packages implements \Blight\Interfaces\Packages\Plugin {
		public function setup(){
			// Prepare plugin
		}
	};

The class should be [namespaced](http://www.php.net/manual/en/language.namespaces.rationale.php) appropriately, along with any other classes in other files in the package.


## Manifest

The package's details and metadata is stored in the `package.json` file – a JSON file with the following components:

- `package`

	- `name`: The name of the package
	- `url`: The URL of the package
	- `description`: A short description of the package
	- `license`
		- `name`: The short name of the license (eg: MIT, BSD)
		- `url`: A URL to the full license text
	- `version`: The current version of the package
	- `namespace`: The [PHP namespace](http://www.php.net/manual/en/language.namespaces.rationale.php) the package uses _(optional – default `\\`)_

- `compatibility`

	- `minimum`: The minimum version of Blight the package requires
	- `max-tested`: The maximum version of Blight the package has been tested on _(optional)_

- `author`: The package author (see below)

- `contributors`: An array of package contributors (see below)

### Author/Contributors

The author/each contributor should be an object with the following elements:

- `name`: The name of the contributor
- `url`: The URL for the contributor

Alternatively, the author/contributor can simply be a string for their respective Twitter handle (with leading `@`)


## Example

	{
		"package":	{
			"name":	"Example Package",
			"url":	"http://www.example.com/package",
			"description":	"An example package",
			"license":	{
				"name":	"BSD",
				"url":	"http://www.example.com/package/license"
			},
			"version":	"1.0",
			"namespace":	"\\Example"
		},
		"compatibility":	{
			"minimum":		"0.5",
			"max-tested":	"0.5"
		},
		"author":	{
			"name":	"Example Author",
			"url":	"http://www.example.com"
		},
		"contributors":	[
			{
				"name":	"Example Contributor 1",
				"url":	"http://www.example.com"
			},
			{
				"name":	"Example Contributor 2",
				"url":	"http://www.example.com"
			},
			"@example"
		]
	}
