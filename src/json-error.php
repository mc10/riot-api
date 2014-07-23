<?php
	if (!function_exists('json_last_error_msg')) {
		function json_last_error_msg() {
			switch (json_last_error()) {
				case JSON_ERROR_DEPTH:
					$error = 'Maximum stack depth exceeded';
					break;

				case JSON_ERROR_STATE_MISMATCH:
					$error = 'Underflow or the modes mismatch';
					break;

				case JSON_ERROR_CTRL_CHAR:
					$error = 'Unexpected control character found';
					break;

				case JSON_ERROR_SYNTAX:
					$error = 'Syntax error, malformed JSON';
					break;

				case JSON_ERROR_UTF8:
					$error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
					break;

				default:
					return;
			}

			return $error;
		}
	}
