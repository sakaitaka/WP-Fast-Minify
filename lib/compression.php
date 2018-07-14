<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function wp_html_compression_finish($html) {
    $wp_inline_js_converter = $GLOBALS['wp_inline_js_converter'];
    if ( !is_null($wp_inline_js_converter) ) {
        $wp_inline_js_converter->includes();
        return $wp_inline_js_converter->parseHTML($html);
    }
    return $html;
}

function wp_html_compression_start() {
    if ( !is_admin() ) {
        ob_start('wp_html_compression_finish');
    }
}

add_action('widgets_init', 'wp_html_compression_start',1);
