<?php
/**
 * Material Safety Database Integration
 * 
 * Integrates UNECE PRTR and EWG Tap Water databases for material safety
 * and water quality information.
 *
 * @package ThrivingRoots
 * @subpackage CommunityResilience
 */

if (!defined('ABSPATH')) {
    exit;
}

class Material_Safety_Database {
    
    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('init', array($this, 'register_custom_post_types'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_shortcode('water_quality_lookup', array($this, 'water_quality_lookup_shortcode'));
        add_shortcode('chemical_safety_info', array($this, 'chemical_safety_info_shortcode'));
    }
    
    /**
     * Register custom post types for material safety data
     */
    public function register_custom_post_types() {
        // Water Quality Data CPT
        register_post_type('water_quality_data', array(
            'labels' => array(
                'name' => __('Water Quality Data', 'thriving-roots'),
                'singular_name' => __('Water Quality Entry', 'thriving-roots'),
                'add_new' => __('Add New Entry', 'thriving-roots'),
                'add_new_item' => __('Add New Water Quality Entry', 'thriving-roots'),
                'edit_item' => __('Edit Water Quality Entry', 'thriving-roots'),
                'view_item' => __('View Water Quality Entry', 'thriving-roots'),
                'search_items' => __('Search Water Quality Data', 'thriving-roots'),
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => array('title', 'editor', 'custom-fields'),
            'menu_icon' => 'dashicons-filter',
            'capability_type' => 'post',
        ));
        
        // Chemical Safety Data CPT
        register_post_type('chemical_safety', array(
            'labels' => array(
                'name' => __('Chemical Safety Data', 'thriving-roots'),
                'singular_name' => __('Chemical Entry', 'thriving-roots'),
                'add_new' => __('Add New Chemical', 'thriving-roots'),
                'add_new_item' => __('Add New Chemical Entry', 'thriving-roots'),
                'edit_item' => __('Edit Chemical Entry', 'thriving-roots'),
                'view_item' => __('View Chemical Entry', 'thriving-roots'),
                'search_items' => __('Search Chemicals', 'thriving-roots'),
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => array('title', 'editor', 'custom-fields'),
            'menu_icon' => 'dashicons-warning',
            'capability_type' => 'post',
        ));
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('thriving-roots/v1', '/water-quality/(?P<zip>[0-9]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_water_quality_by_zip'),
            'permission_callback' => '__return_true',
        ));
        
        register_rest_route('thriving-roots/v1', '/chemical-safety/(?P<cas>[A-Za-z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_chemical_safety_by_cas'),
            'permission_callback' => '__return_true',
        ));
        
        register_rest_route('thriving-roots/v1', '/water-quality/utility/(?P<utility_id>[A-Za-z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_water_quality_by_utility'),
            'permission_callback' => '__return_true',
        ));
    }
    
    /**
     * Fetch water quality data from EWG Tap Water Database
     * 
     * @param string $zip_code ZIP code to lookup
     * @return array Water quality data
     */
    public function fetch_ewg_water_quality($zip_code) {
        // EWG Tap Water Database API endpoint (if available)
        // Note: EWG may not have a public API, so this would need to be adapted
        // to scrape their website or use cached data
        
        $cached_data = get_transient('ewg_water_' . $zip_code);
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        // For now, return sample data structure
        // In production, implement actual API call or web scraping
        $data = array(
            'zip_code' => $zip_code,
            'utility_name' => 'Sample Water Utility',
            'contaminants' => array(
                array(
                    'name' => 'Lead',
                    'level' => '5 ppb',
                    'legal_limit' => '15 ppb',
                    'ewg_guideline' => '1 ppb',
                    'health_effects' => 'Developmental delays in children, kidney problems',
                    'sources' => 'Corrosion of household plumbing',
                    'risk_level' => 'moderate',
                ),
                array(
                    'name' => 'Chlorine',
                    'level' => '2.5 ppm',
                    'legal_limit' => '4 ppm',
                    'ewg_guideline' => '0.4 ppm',
                    'health_effects' => 'Respiratory issues, skin irritation',
                    'sources' => 'Water treatment disinfection',
                    'risk_level' => 'low',
                ),
            ),
            'overall_quality' => 'Fair',
            'last_updated' => current_time('mysql'),
        );
        
        set_transient('ewg_water_' . $zip_code, $data, DAY_IN_SECONDS);
        
        return $data;
    }
    
    /**
     * Fetch chemical safety data from UNECE PRTR
     * 
     * @param string $cas_number CAS Registry Number
     * @return array Chemical safety data
     */
    public function fetch_unece_chemical_data($cas_number) {
        $cached_data = get_transient('unece_chem_' . $cas_number);
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        // UNECE PRTR API endpoint (if available)
        // Note: Implement actual API call to UNECE PRTR database
        
        // Sample data structure
        $data = array(
            'cas_number' => $cas_number,
            'chemical_name' => 'Sample Chemical',
            'hazard_classification' => array(
                'carcinogenic' => false,
                'mutagenic' => false,
                'toxic_to_reproduction' => false,
                'respiratory_sensitizer' => false,
                'skin_sensitizer' => false,
            ),
            'exposure_limits' => array(
                'osha_pel' => '100 ppm',
                'niosh_rel' => '50 ppm',
                'acgih_tlv' => '25 ppm',
            ),
            'health_effects' => array(
                'acute' => 'Irritation of eyes, skin, and respiratory tract',
                'chronic' => 'Prolonged exposure may cause liver damage',
            ),
            'first_aid' => array(
                'inhalation' => 'Move to fresh air. Seek medical attention if symptoms persist.',
                'skin_contact' => 'Wash with soap and water for 15 minutes.',
                'eye_contact' => 'Rinse with water for 15 minutes. Seek medical attention.',
                'ingestion' => 'Do not induce vomiting. Seek immediate medical attention.',
            ),
            'environmental_impact' => 'Toxic to aquatic life with long-lasting effects',
            'disposal' => 'Dispose as hazardous waste according to local regulations',
            'last_updated' => current_time('mysql'),
        );
        
        set_transient('unece_chem_' . $cas_number, $data, WEEK_IN_SECONDS);
        
        return $data;
    }
    
    /**
     * REST API callback: Get water quality by ZIP code
     */
    public function get_water_quality_by_zip($request) {
        $zip = $request['zip'];
        
        if (empty($zip) || strlen($zip) !== 5) {
            return new WP_Error('invalid_zip', 'Invalid ZIP code', array('status' => 400));
        }
        
        $data = $this->fetch_ewg_water_quality($zip);
        
        return rest_ensure_response($data);
    }
    
    /**
     * REST API callback: Get chemical safety by CAS number
     */
    public function get_chemical_safety_by_cas($request) {
        $cas = $request['cas'];
        
        if (empty($cas)) {
            return new WP_Error('invalid_cas', 'Invalid CAS number', array('status' => 400));
        }
        
        $data = $this->fetch_unece_chemical_data($cas);
        
        return rest_ensure_response($data);
    }
    
    /**
     * REST API callback: Get water quality by utility ID
     */
    public function get_water_quality_by_utility($request) {
        $utility_id = $request['utility_id'];
        
        // Query WordPress posts for water quality data
        $query = new WP_Query(array(
            'post_type' => 'water_quality_data',
            'meta_key' => 'utility_id',
            'meta_value' => $utility_id,
            'posts_per_page' => 1,
        ));
        
        if ($query->have_posts()) {
            $post = $query->posts[0];
            $data = array(
                'utility_id' => get_post_meta($post->ID, 'utility_id', true),
                'utility_name' => $post->post_title,
                'contaminants' => json_decode(get_post_meta($post->ID, 'contaminants', true), true),
                'overall_quality' => get_post_meta($post->ID, 'overall_quality', true),
                'last_updated' => $post->post_modified,
            );
            
            return rest_ensure_response($data);
        }
        
        return new WP_Error('not_found', 'Utility not found', array('status' => 404));
    }
    
    /**
     * Shortcode: Water quality lookup
     * 
     * Usage: [water_quality_lookup zip="90001"]
     */
    public function water_quality_lookup_shortcode($atts) {
        $atts = shortcode_atts(array(
            'zip' => '',
        ), $atts);
        
        if (empty($atts['zip'])) {
            return '<div class="water-quality-lookup">
                <form id="water-quality-form">
                    <label for="zip-input">Enter your ZIP code:</label>
                    <input type="text" id="zip-input" name="zip" maxlength="5" pattern="[0-9]{5}" required>
                    <button type="submit">Check Water Quality</button>
                </form>
                <div id="water-quality-results"></div>
            </div>';
        }
        
        $data = $this->fetch_ewg_water_quality($atts['zip']);
        
        ob_start();
        ?>
        <div class="water-quality-results">
            <h3>Water Quality Report for ZIP <?php echo esc_html($data['zip_code']); ?></h3>
            <p><strong>Utility:</strong> <?php echo esc_html($data['utility_name']); ?></p>
            <p><strong>Overall Quality:</strong> <span class="quality-badge"><?php echo esc_html($data['overall_quality']); ?></span></p>
            
            <h4>Contaminants Detected</h4>
            <table class="contaminants-table">
                <thead>
                    <tr>
                        <th>Contaminant</th>
                        <th>Level</th>
                        <th>Legal Limit</th>
                        <th>EWG Guideline</th>
                        <th>Risk</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['contaminants'] as $contaminant): ?>
                    <tr class="risk-<?php echo esc_attr($contaminant['risk_level']); ?>">
                        <td><?php echo esc_html($contaminant['name']); ?></td>
                        <td><?php echo esc_html($contaminant['level']); ?></td>
                        <td><?php echo esc_html($contaminant['legal_limit']); ?></td>
                        <td><?php echo esc_html($contaminant['ewg_guideline']); ?></td>
                        <td><?php echo esc_html(ucfirst($contaminant['risk_level'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="health-effects">
                <h4>Health Effects & Sources</h4>
                <?php foreach ($data['contaminants'] as $contaminant): ?>
                <div class="contaminant-detail">
                    <h5><?php echo esc_html($contaminant['name']); ?></h5>
                    <p><strong>Health Effects:</strong> <?php echo esc_html($contaminant['health_effects']); ?></p>
                    <p><strong>Sources:</strong> <?php echo esc_html($contaminant['sources']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="recommendations">
                <h4>Recommendations</h4>
                <ul>
                    <li>Consider installing a certified water filter</li>
                    <li>Test your water regularly, especially if you have old pipes</li>
                    <li>Contact your water utility for more information</li>
                    <li>Visit <a href="https://www.ewg.org/tapwater/" target="_blank">EWG Tap Water Database</a> for detailed reports</li>
                </ul>
            </div>
            
            <p class="last-updated">Last updated: <?php echo esc_html($data['last_updated']); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Chemical safety information
     * 
     * Usage: [chemical_safety_info cas="50-00-0"]
     */
    public function chemical_safety_info_shortcode($atts) {
        $atts = shortcode_atts(array(
            'cas' => '',
            'name' => '',
        ), $atts);
        
        if (empty($atts['cas']) && empty($atts['name'])) {
            return '<div class="chemical-safety-lookup">
                <form id="chemical-safety-form">
                    <label for="cas-input">Enter CAS Number or Chemical Name:</label>
                    <input type="text" id="cas-input" name="cas" required>
                    <button type="submit">Get Safety Info</button>
                </form>
                <div id="chemical-safety-results"></div>
            </div>';
        }
        
        $data = $this->fetch_unece_chemical_data($atts['cas']);
        
        ob_start();
        ?>
        <div class="chemical-safety-results">
            <h3><?php echo esc_html($data['chemical_name']); ?></h3>
            <p><strong>CAS Number:</strong> <?php echo esc_html($data['cas_number']); ?></p>
            
            <h4>Hazard Classification</h4>
            <ul class="hazard-list">
                <?php foreach ($data['hazard_classification'] as $hazard => $present): ?>
                    <?php if ($present): ?>
                    <li class="hazard-present">⚠️ <?php echo esc_html(ucwords(str_replace('_', ' ', $hazard))); ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
            
            <h4>Exposure Limits</h4>
            <table class="exposure-limits-table">
                <tr>
                    <td><strong>OSHA PEL:</strong></td>
                    <td><?php echo esc_html($data['exposure_limits']['osha_pel']); ?></td>
                </tr>
                <tr>
                    <td><strong>NIOSH REL:</strong></td>
                    <td><?php echo esc_html($data['exposure_limits']['niosh_rel']); ?></td>
                </tr>
                <tr>
                    <td><strong>ACGIH TLV:</strong></td>
                    <td><?php echo esc_html($data['exposure_limits']['acgih_tlv']); ?></td>
                </tr>
            </table>
            
            <h4>Health Effects</h4>
            <p><strong>Acute:</strong> <?php echo esc_html($data['health_effects']['acute']); ?></p>
            <p><strong>Chronic:</strong> <?php echo esc_html($data['health_effects']['chronic']); ?></p>
            
            <h4>First Aid</h4>
            <ul>
                <li><strong>Inhalation:</strong> <?php echo esc_html($data['first_aid']['inhalation']); ?></li>
                <li><strong>Skin Contact:</strong> <?php echo esc_html($data['first_aid']['skin_contact']); ?></li>
                <li><strong>Eye Contact:</strong> <?php echo esc_html($data['first_aid']['eye_contact']); ?></li>
                <li><strong>Ingestion:</strong> <?php echo esc_html($data['first_aid']['ingestion']); ?></li>
            </ul>
            
            <h4>Environmental Impact</h4>
            <p><?php echo esc_html($data['environmental_impact']); ?></p>
            
            <h4>Disposal</h4>
            <p><?php echo esc_html($data['disposal']); ?></p>
            
            <p class="last-updated">Last updated: <?php echo esc_html($data['last_updated']); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Import water quality data from CSV
     * 
     * @param string $file_path Path to CSV file
     * @return array Import results
     */
    public static function import_water_quality_csv($file_path) {
        if (!file_exists($file_path)) {
            return array('success' => false, 'message' => 'File not found');
        }
        
        $imported = 0;
        $errors = 0;
        
        if (($handle = fopen($file_path, 'r')) !== false) {
            $headers = fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($headers, $row);
                
                $post_id = wp_insert_post(array(
                    'post_type' => 'water_quality_data',
                    'post_title' => $data['utility_name'],
                    'post_status' => 'publish',
                ));
                
                if ($post_id) {
                    update_post_meta($post_id, 'utility_id', $data['utility_id']);
                    update_post_meta($post_id, 'zip_code', $data['zip_code']);
                    update_post_meta($post_id, 'contaminants', $data['contaminants_json']);
                    update_post_meta($post_id, 'overall_quality', $data['overall_quality']);
                    $imported++;
                } else {
                    $errors++;
                }
            }
            
            fclose($handle);
        }
        
        return array(
            'success' => true,
            'imported' => $imported,
            'errors' => $errors,
        );
    }
}

// Initialize the class
new Material_Safety_Database();
