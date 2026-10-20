<?php
/**
 * Plugin Name: Loom & Laser Product Configurator
 * Description: Custom live pricing and variation logic for WooCommerce products.
 * Version: 1.0.0
 * Author: Loom & Laser Co
 */

// Exit if accessed directly for security
if (!defined('ABSPATH')) {
    exit; 
}

/**
 * 1. Inject Custom Configurator UI on the Product Page
 * PRD Requirement: Custom text input field for engraving
 */
add_action( 'woocommerce_before_add_to_cart_button', 'llc_custom_engraving_field' );

function llc_custom_engraving_field() {
    global $product;
    
    // Only show on variable products
    if ( ! $product->is_type( 'variable' ) ) {
        return;
    }

    echo '<div class="llc-configurator-wrapper" style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">';
    echo '<h4>Personalize Your Leather</h4>';
    
    // Custom Engraving Text Input
    echo '<div class="llc-engraving-group">';
    echo '<label for="llc_engraving_text">Custom Engraving (+ $15.00)</label><br>';
    echo '<input type="text" id="llc_engraving_text" name="llc_engraving_text" placeholder="e.g. Initials or short quote" maxlength="20" style="width: 100%; padding: 10px; margin-top: 5px;">';
    echo '<small>Maximum 20 characters.</small>';
    echo '</div>';

    // Live Price Display Area (Targeted by JS)
    echo '<div class="llc-live-price-wrapper" style="margin-top: 15px; font-size: 1.2em; font-weight: bold;">';
    echo 'Total Estimate: <span id="llc_live_price_display">Select options above</span>';
    echo '</div>';
    
    echo '</div>';
}

/**
 * 2. Live Pricing JavaScript
 * PRD Requirement: Live price recalculation as options change
 */
add_action( 'wp_footer', 'llc_live_pricing_script' );

function llc_live_pricing_script() {
    // Only load this script on individual product pages
    if ( ! is_product() ) {
        return;
    }
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        let basePrice = 0;
        const engravingFee = 15.00;
        const displayArea = $('#llc_live_price_display');
        const textInput = $('#llc_engraving_text');
        
        // 1. Listen for WooCommerce variation selection (Color picked)
        $('form.variations_form').on('found_variation', function(event, variation) {
            basePrice = variation.display_price; // Get the exact price of the chosen color
            updateLivePrice();
        });

        // 2. Listen for variation reset (Color cleared)
        $('form.variations_form').on('hide_variation', function() {
            basePrice = 0;
            displayArea.text('Select options above');
        });

        // 3. Listen for user typing in the custom text field
        textInput.on('input', function() {
            updateLivePrice();
        });

        // 4. Calculate and render the final price
        function updateLivePrice() {
            if (basePrice === 0) return; // Prevent calculation if no color is picked

            let finalPrice = basePrice;
            let hasEngravingText = textInput.val().trim().length > 0;

            // Add the $15 fee if they have typed anything
            if (hasEngravingText) {
                finalPrice += engravingFee;
            }

            // Update the UI with the properly formatted price
            displayArea.text('$' + finalPrice.toFixed(2));
        }
    });
    </script>
    <?php
}

/**
 * 3. Save Custom Data to Cart Session
 * Captures the POST data when 'Add to Cart' is clicked.
 */
add_filter( 'woocommerce_add_cart_item_data', 'llc_add_engraving_to_cart', 10, 2 );

function llc_add_engraving_to_cart( $cart_item_data, $product_id ) {
    if ( isset( $_POST['llc_engraving_text'] ) && ! empty( sanitize_text_field( $_POST['llc_engraving_text'] ) ) ) {
        $cart_item_data['llc_engraving'] = sanitize_text_field( $_POST['llc_engraving_text'] );
    }
    return $cart_item_data;
}

/**
 * 4. Display Custom Data in the Cart and Checkout tables
 * Shows "Custom Engraving: [User Text]" under the product name.
 */
add_filter( 'woocommerce_get_item_data', 'llc_display_engraving_in_cart', 10, 2 );

function llc_display_engraving_in_cart( $item_data, $cart_item ) {
    if ( array_key_exists( 'llc_engraving', $cart_item ) ) {
        $item_data[] = array(
            'key'   => 'Custom Engraving',
            'value' => $cart_item['llc_engraving']
        );
    }
    return $item_data;
}

/**
 * 5. Update the Actual Cart Price
 * Adds the $15 fee to the WooCommerce cart object if engraving exists.
 */
add_action( 'woocommerce_before_calculate_totals', 'llc_add_engraving_fee', 10, 1 );

function llc_add_engraving_fee( $cart_obj ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }
    
    foreach ( $cart_obj->get_cart() as $cart_item_key => $cart_item ) {
        if ( isset( $cart_item['llc_engraving'] ) ) {
            // Get the base price of the chosen variation (e.g., $45 or $50)
            $base_price = $cart_item['data']->get_regular_price(); 
            
            // Add the $15 engraving fee
            $new_price = $base_price + 15.00;
            
            // Set the new calculated price in the cart
            $cart_item['data']->set_price( $new_price );
        }
    }
}