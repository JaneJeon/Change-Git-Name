<?php
require 'Git.php';

const exclude = ['.', '..', '.git'];

class Dir {
	public static function absolutePath($path) {
		return $path[0] != '~' ? $path : posix_getpwuid(posix_getuid())['dir'].substr($path, 1);
	}
	
	public static function subDirectories($dir) {
		$names = array_diff(scandir($dir), exclude);
		foreach ($names as &$name)
			$name = $dir.'/'.$name;
		return array_filter($names, 'is_dir');
	}
	
	public static function fix($dir, $name, $newName, $newEmail) {
		sleep(0.1);
		
		if (Git::isRepo($dir)) {
			if ($newName || $newEmail)
				Git::fix($dir, $name, $newName, $newEmail);
			else {
				$remote = Git::remote($dir);
				if ($remote && !Git::isValid($dir, $remote))
					echo "Remote $remote in $dir is invalid!\n";
			}
		} else foreach (self::subDirectories($dir) as $subDirectory)
				self::fix($subDirectory, $name, $newName, $newEmail);
	}
}