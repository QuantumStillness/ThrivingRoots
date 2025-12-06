<?php
/**
 * LA City Environmental Resources Integration
 * 
 * Integrates LA City environmental programs, rebates, and resources
 * for community members.
 *
 * @package ThrivingRoots
 * @subpackage CommunityResilience
 */

if (!defined('ABSPATH')) {
    exit;
}

class LA_City_Resources {
    
    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('init', array($this, 'register_custom_post_types'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_shortcode('la_rebate_finder', array($this, 'rebate_finder_shortcode'));
        add_shortcode('la_resource_directory', array($this, 'resource_directory_shortcode'));
        add_shortcode('la_environmental_classes', array($this, 'environmental_classes_shortcode'));
    }
    
    /**
     * Register custom post types
     */
    public function register_custom_post_types() {
        // LA City Resource CPT
        register_post_type('la_city_resource', array(
            'labels' => array(
                'name' => __('LA City Resources', 'thriving-roots'),
                'singular_name' => __('LA City Resource', 'thriving-roots'),
                'add_new' => __('Add New Resource', 'thriving-roots'),
                'add_new_item' => __('Add New LA City Resource', 'thriving-roots'),
                'edit_item' => __('Edit LA City Resource', 'thriving-roots'),
                'view_item' => __('View LA City Resource', 'thriving-roots'),
                'search_items' => __('Search LA City Resources', 'thriving-roots'),
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'menu_icon' => 'dashicons-building',
            'capability_type' => 'post',
            'taxonomies' => array('resource_category', 'resource_tag'),
        ));
        
        // Resource Category Taxonomy
        register_taxonomy('resource_category', 'la_city_resource', array(
            'labels' => array(
                'name' => __('Resource Categories', 'thriving-roots'),
                'singular_name' => __('Resource Category', 'thriving-roots'),
            ),
            'hierarchical' => true,
            'show_in_rest' => true,
            'public' => true,
        ));
        
        // Resource Tag Taxonomy
        register_taxonomy('resource_tag', 'la_city_resource', array(
            'labels' => array(
                'name' => __('Resource Tags', 'thriving-roots'),
                'singular_name' => __('Resource Tag', 'thriving-roots'),
            ),
            'hierarchical' => false,
            'show_in_rest' => true,
            'public' => true,
        ));
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('thriving-roots/v1', '/la-resources', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_la_resources'),
            'permission_callback' => '__return_true',
        ));
        
        register_rest_route('thriving-roots/v1', '/la-rebates', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_la_rebates'),
            'permission_callback' => '__return_true',
        ));
    }
    
    /**
     * Get LA City resources
     */
    public function get_la_resources($request) {
        $category = $request->get_param('category');
        
        $args = array(
            'post_type' => 'la_city_resource',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        );
        
        if ($category) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'resource_category',
                    'field' => 'slug',
                    'terms' => $category,
                ),
            );
        }
        
        $query = new WP_Query($args);
        $resources = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $resources[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'content' => get_the_content(),
                    'url' => get_post_meta(get_the_ID(), 'resource_url', true),
                    'phone' => get_post_meta(get_the_ID(), 'phone', true),
                    'email' => get_post_meta(get_the_ID(), 'email', true),
                    'address' => get_post_meta(get_the_ID(), 'address', true),
                    'category' => wp_get_post_terms(get_the_ID(), 'resource_category', array('fields' => 'names')),
                );
            }
            wp_reset_postdata();
        }
        
        return rest_ensure_response($resources);
    }
    
    /**
     * Get LA City rebates
     */
    public function get_la_rebates($request) {
        // LADWP and LASAN rebate programs
        $rebates = array(
            array(
                'id' => 'turf-replacement',
                'title' => 'Turf Replacement Program',
                'description' => 'Get money back for replacing your lawn with a California Friendly® garden',
                'provider' => 'LADWP',
                'amount' => '$3 per square foot',
                'eligibility' => 'LADWP customers',
                'url' => 'https://www.ladwp.com/turf-replacement',
                'category' => 'Water Conservation',
                'steps' => array(
                    'Apply online at LADWP website',
                    'Schedule pre-inspection',
                    'Remove turf and install new landscaping',
                    'Schedule post-inspection',
                    'Receive rebate payment',
                ),
            ),
            array(
                'id' => 'water-saving-devices',
                'title' => 'Rebates on Water-Saving Devices',
                'description' => 'Rebates for high-efficiency toilets, washing machines, and more',
                'provider' => 'LADWP',
                'amount' => 'Varies by device',
                'eligibility' => 'LADWP customers',
                'url' => 'https://www.ladwp.com/rebates',
                'category' => 'Water Conservation',
                'steps' => array(
                    'Purchase eligible water-saving device',
                    'Submit rebate application with receipt',
                    'Wait for approval',
                    'Receive rebate check',
                ),
            ),
            array(
                'id' => 'sewer-repair',
                'title' => 'Sewer Repair Financial Assistance Program',
                'description' => 'Help with the cost of fixing or replacing sewer laterals',
                'provider' => 'LASAN',
                'amount' => 'Up to $6,000',
                'eligibility' => 'Income-qualified property owners',
                'url' => 'https://www.lacitysan.org/sewer-repair',
                'category' => 'Infrastructure',
                'steps' => array(
                    'Check income eligibility',
                    'Apply online or by phone',
                    'Get sewer lateral inspected',
                    'Hire approved contractor',
                    'Receive reimbursement',
                ),
            ),
            array(
                'id' => 'solar-incentive',
                'title' => 'Solar Incentive Program',
                'description' => 'Incentives for installing solar panels on your home',
                'provider' => 'LADWP',
                'amount' => '$0.20 per watt',
                'eligibility' => 'LADWP customers',
                'url' => 'https://www.ladwp.com/solar',
                'category' => 'Energy Efficiency',
                'steps' => array(
                    'Get solar system quote',
                    'Apply for incentive reservation',
                    'Install solar system',
                    'Pass final inspection',
                    'Receive incentive payment',
                ),
            ),
        );
        
        return rest_ensure_response($rebates);
    }
    
    /**
     * Shortcode: Rebate finder
     * 
     * Usage: [la_rebate_finder]
     */
    public function rebate_finder_shortcode($atts) {
        $rebates = $this->get_la_rebates(new WP_REST_Request())->data;
        
        ob_start();
        ?>
        <div class="la-rebate-finder">
            <h3>LA City Rebate Programs</h3>
            <p>Save money while helping the environment! Explore rebate programs available to LA residents.</p>
            
            <div class="rebate-filter">
                <label for="category-filter">Filter by category:</label>
                <select id="category-filter">
                    <option value="">All Categories</option>
                    <option value="water">Water Conservation</option>
                    <option value="energy">Energy Efficiency</option>
                    <option value="infrastructure">Infrastructure</option>
                </select>
            </div>
            
            <div class="rebates-grid">
                <?php foreach ($rebates as $rebate): ?>
                <div class="rebate-card" data-category="<?php echo esc_attr(strtolower(str_replace(' ', '-', $rebate['category']))); ?>">
                    <h4><?php echo esc_html($rebate['title']); ?></h4>
                    <p class="rebate-provider"><?php echo esc_html($rebate['provider']); ?></p>
                    <p class="rebate-amount">💰 <?php echo esc_html($rebate['amount']); ?></p>
                    <p><?php echo esc_html($rebate['description']); ?></p>
                    
                    <div class="rebate-details">
                        <p><strong>Eligibility:</strong> <?php echo esc_html($rebate['eligibility']); ?></p>
                        
                        <h5>How to Apply:</h5>
                        <ol>
                            <?php foreach ($rebate['steps'] as $step): ?>
                            <li><?php echo esc_html($step); ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                    
                    <a href="<?php echo esc_url($rebate['url']); ?>" target="_blank" class="rebate-apply-btn">Learn More & Apply</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <style>
            .rebates-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .rebate-card {
                border: 1px solid #ddd;
                padding: 20px;
                border-radius: 8px;
                background: #fff;
            }
            .rebate-card h4 {
                margin-top: 0;
                color: #2c5f2d;
            }
            .rebate-provider {
                color: #666;
                font-size: 0.9em;
                margin: 5px 0;
            }
            .rebate-amount {
                font-size: 1.2em;
                font-weight: bold;
                color: #2c5f2d;
                margin: 10px 0;
            }
            .rebate-details {
                margin: 15px 0;
                padding: 15px;
                background: #f9f9f9;
                border-radius: 5px;
            }
            .rebate-apply-btn {
                display: inline-block;
                background: #2c5f2d;
                color: #fff;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 10px;
            }
            .rebate-apply-btn:hover {
                background: #1e4620;
            }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Resource directory
     * 
     * Usage: [la_resource_directory category="water"]
     */
    public function resource_directory_shortcode($atts) {
        $atts = shortcode_atts(array(
            'category' => '',
        ), $atts);
        
        $resources = array(
            array(
                'title' => 'City Plants',
                'description' => 'Free trees for LA residents! Get up to 7 free trees per year.',
                'url' => 'https://www.cityplants.org',
                'phone' => '(213) 847-3077',
                'category' => 'Urban Forestry',
            ),
            array(
                'title' => 'LADWP Free Water-Saving Devices',
                'description' => 'Free showerheads and aerators for LADWP customers',
                'url' => 'https://www.ladwp.com/free-devices',
                'phone' => '(800) 342-5397',
                'category' => 'Water Conservation',
            ),
            array(
                'title' => 'LASAN Environmental Education',
                'description' => 'Free classes on composting, recycling, and sustainable practices',
                'url' => 'https://www.lacitysan.org/education',
                'phone' => '(213) 485-2260',
                'category' => 'Education',
            ),
            array(
                'title' => 'One Water LA 2040 Plan',
                'description' => 'LA\'s comprehensive water management plan for the future',
                'url' => 'https://www.lacitysan.org/onewaterla',
                'phone' => '(213) 485-2121',
                'category' => 'Water Management',
            ),
            array(
                'title' => 'LADWP California Friendly Landscaping Classes',
                'description' => 'Free Saturday classes on water-wise landscaping',
                'url' => 'https://www.ladwp.com/classes',
                'phone' => '(800) 342-5397',
                'category' => 'Education',
            ),
        );
        
        ob_start();
        ?>
        <div class="la-resource-directory">
            <h3>LA City Environmental Resources</h3>
            
            <div class="resources-list">
                <?php foreach ($resources as $resource): ?>
                <div class="resource-item">
                    <h4><?php echo esc_html($resource['title']); ?></h4>
                    <p class="resource-category"><?php echo esc_html($resource['category']); ?></p>
                    <p><?php echo esc_html($resource['description']); ?></p>
                    
                    <div class="resource-contact">
                        <?php if (!empty($resource['phone'])): ?>
                        <p>📞 <a href="tel:<?php echo esc_attr($resource['phone']); ?>"><?php echo esc_html($resource['phone']); ?></a></p>
                        <?php endif; ?>
                        <?php if (!empty($resource['url'])): ?>
                        <p>🌐 <a href="<?php echo esc_url($resource['url']); ?>" target="_blank">Visit Website</a></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <style>
            .resources-list {
                display: flex;
                flex-direction: column;
                gap: 20px;
                margin-top: 20px;
            }
            .resource-item {
                border-left: 4px solid #2c5f2d;
                padding: 15px;
                background: #f9f9f9;
            }
            .resource-item h4 {
                margin-top: 0;
                color: #2c5f2d;
            }
            .resource-category {
                color: #666;
                font-size: 0.9em;
                font-style: italic;
                margin: 5px 0;
            }
            .resource-contact {
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px solid #ddd;
            }
            .resource-contact p {
                margin: 5px 0;
            }
            .resource-contact a {
                color: #2c5f2d;
                text-decoration: none;
            }
            .resource-contact a:hover {
                text-decoration: underline;
            }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Environmental classes
     * 
     * Usage: [la_environmental_classes]
     */
    public function environmental_classes_shortcode($atts) {
        $classes = array(
            array(
                'title' => 'California Friendly Landscaping',
                'provider' => 'LADWP',
                'schedule' => 'Every Saturday, 9am-12pm',
                'location' => 'Virtual & In-Person',
                'description' => 'Learn how to create a beautiful, water-wise garden',
                'registration' => 'https://www.ladwp.com/classes',
            ),
            array(
                'title' => 'Composting 101',
                'provider' => 'LASAN',
                'schedule' => 'Monthly workshops',
                'location' => 'Various LA locations',
                'description' => 'Turn food scraps into garden gold',
                'registration' => 'https://www.lacitysan.org/composting',
            ),
            array(
                'title' => 'Recycling & Waste Reduction',
                'provider' => 'LASAN',
                'schedule' => 'Quarterly workshops',
                'location' => 'Community centers',
                'description' => 'Learn what can and can\'t be recycled',
                'registration' => 'https://www.lacitysan.org/recycling',
            ),
        );
        
        ob_start();
        ?>
        <div class="la-environmental-classes">
            <h3>Free Environmental Classes</h3>
            <p>Learn sustainable practices from LA City experts!</p>
            
            <div class="classes-list">
                <?php foreach ($classes as $class): ?>
                <div class="class-item">
                    <h4><?php echo esc_html($class['title']); ?></h4>
                    <p class="class-provider"><?php echo esc_html($class['provider']); ?></p>
                    <p><?php echo esc_html($class['description']); ?></p>
                    
                    <div class="class-details">
                        <p><strong>Schedule:</strong> <?php echo esc_html($class['schedule']); ?></p>
                        <p><strong>Location:</strong> <?php echo esc_html($class['location']); ?></p>
                    </div>
                    
                    <a href="<?php echo esc_url($class['registration']); ?>" target="_blank" class="class-register-btn">Register Now</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <style>
            .classes-list {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .class-item {
                border: 2px solid #2c5f2d;
                padding: 20px;
                border-radius: 8px;
                background: #fff;
            }
            .class-item h4 {
                margin-top: 0;
                color: #2c5f2d;
            }
            .class-provider {
                color: #666;
                font-size: 0.9em;
                margin: 5px 0;
            }
            .class-details {
                margin: 15px 0;
                padding: 10px;
                background: #f0f8f0;
                border-radius: 5px;
            }
            .class-register-btn {
                display: inline-block;
                background: #2c5f2d;
                color: #fff;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 10px;
            }
            .class-register-btn:hover {
                background: #1e4620;
            }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Seed LA City resources data
     */
    public static function seed_resources() {
        $resources = array(
            array(
                'title' => 'LADWP Turf Replacement Program',
                'content' => 'Get money back for replacing your lawn with a California Friendly® garden that uses less water.',
                'category' => 'Water Conservation',
                'meta' => array(
                    'resource_url' => 'https://www.ladwp.com/turf-replacement',
                    'phone' => '(800) 342-5397',
                    'provider' => 'LADWP',
                    'rebate_amount' => '$3 per square foot',
                ),
            ),
            array(
                'title' => 'City Plants Free Trees',
                'content' => 'Partnership to plant and give away 15,000 trees each year to LA residents.',
                'category' => 'Urban Forestry',
                'meta' => array(
                    'resource_url' => 'https://www.cityplants.org',
                    'phone' => '(213) 847-3077',
                    'provider' => 'City Plants',
                ),
            ),
            array(
                'title' => 'LASAN Sewer Repair Financial Assistance',
                'content' => 'Rebate programs to help property owners with the cost of fixing or replacing sewer laterals.',
                'category' => 'Infrastructure',
                'meta' => array(
                    'resource_url' => 'https://www.lacitysan.org/sewer-repair',
                    'phone' => '(213) 485-2260',
                    'provider' => 'LASAN',
                    'rebate_amount' => 'Up to $6,000',
                ),
            ),
        );
        
        foreach ($resources as $resource) {
            $post_id = wp_insert_post(array(
                'post_type' => 'la_city_resource',
                'post_title' => $resource['title'],
                'post_content' => $resource['content'],
                'post_status' => 'publish',
            ));
            
            if ($post_id) {
                wp_set_object_terms($post_id, $resource['category'], 'resource_category');
                
                foreach ($resource['meta'] as $key => $value) {
                    update_post_meta($post_id, $key, $value);
                }
            }
        }
        
        return true;
    }
}

// Initialize the class
new LA_City_Resources();
