<?php
/**
 * Custom Post Types Registration
 *
 * Registers custom post types for the Environmental Intelligence Core.
 * Uses WordPress native CPT functionality for seamless admin integration.
 *
 * @package EnvironmentalIntelligenceCore
 * @subpackage PostTypes
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * EIC_Post_Types Class
 */
class EIC_Post_Types {

    /**
     * Initialize post types
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
        add_action( 'save_post', array( __CLASS__, 'save_meta_boxes' ) );
    }

    /**
     * Register core post types
     */
    public static function register_post_types() {
        
        if ( ! is_blog_installed() || post_type_exists( 'superfund_site' ) ) {
            return;
        }

        // Register Superfund Site CPT
        register_post_type(
            'superfund_site',
            apply_filters(
                'eic_register_post_type_superfund_site',
                array(
                    'labels' => array(
                        'name' => __( 'Superfund Sites', 'env-intel-core' ),
                        'singular_name' => __( 'Superfund Site', 'env-intel-core' ),
                        'menu_name' => _x( 'Superfund Sites', 'Admin menu name', 'env-intel-core' ),
                        'add_new' => __( 'Add Site', 'env-intel-core' ),
                        'add_new_item' => __( 'Add New Site', 'env-intel-core' ),
                        'edit' => __( 'Edit', 'env-intel-core' ),
                        'edit_item' => __( 'Edit Site', 'env-intel-core' ),
                        'new_item' => __( 'New Site', 'env-intel-core' ),
                        'view' => __( 'View Site', 'env-intel-core' ),
                        'view_item' => __( 'View Site', 'env-intel-core' ),
                        'search_items' => __( 'Search Sites', 'env-intel-core' ),
                        'not_found' => __( 'No sites found', 'env-intel-core' ),
                        'not_found_in_trash' => __( 'No sites found in trash', 'env-intel-core' ),
                        'all_items' => __( 'All Sites', 'env-intel-core' ),
                    ),
                    'description' => __( 'Environmental contamination sites requiring remediation', 'env-intel-core' ),
                    'public' => true,
                    'show_ui' => true,
                    'show_in_menu' => true,
                    'menu_icon' => 'dashicons-location',
                    'menu_position' => 26,
                    'capability_type' => 'post',
                    'map_meta_cap' => true,
                    'publicly_queryable' => true,
                    'exclude_from_search' => false,
                    'hierarchical' => false,
                    'query_var' => true,
                    'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ),
                    'has_archive' => true,
                    'rewrite' => array( 'slug' => 'superfund-sites', 'with_front' => false ),
                    'show_in_rest' => true,
                    'rest_base' => 'superfund-sites',
                )
            )
        );

        // Register Remediation Action CPT
        register_post_type(
            'remediation_action',
            apply_filters(
                'eic_register_post_type_remediation_action',
                array(
                    'labels' => array(
                        'name' => __( 'Remediation Actions', 'env-intel-core' ),
                        'singular_name' => __( 'Remediation Action', 'env-intel-core' ),
                        'menu_name' => _x( 'Remediation Actions', 'Admin menu name', 'env-intel-core' ),
                        'add_new' => __( 'Add Action', 'env-intel-core' ),
                        'add_new_item' => __( 'Add New Action', 'env-intel-core' ),
                        'edit' => __( 'Edit', 'env-intel-core' ),
                        'edit_item' => __( 'Edit Action', 'env-intel-core' ),
                        'new_item' => __( 'New Action', 'env-intel-core' ),
                        'view' => __( 'View Action', 'env-intel-core' ),
                        'view_item' => __( 'View Action', 'env-intel-core' ),
                        'search_items' => __( 'Search Actions', 'env-intel-core' ),
                        'not_found' => __( 'No actions found', 'env-intel-core' ),
                        'not_found_in_trash' => __( 'No actions found in trash', 'env-intel-core' ),
                        'all_items' => __( 'All Actions', 'env-intel-core' ),
                    ),
                    'description' => __( 'Remediation and cleanup actions for environmental sites', 'env-intel-core' ),
                    'public' => true,
                    'show_ui' => true,
                    'show_in_menu' => 'edit.php?post_type=superfund_site',
                    'capability_type' => 'post',
                    'map_meta_cap' => true,
                    'publicly_queryable' => true,
                    'exclude_from_search' => false,
                    'hierarchical' => false,
                    'query_var' => true,
                    'supports' => array( 'title', 'editor', 'custom-fields', 'revisions' ),
                    'has_archive' => true,
                    'rewrite' => array( 'slug' => 'remediation-actions', 'with_front' => false ),
                    'show_in_rest' => true,
                    'rest_base' => 'remediation-actions',
                )
            )
        );
    }

    /**
     * Add meta boxes
     */
    public static function add_meta_boxes() {
        
        // Superfund Site meta boxes
        add_meta_box(
            'eic_site_details',
            __( 'Site Details', 'env-intel-core' ),
            array( __CLASS__, 'render_site_details_meta_box' ),
            'superfund_site',
            'normal',
            'high'
        );

        add_meta_box(
            'eic_site_location',
            __( 'Location Information', 'env-intel-core' ),
            array( __CLASS__, 'render_site_location_meta_box' ),
            'superfund_site',
            'side',
            'default'
        );

        add_meta_box(
            'eic_site_status',
            __( 'Remediation Status', 'env-intel-core' ),
            array( __CLASS__, 'render_site_status_meta_box' ),
            'superfund_site',
            'side',
            'default'
        );

        // Remediation Action meta boxes
        add_meta_box(
            'eic_action_details',
            __( 'Action Details', 'env-intel-core' ),
            array( __CLASS__, 'render_action_details_meta_box' ),
            'remediation_action',
            'normal',
            'high'
        );

        add_meta_box(
            'eic_action_site',
            __( 'Associated Site', 'env-intel-core' ),
            array( __CLASS__, 'render_action_site_meta_box' ),
            'remediation_action',
            'side',
            'default'
        );
    }

    /**
     * Render Site Details meta box
     */
    public static function render_site_details_meta_box( $post ) {
        wp_nonce_field( 'eic_site_details_nonce', 'eic_site_details_nonce' );
        
        $epa_id = get_post_meta( $post->ID, '_eic_epa_id', true );
        $npl_status = get_post_meta( $post->ID, '_eic_npl_status', true );
        $lead_agency = get_post_meta( $post->ID, '_eic_lead_agency', true );
        $remediation_tech = get_post_meta( $post->ID, '_eic_remediation_technology', true );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="eic_epa_id"><?php _e( 'EPA ID', 'env-intel-core' ); ?></label></th>
                <td><input type="text" id="eic_epa_id" name="eic_epa_id" value="<?php echo esc_attr( $epa_id ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="eic_npl_status"><?php _e( 'NPL Status', 'env-intel-core' ); ?></label></th>
                <td>
                    <select id="eic_npl_status" name="eic_npl_status">
                        <option value=""><?php _e( 'Select Status', 'env-intel-core' ); ?></option>
                        <option value="proposed" <?php selected( $npl_status, 'proposed' ); ?>><?php _e( 'Proposed', 'env-intel-core' ); ?></option>
                        <option value="final" <?php selected( $npl_status, 'final' ); ?>><?php _e( 'Final', 'env-intel-core' ); ?></option>
                        <option value="deleted" <?php selected( $npl_status, 'deleted' ); ?>><?php _e( 'Deleted', 'env-intel-core' ); ?></option>
                        <option value="not_on_npl" <?php selected( $npl_status, 'not_on_npl' ); ?>><?php _e( 'Not on NPL', 'env-intel-core' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="eic_lead_agency"><?php _e( 'Lead Agency', 'env-intel-core' ); ?></label></th>
                <td><input type="text" id="eic_lead_agency" name="eic_lead_agency" value="<?php echo esc_attr( $lead_agency ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="eic_remediation_technology"><?php _e( 'Remediation Technology', 'env-intel-core' ); ?></label></th>
                <td><textarea id="eic_remediation_technology" name="eic_remediation_technology" rows="3" class="large-text"><?php echo esc_textarea( $remediation_tech ); ?></textarea></td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render Site Location meta box
     */
    public static function render_site_location_meta_box( $post ) {
        $latitude = get_post_meta( $post->ID, '_eic_latitude', true );
        $longitude = get_post_meta( $post->ID, '_eic_longitude', true );
        ?>
        <p>
            <label for="eic_latitude"><?php _e( 'Latitude', 'env-intel-core' ); ?></label><br>
            <input type="text" id="eic_latitude" name="eic_latitude" value="<?php echo esc_attr( $latitude ); ?>" class="widefat" placeholder="37.7749" />
        </p>
        <p>
            <label for="eic_longitude"><?php _e( 'Longitude', 'env-intel-core' ); ?></label><br>
            <input type="text" id="eic_longitude" name="eic_longitude" value="<?php echo esc_attr( $longitude ); ?>" class="widefat" placeholder="-122.4194" />
        </p>
        <?php
    }

    /**
     * Render Site Status meta box
     */
    public static function render_site_status_meta_box( $post ) {
        $site_status = get_post_meta( $post->ID, '_eic_site_status', true );
        $completion_date = get_post_meta( $post->ID, '_eic_projected_completion_date', true );
        ?>
        <p>
            <label for="eic_site_status"><?php _e( 'Site Status', 'env-intel-core' ); ?></label><br>
            <select id="eic_site_status" name="eic_site_status" class="widefat">
                <option value=""><?php _e( 'Select Status', 'env-intel-core' ); ?></option>
                <option value="assessment" <?php selected( $site_status, 'assessment' ); ?>><?php _e( 'Assessment', 'env-intel-core' ); ?></option>
                <option value="cleanup" <?php selected( $site_status, 'cleanup' ); ?>><?php _e( 'Cleanup', 'env-intel-core' ); ?></option>
                <option value="monitoring" <?php selected( $site_status, 'monitoring' ); ?>><?php _e( 'Monitoring', 'env-intel-core' ); ?></option>
                <option value="completed" <?php selected( $site_status, 'completed' ); ?>><?php _e( 'Completed', 'env-intel-core' ); ?></option>
            </select>
        </p>
        <p>
            <label for="eic_projected_completion_date"><?php _e( 'Projected Completion', 'env-intel-core' ); ?></label><br>
            <input type="date" id="eic_projected_completion_date" name="eic_projected_completion_date" value="<?php echo esc_attr( $completion_date ); ?>" class="widefat" />
        </p>
        <?php
    }

    /**
     * Render Action Details meta box
     */
    public static function render_action_details_meta_box( $post ) {
        wp_nonce_field( 'eic_action_details_nonce', 'eic_action_details_nonce' );
        
        $action_type = get_post_meta( $post->ID, '_eic_action_type', true );
        $start_date = get_post_meta( $post->ID, '_eic_start_date', true );
        $end_date = get_post_meta( $post->ID, '_eic_end_date', true );
        $responsible_party = get_post_meta( $post->ID, '_eic_responsible_party', true );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="eic_action_type"><?php _e( 'Action Type', 'env-intel-core' ); ?></label></th>
                <td>
                    <select id="eic_action_type" name="eic_action_type">
                        <option value=""><?php _e( 'Select Type', 'env-intel-core' ); ?></option>
                        <option value="investigation" <?php selected( $action_type, 'investigation' ); ?>><?php _e( 'Investigation', 'env-intel-core' ); ?></option>
                        <option value="cleanup" <?php selected( $action_type, 'cleanup' ); ?>><?php _e( 'Cleanup', 'env-intel-core' ); ?></option>
                        <option value="monitoring" <?php selected( $action_type, 'monitoring' ); ?>><?php _e( 'Monitoring', 'env-intel-core' ); ?></option>
                        <option value="remediation" <?php selected( $action_type, 'remediation' ); ?>><?php _e( 'Remediation', 'env-intel-core' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="eic_start_date"><?php _e( 'Start Date', 'env-intel-core' ); ?></label></th>
                <td><input type="date" id="eic_start_date" name="eic_start_date" value="<?php echo esc_attr( $start_date ); ?>" /></td>
            </tr>
            <tr>
                <th><label for="eic_end_date"><?php _e( 'End Date', 'env-intel-core' ); ?></label></th>
                <td><input type="date" id="eic_end_date" name="eic_end_date" value="<?php echo esc_attr( $end_date ); ?>" /></td>
            </tr>
            <tr>
                <th><label for="eic_responsible_party"><?php _e( 'Responsible Party', 'env-intel-core' ); ?></label></th>
                <td><input type="text" id="eic_responsible_party" name="eic_responsible_party" value="<?php echo esc_attr( $responsible_party ); ?>" class="regular-text" /></td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render Action Site meta box
     */
    public static function render_action_site_meta_box( $post ) {
        $associated_site = get_post_meta( $post->ID, '_eic_associated_site', true );
        
        $sites = get_posts( array(
            'post_type' => 'superfund_site',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ) );
        ?>
        <p>
            <label for="eic_associated_site"><?php _e( 'Superfund Site', 'env-intel-core' ); ?></label><br>
            <select id="eic_associated_site" name="eic_associated_site" class="widefat">
                <option value=""><?php _e( 'Select Site', 'env-intel-core' ); ?></option>
                <?php foreach ( $sites as $site ) : ?>
                    <option value="<?php echo esc_attr( $site->ID ); ?>" <?php selected( $associated_site, $site->ID ); ?>>
                        <?php echo esc_html( $site->post_title ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    /**
     * Save meta box data
     */
    public static function save_meta_boxes( $post_id ) {
        
        // Check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save Superfund Site meta
        if ( get_post_type( $post_id ) === 'superfund_site' ) {
            if ( isset( $_POST['eic_site_details_nonce'] ) && wp_verify_nonce( $_POST['eic_site_details_nonce'], 'eic_site_details_nonce' ) ) {
                
                $fields = array(
                    'eic_epa_id' => '_eic_epa_id',
                    'eic_npl_status' => '_eic_npl_status',
                    'eic_lead_agency' => '_eic_lead_agency',
                    'eic_remediation_technology' => '_eic_remediation_technology',
                    'eic_latitude' => '_eic_latitude',
                    'eic_longitude' => '_eic_longitude',
                    'eic_site_status' => '_eic_site_status',
                    'eic_projected_completion_date' => '_eic_projected_completion_date'
                );

                foreach ( $fields as $field => $meta_key ) {
                    if ( isset( $_POST[ $field ] ) ) {
                        update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $field ] ) );
                    }
                }
            }
        }

        // Save Remediation Action meta
        if ( get_post_type( $post_id ) === 'remediation_action' ) {
            if ( isset( $_POST['eic_action_details_nonce'] ) && wp_verify_nonce( $_POST['eic_action_details_nonce'], 'eic_action_details_nonce' ) ) {
                
                $fields = array(
                    'eic_action_type' => '_eic_action_type',
                    'eic_start_date' => '_eic_start_date',
                    'eic_end_date' => '_eic_end_date',
                    'eic_responsible_party' => '_eic_responsible_party',
                    'eic_associated_site' => '_eic_associated_site'
                );

                foreach ( $fields as $field => $meta_key ) {
                    if ( isset( $_POST[ $field ] ) ) {
                        update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $field ] ) );
                    }
                }
            }
        }
    }
}
