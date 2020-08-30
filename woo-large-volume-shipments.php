<?php
/*
** Plugin Name: WooCommerce Large Volume Shipments
** Plugin URI: https://wpcare.gr
** Description: Adds a "call for shipping costs" option when an order exceeds a certain weight limit. Useful for large volume orders where shipping costs are not calculated automatically.
** Version: 2.0.2
** Author: WPCARE
** Author URI: https://wpcare.gr
** License: Gpl2 or later
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
** Check if woocommerce is active.
*/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

function wplvs_init() {

global $woocommerce;
if ( ( is_checkout() || is_cart() ) && $woocommerce->cart->cart_contents_weight > (float)get_option( 'wplvs_shipping_weight' ) AND get_option( 'wplvs_shipping_enabled' ) == "yes" ) {

	add_action( 'woocommerce_before_cart', 'wplvs_add_cart_notice', 9 );
	function wplvs_add_cart_notice() {
		if (is_cart()) {
			wc_print_notice( __( get_option( 'wplvs_shipping_message' ), 'woocommerce' ), 'success' );
		}
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
				'default'     => __( 'Call us for Shipping Costs', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'min_weight' => array(
				'title'       => __( 'Minimum Weight', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'The minimum order weight that order is categorized as large volume shipment.', 'woocommerce' ),
				'default'     => __( '10.00', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Message to Customer', 'woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'The message that customer will see at checkout when he selects large volume products.', 'woocommerce' ),
				'default'     => __( 'Your order weighs more than the allowable limit and shipping costs can not be calculated automatically, after placing the order call at (+31) 123456789 and we\'ll inform you about the shipping costs.', 'woocommerce' ),
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
