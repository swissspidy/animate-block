<?php
/**
 * Plugin uninstall handler.
 *
 * @package Swissspidy\AnimateBlock
 */

namespace Swissspidy\AnimateBlock;

use WP_Filesystem_Base;

\defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/* @var WP_Filesystem_Base $wp_filesystem */
global $wp_filesystem;

if ( ! $wp_filesystem ) {
	require_once ABSPATH . '/wp-admin/includes/admin.php';

	\WP_Filesystem();
}

if ( $wp_filesystem ) {
	$wp_filesystem->rmdir( ANIMATION_DIR, true );
}

delete_post_meta_by_key( ANIMATION_FILE_META_KEY );
