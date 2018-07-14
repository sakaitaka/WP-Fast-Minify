<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function wp_inline_js_converter_admin_menu() {
    add_menu_page( 'WP Fast Minify Options', 'WP Fast Minify', 'manage_options', 'wp_inline_js_converter_options', 'wp_inline_js_converter_menu_options' );
}
add_action( 'admin_menu', 'wp_inline_js_converter_admin_menu' );
function deleteData ( $dir ) {
    if ( $dirHandle = opendir ( $dir )) {
        while ( false !== ( $fileName = readdir ( $dirHandle ) ) ) {
            if ( $fileName != "." && $fileName != ".." ) {
                unlink ( $dir.$fileName );
            }
        }
        closedir ( $dirHandle );
    }
}
function wp_inline_js_converter_menu_options() {
    if ( !current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.', 'wp-inline-js-converter' ) );
    }
    $wijc_active = get_option( 'wijc_active' );
    $wijc_tofileJS = get_option( 'wijc_tofile' );
    $wijc_tofileCSS = get_option( 'wijc_tofile_css' );
    $wijc_compressJS = get_option( 'wijc_compress' );
    $wijc_compressCSS = get_option( 'wijc_compress_css' );
    $wijc_compressHTML = get_option( 'wijc_compress_html' );

    if ( !$wijc_active ) $wijc_active = 'yes';
    if ( !$wijc_tofileJS ) $wijc_tofileJS = 'yes';
    if ( !$wijc_tofileCSS ) $wijc_tofileCSS = 'yes';
    if ( !$wijc_compressJS ) $wijc_compressJS = 'yes';
    if ( !$wijc_compressCSS ) $wijc_compressCSS = 'yes';
    if ( !$wijc_compressHTML ) $wijc_compressHTML = 'yes';

    if ( isset($_POST[ 'wp_inline_js_converter_submit_hidden' ]) && $_POST[ 'wp_inline_js_converter_submit_hidden' ] == 'Y' ) {
        if ( isset($_POST["save"]) ) {
            if ( isset( $_POST[ 'wijc_active' ] ) ) $wijc_active = filter_var ( $_POST[ 'wijc_active' ], FILTER_SANITIZE_STRING ); else $wijc_active = 'yes';
            if ( isset( $_POST[ 'wijc_tofileJS' ] ) ) $wijc_tofileJS = filter_var ( $_POST[ 'wijc_tofileJS' ], FILTER_SANITIZE_STRING ); else $wijc_tofileJS = 'yes';
            if ( isset( $_POST[ 'wijc_tofileCSS' ] ) ) $wijc_tofileCSS = filter_var ( $_POST[ 'wijc_tofileCSS' ], FILTER_SANITIZE_STRING ); else $wijc_tofileCSS = 'yes';
            if ( isset( $_POST[ 'wijc_compressJS' ] ) ) $wijc_compressJS = filter_var ( $_POST[ 'wijc_compressJS' ], FILTER_SANITIZE_STRING ); else $wijc_compressJS = 'yes';
            if ( isset( $_POST[ 'wijc_compressCSS' ] ) ) $wijc_compressCSS = filter_var ( $_POST[ 'wijc_compressCSS' ], FILTER_SANITIZE_STRING ); else $wijc_compressCSS = 'yes';
            if ( isset( $_POST[ 'wijc_compressHTML' ] ) ) $wijc_compressHTML = filter_var ( $_POST[ 'wijc_compressHTML' ], FILTER_SANITIZE_STRING ); else $wijc_compressHTML = 'yes';
            update_option( 'wijc_active', $wijc_active );
            update_option( 'wijc_tofile', $wijc_tofileJS );
            update_option( 'wijc_tofile_css', $wijc_tofileCSS );
            update_option( 'wijc_compress', $wijc_compressJS );
            update_option( 'wijc_compress_css', $wijc_compressCSS );
            update_option( 'wijc_compress_html', $wijc_compressHTML );
            echo '<div class="updated"><p><strong>' . __( 'Settings saved.', 'wp-inline-js-converter' ) . '</strong></p></div>';
        } else if ( isset($_POST["clear"]) ) {
            deleteData(__DIR__ . "/../cache/");
            echo '<div class="updated"><p><strong>' . __( 'JavaScript and CSS cache files cleared.', 'wp-inline-js-converter' ) . '</strong></p></div>';
        }
    }
    ?>
<style>
    #wijc label {
        white-space: nowrap;
    }
    #wijc input[type="radio"] {
        margin-left: 15px;
    }
    #wijc input[type="radio"]:first-child {
        margin-left: 0;
    }
    #wijc .value {
        display: inline-block;
        min-width: 50px;
    }
    @media screen and (max-width: 500px) {
        #wjcs label {
            white-space:normal;
        }
    }
</style>
<div class="wrap">
<h2><?php echo __('WP Fast Minify Settings', 'wp-inline-js-converter'); ?></h2>
    <form name="form1" id="wijc" method="post" action="">
        <input type="hidden" name="wp_inline_js_converter_submit_hidden" value="Y">
        <table class="form-table">
            <tbody>
                <tr class="wijc_active">
                    <th><label><?php echo __( 'WP Fast Minify', 'wp-inline-js-converter' ); ?></label></th>
                    <td>
                        <input type="radio" name="wijc_active" value="yes"<?php echo ($wijc_active == 'yes' ? ' checked' : ''); ?>><span class="value"><strong><?php _e( 'Enable', 'wp-inline-js-converter' ); ?></strong></span>
                        <input type="radio" name="wijc_active" value="no"<?php echo ($wijc_active != 'yes' ? ' checked' : ''); ?>><span class="value"><?php _e( 'Disable', 'wp-inline-js-converter' ); ?></span>
                        <p class="description"><?php echo __( 'Enable or disable WP Fast Minify', 'wp-inline-js-converter' ); ?></p>
                    </td>
                </tr>
                <tr class="wijc_tofileJS wp_inline_js_converter_options">
                    <th><label><?php echo __( 'Convert inline JavaScript to File', 'wp-inline-js-converter' ); ?></label></th>
                    <td>
                        <input type="radio" name="wijc_tofileJS" value="yes"<?php echo ($wijc_tofileJS == 'yes' ? ' checked' : ''); ?>><span class="value"><strong><?php _e( 'Yes', 'wp-inline-js-converter' ); ?></strong></span>
                        <input type="radio" name="wijc_tofileJS" value="no"<?php echo ($wijc_tofileJS !='yes' ? ' checked' : ''); ?>><span class="value"><?php _e( 'No', 'wp-inline-js-converter' ); ?></span>
                        <p class="description"><?php echo __( 'This option is typically safe to set to "Yes"', 'wp-inline-js-converter' ); ?></p>
                    </td>
                </tr>
                <tr class="wijc_tofileCSS wp_inline_js_converter_options">
                    <th><label><?php echo __( 'Convert inline CSS to File', 'wp-inline-js-converter' ); ?></label></th>
                    <td>
                        <input type="radio" name="wijc_tofileCSS" value="yes"<?php echo ($wijc_tofileCSS == 'yes' ? ' checked' : ''); ?>><span class="value"><strong><?php _e( 'Yes', 'wp-inline-js-converter' ); ?></strong></span>
                        <input type="radio" name="wijc_tofileCSS" value="no"<?php echo ($wijc_tofileCSS !='yes' ? ' checked' : ''); ?>><span class="value"><?php _e( 'No', 'wp-inline-js-converter' ); ?></span>
                        <p class="description"><?php echo __( 'This option is typically safe to set to "Yes"', 'wp-inline-js-converter' ); ?></p>
                    </td>
                </tr>
                <tr class="wijc_compressJS wp_inline_js_converter_options">
                    <th><label><?php echo __( 'Compress inline JavaScript', 'wp-inline-js-converter' ); ?></label></th>
                    <td>
                        <input type="radio" name="wijc_compressJS" value="yes"<?php echo ($wijc_compressJS == 'yes' ? ' checked' : ''); ?>><span class="value"><strong><?php _e( 'Yes', 'wp-inline-js-converter' ); ?></strong></span>
                        <input type="radio" name="wijc_compressJS" value="no"<?php echo ($wijc_compressJS !='yes' ? ' checked' : ''); ?>><span class="value"><?php _e( 'No', 'wp-inline-js-converter' ); ?></span>
                        <p class="description"><?php echo __( 'This option is typically safe to set to "Yes"', 'wp-inline-js-converter' ); ?></p>
                    </td>
                </tr>
                <tr class="wijc_compressCSS wp_inline_js_converter_options">
                    <th><label><?php echo __( 'Compress inline CSS', 'wp-inline-js-converter' ); ?></label></th>
                    <td>
                        <input type="radio" name="wijc_compressCSS" value="yes"<?php echo ($wijc_compressCSS == 'yes' ? ' checked' : ''); ?>><span class="value"><strong><?php _e( 'Yes', 'wp-inline-js-converter' ); ?></strong></span>
                        <input type="radio" name="wijc_compressCSS" value="no"<?php echo ($wijc_compressCSS !='yes' ? ' checked' : ''); ?>><span class="value"><?php _e( 'No', 'wp-inline-js-converter' ); ?></span>
                        <p class="description"><?php echo __( 'This option is typically safe to set to "Yes"', 'wp-inline-js-converter' ); ?></p>
                    </td>
                </tr>
                <tr class="wijc_compressHTML wp_inline_js_converter_options">
                    <th><label><?php echo __( 'Compress HTML Code', 'wp-inline-js-converter' ); ?></label></th>
                    <td>
                        <input type="radio" name="wijc_compressHTML" value="yes"<?php echo ($wijc_compressHTML == 'yes' ? ' checked' : ''); ?>><span class="value"><strong><?php _e( 'Yes', 'wp-inline-js-converter' ); ?></strong></span>
                        <input type="radio" name="wijc_compressHTML" value="no"<?php echo ($wijc_compressHTML !='yes' ? ' checked' : ''); ?>><span class="value"><?php _e( 'No', 'wp-inline-js-converter' ); ?></span>
                        <p class="description"><?php echo __( 'This option is typically safe to set to "Yes"', 'wp-inline-js-converter' ); ?></p>
                    </td>
                </tr>
                </tr>
                    <td>&nbsp;</td>
                    <td>(<strong><?php echo __( 'Bold', 'wp-inline-js-converter' ); ?></strong> = <?php echo __( 'default value', 'wp-inline-js-converter' ); ?>)</td>
                </tr>
                </tr>
                    <td><?php echo __( 'Clear JavaScript and CSS Cache Files', 'wp-inline-js-converter' ); ?></td>
                    <td><p class="submit"><input type="submit" name="clear" class="button button-primary" value="<?php echo __( 'Clear', 'wp-inline-js-converter' ); ?>" /></p></td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="save" class="button button-primary" value="<?php echo __( 'Save Changes', 'wp-inline-js-converter' ); ?>" />
        </p>
    </form>
</div>
<script>
(function($) {
    $('#wijc .wp_inline_js_converter_options input, #wijc .wijc_active input').on('change', function() {
        if ($('input[name=wijc_active]:checked', '#wijc').val()=='no') {
            $('#wijc .wp_inline_js_converter_options').css('opacity','0.4');
            $('#wijc .wp_inline_js_converter_options input').prop( "disabled", true );
            return;
        } else {
            $('#wijc .wijc_tofileJS').css('opacity','1');
            $('#wijc .wijc_tofileJS input').prop( "disabled", false );
            $('#wijc .wijc_tofileCSS').css('opacity','1');
            $('#wijc .wijc_tofileCSS input').prop( "disabled", false );
            $('#wijc .wijc_compressHTML').css('opacity','1');
            $('#wijc .wijc_compressHTML input').prop( "disabled", false );
        }
        if ($('input[name=wijc_tofileJS]:checked', '#wijc').val()=='no') {
            $('#wijc .wijc_compressJS').css('opacity','0.4');
            $('#wijc .wijc_compressJS input').prop( "disabled", true );
        } else {
            $('#wijc .wijc_compressJS').css('opacity','1');
            $('#wijc .wijc_compressJS input').prop( "disabled", false );
        }
        if ($('input[name=wijc_tofileCSS]:checked', '#wijc').val()=='no') {
            $('#wijc .wijc_compressCSS').css('opacity','0.4');
            $('#wijc .wijc_compressCSS input').prop( "disabled", true );
        } else {
            $('#wijc .wijc_compressCSS').css('opacity','1');
            $('#wijc .wijc_compressCSS input').prop( "disabled", false );
        }
    });
    $('#wijc .wijc_active input').trigger('change');
})( jQuery );
</script>
<?php
}
