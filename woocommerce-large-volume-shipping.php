<?php
/*
** Plugin Name: WooCommerce Large Volume Parcel Shipping
** Plugin URI: https://wpcare.gr
** Description: A WooCommerce Plugin to enable shipping for large volume orders.
** Version: 2.0.0
** Author: Apostolis Mikas @ WordPress Care
** Author URI: https://wpcare.gr
** License: GNU General Public License v3.0
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
** Check if woocommerce is active.
*/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

function wplvs_init() {

global $woocommerce;
if ( ( is_checkout() || is_cart() ) && $woocommerce->cart->cart_contents_weight > (float)get_option( 'wplvs_shipping_weight' ) AND get_option( 'wplvs_shipping_enabled' ) == "yes" ) {

	if (is_cart()) {
		wc_print_notice( __( get_option( 'wplvs_shipping_message' ), 'woocommerce' ), 'success' );
	}

	add_action( 'woocommerce_before_checkout_form', 'wplvs_add_checkout_notice', 9 );
	function wplvs_add_checkout_notice() {
	    wc_print_notice( __( get_option( 'wplvs_shipping_message' ), 'woocommerce' ), 'success' );
	}

}

if ( ! class_exists( 'WC_Large_Volume_Shipping' ) ) {
class WC_Large_Volume_Shipping extends WC_Shipping_Method {

/*
** Constructor for Large Volume Shipping
*/
public function __construct() {
$this->id = 'wplvs_shipping'; // Id for your shipping method. Should be uunique.
$this->method_title = __( 'Large Volume' ); // Title shown in admin
$this->method_description = __( 'Allow users to use Large Volume for large volume products!' ); // Description shown in admin
$this->title = $this->get_option( 'title' );
$this->enabled = $this->get_option( 'enabled' );
$this->description = $this->get_option( 'description' );
$this->min_weight = $this->get_option( 'min_weight' );
update_option( 'wplvs_shipping_weight', $this->min_weight );
update_option( 'wplvs_shipping_enabled', $this->enabled );
update_option( 'wplvs_shipping_message', $this->description );

$this->init();
}

/*
** Initialize settings
*/
function init() {
$this->init_form_fields();
$this->init_settings();

// Save settings in admin if you have any defined
add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
}

	/*
	** init admin form fields
	*/
    public function init_form_fields() {

    	$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Large Volume Shipping', 'woocommerce' ),
				'default' => 'no'
			),
			'title' => array(
				'title'       => __( 'Title', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => __( 'Call us for Shipping Cost', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'min_weight' => array(
				'title'       => __( 'Minimum Weight', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'The minimum weight that products are categorized as large volume products.', 'woocommerce' ),
				'default'     => __( '10.00', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Message to Customer', 'woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'The message that customer will see at checkout when he selects large volume products.', 'woocommerce' ),
				'default'     => __( 'You added in card large volume products that can only be shipped with Metaforiki Shipping.', 'woocommerce' ),
				'desc_tip'    => true,
			),
		);
    }


/*
** Calculate Shipping cost.
*/
public function calculate_shipping( $package=array() ) {

global $woocommerce;
if ($woocommerce->cart->cart_contents_weight > (float)$this->min_weight) { $rate = array(
'id' => $this->id,
'label' => $this->title,
'cost' => '0',
'calc_tax' => 'per_order'
);

}

$this->add_rate( $rate );
}
}
}
}

add_action( 'woocommerce_shipping_init', 'wplvs_init' );

	function add_your_shipping_method( $methods ) {
		$methods[] = 'WC_Large_Volume_Shipping';
		return $methods;
	}

	add_filter( 'woocommerce_shipping_methods', 'add_your_shipping_method' );

	function remove_local_pickup_free_label($full_label, $method){
	    $full_label = preg_replace("/\([^)]+\)/","",$full_label);
	return $full_label;
	}
	add_filter( 'woocommerce_cart_shipping_method_full_label', 'remove_local_pickup_free_label', 10, 2 );
}
