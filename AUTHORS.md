Authors
=======

The site can support multiple authors, stored in the `authors.json` file.

## Details

- **name**: The name of the author
- **email**: _(optional)_ The author's email address
- **url**: _(optional)_ The author's URL

Any additional attributes will be available through the `hasAttribute` and `getAttribute` methods on the Author object.


## Example

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
