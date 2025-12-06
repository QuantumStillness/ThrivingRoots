# ThrivingRoots Platform - Deployment Guide

**Complete deployment instructions for Environmental Intelligence & Community Resilience Platform**

## Overview

The ThrivingRoots platform consists of three integrated components:

1. **Environmental Intelligence Core** - WordPress plugin for environmental data management
2. **Geospatial Intelligence Platform** - Python-based spatial analysis and risk assessment
3. **Community Resilience Module** - WordPress plugin for material safety and fire recovery

## System Requirements

### WordPress Environment
- **WordPress:** 6.0 or higher
- **PHP:** 7.4 or higher
- **MySQL:** 5.7 or higher (or MariaDB 10.3+)
- **Memory Limit:** 256MB minimum (512MB recommended)
- **Max Execution Time:** 300 seconds
- **Upload Size:** 64MB minimum

### Geospatial Environment
- **Python:** 3.8 or higher
- **PostGIS:** 3.0 or higher (optional, for advanced spatial features)
- **Disk Space:** 10GB minimum for data storage
- **RAM:** 4GB minimum (8GB recommended for large datasets)

### Server Requirements
- **Web Server:** Apache 2.4+ or Nginx 1.18+
- **SSL Certificate:** Required for production
- **Cron Jobs:** For scheduled scraping and data updates
- **API Access:** For external data sources (EPA, USGS, etc.)

## Pre-Deployment Checklist

- [ ] WordPress site installed and configured
- [ ] Database backup created
- [ ] FTP/SSH access credentials ready
- [ ] Domain name configured with SSL
- [ ] Email configured for notifications
- [ ] API keys obtained (optional but recommended)
- [ ] Server meets minimum requirements
- [ ] Backup and rollback plan prepared

## Part 1: Environmental Intelligence Core Deployment

### Step 1: Upload Plugin Files

**Via WordPress Admin:**
```
1. Navigate to Plugins > Add New
2. Click "Upload Plugin"
3. Choose environmental-intelligence-core.zip
4. Click "Install Now"
5. Click "Activate Plugin"
```

**Via FTP/SSH:**
```bash
# Upload via FTP
Upload environmental-intelligence-core/ to /wp-content/plugins/

# Or via SSH
cd /var/www/html/wp-content/plugins/
git clone https://github.com/QuantumStillness/ThrivingRoots.git
mv ThrivingRoots/environmental-intelligence-core ./
```

### Step 2: Database Setup

The plugin automatically creates custom tables on activation. Verify:

```sql
-- Check if tables exist
SHOW TABLES LIKE 'wp_eic_%';

-- Expected tables:
-- wp_eic_site_relationships
-- wp_eic_data_logs
-- wp_eic_scraper_jobs
```

**Manual Database Setup (if needed):**
```bash
mysql -u username -p database_name < database-schema.sql
```

### Step 3: Configure Plugin Settings

```
1. Navigate to Environmental Intelligence > Settings
2. Configure:
   - Data Source URLs (EPA SEMS, CalEPA EnviroStor)
   - Scraper Settings (rate limits, timeout)
   - Compliance Settings (disclaimers, manual review)
   - WooCommerce Integration (if applicable)
3. Save settings
```

### Step 4: Test Scraper Functionality

```bash
# Via WP-CLI
wp env-scraper test --dry-run

# List available scrapers
wp env-scraper list

# Run a test scrape
wp env-scraper run epa-sems --limit=10 --dry-run
```

### Step 5: Create Initial Content

```
1. Navigate to Superfund Sites > Add New
2. Create a test site manually
3. Verify custom fields and taxonomies work
4. Test WooCommerce product association (if applicable)
```

### Step 6: Set Up Cron Jobs

**Via cPanel:**
```
Add cron job:
Schedule: Daily at 2:00 AM
Command: cd /path/to/wordpress && wp env-scraper run epa-sems
```

**Via crontab:**
```bash
crontab -e

# Add:
0 2 * * * cd /var/www/html && wp env-scraper run epa-sems >> /var/log/eic-scraper.log 2>&1
```

## Part 2: Geospatial Intelligence Platform Deployment

### Step 1: Install Python Dependencies

```bash
# Create virtual environment
cd /home/ubuntu/ThrivingRoots/geospatial-intelligence
python3 -m venv venv
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt

# Or install manually:
pip install requests beautifulsoup4 lxml
```

### Step 2: Configure Data Sources

Edit `scripts/environmental_data_processor.py`:

```python
# Update data source URLs
EPA_AIR_QUALITY_URL = "https://aqs.epa.gov/..."
USGS_WATER_QUALITY_URL = "https://waterdata.usgs.gov/..."

# Set output paths
OUTPUT_DIR = "/var/www/html/wp-content/uploads/geospatial/"
```

### Step 3: Run Initial Data Processing

```bash
# Process environmental data
python scripts/environmental_data_processor.py

# Run spatial analysis
python scripts/spatial_analysis.py

# Validate data
python scripts/data_validation.py
```

### Step 4: Set Up PostGIS Database (Optional)

```bash
# Create PostGIS database
createdb -U postgres environmental_intelligence
psql -U postgres -d environmental_intelligence -c "CREATE EXTENSION postgis;"

# Import schema
psql -U postgres -d environmental_intelligence < sql/postgis_schema.sql

# Import data
psql -U postgres -d environmental_intelligence < sql/import_data.sql
```

### Step 5: Integrate with WordPress

**Option A: File-based Integration**
```bash
# Copy GeoJSON files to WordPress uploads
cp outputs/*.geojson /var/www/html/wp-content/uploads/geospatial/

# Update WordPress plugin to reference files
```

**Option B: Database Integration**
```php
// In WordPress plugin, connect to PostGIS
$conn = pg_connect("host=localhost dbname=environmental_intelligence user=postgres");
```

### Step 6: Schedule Data Updates

```bash
crontab -e

# Add:
0 3 * * * cd /home/ubuntu/ThrivingRoots/geospatial-intelligence && /usr/bin/python3 scripts/environmental_data_processor.py >> /var/log/geospatial-processor.log 2>&1
```

## Part 3: Community Resilience Module Deployment

### Step 1: Upload Plugin Files

**Via WordPress Admin:**
```
1. Navigate to Plugins > Add New
2. Click "Upload Plugin"
3. Choose community-resilience.zip
4. Click "Install Now"
5. Click "Activate Plugin"
```

**Via FTP/SSH:**
```bash
# Upload via FTP
Upload community-resilience/ to /wp-content/plugins/

# Or via SSH
cd /var/www/html/wp-content/plugins/
cp -r /home/ubuntu/ThrivingRoots/community-resilience ./
```

### Step 2: Configure Settings

```
1. Navigate to Community Resilience > Settings
2. Configure:
   - EPA AirNow API Key (optional)
   - Contact Email
   - Enable/Disable Features
3. Save settings
```

### Step 3: Seed Initial Data

The plugin automatically seeds data on activation. Verify:

```
1. Navigate to Community Resilience > LA City Resources
2. Check for pre-populated resources
3. Navigate to Community Resilience > Eaton Fire Recovery
4. Verify need categories exist
```

### Step 4: Create Pages with Shortcodes

**Create Resource Pages:**

1. **Water Quality Page**
   ```
   Title: Check Your Water Quality
   Content: [water_quality_lookup]
   ```

2. **LA Rebates Page**
   ```
   Title: LA City Rebate Programs
   Content: [la_rebate_finder]
   ```

3. **Action Plan Page**
   ```
   Title: Build Your Sustainability Plan
   Content: [action_plan_builder]
   ```

4. **Fire Recovery Page**
   ```
   Title: Eaton Fire Recovery Resources
   Content: [eaton_fire_resources]
   ```

5. **Unmet Needs Page**
   ```
   Title: Altadena's Unmet Needs
   Content: [altadena_unmet_needs]
   ```

### Step 5: Test Shortcodes

Visit each page and verify:
- [ ] Forms load correctly
- [ ] Interactive elements work
- [ ] Data displays properly
- [ ] Links function correctly

### Step 6: Customize Styling (Optional)

Add to theme's `style.css`:

```css
/* Community Resilience Custom Styles */
.water-quality-results {
    background: #f0f8ff;
    padding: 20px;
    border-radius: 10px;
}

.rebate-card {
    border: 2px solid #2c5f2d;
    transition: transform 0.3s;
}

.rebate-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
```

## Part 4: Integration & Testing

### Step 1: Test Plugin Integration

**Environmental Intelligence Core + Community Resilience:**
```
1. Create a Superfund Site
2. Add water quality data for same ZIP code
3. Verify data appears in both plugins
4. Test cross-referencing
```

**Geospatial + WordPress:**
```
1. Check if GeoJSON files are accessible
2. Test map display on frontend
3. Verify spatial queries work
4. Test risk assessment integration
```

### Step 2: Test REST API Endpoints

```bash
# Test water quality endpoint
curl https://yoursite.com/wp-json/thriving-roots/v1/water-quality/90001

# Test fire recovery resources
curl https://yoursite.com/wp-json/thriving-roots/v1/fire-recovery/resources

# Test LA City resources
curl https://yoursite.com/wp-json/thriving-roots/v1/la-resources
```

### Step 3: Test WP-CLI Commands

```bash
# Test scraper
wp env-scraper test

# Test geospatial import
wp geospatial import --file=/path/to/data.geojson

# Test data validation
wp env-data validate
```

### Step 4: Performance Testing

**Load Testing:**
```bash
# Install Apache Bench
sudo apt-get install apache2-utils

# Test homepage
ab -n 1000 -c 10 https://yoursite.com/

# Test shortcode page
ab -n 100 -c 5 https://yoursite.com/water-quality/
```

**Database Optimization:**
```sql
-- Add indexes for performance
ALTER TABLE wp_eic_data_logs ADD INDEX idx_created_at (created_at);
ALTER TABLE wp_eic_scraper_jobs ADD INDEX idx_status (status);

-- Optimize tables
OPTIMIZE TABLE wp_eic_site_relationships;
OPTIMIZE TABLE wp_eic_data_logs;
```

### Step 5: Security Hardening

**File Permissions:**
```bash
# Set correct permissions
find /var/www/html/wp-content/plugins/ -type d -exec chmod 755 {} \;
find /var/www/html/wp-content/plugins/ -type f -exec chmod 644 {} \;
```

**Security Headers:**
```apache
# Add to .htaccess
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
```

**API Rate Limiting:**
```php
// In wp-config.php
define('TR_API_RATE_LIMIT', 100); // requests per hour
define('TR_API_RATE_WINDOW', 3600); // 1 hour
```

## Part 5: Production Deployment

### Step 1: Pre-Production Checklist

- [ ] All tests passed
- [ ] Performance optimized
- [ ] Security hardened
- [ ] Backup created
- [ ] SSL configured
- [ ] Monitoring set up
- [ ] Documentation reviewed

### Step 2: Deploy to Production

**Blue-Green Deployment:**
```bash
# 1. Deploy to staging
rsync -avz --exclude 'wp-config.php' /staging/ /production-blue/

# 2. Test staging
curl https://staging.yoursite.com/health-check

# 3. Switch to production
ln -sfn /production-blue /var/www/html

# 4. Verify
curl https://yoursite.com/health-check
```

**Rolling Deployment:**
```bash
# 1. Deploy plugin updates
wp plugin update environmental-intelligence-core
wp plugin update community-resilience

# 2. Clear caches
wp cache flush
wp rewrite flush

# 3. Verify
wp plugin list
```

### Step 3: Post-Deployment Verification

**Smoke Tests:**
```bash
# Test homepage
curl -I https://yoursite.com/

# Test API endpoints
curl https://yoursite.com/wp-json/thriving-roots/v1/health

# Test shortcodes
curl https://yoursite.com/water-quality/ | grep "water-quality-lookup"
```

**Monitoring:**
```bash
# Check error logs
tail -f /var/log/apache2/error.log
tail -f /var/www/html/wp-content/debug.log

# Check scraper logs
tail -f /var/log/eic-scraper.log
```

### Step 4: Set Up Monitoring & Alerts

**Uptime Monitoring:**
- Use UptimeRobot, Pingdom, or similar
- Monitor: Homepage, API endpoints, critical pages

**Error Monitoring:**
```php
// In wp-config.php
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Set up email alerts for critical errors
```

**Performance Monitoring:**
- Use New Relic, DataDog, or similar
- Monitor: Page load times, database queries, API response times

### Step 5: Backup & Disaster Recovery

**Automated Backups:**
```bash
# Database backup
0 1 * * * mysqldump -u user -p database > /backups/db-$(date +\%Y\%m\%d).sql

# File backup
0 2 * * * tar -czf /backups/files-$(date +\%Y\%m\%d).tar.gz /var/www/html/

# Geospatial data backup
0 3 * * * tar -czf /backups/geospatial-$(date +\%Y\%m\%d).tar.gz /home/ubuntu/ThrivingRoots/geospatial-intelligence/outputs/
```

**Disaster Recovery Plan:**
1. Restore database from backup
2. Restore files from backup
3. Verify plugin activation
4. Run data validation
5. Test critical functionality
6. Update DNS if needed

## Part 6: Maintenance & Updates

### Daily Tasks
- [ ] Check error logs
- [ ] Monitor uptime
- [ ] Review scraper jobs
- [ ] Check disk space

### Weekly Tasks
- [ ] Review performance metrics
- [ ] Check for plugin updates
- [ ] Validate data integrity
- [ ] Test backups

### Monthly Tasks
- [ ] Security audit
- [ ] Database optimization
- [ ] Update documentation
- [ ] Review user feedback

### Quarterly Tasks
- [ ] Major version updates
- [ ] Feature enhancements
- [ ] Comprehensive testing
- [ ] Disaster recovery drill

## Troubleshooting

### Common Issues

**Issue: Plugin won't activate**
```
Solution:
1. Check PHP version (7.4+ required)
2. Increase memory limit in wp-config.php
3. Check file permissions
4. Review error logs
```

**Issue: Scraper not running**
```
Solution:
1. Verify WP-CLI is installed
2. Check cron jobs are configured
3. Test scraper manually
4. Review scraper logs
```

**Issue: Shortcodes not displaying**
```
Solution:
1. Verify plugin is activated
2. Check for JavaScript errors
3. Clear cache
4. Test in different theme
```

**Issue: Geospatial data not loading**
```
Solution:
1. Check file paths
2. Verify GeoJSON format
3. Test file permissions
4. Review Python logs
```

**Issue: Performance problems**
```
Solution:
1. Enable caching (WP Super Cache, W3 Total Cache)
2. Optimize database
3. Use CDN for assets
4. Increase server resources
```

## Support & Resources

### Documentation
- Main README: `/ThrivingRoots/README.md`
- Implementation Guide: `/ThrivingRoots/IMPLEMENTATION_GUIDE.md`
- Community Resilience README: `/community-resilience/README.md`
- Geospatial README: `/geospatial-intelligence/README.md`

### Community
- GitHub: https://github.com/QuantumStillness/ThrivingRoots
- Issues: https://github.com/QuantumStillness/ThrivingRoots/issues
- Email: altadenarisingnow@gmail.com (for Eaton Fire recovery)

### API Keys
- **EPA AirNow:** https://docs.airnowapi.org/
- **USGS Water Services:** https://waterservices.usgs.gov/
- **Google Maps:** https://developers.google.com/maps

## Appendix

### A. Server Configuration Examples

**Apache Virtual Host:**
```apache
<VirtualHost *:443>
    ServerName yoursite.com
    DocumentRoot /var/www/html
    
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/yoursite.crt
    SSLCertificateKeyFile /etc/ssl/private/yoursite.key
    
    <Directory /var/www/html>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

**Nginx Configuration:**
```nginx
server {
    listen 443 ssl http2;
    server_name yoursite.com;
    root /var/www/html;
    
    ssl_certificate /etc/ssl/certs/yoursite.crt;
    ssl_certificate_key /etc/ssl/private/yoursite.key;
    
    location / {
        try_files $uri $uri/ /index.php?$args;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

### B. Database Configuration

**MySQL Optimization:**
```ini
[mysqld]
max_connections = 200
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M
query_cache_size = 64M
tmp_table_size = 64M
max_heap_table_size = 64M
```

### C. PHP Configuration

**php.ini Optimization:**
```ini
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 64M
max_input_vars = 5000
```

### D. WordPress Configuration

**wp-config.php Optimization:**
```php
define('WP_MEMORY_LIMIT', '512M');
define('WP_MAX_MEMORY_LIMIT', '512M');
define('WP_CACHE', true);
define('COMPRESS_CSS', true);
define('COMPRESS_SCRIPTS', true);
define('CONCATENATE_SCRIPTS', true);
define('ENFORCE_GZIP', true);
```

---

**Deployment Guide Version:** 1.0.0  
**Last Updated:** 2025-11-25  
**Status:** Production Ready ✅

For questions or support, please visit our GitHub repository or contact the development team.
