<?php
/**
 * Plugin Name:       Majesco Courses
 * Plugin URI:        https://www.colinduwe.com/
 * Description:       Displays Majesco&#39;s public Docebo course catalog.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Colin Duwe
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       majesco-docebo-block
 *
 * @package           majesco-docebo
 */

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function majesco_docebo_majesco_docebo_block_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'majesco_docebo_majesco_docebo_block_block_init' );
