<?php

class Command {
	public static function exec($command, $getExitCode) {
		$output = [];
		$exitCode = 0;
		if ($getExitCode) $command .= ' 2>/dev/null';
		$result = exec(self::stripWhitespaces($command), $output, $exitCode);
		return $getExitCode ? $exitCode : $result;
	}
	
	private static function stripWhitespaces($str) {
		return str_replace(["\n", "\t"], [' ', ''], $str);
	}
	
	public static function read($prompt) {
		return trim(readline($prompt));
	}
	
	public static function ensureNotEmpty($prompt) {
		do {
			$var = self::read($prompt);
		} while (empty($var));
		return $var;
	}
}