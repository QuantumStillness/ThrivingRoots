import * as React from 'react';
import Map, { NavigationControl, Marker, ViewState } from 'react-map-gl';
import 'mapbox-gl/dist/mapbox-gl.css';

const MAPBOX_TOKEN = 'pk.eyJ1IjoiNGV2ci1pYW1iIiwiYSI6ImNta25tMG1qdzAyOHczZm9reDF4cjBxb2MifQ.XxPMT2ZY9CswREfA3wQj8g';

interface InteractiveMapProps {
  initialViewState?: {
    longitude: number;
    latitude: number;
    zoom: number;
  };
  viewState?: {
    longitude: number;
    latitude: number;
    zoom: number;
    bearing?: number;
    pitch?: number;
    padding?: { top: number; bottom: number; left: number; right: number };
  };
  onMove?: (evt: { viewState: ViewState }) => void;
  style?: React.CSSProperties;
  mapStyle?: string;
  markers?: Array<{
    longitude: number;
    latitude: number;
    label?: string;
  }>;
  className?: string;
  interactive?: boolean;
}

export default function InteractiveMap({
  initialViewState = {
    longitude: -118.1319, // Altadena/Pasadena area
    latitude: 34.1800,
    zoom: 11
  },
  viewState,
  onMove,
  style = { width: '100%', height: '100%' },
  mapStyle = "mapbox://styles/mapbox/light-v11", // Light theme to match B&W aesthetic
  markers = [],
  className = "",
  interactive = true
}: InteractiveMapProps) {
  // Use a ref to track if the map is controlled or uncontrolled
  const isControlled = viewState !== undefined;

  return (
    <div className={`relative overflow-hidden ${className}`}>
      <Map
        {...(isControlled ? { 
          longitude: viewState?.longitude,
          latitude: viewState?.latitude,
          zoom: viewState?.zoom,
          bearing: viewState?.bearing || 0,
          pitch: viewState?.pitch || 0,
          padding: viewState?.padding || { top: 0, bottom: 0, left: 0, right: 0 }
        } : { initialViewState })}
        {...(onMove ? { onMove } : {})}
        style={style}
        mapStyle={mapStyle}
        mapboxAccessToken={MAPBOX_TOKEN}
        attributionControl={false}
        scrollZoom={interactive}
        dragPan={interactive}
        dragRotate={interactive}
        doubleClickZoom={interactive}
      >
        {interactive && <NavigationControl position="top-right" />}
        
        {markers.map((marker, index) => (
          <Marker 
            key={index} 
            longitude={marker.longitude} 
            latitude={marker.latitude} 
            anchor="bottom"
          >
            <div className="flex flex-col items-center">
              <div className="w-6 h-6 bg-black rounded-full border-2 border-white shadow-lg" />
              {marker.label && (
                <span className="bg-white px-2 py-1 text-xs font-bold uppercase tracking-wider shadow-md mt-1 rounded-sm">
                  {marker.label}
                </span>
              )}
            </div>
          </Marker>
        ))}
      </Map>
    </div>
  );
}
