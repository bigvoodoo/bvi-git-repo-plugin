<?php
/**
 * Plugin Name: Big Voodoo Git Repo
 * Plugin URI: http://www.bigvoodoo.com
 * Description: Ensures the Git repositories are kept current and up to date with uploads made within WordPress.
 * Author: Big Voodoo Interactive
 * Version: 0.1.0
 * Author URI: http://www.bigvoodoo.com
 * @author Big Voodoo Interactive
 * @TODO error reporting
 */

define( 'WP_GIT_USER', 'WordPress Git Plugin <' . get_bloginfo('admin_email') . '>' );

require_once( 'git-interface.php' );

/**
 * static class used for namespacing.
 */
class Git_Plugin {
	/**
	 * gets a list of all files associated with an attachment having ID=$ID.
	 * @param int the ID of the attachment
	 * @return array the list of files associated with $ID
	 */
	private static function get_attachment_files( $ID ) {
		$meta = wp_get_attachment_metadata( $ID );
		$file = get_attached_file( $ID );

		// figure out the list of files
		// the main file is $file
		$files = array( $file );

		// do we have a thumbnail?
		if( !empty( $meta['thumb'] ) ) {
			$files[] = str_replace( basename( $file ), $meta['thumb'], $file );
		}

		// do we have intermediate sizes created automatically by WP?
		foreach ( get_intermediate_image_sizes() as $size ) {
			if ( $intermediate = image_get_intermediate_size( $ID, $size ) ) {
				$files[] = self::add_upload_basedir( $intermediate['path'] );
			}
		}

		// are there any backups?
		$backup_sizes = get_post_meta( $ID, '_wp_attachment_backup_sizes', true );
		if( $backup_sizes ) {
			foreach ( $backup_sizes as $size ) {
				$backup_file = path_join( dirname( $meta['file'] ), $size['file'] );
				$files[] = self::add_upload_basedir( $backup_file );
			}
		}

		// remove duplicates & return
		return array_unique( $files );
	}

	/**
	 * uses wp_upload_dir(), path_join(), & realpath() to get an absolute path
	 * to the file
	 * @param string the relative path to the file
	 * @return string the absolute path to the file
	 */
	private static function add_upload_basedir( $path ) {
		$uploadpath = wp_upload_dir();
		return realpath( path_join( $uploadpath['basedir'], $path ) );
	}

	/**
	 * filter function for when an attachment gets added.
	 */
	public static function add_attachment( $metadata, $ID ) {
		// non-images get $metadata as an empty array
		if( isset( $metadata['file'] ) ) {
			// new uploads don't have the meta data for extra sizes stored yet, so
			// grab it from $metadata
			$files = array( self::add_upload_basedir( $metadata['file'] ) );
			if( isset( $metadata['sizes'] ) ) {
				foreach( $metadata['sizes'] as $size ) {
					$files[] = self::add_upload_basedir( $size['file'] );
				}
			}
		} else {
			// non-images can still use self::get_attachment_files()
			$files = self::get_attachment_files( $ID );
		}

		// add, commit, & push!
		Git_Interface::add( $files );
		Git_Interface::commit( $files, 'Added attachment '.$ID.': '.basename( $files[0] ) );
		Git_Interface::push();
		return $metadata;
	}

	/**
	 * action function for when an attachment gets edited.
	 */
	public static function edit_attachment( $ID ) {
		$files = self::get_attachment_files( $ID );

		// add, commit, & push!
		Git_Interface::add( $files );
		Git_Interface::commit( $files, 'Edited attachment '.$ID.': '.basename( $files[0] ) );
		Git_Interface::push();
	}

	/**
	 * action function for when an attachment gets deleted.
	 */
	public static function delete_attachment( $ID ) {
		$files = self::get_attachment_files( $ID );

		// remove, commit, & push!
		Git_Interface::remove( $files );
		Git_Interface::commit( $files, 'Deleted attachment '.$ID.': '.basename( $files[0] ) );
		Git_Interface::push();
	}
}

// sadly, the add_attachment action gets called before any of the extra sizes
// are created. instead, we'll hook into the 'wp_generate_attachment_metadata'
// filter, which works just fine.
// add_action( 'add_attachment', array('Git_Plugin', 'add_attachment'), 999 );
add_filter( 'wp_generate_attachment_metadata', array('Git_Plugin', 'add_attachment'), 999, 2 );

// hook into the 'edit_attachment' action
add_action( 'edit_attachment', array('Git_Plugin', 'edit_attachment'), 999 );

// hook into the 'delete_attachment' action
add_action( 'delete_attachment', array('Git_Plugin', 'delete_attachment'), 999 );
