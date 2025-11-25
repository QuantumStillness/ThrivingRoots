<?php
/**
 * Geospatial Intelligence Integration
 *
 * Integrates geospatial analysis results with WordPress and the Environmental Intelligence Core.
 * Provides methods to import GeoJSON data, display maps, and manage spatial layers.
 *
 * @package EnvironmentalIntelligenceCore
 * @subpackage Geospatial
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * EIC_Geospatial Class
 */
class EIC_Geospatial {

    /**
     * Initialize geospatial features
     */
    public static function init() {
        // Register custom post type for environmental layers
        add_action( 'init', array( __CLASS__, 'register_layer_cpt' ), 6 );
        
        // Add meta boxes
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
        add_action( 'save_post', array( __CLASS__, 'save_meta_boxes' ) );
        
        // Add shortcodes
        add_shortcode( 'environmental_map', array( __CLASS__, 'render_map_shortcode' ) );
        add_shortcode( 'risk_heatmap', array( __CLASS__, 'render_heatmap_shortcode' ) );
        
        // Enqueue scripts
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_map_scripts' ) );
    }

    /**
     * Register Environmental Layer custom post type
     */
    public static function register_layer_cpt() {
        register_post_type(
            'environmental_layer',
            apply_filters(
                'eic_register_post_type_environmental_layer',
                array(
                    'labels' => array(
                        'name' => __( 'Environmental Layers', 'env-intel-core' ),
                        'singular_name' => __( 'Environmental Layer', 'env-intel-core' ),
                        'menu_name' => _x( 'Geo Layers', 'Admin menu name', 'env-intel-core' ),
                        'add_new' => __( 'Add Layer', 'env-intel-core' ),
                        'add_new_item' => __( 'Add New Layer', 'env-intel-core' ),
                        'edit_item' => __( 'Edit Layer', 'env-intel-core' ),
                        'view_item' => __( 'View Layer', 'env-intel-core' ),
                    ),
                    'description' => __( 'Geospatial environmental data layers', 'env-intel-core' ),
                    'public' => true,
                    'show_ui' => true,
                    'show_in_menu' => 'edit.php?post_type=superfund_site',
                    'menu_icon' => 'dashicons-location-alt',
                    'capability_type' => 'post',
                    'supports' => array( 'title', 'editor', 'custom-fields' ),
                    'has_archive' => true,
                    'rewrite' => array( 'slug' => 'geo-layers' ),
                    'show_in_rest' => true,
                )
            )
        );
    }

    /**
     * Add meta boxes
     */
    public static function add_meta_boxes() {
        add_meta_box(
            'eic_layer_data',
            __( 'Layer Data', 'env-intel-core' ),
            array( __CLASS__, 'render_layer_data_meta_box' ),
            'environmental_layer',
            'normal',
            'high'
        );

        add_meta_box(
            'eic_layer_map',
            __( 'Map Preview', 'env-intel-core' ),
            array( __CLASS__, 'render_layer_map_meta_box' ),
            'environmental_layer',
            'side',
            'default'
        );
    }

    /**
     * Render layer data meta box
     */
    public static function render_layer_data_meta_box( $post ) {
        wp_nonce_field( 'eic_layer_data_nonce', 'eic_layer_data_nonce' );
        
        $layer_type = get_post_meta( $post->ID, '_eic_layer_type', true );
        $data_source = get_post_meta( $post->ID, '_eic_data_source', true );
        $geojson = get_post_meta( $post->ID, '_eic_geojson', true );
        $risk_score = get_post_meta( $post->ID, '_eic_risk_score', true );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="eic_layer_type"><?php _e( 'Layer Type', 'env-intel-core' ); ?></label></th>
                <td>
                    <select id="eic_layer_type" name="eic_layer_type" class="regular-text">
                        <option value=""><?php _e( 'Select Type', 'env-intel-core' ); ?></option>
                        <option value="air_quality" <?php selected( $layer_type, 'air_quality' ); ?>><?php _e( 'Air Quality', 'env-intel-core' ); ?></option>
                        <option value="water_quality" <?php selected( $layer_type, 'water_quality' ); ?>><?php _e( 'Water Quality', 'env-intel-core' ); ?></option>
                        <option value="superfund_sites" <?php selected( $layer_type, 'superfund_sites' ); ?>><?php _e( 'Superfund Sites', 'env-intel-core' ); ?></option>
                        <option value="risk_assessment" <?php selected( $layer_type, 'risk_assessment' ); ?>><?php _e( 'Risk Assessment', 'env-intel-core' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="eic_data_source"><?php _e( 'Data Source', 'env-intel-core' ); ?></label></th>
                <td><input type="text" id="eic_data_source" name="eic_data_source" value="<?php echo esc_attr( $data_source ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="eic_risk_score"><?php _e( 'Risk Score', 'env-intel-core' ); ?></label></th>
                <td>
                    <input type="number" id="eic_risk_score" name="eic_risk_score" value="<?php echo esc_attr( $risk_score ); ?>" min="0" max="1" step="0.01" />
                    <p class="description"><?php _e( 'Composite risk score (0-1)', 'env-intel-core' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="eic_geojson"><?php _e( 'GeoJSON Data', 'env-intel-core' ); ?></label></th>
                <td>
                    <textarea id="eic_geojson" name="eic_geojson" rows="10" class="large-text code"><?php echo esc_textarea( $geojson ); ?></textarea>
                    <p class="description"><?php _e( 'Paste GeoJSON feature or feature collection', 'env-intel-core' ); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render layer map preview meta box
     */
    public static function render_layer_map_meta_box( $post ) {
        $geojson = get_post_meta( $post->ID, '_eic_geojson', true );
        
        if ( ! empty( $geojson ) ) {
            $data = json_decode( $geojson, true );
            
            if ( $data && isset( $data['geometry']['coordinates'] ) ) {
                $coords = $data['geometry']['coordinates'];
                $lat = $coords[1];
                $lon = $coords[0];
                ?>
                <p><strong><?php _e( 'Location:', 'env-intel-core' ); ?></strong></p>
                <p>
                    <?php _e( 'Latitude:', 'env-intel-core' ); ?> <?php echo esc_html( $lat ); ?><br>
                    <?php _e( 'Longitude:', 'env-intel-core' ); ?> <?php echo esc_html( $lon ); ?>
                </p>
                <p>
                    <a href="https://www.google.com/maps?q=<?php echo esc_attr( $lat ); ?>,<?php echo esc_attr( $lon ); ?>" target="_blank">
                        <?php _e( 'View on Google Maps', 'env-intel-core' ); ?> &rarr;
                    </a>
                </p>
                <?php
            }
        } else {
            ?>
            <p><?php _e( 'No geographic data available.', 'env-intel-core' ); ?></p>
            <?php
        }
    }

    /**
     * Save meta boxes
     */
    public static function save_meta_boxes( $post_id ) {
        
        if ( ! isset( $_POST['eic_layer_data_nonce'] ) || ! wp_verify_nonce( $_POST['eic_layer_data_nonce'], 'eic_layer_data_nonce' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( get_post_type( $post_id ) !== 'environmental_layer' ) {
            return;
        }

        $fields = array(
            'eic_layer_type' => '_eic_layer_type',
            'eic_data_source' => '_eic_data_source',
            'eic_risk_score' => '_eic_risk_score',
            'eic_geojson' => '_eic_geojson'
        );

        foreach ( $fields as $field => $meta_key ) {
            if ( isset( $_POST[ $field ] ) ) {
                $value = $_POST[ $field ];
                
                // Special handling for GeoJSON
                if ( $field === 'eic_geojson' ) {
                    $value = wp_unslash( $value );
                    
                    // Validate JSON
                    $decoded = json_decode( $value );
                    if ( json_last_error() !== JSON_ERROR_NONE ) {
                        continue; // Skip invalid JSON
                    }
                } else {
                    $value = sanitize_text_field( $value );
                }
                
                update_post_meta( $post_id, $meta_key, $value );
            }
        }
    }

    /**
     * Enqueue map scripts
     */
    public static function enqueue_map_scripts() {
        // Leaflet CSS and JS
        wp_enqueue_style(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            array(),
            '1.9.4'
        );

        wp_enqueue_script(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            array(),
            '1.9.4',
            true
        );

        // Custom map script
        wp_enqueue_script(
            'eic-maps',
            EIC_PLUGIN_URL . 'assets/js/maps.js',
            array( 'jquery', 'leaflet' ),
            EIC_VERSION,
            true
        );
    }

    /**
     * Render map shortcode
     * 
     * Usage: [environmental_map layer_id="123" height="400px"]
     */
    public static function render_map_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'layer_id' => '',
            'height' => '400px',
            'zoom' => '10'
        ), $atts );

        if ( empty( $atts['layer_id'] ) ) {
            return '<p>' . __( 'No layer ID specified.', 'env-intel-core' ) . '</p>';
        }

        $geojson = get_post_meta( $atts['layer_id'], '_eic_geojson', true );
        
        if ( empty( $geojson ) ) {
            return '<p>' . __( 'No geographic data available for this layer.', 'env-intel-core' ) . '</p>';
        }

        $map_id = 'eic-map-' . $atts['layer_id'];
        
        ob_start();
        ?>
        <div id="<?php echo esc_attr( $map_id ); ?>" style="height: <?php echo esc_attr( $atts['height'] ); ?>; width: 100%;"></div>
        <script>
        (function() {
            var mapData = <?php echo $geojson; ?>;
            var coords = mapData.geometry.coordinates;
            var map = L.map('<?php echo esc_js( $map_id ); ?>').setView([coords[1], coords[0]], <?php echo intval( $atts['zoom'] ); ?>);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            L.geoJSON(mapData, {
                onEachFeature: function(feature, layer) {
                    if (feature.properties) {
                        var popup = '<strong>' + (feature.properties.location || feature.properties.site_name || 'Location') + '</strong>';
                        for (var key in feature.properties) {
                            if (key !== 'location' && key !== 'site_name') {
                                popup += '<br>' + key + ': ' + feature.properties[key];
                            }
                        }
                        layer.bindPopup(popup);
                    }
                }
            }).addTo(map);
        })();
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Render heatmap shortcode
     * 
     * Usage: [risk_heatmap]
     */
    public static function render_heatmap_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'height' => '500px',
            'center_lat' => '36.7783',
            'center_lon' => '-119.4179',
            'zoom' => '6'
        ), $atts );

        // Get all environmental layers with risk scores
        $layers = get_posts( array(
            'post_type' => 'environmental_layer',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_eic_risk_score',
                    'compare' => 'EXISTS'
                )
            )
        ) );

        if ( empty( $layers ) ) {
            return '<p>' . __( 'No risk data available.', 'env-intel-core' ) . '</p>';
        }

        $map_id = 'eic-heatmap-' . uniqid();
        $points = array();

        foreach ( $layers as $layer ) {
            $geojson = get_post_meta( $layer->ID, '_eic_geojson', true );
            $risk_score = get_post_meta( $layer->ID, '_eic_risk_score', true );
            
            if ( ! empty( $geojson ) && ! empty( $risk_score ) ) {
                $data = json_decode( $geojson, true );
                if ( isset( $data['geometry']['coordinates'] ) ) {
                    $coords = $data['geometry']['coordinates'];
                    $points[] = array(
                        'lat' => $coords[1],
                        'lon' => $coords[0],
                        'intensity' => floatval( $risk_score )
                    );
                }
            }
        }

        ob_start();
        ?>
        <div id="<?php echo esc_attr( $map_id ); ?>" style="height: <?php echo esc_attr( $atts['height'] ); ?>; width: 100%;"></div>
        <script>
        (function() {
            var map = L.map('<?php echo esc_js( $map_id ); ?>').setView([<?php echo floatval( $atts['center_lat'] ); ?>, <?php echo floatval( $atts['center_lon'] ); ?>], <?php echo intval( $atts['zoom'] ); ?>);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            var points = <?php echo json_encode( $points ); ?>;
            
            points.forEach(function(point) {
                var color = point.intensity >= 0.7 ? 'red' : (point.intensity >= 0.4 ? 'orange' : 'green');
                L.circleMarker([point.lat, point.lon], {
                    radius: 8,
                    fillColor: color,
                    color: '#000',
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.6
                }).addTo(map).bindPopup('Risk Score: ' + point.intensity.toFixed(3));
            });
        })();
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Import GeoJSON data from file
     * 
     * @param string $file_path Path to GeoJSON file
     * @param string $layer_type Type of layer
     * @param string $data_source Data source name
     * @return array Array of created post IDs
     */
    public static function import_geojson_file( $file_path, $layer_type, $data_source ) {
        
        if ( ! file_exists( $file_path ) ) {
            return new WP_Error( 'file_not_found', __( 'GeoJSON file not found', 'env-intel-core' ) );
        }

        $geojson_content = file_get_contents( $file_path );
        $geojson_data = json_decode( $geojson_content, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return new WP_Error( 'invalid_json', __( 'Invalid GeoJSON format', 'env-intel-core' ) );
        }

        $created_posts = array();

        if ( isset( $geojson_data['features'] ) ) {
            // FeatureCollection
            foreach ( $geojson_data['features'] as $feature ) {
                $post_id = self::import_geojson_feature( $feature, $layer_type, $data_source );
                if ( ! is_wp_error( $post_id ) ) {
                    $created_posts[] = $post_id;
                }
            }
        } else {
            // Single feature
            $post_id = self::import_geojson_feature( $geojson_data, $layer_type, $data_source );
            if ( ! is_wp_error( $post_id ) ) {
                $created_posts[] = $post_id;
            }
        }

        return $created_posts;
    }

    /**
     * Import single GeoJSON feature
     * 
     * @param array $feature GeoJSON feature
     * @param string $layer_type Layer type
     * @param string $data_source Data source
     * @return int|WP_Error Post ID or error
     */
    private static function import_geojson_feature( $feature, $layer_type, $data_source ) {
        
        $properties = isset( $feature['properties'] ) ? $feature['properties'] : array();
        
        $title = isset( $properties['location'] ) ? $properties['location'] : 
                (isset( $properties['site_name'] ) ? $properties['site_name'] : 
                'Environmental Layer');

        $post_data = array(
            'post_type' => 'environmental_layer',
            'post_title' => $title,
            'post_status' => 'publish',
            'meta_input' => array(
                '_eic_layer_type' => $layer_type,
                '_eic_data_source' => $data_source,
                '_eic_geojson' => wp_json_encode( $feature ),
                '_eic_properties' => wp_json_encode( $properties )
            )
        );

        // Add risk score if available
        if ( isset( $properties['risk_score'] ) ) {
            $post_data['meta_input']['_eic_risk_score'] = floatval( $properties['risk_score'] );
        }

        $post_id = wp_insert_post( $post_data );

        return $post_id;
    }
}
