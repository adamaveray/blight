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

This file must contain a class of the same name, extending `\Blight\Models\Packages\Package`. The file should implement whichever of the following interfaces are appropriate:

- `\Blight\Interfaces\Models\Packages\Plugin`: Plugin hooks will be called on the class

Themes can also be plugins, provided they also implement the above interface. If the interface is not implemented, no hooks will be called on the theme. _Only the active theme will have hooks called on it._


### Hooks

See the [Hooks](HOOKS.md) file for the list of available hooks.

Implementing callback functions for the hooks available requires creating an instance method on your main class named `hook_{HOOKNAME}`.

Some hooks will provide parameters to the callbacks, which will be passed as an associative array to the callback. Hooks that allow the callbacks to modify
some of the parameters will pass those parameters by reference in the array, meaning any changes to the variable will change the original value. To make
changes to the parameter locally without modifying the final value, simply assign the parameter from the array to a variable.


### Package Execution

Packages cannot override the `__construct()` method, and instead should implement the `setup()` method, which will be called immediately after the class is instantiated.


### Example

	<?php
	namespace \Example;

	class ExamplePlugin extends \Blight\Models\Packages\Packages implements \Blight\Interfaces\Models\Packages\Plugin {
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
