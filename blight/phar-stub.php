<?php
Phar::mapPhar('Blight.phar');

// Load app
include 'phar://'.__FILE__.'/blight.php';

__HALT_COMPILER();
?>