# Environmental Intelligence Core - Implementation Guide

This document provides a comprehensive implementation guide for the Environmental Intelligence Core plugin, addressing all requirements from the Phase 1A directive.

## Table of Contents

1. [Hybrid Data Model Specification](#hybrid-data-model-specification)
2. [WooCommerce Integration Strategy](#woocommerce-integration-strategy)
3. [Environmental Scraper Tool Specification](#environmental-scraper-tool-specification)
4. [Compliance & Risk Mitigation Framework](#compliance--risk-mitigation-framework)
5. [Phased Implementation Roadmap](#phased-implementation-roadmap)

---

## Part 1: Hybrid Data Model Specification

### A) Custom Post Types & Taxonomies

The plugin implements a WordPress-native content model that leverages the admin UI and querying capabilities while maintaining flexibility for environmental data.

#### Custom Post Type: `superfund_site`

**Purpose:** Primary content type for environmental contamination sites requiring remediation.

**Core Fields:**
- `post_title` - Site name (e.g., "Iron Mountain Mine Superfund Site")
- `post_content` - Detailed site description and history

**Meta Fields:**
- `_eic_epa_id` - EPA identification number (unique identifier)
- `_eic_npl_status` - National Priorities List status (proposed, final, deleted, not_on_npl)
- `_eic_latitude` - Geographic latitude coordinate
- `_eic_longitude` - Geographic longitude coordinate
- `_eic_lead_agency` - Responsible government agency
- `_eic_site_status` - Current remediation status (assessment, cleanup, monitoring, completed)
- `_eic_remediation_technology` - Technologies and methods being employed
- `_eic_projected_completion_date` - Expected completion date
- `_eic_source` - Data source identifier (for provenance)
- `_eic_source_hash` - SHA-256 hash of source data (for integrity verification)

**Admin Features:**
- Custom meta boxes for site details, location, and status
- Integration with WordPress media library for site photos
- Revision tracking for all changes
- REST API support for headless applications

#### Custom Post Type: `remediation_action`

**Purpose:** Track specific remediation and cleanup actions associated with sites.

**Core Fields:**
- `post_title` - Action name (e.g., "Soil Excavation Phase 2")
- `post_content` - Detailed action description

**Meta Fields:**
- `_eic_action_type` - Type of action (investigation, cleanup, monitoring, remediation)
- `_eic_start_date` - Action start date
- `_eic_end_date` - Action completion date
- `_eic_responsible_party` - Entity responsible for action
- `_eic_associated_site` - Post ID of related superfund_site

**Relationship:** Connected to `superfund_site` via post-to-post relationship field stored in `_eic_associated_site` meta.

#### Taxonomy: `contaminant`

**Purpose:** Hierarchical classification of environmental contaminants found at sites.

**Structure:** Hierarchical (allows parent/child relationships)

**Default Terms:**
```
Heavy Metals
├── Lead
├── Arsenic
├── Mercury
├── Cadmium
└── Chromium

Volatile Organic Compounds (VOCs)
├── Trichloroethylene (TCE)
├── Perchloroethylene (PCE)
├── Benzene
├── Toluene
└── Vinyl Chloride

Petroleum Products
├── Gasoline
├── Diesel
├── Oil
└── BTEX

Pesticides & Herbicides
├── DDT
├── Chlordane
├── Dioxins
└── PCBs

Other
├── Asbestos
├── Radioactive Materials
└── PFAS
```

**Attached To:** `superfund_site` CPT

**Admin Features:**
- Hierarchical term selection
- Admin column display
- REST API support
- Tag cloud widget

#### Taxonomy: `environmental_justice_zone`

**Purpose:** Flag sites located in vulnerable or disadvantaged communities requiring special attention.

**Structure:** Non-hierarchical (flat taxonomy)

**Default Terms:**
- CalEnviroScreen High Priority
- Disadvantaged Community
- Low-Income Community
- Tribal Land
- Historically Redlined Area

**Attached To:** `superfund_site` CPT

**Use Cases:**
- Prioritize community outreach
- Target funding and resources
- Support environmental justice advocacy
- Comply with equity requirements

### B) Custom Database Tables

While WordPress CPTs handle primary content, custom tables provide performance and flexibility for complex relational data.

#### Table: `wp_env_site_relationships`

**Purpose:** Store complex site-to-site relationships that cannot be efficiently managed through WordPress post relationships.

**Schema:**
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
    KEY idx_site_id (site_id),
    KEY idx_related_site_id (related_site_id),
    UNIQUE KEY unique_relationship (site_id, related_site_id, relationship_type)
);
```

**Relationship Types:**
- `upstream` - Contamination flows from this site
- `downstream` - Contamination flows to this site
- `shared_aquifer` - Sites share groundwater source
- `regional_cluster` - Sites in same contaminated region

**Performance Optimization:**
- Indexed on `site_id` and `related_site_id` for fast lookups
- Unique constraint prevents duplicate relationships
- Confidence level supports data quality tracking

#### Table: `wp_env_data_log`

**Purpose:** Comprehensive audit trail for all scraper operations, supporting data provenance and compliance.

**Schema:**
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
    KEY idx_scraper_job_id (scraper_job_id),
    KEY idx_fetch_timestamp (fetch_timestamp),
    KEY idx_status (status),
    KEY idx_source_hash (source_hash)
);
```

**Key Features:**
- `source_hash` enables change detection (only update if data changed)
- `execution_time` supports performance monitoring
- `records_*` fields provide detailed operation statistics
- Indexed on `fetch_timestamp` for time-series analysis

#### Table: `wp_env_scraper_jobs`

**Purpose:** Configuration and scheduling for different environmental data sources.

**Schema:**
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
    UNIQUE KEY unique_source_name (source_name),
    KEY idx_is_active (is_active),
    KEY idx_next_run (next_run)
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

---

## Part 2: WooCommerce Integration Strategy

### Product Association Architecture

The plugin creates a bidirectional relationship between WooCommerce products and environmental sites, enabling commerce around environmental data and services.

#### Data Relationship Model

```
WooCommerce Product (product CPT)
    ↓ (meta: _eic_associated_site)
Superfund Site (superfund_site CPT)
    ↓ (reverse query)
Associated Products
```

**Implementation:**
- Meta field `_eic_associated_site` stores the post ID of the linked superfund site
- Custom meta box on product edit screen for site selection
- Automatic site information display on product pages
- Custom admin column showing associated site

#### Use Cases

**1. Soil Testing Kits**
```php
$product_id = EIC_WooCommerce::create_site_product( 123, array(
    'name' => 'Soil Testing Kit - Iron Mountain Mine',
    'price' => '49.99',
    'description' => 'Professional soil testing kit for heavy metals',
    'type' => 'simple'
) );
```

**2. Fundraising for Site Cleanup**
```php
$product_id = EIC_WooCommerce::create_site_product( 123, array(
    'name' => 'Support Cleanup - Iron Mountain Mine',
    'price' => '25.00',
    'description' => 'Donate to support community-led cleanup efforts',
    'type' => 'simple'
) );
```

**3. Remediation Consulting Services**
```php
$product_id = EIC_WooCommerce::create_site_product( 123, array(
    'name' => 'Site Assessment Consultation',
    'price' => '500.00',
    'description' => 'Professional environmental assessment',
    'type' => 'booking' // requires WooCommerce Bookings
) );
```

### Data-as-a-Service (DaaS) Implementation

#### Product Configuration

Products can be flagged as DaaS offerings with configurable scopes:

**Meta Fields:**
- `_eic_is_daas_product` - Boolean flag for DaaS products
- `_eic_daas_scope` - Scope of data (single_site, regional, statewide, custom)

**Data Scopes:**
- **Single Site Report** - Comprehensive data for one site
- **Regional Analysis** - Multi-site regional assessment
- **Statewide Dataset** - Complete California environmental data
- **Custom Data Request** - Bespoke data compilation

#### Delivery Mechanisms

**Virtual Products:**
```php
$product = new WC_Product_Simple();
$product->set_virtual( true );
$product->set_downloadable( true );
update_post_meta( $product_id, '_eic_is_daas_product', 'yes' );
update_post_meta( $product_id, '_eic_daas_scope', 'single_site' );
```

**Subscription Products (requires WooCommerce Subscriptions):**
```php
$product = new WC_Product_Subscription();
$product->set_subscription_period( 'month' );
$product->set_subscription_price( '29.99' );
update_post_meta( $product_id, '_eic_is_daas_product', 'yes' );
update_post_meta( $product_id, '_eic_daas_scope', 'statewide' );
```

#### Programmatic Product Creation

**Function Signature:**
```php
EIC_WooCommerce::create_site_product( int $site_id, array $args ) : int|WP_Error
```

**Example Implementation:**
```php
// Create a product linked to site ID 123
$product_id = EIC_WooCommerce::create_site_product( 123, array(
    'name' => 'Comprehensive Site Report - Iron Mountain Mine',
    'price' => '99.99',
    'description' => 'Complete environmental data package including contaminant analysis, remediation history, and risk assessment.',
    'short_description' => 'Professional-grade environmental data report',
    'type' => 'simple',
    'sku' => 'SITE-REPORT-123',
    'manage_stock' => false,
    'in_stock' => true
) );

if ( is_wp_error( $product_id ) ) {
    error_log( 'Product creation failed: ' . $product_id->get_error_message() );
} else {
    // Mark as DaaS product
    update_post_meta( $product_id, '_eic_is_daas_product', 'yes' );
    update_post_meta( $product_id, '_eic_daas_scope', 'single_site' );
}
```

---

## Part 3: Environmental Scraper Tool Specification

### Technology Stack

**Core Technologies:**
- **PHP** - Primary scraping logic
- **WP_Http** - WordPress HTTP API for requests
- **DOMDocument** - HTML parsing
- **DOMXPath** - CSS selector querying
- **WP-CLI** - Command-line interface

**Optional Enhancements:**
- **Headless Browser** - For JavaScript-rendered content (Puppeteer, Playwright)
- **Proxy Rotation** - For IP-based rate limiting
- **CAPTCHA Solving** - For protected sources (manual intervention required)

### Data Flow Architecture

```
┌─────────────────┐
│  WP-CLI Command │
│  wp env-scraper │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────────────────────┐
│ 1. FETCH                                        │
│ - Retrieve HTML from target URL                │
│ - Apply rate limiting (respectful delays)       │
│ - Use configured user agent                    │
│ - Handle HTTP errors and retries               │
└────────┬────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────┐
│ 2. PARSE & VALIDATE                             │
│ - Load HTML into DOMDocument                    │
│ - Extract data using CSS selectors              │
│ - Validate against expected schema              │
│ - Handle missing or malformed data              │
└────────┬────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────┐
│ 3. TRANSFORM & STORE                            │
│ - Map scraped data to WordPress CPTs            │
│ - Create or update posts and meta fields        │
│ - Assign taxonomies                             │
│ - Log all operations to wp_env_data_log         │
└─────────────────────────────────────────────────┘
```

### WP-CLI Command Structure

**Command Registration:**
```php
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    WP_CLI::add_command( 'env-scraper', 'EIC_Scraper_Command' );
}
```

**Available Commands:**
```bash
# List all configured scraper jobs
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

### Resilience Features

#### 1. Pagination Handling

**Numbered Pagination:**
```json
{
    "pagination": {
        "type": "numbered",
        "selector": ".pagination a.next",
        "max_pages": 100
    }
}
```

**Load More Button:**
```json
{
    "pagination": {
        "type": "load_more",
        "selector": "button.load-more",
        "max_iterations": 50
    }
}
```

**Infinite Scroll:**
```json
{
    "pagination": {
        "type": "infinite",
        "trigger_selector": ".load-trigger",
        "max_scrolls": 20
    }
}
```

#### 2. JavaScript-Rendered Content

**Detection Strategy:**
- Check for empty or minimal HTML body
- Look for JavaScript framework indicators (React, Vue, Angular)
- Detect AJAX loading patterns

**Fallback Approach:**
```php
// If JavaScript detected, suggest headless browser
if ( $this->is_javascript_rendered( $html ) ) {
    WP_CLI::warning( 'JavaScript-rendered content detected. Consider using headless browser.' );
    // Log recommendation for headless browser implementation
}
```

**Headless Browser Integration (Future Enhancement):**
```javascript
// Example using Puppeteer (Node.js)
const puppeteer = require('puppeteer');

async function scrapeWithBrowser(url) {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    await page.goto(url, { waitUntil: 'networkidle2' });
    const html = await page.content();
    await browser.close();
    return html;
}
```

#### 3. CAPTCHA Handling

**Detection:**
- Check for common CAPTCHA services (reCAPTCHA, hCaptcha)
- Monitor for HTTP 403 responses
- Detect CAPTCHA-related DOM elements

**Mitigation Strategies:**
1. **Manual Intervention** - Pause and request user to solve CAPTCHA
2. **Session Cookies** - Save authenticated session for reuse
3. **API Access** - Request official API access from data provider
4. **Reduced Frequency** - Slow down scraping to avoid triggering

**Implementation:**
```php
if ( $this->has_captcha( $html ) ) {
    WP_CLI::error( 'CAPTCHA detected. Manual intervention required.' );
    // Log CAPTCHA event
    // Optionally: Send notification to admin
}
```

#### 4. Rate Limiting

**Respectful Delays:**
```php
// Configurable delay between requests
sleep( $job->rate_limit_delay ); // Default: 2-3 seconds
```

**Adaptive Rate Limiting:**
```php
// Increase delay if errors detected
if ( $error_count > 3 ) {
    $delay = min( $delay * 2, 30 ); // Exponential backoff, max 30s
}
```

**User-Agent Rotation:**
```php
$user_agents = array(
    'Mozilla/5.0 (compatible; EnvironmentalIntelligenceBot/1.0; +https://thrivingroots.org)',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    // Additional user agents
);
$user_agent = $user_agents[ array_rand( $user_agents ) ];
```

#### 5. Error Handling & Retries

**Retry Logic:**
```php
$max_retries = $job->max_retries;
$retry_count = 0;

while ( $retry_count < $max_retries ) {
    $response = wp_remote_get( $url, $args );
    
    if ( ! is_wp_error( $response ) ) {
        break; // Success
    }
    
    $retry_count++;
    sleep( pow( 2, $retry_count ) ); // Exponential backoff
}
```

**Error Logging:**
```php
EIC_Database::log_scraper_operation( array(
    'scraper_job_id' => $job->job_id,
    'source_url' => $url,
    'status' => 'error',
    'error_message' => $response->get_error_message(),
    'execution_time' => $execution_time
) );
```

---

## Part 4: Compliance & Risk Mitigation Framework

### Top Risks & Mitigation Strategies

#### Risk 1: Data Accuracy Liability

**Risk Description:** Publishing inaccurate environmental data could lead to:
- Misinformed health decisions
- Property value disputes
- Legal liability claims
- Regulatory violations

**Mitigation Strategies:**

**1. Data Provenance System**
```php
// Store source information with every record
update_post_meta( $post_id, '_eic_source', 'EPA_SEMS' );
update_post_meta( $post_id, '_eic_source_url', $source_url );
update_post_meta( $post_id, '_eic_source_hash', hash( 'sha256', $raw_data ) );
update_post_meta( $post_id, '_eic_fetch_date', current_time( 'mysql' ) );
```

**2. Public Disclaimer**

**Default Disclaimer Text:**
> DISCLAIMER: The environmental data presented on this site is compiled from public sources including the U.S. Environmental Protection Agency (EPA) and California Environmental Protection Agency (CalEPA). While we strive for accuracy, this information is provided "as is" without warranty of any kind. Users should verify critical information with official government sources. This data is intended for informational and educational purposes only and should not be used as the sole basis for legal, financial, or health-related decisions. Data provenance and source URLs are maintained for all records to support verification and transparency.

**Implementation:**
```php
// Display disclaimer on all environmental data pages
add_action( 'wp_footer', function() {
    if ( is_singular( 'superfund_site' ) ) {
        echo '<div class="eic-disclaimer">' . 
             esc_html( get_option( 'eic_data_disclaimer' ) ) . 
             '</div>';
    }
});
```

**3. Manual Review Workflow**

**Setting:**
```php
// Enable manual review mode
update_option( 'eic_require_manual_review', 1 );
```

**Implementation:**
```php
// Save scraped sites as drafts for review
$post_status = get_option( 'eic_require_manual_review' ) ? 'draft' : 'publish';

wp_insert_post( array(
    'post_type' => 'superfund_site',
    'post_status' => $post_status,
    // ... other fields
) );
```

**4. Verification Flags**

**Custom Meta:**
```php
// Flag sites requiring verification
update_post_meta( $post_id, '_eic_verification_status', 'pending' );
update_post_meta( $post_id, '_eic_verified_by', $user_id );
update_post_meta( $post_id, '_eic_verification_date', current_time( 'mysql' ) );
```

#### Risk 2: Copyright of Scraped Data

**Risk Description:** Scraping copyrighted content could result in:
- DMCA takedown notices
- Copyright infringement lawsuits
- Cease and desist orders

**Fair Use Justification:**

**1. Transformative Use**
- Data is restructured and enhanced for community benefit
- Original format is not replicated
- Additional analysis and context added
- Different purpose than original source

**2. Non-Commercial Purpose**
- Primary goal is public interest and environmental justice
- Any revenue supports platform maintenance
- Educational and informational focus

**3. Public Benefit**
- Empowers communities to understand environmental hazards
- Supports public health and safety
- Promotes government transparency
- Facilitates civic engagement

**4. Factual Data**
- Facts are not copyrightable
- Government data is public domain
- Only factual information is extracted, not creative expression

**Documentation:**
```php
// Maintain clear attribution
$attribution = sprintf(
    'Data sourced from %s on %s. Original data: %s',
    $source_name,
    date( 'Y-m-d' ),
    $source_url
);
update_post_meta( $post_id, '_eic_attribution', $attribution );
```

#### Risk 3: WooCommerce Transaction Liability

**Risk Description:** Selling environmental services/products could create liability if:
- Services don't meet expectations
- Data is used for critical decisions
- Products fail to deliver promised results

**Mitigation Strategies:**

**1. Clear Terms of Service**
```
All environmental data products and services are provided for 
informational purposes only. No guarantees are made regarding 
accuracy, completeness, or fitness for a particular purpose. 
Purchasers should conduct independent verification for critical 
decisions.
```

**2. Product Disclaimers**
```php
// Add disclaimer to product pages
add_action( 'woocommerce_before_add_to_cart_form', function() {
    global $post;
    $is_daas = get_post_meta( $post->ID, '_eic_is_daas_product', true );
    
    if ( $is_daas === 'yes' ) {
        echo '<div class="eic-product-disclaimer">';
        echo '<strong>Important:</strong> This data product is for informational purposes only. ';
        echo 'Verify all critical information with official sources.';
        echo '</div>';
    }
});
```

**3. Service Limitations**
```php
// Document service scope in product description
$description = 'This consultation provides general environmental assessment guidance. 
It does not constitute official testing, legal advice, or regulatory compliance 
certification. Professional licensed services may be required for official purposes.';
```

### Cryptographic Data Integrity Pattern

**Purpose:** Enable future verification that data hasn't been tampered with.

**Implementation:**
```php
// Store SHA-256 hash of source data
$source_hash = hash( 'sha256', $raw_html );

// Log hash with operation
EIC_Database::log_scraper_operation( array(
    'source_hash' => $source_hash,
    // ... other fields
) );

// Store hash with post
update_post_meta( $post_id, '_eic_source_hash', $source_hash );
```

**Verification:**
```php
// Later, verify data integrity
$stored_hash = get_post_meta( $post_id, '_eic_source_hash', true );
$current_hash = hash( 'sha256', $current_raw_html );

if ( $stored_hash !== $current_hash ) {
    // Data has changed - flag for review
    update_post_meta( $post_id, '_eic_data_changed', true );
}
```

---

## Part 5: Phased Implementation Roadmap

### Phase 1A: Foundation (COMPLETED)

**Deliverables:**
- ✅ Plugin bootstrap with proper WordPress integration
- ✅ Custom database tables with indexes
- ✅ Custom post types (superfund_site, remediation_action)
- ✅ Custom taxonomies (contaminant, environmental_justice_zone)
- ✅ Basic scraper framework with WP-CLI commands
- ✅ Admin monitoring interface
- ✅ WooCommerce integration hooks
- ✅ Comprehensive documentation

**Files Created:**
- `environmental-intelligence-core.php` - Main plugin file
- `includes/class-eic-database.php` - Database schema
- `includes/class-eic-post-types.php` - CPT registration
- `includes/class-eic-taxonomies.php` - Taxonomy registration
- `includes/class-eic-woocommerce.php` - WooCommerce integration
- `admin/class-eic-admin.php` - Admin interface
- `cli/class-eic-scraper-command.php` - WP-CLI commands
- `README.md` - User documentation
- `IMPLEMENTATION_GUIDE.md` - Technical documentation
- `database-schema.sql` - Standalone SQL schema

### Phase 1B: Enhanced Scraping (NEXT)

**Timeline:** 2-3 weeks

**Objectives:**
1. Implement production-ready CSS selector engine
2. Add JavaScript-rendered content support
3. Develop robust pagination logic
4. Implement data validation and sanitization
5. Add duplicate detection and merging

**Technical Tasks:**

**1. Advanced CSS Selector Engine**
```php
class EIC_Selector_Engine {
    public function extract( $html, $selectors ) {
        $dom = new DOMDocument();
        @$dom->loadHTML( $html );
        $xpath = new DOMXPath( $dom );
        
        $data = array();
        foreach ( $selectors as $key => $selector ) {
            $data[ $key ] = $this->query_selector( $xpath, $selector );
        }
        return $data;
    }
}
```

**2. Headless Browser Integration**
```bash
# Install Puppeteer
npm install puppeteer

# Create Node.js scraper wrapper
node scraper-wrapper.js <url> <selectors>
```

**3. Pagination Engine**
```php
class EIC_Pagination_Handler {
    public function handle_pagination( $job, $initial_url ) {
        $pages = array( $initial_url );
        $config = json_decode( $job->config, true );
        
        switch ( $config['pagination']['type'] ) {
            case 'numbered':
                $pages = $this->handle_numbered( $initial_url, $config );
                break;
            case 'load_more':
                $pages = $this->handle_load_more( $initial_url, $config );
                break;
        }
        
        return $pages;
    }
}
```

**4. Data Validation**
```php
class EIC_Data_Validator {
    public function validate_site_data( $data ) {
        $errors = array();
        
        // Required fields
        if ( empty( $data['site_name'] ) ) {
            $errors[] = 'Site name is required';
        }
        
        // Format validation
        if ( ! empty( $data['latitude'] ) && ! $this->is_valid_latitude( $data['latitude'] ) ) {
            $errors[] = 'Invalid latitude format';
        }
        
        return empty( $errors ) ? true : $errors;
    }
}
```

**5. Duplicate Detection**
```php
class EIC_Duplicate_Detector {
    public function find_duplicate( $epa_id ) {
        $existing = get_posts( array(
            'post_type' => 'superfund_site',
            'meta_key' => '_eic_epa_id',
            'meta_value' => $epa_id,
            'posts_per_page' => 1
        ) );
        
        return ! empty( $existing ) ? $existing[0]->ID : false;
    }
}
```

### Phase 2: Data Enrichment (4-6 weeks)

**Objectives:**
1. Geocoding and mapping integration
2. Contaminant risk scoring
3. Environmental justice analysis
4. Community demographic overlay
5. Historical data tracking

**Key Features:**
- Google Maps / Mapbox integration
- CalEnviroScreen data overlay
- Census demographic data
- Historical contamination timelines
- Risk assessment algorithms

### Phase 3: Public Interface (4-6 weeks)

**Objectives:**
1. Frontend site directory with search/filter
2. Interactive mapping interface
3. Data export functionality
4. Public API endpoints
5. Mobile-responsive design

**Key Features:**
- Advanced search (by contaminant, location, status)
- Interactive map with clustering
- CSV/JSON export
- REST API for third-party access
- Responsive templates

### Phase 4: Community Features (6-8 weeks)

**Objectives:**
1. User submissions and corrections
2. Community monitoring reports
3. Notification system for site updates
4. Fundraising campaign integration
5. Social sharing and advocacy tools

**Key Features:**
- User-submitted site reports
- Photo upload and documentation
- Email notifications for site changes
- WooCommerce fundraising campaigns
- Social media integration

---

## Conclusion

This implementation provides a complete foundation for the Environmental Intelligence Core platform. The hybrid data model balances WordPress native functionality with custom database performance. The scraper framework is extensible and resilient. WooCommerce integration enables sustainable revenue models. Compliance features mitigate legal risks.

**Next Steps:**
1. Deploy plugin to WordPress staging environment
2. Customize scraper selectors for actual EPA/CalEPA sites
3. Test scraper operations with dry runs
4. Conduct legal review of disclaimers and terms
5. Begin Phase 1B development

**Success Metrics:**
- Sites scraped and published
- Data accuracy verification rate
- User engagement with site data
- Revenue from DaaS products
- Community feedback and submissions

This platform empowers communities with transparent environmental data, supporting informed decision-making and environmental justice advocacy.
