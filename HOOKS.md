Hooks
=====

_Editable parameters for hooks can be modified from within the callback to change the value outside the parameter._

-	## will_publish_post

	Called before publishing a new post

	### Params

	- \Blight\Interfaces\Post **post**: The post being published


-	## did_publish_post

	Called after publishing a new post

	### Params

	- \Blight\Interfaces\Post **post**: The post being published

	### Notes

	- The URL the post was published to can be accessed through the `$post->get_permalink()` method
