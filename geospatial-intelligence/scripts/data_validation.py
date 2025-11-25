#!/usr/bin/env python3
"""
Data Validation and Quality Assurance
Validates geospatial data integrity and generates quality reports
"""

import json
import hashlib
from datetime import datetime
from typing import Dict, List, Any, Tuple


class DataValidator:
    """Validate and ensure quality of geospatial environmental data"""
    
    def __init__(self):
        self.validation_results = []
    
    def validate_geojson(self, geojson_data: Dict) -> Dict:
        """
        Validate GeoJSON structure and content
        
        Args:
            geojson_data: GeoJSON object to validate
        
        Returns:
            Validation result dictionary
        """
        errors = []
        warnings = []
        
        # Check type
        if 'type' not in geojson_data:
            errors.append('Missing "type" field')
        elif geojson_data['type'] not in ['Feature', 'FeatureCollection']:
            errors.append(f'Invalid type: {geojson_data["type"]}')
        
        # Validate FeatureCollection
        if geojson_data.get('type') == 'FeatureCollection':
            if 'features' not in geojson_data:
                errors.append('FeatureCollection missing "features" array')
            else:
                features = geojson_data['features']
                if not isinstance(features, list):
                    errors.append('"features" must be an array')
                else:
                    for i, feature in enumerate(features):
                        feature_errors = self._validate_feature(feature)
                        if feature_errors:
                            errors.append(f'Feature {i}: {", ".join(feature_errors)}')
        
        # Validate single Feature
        elif geojson_data.get('type') == 'Feature':
            feature_errors = self._validate_feature(geojson_data)
            errors.extend(feature_errors)
        
        # Check CRS
        if 'crs' in geojson_data:
            warnings.append('CRS field present (deprecated in GeoJSON spec)')
        
        return {
            'valid': len(errors) == 0,
            'errors': errors,
            'warnings': warnings,
            'timestamp': datetime.now().isoformat()
        }
    
    def _validate_feature(self, feature: Dict) -> List[str]:
        """Validate a single GeoJSON feature"""
        errors = []
        
        if 'type' not in feature or feature['type'] != 'Feature':
            errors.append('Invalid or missing feature type')
        
        if 'geometry' not in feature:
            errors.append('Missing geometry')
        else:
            geom_errors = self._validate_geometry(feature['geometry'])
            errors.extend(geom_errors)
        
        if 'properties' not in feature:
            errors.append('Missing properties')
        elif not isinstance(feature['properties'], dict):
            errors.append('Properties must be an object')
        
        return errors
    
    def _validate_geometry(self, geometry: Dict) -> List[str]:
        """Validate geometry object"""
        errors = []
        
        if 'type' not in geometry:
            errors.append('Geometry missing type')
            return errors
        
        geom_type = geometry['type']
        valid_types = ['Point', 'LineString', 'Polygon', 'MultiPoint', 
                      'MultiLineString', 'MultiPolygon', 'GeometryCollection']
        
        if geom_type not in valid_types:
            errors.append(f'Invalid geometry type: {geom_type}')
        
        if 'coordinates' not in geometry and geom_type != 'GeometryCollection':
            errors.append('Geometry missing coordinates')
        elif 'coordinates' in geometry:
            coords = geometry['coordinates']
            
            # Validate Point
            if geom_type == 'Point':
                if not self._is_valid_position(coords):
                    errors.append('Invalid Point coordinates')
            
            # Validate LineString
            elif geom_type == 'LineString':
                if not isinstance(coords, list) or len(coords) < 2:
                    errors.append('LineString must have at least 2 positions')
            
            # Validate Polygon
            elif geom_type == 'Polygon':
                if not isinstance(coords, list) or len(coords) == 0:
                    errors.append('Polygon must have at least one ring')
                else:
                    for ring in coords:
                        if len(ring) < 4:
                            errors.append('Polygon ring must have at least 4 positions')
                        elif ring[0] != ring[-1]:
                            errors.append('Polygon ring must be closed (first == last)')
        
        return errors
    
    def _is_valid_position(self, pos: Any) -> bool:
        """Check if position is valid [lon, lat] or [lon, lat, elevation]"""
        if not isinstance(pos, list) or len(pos) < 2:
            return False
        
        lon, lat = pos[0], pos[1]
        
        # Check if numbers
        if not isinstance(lon, (int, float)) or not isinstance(lat, (int, float)):
            return False
        
        # Check ranges
        if lon < -180 or lon > 180:
            return False
        if lat < -90 or lat > 90:
            return False
        
        return True
    
    def check_data_consistency(self, geojson_data: Dict) -> Dict:
        """
        Check data consistency and completeness
        
        Args:
            geojson_data: GeoJSON FeatureCollection
        
        Returns:
            Consistency check results
        """
        if geojson_data.get('type') != 'FeatureCollection':
            return {'error': 'Not a FeatureCollection'}
        
        features = geojson_data.get('features', [])
        
        if not features:
            return {'error': 'No features found'}
        
        # Analyze properties
        all_properties = set()
        property_counts = {}
        
        for feature in features:
            props = feature.get('properties', {})
            for key in props.keys():
                all_properties.add(key)
                property_counts[key] = property_counts.get(key, 0) + 1
        
        # Calculate completeness
        total_features = len(features)
        property_completeness = {
            key: (count / total_features) * 100
            for key, count in property_counts.items()
        }
        
        # Find missing data
        incomplete_properties = {
            key: pct for key, pct in property_completeness.items()
            if pct < 100
        }
        
        return {
            'total_features': total_features,
            'unique_properties': len(all_properties),
            'property_completeness': property_completeness,
            'incomplete_properties': incomplete_properties,
            'completeness_score': sum(property_completeness.values()) / len(property_completeness) if property_completeness else 0
        }
    
    def check_data_freshness(self, geojson_data: Dict, max_age_days: int = 30) -> Dict:
        """
        Check if data is recent enough
        
        Args:
            geojson_data: GeoJSON data with metadata
            max_age_days: Maximum acceptable age in days
        
        Returns:
            Freshness check results
        """
        metadata = geojson_data.get('metadata', {})
        generated_at = metadata.get('generated_at')
        
        if not generated_at:
            return {
                'fresh': False,
                'reason': 'No timestamp found',
                'age_days': None
            }
        
        try:
            generated_date = datetime.fromisoformat(generated_at.replace('Z', '+00:00'))
            age = datetime.now() - generated_date.replace(tzinfo=None)
            age_days = age.days
            
            return {
                'fresh': age_days <= max_age_days,
                'age_days': age_days,
                'generated_at': generated_at,
                'max_age_days': max_age_days
            }
        except Exception as e:
            return {
                'fresh': False,
                'reason': f'Invalid timestamp: {e}',
                'age_days': None
            }
    
    def generate_data_hash(self, data: Any) -> str:
        """Generate SHA-256 hash for data provenance"""
        data_string = json.dumps(data, sort_keys=True)
        return hashlib.sha256(data_string.encode()).hexdigest()
    
    def validate_spatial_extent(self, geojson_data: Dict, 
                               expected_bounds: Dict = None) -> Dict:
        """
        Validate spatial extent of data
        
        Args:
            geojson_data: GeoJSON data
            expected_bounds: Expected bounding box (optional)
        
        Returns:
            Spatial extent validation results
        """
        if geojson_data.get('type') != 'FeatureCollection':
            return {'error': 'Not a FeatureCollection'}
        
        features = geojson_data.get('features', [])
        
        if not features:
            return {'error': 'No features found'}
        
        # Calculate actual bounds
        lons = []
        lats = []
        
        for feature in features:
            geom = feature.get('geometry', {})
            if geom.get('type') == 'Point':
                coords = geom.get('coordinates', [])
                if len(coords) >= 2:
                    lons.append(coords[0])
                    lats.append(coords[1])
        
        if not lons or not lats:
            return {'error': 'No valid coordinates found'}
        
        actual_bounds = {
            'min_lon': min(lons),
            'max_lon': max(lons),
            'min_lat': min(lats),
            'max_lat': max(lats)
        }
        
        result = {
            'actual_bounds': actual_bounds,
            'center': {
                'lon': (actual_bounds['min_lon'] + actual_bounds['max_lon']) / 2,
                'lat': (actual_bounds['min_lat'] + actual_bounds['max_lat']) / 2
            }
        }
        
        # Check against expected bounds if provided
        if expected_bounds:
            within_bounds = (
                actual_bounds['min_lon'] >= expected_bounds.get('min_lon', -180) and
                actual_bounds['max_lon'] <= expected_bounds.get('max_lon', 180) and
                actual_bounds['min_lat'] >= expected_bounds.get('min_lat', -90) and
                actual_bounds['max_lat'] <= expected_bounds.get('max_lat', 90)
            )
            result['within_expected_bounds'] = within_bounds
            result['expected_bounds'] = expected_bounds
        
        return result
    
    def generate_quality_report(self, geojson_files: List[str]) -> Dict:
        """
        Generate comprehensive quality report for multiple files
        
        Args:
            geojson_files: List of GeoJSON file paths
        
        Returns:
            Comprehensive quality report
        """
        report = {
            'generated_at': datetime.now().isoformat(),
            'files_checked': len(geojson_files),
            'file_reports': []
        }
        
        for filepath in geojson_files:
            try:
                with open(filepath, 'r') as f:
                    data = json.load(f)
                
                file_report = {
                    'file': filepath,
                    'validation': self.validate_geojson(data),
                    'consistency': self.check_data_consistency(data),
                    'freshness': self.check_data_freshness(data),
                    'spatial_extent': self.validate_spatial_extent(data),
                    'data_hash': self.generate_data_hash(data)
                }
                
                # Calculate overall quality score
                quality_score = 0
                if file_report['validation']['valid']:
                    quality_score += 40
                
                completeness = file_report['consistency'].get('completeness_score', 0)
                quality_score += (completeness / 100) * 30
                
                if file_report['freshness']['fresh']:
                    quality_score += 30
                
                file_report['quality_score'] = round(quality_score, 2)
                
                report['file_reports'].append(file_report)
                
            except Exception as e:
                report['file_reports'].append({
                    'file': filepath,
                    'error': str(e),
                    'quality_score': 0
                })
        
        # Calculate overall statistics
        valid_reports = [r for r in report['file_reports'] if 'error' not in r]
        if valid_reports:
            report['summary'] = {
                'average_quality_score': sum(r['quality_score'] for r in valid_reports) / len(valid_reports),
                'files_passed': sum(1 for r in valid_reports if r['validation']['valid']),
                'files_failed': len(report['file_reports']) - len(valid_reports)
            }
        
        return report


def main():
    """Main execution function"""
    print("=" * 60)
    print("ThrivingRoots Data Validation & Quality Assurance")
    print("=" * 60)
    
    validator = DataValidator()
    
    # List of files to validate
    geojson_files = [
        '../outputs/california_air_quality.geojson',
        '../outputs/california_water_quality.geojson',
        '../outputs/california_superfund_sites.geojson'
    ]
    
    print("\nValidating GeoJSON files...")
    
    # Generate comprehensive report
    quality_report = validator.generate_quality_report(geojson_files)
    
    # Display results
    print(f"\n{'='*60}")
    print("QUALITY REPORT SUMMARY")
    print(f"{'='*60}")
    
    if 'summary' in quality_report:
        summary = quality_report['summary']
        print(f"\nFiles Checked: {quality_report['files_checked']}")
        print(f"Files Passed: {summary['files_passed']}")
        print(f"Files Failed: {summary['files_failed']}")
        print(f"Average Quality Score: {summary['average_quality_score']:.2f}/100")
    
    print(f"\n{'='*60}")
    print("INDIVIDUAL FILE REPORTS")
    print(f"{'='*60}")
    
    for file_report in quality_report['file_reports']:
        filename = file_report['file'].split('/')[-1]
        print(f"\n📄 {filename}")
        print(f"   Quality Score: {file_report.get('quality_score', 0):.2f}/100")
        
        if 'error' in file_report:
            print(f"   ❌ Error: {file_report['error']}")
            continue
        
        # Validation
        validation = file_report['validation']
        if validation['valid']:
            print(f"   ✅ Structure: Valid")
        else:
            print(f"   ❌ Structure: Invalid")
            for error in validation['errors']:
                print(f"      - {error}")
        
        # Consistency
        consistency = file_report['consistency']
        print(f"   📊 Features: {consistency.get('total_features', 0)}")
        print(f"   📈 Completeness: {consistency.get('completeness_score', 0):.1f}%")
        
        # Freshness
        freshness = file_report['freshness']
        if freshness['fresh']:
            print(f"   🕐 Freshness: ✅ ({freshness.get('age_days', 0)} days old)")
        else:
            print(f"   🕐 Freshness: ⚠️  {freshness.get('reason', 'Unknown')}")
        
        # Data hash
        print(f"   🔒 Hash: {file_report['data_hash'][:16]}...")
    
    # Save report
    report_path = '../outputs/quality_report.json'
    with open(report_path, 'w') as f:
        json.dump(quality_report, f, indent=2)
    
    print(f"\n{'='*60}")
    print(f"Full report saved: {report_path}")
    print(f"{'='*60}\n")


if __name__ == '__main__':
    main()
