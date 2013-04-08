<?php
namespace Blight;

class EchoHandler extends \Monolog\Handler\AbstractHandler {
	public function handle(array $record){
		if(!IS_CLI || !VERBOSE) return;

		$timestamp	= number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4, '.', '');
		$memstamp	= floor(memory_get_usage()/1024).'k';

		echo $timestamp.'-'.$memstamp.': '.$record['message'].PHP_EOL;
	}
};
