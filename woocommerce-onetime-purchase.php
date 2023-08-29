<?php

/*
 * Plugin Name:   	  Woocommerce Onetime Purchase
 * Plugin URI:        https://romanmiranda.com
 * Description:       Set products to allow onetime purchase.
 * Author: 			  Roman Miranda
 * Version:           0.0.1
 */

class OnetimePurchase{

	public function init()
	{
		add_filter( 'woocommerce_variation_is_purchasable', [$this, 'products_purchasable_once'], 10, 2 );
		add_filter( 'woocommerce_is_purchasable', [$this,'products_purchasable_once'], 10, 2 );
		
		add_action('woocommerce_product_options_general_product_data', [$this, 'add_custom_product_fields']);
		add_action('woocommerce_process_product_meta', [$this,'save_custom_product_fields']);
	}

	public function products_purchasable_once($purchasable, $product)
	{
	    // Check if is one time purchase
	    $one_time_purchase = get_post_meta($product->get_id(),'_one_time_purchase', true);

	    // Only for logged in users and not for variable products
	    if( ! is_user_logged_in() || $product->is_type('variable') )
	        return $purchasable; // Exit

	    $user = wp_get_current_user(); // The WP_User Object

	    if ( $one_time_purchase && wc_customer_bought_product( $user->user_email, $user->ID, $product->get_id() ) ) {
	        $purchasable = false;
	    }

	    return $purchasable;
	}

	public function add_custom_product_fields()
	{
	    global $product_object;
	    echo '<div class="product_custom_fields">';
	    // Checkbox
	    woocommerce_wp_checkbox(
	    	array(
	    		'id' => '_one_time_purchase',
	    		'label' => __('One Time Purchase', 'woocommerce-onetime-purchase'),
	    		'cbvalue' => 1,
	    		'desc_tip' => 'true',
	    		'description' => __('Customers can purchase this product only once.', 'woocommerce-onetime-purchase')
	    	)
	    );
	    echo '</div>';
	}

	public function save_custom_product_fields($product_id)
	{
	    // Save text field
	    $one_time_purchace = isset($_POST['_one_time_purchase']) ? sanitize_text_field($_POST['_one_time_purchase']) : '';
	    update_post_meta($product_id, '_one_time_purchase', $one_time_purchace);
	}
}

(new OnetimePurchase)->init();

