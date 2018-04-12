<?php
require 'Command.php';

class Git {
	public static function isRepo($dir) {
		return in_array('.git', scandir($dir));
	}
	
	public static function command($dir, $command, $returnCode = false) {
		return Command::exec("cd '$dir'; $command", $returnCode);
	}
	
	public static function remote($dir) {
		return self::command($dir, 'git config --get remote.origin.url');
	}
	
	# check if remote is set properly
	public static function isValid($dir, $remote) {
		return !self::command($dir, "git ls-remote $remote", true);
	}
	
	# add unmodified file changes and commit before changing name
	private static function commit($dir) {
		$command = <<<SH
git add -u :/ ;
git commit -m 'fixing git'
SH;
		self::command($dir, $command);
	}
	
	# https://stackoverflow.com/a/4494037
	public static function fix($dir, $name, $newName, $newEmail) {
		$change = !$newName ? '' : <<<SH
export GIT_AUTHOR_NAME="$newName";
export GIT_COMMITTER_NAME="$newName";
SH;
		if ($newEmail) $change .= <<<SH
export GIT_AUTHOR_EMAIL="$newEmail";
export GIT_COMMITTER_EMAIL="$newEmail";
SH;
		$command = <<<SH
git filter-branch -f --commit-filter
	'if [ "\$GIT_AUTHOR_NAME" = "$name" ]; then
		$change
	fi;
	git commit-tree "$@"';
	git push --force
SH;
		self::commit($dir);
		self::command($dir, $command);
	}
}