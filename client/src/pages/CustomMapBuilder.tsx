import { useState } from "react";
import Layout from "@/components/Layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { MapPin, Type, LayoutTemplate, Palette, ZoomIn, ZoomOut, Move } from "lucide-react";
import InteractiveMap from "@/components/InteractiveMap";

export default function CustomMapBuilder() {
  // State for customization options
  const [location, setLocation] = useState("Pasadena, CA");
  const [title, setTitle] = useState("PASADENA");
  const [subtitle, setSubtitle] = useState("CALIFORNIA");
  const [layoutStyle, setLayoutStyle] = useState("classic");
  const [size, setSize] = useState("18x24");
  const [orientation, setOrientation] = useState("portrait");
  const [viewState, setViewState] = useState({
    longitude: -118.1445,
    latitude: 34.1478,
    zoom: 12
  });

  const handleZoomIn = () => {
    setViewState(prev => ({ ...prev, zoom: Math.min(prev.zoom + 1, 18) }));
  };

  const handleZoomOut = () => {
    setViewState(prev => ({ ...prev, zoom: Math.max(prev.zoom - 1, 4) }));
  };

  return (
    <Layout>
      <div className="flex flex-col lg:flex-row h-[calc(100vh-64px)]">
        {/* Left Panel: Customization Controls */}
        <div className="w-full lg:w-1/3 bg-white border-r border-gray-200 overflow-y-auto p-6 z-10 shadow-xl">
          <h1 className="text-2xl font-bold mb-6 uppercase tracking-tight">Design Your Map</h1>
          
          <div className="space-y-8">
            {/* Section 1: Location */}
            <div className="space-y-4">
              <div className="flex items-center gap-2 text-lg font-bold border-b border-gray-100 pb-2">
                <MapPin className="w-5 h-5" />
                <h2>Location</h2>
              </div>
              <div className="space-y-2">
                <Label htmlFor="location">Search City or Place</Label>
                <div className="flex gap-2">
                  <Input 
                    id="location" 
                    value={location} 
                    onChange={(e) => setLocation(e.target.value)}
                    placeholder="Enter a location..."
                    className="rounded-none border-black focus:ring-0"
                  />
                  <Button className="rounded-none bg-black text-white hover:bg-gray-800">
                    Find
                  </Button>
                </div>
              </div>
            </div>

            {/* Section 2: Style & Layout */}
            <div className="space-y-4">
              <div className="flex items-center gap-2 text-lg font-bold border-b border-gray-100 pb-2">
                <LayoutTemplate className="w-5 h-5" />
                <h2>Style</h2>
              </div>
              
              <div className="grid grid-cols-2 gap-4">
                {/* Classic Style */}
                <div 
                  className={`border p-4 cursor-pointer transition-all ${layoutStyle === 'classic' ? 'border-black bg-gray-50' : 'border-gray-200 hover:border-gray-400'}`}
                  onClick={() => setLayoutStyle('classic')}
                >
                  <div className="aspect-[3/4] border border-gray-300 mb-2 bg-white p-2 flex flex-col items-center justify-end">
                    <div className="w-full h-1 bg-gray-200 mb-1"></div>
                    <div className="w-2/3 h-1 bg-gray-200"></div>
                  </div>
                  <p className="text-center text-sm font-bold uppercase">Classic</p>
                </div>
                
                {/* Modern Style */}
                <div 
                  className={`border p-4 cursor-pointer transition-all ${layoutStyle === 'modern' ? 'border-black bg-gray-50' : 'border-gray-200 hover:border-gray-400'}`}
                  onClick={() => setLayoutStyle('modern')}
                >
                  <div className="aspect-[3/4] border border-gray-300 mb-2 bg-gray-100 relative">
                    <div className="absolute bottom-4 left-0 right-0 text-center">
                      <div className="w-16 h-1 bg-black mx-auto mb-1"></div>
                    </div>
                  </div>
                  <p className="text-center text-sm font-bold uppercase">Modern</p>
                </div>

                {/* Circle Style */}
                <div 
                  className={`border p-4 cursor-pointer transition-all ${layoutStyle === 'circle' ? 'border-black bg-gray-50' : 'border-gray-200 hover:border-gray-400'}`}
                  onClick={() => setLayoutStyle('circle')}
                >
                  <div className="aspect-[3/4] border border-gray-300 mb-2 bg-white p-2 flex flex-col items-center justify-center">
                    <div className="w-16 h-16 rounded-full bg-gray-200 mb-2"></div>
                    <div className="w-12 h-1 bg-gray-200"></div>
                  </div>
                  <p className="text-center text-sm font-bold uppercase">Circle</p>
                </div>

                {/* Clean Style */}
                <div 
                  className={`border p-4 cursor-pointer transition-all ${layoutStyle === 'clean' ? 'border-black bg-gray-50' : 'border-gray-200 hover:border-gray-400'}`}
                  onClick={() => setLayoutStyle('clean')}
                >
                  <div className="aspect-[3/4] border border-gray-300 mb-2 bg-gray-200"></div>
                  <p className="text-center text-sm font-bold uppercase">Clean</p>
                </div>
              </div>

              <div className="space-y-2 pt-2">
                <Label>Orientation</Label>
                <div className="flex border border-gray-200">
                  <button 
                    className={`flex-1 py-2 text-sm font-bold uppercase ${orientation === 'portrait' ? 'bg-black text-white' : 'bg-white text-gray-600 hover:bg-gray-50'}`}
                    onClick={() => setOrientation('portrait')}
                  >
                    Portrait
                  </button>
                  <button 
                    className={`flex-1 py-2 text-sm font-bold uppercase ${orientation === 'landscape' ? 'bg-black text-white' : 'bg-white text-gray-600 hover:bg-gray-50'}`}
                    onClick={() => setOrientation('landscape')}
                  >
                    Landscape
                  </button>
                </div>
              </div>
            </div>

            {/* Section 3: Text */}
            {layoutStyle !== 'clean' && (
              <div className="space-y-4">
                <div className="flex items-center gap-2 text-lg font-bold border-b border-gray-100 pb-2">
                  <Type className="w-5 h-5" />
                  <h2>Text</h2>
                </div>
                
                <div className="space-y-3">
                  <div>
                    <Label htmlFor="title">Headline</Label>
                    <Input 
                      id="title" 
                      value={title} 
                      onChange={(e) => setTitle(e.target.value)}
                      className="rounded-none border-gray-300 focus:border-black"
                    />
                  </div>
                  <div>
                    <Label htmlFor="subtitle">Subtitle / Coordinates</Label>
                    <Input 
                      id="subtitle" 
                      value={subtitle} 
                      onChange={(e) => setSubtitle(e.target.value)}
                      className="rounded-none border-gray-300 focus:border-black"
                    />
                  </div>
                </div>
              </div>
            )}

            {/* Section 4: Size */}
            <div className="space-y-4 pb-24">
              <div className="flex items-center gap-2 text-lg font-bold border-b border-gray-100 pb-2">
                <Palette className="w-5 h-5" />
                <h2>Size & Format</h2>
              </div>
              
              <div className="grid grid-cols-3 gap-2">
                {['8x10', '12x18', '18x24', '24x36'].map((s) => (
                  <button
                    key={s}
                    className={`py-2 border text-sm font-bold ${size === s ? 'border-black bg-black text-white' : 'border-gray-200 hover:border-black'}`}
                    onClick={() => setSize(s)}
                  >
                    {s}
                  </button>
                ))}
              </div>
            </div>
          </div>

          {/* Sticky Footer Action */}
          <div className="fixed bottom-0 left-0 lg:w-1/3 w-full bg-white border-t border-gray-200 p-4 shadow-lg z-20">
            <div className="flex items-center justify-between mb-2">
              <span className="text-sm text-gray-500">Total</span>
              <span className="text-xl font-bold">$59.00</span>
            </div>
            <Button className="w-full rounded-none bg-black text-white hover:bg-gray-800 py-6 text-lg uppercase tracking-widest font-bold">
              Add to Cart
            </Button>
          </div>
        </div>

        {/* Right Panel: Live Preview */}
        <div className="w-full lg:w-2/3 bg-gray-100 flex items-center justify-center p-8 relative overflow-hidden">
          {/* Map Controls Overlay */}
          <div className="absolute top-8 right-8 z-10 flex flex-col gap-2">
            <Button 
              variant="outline" 
              size="icon" 
              className="bg-white border-gray-200 hover:bg-gray-50 rounded-none shadow-sm"
              onClick={handleZoomIn}
            >
              <ZoomIn className="w-4 h-4" />
            </Button>
            <Button 
              variant="outline" 
              size="icon" 
              className="bg-white border-gray-200 hover:bg-gray-50 rounded-none shadow-sm"
              onClick={handleZoomOut}
            >
              <ZoomOut className="w-4 h-4" />
            </Button>
          </div>

          {/* Poster Preview */}
          <div 
            className={`bg-white shadow-2xl relative transition-all duration-500 ease-in-out flex flex-col ${
              orientation === 'landscape' ? 'aspect-[4/3] w-full max-w-4xl' : 'aspect-[3/4] h-full max-h-[80vh]'
            }`}
          >
            {/* Map Area */}
            <div className={`relative flex-grow overflow-hidden ${
              layoutStyle === 'classic' ? 'm-8 mb-0 border border-black' : 
              layoutStyle === 'circle' ? 'm-8 mb-0 flex items-center justify-center' :
              'm-0'
            }`}>
              {layoutStyle === 'circle' ? (
                <div className="w-full aspect-square rounded-full overflow-hidden border border-black relative max-h-full">
                  <div className="absolute inset-0 bg-gray-200">
                    <InteractiveMap 
                      viewState={viewState}
                      onMove={(evt) => setViewState({
                        longitude: evt.viewState.longitude,
                        latitude: evt.viewState.latitude,
                        zoom: evt.viewState.zoom
                      })}
                      className="w-full h-full grayscale contrast-125"
                    />
                  </div>
                </div>
              ) : (
                <div className="absolute inset-0 bg-gray-200">
                  <InteractiveMap 
                    viewState={viewState}
                    onMove={(evt) => setViewState({
                      longitude: evt.viewState.longitude,
                      latitude: evt.viewState.latitude,
                      zoom: evt.viewState.zoom
                    })}
                    className="w-full h-full grayscale contrast-125"
                  />
                  
                  {/* Overlay for "Modern" style text */}
                  {layoutStyle === 'modern' && (
                    <div className="absolute bottom-12 left-0 right-0 text-center z-10 pointer-events-none">
                      <h2 className="text-6xl font-bold tracking-tighter text-white drop-shadow-lg uppercase mb-2">{title}</h2>
                      <p className="text-xl font-light tracking-[0.2em] text-white drop-shadow-md uppercase">{subtitle}</p>
                    </div>
                  )}
                </div>
              )}
            </div>

            {/* Text Area (Classic & Circle Style) */}
            {(layoutStyle === 'classic' || layoutStyle === 'circle') && (
              <div className="h-48 flex flex-col items-center justify-center p-8 bg-white">
                <h2 className="text-5xl font-bold tracking-tighter text-black uppercase mb-3">{title}</h2>
                <div className="w-12 h-0.5 bg-black mb-3"></div>
                <p className="text-sm font-light tracking-[0.3em] text-gray-600 uppercase">{subtitle}</p>
              </div>
            )}
          </div>
          
          {/* Dimensions Label */}
          <div className="absolute bottom-8 right-8 text-gray-400 text-xs font-mono">
            PREVIEW MODE • {size} • {orientation.toUpperCase()}
          </div>
        </div>
      </div>
    </Layout>
  );
}
