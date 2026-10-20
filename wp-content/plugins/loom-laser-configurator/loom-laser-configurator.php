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