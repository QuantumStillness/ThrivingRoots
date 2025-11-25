<?php
/**
 * WooCommerce Integration
 *
 * Integrates environmental data with WooCommerce for:
 * - Product associations with superfund sites
 * - Data-as-a-Service (DaaS) offerings
 * - Fundraising and service sales
 *
 * @package EnvironmentalIntelligenceCore
 * @subpackage WooCommerce
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * EIC_WooCommerce Class
 */
class EIC_WooCommerce {

    /**
     * Initialize WooCommerce integration
     */
    public static function init() {
        // Add meta box to products
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_product_meta_boxes' ) );
        add_action( 'save_post', array( __CLASS__, 'save_product_meta_boxes' ) );
        
        // Add custom product fields
        add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'add_product_fields' ) );
        add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_product_fields' ) );
        
        // Display site info on product page
        add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'display_site_info' ), 25 );
        
        // Add site column to products admin
        add_filter( 'manage_product_posts_columns', array( __CLASS__, 'add_product_columns' ) );
        add_action( 'manage_product_posts_custom_column', array( __CLASS__, 'render_product_columns' ), 10, 2 );
    }

    /**
     * Add meta boxes to product edit screen
     */
    public static function add_product_meta_boxes() {
        add_meta_box(
            'eic_product_site_association',
            __( 'Environmental Site Association', 'env-intel-core' ),
            array( __CLASS__, 'render_product_site_meta_box' ),
            'product',
            'side',
            'default'
        );
    }

    /**
     * Render product site association meta box
     */
    public static function render_product_site_meta_box( $post ) {
        wp_nonce_field( 'eic_product_site_nonce', 'eic_product_site_nonce' );
        
        $associated_site = get_post_meta( $post->ID, '_eic_associated_site', true );
        
        $sites = get_posts( array(
            'post_type' => 'superfund_site',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ) );
        ?>
        <p>
            <label for="eic_product_associated_site"><?php _e( 'Link to Superfund Site', 'env-intel-core' ); ?></label><br>
            <select id="eic_product_associated_site" name="eic_product_associated_site" class="widefat">
                <option value=""><?php _e( 'None', 'env-intel-core' ); ?></option>
                <?php foreach ( $sites as $site ) : ?>
                    <option value="<?php echo esc_attr( $site->ID ); ?>" <?php selected( $associated_site, $site->ID ); ?>>
                        <?php echo esc_html( $site->post_title ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p class="description">
            <?php _e( 'Associate this product with a specific environmental site. Useful for site-specific testing kits, cleanup fundraising, or consulting services.', 'env-intel-core' ); ?>
        </p>
        <?php
    }

    /**
     * Save product meta boxes
     */
    public static function save_product_meta_boxes( $post_id ) {
        
        // Check nonce
        if ( ! isset( $_POST['eic_product_site_nonce'] ) || ! wp_verify_nonce( $_POST['eic_product_site_nonce'], 'eic_product_site_nonce' ) ) {
            return;
        }

        // Check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save associated site
        if ( isset( $_POST['eic_product_associated_site'] ) ) {
            update_post_meta( $post_id, '_eic_associated_site', sanitize_text_field( $_POST['eic_product_associated_site'] ) );
        }
    }

    /**
     * Add custom product fields
     */
    public static function add_product_fields() {
        global $post;
        
        $is_daas = get_post_meta( $post->ID, '_eic_is_daas_product', true );
        $data_scope = get_post_meta( $post->ID, '_eic_daas_scope', true );
        
        echo '<div class="options_group">';
        
        woocommerce_wp_checkbox( array(
            'id' => '_eic_is_daas_product',
            'label' => __( 'Data-as-a-Service Product', 'env-intel-core' ),
            'description' => __( 'This product provides environmental data access', 'env-intel-core' ),
            'value' => $is_daas ? 'yes' : 'no',
        ) );
        
        woocommerce_wp_select( array(
            'id' => '_eic_daas_scope',
            'label' => __( 'Data Scope', 'env-intel-core' ),
            'options' => array(
                '' => __( 'Select scope', 'env-intel-core' ),
                'single_site' => __( 'Single Site Report', 'env-intel-core' ),
                'regional' => __( 'Regional Analysis', 'env-intel-core' ),
                'statewide' => __( 'Statewide Dataset', 'env-intel-core' ),
                'custom' => __( 'Custom Data Request', 'env-intel-core' ),
            ),
            'value' => $data_scope,
        ) );
        
        echo '</div>';
    }

    /**
     * Save custom product fields
     */
    public static function save_product_fields( $post_id ) {
        
        $is_daas = isset( $_POST['_eic_is_daas_product'] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_eic_is_daas_product', $is_daas );
        
        if ( isset( $_POST['_eic_daas_scope'] ) ) {
            update_post_meta( $post_id, '_eic_daas_scope', sanitize_text_field( $_POST['_eic_daas_scope'] ) );
        }
    }

    /**
     * Display site information on product page
     */
    public static function display_site_info() {
        global $post;
        
        $associated_site_id = get_post_meta( $post->ID, '_eic_associated_site', true );
        
        if ( ! $associated_site_id ) {
            return;
        }
        
        $site = get_post( $associated_site_id );
        
        if ( ! $site || $site->post_type !== 'superfund_site' ) {
            return;
        }
        
        $epa_id = get_post_meta( $associated_site_id, '_eic_epa_id', true );
        $status = get_post_meta( $associated_site_id, '_eic_site_status', true );
        $contaminants = wp_get_post_terms( $associated_site_id, 'contaminant', array( 'fields' => 'names' ) );
        
        ?>
        <div class="eic-product-site-info" style="background: #f7f7f7; padding: 15px; margin: 20px 0; border-left: 4px solid #2271b1;">
            <h3><?php _e( 'Associated Environmental Site', 'env-intel-core' ); ?></h3>
            <p><strong><?php echo esc_html( $site->post_title ); ?></strong></p>
            <?php if ( $epa_id ) : ?>
                <p><small><?php _e( 'EPA ID:', 'env-intel-core' ); ?> <?php echo esc_html( $epa_id ); ?></small></p>
            <?php endif; ?>
            <?php if ( $status ) : ?>
                <p><small><?php _e( 'Status:', 'env-intel-core' ); ?> <?php echo esc_html( ucfirst( $status ) ); ?></small></p>
            <?php endif; ?>
            <?php if ( ! empty( $contaminants ) && ! is_wp_error( $contaminants ) ) : ?>
                <p><small><?php _e( 'Contaminants:', 'env-intel-core' ); ?> <?php echo esc_html( implode( ', ', $contaminants ) ); ?></small></p>
            <?php endif; ?>
            <p><a href="<?php echo get_permalink( $associated_site_id ); ?>"><?php _e( 'View Full Site Details', 'env-intel-core' ); ?> &rarr;</a></p>
        </div>
        <?php
    }

    /**
     * Add custom columns to products admin
     */
    public static function add_product_columns( $columns ) {
        $new_columns = array();
        
        foreach ( $columns as $key => $value ) {
            $new_columns[ $key ] = $value;
            
            if ( $key === 'name' ) {
                $new_columns['eic_site'] = __( 'Environmental Site', 'env-intel-core' );
            }
        }
        
        return $new_columns;
    }

    /**
     * Render custom columns in products admin
     */
    public static function render_product_columns( $column, $post_id ) {
        
        if ( $column === 'eic_site' ) {
            $associated_site_id = get_post_meta( $post_id, '_eic_associated_site', true );
            
            if ( $associated_site_id ) {
                $site = get_post( $associated_site_id );
                if ( $site ) {
                    echo '<a href="' . get_edit_post_link( $associated_site_id ) . '">' . esc_html( $site->post_title ) . '</a>';
                }
            } else {
                echo '—';
            }
        }
    }

    /**
     * Programmatically create a WooCommerce product linked to a superfund site
     *
     * Example usage:
     * $product_id = EIC_WooCommerce::create_site_product( 123, array(
     *     'name' => 'Soil Testing Kit - Site Name',
     *     'price' => '49.99',
     *     'description' => 'Professional soil testing kit for this site',
     *     'type' => 'simple'
     * ) );
     *
     * @param int $site_id Superfund site post ID
     * @param array $args Product arguments
     * @return int|WP_Error Product ID on success, WP_Error on failure
     */
    public static function create_site_product( $site_id, $args = array() ) {
        
        // Verify site exists
        $site = get_post( $site_id );
        if ( ! $site || $site->post_type !== 'superfund_site' ) {
            return new WP_Error( 'invalid_site', __( 'Invalid superfund site ID', 'env-intel-core' ) );
        }

        // Parse arguments
        $defaults = array(
            'name' => sprintf( __( 'Product for %s', 'env-intel-core' ), $site->post_title ),
            'type' => 'simple',
            'price' => '0',
            'description' => '',
            'short_description' => '',
            'sku' => '',
            'manage_stock' => false,
            'stock_quantity' => null,
            'in_stock' => true,
        );
        
        $args = wp_parse_args( $args, $defaults );

        // Create product
        $product = new WC_Product_Simple();
        $product->set_name( $args['name'] );
        $product->set_regular_price( $args['price'] );
        $product->set_description( $args['description'] );
        $product->set_short_description( $args['short_description'] );
        
        if ( ! empty( $args['sku'] ) ) {
            $product->set_sku( $args['sku'] );
        }
        
        if ( $args['manage_stock'] ) {
            $product->set_manage_stock( true );
            $product->set_stock_quantity( $args['stock_quantity'] );
        }
        
        $product->set_stock_status( $args['in_stock'] ? 'instock' : 'outofstock' );
        
        // Save product
        $product_id = $product->save();
        
        if ( ! $product_id ) {
            return new WP_Error( 'product_creation_failed', __( 'Failed to create product', 'env-intel-core' ) );
        }

        // Associate with site
        update_post_meta( $product_id, '_eic_associated_site', $site_id );

        return $product_id;
    }
}
