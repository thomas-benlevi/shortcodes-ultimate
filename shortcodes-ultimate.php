<?php

	/*
	  Plugin Name: Shortcodes Ultimate
	  Plugin URI: http://ilovecode.ru/?p=122
	  Version: 1.5.0
	  Author: Vladimir Anokhin
	  Author URI: http://ilovecode.ru/
	  Description: Provides support for many easy to use shortcodes
	  Text Domain: shortcodes-ultimate
	  Domain Path: /languages
	  License: GPL2
	 */

	/**
	 * Plugin initialization
	 */
	function su_plugin_init() {

		// Make plugin available for tramslation
		load_plugin_textdomain( 'shortcodes-ultimate', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		// Load libs
		require_once( dirname( __FILE__ ) . '/lib/admin.php' );
		require_once( dirname( __FILE__ ) . '/lib/color.php' );
		require_once( dirname( __FILE__ ) . '/lib/csv.php' );
		require_once( dirname( __FILE__ ) . '/lib/media.php' );
		require_once( dirname( __FILE__ ) . '/lib/shortcodes.php' );

		// Enable shortcodes in text widgets
		add_filter( 'widget_text', 'do_shortcode' );

		// Enable auto-formatting
		if ( get_option( 'su_disable_custom_formatting' ) != 'on' ) {

			// Disable WordPress native formatters
			remove_filter( 'the_content', 'wpautop' );
			remove_filter( 'the_content', 'wptexturize' );

			// Apply custom formatter function
			add_filter( 'the_content', 'su_custom_formatter', 99 );
			add_filter( 'widget_text', 'su_custom_formatter', 99 );
		}

		// Fix for large posts, http://core.trac.wordpress.org/ticket/8553
		@ini_set( 'pcre.backtrack_limit', 500000 );

		// Register and enqueue styles and scripts
		if ( !is_admin() ) {

			// Register
			wp_register_style( 'shortcodes-ultimate', su_plugin_url() . '/css/style.css', false, su_get_version(), 'all' );
			wp_register_script( 'shortcodes-ultimate', su_plugin_url() . '/js/init.js', false, su_get_version(), false );
			wp_register_script( 'jwplayer', su_plugin_url() . '/js/jwplayer.js', false, su_get_version(), false );

			// Enqueue
			wp_enqueue_style( 'shortcodes-ultimate' );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jwplayer' );
			wp_enqueue_script( 'shortcodes-ultimate' );
		}

		// Register shortcodes
		add_shortcode( su_compatibility_mode_prefix() . 'heading', 'su_heading_shortcode' );
		add_shortcode( su_compatibility_mode_prefix() . 'frame', 'su_frame_shortcode' );
		add_shortcode( su_compatibility_mode_prefix() . 'tabs', 'su_tabs' );
		add_shortcode( su_compatibility_mode_prefix() . 'tab', 'su_tab' );
		add_shortcode( su_compatibility_mode_prefix() . 'spoiler', 'su_spoiler_shortcode' );
		add_shortcode( su_compatibility_mode_prefix() . 'divider', 'su_divider_shortcode' );
		add_shortcode( su_compatibility_mode_prefix() . 'spacer', 'su_spacer_shortcode' );
		add_shortcode( su_compatibility_mode_prefix() . 'quote', 'su_quote_shortcode' );
		add_shortcode( su_compatibility_mode_prefix() . 'pullquote', 'su_pullquote_shortcode' );
		add_shortcode( su_compatibility_mode_prefix() . 'highlight', 'su_highlight_shortcode' );
		add_shortcode( su_compatibility_mode_prefix() . 'button', 'su_button_shortcode' );
		add_shortcode( su_compatibility_mode_prefix() . 'fancy_link', 'su_fancy_link_shortcode' );
		add_shortcode( su_compatibility_mode_prefix() . 'service', 'su_service_shortcode' );
		add_shortcode( su_compatibility_mode_prefix() . 'box', 'su_box_shortcode' );
		add_shortcode( su_compatibility_mode_prefix() . 'note', 'su_note_shortcode' );
		add_shortcode( su_compatibility_mode_prefix() . 'list', 'su_list_shortcode' );
		add_shortcode( su_compatibility_mode_prefix() . 'column', 'su_column_shortcode' );
		add_shortcode( su_compatibility_mode_prefix() . 'media', 'su_media_shortcode' );
		add_shortcode( su_compatibility_mode_prefix() . 'table', 'su_table_shortcode' );
	}

	add_action( 'init', 'su_plugin_init' );

	/**
	 * Returns current plugin version.
	 *
	 * @return string Plugin version
	 */
	function su_get_version() {
		if ( !function_exists( 'get_plugins' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
		$plugin_file = basename( ( __FILE__ ) );
		return $plugin_folder[$plugin_file]['Version'];
	}

	/**
	 * Returns current plugin url
	 *
	 * @return string Plugin url
	 */
	function su_plugin_url() {
		return plugins_url( basename( __FILE__, '.php' ), dirname( __FILE__ ) );
	}

	/**
	 * Shortcode names prefix in compatibility mode
	 *
	 * @return string Special prefix
	 */
	function su_compatibility_mode_prefix() {
		$prefix = ( get_option( 'su_compatibility_mode' ) == 'on' ) ? 'gn_' : '';
		return $prefix;
	}

	/**
	 * Hook to translate plugin information
	 */
	function su_add_locale_strings() {
		$strings = __( 'Shortcodes Ultimate', 'shortcodes-ultimate' ) . __( 'Vladimir Anokhin', 'shortcodes-ultimate' ) . __( 'Provides support for many easy to use shortcodes', 'shortcodes-ultimate' );
	}

	/**
	 * Disable auto-formatting for shortcodes
	 *
	 * @param string $content
	 * @return string Formatted content with clean shortcodes content
	 */
	function su_custom_formatter( $content ) {
		$new_content = '';

		// Matches the contents and the open and closing tags
		$pattern_full = '{(\[raw\].*?\[/raw\])}is';

		// Matches just the contents
		$pattern_contents = '{\[raw\](.*?)\[/raw\]}is';

		// Divide content into pieces
		$pieces = preg_split( $pattern_full, $content, -1, PREG_SPLIT_DELIM_CAPTURE );

		// Loop over pieces
		foreach ( $pieces as $piece ) {

			// Look for presence of the shortcode
			if ( preg_match( $pattern_contents, $piece, $matches ) ) {

				// Append to content (no formatting)
				$new_content .= $matches[1];
			} else {

				// Format and append to content
				$new_content .= wptexturize( wpautop( $piece ) );
			}
		}

		return $new_content;
	}
?>