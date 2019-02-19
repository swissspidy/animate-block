<?php

namespace Swissspidy\AnimateBlock;

use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use WP_Post;

function bootstrap() {
	add_action( 'init', __NAMESPACE__ . '\load_textdomain' );

	add_action( 'init', __NAMESPACE__ . '\register_editor_assets' );
	add_action( 'init', __NAMESPACE__ . '\register_block_types' );
	add_action( 'init', __NAMESPACE__ . '\register_rest_field' );
	add_action( 'init', __NAMESPACE__ . '\register_post_meta' );

	add_action( 'rest_insert_attachment', __NAMESPACE__ . '\rest_extract_zip_attachment', 10, 3 );

	add_filter( 'wp_prepare_attachment_for_js', __NAMESPACE__ . '\filter_attachment_for_js', 10, 2 );

	add_filter( 'upload_mimes', __NAMESPACE__ . '\filter_upload_mimes' );
}

/**
 * Filters the list of allowed mime types.
 *
 * @param array $mimes List of mime types.
 * @return array Filtered list of mime types.
 */
function upload_mimes( $mimes ) {
	$mimes['zip'] = 'application/zip';

	return $mimes;
}

/**
 * Loads the plugin's translations.
 */
function load_textdomain() {
	load_plugin_textdomain(
		'animate-block',
		false,
		\dirname( plugin_basename( PLUGIN_FILE ) ) . '/languages'
	);
}

/**
 * Registers JavaScript and CSS for the block editor.
 */
function register_editor_assets() {
	if ( ! \function_exists( 'register_block_type' ) ) {
		return;
	}

	wp_register_script(
		'animate-block',
		plugins_url( 'assets/js/editor.js', __DIR__ ),
		[
			'wp-blocks',
			'wp-components',
			'wp-data',
			'wp-edit-post',
			'wp-editor',
			'wp-element',
			'wp-i18n',
		],
		filemtime( plugin_dir_path( __DIR__ ) . 'assets/js/editor.js' ),
		true
	);

	wp_set_script_translations( 'animate-block', 'animate-block' );

	wp_register_style(
		'animate-block',
		plugins_url( 'assets/css/editor.css', __DIR__ ),
		[],
		filemtime( plugin_dir_path( __DIR__ ) . 'assets/css/editor.css' )
	);
}

/**
 * Registers the custom block types for server side rendering.
 */
function register_block_types(): void {
	if ( ! \function_exists( 'register_block_type' ) ) {
		return;
	}

	register_block_type(
		'swissspidy/animation',
		[
			'editor_script' => 'animate-block',
			'editor_style'  => 'animate-block',
		]
	);
}

function register_rest_field(): void {
	\register_rest_field(
		'attachment',
		'animation_url',
		[
			'get_callback' => __NAMESPACE__ . '\rest_get_animation_url',
			'schema'       => [
				'description' => __( 'Animation URL', 'animate-block' ),
				'type'        => 'string',
				'context'     => [ 'view', 'edit' ],
			],
		]
	);
}

function rest_get_animation_url( array $attachment ) {
	return get_animation_url( get_post( $attachment['id'] ) );
}

function get_animation_url( WP_Post $attachment ) {
	$animation = get_post_meta( $attachment->ID, ANIMATION_FILE_META_KEY, true );

	if ( ! $animation ) {
		return null;
	}

	return content_url( str_replace( WP_CONTENT_DIR, '', $animation ) );
}

function register_post_meta(): void {
	\register_post_meta(
		'attachment',
		ANIMATION_FILE_META_KEY,
		[
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => false,
		]
	);
}

/**
 * Filters the attachment data prepared for JavaScript.
 *
 * @param array   $response   Array of prepared attachment data.
 * @param WP_Post $attachment Attachment object.
 *
 * @return array Filtered attachment data.
 */
function filter_attachment_for_js( $response, WP_Post $attachment ) {
	$response['animation_url'] = get_animation_url( $attachment );

	return $response;
}

/**
 * Extracts a single ZIP attachment when created via the REST API.
 *
 * @todo Use something like wp_unique_filename() to create unique directory names.
 * @todo Only act on ZIP archives uploaded via animation block.
 *
 * @param \WP_Post         $attachment Inserted or updated attachment
 *                                     object.
 * @param \WP_REST_Request $request    The request sent to the API.
 * @param bool             $creating   True when creating an attachment, false when updating.
 */
function rest_extract_zip_attachment( WP_Post $attachment, \WP_REST_Request $request = null, $creating = false ) {
	if ( ! $creating ) {
		return;
	}

	if ( 'application/zip' !== $attachment->post_mime_type ) {
		return;
	}

	$zip = get_attached_file( $attachment->ID );

	if ( ! $zip ) {
		return;
	}

	if ( ! function_exists( 'unzip_file' ) ) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
	}

	$fs_available = WP_Filesystem();

	if ( ! $fs_available ) {
		return;
	}

	$to = sanitize_file_name( pathinfo( basename( $zip ), PATHINFO_FILENAME ) );

	$result = unzip_file( $zip, trailingslashit( ANIMATION_DIR ) . $to );

	if ( ! $result ) {
		return;
	}

	$animation = find_html_file_in_dir( trailingslashit( ANIMATION_DIR ) . $to );

	if ( $animation ) {
		$relative_path = str_replace( WP_CONTENT_DIR, '', $animation );
		add_post_meta( $attachment->ID, ANIMATION_FILE_META_KEY, $relative_path, true );
	}
}

function find_html_file_in_dir( $dir ): ?string {
	if ( ! is_dir( $dir ) ) {
		return null;
	}

	$files = new RecursiveIteratorIterator(
		new RecursiveCallbackFilterIterator(
			new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::UNIX_PATHS ),
			function ( $file, $key, $iterator ) {
				/** @var RecursiveCallbackFilterIterator $iterator */
				/** @var SplFileInfo $file */
				return $iterator->hasChildren() || ( $file->isFile() && 'html' === $file->getExtension() );
			}
		),
		RecursiveIteratorIterator::CHILD_FIRST
	);

	foreach ( $files as $file ) {
		/** @var SplFileInfo $file */
		if ( $file->isFile() && 'html' === $file->getExtension() && $file->isReadable() ) {
			return $file->getRealPath();
		}
	}

	return null;
}
