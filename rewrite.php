<?php
require 'Dir.php';
# change git name & email on all of my repositories

$directories = [];

while (true) {
	if ($input = readline('Directory to search or enter to continue: '))
		$input = Dir::absolutePath($input);
	else if (count($directories)) break;
	
	if (is_dir($input)) $directories[] = $input;
}
echo "Directories to search: \n".print_r($directories, true);

$name = Command::ensureNotEmpty('Git name: ');
$newName = Command::read('New git name or enter to continue: ');
$newEmail = Command::read('New git email or enter to continue: ');
echo $newName || $newEmail ? "Fixing git...\n" : "Checking validity of remotes...\n";
echo "If the directories contain numerous and/or humongous git repositories, go grab a coffee.\n";

$start = microtime(true);
foreach ($directories as $directory)
	Dir::fix($directory, $name, $newName, $newEmail);

echo 'Took '.round(microtime(true) - $start, 2)." seconds.\n";