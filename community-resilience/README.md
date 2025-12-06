# ThrivingRoots Community Resilience

**WordPress Plugin for Community Empowerment, Material Safety, and Fire Recovery**

A comprehensive platform providing material safety databases, sustainable living resources, and Eaton Fire recovery tools for communities impacted by environmental disasters.

## Overview

The Community Resilience plugin extends the ThrivingRoots Environmental Intelligence Platform with:

- **Material Safety Databases** - Water quality and chemical safety information (EWG, UNECE)
- **LA City Resources** - Environmental programs, rebates, and classes
- **Action Tools** - Sustainable living guides and personal action plan builders
- **Eaton Fire Recovery** - Comprehensive resources for Altadena and surrounding communities

## Features

### 1. Material Safety Database Integration

**Water Quality Lookup**
- EWG Tap Water Database integration
- ZIP code-based water quality reports
- Contaminant identification and health effects
- Filter recommendations

**Chemical Safety Information**
- UNECE PRTR database integration
- CAS number lookup
- Hazard classification
- Exposure limits and first aid

**Shortcodes:**
- `[water_quality_lookup zip="90001"]`
- `[chemical_safety_info cas="50-00-0"]`

### 2. LA City Environmental Resources

**Rebate Programs**
- LADWP Turf Replacement Program ($3/sq ft)
- Water-Saving Devices Rebates
- LASAN Sewer Repair Financial Assistance (up to $6,000)
- Solar Incentive Program ($0.20/watt)

**Free Resources**
- City Plants (free trees)
- Free water-saving devices
- Environmental education classes

**Shortcodes:**
- `[la_rebate_finder]`
- `[la_resource_directory category="water"]`
- `[la_environmental_classes]`

### 3. Community Action Tools

**Action Plan Builder**
- Personalized sustainability plans
- Goal-based recommendations
- Time and budget considerations
- Printable action plans

**Sustainable Living Guide**
- Water conservation tips
- Energy efficiency strategies
- Waste reduction practices
- Urban gardening guidance
- Mindful eating resources

**Shortcodes:**
- `[action_plan_builder]`
- `[sustainable_living_guide]`
- `[mindful_eating_resources]`
- `[community_garden_finder zip="90001"]`

### 4. Eaton Fire Recovery Resources

**Based on "Altadena's Unmet Needs" Report (2025)**

**54 Needs Across 8 Categories:**
1. **Care Management** (5 needs)
   - Elder support
   - Displaced family assistance
   - Under-networked family outreach
   - Long-term case management
   - Real-time resource information

2. **Essential Needs** (3 needs)
   - Distribution calendar
   - No-line access
   - Mail forwarding

3. **Rehousing & Financial** (16 needs)
   - Stable housing (12+ months)
   - Insurance access
   - Direct cash assistance
   - Financial literacy

4. **Land & Rebuilding** (7 needs)
   - Rebuild cost coverage
   - Community land control
   - Expedited permits

5. **Community Support** (15 needs)
   - Youth development
   - Mental health services
   - Historical education
   - Leadership trust

6. **Environmental Safety** (1 need)
   - Safe return and restoration
   - Air/water/soil testing
   - Health screenings

7. **Worker Protection** (1 need)
   - Health and safety for recovery workers

8. **Fire Systems** (1 need)
   - Accountability and prevention

**Shortcodes:**
- `[eaton_fire_resources]`
- `[altadena_unmet_needs]`
- `[fire_recovery_tracker]`
- `[environmental_safety_post_fire]`

## Installation

### Requirements
- WordPress 6.0+
- PHP 7.4+
- Environmental Intelligence Core plugin (recommended)

### Installation Steps

1. **Upload Plugin**
   ```bash
   # Via WordPress admin
   Plugins > Add New > Upload Plugin > Choose community-resilience.zip
   
   # Or via FTP
   Upload community-resilience/ to /wp-content/plugins/
   ```

2. **Activate Plugin**
   ```
   Plugins > Installed Plugins > Activate "ThrivingRoots Community Resilience"
   ```

3. **Configure Settings**
   ```
   Community Resilience > Settings
   - Enter EPA AirNow API key (optional)
   - Set contact email
   - Enable/disable features
   ```

4. **Seed Initial Data**
   ```
   Plugin automatically seeds:
   - LA City resources
   - Fire recovery categories
   - Resource taxonomies
   ```

## Usage

### For Site Administrators

**Admin Dashboard**
- Navigate to **Community Resilience** in WordPress admin
- View statistics and manage resources
- Access all modules from central dashboard

**Material Safety**
- Import water quality data via CSV
- Manage chemical safety entries
- View recent data entries

**LA City Resources**
- Add/edit city resources
- Categorize by program type
- Track resource providers

**Fire Recovery**
- Add recovery resources
- Track unmet needs
- Monitor status updates

### For Content Creators

**Adding Shortcodes to Pages/Posts**

1. **Water Quality Lookup**
   ```
   [water_quality_lookup]
   ```
   Displays interactive form for ZIP code lookup

2. **LA Rebate Finder**
   ```
   [la_rebate_finder]
   ```
   Shows all available rebate programs with filtering

3. **Action Plan Builder**
   ```
   [action_plan_builder]
   ```
   Interactive form to create personalized sustainability plans

4. **Eaton Fire Resources**
   ```
   [eaton_fire_resources]
   ```
   Comprehensive fire recovery resource directory

5. **Altadena Unmet Needs**
   ```
   [altadena_unmet_needs]
   ```
   Full unmet needs report with 54 needs

6. **Environmental Safety Post-Fire**
   ```
   [environmental_safety_post_fire]
   ```
   Safety guidelines for returning home after wildfire

### For Developers

**REST API Endpoints**

```php
// Water quality by ZIP code
GET /wp-json/thriving-roots/v1/water-quality/90001

// Chemical safety by CAS number
GET /wp-json/thriving-roots/v1/chemical-safety/50-00-0

// LA City resources
GET /wp-json/thriving-roots/v1/la-resources?category=water

// LA rebates
GET /wp-json/thriving-roots/v1/la-rebates

// Fire recovery resources
GET /wp-json/thriving-roots/v1/fire-recovery/resources

// Unmet needs
GET /wp-json/thriving-roots/v1/fire-recovery/needs?category=care_management
```

**Custom Post Types**

- `water_quality_data` - Water quality entries
- `chemical_safety` - Chemical safety data
- `la_city_resource` - LA City resources
- `action_plan` - User action plans
- `sustainable_practice` - Sustainable practices
- `fire_recovery_resource` - Fire recovery resources

**Taxonomies**

- `resource_category` - Resource categories (hierarchical)
- `resource_tag` - Resource tags (flat)
- `need_category` - Fire recovery need categories
- `resource_status` - Resource status (Unmet, In Progress, Being Met, Met)

## Shortcode Reference

| Shortcode | Description | Parameters |
|-----------|-------------|------------|
| `[water_quality_lookup]` | Water quality lookup form | `zip` (optional) |
| `[chemical_safety_info]` | Chemical safety lookup | `cas` or `name` (optional) |
| `[la_rebate_finder]` | LA City rebate programs | None |
| `[la_resource_directory]` | LA City resources | `category` (optional) |
| `[la_environmental_classes]` | Free environmental classes | None |
| `[action_plan_builder]` | Personal action plan builder | None |
| `[sustainable_living_guide]` | Sustainable living guide | None |
| `[mindful_eating_resources]` | Mindful eating resources | None |
| `[community_garden_finder]` | Community garden finder | `zip` (optional) |
| `[eaton_fire_resources]` | Eaton Fire recovery resources | None |
| `[altadena_unmet_needs]` | Altadena unmet needs report | None |
| `[fire_recovery_tracker]` | Recovery progress tracker | None |
| `[environmental_safety_post_fire]` | Post-fire environmental safety | None |

## Data Sources

### Material Safety
- **EWG Tap Water Database** - https://www.ewg.org/tapwater/
- **UNECE PRTR** - https://prtr.unece.org/

### LA City Resources
- **LADWP** - https://www.ladwp.com
- **LASAN** - https://www.lacitysan.org
- **City Plants** - https://www.cityplants.org

### Fire Recovery
- **Altadena Rising** - altadenarisingnow@gmail.com
- **"Altadena's Unmet Needs" Report** - Collab and Care Report 2025
- **42+ Collaborating Organizations**

## API Keys (Optional)

### EPA AirNow API
For real-time air quality data:
1. Register at https://docs.airnowapi.org/
2. Get API key
3. Enter in **Community Resilience > Settings**

### EWG Tap Water Database
Currently uses cached/sample data. For production:
- Contact EWG for API access
- Or implement web scraping (check terms of service)

## Customization

### Custom Styles

Add to your theme's `style.css`:

```css
/* Water quality results */
.water-quality-results {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 10px;
}

/* Rebate cards */
.rebate-card {
    border: 2px solid #2c5f2d;
    transition: transform 0.3s;
}

.rebate-card:hover {
    transform: translateY(-5px);
}

/* Fire recovery resources */
.eaton-fire-resources h2 {
    color: #d32f2f;
}
```

### Custom JavaScript

Add to your theme's `functions.php`:

```php
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script(
        'my-custom-resilience',
        get_stylesheet_directory_uri() . '/js/custom-resilience.js',
        array('tr-community-resilience'),
        '1.0.0',
        true
    );
});
```

## Integration with Environmental Intelligence Core

This plugin seamlessly integrates with the Environmental Intelligence Core:

1. **Shared Data Model** - Uses same taxonomies and meta fields
2. **Geospatial Data** - Links to environmental layers
3. **Superfund Sites** - Connects water quality to contamination sources
4. **Risk Assessment** - Enhances with material safety data

## Community Engagement

### For Organizations

**Add Your Resources**
1. Contact: altadenarisingnow@gmail.com
2. Submit resource information
3. Get listed in directory

**Track Unmet Needs**
1. Review Altadena's Unmet Needs
2. Identify gaps you can fill
3. Report progress

### For Community Members

**Get Help**
- Browse resources by category
- Use shortcodes to find assistance
- Contact organizations directly

**Take Action**
- Build personal action plan
- Join community gardens
- Apply for rebates and programs
- Share resources with neighbors

## Support

**Documentation**
- Main README: `/ThrivingRoots/README.md`
- Implementation Guide: `/ThrivingRoots/IMPLEMENTATION_GUIDE.md`

**Contact**
- GitHub: https://github.com/QuantumStillness/ThrivingRoots
- Email: altadenarisingnow@gmail.com (for Eaton Fire recovery)

**Report Issues**
- GitHub Issues: https://github.com/QuantumStillness/ThrivingRoots/issues

## Roadmap

### Phase 2 (Planned)
- **Real-time API Integration** - Live data from EWG, UNECE
- **Mobile App** - Field data collection
- **SMS Alerts** - Water quality and air quality alerts
- **Multi-language Support** - Spanish, Mandarin, Korean

### Phase 3 (Future)
- **Community Portal** - User accounts and saved plans
- **Resource Matching** - AI-powered resource recommendations
- **Impact Tracking** - Measure community resilience improvements
- **Grant Management** - Track funding and distribution

## Credits

**Developed by:** ThrivingRoots  
**License:** Apache-2.0  
**Repository:** https://github.com/QuantumStillness/ThrivingRoots

**Based on:**
- "Altadena's Unmet Needs" - A Collab and Care Report 2025
- 42+ collaborating organizations in Altadena

**Data Sources:**
- EWG (Environmental Working Group)
- UNECE (United Nations Economic Commission for Europe)
- LA City (LADWP, LASAN, City Plants)

## License

Apache License 2.0 - See LICENSE file for details

## Changelog

### Version 1.0.0 (2025-11-25)
- Initial release
- Material safety database integration
- LA City resources integration
- Community action tools
- Eaton Fire recovery platform
- 12 shortcodes
- REST API endpoints
- Admin dashboard

---

**Built with a commitment to environmental justice, community empowerment, and transparent data access.**

For questions or contributions, please visit our GitHub repository or contact the development team.
