<?php
/**
 * Plugin Name: Environmental Intelligence Core
 * Plugin URI: https://github.com/QuantumStillness/ThrivingRoots
 * Description: Community Environmental Intelligence Platform - Database and data-acquisition system for environmental remediation and sustainable development in California.
 * Version: 1.0.0
 * Author: ThrivingRoots
 * Author URI: https://github.com/QuantumStillness
 * License: Apache-2.0
 * License URI: http://www.apache.org/licenses/LICENSE-2.0
 * Text Domain: env-intel-core
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package EnvironmentalIntelligenceCore
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'EIC_VERSION', '1.0.0' );
define( 'EIC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EIC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'EIC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main Environmental Intelligence Core Class
 *
 * @class EIC_Core
 * @version 1.0.0
 */
final class EIC_Core {

    /**
     * The single instance of the class
     *
     * @var EIC_Core
     */
    protected static $_instance = null;

    /**
     * Main EIC_Core Instance
     *
     * Ensures only one instance of EIC_Core is loaded or can be loaded.
     *
     * @static
     * @return EIC_Core - Main instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * EIC_Core Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->includes();
    }

    /**
     * Hook into actions and filters
     */
    private function init_hooks() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
        
        add_action( 'init', array( $this, 'init' ), 0 );
        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
    }

    /**
     * Include required core files
     */
    public function includes() {
        // Core includes
        require_once EIC_PLUGIN_DIR . 'includes/class-eic-database.php';
        require_once EIC_PLUGIN_DIR . 'includes/class-eic-post-types.php';
        require_once EIC_PLUGIN_DIR . 'includes/class-eic-taxonomies.php';
        require_once EIC_PLUGIN_DIR . 'includes/class-eic-woocommerce.php';
        
        // Admin includes
        if ( is_admin() ) {
            require_once EIC_PLUGIN_DIR . 'admin/class-eic-admin.php';
        }
        
        // CLI includes
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            require_once EIC_PLUGIN_DIR . 'cli/class-eic-scraper-command.php';
        }
    }

    /**
     * Init EIC_Core when WordPress Initialises
     */
    public function init() {
        // Before init action
        do_action( 'before_eic_init' );

        // Set up localisation
        $this->load_plugin_textdomain();

        // Initialize components
        EIC_Post_Types::init();
        EIC_Taxonomies::init();
        
        if ( class_exists( 'WooCommerce' ) ) {
            EIC_WooCommerce::init();
        }

        // Init action
        do_action( 'eic_init' );
    }

    /**
     * Load Localisation files
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain( 'env-intel-core', false, dirname( EIC_PLUGIN_BASENAME ) . '/languages' );
    }

    /**
     * Plugins loaded hook
     */
    public function plugins_loaded() {
        do_action( 'eic_loaded' );
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        EIC_Database::create_tables();
        
        // Register post types and taxonomies for rewrite rules
        EIC_Post_Types::register_post_types();
        EIC_Taxonomies::register_taxonomies();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set default options
        add_option( 'eic_version', EIC_VERSION );
        add_option( 'eic_activation_date', current_time( 'mysql' ) );
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Get the plugin url
     *
     * @return string
     */
    public function plugin_url() {
        return untrailingslashit( plugins_url( '/', __FILE__ ) );
    }

    /**
     * Get the plugin path
     *
     * @return string
     */
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }
}

/**
 * Main instance of EIC_Core
 *
 * Returns the main instance of EIC_Core to prevent the need to use globals.
 *
 * @return EIC_Core
 */
function EIC() {
    return EIC_Core::instance();
}

// Initialize the plugin
EIC();
