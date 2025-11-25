# Environmental Intelligence Core

**Version:** 1.0.0  
**License:** Apache-2.0  
**Requires WordPress:** 6.0+  
**Requires PHP:** 7.4+

A comprehensive WordPress plugin for building a Community Environmental Intelligence Platform. This plugin provides database architecture, data acquisition tools, and WooCommerce integration for environmental remediation and sustainable development projects in California.

## Overview

The Environmental Intelligence Core plugin implements a complete data management and web scraping system for environmental contamination sites, with a focus on Superfund sites and California environmental data. The system is designed to empower community-led remediation efforts through transparent, accessible environmental data.

## Key Features

### 1. Hybrid Data Architecture

**Custom Post Types:**
- **Superfund Sites** - Environmental contamination sites requiring remediation
- **Remediation Actions** - Cleanup and investigation activities

**Custom Taxonomies:**
- **Contaminants** - Hierarchical classification of environmental pollutants
- **Environmental Justice Zones** - Flags for vulnerable communities

**Custom Database Tables:**
- **Site Relationships** - Complex site-to-site connections (upstream/downstream, shared aquifers)
- **Data Log** - Comprehensive audit trail for all scraper operations
- **Scraper Jobs** - Configuration and scheduling for data sources

### 2. Web Scraping Engine

**WP-CLI Integration:**
```bash
# List all scraper jobs
wp env-scraper list

# Run specific scraper
wp env-scraper run EPA_SEMS

# Run all active scrapers
wp env-scraper run --all

# Test scraper configuration
wp env-scraper test EPA_SEMS

# Dry run (no data saved)
wp env-scraper run EPA_SEMS --dry-run
```

**Pre-configured Data Sources:**
- EPA SEMS (Superfund Enterprise Management System)
- CalEPA EnviroStor

**Features:**
- Configurable CSS selectors for data extraction
- Pagination support
- Rate limiting and respectful delays
- User-agent rotation
- Error handling and retry logic
- Data integrity verification (SHA-256 hashing)
- Comprehensive logging

### 3. WooCommerce Integration

**Product Association:**
- Link products to specific environmental sites
- Automatic site information display on product pages
- Custom product columns in admin

**Data-as-a-Service (DaaS):**
- Sell granular site data or customized reports
- Virtual and subscription product support
- Configurable data scopes (single site, regional, statewide)

**Programmatic Product Creation:**
```php
// Create a product linked to a superfund site
$product_id = EIC_WooCommerce::create_site_product( 123, array(
    'name' => 'Soil Testing Kit - Site Name',
    'price' => '49.99',
    'description' => 'Professional soil testing kit for this site',
    'type' => 'simple'
) );
```

### 4. Admin Interface

**Scraper Monitor Dashboard:**
- Real-time operation statistics
- Success/error tracking
- Recent operation logs
- Performance metrics

**Scraper Jobs Management:**
- View all configured data sources
- Monitor job status and schedules
- Track last run and next run times

**Settings:**
- Customizable data disclaimer
- Manual review workflow toggle
- Compliance and liability controls

### 5. Compliance & Risk Mitigation

**Data Provenance:**
- Source URL tracking for all records
- SHA-256 hash verification
- Timestamp logging
- Response code tracking

**Legal Safeguards:**
- Default disclaimer text for public-facing pages
- Optional manual review workflow
- Clear data attribution
- Fair use justification framework

## Installation

### Standard Installation

1. Download or clone this repository
2. Upload the `environmental-intelligence-core` folder to `/wp-content/plugins/`
3. Activate the plugin through the WordPress admin panel
4. Navigate to **Superfund Sites** in the admin menu

### Database Setup

The plugin automatically creates custom tables on activation:
- `wp_env_site_relationships`
- `wp_env_data_log`
- `wp_env_scraper_jobs`

### WP-Cron Integration (Optional)

To schedule automatic scraping, add to your theme's `functions.php`:

```php
// Schedule daily scraping
if ( ! wp_next_scheduled( 'eic_daily_scrape' ) ) {
    wp_schedule_event( time(), 'daily', 'eic_daily_scrape' );
}

add_action( 'eic_daily_scrape', function() {
    if ( defined( 'WP_CLI' ) && class_exists( 'WP_CLI' ) ) {
        WP_CLI::runcommand( 'env-scraper run --all' );
    }
});
```

## Database Schema

### Custom Post Types

#### Superfund Site
- **Post Title:** Site name
- **Post Content:** Site description
- **Meta Fields:**
  - `_eic_epa_id` - EPA identification number
  - `_eic_npl_status` - NPL (National Priorities List) status
  - `_eic_latitude` - Geographic latitude
  - `_eic_longitude` - Geographic longitude
  - `_eic_lead_agency` - Responsible government agency
  - `_eic_site_status` - Current remediation status
  - `_eic_remediation_technology` - Technologies being used
  - `_eic_projected_completion_date` - Expected completion date

#### Remediation Action
- **Post Title:** Action name
- **Post Content:** Action details
- **Meta Fields:**
  - `_eic_action_type` - Type of action (investigation, cleanup, etc.)
  - `_eic_start_date` - Action start date
  - `_eic_end_date` - Action end date
  - `_eic_responsible_party` - Party responsible for action
  - `_eic_associated_site` - Linked superfund site ID

### Custom Tables

#### wp_env_site_relationships
```sql
CREATE TABLE wp_env_site_relationships (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    site_id bigint(20) UNSIGNED NOT NULL,
    related_site_id bigint(20) UNSIGNED NOT NULL,
    relationship_type varchar(50) NOT NULL,
    confidence_level enum('high','medium','low') DEFAULT 'medium',
    evidence_source text,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY site_id (site_id),
    KEY related_site_id (related_site_id),
    UNIQUE KEY unique_relationship (site_id, related_site_id, relationship_type)
);
```

**Relationship Types:**
- `upstream` - Contamination flows from this site
- `downstream` - Contamination flows to this site
- `shared_aquifer` - Sites share groundwater source
- `regional_cluster` - Sites in same contaminated region

#### wp_env_data_log
```sql
CREATE TABLE wp_env_data_log (
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
    PRIMARY KEY (log_id),
    KEY scraper_job_id (scraper_job_id),
    KEY fetch_timestamp (fetch_timestamp),
    KEY status (status)
);
```

#### wp_env_scraper_jobs
```sql
CREATE TABLE wp_env_scraper_jobs (
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
    PRIMARY KEY (job_id),
    UNIQUE KEY source_name (source_name)
);
```

**Configuration JSON Structure:**
```json
{
    "selectors": {
        "site_name": ".site-name",
        "epa_id": ".epa-id",
        "status": ".site-status"
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
            "npl_status": "status"
        }
    }
}
```

## API Reference

### Database Functions

```php
// Get active scraper jobs
$jobs = EIC_Database::get_active_jobs();

// Log scraper operation
EIC_Database::log_scraper_operation( array(
    'scraper_job_id' => 1,
    'source_url' => 'https://example.com',
    'data_type' => 'superfund_site',
    'status' => 'success',
    'records_processed' => 10
) );

// Add site relationship
EIC_Database::add_site_relationship( 
    $site_id, 
    $related_site_id, 
    'upstream',
    array( 'confidence_level' => 'high' )
);

// Get site relationships
$relationships = EIC_Database::get_site_relationships( $site_id, 'upstream' );
```

### WooCommerce Functions

```php
// Create product linked to site
$product_id = EIC_WooCommerce::create_site_product( $site_id, array(
    'name' => 'Product Name',
    'price' => '99.99',
    'description' => 'Product description',
    'type' => 'simple'
) );
```

## Hooks & Filters

### Actions

```php
// Before plugin initialization
do_action( 'before_eic_init' );

// After plugin initialization
do_action( 'eic_init' );

// After all plugins loaded
do_action( 'eic_loaded' );
```

### Filters

```php
// Modify superfund site post type registration
apply_filters( 'eic_register_post_type_superfund_site', $args );

// Modify remediation action post type registration
apply_filters( 'eic_register_post_type_remediation_action', $args );

// Modify contaminant taxonomy registration
apply_filters( 'eic_register_taxonomy_contaminant', $args );

// Modify environmental justice zone taxonomy registration
apply_filters( 'eic_register_taxonomy_ej_zone', $args );
```

## Compliance & Legal Considerations

### Data Disclaimer

The plugin includes a default disclaimer that should be displayed on all public-facing environmental data pages:

> DISCLAIMER: The environmental data presented on this site is compiled from public sources including the U.S. Environmental Protection Agency (EPA) and California Environmental Protection Agency (CalEPA). While we strive for accuracy, this information is provided "as is" without warranty of any kind. Users should verify critical information with official government sources. This data is intended for informational and educational purposes only and should not be used as the sole basis for legal, financial, or health-related decisions. Data provenance and source URLs are maintained for all records to support verification and transparency.

### Fair Use Justification

This plugin scrapes public government data for:
- **Transformative use** - Data is restructured and enhanced for community benefit
- **Non-commercial purpose** - Focused on public interest and environmental justice
- **Public benefit** - Empowers communities to understand and address environmental hazards

### Data Verification Workflow

Enable manual review mode in settings to:
- Save all scraped data as drafts
- Allow manual verification before publication
- Flag high-priority sites for review
- Maintain editorial control over published data

## Phased Implementation Roadmap

### Phase 1A: Foundation (Current)
✅ Plugin bootstrap and architecture  
✅ Database schema implementation  
✅ Custom post types and taxonomies  
✅ Basic scraper framework  
✅ WP-CLI commands  
✅ Admin monitoring interface  
✅ WooCommerce integration hooks  

### Phase 1B: Enhanced Scraping (Next)
- Advanced CSS selector engine
- JavaScript-rendered content support (headless browser)
- CAPTCHA handling strategies
- Multi-page pagination logic
- Data validation and sanitization
- Duplicate detection and merging

### Phase 2: Data Enrichment
- Geocoding and mapping integration
- Contaminant risk scoring
- Environmental justice analysis
- Community demographic overlay
- Historical data tracking

### Phase 3: Public Interface
- Frontend site directory
- Interactive mapping
- Advanced search and filtering
- Data export functionality
- Public API endpoints

### Phase 4: Community Features
- User submissions and corrections
- Community monitoring reports
- Notification system for site updates
- Fundraising campaign integration

## Development

### File Structure

```
environmental-intelligence-core/
├── admin/
│   └── class-eic-admin.php          # Admin interface
├── assets/
│   ├── css/
│   │   └── admin.css                # Admin styles
│   └── js/
│       └── admin.js                 # Admin scripts
├── cli/
│   └── class-eic-scraper-command.php # WP-CLI commands
├── includes/
│   ├── class-eic-database.php       # Database schema
│   ├── class-eic-post-types.php     # Custom post types
│   ├── class-eic-taxonomies.php     # Custom taxonomies
│   └── class-eic-woocommerce.php    # WooCommerce integration
└── environmental-intelligence-core.php # Main plugin file
```

### Coding Standards

This plugin follows:
- WordPress Coding Standards
- PHP 7.4+ compatibility
- Object-oriented architecture
- Comprehensive inline documentation
- Security best practices (nonce verification, capability checks, data sanitization)

### Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Follow WordPress coding standards
4. Add inline documentation
5. Test thoroughly
6. Submit a pull request

## Support & Resources

### Official Data Sources

- [EPA Superfund Sites](https://www.epa.gov/superfund)
- [CalEPA EnviroStor](https://www.envirostor.dtsc.ca.gov/)
- [EPA SEMS Database](https://cumulis.epa.gov/supercpad/)

### WordPress Resources

- [WP-CLI Documentation](https://wp-cli.org/)
- [WooCommerce Developer Docs](https://woocommerce.com/documentation/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)

## License

This project is licensed under the Apache License 2.0 - see the [LICENSE](LICENSE) file for details.

## Credits

**Developed by:** ThrivingRoots  
**Repository:** [github.com/QuantumStillness/ThrivingRoots](https://github.com/QuantumStillness/ThrivingRoots)

Built with a commitment to environmental justice, community empowerment, and transparent data access.

---

**Note:** This is a foundational implementation. Production deployment requires:
- Customization of scraper selectors for actual data sources
- Legal review of data usage and disclaimers
- Security audit and penetration testing
- Performance optimization for large datasets
- Backup and disaster recovery planning
