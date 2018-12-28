<?php
/**
 * Plugin Name: Adobe Animate CC Animation Block
 * Plugin URI:  https://github.com/swissspidy/animate-block
 * Description: Embed Adobe Animate CC animations on your site.
 * Version:     1.0.0
 * Author:      Pascal Birchler
 * Author URI:  https://pascalbirchler.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: animate-block
 * Domain Path: /languages
 *
 * @package Swissspidy\AnimateBlock
 */

namespace Swissspidy\AnimateBlock;

\define( __NAMESPACE__ . '\PLUGIN_FILE', __FILE__ );
\define( __NAMESPACE__ . '\ANIMATION_DIR_NAME', 'animations' );
\define( __NAMESPACE__ . '\ANIMATION_DIR', WP_CONTENT_DIR . '/' . ANIMATION_DIR_NAME );
\define( __NAMESPACE__ . '\ANIMATION_FILE_META_KEY', 'animation_file' );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

if ( \function_exists( __NAMESPACE__ . '\bootstrap' ) ) {
	add_action( 'plugins_loaded', __NAMESPACE__ . '\bootstrap' );
}
