import Layout from "@/components/Layout";
import InteractiveMap from "@/components/InteractiveMap";
import { Button } from "@/components/ui/button";
import { Star, ShieldCheck, Truck, ArrowRight } from "lucide-react";

export default function ProductWireframe() {
  return (
    <Layout>
      <div className="container mx-auto px-4 py-12">
        {/* Breadcrumb */}
        <div className="text-sm text-gray-500 mb-8">
          Home / Local Maps / <span className="text-black font-medium">Altadena Custom Map</span>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-24">
          {/* Image Gallery */}
          <div className="space-y-4">
            <div className="aspect-[4/5] bg-gray-100 w-full overflow-hidden">
              <img src="/images/product-map-altadena.jpg" alt="Product Main" className="w-full h-full object-cover" />
            </div>
            
            {/* Interactive Map Preview */}
            <div className="mt-8">
              <h3 className="text-sm font-bold uppercase tracking-widest mb-3">Live Map Preview</h3>
              <InteractiveMap 
                style={{ width: '100%', height: '300px' }}
                initialViewState={{
                  longitude: -118.1319,
                  latitude: 34.1800,
                  zoom: 12
                }}
                markers={[
                  { longitude: -118.1319, latitude: 34.1800, label: "Altadena" }
                ]}
              />
            </div>

            <div className="grid grid-cols-4 gap-4">
              {[1, 2, 3, 4].map((i) => (
                <div key={i} className="aspect-square bg-gray-100 cursor-pointer hover:opacity-80 transition-opacity" />
              ))}
            </div>
          </div>

          {/* Product Details */}
          <div className="space-y-8">
            <div>
              <h1 className="text-4xl font-bold tracking-tight mb-2">Altadena Custom Map</h1>
              <div className="flex items-center gap-2 mb-4">
                <div className="flex text-orange-500">
                  {[1, 2, 3, 4, 5].map((i) => (
                    <Star key={i} className="w-4 h-4 fill-current" />
                  ))}
                </div>
                <span className="text-sm text-gray-500">(24 reviews)</span>
              </div>
              <p className="text-2xl font-medium">$25.00</p>
            </div>

            <div className="prose prose-sm text-gray-600">
              <p>
                A detailed, minimalist map print of Altadena, California. Featuring local streets, 
                landmarks, and the winding paths of the foothills. Perfect for residents and 
                nature lovers alike.
              </p>
            </div>

            {/* Customization Options */}
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-bold uppercase tracking-wide mb-2">Size</label>
                <div className="flex flex-wrap gap-3">
                  {['8x10"', '11x14"', '16x20"', '18x24"'].map((size) => (
                    <button key={size} className="px-4 py-2 border border-gray-200 hover:border-black transition-colors text-sm">
                      {size}
                    </button>
                  ))}
                </div>
              </div>
              
              <div>
                <label className="block text-sm font-bold uppercase tracking-wide mb-2">Style</label>
                <div className="flex flex-wrap gap-3">
                  {['Modern Black', 'Classic White', 'Vintage'].map((style) => (
                    <button key={style} className="px-4 py-2 border border-gray-200 hover:border-black transition-colors text-sm">
                      {style}
                    </button>
                  ))}
                </div>
              </div>
            </div>

            {/* Actions */}
            <div className="space-y-4 pt-4 border-t border-gray-100">
              <Button className="w-full h-14 text-lg uppercase tracking-widest font-bold rounded-none" onClick={() => {
                // In a real app, this would add to cart. For demo, we trigger download.
                const link = document.createElement('a');
                link.href = '/downloads/altadena-map-print.jpg';
                link.download = 'altadena-map-print.jpg';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
              }}>
                Download Digital File ($25.00)
              </Button>
              <p className="text-xs text-center text-gray-500">
                <span className="font-bold">Instant Download:</span> High-resolution JPG file (300 DPI) suitable for printing up to 24x36".
              </p>
            </div>

            {/* Trust Badges */}
            <div className="grid grid-cols-2 gap-4 pt-8">
              <div className="flex items-center gap-3">
                <ShieldCheck className="w-5 h-5 text-gray-400" />
                <span className="text-sm text-gray-600">Secure Checkout</span>
              </div>
              <div className="flex items-center gap-3">
                <Truck className="w-5 h-5 text-gray-400" />
                <span className="text-sm text-gray-600">Instant Delivery</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Layout>
  );
}
