-- ============================================================================
-- ThrivingRoots Geospatial Intelligence Platform - PostGIS Schema
-- ============================================================================
-- Version: 1.0.0
-- Purpose: Spatial database schema for environmental intelligence layers
-- Database: PostgreSQL with PostGIS extension
-- 
-- This schema extends the WordPress/MySQL database with advanced spatial
-- capabilities for complex geospatial analysis and visualization.
-- ============================================================================

-- Enable PostGIS extension
CREATE EXTENSION IF NOT EXISTS postgis;
CREATE EXTENSION IF NOT EXISTS postgis_topology;

-- ============================================================================
-- CORE SPATIAL TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: environmental_layers
-- Purpose: Store geospatial environmental data layers
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS environmental_layers (
    layer_id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    layer_name VARCHAR(255) NOT NULL,
    layer_type VARCHAR(50) NOT NULL,  -- 'air_quality', 'water_quality', 'superfund', etc.
    data_source VARCHAR(100) NOT NULL,
    source_url TEXT,
    
    -- Spatial data (supports any geometry type)
    spatial_data GEOMETRY(GEOMETRY, 4326) NOT NULL,
    
    -- Attributes as flexible JSON
    attributes JSONB NOT NULL DEFAULT '{}',
    
    -- Data provenance
    data_hash VARCHAR(64) NOT NULL,
    fetch_timestamp TIMESTAMP NOT NULL,
    
    -- Metadata
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Quality metrics
    data_quality_score FLOAT CHECK (data_quality_score >= 0 AND data_quality_score <= 1),
    validation_status VARCHAR(20) DEFAULT 'pending'  -- 'pending', 'validated', 'flagged'
);

-- Indexes for performance
CREATE INDEX idx_env_layers_geom ON environmental_layers USING GIST(spatial_data);
CREATE INDEX idx_env_layers_type ON environmental_layers(layer_type);
CREATE INDEX idx_env_layers_source ON environmental_layers(data_source);
CREATE INDEX idx_env_layers_active ON environmental_layers(is_active);
CREATE INDEX idx_env_layers_attrs ON environmental_layers USING GIN(attributes);

-- ----------------------------------------------------------------------------
-- Table: analysis_results
-- Purpose: Store results of spatial analysis operations
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS analysis_results (
    analysis_id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    analysis_type VARCHAR(50) NOT NULL,  -- 'risk_assessment', 'buffer', 'proximity', etc.
    analysis_name VARCHAR(255),
    
    -- Input layers (array of UUIDs)
    input_layers UUID[] NOT NULL,
    
    -- Result geometry
    result_data GEOMETRY(GEOMETRY, 4326),
    
    -- Risk scores and metrics
    risk_scores JSONB DEFAULT '{}',
    metrics JSONB DEFAULT '{}',
    
    -- Analysis parameters
    parameters JSONB DEFAULT '{}',
    
    -- Metadata
    generated_at TIMESTAMP DEFAULT NOW(),
    generated_by VARCHAR(100),
    
    -- Quality and validation
    confidence_level FLOAT CHECK (confidence_level >= 0 AND confidence_level <= 1),
    validation_notes TEXT
);

-- Indexes
CREATE INDEX idx_analysis_geom ON analysis_results USING GIST(result_data);
CREATE INDEX idx_analysis_type ON analysis_results(analysis_type);
CREATE INDEX idx_analysis_generated ON analysis_results(generated_at);
CREATE INDEX idx_analysis_scores ON analysis_results USING GIN(risk_scores);

-- ----------------------------------------------------------------------------
-- Table: priority_areas
-- Purpose: Store prioritized areas for remediation and intervention
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS priority_areas (
    area_id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    area_name VARCHAR(255) NOT NULL,
    
    -- Location
    area_geometry GEOMETRY(POLYGON, 4326) NOT NULL,
    centroid GEOMETRY(POINT, 4326),
    
    -- Priority metrics
    priority_rank INTEGER NOT NULL,
    priority_score FLOAT NOT NULL,
    composite_risk FLOAT NOT NULL CHECK (composite_risk >= 0 AND composite_risk <= 1),
    
    -- Risk factors
    risk_factors JSONB DEFAULT '{}',
    
    -- Demographics
    population_estimate INTEGER,
    demographic_vulnerability FLOAT CHECK (demographic_vulnerability >= 0 AND demographic_vulnerability <= 1),
    
    -- Environmental factors
    superfund_proximity_km FLOAT,
    air_quality_index INTEGER,
    water_quality_score FLOAT,
    
    -- Recommended actions
    recommended_actions JSONB DEFAULT '[]',
    
    -- Status tracking
    status VARCHAR(50) DEFAULT 'identified',  -- 'identified', 'under_review', 'active', 'completed'
    assigned_to VARCHAR(100),
    
    -- Metadata
    identified_at TIMESTAMP DEFAULT NOW(),
    last_updated TIMESTAMP DEFAULT NOW()
);

-- Indexes
CREATE INDEX idx_priority_geom ON priority_areas USING GIST(area_geometry);
CREATE INDEX idx_priority_centroid ON priority_areas USING GIST(centroid);
CREATE INDEX idx_priority_rank ON priority_areas(priority_rank);
CREATE INDEX idx_priority_status ON priority_areas(status);

-- ----------------------------------------------------------------------------
-- Table: spatial_relationships
-- Purpose: Store complex spatial relationships between features
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS spatial_relationships (
    relationship_id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    
    -- Source and target features
    source_layer_id UUID REFERENCES environmental_layers(layer_id),
    target_layer_id UUID REFERENCES environmental_layers(layer_id),
    
    -- Relationship type
    relationship_type VARCHAR(50) NOT NULL,  -- 'within', 'intersects', 'near', 'upstream', etc.
    
    -- Spatial metrics
    distance_km FLOAT,
    overlap_area_sqkm FLOAT,
    
    -- Relationship strength/confidence
    confidence FLOAT CHECK (confidence >= 0 AND confidence <= 1),
    
    -- Additional attributes
    attributes JSONB DEFAULT '{}',
    
    -- Metadata
    calculated_at TIMESTAMP DEFAULT NOW()
);

-- Indexes
CREATE INDEX idx_spatial_rel_source ON spatial_relationships(source_layer_id);
CREATE INDEX idx_spatial_rel_target ON spatial_relationships(target_layer_id);
CREATE INDEX idx_spatial_rel_type ON spatial_relationships(relationship_type);

-- ============================================================================
-- VIEWS FOR COMMON QUERIES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- View: high_risk_areas
-- Purpose: Quick access to high-priority risk areas
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW high_risk_areas AS
SELECT 
    area_id,
    area_name,
    ST_AsGeoJSON(area_geometry) as geometry_geojson,
    ST_AsGeoJSON(centroid) as centroid_geojson,
    priority_rank,
    priority_score,
    composite_risk,
    risk_factors,
    recommended_actions,
    status
FROM priority_areas
WHERE composite_risk >= 0.7 AND is_active = TRUE
ORDER BY priority_rank;

-- ----------------------------------------------------------------------------
-- View: active_environmental_layers
-- Purpose: All active environmental data layers with GeoJSON output
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW active_environmental_layers AS
SELECT 
    layer_id,
    layer_name,
    layer_type,
    data_source,
    ST_AsGeoJSON(spatial_data) as geometry_geojson,
    attributes,
    data_quality_score,
    created_at
FROM environmental_layers
WHERE is_active = TRUE
ORDER BY created_at DESC;

-- ============================================================================
-- SPATIAL ANALYSIS FUNCTIONS
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Function: calculate_buffer_zone
-- Purpose: Create buffer zone around a geometry
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION calculate_buffer_zone(
    input_geom GEOMETRY,
    buffer_distance_km FLOAT
) RETURNS GEOMETRY AS $$
BEGIN
    -- Convert km to degrees (approximate at equator)
    -- 1 degree ≈ 111 km
    RETURN ST_Buffer(input_geom, buffer_distance_km / 111.0);
END;
$$ LANGUAGE plpgsql IMMUTABLE;

-- ----------------------------------------------------------------------------
-- Function: find_features_within_distance
-- Purpose: Find all features within specified distance of a point
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION find_features_within_distance(
    target_point GEOMETRY,
    search_distance_km FLOAT,
    feature_type VARCHAR DEFAULT NULL
) RETURNS TABLE (
    layer_id UUID,
    layer_name VARCHAR,
    distance_km FLOAT,
    attributes JSONB
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        el.layer_id,
        el.layer_name,
        ST_Distance(el.spatial_data::geography, target_point::geography) / 1000.0 AS distance_km,
        el.attributes
    FROM environmental_layers el
    WHERE 
        ST_DWithin(el.spatial_data::geography, target_point::geography, search_distance_km * 1000)
        AND el.is_active = TRUE
        AND (feature_type IS NULL OR el.layer_type = feature_type)
    ORDER BY distance_km;
END;
$$ LANGUAGE plpgsql STABLE;

-- ----------------------------------------------------------------------------
-- Function: calculate_environmental_risk
-- Purpose: Calculate composite environmental risk for a location
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION calculate_environmental_risk(
    location_point GEOMETRY,
    demographic_vuln FLOAT DEFAULT 0.5
) RETURNS JSONB AS $$
DECLARE
    superfund_risk FLOAT;
    air_risk FLOAT;
    water_risk FLOAT;
    composite_risk FLOAT;
    result JSONB;
BEGIN
    -- Calculate proximity to Superfund sites
    SELECT COALESCE(1.0 - MIN(ST_Distance(spatial_data::geography, location_point::geography) / 1000.0) / 10.0, 0)
    INTO superfund_risk
    FROM environmental_layers
    WHERE layer_type = 'superfund_sites' AND is_active = TRUE;
    
    superfund_risk := GREATEST(0, LEAST(1, superfund_risk));
    
    -- Calculate air quality risk
    SELECT COALESCE((attributes->>'aqi')::FLOAT / 200.0, 0.5)
    INTO air_risk
    FROM environmental_layers
    WHERE layer_type = 'air_quality' AND is_active = TRUE
    ORDER BY ST_Distance(spatial_data, location_point)
    LIMIT 1;
    
    air_risk := GREATEST(0, LEAST(1, air_risk));
    
    -- Calculate water quality risk
    water_risk := 0.3;  -- Default placeholder
    
    -- Composite calculation
    composite_risk := (superfund_risk * 0.3 + air_risk * 0.3 + water_risk * 0.2 + demographic_vuln * 0.2);
    
    -- Build result JSON
    result := jsonb_build_object(
        'composite_risk', composite_risk,
        'superfund_risk', superfund_risk,
        'air_risk', air_risk,
        'water_risk', water_risk,
        'demographic_vulnerability', demographic_vuln,
        'category', CASE 
            WHEN composite_risk >= 0.7 THEN 'High Risk'
            WHEN composite_risk >= 0.4 THEN 'Moderate Risk'
            ELSE 'Low Risk'
        END
    );
    
    RETURN result;
END;
$$ LANGUAGE plpgsql STABLE;

-- ============================================================================
-- TRIGGERS FOR AUTOMATIC UPDATES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Trigger: Update timestamp on environmental_layers
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION update_modified_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER env_layers_update_timestamp
BEFORE UPDATE ON environmental_layers
FOR EACH ROW
EXECUTE FUNCTION update_modified_timestamp();

-- ----------------------------------------------------------------------------
-- Trigger: Auto-calculate centroid for priority_areas
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION calculate_centroid()
RETURNS TRIGGER AS $$
BEGIN
    NEW.centroid = ST_Centroid(NEW.area_geometry);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER priority_areas_calculate_centroid
BEFORE INSERT OR UPDATE ON priority_areas
FOR EACH ROW
EXECUTE FUNCTION calculate_centroid();

-- ============================================================================
-- SAMPLE DATA INSERTION (for testing)
-- ============================================================================

-- Insert sample environmental layer
INSERT INTO environmental_layers (
    layer_name,
    layer_type,
    data_source,
    source_url,
    spatial_data,
    attributes,
    data_hash,
    fetch_timestamp,
    data_quality_score
) VALUES (
    'Los Angeles Air Quality Station',
    'air_quality',
    'EPA AirNow',
    'https://www.airnowapi.org',
    ST_SetSRID(ST_MakePoint(-118.2437, 34.0522), 4326),
    '{"aqi": 105, "pm25": 45.2, "category": "Unhealthy for Sensitive Groups"}'::jsonb,
    'sample_hash_123',
    NOW(),
    0.85
);

-- ============================================================================
-- UTILITY QUERIES
-- ============================================================================

-- Check PostGIS version
-- SELECT PostGIS_Version();

-- List all spatial tables
-- SELECT f_table_name, f_geometry_column, srid, type 
-- FROM geometry_columns;

-- Count features by type
-- SELECT layer_type, COUNT(*) 
-- FROM environmental_layers 
-- GROUP BY layer_type;

-- ============================================================================
-- MAINTENANCE COMMANDS
-- ============================================================================

-- Vacuum and analyze for performance
-- VACUUM ANALYZE environmental_layers;
-- VACUUM ANALYZE analysis_results;
-- VACUUM ANALYZE priority_areas;

-- Rebuild spatial indexes
-- REINDEX INDEX idx_env_layers_geom;
-- REINDEX INDEX idx_analysis_geom;
-- REINDEX INDEX idx_priority_geom;

-- ============================================================================
-- End of Schema
-- ============================================================================
