=== Big Voodoo Git Repo ===
Contributors: bigvoodoo, firejdl
Tags: git, repository, devops
Requires at least: 3.0
Tested up to: 3.6.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Ensures the Git repositories are kept current and up to date with uploads made within WordPress.

== Description ==

This plugin will add or remove, commit, and push any files uploaded through WordPress's Media Library.

Any files uploaded ito the Media Library or edited after being uploaded will be added, and any files deleted will be removed.

This plugin makes several assumptions:

* DOCUMENT_ROOT is a clone of a git repository.
* whatever branch this clone is on is the branch that you wish to commit to.
* the clone is set up properly, including remotes.

License: [GPLv2 or later](http://www.gnu.org/licenses/gpl-2.0.html)

== Installation ==

1. Upload `git-repo-plugin` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. ???
1. PROFIT!!!

== Frequently Asked Questions ==

= Where does the plugin commit to? =

It assumes that DOCUMENT_ROOT is a clone of a git repository, and runs all operations in that directory. This means that remotes, etc. must be set up properly on this clone.

= What branch does the plugin commit to? =

Whatever branch is currently checked out.

= What user shows up in the commit log? =

The user is defined in **git-repo-plugin.php**:

`define( 'WP_GIT_USER', 'WordPress Git Plugin <' . get_bloginfo('admin_email') . '>' );`

== Changelog ==

= 0.1.0 =

* Initial release.

== Upgrade Notice ==

= 0.1.0 =

* Initial release.
