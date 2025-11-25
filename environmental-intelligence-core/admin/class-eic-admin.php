<?php
/**
 * Admin Interface
 *
 * Provides admin UI for monitoring scraper operations and managing data.
 *
 * @package EnvironmentalIntelligenceCore
 * @subpackage Admin
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * EIC_Admin Class
 */
class EIC_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        
        // Main settings page
        add_submenu_page(
            'edit.php?post_type=superfund_site',
            __( 'Scraper Monitor', 'env-intel-core' ),
            __( 'Scraper Monitor', 'env-intel-core' ),
            'manage_options',
            'eic-scraper-monitor',
            array( $this, 'render_scraper_monitor_page' )
        );

        // Scraper jobs page
        add_submenu_page(
            'edit.php?post_type=superfund_site',
            __( 'Scraper Jobs', 'env-intel-core' ),
            __( 'Scraper Jobs', 'env-intel-core' ),
            'manage_options',
            'eic-scraper-jobs',
            array( $this, 'render_scraper_jobs_page' )
        );

        // Settings page
        add_submenu_page(
            'edit.php?post_type=superfund_site',
            __( 'Settings', 'env-intel-core' ),
            __( 'Settings', 'env-intel-core' ),
            'manage_options',
            'eic-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets( $hook ) {
        
        // Only load on our admin pages
        if ( strpos( $hook, 'eic-' ) === false && strpos( $hook, 'superfund_site' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'eic-admin',
            EIC_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            EIC_VERSION
        );

        wp_enqueue_script(
            'eic-admin',
            EIC_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            EIC_VERSION,
            true
        );
    }

    /**
     * Render scraper monitor page
     */
    public function render_scraper_monitor_page() {
        global $wpdb;
        
        $table_data_log = $wpdb->prefix . 'env_data_log';
        
        // Get recent logs
        $logs = $wpdb->get_results(
            "SELECT * FROM $table_data_log ORDER BY fetch_timestamp DESC LIMIT 50"
        );

        // Get statistics
        $total_logs = $wpdb->get_var( "SELECT COUNT(*) FROM $table_data_log" );
        $success_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_data_log WHERE status = 'success'" );
        $error_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_data_log WHERE status = 'error'" );
        $success_rate = $total_logs > 0 ? round( ( $success_count / $total_logs ) * 100, 2 ) : 0;
        
        ?>
        <div class="wrap">
            <h1><?php _e( 'Scraper Monitor', 'env-intel-core' ); ?></h1>
            
            <div class="eic-stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 20px 0;">
                <div class="eic-stat-card" style="background: white; padding: 20px; border-left: 4px solid #2271b1;">
                    <h3 style="margin: 0 0 10px 0; color: #666;"><?php _e( 'Total Operations', 'env-intel-core' ); ?></h3>
                    <p style="font-size: 32px; margin: 0; font-weight: bold;"><?php echo number_format( $total_logs ); ?></p>
                </div>
                <div class="eic-stat-card" style="background: white; padding: 20px; border-left: 4px solid #00a32a;">
                    <h3 style="margin: 0 0 10px 0; color: #666;"><?php _e( 'Successful', 'env-intel-core' ); ?></h3>
                    <p style="font-size: 32px; margin: 0; font-weight: bold;"><?php echo number_format( $success_count ); ?></p>
                </div>
                <div class="eic-stat-card" style="background: white; padding: 20px; border-left: 4px solid #d63638;">
                    <h3 style="margin: 0 0 10px 0; color: #666;"><?php _e( 'Errors', 'env-intel-core' ); ?></h3>
                    <p style="font-size: 32px; margin: 0; font-weight: bold;"><?php echo number_format( $error_count ); ?></p>
                </div>
                <div class="eic-stat-card" style="background: white; padding: 20px; border-left: 4px solid #2271b1;">
                    <h3 style="margin: 0 0 10px 0; color: #666;"><?php _e( 'Success Rate', 'env-intel-core' ); ?></h3>
                    <p style="font-size: 32px; margin: 0; font-weight: bold;"><?php echo $success_rate; ?>%</p>
                </div>
            </div>

            <h2><?php _e( 'Recent Operations', 'env-intel-core' ); ?></h2>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e( 'ID', 'env-intel-core' ); ?></th>
                        <th><?php _e( 'Job ID', 'env-intel-core' ); ?></th>
                        <th><?php _e( 'Source URL', 'env-intel-core' ); ?></th>
                        <th><?php _e( 'Data Type', 'env-intel-core' ); ?></th>
                        <th><?php _e( 'Timestamp', 'env-intel-core' ); ?></th>
                        <th><?php _e( 'Status', 'env-intel-core' ); ?></th>
                        <th><?php _e( 'Records', 'env-intel-core' ); ?></th>
                        <th><?php _e( 'Execution Time', 'env-intel-core' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $logs ) ) : ?>
                        <tr>
                            <td colspan="8"><?php _e( 'No scraper operations logged yet.', 'env-intel-core' ); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $logs as $log ) : ?>
                            <tr>
                                <td><?php echo esc_html( $log->log_id ); ?></td>
                                <td><?php echo esc_html( $log->scraper_job_id ); ?></td>
                                <td><a href="<?php echo esc_url( $log->source_url ); ?>" target="_blank" title="<?php echo esc_attr( $log->source_url ); ?>">
                                    <?php echo esc_html( wp_trim_words( $log->source_url, 8, '...' ) ); ?>
                                </a></td>
                                <td><?php echo esc_html( $log->data_type ); ?></td>
                                <td><?php echo esc_html( $log->fetch_timestamp ); ?></td>
                                <td>
                                    <?php
                                    $status_colors = array(
                                        'success' => '#00a32a',
                                        'error' => '#d63638',
                                        'partial' => '#dba617',
                                        'skipped' => '#999',
                                    );
                                    $color = isset( $status_colors[ $log->status ] ) ? $status_colors[ $log->status ] : '#999';
                                    ?>
                                    <span style="color: <?php echo $color; ?>; font-weight: bold;">
                                        <?php echo esc_html( ucfirst( $log->status ) ); ?>
                                    </span>
                                    <?php if ( $log->error_message ) : ?>
                                        <br><small style="color: #666;" title="<?php echo esc_attr( $log->error_message ); ?>">
                                            <?php echo esc_html( wp_trim_words( $log->error_message, 10, '...' ) ); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo number_format( $log->records_processed ); ?> processed<br>
                                    <small style="color: #666;">
                                        <?php echo number_format( $log->records_created ); ?> created, 
                                        <?php echo number_format( $log->records_updated ); ?> updated
                                    </small>
                                </td>
                                <td><?php echo $log->execution_time ? number_format( $log->execution_time, 2 ) . 's' : '—'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render scraper jobs page
     */
    public function render_scraper_jobs_page() {
        global $wpdb;
        
        $table_scraper_jobs = $wpdb->prefix . 'env_scraper_jobs';
        
        $jobs = $wpdb->get_results( "SELECT * FROM $table_scraper_jobs ORDER BY job_id ASC" );
        
        ?>
        <div class="wrap">
            <h1><?php _e( 'Scraper Jobs', 'env-intel-core' ); ?></h1>
            
            <p><?php _e( 'Configure and manage data scraping jobs for different environmental data sources.', 'env-intel-core' ); ?></p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e( 'ID', 'env-intel-core' ); ?></th>
                        <th><?php _e( 'Source Name', 'env-intel-core' ); ?></th>
                        <th><?php _e( 'Type', 'env-intel-core' ); ?></th>
                        <th><?php _e( 'Base URL', 'env-intel-core' ); ?></th>
                        <th><?php _e( 'Frequency', 'env-intel-core' ); ?></th>
                        <th><?php _e( 'Last Run', 'env-intel-core' ); ?></th>
                        <th><?php _e( 'Next Run', 'env-intel-core' ); ?></th>
                        <th><?php _e( 'Status', 'env-intel-core' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $jobs ) ) : ?>
                        <tr>
                            <td colspan="8"><?php _e( 'No scraper jobs configured.', 'env-intel-core' ); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $jobs as $job ) : ?>
                            <tr>
                                <td><?php echo esc_html( $job->job_id ); ?></td>
                                <td><strong><?php echo esc_html( $job->source_name ); ?></strong></td>
                                <td><?php echo esc_html( $job->source_type ); ?></td>
                                <td><a href="<?php echo esc_url( $job->base_url ); ?>" target="_blank">
                                    <?php echo esc_html( wp_trim_words( $job->base_url, 6, '...' ) ); ?>
                                </a></td>
                                <td><?php echo esc_html( $job->run_frequency ); ?></td>
                                <td><?php echo $job->last_run ? esc_html( $job->last_run ) : '—'; ?></td>
                                <td><?php echo $job->next_run ? esc_html( $job->next_run ) : '—'; ?></td>
                                <td>
                                    <?php if ( $job->is_active ) : ?>
                                        <span style="color: #00a32a;">● <?php _e( 'Active', 'env-intel-core' ); ?></span>
                                    <?php else : ?>
                                        <span style="color: #999;">○ <?php _e( 'Inactive', 'env-intel-core' ); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e( 'Environmental Intelligence Core Settings', 'env-intel-core' ); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields( 'eic_settings' ); ?>
                <?php do_settings_sections( 'eic_settings' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e( 'Data Disclaimer', 'env-intel-core' ); ?></th>
                        <td>
                            <textarea name="eic_data_disclaimer" rows="5" class="large-text"><?php echo esc_textarea( get_option( 'eic_data_disclaimer', self::get_default_disclaimer() ) ); ?></textarea>
                            <p class="description"><?php _e( 'This disclaimer will be displayed on all public-facing environmental data pages.', 'env-intel-core' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e( 'Require Manual Review', 'env-intel-core' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="eic_require_manual_review" value="1" <?php checked( get_option( 'eic_require_manual_review' ), 1 ); ?> />
                                <?php _e( 'Require manual review before publishing scraped data', 'env-intel-core' ); ?>
                            </label>
                            <p class="description"><?php _e( 'When enabled, scraped sites will be saved as drafts for review.', 'env-intel-core' ); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Get default disclaimer text
     */
    public static function get_default_disclaimer() {
        return __( 'DISCLAIMER: The environmental data presented on this site is compiled from public sources including the U.S. Environmental Protection Agency (EPA) and California Environmental Protection Agency (CalEPA). While we strive for accuracy, this information is provided "as is" without warranty of any kind. Users should verify critical information with official government sources. This data is intended for informational and educational purposes only and should not be used as the sole basis for legal, financial, or health-related decisions. Data provenance and source URLs are maintained for all records to support verification and transparency.', 'env-intel-core' );
    }
}

// Initialize admin
new EIC_Admin();
