<?php
# change git name on all of my repositories

function absolutePath(string $path): string {
	return $path[0] != '~' ? $path : posix_getpwuid(posix_getuid())['dir'].substr($path, 1);
}

function ensureNotEmpty(string $prompt): string {
	do {
		$var = escapeshellarg(trim(readline($prompt)));
	} while (!$var);
	return $var;
}

function getDirectSubDirectories(string $dir): array {
	$names = array_diff(scandir($dir), ['.', '..', '.git']);
	foreach ($names as &$name)
		$name = $dir.'/'.$name;
	return array_filter($names, 'is_dir');
}

function isRepository(string $dir): bool {
	return in_array('.git', scandir($dir));
}

function stripWhitespaces(string $str): string {
	return str_replace(["\n", "\t"], [' ', ''], $str);
}

# run commands asynchronously
function asyncExec(string $command) {
	exec($command.' > /dev/null &');
}

function fixRepository(string $dir, string $oldName, string $newName) {
	# rather than escape the names, just put them in single quotes
	echo "Fixing $dir\n";
	
	# add unmodified file changes and commit before changing name
	$command1 = <<<EOD
cd "$dir" &&
git add -u :/ &&
git commit -m "fixing git username"
EOD;
	
	$command2 = <<<EOD
cd "$dir" &&
git filter-branch -f --commit-filter
	'if [ "\$GIT_AUTHOR_NAME" = "$oldName" ]; then
		export GIT_AUTHOR_NAME="$newName";
		export GIT_COMMITTER_NAME="$newName";
	fi;
	git commit-tree "$@"'
EOD;

	# we run two separate commands in case the first command fails
	@exec(stripWhitespaces($command1));
	asyncExec(stripWhitespaces($command2));
}

function fix(string $dir, string $oldName, string $newName) {
	if (isRepository($dir)) fixRepository($dir, $oldName, $newName);
	else
		foreach (getDirectSubDirectories($dir) as $subDirectory)
			fix($subDirectory, $oldName, $newName);
}

# -------------------- main --------------------
if (!debug_backtrace()) {
	$directories = [];
	
	while (true) {
		if ($input = readline('Directory to search or enter to continue: '))
			$input = absolutePath($input);
		else if (count($directories)) break;
		
		if (is_dir($input)) $directories[] = $input;
	}
	echo "Directories to search: \n".print_r($directories, true);
	
	$oldName = ensureNotEmpty('Previous git name: ');
	$newName = ensureNotEmpty('New git name: ');
	echo "If the directories contain numerous and/or humongous git repositories, go grab a coffee.\n";
	
	$start = microtime(true);
	foreach ($directories as $directory)
		fix(escapeshellarg($directory), $oldName, $newName);
	echo 'Took '.round(microtime(true) - $start, 2)." seconds.\n";
}