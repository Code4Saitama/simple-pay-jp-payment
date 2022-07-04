<?php
/*
Plugin Name: Social Project Donation with PAY.JP
Plugin URI: https://github.com/Code4Saitama/simple-pay-jp-payment
Description: Add payment by PAY.JP
Version: 1.2.0
Author: NPO-toiro-commune-designing
Author URI: https://github.com/NPO-toiro-commune-designing
Text Domain: social-project-donation-with-payjp
Domain Path: /languages
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

{Plugin Name} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
{Plugin Name} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {License URI}.
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once 'vendor/payjp/payjp-php/init.php';

function simplepayjppayment_get_secret_key() {
    $live_enabled = get_option( 'simplepayjppayment-live-enabled' );
    $secret_key = get_option( 'simplepayjppayment-test-secret-key', "" );
    if ( $live_enabled == 1 ) {
        $secret_key = get_option( 'simplepayjppayment-live-secret-key', "" );
    }
    if ( $secret_key != "" ) {
        $secret_key = simplepayjppayment_decrypt( $secret_key );
    }
    return $secret_key;
}

function simplepayjppayment_get_public_key() {
    $live_enabled = get_option( 'simplepayjppayment-live-enabled' );
    $public_key = get_option( 'simplepayjppayment-test-public-key', "" );
    if ( $live_enabled == 1 ) {
        $public_key = get_option( 'simplepayjppayment-live-public-key', "" );
    }
    if ( $public_key != "" ) {
        $public_key = simplepayjppayment_decrypt( $public_key );
    }
    return $public_key;
}

function simplepayjppayment_create_customer( $secret_key, $token, $mail, $description ) {
    try {
        Payjp\Payjp::setApiKey($secret_key);
        $result = Payjp\Customer::create( array(
                "email" => $mail,
                "card" => $token,
                "description" => $description,
        ));
        if (isset($result['error'])) {
            throw new Exception();
        }
    } catch (Exception $e) {
        return '';
    }
    return $result['id'];
}

function simplepayjppayment_create_subscription( $secret_key, $customer_id, $plan_id, $prorate ) {
    try {
        Payjp\Payjp::setApiKey($secret_key);
        Payjp\Subscription::create( array(
                "customer" => $customer_id,
                "plan" => $plan_id,
                "prorate" => $prorate,
        ));
        if (isset($result['error'])) {
            throw new Exception();
        }
    } catch (Exception $e) {
        return false;
    }
    return true;
}

function simplepayjppayment_create_single_payment( $secret_key, $token, $amount, $currency, $desc ) {
    return simplepayjppayment_create_payment( $secret_key, $token, $amount, $currency, $desc );
}

function simplepayjppayment_create_subscription_payment( $secret_key, $token, $plan_id, $mail, $desc, $prorate ) {
    $customer_id = simplepayjppayment_create_customer( $secret_key, $token, $mail, $desc );
    if ( $customer_id === '' ) {
        return false;
    }
    return simplepayjppayment_create_subscription( $secret_key, $customer_id, $plan_id, $prorate );
}

// option
$simplepayjppayment_option_default = new SimplePayjpPayment_Option_Default();
class SimplePayjpPayment_Option_Default {
    public $test_public_key = '';
    public $test_secret_key = '';
    public $live_public_key = '';
    public $live_secret_key = '';
    public $live_enabled = 0;
}

// css
function simplepayjppayment_register_my_styles() {
	wp_enqueue_style(
        'simplepayjppayment',
        plugins_url( 'css/simple-payjp-payment.css', __FILE__ )
    );
}
add_action( 'wp_enqueue_scripts', 'simplepayjppayment_register_my_styles' );

// admin menu
function simplepayjppayment_admin_menu() {
    add_options_page(
        __('Simple PAY.JP Payment', 'simple-payjp-payment'),
        __('Simple PAY.JP Payment', 'simple-payjp-payment'),
        'administrator',
        'simplepayjppayment_show_admin_panel',
        'simplepayjppayment_show_admin_panel'
    );
}
add_action( 'admin_menu', 'simplepayjppayment_admin_menu');

function simplepayjppayment_show_admin_panel() {
    global $simplepayjppayment_option_default;

    $simplepayjppayment_option_default->test_public_key = get_option( 'simplepayjppayment-test-public-key', "" );
    $simplepayjppayment_option_default->test_secret_key = get_option( 'simplepayjppayment-test-secret-key', "" );
    $simplepayjppayment_option_default->live_public_key = get_option( 'simplepayjppayment-live-public-key', "" );
    $simplepayjppayment_option_default->live_secret_key = get_option( 'simplepayjppayment-live-secret-key', "" );

    if ( $simplepayjppayment_option_default->test_public_key != "" ) {
        $simplepayjppayment_option_default->test_public_key = simplepayjppayment_decrypt( $simplepayjppayment_option_default->test_public_key );
    }
    if ( $simplepayjppayment_option_default->test_secret_key != "" ) {
        $simplepayjppayment_option_default->test_secret_key = simplepayjppayment_decrypt( $simplepayjppayment_option_default->test_secret_key );
    }
    if ( $simplepayjppayment_option_default->live_public_key != "" ) {
        $simplepayjppayment_option_default->live_public_key = simplepayjppayment_decrypt( $simplepayjppayment_option_default->live_public_key );
    }
    if ( $simplepayjppayment_option_default->live_secret_key != "" ) {
        $simplepayjppayment_option_default->live_secret_key = simplepayjppayment_decrypt( $simplepayjppayment_option_default->live_secret_key );
    }

    $simplepayjppayment_option_default->live_enabled = get_option( 'simplepayjppayment-live-enabled', 0 );
?>
<div class="warp">
    <h2>Simple PAY.JP Payment</h2>
    <form id="simplepayjppayment-form" method="post" action="">
        <?php wp_nonce_field( 'my-nonce-key', 'simplepayjppayment_admin_menu' ); ?>

        <table class="form-table">
        <tbody>
        <tr>
            <th scope="row"><label for="test-secret-key"><?php esc_html_e( 'Test Secret Key', 'simple-payjp-payment' ); ?> </label>
            </th>
            <td>
                <input type="text" name="test-secret-key" class="regular-text" value="<?php echo esc_attr( $simplepayjppayment_option_default->test_secret_key ) ; ?>">
            </td>
        </tr>

        <tr>
            <th scope="row"><label for="test-public-key"><?php esc_html_e( 'Test Public Key', 'simple-payjp-payment' ); ?> </label>
            </th>
            <td>
                <input type="text" name="test-public-key" class="regular-text" value="<?php echo esc_attr( $simplepayjppayment_option_default->test_public_key ) ; ?>">
            </td>
        </tr>

        <tr>
            <th scope="row"><label for="live-secret-key"><?php esc_html_e( 'Live Secret Key', 'simple-payjp-payment' ); ?> </label>
            </th>
            <td>
                <input type="text" name="live-secret-key" class="regular-text" value="<?php echo esc_attr( $simplepayjppayment_option_default->live_secret_key ) ; ?>">
            </td>
        </tr>

        <tr>
            <th scope="row"><label for="live-public-key"><?php esc_html_e( 'Live Public Key', 'simple-payjp-payment' ); ?> </label>
            </th>
            <td>
                <input type="text" name="live-public-key" class="regular-text" value="<?php echo esc_attr( $simplepayjppayment_option_default->live_public_key ) ; ?>">
            </td>
        </tr>

        <tr>
            <th scope="row"><label for="live-enabled"><?php esc_html_e( 'Enable Live', 'simple-payjp-payment' ); ?>
            </label>
            </th>
            <td>
                <input type="checkbox" name="live-enabled" class="checkbox" value="1" 
                <?php if ( $simplepayjppayment_option_default->live_enabled == 1 ) {
                ?> checked="checked"><?php } ?>
            </td>
        </tr>
        </tbody>
        </table>

        <p><input type="submit"
        value="<?php echo esc_attr( __( 'Save', 'simple-payjp-payment' ) ); ?>"
        class="button button-primary button-large">
        </p>
    </form>
</div>
<?php
}

function simplepayjppayment_admin_init() {
    global $SimplePayjpPayment_option_default;

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( ! isset( $_POST['simplepayjppayment_admin_menu'] ) || ! $_POST['simplepayjppayment_admin_menu']) {
        simplepayjppayment_security_migration();
        return;
    }

    if ( ! check_admin_referer( 'my-nonce-key', 'simplepayjppayment_admin_menu' ) ) {
        return;
    }

    $safe_test_public_key = '';
    if ( isset( $_POST['test-public-key'] ) ) {
        $test_public_key = (string) filter_input( INPUT_POST, 'test-public-key' );
        $safe_test_public_key = sanitize_text_field( $test_public_key );
        if ( simplepayjppayment_has_public_key_prefix( $safe_test_public_key ) ) {
            $safe_test_public_key = simplepayjppayment_encrypt( $safe_test_public_key );
        } else {
            $safe_test_public_key = '';
        }
        update_option( 'simplepayjppayment-test-public-key', $safe_test_public_key );
    }

    $safe_test_secret_key = '';
    if ( isset( $_POST['test-secret-key'] ) ) {
        $test_secret_key = (string) filter_input( INPUT_POST, 'test-secret-key' );
        $safe_test_secret_key = sanitize_text_field( $test_secret_key );
        if ( simplepayjppayment_has_secret_key_prefix( $safe_test_secret_key ) ) {
            $safe_test_secret_key = simplepayjppayment_encrypt( $safe_test_secret_key );
        } else {
            $safe_test_secret_key = '';
        }
        update_option( 'simplepayjppayment-test-secret-key', $safe_test_secret_key );
    }

    $safe_live_public_key = '';
    if ( isset( $_POST['live-public-key'] ) ) {
        $live_public_key = (string) filter_input( INPUT_POST, 'live-public-key' );
        $safe_live_public_key = sanitize_text_field( $live_public_key );
        if ( simplepayjppayment_has_public_key_prefix( $safe_live_public_key ) ) {
            $safe_live_public_key = simplepayjppayment_encrypt( $safe_live_public_key );
        } else {
            $safe_live_public_key = '';
        }
        update_option( 'simplepayjppayment-live-public-key', $safe_live_public_key );
    }

    $safe_live_secret_key = '';
    if ( isset( $_POST['live-public-key'] ) ) {
        $live_secret_key = (string) filter_input( INPUT_POST, 'live-secret-key' );
        $safe_live_secret_key = sanitize_text_field( $live_secret_key );
        if ( simplepayjppayment_has_secret_key_prefix( $safe_live_secret_key ) ) {
            $safe_live_secret_key = simplepayjppayment_encrypt( $safe_live_secret_key );
        } else {
            $safe_live_secret_key = '';
        }
        update_option( 'simplepayjppayment-live-secret-key', $safe_live_secret_key );
    }

    $safe_live_enabled = 0;
    if ( isset( $_POST['live-enabled'] ) ) {
        $safe_live_enabled = intval( $_POST['live-enabled'] );
    }
    update_option( 'simplepayjppayment-live-enabled', $safe_live_enabled );

    wp_safe_redirect( menu_page_url( 'simplepayjppayment_admin_menu', false ) );
}
add_action( 'admin_init', 'simplepayjppayment_admin_init' );

// admin css
function simplepayjppayment_register_my_admin_styles( $hook ) {
    if ( 'settings_page_simplepayjppayment_show_admin_panel' != $hook ) {
        return;
    }
	wp_enqueue_style(
        'style-name-admin',
        plugins_url( 'css/simple-payjp-payment-admin.css', __FILE__ )
    );
}
add_action( 'admin_enqueue_scripts', 'simplepayjppayment_register_my_admin_styles' );

// i18n
function simplepayjppayment_load_textdomain() {
    load_plugin_textdomain( 'simple-payjp-payment', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'simplepayjppayment_load_textdomain' );


// security update 0.1.7
function simplepayjppayment_security_migration() {
    $test_public_key = get_option( 'simplepayjppayment-test-public-key' );
    $test_secret_key = get_option( 'simplepayjppayment-test-secret-key' );
    $live_public_key = get_option( 'simplepayjppayment-live-public-key' );
    $live_secret_key = get_option( 'simplepayjppayment-live-secret-key' );

    if ( $test_public_key ) {
        $test_public_key_decoded = simplepayjppayment_decrypt( $test_public_key );
        if ( ! simplepayjppayment_has_public_key_prefix( $test_public_key_decoded ) ) {
            if ( simplepayjppayment_has_public_key_prefix ( $test_public_key ) ) {
                update_option( 'simplepayjppayment-test-public-key', simplepayjppayment_encrypt( $test_public_key ) );
            } else {
                update_option( 'simplepayjppayment-test-public-key', "" );
            }
        }
    }
    if ( $test_secret_key ) {
        $test_secret_key_decoded = simplepayjppayment_decrypt( $test_secret_key );
        if ( ! simplepayjppayment_has_secret_key_prefix( $test_secret_key_decoded ) ) {
            if ( simplepayjppayment_has_secret_key_prefix ( $test_secret_key ) ) {
                update_option( 'simplepayjppayment-test-secret-key', simplepayjppayment_encrypt( $test_secret_key ) );
            } else {
                update_option( 'simplepayjppayment-test-secret-key', "" );
            }
        }
    }
    if ( $live_public_key ) {
        $live_public_key_decoded = simplepayjppayment_decrypt( $live_public_key );
        if ( ! simplepayjppayment_has_public_key_prefix( $live_public_key_decoded ) ) {
            if ( simplepayjppayment_has_public_key_prefix ( $live_public_key ) ) {
                update_option( 'simplepayjppayment-live-public-key', simplepayjppayment_encrypt( $live_public_key ) );
            } else {
                update_option( 'simplepayjppayment-live-public-key', "" );
            }
        }
    }
    if ( $live_secret_key ) {
        $live_secret_key_decoded = simplepayjppayment_decrypt( $live_secret_key );
        if ( ! simplepayjppayment_has_secret_key_prefix( $live_secret_key_decoded ) ) {
            if ( simplepayjppayment_has_public_key_prefix ( $live_secret_key ) ) {
                update_option( 'simplepayjppayment-live-secret-key', simplepayjppayment_encrypt( $live_secret_key ) );
            } else {
                update_option( 'simplepayjppayment-live-secret-key', "" );
            }
        }
    }
}

function simplepayjppayment_has_public_key_prefix( $str ) {
    $public_key_pattern = '/^pk_.*/';
    return preg_match( $public_key_pattern, $str );
}
function simplepayjppayment_has_secret_key_prefix( $str ) {
    $secret_key_pattern = '/^sk_.*/';
    return preg_match( $secret_key_pattern, $str );
}

// encrypt/decrypt
function simplepayjppayment_get_encrypt_key() {
    $key = get_option( 'simplepayjppayment-encrypt-key' );
    if ( ! $key ) {
        $ivlen = openssl_cipher_iv_length( $cipher="AES-128-CBC" );
        $key = openssl_random_pseudo_bytes( $ivlen );
        $key = base64_encode( $key );
        $retval = update_option( 'simplepayjppayment-encrypt-key', $key );
    }
    return base64_decode( $key );
}

function simplepayjppayment_encrypt( $plaintext ) {
    $cipher = "aes-128-gcm";
    $key = simplepayjppayment_get_encrypt_key();
    $ivlen = openssl_cipher_iv_length( $cipher="AES-128-CBC" );
    $iv = openssl_random_pseudo_bytes( $ivlen );
    $ciphertext_raw = openssl_encrypt( $plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv );
    $hmac = hash_hmac( 'sha256', $ciphertext_raw, $key, $as_binary=true );
    $ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );
    return $ciphertext;
}

function simplepayjppayment_decrypt( $ciphertext ) {
    $cipher = "aes-128-gcm";
    $key = simplepayjppayment_get_encrypt_key();
    $c = base64_decode( $ciphertext );
    $ivlen = openssl_cipher_iv_length( $cipher="AES-128-CBC" );
    $iv = substr( $c, 0, $ivlen );
    $sha2len = 32;
    $hmac = substr( $c, $ivlen, $sha2len );
    $ciphertext_raw = substr( $c, $ivlen+$sha2len );
    $original_plaintext = openssl_decrypt( $ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv );
    $calcmac = hash_hmac( 'sha256', $ciphertext_raw, $key, $as_binary=true );
    //PHP 5.6+ timing attack safe comparison
    if ( hash_equals( $hmac, $calcmac ) ) {
        return $original_plaintext;
    }
    return "";
}

?>
