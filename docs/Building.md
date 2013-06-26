Building
========

To build the site to a [Phar](http://us3.php.net/manual/en/intro.phar.php), run the `build.php` script:

~~~bash
php build.php
~~~

Dependencies are installed using [Composer](http://getcomposer.org). If you don't have Composer installed, run `curl -sS https://getcomposer.org/installer | php` from the terminal, then `php composer.phar install`. This must be run once before calling the main build script.


## Testing

The unit tests use PHPUnit, and require the [`config.xml`](../tests/Blight/config.xml) file, and the [`bootstrap.php`](../tests/Blight/bootstrap.php) file. The tests can be run from the root source directory with the following command:

~~~bash
phpunit -c tests/Blight/config.xml -v
~~~
