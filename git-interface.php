<?php

/**
 * really simple class to call Git commands. it simply wraps git add, rm,
 * commit, and push calls with some escapeshellarg(), chdir(), and exec() calls
 */
class Git_Interface {
	/**
	 * add a list of files to the git repo.
	 * @param array list of file paths to add
	 * @return boolean false if there were errors
	 */
	public static function add( $files ) {
		return self::exec( 'git add ' . implode( ' ', self::escape_arg_list( $files ) ) );
	}

	/**
	 * remove a list of files from the git repo.
	 * @param array list of file paths to remove
	 * @return boolean false if there were errors
	 */
	public static function remove( $files ) {
		return self::exec( 'git rm -f ' . implode( ' ', self::escape_arg_list( $files ) ) );
	}

	/**
	 * commit a list of files to the git repo.
	 * @param array list of file paths to commit
	 * @param string commit message
	 * @return boolean false if there were errors
	 */
	public static function commit( $files, $message ) {
		return self::exec( 'git commit --author="' . WP_GIT_USER . '" -m ' . escapeshellarg( $message ) . ' -- ' . implode( ' ', self::escape_arg_list( $files ) ) );
	}

	/**
	 * push changes to the git repo upstream.
	 * @return boolean false if there were errors
	 */
	public static function push() {
		return self::exec( 'git push' );
	}

	/**
	 * calls escapeshellarg() on all elements of $args
	 * @param array list of arguments to escape
	 * @return array list of escaped arguments
	 */
	private static function escape_arg_list( $args ) {
		if( !is_array( $args ) ) {
			$args = array( $args );
		}
		array_walk( $args, function( $arg ) {
			$arg = escapeshellarg( $arg );
		});
		return $args;
	}

	/**
	 * saves the current working directory, changes directory to DOCUMENT_ROOT,
	 * executes the given $command, and changes directory back to the original.
	 * @param string the command to execute
	 * @return boolean false if there were errors
	 */
	private static function exec( $command ) {
		$pwd = getcwd();
		chdir( $_SERVER['DOCUMENT_ROOT'] );
		exec( $command. ' 2>&1', $output, $return_var );
		chdir( $pwd );
		return $return_var == 0;
	}
}
