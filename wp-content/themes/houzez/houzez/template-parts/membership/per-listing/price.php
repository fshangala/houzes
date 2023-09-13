<?php
global $post, $houzez_local;
$post_id    = get_the_ID();
$user_data         = get_userdata( get_current_user_id() );
$role              = $user_data->roles[0];
$currency_symbol = houzez_option( 'currency_symbol' );
$where_currency = houzez_option( 'currency_position' );
$price_listing_submission = houzez_option('price_listing_submission');
$price_featured_submission = houzez_option('price_featured_listing_submission');
$price_per_submission = floatval($price_listing_submission);
$price_featured_submission = floatval($price_featured_submission);
$property_status = get_post_status ( $post->ID );
$property_status_terms = wp_get_post_terms($post->ID, 'property_status');
$property_status = houzez_taxonomy_simple('property_status');



// Set custom pricing for various submission types
$price_listing_seller_sale_submission = houzez_option('price_listing_seller_sale_submission');
$price_featured_listing_seller_sale_submission = houzez_option('price_featured_listing_seller_sale_submission');
$price_listing_agent_sale_submission = houzez_option('price_listing_agent_sale_submission');
$price_featured_listing_agent_sale_submission = houzez_option('price_featured_listing_agent_sale_submission');
$price_listing_seller_rent_submission = houzez_option('price_listing_seller_rent_submission');
$price_featured_listing_seller_rent_submission = houzez_option('price_featured_listing_seller_rent_submission');
$price_listing_agent_rent_submission = houzez_option('price_listing_agent_rent_submission');
$price_featured_listing_agent_rent_submission = houzez_option('price_featured_listing_agent_rent_submission');

// Convert custom pricing values to float
$price_listing_seller_sale_submission = floatval($price_listing_seller_sale_submission);
$price_featured_listing_seller_sale_submission = floatval($price_featured_listing_seller_sale_submission);
$price_listing_agent_sale_submission = floatval($price_listing_agent_sale_submission);
$price_featured_listing_agent_sale_submission = floatval($price_featured_listing_agent_sale_submission);
$price_listing_seller_rent_submission = floatval($price_listing_seller_rent_submission);
$price_featured_listing_seller_rent_submission = floatval($price_featured_listing_seller_rent_submission);
$price_listing_agent_rent_submission = floatval($price_listing_agent_rent_submission);
$price_featured_listing_agent_rent_submission = floatval($price_featured_listing_agent_rent_submission);

$upgrade_id = isset( $_GET['upgrade_id'] ) ? $_GET['upgrade_id'] : '';
$prop_id = isset( $_GET['prop-id'] ) ? $_GET['prop-id'] : '';




		
		

// Determine property status
$property_status = '';

if ($prop_id > 0) {
    $property_status_terms = wp_get_post_terms($prop_id, 'property_status');

    if (!empty($property_status_terms)) {
        foreach ($property_status_terms as $term) {
            if ($term->slug == 'for-sale') {
                $property_status = 'for-sale';
                break; // Exit the loop when the status is found
            } elseif ($term->slug == 'for-rent') {
                $property_status = 'for-rent';
                break; // Exit the loop when the status is found
            }
        }
    }
}

// Output property status
if (empty($property_status)) {
    echo 'No property status assigned.'; // Output if no property status is assigned or found
} else {
    echo 'Property Status: ' . $property_status; // Output the determined property status
}
		
	
if ($role === 'houzez_agent') {
    echo ' Agent';
} elseif ($role === 'houzez_seller') {
    echo ' User';
} else {
    echo ' Unknown Role'; // You can customize this message for other roles
}

// Determine featured
$prop_featured = get_post_meta($prop_id, 'fave_featured', true);
if($prop_featured == "1") {
    echo ', Property Featured: True';
} else {
    echo ', Property Featured: False';
}

?>		
		
<div class="membership-package-order-detail-wrap">
    <div class="dashboard-content-block">
		
<h3><?php esc_html_e('Pay Listing', 'houzez'); ?></h3>
<div class="membership-package-order-detail">
    <ul class="list-unstyled mebership-list-info">
        <?php 
        global $submission_price;
        $submission_price = 0; // Initialize the submission price to 0
        
            //$prop_featured = get_post_meta($upgrade_id, 'fave_featured', true);
            
            if ($prop_featured == 1) {
                // Featured, calculate Featured Fee
                if ($role === 'houzez_seller') {
                    if ($property_status === 'for-sale') {
                        $submission_price = $price_featured_listing_seller_sale_submission;
                    } elseif ($property_status === 'for-rent') {
                        $submission_price = $price_featured_listing_seller_rent_submission;
                    }
                } elseif ($role === 'houzez_agent') {
                    if ($property_status === 'for-sale') {
                        $submission_price = $price_featured_listing_agent_sale_submission;
                    } elseif ($property_status === 'for-rent') {
                        $submission_price = $price_featured_listing_agent_rent_submission;
                    }
                }
            } else  {
                // Not featured, calculate Submission Fee
                if ($role === 'houzez_seller') {
                    if ($property_status === 'for-sale') {
                        $submission_price = $price_listing_seller_sale_submission;
                    } elseif ($property_status === 'for-rent') {
                        $submission_price = $price_listing_seller_rent_submission;
                    }
                } elseif ($role === 'houzez_agent') {
                    if ($property_status === 'for-sale') {
                        $submission_price = $price_listing_agent_sale_submission;
                    } elseif ($property_status === 'for-rent') {
                        $submission_price = $price_listing_agent_rent_submission;
                    }
                }
            
        }
        //setcookie("submission_price", $submission_price, time() + (86400 * 30), "/");
        update_user_meta( $user_data->ID, "submission_price", $submission_price );
        ?>

        <li>
            <i class="houzez-icon icon-check-circle-1 mr-2 primary-text"></i>
            <?php esc_html_e('Submission Fee', 'houzez'); ?>
            <strong><?php echo esc_attr($submission_price); ?></strong>
            <span id="submission_price" class="hidden"><?php echo esc_attr($submission_price); ?></span>
        </li>

        <li class="total-price"><?php esc_html_e('Total Price', 'houzez'); ?>
            <strong><?php echo esc_attr($submission_price); ?></strong>
            <span id="total_price" class="hidden"><?php echo esc_attr($submission_price); ?></span>
        </li>
    </ul>
</div><!-- membership-package-order-detail -->




    </div>
</div><!-- membership-package-order-detail-wrap -->