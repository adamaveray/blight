Authors
=======

The site can support multiple authors, stored in the `authors.json` file. The default site author is defined in the [config file](Config.md).

## Details

The following are the main, standard attributes of the author:

- **name**: The name of the author
- **email**: _(optional)_ The author's email address
- **url**: _(optional)_ The author's URL

Any additional attributes added to the author will be available through the `hasAttribute` and `getAttribute` methods on the Author object.


## Example

~~~json
[
	{
		"name":		"Sam Pell",
		"email":	"sam@example.com",
		"url":		"http://www.example.com/sample"
	},
	{
		"name":		"Joe Bloggs",
		"url":		"http://www.example.com/joe"
	}
]
~~~
