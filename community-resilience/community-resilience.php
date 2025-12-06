<?php
/**
 * Plugin Name: ThrivingRoots Community Resilience
 * Plugin URI: https://github.com/QuantumStillness/ThrivingRoots
 * Description: Community resilience tools, material safety databases, and Eaton Fire recovery resources
 * Version: 1.0.0
 * Author: ThrivingRoots
 * Author URI: https://github.com/QuantumStillness
 * License: Apache-2.0
 * License URI: https://www.apache.org/licenses/LICENSE-2.0
 * Text Domain: thriving-roots
 * Domain Path: /languages
 *
 * @package ThrivingRoots
 * @subpackage CommunityResilience
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TR_COMMUNITY_RESILIENCE_VERSION', '1.0.0');
define('TR_COMMUNITY_RESILIENCE_PATH', plugin_dir_path(__FILE__));
define('TR_COMMUNITY_RESILIENCE_URL', plugin_dir_url(__FILE__));

/**
 * Main Community Resilience Plugin Class
 */
class ThrivingRoots_Community_Resilience {
    
    /**
     * Single instance of the class
     *
     * @var ThrivingRoots_Community_Resilience
     */
    protected static $_instance = null;
    
    /**
     * Main Instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();
        $this->init_hooks();
    }
    
    /**
     * Include required files
     */
    private function includes() {
        // Core classes
        require_once TR_COMMUNITY_RESILIENCE_PATH . 'includes/class-material-safety-db.php';
        require_once TR_COMMUNITY_RESILIENCE_PATH . 'includes/class-la-city-resources.php';
        require_once TR_COMMUNITY_RESILIENCE_PATH . 'includes/class-action-tools.php';
        require_once TR_COMMUNITY_RESILIENCE_PATH . 'includes/class-eaton-fire-recovery.php';
    }
    
    /**
     * Hook into WordPress
     */
    private function init_hooks() {
        add_action('init', array($this, 'load_textdomain'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('thriving-roots', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'tr-community-resilience',
            TR_COMMUNITY_RESILIENCE_URL . 'assets/css/community-resilience.css',
            array(),
            TR_COMMUNITY_RESILIENCE_VERSION
        );
        
        wp_enqueue_script(
            'tr-community-resilience',
            TR_COMMUNITY_RESILIENCE_URL . 'assets/js/community-resilience.js',
            array('jquery'),
            TR_COMMUNITY_RESILIENCE_VERSION,
            true
        );
        
        // Localize script with REST API endpoint
        wp_localize_script('tr-community-resilience', 'trCommunityResilience', array(
            'apiUrl' => rest_url('thriving-roots/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
        ));
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'community-resilience') === false) {
            return;
        }
        
        wp_enqueue_style(
            'tr-community-resilience-admin',
            TR_COMMUNITY_RESILIENCE_URL . 'assets/css/admin.css',
            array(),
            TR_COMMUNITY_RESILIENCE_VERSION
        );
        
        wp_enqueue_script(
            'tr-community-resilience-admin',
            TR_COMMUNITY_RESILIENCE_URL . 'assets/js/admin.js',
            array('jquery'),
            TR_COMMUNITY_RESILIENCE_VERSION,
            true
        );
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Community Resilience', 'thriving-roots'),
            __('Community Resilience', 'thriving-roots'),
            'manage_options',
            'community-resilience',
            array($this, 'admin_dashboard'),
            'dashicons-heart',
            30
        );
        
        add_submenu_page(
            'community-resilience',
            __('Dashboard', 'thriving-roots'),
            __('Dashboard', 'thriving-roots'),
            'manage_options',
            'community-resilience',
            array($this, 'admin_dashboard')
        );
        
        add_submenu_page(
            'community-resilience',
            __('Material Safety', 'thriving-roots'),
            __('Material Safety', 'thriving-roots'),
            'manage_options',
            'community-resilience-material-safety',
            array($this, 'admin_material_safety')
        );
        
        add_submenu_page(
            'community-resilience',
            __('LA City Resources', 'thriving-roots'),
            __('LA City Resources', 'thriving-roots'),
            'manage_options',
            'community-resilience-la-resources',
            array($this, 'admin_la_resources')
        );
        
        add_submenu_page(
            'community-resilience',
            __('Eaton Fire Recovery', 'thriving-roots'),
            __('Eaton Fire Recovery', 'thriving-roots'),
            'manage_options',
            'community-resilience-fire-recovery',
            array($this, 'admin_fire_recovery')
        );
        
        add_submenu_page(
            'community-resilience',
            __('Settings', 'thriving-roots'),
            __('Settings', 'thriving-roots'),
            'manage_options',
            'community-resilience-settings',
            array($this, 'admin_settings')
        );
    }
    
    /**
     * Admin dashboard page
     */
    public function admin_dashboard() {
        ?>
        <div class="wrap">
            <h1><?php _e('Community Resilience Dashboard', 'thriving-roots'); ?></h1>
            
            <div class="tr-dashboard-cards">
                <div class="tr-card">
                    <h2><?php _e('Material Safety Database', 'thriving-roots'); ?></h2>
                    <p><?php _e('Water quality data, chemical safety information, and environmental monitoring.', 'thriving-roots'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=community-resilience-material-safety'); ?>" class="button button-primary"><?php _e('Manage', 'thriving-roots'); ?></a>
                </div>
                
                <div class="tr-card">
                    <h2><?php _e('LA City Resources', 'thriving-roots'); ?></h2>
                    <p><?php _e('Rebate programs, environmental classes, and city resources.', 'thriving-roots'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=community-resilience-la-resources'); ?>" class="button button-primary"><?php _e('Manage', 'thriving-roots'); ?></a>
                </div>
                
                <div class="tr-card">
                    <h2><?php _e('Eaton Fire Recovery', 'thriving-roots'); ?></h2>
                    <p><?php _e('Recovery resources, unmet needs tracking, and environmental safety.', 'thriving-roots'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=community-resilience-fire-recovery'); ?>" class="button button-primary"><?php _e('Manage', 'thriving-roots'); ?></a>
                </div>
                
                <div class="tr-card">
                    <h2><?php _e('Action Tools', 'thriving-roots'); ?></h2>
                    <p><?php _e('Sustainable living guides, action plan builders, and community resources.', 'thriving-roots'); ?></p>
                    <a href="<?php echo admin_url('edit.php?post_type=action_plan'); ?>" class="button button-primary"><?php _e('View', 'thriving-roots'); ?></a>
                </div>
            </div>
            
            <div class="tr-shortcodes">
                <h2><?php _e('Available Shortcodes', 'thriving-roots'); ?></h2>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Shortcode', 'thriving-roots'); ?></th>
                            <th><?php _e('Description', 'thriving-roots'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[water_quality_lookup]</code></td>
                            <td><?php _e('Water quality lookup by ZIP code', 'thriving-roots'); ?></td>
                        </tr>
                        <tr>
                            <td><code>[chemical_safety_info]</code></td>
                            <td><?php _e('Chemical safety information lookup', 'thriving-roots'); ?></td>
                        </tr>
                        <tr>
                            <td><code>[la_rebate_finder]</code></td>
                            <td><?php _e('LA City rebate programs directory', 'thriving-roots'); ?></td>
                        </tr>
                        <tr>
                            <td><code>[la_resource_directory]</code></td>
                            <td><?php _e('LA City environmental resources', 'thriving-roots'); ?></td>
                        </tr>
                        <tr>
                            <td><code>[la_environmental_classes]</code></td>
                            <td><?php _e('Free environmental classes', 'thriving-roots'); ?></td>
                        </tr>
                        <tr>
                            <td><code>[action_plan_builder]</code></td>
                            <td><?php _e('Personal action plan builder', 'thriving-roots'); ?></td>
                        </tr>
                        <tr>
                            <td><code>[sustainable_living_guide]</code></td>
                            <td><?php _e('Sustainable living guide', 'thriving-roots'); ?></td>
                        </tr>
                        <tr>
                            <td><code>[mindful_eating_resources]</code></td>
                            <td><?php _e('Mindful eating and food safety resources', 'thriving-roots'); ?></td>
                        </tr>
                        <tr>
                            <td><code>[community_garden_finder]</code></td>
                            <td><?php _e('Community garden finder', 'thriving-roots'); ?></td>
                        </tr>
                        <tr>
                            <td><code>[eaton_fire_resources]</code></td>
                            <td><?php _e('Eaton Fire recovery resources', 'thriving-roots'); ?></td>
                        </tr>
                        <tr>
                            <td><code>[altadena_unmet_needs]</code></td>
                            <td><?php _e('Altadena unmet needs tracker', 'thriving-roots'); ?></td>
                        </tr>
                        <tr>
                            <td><code>[environmental_safety_post_fire]</code></td>
                            <td><?php _e('Environmental safety after wildfire', 'thriving-roots'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <style>
            .tr-dashboard-cards {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
                margin: 30px 0;
            }
            .tr-card {
                background: #fff;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .tr-card h2 {
                margin-top: 0;
                color: #2c5f2d;
            }
            .tr-shortcodes {
                margin-top: 40px;
            }
            .tr-shortcodes code {
                background: #f0f0f0;
                padding: 2px 6px;
                border-radius: 3px;
            }
        </style>
        <?php
    }
    
    /**
     * Material Safety admin page
     */
    public function admin_material_safety() {
        ?>
        <div class="wrap">
            <h1><?php _e('Material Safety Database', 'thriving-roots'); ?></h1>
            <p><?php _e('Manage water quality data and chemical safety information.', 'thriving-roots'); ?></p>
            
            <h2><?php _e('Import Water Quality Data', 'thriving-roots'); ?></h2>
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('import_water_quality', 'water_quality_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><?php _e('CSV File', 'thriving-roots'); ?></th>
                        <td>
                            <input type="file" name="water_quality_csv" accept=".csv" required>
                            <p class="description"><?php _e('Upload a CSV file with water quality data', 'thriving-roots'); ?></p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="import_water_quality" class="button button-primary" value="<?php _e('Import Data', 'thriving-roots'); ?>">
                </p>
            </form>
            
            <h2><?php _e('Recent Water Quality Entries', 'thriving-roots'); ?></h2>
            <?php
            $query = new WP_Query(array(
                'post_type' => 'water_quality_data',
                'posts_per_page' => 10,
                'orderby' => 'date',
                'order' => 'DESC',
            ));
            
            if ($query->have_posts()) {
                echo '<table class="widefat">';
                echo '<thead><tr><th>Utility</th><th>ZIP Code</th><th>Quality</th><th>Date</th></tr></thead>';
                echo '<tbody>';
                while ($query->have_posts()) {
                    $query->the_post();
                    echo '<tr>';
                    echo '<td>' . get_the_title() . '</td>';
                    echo '<td>' . get_post_meta(get_the_ID(), 'zip_code', true) . '</td>';
                    echo '<td>' . get_post_meta(get_the_ID(), 'overall_quality', true) . '</td>';
                    echo '<td>' . get_the_date() . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
                wp_reset_postdata();
            } else {
                echo '<p>' . __('No water quality data found.', 'thriving-roots') . '</p>';
            }
            ?>
        </div>
        <?php
    }
    
    /**
     * LA Resources admin page
     */
    public function admin_la_resources() {
        ?>
        <div class="wrap">
            <h1><?php _e('LA City Resources', 'thriving-roots'); ?></h1>
            <p><?php _e('Manage LA City environmental programs and resources.', 'thriving-roots'); ?></p>
            
            <a href="<?php echo admin_url('post-new.php?post_type=la_city_resource'); ?>" class="button button-primary"><?php _e('Add New Resource', 'thriving-roots'); ?></a>
            
            <h2><?php _e('Recent Resources', 'thriving-roots'); ?></h2>
            <?php
            $query = new WP_Query(array(
                'post_type' => 'la_city_resource',
                'posts_per_page' => 10,
            ));
            
            if ($query->have_posts()) {
                echo '<table class="widefat">';
                echo '<thead><tr><th>Title</th><th>Category</th><th>Provider</th><th>Actions</th></tr></thead>';
                echo '<tbody>';
                while ($query->have_posts()) {
                    $query->the_post();
                    $categories = wp_get_post_terms(get_the_ID(), 'resource_category');
                    $category_names = !empty($categories) ? implode(', ', wp_list_pluck($categories, 'name')) : 'N/A';
                    
                    echo '<tr>';
                    echo '<td>' . get_the_title() . '</td>';
                    echo '<td>' . $category_names . '</td>';
                    echo '<td>' . get_post_meta(get_the_ID(), 'provider', true) . '</td>';
                    echo '<td><a href="' . get_edit_post_link() . '">Edit</a></td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
                wp_reset_postdata();
            } else {
                echo '<p>' . __('No resources found.', 'thriving-roots') . '</p>';
            }
            ?>
        </div>
        <?php
    }
    
    /**
     * Fire Recovery admin page
     */
    public function admin_fire_recovery() {
        ?>
        <div class="wrap">
            <h1><?php _e('Eaton Fire Recovery', 'thriving-roots'); ?></h1>
            <p><?php _e('Manage fire recovery resources and track unmet needs.', 'thriving-roots'); ?></p>
            
            <a href="<?php echo admin_url('post-new.php?post_type=fire_recovery_resource'); ?>" class="button button-primary"><?php _e('Add New Resource', 'thriving-roots'); ?></a>
            
            <h2><?php _e('Unmet Needs Summary', 'thriving-roots'); ?></h2>
            <div class="tr-needs-summary">
                <div class="tr-stat-card">
                    <h3>54</h3>
                    <p>Total Needs</p>
                </div>
                <div class="tr-stat-card">
                    <h3>8</h3>
                    <p>Categories</p>
                </div>
                <div class="tr-stat-card">
                    <h3>42+</h3>
                    <p>Organizations</p>
                </div>
            </div>
            
            <h2><?php _e('Recent Recovery Resources', 'thriving-roots'); ?></h2>
            <?php
            $query = new WP_Query(array(
                'post_type' => 'fire_recovery_resource',
                'posts_per_page' => 10,
            ));
            
            if ($query->have_posts()) {
                echo '<table class="widefat">';
                echo '<thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Actions</th></tr></thead>';
                echo '<tbody>';
                while ($query->have_posts()) {
                    $query->the_post();
                    $categories = wp_get_post_terms(get_the_ID(), 'need_category');
                    $statuses = wp_get_post_terms(get_the_ID(), 'resource_status');
                    
                    $category_names = !empty($categories) ? implode(', ', wp_list_pluck($categories, 'name')) : 'N/A';
                    $status_names = !empty($statuses) ? implode(', ', wp_list_pluck($statuses, 'name')) : 'N/A';
                    
                    echo '<tr>';
                    echo '<td>' . get_the_title() . '</td>';
                    echo '<td>' . $category_names . '</td>';
                    echo '<td>' . $status_names . '</td>';
                    echo '<td><a href="' . get_edit_post_link() . '">Edit</a></td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
                wp_reset_postdata();
            } else {
                echo '<p>' . __('No recovery resources found.', 'thriving-roots') . '</p>';
            }
            ?>
        </div>
        
        <style>
            .tr-needs-summary {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
                margin: 20px 0;
            }
            .tr-stat-card {
                background: #2c5f2d;
                color: #fff;
                padding: 30px;
                text-align: center;
                border-radius: 8px;
            }
            .tr-stat-card h3 {
                font-size: 3em;
                margin: 0;
            }
            .tr-stat-card p {
                margin: 10px 0 0 0;
                font-size: 1.2em;
            }
        </style>
        <?php
    }
    
    /**
     * Settings admin page
     */
    public function admin_settings() {
        ?>
        <div class="wrap">
            <h1><?php _e('Community Resilience Settings', 'thriving-roots'); ?></h1>
            <p><?php _e('Configure plugin settings and integrations.', 'thriving-roots'); ?></p>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('tr_community_resilience_settings');
                do_settings_sections('tr_community_resilience_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th><?php _e('EPA AirNow API Key', 'thriving-roots'); ?></th>
                        <td>
                            <input type="text" name="tr_airnow_api_key" value="<?php echo esc_attr(get_option('tr_airnow_api_key')); ?>" class="regular-text">
                            <p class="description"><?php _e('Optional: Enter your EPA AirNow API key for real-time air quality data', 'thriving-roots'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Contact Email', 'thriving-roots'); ?></th>
                        <td>
                            <input type="email" name="tr_contact_email" value="<?php echo esc_attr(get_option('tr_contact_email')); ?>" class="regular-text">
                            <p class="description"><?php _e('Email address for community inquiries', 'thriving-roots'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Enable Eaton Fire Resources', 'thriving-roots'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="tr_enable_fire_resources" value="1" <?php checked(get_option('tr_enable_fire_resources'), 1); ?>>
                                <?php _e('Show Eaton Fire recovery resources', 'thriving-roots'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Seed initial data
        LA_City_Resources::seed_resources();
        Eaton_Fire_Recovery::seed_fire_recovery_resources();
        
        // Set default options
        add_option('tr_enable_fire_resources', 1);
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

/**
 * Returns the main instance of ThrivingRoots_Community_Resilience
 */
function TR_Community_Resilience() {
    return ThrivingRoots_Community_Resilience::instance();
}

// Initialize the plugin
TR_Community_Resilience();
