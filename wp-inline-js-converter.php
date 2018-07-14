<?php
/*
   Plugin Name: WP Fast Minify
   Plugin URI: http://wordpress.org/extend/plugins/wp-inline-js-converter/
   Version: 1.4.0
   Author: skita45
   Description: Compress HTML Code, And Converting Inline Script and Style To JavaScript and CSS Compressed File.
   Text Domain: wp-inline-js-converter
   License: GPLv3
   Domain Path: /languages
  */

/*
    "WP Fast Minify" Copyright (C) 2017 skita45  (email : sakai.taka3945@gmail.com)

    WP Fast Minify is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    any later version.
 
    WP Fast Minify is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with WP Fast Minify. If not, see http://www.gnu.org/licenses/gpl-3.0.html
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'WP_INLINE_JS_CONVERTER_VERSION' ) ) {
	return;
}

define( 'WP_INLINE_JS_CONVERTER_VERSION', '1.4.0' );
define( 'WP_INLINE_JS_CONVERTER_FILE', __FILE__ );
define( 'WP_INLINE_JS_CONVERTER_PATH', plugin_dir_path( WP_INLINE_JS_CONVERTER_FILE ) );
define( 'WP_INLINE_JS_CONVERTER_URL', plugin_dir_url( WP_INLINE_JS_CONVERTER_FILE ) );
register_activation_hook( WP_INLINE_JS_CONVERTER_FILE, array( 'WPInlineJSConverter', 'activate' ) );
register_deactivation_hook( WP_INLINE_JS_CONVERTER_FILE, array( 'WPInlineJSConverter', 'deactivate' ) );

final class WPInlineJSConverter {
    private static $instance = null;

    protected $html;
    private $active = true;
    private $compressJS = true;
    private $compressCSS = true;
    private $compressHTML = true;
    private $tofileJS = true;
    private $tofileCSS = true;

    public function setTofileJS($is_tofileJS) {
        $this->$tofileJS = $is_tofileJS;
    }

    public function getTofileJS() {
        return $this->$tofileJS;
    }

    public function setTofileCSS($is_tofileCSS) {
        $this->$tofileCSS = $is_tofileCSS;
    }

    public function getTofileCSS() {
        return $this->$tofileCSS;
    }

    public function setActive($is_active) {
        $this->active = $is_active;
    }

    public function getActive() {
        return $this->active;
    }

    public function setCompressJS($is_compress) {
        $this->compressJS = $is_compress;
    }

    public function getCompressJS() {
        return $this->compressJS;
    }

    public function setCompressCSS($is_compress) {
        $this->compressCSS = $is_compress;
    }

    public function getCompressCSS() {
        return $this->compressCSS;
    }

    public function setCompressHTML($is_compress) {
        $this->compressHTML = $is_compress;
    }

    public function getCompressHTML() {
        return $this->compressHTML;
    }

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self;
        }
        return self::$instance;
    }
    private function __construct() {
        $this->includes();
        add_action( 'plugins_loaded', array( $this, 'init' ) );
    }
    public function __toString() {
        return $this->html;
    }
    protected function jsToFile($html) {
        $pattern = '/<(?<script>script).*?<\/script\s*>|<(?<style>style).*?<\/style\s*>|<!(?<comment>--).*?-->|<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
        $html = '';
        $raw_tag = false;
        foreach ($matches as $token) {
            $tag = (isset($token['tag'])) ? strtolower($token['tag']) : null;
            $content = $token[0];
            
            if (is_null($tag)) {
                if ( !empty($token['script']) && $this->tofileJS ) {
                    $regax = "/<script((?:(?!src=).)*?)>(.*?)<\/script>/smix";
                    if ( preg_match($regax, $content) ) {
                        $script = array();
                        if ( preg_match($regax, $content, $script) ) {
                            $js = $script[2];
                            if ( $this->compressJS ) {
                                $js = $this->compress_script($js);
                            }
                            $filepath = $this->getFilePath($js, "js");
                            $url = $this->getUrlPath($js, "js");
                            if( !file_exists($filepath) ){
                                file_put_contents($filepath, $js);
                            }
                            $pos = mb_strpos($content, ">");
                            $src = ' src="' . $url . '"';
                            $content = substr ( $this->insertStr($script[0], $src, $pos), 0, strlen($src) + $pos + 1). "</script>";
                        }
                    }
                } else if ( !empty($token['style']) && $this->tofileCSS ) {
                    $regax = "/<style(.*?)>(.*?)<\/style>/smix";
                    if ( preg_match($regax, $content) ) {
                        $css = array();
                        if ( preg_match($regax, $content, $css) ) {
                            $css = $css[2];
                            if ( $this->compressCSS ) {
                                $css = $this->compress_css($css);
                            }
                            $filepath = $this->getFilePath($css, "css");
                            $url = $this->getUrlPath($css, "css");
                            if( !file_exists($filepath) ){
                                file_put_contents($filepath, $css);
                            }
                            $content = '<link rel="stylesheet" href="' . $url . '"' . ' />';
                        }
                    }
                }
            } else if ( $this->compressHTML ) {
                if ($tag == 'pre' || $tag == 'textarea') {
                    $raw_tag = $tag;
                } else if ($tag == '/pre' || $tag == '/textarea') {
                    $raw_tag = false;
                } else{
                    if ( !$raw_tag ) {
                        $content = preg_replace('/(\s+)(\w++(?<!\baction|\balt|\bcontent|\bsrc)="")/', '$1', $content);
                        $content = str_replace(' />', '/>', $content);
                        $content = $this->compress_html($content);
                    }
                }
            }

            $html .= $content;
        }
        
        return $html;
    }
    protected function uniqueFilename($content) {
        return hash("md5", $content);
    }
    protected function insertStr($text, $insert, $num){
        return preg_replace("/^.{0,$num}+\K/us", $insert, $text);
    }
    protected function getUrlPath($content, $extension) {
        return WP_INLINE_JS_CONVERTER_URL . "cache/" . $this->uniqueFilename($content) .".".$extension;
    }
    protected function getFilePath($content, $extension) {
        return WP_INLINE_JS_CONVERTER_PATH . "cache/" . $this->uniqueFilename($content) .".".$extension;
    }
	public function parseHTML($html) {
        if ($this->active && !empty($html)) {
            $this->html = $this->jsToFile($html);
        } else {
            $this->html = $html;
        }
        return $this->html;
    }
    protected function compress_html($str) {
        $str = str_replace("\t", ' ', $str);
        $str = str_replace("\n",  '', $str);
        $str = str_replace("\r",  '', $str);
        
        while (stristr($str, '  '))
        {
            $str = str_replace('  ', ' ', $str);
        }
        
        return $str;
    }
    protected function compress_css($css) {
      $replace = array(
        "#/\*.*?\*/#s" => "",  // Strip C style comments.
        "#\s\s+#"      => " ", // Strip excess whitespace.
      );
      $search = array_keys($replace);
      $css = preg_replace($search, $replace, $css);
    
      $replace = array(
        ": "  => ":",
        "; "  => ";",
        " {"  => "{",
        " }"  => "}",
        ", "  => ",",
        "{ "  => "{",
        ";}"  => "}", // Strip optional semicolons.
        ",\n" => ",", // Don't wrap multiple selectors.
        "\n}" => "}", // Don't wrap closing braces.
        "} "  => "}\n", // Put each rule on it's own line.
      );
      $search = array_keys($replace);
      $css = str_replace($search, $replace, $css);
    
      return trim($css);
    }
    protected function compress_script( $buffer ) {
        $buffer = preg_replace('/<p>/s', "", $buffer );
        $buffer = preg_replace('/<\/p>/s', "", $buffer );
        $replace = array(
            '#\'([^\n\']*?)/\*([^\n\']*)\'#' => "'\1/'+\'\'+'*\2'", // remove comments from ' strings
            '#\"([^\n\"]*?)/\*([^\n\"]*)\"#' => '"\1/"+\'\'+"*\2"', // remove comments from " strings
            '#/\*.*?\*/#s'            => "",      // strip C style comments
            '#[\r\n]+#'               => "\n",    // remove blank lines and \r's
            '#\n([ \t]*//.*?\n)*#s'   => "\n",    // strip line comments (whole line only)
            '#([^\\])//([^\'"\n]*)\n#s' => "\\1\n",
                                                    // strip line comments
                                                    // (that aren't possibly in strings or regex's)
            '#\n\s+#'                 => "\n",    // strip excess whitespace
            '#\s+\n#'                 => "\n",    // strip excess whitespace
            '#(//[^\n]*\n)#s'         => "\\1\n", // extra line feed after any comments left
                                                    // (important given later replacements)
            '#/([\'"])\+\'\'\+([\'"])\*#' => "/*" // restore comments in strings
        );

        $search = array_keys( $replace );
        $script = preg_replace( $search, $replace, $buffer );

        $replace = array(
            "&&\n" => "&&",
            "||\n" => "||",
            "(\n"  => "(",
            ")\n"  => ")",
            "[\n"  => "[",
            "]\n"  => "]",
            "+\n"  => "+",
            ",\n"  => ",",
            "?\n"  => "?",
            ":\n"  => ":",
            ";\n"  => ";",
            "{\n"  => "{",
            "\n]"  => "]",
            "\n)"  => ")",
            "\n}"  => "}",
            "\n\n" => "\n"
        );

        $search = array_keys( $replace );
        $script = str_replace( $search, $replace, $script );
        return $script;
    }
    /**
     * Load plugin function files here.
     */
    public function includes() {
        $wijc_active = get_option( 'wijc_active' );
        $wijc_tofileJS = get_option( 'wijc_tofile' );
        $wijc_tofileCSS = get_option( 'wijc_tofile_css' );
        $wijc_compressJS = get_option( 'wijc_compress' );
        $wijc_compressCSS = get_option( 'wijc_compress_css' );
        $wijc_compressHTML = get_option( 'wijc_compress_html' );
        $this->active = !$wijc_active || $wijc_active == "yes";
        $this->tofileJS = !$wijc_tofileJS || $wijc_tofileJS == "yes";
        $this->tofileCSS = !$wijc_tofileCSS || $wijc_tofileCSS == "yes";
        $this->compressJS = !$wijc_compressJS || $wijc_compressJS == "yes";
        $this->compressCSS = !$wijc_compressCSS || $wijc_compressCSS == "yes";
        $this->compressHTML = !$wijc_compressHTML || $wijc_compressHTML == "yes";
    }
    /**
     * Code you want to run when all other plugins loaded.
     */
    public function init() {
        load_plugin_textdomain( 'wp-inline-js-converter', false, 'wp-inline-js-converter/languages' );
    }
    /**
     * Run when activate plugin.
     */
    public static function activate() {
    }
    /**
     * Run when deactivate plugin.
     */
    public static function deactivate() {
    }
}
function wp_inline_js_converter() {
    return WPInlineJSConverter::get_instance();
}
$GLOBALS['wp_inline_js_converter'] = wp_inline_js_converter();

require_once(WP_INLINE_JS_CONVERTER_PATH . "lib/compression.php");
require_once(WP_INLINE_JS_CONVERTER_PATH . "admin/admin_page.php");
