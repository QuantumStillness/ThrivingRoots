#!/usr/bin/env python3
"""
Spatial Analysis Module
Environmental Justice and Remediation Prioritization Algorithms
"""

import json
import math
from datetime import datetime
from typing import Dict, List, Tuple, Any


class SpatialAnalyzer:
    """Spatial analysis for environmental justice and risk assessment"""
    
    def __init__(self):
        self.earth_radius_km = 6371.0
    
    def haversine_distance(self, lat1: float, lon1: float, 
                          lat2: float, lon2: float) -> float:
        """
        Calculate distance between two points using Haversine formula
        
        Args:
            lat1, lon1: First point coordinates
            lat2, lon2: Second point coordinates
        
        Returns:
            Distance in kilometers
        """
        # Convert to radians
        lat1_rad = math.radians(lat1)
        lat2_rad = math.radians(lat2)
        delta_lat = math.radians(lat2 - lat1)
        delta_lon = math.radians(lon2 - lon1)
        
        # Haversine formula
        a = (math.sin(delta_lat / 2) ** 2 + 
             math.cos(lat1_rad) * math.cos(lat2_rad) * 
             math.sin(delta_lon / 2) ** 2)
        c = 2 * math.atan2(math.sqrt(a), math.sqrt(1 - a))
        
        return self.earth_radius_km * c
    
    def buffer_analysis(self, point: Dict, radius_km: float, 
                       target_points: List[Dict]) -> List[Dict]:
        """
        Find all points within radius of a given point
        
        Args:
            point: Center point with lat/lon
            radius_km: Buffer radius in kilometers
            target_points: List of points to check
        
        Returns:
            List of points within buffer
        """
        lat1, lon1 = self._extract_coords(point)
        within_buffer = []
        
        for target in target_points:
            lat2, lon2 = self._extract_coords(target)
            distance = self.haversine_distance(lat1, lon1, lat2, lon2)
            
            if distance <= radius_km:
                target_copy = target.copy()
                target_copy['distance_km'] = round(distance, 2)
                within_buffer.append(target_copy)
        
        return within_buffer
    
    def nearest_neighbor(self, point: Dict, 
                        target_points: List[Dict]) -> Tuple[Dict, float]:
        """
        Find nearest neighbor to a given point
        
        Args:
            point: Source point
            target_points: List of potential neighbors
        
        Returns:
            Tuple of (nearest point, distance in km)
        """
        lat1, lon1 = self._extract_coords(point)
        min_distance = float('inf')
        nearest = None
        
        for target in target_points:
            lat2, lon2 = self._extract_coords(target)
            distance = self.haversine_distance(lat1, lon1, lat2, lon2)
            
            if distance < min_distance:
                min_distance = distance
                nearest = target
        
        return nearest, round(min_distance, 2)
    
    def calculate_ej_risk_score(self, location: Dict, 
                                superfund_sites: List[Dict],
                                air_quality_data: List[Dict],
                                water_sources: List[Dict],
                                demographic_vulnerability: float = 0.5) -> Dict:
        """
        Calculate Environmental Justice risk score
        
        Args:
            location: Location to assess
            superfund_sites: List of Superfund sites
            air_quality_data: Air quality measurements
            water_sources: Water quality measurements
            demographic_vulnerability: Demographic risk factor (0-1)
        
        Returns:
            Comprehensive risk assessment
        """
        lat, lon = self._extract_coords(location)
        
        # 1. Proximity to Superfund sites
        nearest_superfund, superfund_distance = self.nearest_neighbor(
            location, superfund_sites
        )
        proximity_risk = max(0, 1.0 - (superfund_distance / 10.0))
        
        # 2. Air quality assessment
        nearest_air, air_distance = self.nearest_neighbor(
            location, air_quality_data
        )
        if nearest_air and 'aqi' in nearest_air:
            air_risk = min(nearest_air['aqi'] / 200.0, 1.0)
        else:
            air_risk = 0.5  # Default moderate risk
        
        # 3. Water source vulnerability
        water_within_5km = self.buffer_analysis(
            location, 5.0, water_sources
        )
        if water_within_5km:
            # Average dissolved oxygen (lower is worse)
            avg_do = sum(w.get('dissolved_oxygen', 8.0) 
                        for w in water_within_5km) / len(water_within_5km)
            water_risk = max(0, (8.0 - avg_do) / 8.0)
        else:
            water_risk = 0.3  # Default if no data
        
        # 4. Composite risk calculation
        composite_risk = (
            proximity_risk * 0.3 +
            air_risk * 0.3 +
            water_risk * 0.2 +
            demographic_vulnerability * 0.2
        )
        
        # Categorize risk
        if composite_risk >= 0.7:
            category = 'High Risk - Priority Intervention'
            priority = 1
        elif composite_risk >= 0.5:
            category = 'Moderate-High Risk - Monitoring Required'
            priority = 2
        elif composite_risk >= 0.3:
            category = 'Moderate Risk - Routine Monitoring'
            priority = 3
        else:
            category = 'Low Risk'
            priority = 4
        
        return {
            'location': location.get('location', 'Unknown'),
            'latitude': lat,
            'longitude': lon,
            'composite_risk': round(composite_risk, 3),
            'risk_factors': {
                'proximity_to_superfund': round(proximity_risk, 3),
                'air_quality_risk': round(air_risk, 3),
                'water_vulnerability': round(water_risk, 3),
                'demographic_vulnerability': round(demographic_vulnerability, 3)
            },
            'category': category,
            'priority': priority,
            'nearest_superfund': {
                'name': nearest_superfund.get('SITE_NAME', 'Unknown') if nearest_superfund else 'None',
                'distance_km': superfund_distance
            },
            'air_quality': {
                'nearest_station': nearest_air.get('location', 'Unknown') if nearest_air else 'None',
                'aqi': nearest_air.get('aqi', 'N/A') if nearest_air else 'N/A',
                'distance_km': air_distance
            },
            'water_sources_within_5km': len(water_within_5km),
            'timestamp': datetime.now().isoformat()
        }
    
    def prioritize_remediation_areas(self, risk_assessments: List[Dict],
                                    population_density: Dict = None,
                                    infrastructure_data: Dict = None) -> List[Dict]:
        """
        Generate prioritized list of areas for remediation
        
        Args:
            risk_assessments: List of risk assessment results
            population_density: Optional population density data
            infrastructure_data: Optional critical infrastructure data
        
        Returns:
            Sorted list of priority areas
        """
        priority_areas = []
        
        for assessment in risk_assessments:
            # Base priority from risk assessment
            base_priority = assessment['priority']
            composite_risk = assessment['composite_risk']
            
            # Adjust for population density if available
            if population_density:
                # Placeholder: would use actual population data
                pop_factor = 1.0
            else:
                pop_factor = 1.0
            
            # Adjust for infrastructure proximity if available
            if infrastructure_data:
                # Placeholder: would check proximity to schools, hospitals, etc.
                infra_factor = 1.0
            else:
                infra_factor = 1.0
            
            # Calculate final priority score (lower is higher priority)
            priority_score = base_priority / (pop_factor * infra_factor)
            
            priority_areas.append({
                'location': assessment['location'],
                'latitude': assessment['latitude'],
                'longitude': assessment['longitude'],
                'priority_score': round(priority_score, 2),
                'priority_rank': base_priority,
                'composite_risk': composite_risk,
                'category': assessment['category'],
                'recommended_actions': self._recommend_actions(assessment)
            })
        
        # Sort by priority score (lower is higher priority)
        priority_areas.sort(key=lambda x: x['priority_score'])
        
        # Add rank numbers
        for i, area in enumerate(priority_areas, 1):
            area['rank'] = i
        
        return priority_areas
    
    def _recommend_actions(self, assessment: Dict) -> List[str]:
        """Recommend actions based on risk assessment"""
        actions = []
        risk_factors = assessment['risk_factors']
        
        if risk_factors['proximity_to_superfund'] > 0.6:
            actions.append('Conduct soil and groundwater testing')
            actions.append('Establish community health monitoring program')
        
        if risk_factors['air_quality_risk'] > 0.6:
            actions.append('Install air quality monitoring stations')
            actions.append('Provide air filtration resources to residents')
        
        if risk_factors['water_vulnerability'] > 0.5:
            actions.append('Test drinking water sources')
            actions.append('Provide water filtration systems')
        
        if risk_factors['demographic_vulnerability'] > 0.6:
            actions.append('Prioritize community outreach and education')
            actions.append('Establish environmental justice task force')
        
        if assessment['composite_risk'] > 0.7:
            actions.append('URGENT: Immediate assessment required')
            actions.append('Coordinate with state/federal agencies')
        
        return actions if actions else ['Continue routine monitoring']
    
    def generate_heatmap_data(self, risk_assessments: List[Dict], 
                             grid_resolution: float = 0.1) -> Dict:
        """
        Generate heatmap data for visualization
        
        Args:
            risk_assessments: List of risk assessments
            grid_resolution: Grid cell size in degrees
        
        Returns:
            Heatmap data structure
        """
        # Find bounding box
        lats = [a['latitude'] for a in risk_assessments]
        lons = [a['longitude'] for a in risk_assessments]
        
        min_lat, max_lat = min(lats), max(lats)
        min_lon, max_lon = min(lons), max(lons)
        
        # Create grid
        heatmap_points = []
        for assessment in risk_assessments:
            heatmap_points.append({
                'lat': assessment['latitude'],
                'lon': assessment['longitude'],
                'intensity': assessment['composite_risk'],
                'category': assessment['category']
            })
        
        return {
            'type': 'heatmap',
            'bounds': {
                'min_lat': min_lat,
                'max_lat': max_lat,
                'min_lon': min_lon,
                'max_lon': max_lon
            },
            'points': heatmap_points,
            'metadata': {
                'point_count': len(heatmap_points),
                'generated_at': datetime.now().isoformat()
            }
        }
    
    def _extract_coords(self, point: Dict) -> Tuple[float, float]:
        """Extract latitude and longitude from various formats"""
        if 'latitude' in point and 'longitude' in point:
            return float(point['latitude']), float(point['longitude'])
        elif 'LATITUDE' in point and 'LONGITUDE' in point:
            return float(point['LATITUDE']), float(point['LONGITUDE'])
        elif 'geometry' in point and 'coordinates' in point['geometry']:
            coords = point['geometry']['coordinates']
            return float(coords[1]), float(coords[0])  # GeoJSON is [lon, lat]
        else:
            raise ValueError(f"Cannot extract coordinates from point: {point}")


def main():
    """Main execution function"""
    print("=" * 60)
    print("ThrivingRoots Spatial Analysis Module")
    print("=" * 60)
    
    # Load data
    print("\nLoading environmental data...")
    with open('../outputs/california_air_quality.geojson', 'r') as f:
        air_data = json.load(f)
    
    with open('../outputs/california_water_quality.geojson', 'r') as f:
        water_data = json.load(f)
    
    with open('../outputs/california_superfund_sites.geojson', 'r') as f:
        superfund_data = json.load(f)
    
    # Extract features
    air_quality = [f['properties'] | {'geometry': f['geometry']} 
                  for f in air_data['features']]
    water_sources = [f['properties'] | {'geometry': f['geometry']} 
                    for f in water_data['features']]
    superfund_sites = [f['properties'] | {'geometry': f['geometry']} 
                      for f in superfund_data['features']]
    
    # Initialize analyzer
    analyzer = SpatialAnalyzer()
    
    # Perform risk assessments for each air quality location
    print("\nPerforming Environmental Justice Risk Assessments...")
    risk_assessments = []
    
    for location in air_quality:
        # Vary demographic vulnerability based on location
        # In production, this would come from census data
        demo_vuln = 0.7 if 'Los Angeles' in location.get('location', '') else 0.5
        
        assessment = analyzer.calculate_ej_risk_score(
            location,
            superfund_sites,
            air_quality,
            water_sources,
            demographic_vulnerability=demo_vuln
        )
        risk_assessments.append(assessment)
        
        print(f"\n  Location: {assessment['location']}")
        print(f"  Composite Risk: {assessment['composite_risk']} ({assessment['category']})")
        print(f"  Priority: {assessment['priority']}")
    
    # Generate prioritization
    print("\n\nGenerating Remediation Prioritization...")
    priority_areas = analyzer.prioritize_remediation_areas(risk_assessments)
    
    print(f"\nTop Priority Areas:")
    for area in priority_areas[:3]:
        print(f"\n  Rank {area['rank']}: {area['location']}")
        print(f"  Risk Score: {area['composite_risk']}")
        print(f"  Recommended Actions:")
        for action in area['recommended_actions']:
            print(f"    - {action}")
    
    # Generate heatmap data
    print("\n\nGenerating Heatmap Data...")
    heatmap = analyzer.generate_heatmap_data(risk_assessments)
    
    # Save outputs
    print("\nSaving analysis results...")
    with open('../outputs/risk_assessments.json', 'w') as f:
        json.dump(risk_assessments, f, indent=2)
    print("  Saved: risk_assessments.json")
    
    with open('../outputs/priority_areas.json', 'w') as f:
        json.dump(priority_areas, f, indent=2)
    print("  Saved: priority_areas.json")
    
    with open('../outputs/heatmap_data.json', 'w') as f:
        json.dump(heatmap, f, indent=2)
    print("  Saved: heatmap_data.json")
    
    print("\n" + "=" * 60)
    print("Spatial Analysis Complete!")
    print("=" * 60)
    print(f"\nAssessed {len(risk_assessments)} locations")
    print(f"Identified {sum(1 for a in risk_assessments if a['priority'] == 1)} high-priority areas")
    print(f"Generated heatmap with {len(heatmap['points'])} data points")


if __name__ == '__main__':
    main()
