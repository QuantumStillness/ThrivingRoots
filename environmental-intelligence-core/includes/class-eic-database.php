<?php
/**
 * Database Schema Management
 *
 * Handles creation and management of custom database tables for the Environmental Intelligence Core.
 * 
 * This class implements the hybrid data model:
 * - Custom Post Types for primary content (managed by WordPress core)
 * - Custom tables for complex relational data and logging
 *
 * @package EnvironmentalIntelligenceCore
 * @subpackage Database
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * EIC_Database Class
 */
class EIC_Database {

    /**
     * Create all custom database tables
     *
     * This method creates three custom tables:
     * 1. wp_env_site_relationships - For complex site-to-site relationships
     * 2. wp_env_data_log - For scraper operation logging and audit trail
     * 3. wp_env_scraper_jobs - For scraper job configuration and scheduling
     *
     * @global wpdb $wpdb WordPress database abstraction object
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        // Table 1: Site Relationships
        // Purpose: Store complex relationships between superfund sites
        // Use cases: Upstream/downstream contamination, shared aquifers, regional clusters
        $table_site_relationships = $wpdb->prefix . 'env_site_relationships';
        $sql_relationships = "CREATE TABLE IF NOT EXISTS $table_site_relationships (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            site_id bigint(20) UNSIGNED NOT NULL,
            related_site_id bigint(20) UNSIGNED NOT NULL,
            relationship_type varchar(50) NOT NULL,
            confidence_level enum('high','medium','low') DEFAULT 'medium',
            evidence_source text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY site_id (site_id),
            KEY related_site_id (related_site_id),
            KEY relationship_type (relationship_type),
            UNIQUE KEY unique_relationship (site_id, related_site_id, relationship_type)
        ) $charset_collate;";

        // Table 2: Data Log
        // Purpose: Comprehensive logging of all scraper operations
        // Use cases: Audit trail, error tracking, data provenance, compliance verification
        $table_data_log = $wpdb->prefix . 'env_data_log';
        $sql_data_log = "CREATE TABLE IF NOT EXISTS $table_data_log (
            log_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            scraper_job_id bigint(20) UNSIGNED NOT NULL,
            source_url varchar(500) NOT NULL,
            data_type varchar(100) NOT NULL,
            fetch_timestamp datetime NOT NULL,
            status enum('success','error','partial','skipped') NOT NULL,
            records_processed int(11) DEFAULT 0,
            records_created int(11) DEFAULT 0,
            records_updated int(11) DEFAULT 0,
            error_message text,
            source_hash varchar(64),
            response_code int(11),
            execution_time float,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (log_id),
            KEY scraper_job_id (scraper_job_id),
            KEY fetch_timestamp (fetch_timestamp),
            KEY status (status),
            KEY data_type (data_type),
            KEY source_hash (source_hash)
        ) $charset_collate;";

        // Table 3: Scraper Jobs
        // Purpose: Configuration and scheduling for different data sources
        // Use cases: Multi-source scraping, job scheduling, configuration management
        $table_scraper_jobs = $wpdb->prefix . 'env_scraper_jobs';
        $sql_scraper_jobs = "CREATE TABLE IF NOT EXISTS $table_scraper_jobs (
            job_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            source_name varchar(100) NOT NULL,
            source_type varchar(50) NOT NULL,
            base_url varchar(500) NOT NULL,
            last_run datetime,
            next_run datetime,
            is_active tinyint(1) DEFAULT 1,
            run_frequency varchar(50) DEFAULT 'daily',
            config longtext,
            user_agent varchar(255),
            rate_limit_delay int(11) DEFAULT 2,
            max_retries int(11) DEFAULT 3,
            timeout int(11) DEFAULT 30,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (job_id),
            UNIQUE KEY source_name (source_name),
            KEY is_active (is_active),
            KEY next_run (next_run),
            KEY source_type (source_type)
        ) $charset_collate;";

        // Execute table creation
        dbDelta( $sql_relationships );
        dbDelta( $sql_data_log );
        dbDelta( $sql_scraper_jobs );

        // Insert default scraper jobs
        self::insert_default_scraper_jobs();

        // Store database version
        update_option( 'eic_db_version', '1.0.0' );
    }

    /**
     * Insert default scraper job configurations
     *
     * Configures initial scraper jobs for EPA SEMS and CalEPA EnviroStor
     */
    private static function insert_default_scraper_jobs() {
        global $wpdb;
        
        $table_scraper_jobs = $wpdb->prefix . 'env_scraper_jobs';

        // Check if jobs already exist
        $existing_jobs = $wpdb->get_var( "SELECT COUNT(*) FROM $table_scraper_jobs" );
        
        if ( $existing_jobs > 0 ) {
            return; // Jobs already configured
        }

        // EPA SEMS (Superfund Enterprise Management System)
        $epa_config = array(
            'selectors' => array(
                'site_name' => '.site-name',
                'epa_id' => '.epa-id',
                'status' => '.site-status',
                'address' => '.site-address',
                'coordinates' => '.coordinates'
            ),
            'pagination' => array(
                'type' => 'numbered',
                'selector' => '.pagination a.next',
                'max_pages' => 100
            ),
            'data_mapping' => array(
                'post_type' => 'superfund_site',
                'meta_fields' => array(
                    'epa_id' => 'epa_id',
                    'npl_status' => 'status',
                    'latitude' => 'lat',
                    'longitude' => 'lng'
                )
            )
        );

        $wpdb->insert(
            $table_scraper_jobs,
            array(
                'source_name' => 'EPA_SEMS',
                'source_type' => 'html',
                'base_url' => 'https://cumulis.epa.gov/supercpad/cursites/srchsites.cfm',
                'is_active' => 1,
                'run_frequency' => 'weekly',
                'config' => wp_json_encode( $epa_config ),
                'user_agent' => 'Mozilla/5.0 (compatible; EnvironmentalIntelligenceBot/1.0; +https://thrivingroots.org)',
                'rate_limit_delay' => 3,
                'max_retries' => 3,
                'timeout' => 30
            ),
            array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d' )
        );

        // CalEPA EnviroStor
        $calepa_config = array(
            'selectors' => array(
                'site_name' => 'h2.site-title',
                'site_id' => '.site-id',
                'status' => '.cleanup-status',
                'contaminants' => '.contaminant-list li'
            ),
            'pagination' => array(
                'type' => 'load_more',
                'selector' => 'button.load-more',
                'max_iterations' => 50
            ),
            'data_mapping' => array(
                'post_type' => 'superfund_site',
                'taxonomies' => array(
                    'contaminant' => 'contaminants'
                )
            )
        );

        $wpdb->insert(
            $table_scraper_jobs,
            array(
                'source_name' => 'CalEPA_EnviroStor',
                'source_type' => 'html',
                'base_url' => 'https://www.envirostor.dtsc.ca.gov/public/',
                'is_active' => 1,
                'run_frequency' => 'weekly',
                'config' => wp_json_encode( $calepa_config ),
                'user_agent' => 'Mozilla/5.0 (compatible; EnvironmentalIntelligenceBot/1.0; +https://thrivingroots.org)',
                'rate_limit_delay' => 2,
                'max_retries' => 3,
                'timeout' => 30
            ),
            array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d' )
        );
    }

    /**
     * Get all active scraper jobs
     *
     * @return array Array of scraper job objects
     */
    public static function get_active_jobs() {
        global $wpdb;
        
        $table_scraper_jobs = $wpdb->prefix . 'env_scraper_jobs';
        
        return $wpdb->get_results(
            "SELECT * FROM $table_scraper_jobs WHERE is_active = 1 ORDER BY job_id ASC"
        );
    }

    /**
     * Log scraper operation
     *
     * @param array $log_data Log entry data
     * @return int|false The number of rows inserted, or false on error
     */
    public static function log_scraper_operation( $log_data ) {
        global $wpdb;
        
        $table_data_log = $wpdb->prefix . 'env_data_log';
        
        $defaults = array(
            'scraper_job_id' => 0,
            'source_url' => '',
            'data_type' => 'unknown',
            'fetch_timestamp' => current_time( 'mysql' ),
            'status' => 'error',
            'records_processed' => 0,
            'records_created' => 0,
            'records_updated' => 0,
            'error_message' => null,
            'source_hash' => null,
            'response_code' => null,
            'execution_time' => null
        );
        
        $log_data = wp_parse_args( $log_data, $defaults );
        
        return $wpdb->insert(
            $table_data_log,
            $log_data,
            array( '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%d', '%f' )
        );
    }

    /**
     * Add site relationship
     *
     * @param int $site_id Primary site post ID
     * @param int $related_site_id Related site post ID
     * @param string $relationship_type Type of relationship
     * @param array $args Additional arguments
     * @return int|false The number of rows inserted, or false on error
     */
    public static function add_site_relationship( $site_id, $related_site_id, $relationship_type, $args = array() ) {
        global $wpdb;
        
        $table_relationships = $wpdb->prefix . 'env_site_relationships';
        
        $defaults = array(
            'confidence_level' => 'medium',
            'evidence_source' => null
        );
        
        $args = wp_parse_args( $args, $defaults );
        
        return $wpdb->insert(
            $table_relationships,
            array(
                'site_id' => $site_id,
                'related_site_id' => $related_site_id,
                'relationship_type' => $relationship_type,
                'confidence_level' => $args['confidence_level'],
                'evidence_source' => $args['evidence_source']
            ),
            array( '%d', '%d', '%s', '%s', '%s' )
        );
    }

    /**
     * Get site relationships
     *
     * @param int $site_id Site post ID
     * @param string $relationship_type Optional. Filter by relationship type
     * @return array Array of relationship objects
     */
    public static function get_site_relationships( $site_id, $relationship_type = '' ) {
        global $wpdb;
        
        $table_relationships = $wpdb->prefix . 'env_site_relationships';
        
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_relationships WHERE site_id = %d OR related_site_id = %d",
            $site_id,
            $site_id
        );
        
        if ( ! empty( $relationship_type ) ) {
            $sql .= $wpdb->prepare( " AND relationship_type = %s", $relationship_type );
        }
        
        return $wpdb->get_results( $sql );
    }
}
