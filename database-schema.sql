-- ============================================================================
-- Environmental Intelligence Core - Database Schema
-- ============================================================================
-- Version: 1.0.0
-- Purpose: Custom database tables for environmental data scraping and management
-- Platform: WordPress/MySQL
-- 
-- This script creates three custom tables to support the Environmental
-- Intelligence Core plugin's data management and scraping operations.
-- 
-- IMPORTANT: Replace 'wp_' prefix with your actual WordPress table prefix
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table 1: Site Relationships
-- ----------------------------------------------------------------------------
-- Purpose: Store complex relationships between superfund sites that cannot
--          be efficiently managed through WordPress post relationships.
-- 
-- Use Cases:
--   - Upstream/downstream contamination tracking
--   - Shared aquifer identification
--   - Regional contamination clusters
--   - Multi-site remediation planning
-- 
-- Relationship Types:
--   - 'upstream': Contamination flows FROM this site
--   - 'downstream': Contamination flows TO this site
--   - 'shared_aquifer': Sites share groundwater source
--   - 'regional_cluster': Sites in same contaminated region
--   - 'legal_connection': Sites linked by legal proceedings
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS wp_env_site_relationships (
    -- Primary key
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    
    -- Site references (WordPress post IDs)
    site_id bigint(20) UNSIGNED NOT NULL COMMENT 'Primary site post ID',
    related_site_id bigint(20) UNSIGNED NOT NULL COMMENT 'Related site post ID',
    
    -- Relationship metadata
    relationship_type varchar(50) NOT NULL COMMENT 'Type of relationship between sites',
    confidence_level enum('high','medium','low') DEFAULT 'medium' COMMENT 'Confidence in relationship accuracy',
    evidence_source text COMMENT 'Source documentation or reasoning for relationship',
    
    -- Audit timestamps
    created_at datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
    
    -- Indexes
    PRIMARY KEY (id),
    KEY idx_site_id (site_id),
    KEY idx_related_site_id (related_site_id),
    KEY idx_relationship_type (relationship_type),
    KEY idx_confidence_level (confidence_level),
    
    -- Constraints
    UNIQUE KEY unique_relationship (site_id, related_site_id, relationship_type)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Complex site-to-site relationships for environmental data';

-- ----------------------------------------------------------------------------
-- Table 2: Data Log
-- ----------------------------------------------------------------------------
-- Purpose: Comprehensive audit trail for all scraper operations, supporting
--          data provenance, error tracking, and compliance verification.
-- 
-- Use Cases:
--   - Scraper operation monitoring
--   - Error diagnosis and debugging
--   - Data provenance verification
--   - Compliance auditing
--   - Performance optimization
--   - Change detection (via source_hash)
-- 
-- Status Values:
--   - 'success': Operation completed successfully
--   - 'error': Operation failed with error
--   - 'partial': Some data retrieved, some failed
--   - 'skipped': Operation skipped (e.g., no changes detected)
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS wp_env_data_log (
    -- Primary key
    log_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    
    -- Job reference
    scraper_job_id bigint(20) UNSIGNED NOT NULL COMMENT 'Reference to scraper job configuration',
    
    -- Source information
    source_url varchar(500) NOT NULL COMMENT 'URL of scraped data source',
    data_type varchar(100) NOT NULL COMMENT 'Type of data scraped (e.g., superfund_site)',
    
    -- Operation details
    fetch_timestamp datetime NOT NULL COMMENT 'When data was fetched',
    status enum('success','error','partial','skipped') NOT NULL COMMENT 'Operation status',
    
    -- Record statistics
    records_processed int(11) DEFAULT 0 COMMENT 'Total records processed',
    records_created int(11) DEFAULT 0 COMMENT 'New records created',
    records_updated int(11) DEFAULT 0 COMMENT 'Existing records updated',
    
    -- Error tracking
    error_message text COMMENT 'Error details if status is error',
    
    -- Data integrity
    source_hash varchar(64) COMMENT 'SHA-256 hash of source data for change detection',
    
    -- Performance metrics
    response_code int(11) COMMENT 'HTTP response code',
    execution_time float COMMENT 'Operation execution time in seconds',
    
    -- Audit timestamp
    created_at datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Log entry creation timestamp',
    
    -- Indexes
    PRIMARY KEY (log_id),
    KEY idx_scraper_job_id (scraper_job_id),
    KEY idx_fetch_timestamp (fetch_timestamp),
    KEY idx_status (status),
    KEY idx_data_type (data_type),
    KEY idx_source_hash (source_hash),
    KEY idx_created_at (created_at)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Comprehensive audit trail for scraper operations';

-- ----------------------------------------------------------------------------
-- Table 3: Scraper Jobs
-- ----------------------------------------------------------------------------
-- Purpose: Configuration and scheduling for different environmental data
--          sources, enabling multi-source scraping with individual settings.
-- 
-- Use Cases:
--   - Multi-source data collection
--   - Job scheduling and automation
--   - Source-specific configuration
--   - Rate limiting management
--   - Retry logic configuration
-- 
-- Source Types:
--   - 'html': Standard HTML scraping
--   - 'json': JSON API endpoints
--   - 'xml': XML data feeds
--   - 'csv': CSV file downloads
-- 
-- Run Frequencies:
--   - 'hourly', 'daily', 'weekly', 'monthly', 'manual'
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS wp_env_scraper_jobs (
    -- Primary key
    job_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    
    -- Source identification
    source_name varchar(100) NOT NULL COMMENT 'Unique identifier for data source',
    source_type varchar(50) NOT NULL COMMENT 'Type of data source (html, json, xml, csv)',
    base_url varchar(500) NOT NULL COMMENT 'Base URL for data source',
    
    -- Scheduling
    last_run datetime COMMENT 'Timestamp of last execution',
    next_run datetime COMMENT 'Scheduled next execution time',
    is_active tinyint(1) DEFAULT 1 COMMENT 'Whether job is active',
    run_frequency varchar(50) DEFAULT 'daily' COMMENT 'How often to run (hourly, daily, weekly, monthly)',
    
    -- Configuration
    config longtext COMMENT 'JSON configuration (selectors, pagination, mapping)',
    
    -- HTTP settings
    user_agent varchar(255) COMMENT 'User agent string for HTTP requests',
    rate_limit_delay int(11) DEFAULT 2 COMMENT 'Delay between requests in seconds',
    max_retries int(11) DEFAULT 3 COMMENT 'Maximum retry attempts on failure',
    timeout int(11) DEFAULT 30 COMMENT 'Request timeout in seconds',
    
    -- Audit timestamps
    created_at datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Job creation timestamp',
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
    
    -- Indexes
    PRIMARY KEY (job_id),
    UNIQUE KEY unique_source_name (source_name),
    KEY idx_is_active (is_active),
    KEY idx_next_run (next_run),
    KEY idx_source_type (source_type),
    KEY idx_run_frequency (run_frequency)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Scraper job configuration and scheduling';

-- ----------------------------------------------------------------------------
-- Default Data: Scraper Jobs
-- ----------------------------------------------------------------------------
-- Insert default configurations for EPA SEMS and CalEPA EnviroStor
-- These are starter configurations that should be customized for production
-- ----------------------------------------------------------------------------

INSERT INTO wp_env_scraper_jobs 
(source_name, source_type, base_url, is_active, run_frequency, config, user_agent, rate_limit_delay, max_retries, timeout)
VALUES 
(
    'EPA_SEMS',
    'html',
    'https://cumulis.epa.gov/supercpad/cursites/srchsites.cfm',
    1,
    'weekly',
    '{
        "selectors": {
            "site_name": ".site-name",
            "epa_id": ".epa-id",
            "status": ".site-status",
            "address": ".site-address",
            "coordinates": ".coordinates"
        },
        "pagination": {
            "type": "numbered",
            "selector": ".pagination a.next",
            "max_pages": 100
        },
        "data_mapping": {
            "post_type": "superfund_site",
            "meta_fields": {
                "epa_id": "epa_id",
                "npl_status": "status",
                "latitude": "lat",
                "longitude": "lng"
            }
        }
    }',
    'Mozilla/5.0 (compatible; EnvironmentalIntelligenceBot/1.0; +https://thrivingroots.org)',
    3,
    3,
    30
),
(
    'CalEPA_EnviroStor',
    'html',
    'https://www.envirostor.dtsc.ca.gov/public/',
    1,
    'weekly',
    '{
        "selectors": {
            "site_name": "h2.site-title",
            "site_id": ".site-id",
            "status": ".cleanup-status",
            "contaminants": ".contaminant-list li"
        },
        "pagination": {
            "type": "load_more",
            "selector": "button.load-more",
            "max_iterations": 50
        },
        "data_mapping": {
            "post_type": "superfund_site",
            "taxonomies": {
                "contaminant": "contaminants"
            }
        }
    }',
    'Mozilla/5.0 (compatible; EnvironmentalIntelligenceBot/1.0; +https://thrivingroots.org)',
    2,
    3,
    30
);

-- ----------------------------------------------------------------------------
-- Verification Queries
-- ----------------------------------------------------------------------------
-- Use these queries to verify successful table creation and data insertion
-- ----------------------------------------------------------------------------

-- Verify table creation
-- SHOW TABLES LIKE 'wp_env_%';

-- Verify table structure
-- DESCRIBE wp_env_site_relationships;
-- DESCRIBE wp_env_data_log;
-- DESCRIBE wp_env_scraper_jobs;

-- Verify default data
-- SELECT * FROM wp_env_scraper_jobs;

-- Check indexes
-- SHOW INDEX FROM wp_env_site_relationships;
-- SHOW INDEX FROM wp_env_data_log;
-- SHOW INDEX FROM wp_env_scraper_jobs;

-- ============================================================================
-- End of Schema
-- ============================================================================
