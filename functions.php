<?php
/**
 * Thoreau CBOX Child Theme Functions
 *
 * Theme amendments and overrides.
 *
 * @package Thoreau_CBOX_Child
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Set our version here.
define( 'THOREAU_CBOX_CHILD_VERSION', '1.0' );



// Bootstrap Infinity engine.
require_once 'engine/includes/custom.php';



/**
 * Enqueue CSS.
 *
 * @since 1.0
 */
function thoreau_cbox_enqueue_styles() {

	// Load our custom CSS.
	wp_enqueue_style(
		'thoreau_styles',
		get_stylesheet_directory_uri() . '/assets/css/thoreau.css',
		[ '@:dynamic', 'open-sans' ], // Dependencies.
		THOREAU_CBOX_CHILD_VERSION, // Version.
		'all' // Media.
	);

	// If we've got the homepage template.
	if ( is_page_template( 'templates/homepage-template.php' ) ) {

		// Force flexslider CSS to load.
		wp_enqueue_style(
			'flex_slider',
			get_template_directory_uri() . '/assets/css/flexslider/flexslider.css',
			[], // Dependencies.
			THOREAU_CBOX_CHILD_VERSION, // Version.
			'all' // Media.
		);

	}

	/*
	// Use the bundled version of Open Sans.
	wp_enqueue_style( 'open-sans' );
	*/

}

// Add a filter for the above.
add_action( 'wp_enqueue_scripts', 'thoreau_cbox_enqueue_styles', 100 );



/**
 * Enable TinyMCE editor in bbPress.
 *
 * @since 1.0
 *
 * @param array $args The TinyMCE arguments.
 */
function thoreau_enable_visual_editor( $args = [] ) {

	// Add TinyMCE.
	$args['tinymce'] = true;

	// --<
	return $args;

}

// Add filter for the above.
add_filter( 'bbp_after_get_the_content_parse_args', 'thoreau_enable_visual_editor' );



/**
 * Override default BP Groupblog nav item functionality
 *
 * @since 1.0
 */
function thoreau_groupblog_setup_nav() {

	// Only add for groups.
	if ( ! bp_is_group() ) {
		return;
	}

	// Only act if blog not embedded in group template.
	$checks = get_site_option( 'bp_groupblog_blog_defaults_options' );
	if ( $checks['deep_group_integration'] ) {
		return;
	}

	// Get current group.
	$current_group = groups_get_current_group();

	// Existing groupblog logic.
	$enabled = bp_groupblog_is_blog_enabled( $current_group->id );

	// Use mahype's fixes for the non-appearance of the groupblog tab.
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$is_new = isset( $_POST['groupblog-create-new'] ) ? sanitize_text_field( wp_unslash( $_POST['groupblog-create-new'] ) ) : false;

	if ( $enabled || 'yes' === $is_new ) {

		// Access BP.
		$bp = buddypress();

		// Group link.
		$group_link = bp_get_group_permalink( $current_group );

		// Parent slug.
		$parent_slug = isset( $bp->bp_nav[ $current_group->slug ] ) ? $current_group->slug : $bp->groups->slug;

		// Default name.
		$name = __( 'Blog', 'groupblog' );

		/**
		 * Filter the nav item name.
		 *
		 * @since 1.0
		 *
		 * @param str $name The default nav item name.
		 */
		$name = apply_filters( 'bp_groupblog_subnav_item_name', $name );

		/**
		 * Filter the nav item slug.
		 *
		 * @since 1.0
		 *
		 * @param str The default nav item slug.
		 */
		$slug = apply_filters( 'bp_groupblog_subnav_item_slug', 'blog' );

		// Is this a private group?
		if ( $current_group->status == 'private' ) {

			// Get group blog details.
			$blog_id = get_groupblog_blog_id( $current_group->id );
			$details = get_blog_details( $blog_id );

			// Is the group blog public?
			if ( (bool) $details->public == true ) {

				// Override slug.
				$slug = $details->path;

				// Override group link.
				$group_link = bp_get_root_domain();

			}

		}

		// Define subnav item.
		bp_core_new_subnav_item(
			[
				'name' => $name,
				'slug' => $slug,
				'parent_url' => $group_link,
				'parent_slug' => $parent_slug,
				'screen_function' => 'groupblog_screen_blog',
				'position' => 32,
				'item_css_id' => 'group-blog',
			]
		);

	}

}

// Do we have the BP Groupblog action?
if ( has_action( 'bp_setup_nav', 'bp_groupblog_setup_nav' ) ) {

	// Remove BP Groupblog's action.
	remove_action( 'bp_setup_nav', 'bp_groupblog_setup_nav' );

	// Replace with our own.
	add_action( 'bp_setup_nav', 'thoreau_groupblog_setup_nav' );

}



/**
 * Add our logo to the sliding header.
 *
 * @since 1.0
 */
function thoreau_cbox_add_logo_to_panel() {
	return get_stylesheet_directory_uri() . '/assets/images/thoreau-logo-panel.png';
}

/*
// Add action for the above.
add_action( 'bp_sliding_login_logo', 'thoreau_cbox_add_logo_to_panel' );
*/



/**
 * Use medium size "site images".
 *
 * @since 1.0
 *
 * @param str $type The existing avatar type.
 * @return str $type The modified avatar type.
 */
function thoreau_bloginfo_avatar_type( $type ) {
	return 'medium';
}

// Add filter for the above.
add_filter( 'bpgsites_bloginfo_avatar_type', 'thoreau_bloginfo_avatar_type' );
