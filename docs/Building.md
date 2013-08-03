Building
========

To build the site to a [Phar](http://us3.php.net/manual/en/intro.phar.php), run the `build` script:

~~~bash
./build
~~~

_Note that it must be made executable (`chmod +x build`)._

Calling `./build --help` will list the options for the script:

- `-c-`: Skip Composer installation
- `-c+`: Update Composer packages
- `-p`: Prepare packages
- `-t`: Run tests
- `-r`: Run the blog
- `-v`: Output detailed information
- `--all`: Build system, prepare packages, run tests and run blog

Dependencies are installed using [Composer](http://getcomposer.org). If you don't have Composer installed, run `curl -sS https://getcomposer.org/installer | php` from the terminal.


## Testing

The unit tests use PHPUnit, and require the [`config.xml`](../tests/Blight/config.xml) file, and the [`bootstrap.php`](../tests/Blight/bootstrap.php) file. The tests can be run from the root source directory with the following command:

~~~bash
phpunit -c tests/Blight/config.xml -v
~~~

Alternatively, the tests can be run after building the site by calling:

~~~bash
./build -t
~~~
