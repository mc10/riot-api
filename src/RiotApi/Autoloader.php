<?php
	namespace RiotApi;

	spl_autoload_register(function($class) {
		$path = substr(str_replace('\\', '/', $class), strlen(__NAMESPACE__));
		$path = __DIR__ . $path . '.php';
		if (file_exists($path)) {
			require $path;
		}
	});
