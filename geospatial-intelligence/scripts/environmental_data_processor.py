#!/usr/bin/env python3
"""
Environmental Data Processor
Processes environmental data from EPA and USGS sources for ThrivingRoots platform
"""

import json
import hashlib
from datetime import datetime
import requests
from typing import Dict, List, Any

class EnvironmentalDataProcessor:
    """Process and integrate environmental data from multiple sources"""
    
    def __init__(self, output_dir='../outputs'):
        self.output_dir = output_dir
        self.data_sources = {
            'epa_air_quality': 'https://www.airnowapi.org/aq/observation/zipCode/current/',
            'usgs_water': 'https://waterservices.usgs.gov/nwis/iv/',
            'epa_superfund': 'https://enviro.epa.gov/enviro/efservice/'
        }
        
    def fetch_air_quality_data(self, zip_code='90001', api_key=None):
        """
        Fetch air quality data from EPA AirNow API
        Note: Requires API key from https://docs.airnowapi.org/
        """
        if not api_key:
            print("Warning: No API key provided for AirNow. Using sample data.")
            return self._generate_sample_air_quality()
        
        try:
            params = {
                'format': 'application/json',
                'zipCode': zip_code,
                'distance': 25,
                'API_KEY': api_key
            }
            response = requests.get(self.data_sources['epa_air_quality'], params=params, timeout=10)
            response.raise_for_status()
            return response.json()
        except Exception as e:
            print(f"Error fetching air quality data: {e}")
            return self._generate_sample_air_quality()
    
    def fetch_water_quality_data(self, state_code='ca'):
        """
        Fetch water quality data from USGS Water Services
        """
        try:
            params = {
                'stateCd': state_code,
                'parameterCd': '00010,00095,00300',  # Temperature, Conductivity, Dissolved Oxygen
                'siteType': 'ST',  # Stream
                'format': 'json'
            }
            response = requests.get(self.data_sources['usgs_water'], params=params, timeout=30)
            response.raise_for_status()
            data = response.json()
            return self._process_usgs_data(data)
        except Exception as e:
            print(f"Error fetching water quality data: {e}")
            return self._generate_sample_water_quality()
    
    def fetch_superfund_sites(self, state='CA', limit=100):
        """
        Fetch Superfund site data from EPA Envirofacts API
        """
        try:
            url = f"{self.data_sources['epa_superfund']}SEMS_SITE_INFO/STATE_CODE/{state}/JSON"
            response = requests.get(url, timeout=30)
            response.raise_for_status()
            return response.json()[:limit]
        except Exception as e:
            print(f"Error fetching Superfund data: {e}")
            return self._generate_sample_superfund()
    
    def _process_usgs_data(self, raw_data: Dict) -> List[Dict]:
        """Process USGS water quality data into simplified format"""
        processed = []
        
        if 'value' not in raw_data or 'timeSeries' not in raw_data['value']:
            return processed
        
        for site in raw_data['value']['timeSeries']:
            try:
                site_info = site['sourceInfo']
                processed.append({
                    'site_code': site_info['siteCode'][0]['value'],
                    'site_name': site_info['siteName'],
                    'latitude': float(site_info['geoLocation']['geogLocation']['latitude']),
                    'longitude': float(site_info['geoLocation']['geogLocation']['longitude']),
                    'parameter': site['variable']['variableName'],
                    'values': site['values'][0]['value'] if site['values'] else []
                })
            except (KeyError, IndexError, ValueError) as e:
                continue
        
        return processed
    
    def _generate_sample_air_quality(self) -> List[Dict]:
        """Generate sample air quality data for demonstration"""
        return [
            {
                'location': 'Los Angeles',
                'latitude': 34.0522,
                'longitude': -118.2437,
                'pm25': 45.2,
                'pm10': 78.5,
                'ozone': 0.065,
                'aqi': 105,
                'category': 'Unhealthy for Sensitive Groups',
                'timestamp': datetime.now().isoformat()
            },
            {
                'location': 'San Diego',
                'latitude': 32.7157,
                'longitude': -117.1611,
                'pm25': 28.3,
                'pm10': 52.1,
                'ozone': 0.048,
                'aqi': 68,
                'category': 'Moderate',
                'timestamp': datetime.now().isoformat()
            },
            {
                'location': 'San Francisco',
                'latitude': 37.7749,
                'longitude': -122.4194,
                'pm25': 22.1,
                'pm10': 41.3,
                'ozone': 0.042,
                'aqi': 55,
                'category': 'Moderate',
                'timestamp': datetime.now().isoformat()
            }
        ]
    
    def _generate_sample_water_quality(self) -> List[Dict]:
        """Generate sample water quality data"""
        return [
            {
                'site_code': 'USGS-11074000',
                'site_name': 'Santa Ana River',
                'latitude': 33.8121,
                'longitude': -117.8897,
                'temperature': 18.5,
                'conductivity': 850,
                'dissolved_oxygen': 7.2,
                'timestamp': datetime.now().isoformat()
            },
            {
                'site_code': 'USGS-11023340',
                'site_name': 'San Diego River',
                'latitude': 32.7686,
                'longitude': -117.1311,
                'temperature': 19.2,
                'conductivity': 920,
                'dissolved_oxygen': 6.8,
                'timestamp': datetime.now().isoformat()
            }
        ]
    
    def _generate_sample_superfund(self) -> List[Dict]:
        """Generate sample Superfund site data"""
        return [
            {
                'SITE_NAME': 'Iron Mountain Mine',
                'EPA_ID': 'CAD980498612',
                'LATITUDE': 40.6653,
                'LONGITUDE': -122.5236,
                'NPL_STATUS': 'Final',
                'SITE_STATUS': 'Cleanup',
                'CITY': 'Redding',
                'COUNTY': 'Shasta'
            },
            {
                'SITE_NAME': 'Operating Industries Inc Landfill',
                'EPA_ID': 'CAD008188504',
                'LATITUDE': 33.9011,
                'LONGITUDE': -118.0631,
                'NPL_STATUS': 'Final',
                'SITE_STATUS': 'Monitoring',
                'CITY': 'Monterey Park',
                'COUNTY': 'Los Angeles'
            }
        ]
    
    def calculate_risk_score(self, air_quality: Dict, water_quality: Dict, 
                           superfund_proximity: float) -> Dict:
        """
        Calculate composite environmental risk score
        
        Args:
            air_quality: Air quality metrics
            water_quality: Water quality metrics
            superfund_proximity: Distance to nearest Superfund site (km)
        
        Returns:
            Dict with risk scores and category
        """
        # Air quality risk (0-1 scale)
        aqi = air_quality.get('aqi', 50)
        air_risk = min(aqi / 200.0, 1.0)
        
        # Water quality risk (based on dissolved oxygen)
        do_level = water_quality.get('dissolved_oxygen', 8.0)
        water_risk = max(0, (8.0 - do_level) / 8.0)
        
        # Superfund proximity risk (inverse relationship)
        proximity_risk = max(0, 1.0 - (superfund_proximity / 10.0))
        
        # Composite score (weighted average)
        composite = (air_risk * 0.4 + water_risk * 0.3 + proximity_risk * 0.3)
        
        # Categorize risk
        if composite >= 0.7:
            category = 'High Risk'
        elif composite >= 0.4:
            category = 'Moderate Risk'
        else:
            category = 'Low Risk'
        
        return {
            'composite_score': round(composite, 3),
            'air_risk': round(air_risk, 3),
            'water_risk': round(water_risk, 3),
            'proximity_risk': round(proximity_risk, 3),
            'category': category,
            'timestamp': datetime.now().isoformat()
        }
    
    def generate_geojson(self, data_points: List[Dict], data_type: str) -> Dict:
        """
        Generate GeoJSON from data points
        
        Args:
            data_points: List of data dictionaries with lat/lon
            data_type: Type of data (air_quality, water_quality, superfund)
        
        Returns:
            GeoJSON FeatureCollection
        """
        features = []
        
        for point in data_points:
            # Extract coordinates
            if 'LATITUDE' in point and 'LONGITUDE' in point:
                lat, lon = point['LATITUDE'], point['LONGITUDE']
            elif 'latitude' in point and 'longitude' in point:
                lat, lon = point['latitude'], point['longitude']
            else:
                continue
            
            # Create feature
            feature = {
                'type': 'Feature',
                'geometry': {
                    'type': 'Point',
                    'coordinates': [float(lon), float(lat)]
                },
                'properties': {k: v for k, v in point.items() 
                             if k not in ['latitude', 'longitude', 'LATITUDE', 'LONGITUDE']}
            }
            features.append(feature)
        
        return {
            'type': 'FeatureCollection',
            'metadata': {
                'data_type': data_type,
                'feature_count': len(features),
                'generated_at': datetime.now().isoformat(),
                'crs': 'EPSG:4326'
            },
            'features': features
        }
    
    def generate_data_hash(self, data: Any) -> str:
        """Generate SHA-256 hash for data provenance"""
        data_string = json.dumps(data, sort_keys=True)
        return hashlib.sha256(data_string.encode()).hexdigest()
    
    def save_to_file(self, data: Any, filename: str):
        """Save data to JSON file"""
        filepath = f"{self.output_dir}/{filename}"
        with open(filepath, 'w') as f:
            json.dump(data, f, indent=2)
        print(f"Saved: {filepath}")
        return filepath
    
    def generate_wordpress_import(self, environmental_data: Dict) -> Dict:
        """
        Generate WordPress-compatible import format
        
        Args:
            environmental_data: Processed environmental data
        
        Returns:
            WordPress CPT import format
        """
        wordpress_data = {
            'posts': [],
            'meta': {
                'import_date': datetime.now().isoformat(),
                'data_hash': self.generate_data_hash(environmental_data),
                'source': 'geospatial_intelligence_processor'
            }
        }
        
        # Convert each data point to WordPress post format
        for feature in environmental_data.get('features', []):
            props = feature['properties']
            coords = feature['geometry']['coordinates']
            
            post = {
                'post_type': 'environmental_layer',
                'post_title': props.get('location', props.get('site_name', 'Environmental Data Point')),
                'post_status': 'publish',
                'meta_input': {
                    '_eic_latitude': coords[1],
                    '_eic_longitude': coords[0],
                    '_eic_data_type': environmental_data['metadata']['data_type'],
                    '_eic_properties': json.dumps(props),
                    '_eic_source_hash': self.generate_data_hash(feature)
                }
            }
            wordpress_data['posts'].append(post)
        
        return wordpress_data


def main():
    """Main execution function"""
    print("=" * 60)
    print("ThrivingRoots Environmental Data Processor")
    print("=" * 60)
    
    processor = EnvironmentalDataProcessor(output_dir='../outputs')
    
    # Fetch and process data
    print("\n1. Fetching Air Quality Data...")
    air_quality = processor.fetch_air_quality_data()
    air_geojson = processor.generate_geojson(air_quality, 'air_quality')
    processor.save_to_file(air_geojson, 'california_air_quality.geojson')
    
    print("\n2. Fetching Water Quality Data...")
    water_quality = processor.fetch_water_quality_data()
    water_geojson = processor.generate_geojson(water_quality, 'water_quality')
    processor.save_to_file(water_geojson, 'california_water_quality.geojson')
    
    print("\n3. Fetching Superfund Sites...")
    superfund_sites = processor.fetch_superfund_sites()
    superfund_geojson = processor.generate_geojson(superfund_sites, 'superfund_sites')
    processor.save_to_file(superfund_geojson, 'california_superfund_sites.geojson')
    
    print("\n4. Calculating Risk Scores...")
    # Calculate sample risk score
    sample_risk = processor.calculate_risk_score(
        air_quality[0] if air_quality else {},
        water_quality[0] if water_quality else {},
        superfund_proximity=5.2
    )
    print(f"Sample Risk Score: {sample_risk}")
    processor.save_to_file(sample_risk, 'sample_risk_analysis.json')
    
    print("\n5. Generating WordPress Import Data...")
    wp_import = processor.generate_wordpress_import(air_geojson)
    processor.save_to_file(wp_import, 'wordpress_import_data.json')
    
    print("\n" + "=" * 60)
    print("Processing Complete!")
    print("=" * 60)
    print(f"\nGenerated {len(air_geojson['features'])} air quality features")
    print(f"Generated {len(water_geojson['features'])} water quality features")
    print(f"Generated {len(superfund_geojson['features'])} superfund site features")
    print(f"WordPress import ready with {len(wp_import['posts'])} posts")


if __name__ == '__main__':
    main()
