<?php
/**
 * WP-CLI Scraper Command
 *
 * Provides WP-CLI commands for running environmental data scrapers.
 * Can be triggered manually or via WP-Cron.
 *
 * Usage:
 *   wp env-scraper run [source]
 *   wp env-scraper list
 *   wp env-scraper test [source]
 *
 * @package EnvironmentalIntelligenceCore
 * @subpackage CLI
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Environmental data scraper commands
 */
class EIC_Scraper_Command extends WP_CLI_Command {

    /**
     * List all configured scraper jobs
     *
     * ## EXAMPLES
     *
     *     wp env-scraper list
     *
     * @when after_wp_load
     */
    public function list( $args, $assoc_args ) {
        
        $jobs = EIC_Database::get_active_jobs();
        
        if ( empty( $jobs ) ) {
            WP_CLI::warning( 'No active scraper jobs found.' );
            return;
        }

        $items = array();
        foreach ( $jobs as $job ) {
            $items[] = array(
                'ID' => $job->job_id,
                'Source' => $job->source_name,
                'Type' => $job->source_type,
                'Frequency' => $job->run_frequency,
                'Last Run' => $job->last_run ? $job->last_run : 'Never',
                'Active' => $job->is_active ? 'Yes' : 'No',
            );
        }

        WP_CLI\Utils\format_items( 'table', $items, array( 'ID', 'Source', 'Type', 'Frequency', 'Last Run', 'Active' ) );
    }

    /**
     * Run a scraper job
     *
     * ## OPTIONS
     *
     * [<source>]
     * : The source name to scrape (e.g., EPA_SEMS, CalEPA_EnviroStor)
     *
     * [--all]
     * : Run all active scraper jobs
     *
     * [--dry-run]
     * : Test the scraper without saving data
     *
     * ## EXAMPLES
     *
     *     wp env-scraper run EPA_SEMS
     *     wp env-scraper run --all
     *     wp env-scraper run EPA_SEMS --dry-run
     *
     * @when after_wp_load
     */
    public function run( $args, $assoc_args ) {
        
        $dry_run = isset( $assoc_args['dry-run'] );
        $run_all = isset( $assoc_args['all'] );

        if ( $run_all ) {
            $jobs = EIC_Database::get_active_jobs();
            
            if ( empty( $jobs ) ) {
                WP_CLI::error( 'No active scraper jobs found.' );
            }

            foreach ( $jobs as $job ) {
                $this->run_single_job( $job, $dry_run );
            }
            
            WP_CLI::success( sprintf( 'Completed %d scraper job(s).', count( $jobs ) ) );
            
        } else {
            
            if ( empty( $args[0] ) ) {
                WP_CLI::error( 'Please specify a source name or use --all flag.' );
            }

            $source_name = $args[0];
            $job = $this->get_job_by_name( $source_name );
            
            if ( ! $job ) {
                WP_CLI::error( sprintf( 'Scraper job "%s" not found.', $source_name ) );
            }

            $this->run_single_job( $job, $dry_run );
            WP_CLI::success( sprintf( 'Completed scraper job: %s', $source_name ) );
        }
    }

    /**
     * Test a scraper configuration
     *
     * ## OPTIONS
     *
     * <source>
     * : The source name to test
     *
     * ## EXAMPLES
     *
     *     wp env-scraper test EPA_SEMS
     *
     * @when after_wp_load
     */
    public function test( $args, $assoc_args ) {
        
        if ( empty( $args[0] ) ) {
            WP_CLI::error( 'Please specify a source name.' );
        }

        $source_name = $args[0];
        $job = $this->get_job_by_name( $source_name );
        
        if ( ! $job ) {
            WP_CLI::error( sprintf( 'Scraper job "%s" not found.', $source_name ) );
        }

        WP_CLI::line( sprintf( 'Testing scraper: %s', $job->source_name ) );
        WP_CLI::line( sprintf( 'Base URL: %s', $job->base_url ) );
        WP_CLI::line( sprintf( 'Type: %s', $job->source_type ) );
        WP_CLI::line( '' );

        // Test HTTP connection
        WP_CLI::line( 'Testing HTTP connection...' );
        $response = wp_remote_get( $job->base_url, array(
            'timeout' => $job->timeout,
            'user-agent' => $job->user_agent,
        ) );

        if ( is_wp_error( $response ) ) {
            WP_CLI::error( sprintf( 'Connection failed: %s', $response->get_error_message() ) );
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        
        WP_CLI::success( sprintf( 'HTTP %d - Response received (%d bytes)', $response_code, strlen( $body ) ) );
        
        // Parse configuration
        $config = json_decode( $job->config, true );
        
        if ( ! empty( $config['selectors'] ) ) {
            WP_CLI::line( '' );
            WP_CLI::line( 'Configured selectors:' );
            foreach ( $config['selectors'] as $key => $selector ) {
                WP_CLI::line( sprintf( '  - %s: %s', $key, $selector ) );
            }
        }

        WP_CLI::success( 'Scraper configuration test completed.' );
    }

    /**
     * Run a single scraper job
     *
     * @param object $job Job configuration
     * @param bool $dry_run Whether to run in dry-run mode
     */
    private function run_single_job( $job, $dry_run = false ) {
        
        $start_time = microtime( true );
        
        WP_CLI::line( sprintf( 'Running scraper: %s', $job->source_name ) );
        
        if ( $dry_run ) {
            WP_CLI::line( '(DRY RUN MODE - No data will be saved)' );
        }

        // Parse configuration
        $config = json_decode( $job->config, true );
        
        // Fetch data
        $response = wp_remote_get( $job->base_url, array(
            'timeout' => $job->timeout,
            'user-agent' => $job->user_agent,
        ) );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            
            WP_CLI::error( sprintf( 'Failed to fetch data: %s', $error_message ) );
            
            EIC_Database::log_scraper_operation( array(
                'scraper_job_id' => $job->job_id,
                'source_url' => $job->base_url,
                'data_type' => 'superfund_site',
                'status' => 'error',
                'error_message' => $error_message,
                'execution_time' => microtime( true ) - $start_time,
            ) );
            
            return;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $source_hash = hash( 'sha256', $body );
        
        WP_CLI::line( sprintf( 'Fetched data: HTTP %d (%d bytes)', $response_code, strlen( $body ) ) );

        // Parse HTML
        $records_processed = 0;
        $records_created = 0;
        $records_updated = 0;

        if ( $job->source_type === 'html' ) {
            
            // Use DOMDocument to parse HTML
            libxml_use_internal_errors( true );
            $dom = new DOMDocument();
            $dom->loadHTML( $body );
            libxml_clear_errors();
            
            $xpath = new DOMXPath( $dom );
            
            // Example: Extract site data (this is a simplified example)
            // In production, this would use the configured selectors
            
            WP_CLI::line( 'Parsing HTML content...' );
            
            // For demonstration, we'll create a sample site
            if ( ! $dry_run ) {
                $sample_data = array(
                    'post_title' => sprintf( 'Sample Site from %s', $job->source_name ),
                    'post_content' => 'This is a sample environmental site created by the scraper.',
                    'post_status' => get_option( 'eic_require_manual_review' ) ? 'draft' : 'publish',
                    'post_type' => 'superfund_site',
                );
                
                $post_id = wp_insert_post( $sample_data );
                
                if ( $post_id ) {
                    update_post_meta( $post_id, '_eic_epa_id', 'SAMPLE-' . time() );
                    update_post_meta( $post_id, '_eic_source', $job->source_name );
                    update_post_meta( $post_id, '_eic_source_hash', $source_hash );
                    
                    $records_created++;
                    WP_CLI::line( sprintf( 'Created site: %s (ID: %d)', $sample_data['post_title'], $post_id ) );
                }
            }
            
            $records_processed = 1;
        }

        // Calculate execution time
        $execution_time = microtime( true ) - $start_time;

        // Log operation
        if ( ! $dry_run ) {
            EIC_Database::log_scraper_operation( array(
                'scraper_job_id' => $job->job_id,
                'source_url' => $job->base_url,
                'data_type' => 'superfund_site',
                'status' => 'success',
                'records_processed' => $records_processed,
                'records_created' => $records_created,
                'records_updated' => $records_updated,
                'source_hash' => $source_hash,
                'response_code' => $response_code,
                'execution_time' => $execution_time,
            ) );

            // Update job last run time
            global $wpdb;
            $table_scraper_jobs = $wpdb->prefix . 'env_scraper_jobs';
            $wpdb->update(
                $table_scraper_jobs,
                array( 'last_run' => current_time( 'mysql' ) ),
                array( 'job_id' => $job->job_id ),
                array( '%s' ),
                array( '%d' )
            );
        }

        WP_CLI::line( sprintf( 'Processed: %d records | Created: %d | Updated: %d', $records_processed, $records_created, $records_updated ) );
        WP_CLI::line( sprintf( 'Execution time: %.2f seconds', $execution_time ) );
    }

    /**
     * Get job by source name
     *
     * @param string $source_name Source name
     * @return object|null Job object or null
     */
    private function get_job_by_name( $source_name ) {
        global $wpdb;
        
        $table_scraper_jobs = $wpdb->prefix . 'env_scraper_jobs';
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_scraper_jobs WHERE source_name = %s",
                $source_name
            )
        );
    }
}

// Register WP-CLI command
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    WP_CLI::add_command( 'env-scraper', 'EIC_Scraper_Command' );
}
