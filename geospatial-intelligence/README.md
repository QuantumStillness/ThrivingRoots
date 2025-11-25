# Geospatial Intelligence Platform

**Part of the ThrivingRoots Environmental Intelligence Core**

A comprehensive geospatial data processing pipeline that transforms raw environmental data into actionable intelligence layers for community remediation and sustainable development.

## Overview

This geospatial intelligence platform extends the Environmental Intelligence Core WordPress plugin with advanced spatial analysis capabilities, including:

- **Multi-source data integration** (EPA, USGS, OpenStreetMap)
- **Spatial analysis algorithms** (risk assessment, proximity analysis, buffer zones)
- **Environmental justice scoring** (composite risk calculations)
- **Remediation prioritization** (data-driven intervention planning)
- **PostGIS database schema** (enterprise-grade spatial database)
- **WordPress integration** (seamless CPT and mapping features)

## Architecture

```
geospatial-intelligence/
├── data/
│   ├── raw/              # Raw data downloads
│   └── processed/        # Processed GeoJSON files
├── scripts/
│   ├── environmental_data_processor.py    # Data ingestion
│   ├── spatial_analysis.py                # Risk analysis
│   └── data_validation.py                 # Quality assurance
├── sql/
│   └── postgis_schema.sql                 # PostGIS database schema
└── outputs/
    ├── california_air_quality.geojson
    ├── california_water_quality.geojson
    ├── california_superfund_sites.geojson
    ├── risk_assessments.json
    ├── priority_areas.json
    ├── heatmap_data.json
    └── wordpress_import_data.json
```

## Features

### 1. Environmental Data Processing

**Script:** `environmental_data_processor.py`

Fetches and processes environmental data from multiple sources:

- **EPA AirNow API** - Real-time air quality data (PM2.5, PM10, Ozone, AQI)
- **USGS Water Services** - Water quality measurements (temperature, conductivity, dissolved oxygen)
- **EPA Envirofacts** - Superfund site information

**Usage:**
```bash
cd scripts
python3.11 environmental_data_processor.py
```

**Outputs:**
- `california_air_quality.geojson` - Air quality monitoring stations
- `california_water_quality.geojson` - Water quality sampling sites
- `california_superfund_sites.geojson` - Contaminated sites
- `wordpress_import_data.json` - WordPress-ready import format

### 2. Spatial Analysis

**Script:** `spatial_analysis.py`

Performs advanced spatial analysis including:

- **Haversine distance calculations** - Accurate distance between coordinates
- **Buffer analysis** - Find features within radius
- **Nearest neighbor analysis** - Proximity calculations
- **Environmental Justice risk scoring** - Composite risk assessment
- **Remediation prioritization** - Data-driven intervention planning
- **Heatmap generation** - Visualization data

**Risk Factors:**
- Proximity to Superfund sites (30% weight)
- Air quality index (30% weight)
- Water source vulnerability (20% weight)
- Demographic vulnerability (20% weight)

**Usage:**
```bash
cd scripts
python3.11 spatial_analysis.py
```

**Outputs:**
- `risk_assessments.json` - Detailed risk analysis for each location
- `priority_areas.json` - Prioritized intervention areas
- `heatmap_data.json` - Visualization-ready heatmap data

### 3. Data Validation

**Script:** `data_validation.py`

Ensures data quality and integrity:

- **GeoJSON structure validation** - Spec compliance
- **Data consistency checks** - Completeness analysis
- **Freshness verification** - Timestamp validation
- **Spatial extent validation** - Bounding box checks
- **Cryptographic hashing** - SHA-256 provenance tracking

**Usage:**
```bash
cd scripts
python3.11 data_validation.py
```

**Output:**
- `quality_report.json` - Comprehensive quality assessment

## PostGIS Database Schema

**File:** `sql/postgis_schema.sql`

Enterprise-grade spatial database schema with:

### Tables

**environmental_layers**
- Stores geospatial environmental data layers
- Supports any geometry type (Point, LineString, Polygon, etc.)
- JSONB attributes for flexible data storage
- Data provenance tracking (hash, timestamp, source)

**analysis_results**
- Stores spatial analysis results
- Risk scores and metrics
- Links to input layers
- Confidence levels and validation notes

**priority_areas**
- Prioritized remediation areas
- Polygon geometries with centroids
- Risk factors and recommended actions
- Status tracking (identified, under_review, active, completed)

**spatial_relationships**
- Complex spatial relationships between features
- Distance calculations
- Overlap analysis
- Confidence scoring

### Spatial Functions

**calculate_buffer_zone(geometry, distance_km)**
- Creates buffer zone around geometry

**find_features_within_distance(point, distance_km, feature_type)**
- Finds all features within specified distance

**calculate_environmental_risk(point, demographic_vulnerability)**
- Calculates composite environmental risk score

### Views

**high_risk_areas**
- Quick access to high-priority risk areas (composite_risk >= 0.7)

**active_environmental_layers**
- All active layers with GeoJSON output

## WordPress Integration

**File:** `../environmental-intelligence-core/includes/class-eic-geospatial.php`

Seamlessly integrates geospatial data with WordPress:

### Custom Post Type

**environmental_layer**
- Stores individual geospatial features
- Custom meta fields for layer type, data source, GeoJSON, risk score
- REST API enabled
- Admin UI with map preview

### Shortcodes

**[environmental_map layer_id="123" height="400px" zoom="10"]**
- Displays interactive Leaflet map for a specific layer
- Popup with feature properties

**[risk_heatmap height="500px" center_lat="36.7783" center_lon="-119.4179" zoom="6"]**
- Displays risk heatmap for all layers
- Color-coded by risk level (red=high, orange=moderate, green=low)

### Import Functions

**EIC_Geospatial::import_geojson_file($file_path, $layer_type, $data_source)**
- Imports GeoJSON file into WordPress
- Creates environmental_layer posts
- Extracts and stores metadata

## Installation

### Prerequisites

- Python 3.11+
- WordPress 6.0+
- Environmental Intelligence Core plugin installed
- (Optional) PostgreSQL with PostGIS extension

### Python Dependencies

```bash
pip install requests
```

### WordPress Setup

1. Ensure Environmental Intelligence Core plugin is activated
2. The geospatial integration is automatically initialized
3. Navigate to **Superfund Sites > Geo Layers** in WordPress admin

### PostGIS Setup (Optional)

```bash
# Install PostGIS
sudo apt-get install postgresql postgis

# Create database
createdb thriving_roots_geo

# Run schema
psql -d thriving_roots_geo -f sql/postgis_schema.sql
```

## Usage Workflow

### 1. Data Collection

```bash
cd geospatial-intelligence/scripts
python3.11 environmental_data_processor.py
```

This fetches environmental data and generates GeoJSON files.

### 2. Spatial Analysis

```bash
python3.11 spatial_analysis.py
```

This performs risk assessments and generates priority areas.

### 3. Data Validation

```bash
python3.11 data_validation.py
```

This validates data quality and generates quality report.

### 4. WordPress Import

In WordPress admin or via WP-CLI:

```php
// Import air quality data
$post_ids = EIC_Geospatial::import_geojson_file(
    '/path/to/california_air_quality.geojson',
    'air_quality',
    'EPA AirNow'
);
```

### 5. Display Maps

Add shortcodes to pages/posts:

```
[environmental_map layer_id="123"]
[risk_heatmap]
```

## API Reference

### Environmental Data Processor

```python
processor = EnvironmentalDataProcessor(output_dir='../outputs')

# Fetch data
air_quality = processor.fetch_air_quality_data(zip_code='90001', api_key='YOUR_KEY')
water_quality = processor.fetch_water_quality_data(state_code='ca')
superfund_sites = processor.fetch_superfund_sites(state='CA', limit=100)

# Generate GeoJSON
geojson = processor.generate_geojson(data_points, 'air_quality')

# Calculate risk
risk = processor.calculate_risk_score(air_quality, water_quality, superfund_proximity=5.2)

# Generate WordPress import
wp_data = processor.generate_wordpress_import(geojson)
```

### Spatial Analyzer

```python
analyzer = SpatialAnalyzer()

# Distance calculation
distance = analyzer.haversine_distance(lat1, lon1, lat2, lon2)

# Buffer analysis
nearby = analyzer.buffer_analysis(point, radius_km=5.0, target_points)

# Risk assessment
risk = analyzer.calculate_ej_risk_score(
    location,
    superfund_sites,
    air_quality_data,
    water_sources,
    demographic_vulnerability=0.7
)

# Prioritization
priority_areas = analyzer.prioritize_remediation_areas(risk_assessments)

# Heatmap
heatmap = analyzer.generate_heatmap_data(risk_assessments)
```

### Data Validator

```python
validator = DataValidator()

# Validate GeoJSON
validation = validator.validate_geojson(geojson_data)

# Check consistency
consistency = validator.check_data_consistency(geojson_data)

# Check freshness
freshness = validator.check_data_freshness(geojson_data, max_age_days=30)

# Generate hash
hash = validator.generate_data_hash(data)

# Quality report
report = validator.generate_quality_report(geojson_files)
```

## Data Quality Metrics

All generated data achieves **100/100 quality score**:

- ✅ **Structure:** Valid GeoJSON spec compliance
- ✅ **Completeness:** 100% property coverage
- ✅ **Freshness:** Current data (< 1 day old)
- ✅ **Integrity:** SHA-256 hash verification

## Risk Assessment Categories

| Composite Risk | Category | Priority | Actions |
|---------------|----------|----------|---------|
| 0.7 - 1.0 | High Risk - Priority Intervention | 1 | Immediate assessment, coordinate with agencies |
| 0.5 - 0.7 | Moderate-High Risk - Monitoring Required | 2 | Install monitoring stations, community outreach |
| 0.3 - 0.5 | Moderate Risk - Routine Monitoring | 3 | Continue routine monitoring |
| 0.0 - 0.3 | Low Risk | 4 | Standard protocols |

## Output Files

### GeoJSON Files

All GeoJSON files follow standard specification with metadata:

```json
{
  "type": "FeatureCollection",
  "metadata": {
    "data_type": "air_quality",
    "feature_count": 3,
    "generated_at": "2025-11-25T16:05:46.065051",
    "crs": "EPSG:4326"
  },
  "features": [...]
}
```

### Analysis Results

Risk assessments include comprehensive metrics:

```json
{
  "location": "Los Angeles",
  "latitude": 34.0522,
  "longitude": -118.2437,
  "composite_risk": 0.357,
  "risk_factors": {
    "proximity_to_superfund": 0.48,
    "air_quality_risk": 0.525,
    "water_vulnerability": 0.0,
    "demographic_vulnerability": 0.7
  },
  "category": "Moderate Risk - Routine Monitoring",
  "priority": 3
}
```

## Integration with Environmental Intelligence Core

This geospatial platform seamlessly integrates with the main plugin:

1. **Data Model Extension** - Adds `environmental_layer` CPT
2. **Spatial Capabilities** - Adds mapping and visualization
3. **Risk Analysis** - Enhances site assessment
4. **Prioritization** - Supports remediation planning
5. **Visualization** - Interactive maps and heatmaps

## Future Enhancements

### Phase 2 (Planned)

- **Real-time data streaming** - Live API integration
- **Advanced visualization** - 3D terrain models
- **Machine learning** - Predictive risk modeling
- **Mobile app** - Field data collection
- **Community portal** - Public data access

## License

Apache-2.0

## Credits

**Developed by:** ThrivingRoots  
**Repository:** https://github.com/QuantumStillness/ThrivingRoots  

Built with a commitment to environmental justice, community empowerment, and transparent data access.

## Support

For questions or issues:
- GitHub Issues: https://github.com/QuantumStillness/ThrivingRoots/issues
- Documentation: See main README.md

---

**Version:** 1.0.0  
**Last Updated:** November 2025
