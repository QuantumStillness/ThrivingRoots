import Layout from "@/components/Layout";
import ProductCard from "@/components/ProductCard";
import InteractiveMap from "@/components/InteractiveMap";
import { Button } from "@/components/ui/button";
import { ChevronDown } from "lucide-react";

export default function TouchGrass() {
  return (
    <Layout>
      {/* Header Section */}
      <div className="bg-white py-16 text-center border-b border-gray-100">
        <div className="container mx-auto px-4">
          <h1 className="text-4xl md:text-5xl font-bold tracking-tight mb-6 uppercase">Hiking Trail Map</h1>
          <div className="w-12 h-1 bg-orange-500 mx-auto mb-8" />
          
          <div className="max-w-4xl mx-auto space-y-6 text-gray-600 leading-relaxed text-left md:text-center">
            <p>
              There are many amazing trails worth traveling along throughout the United States. These include many trails that go along some of the best parks and mountain ranges in the country. Others go through multiple states and bring you through everything that makes a space so outstanding.
            </p>
            <p>
              You can also find some great trails well outside of the country. Some trails will go through major archeological sites while others move along some of the world’s tallest mountains.
            </p>
            <p>
              We understand that people who want to go outdoors and see the world will surely want to see what types of trails are out there. It is with this that we proudly sell numerous hiking trail maps that display all these great places and the many lands that they traverse along. You will see on each trail map how well various iconic trails move along.
            </p>
          </div>
        </div>
      </div>

      {/* Interactive Map Section */}
      <div className="container mx-auto px-4 py-12">
        <div className="max-w-6xl mx-auto">
          <h2 className="text-2xl font-bold mb-6 uppercase tracking-widest text-center">Explore Our Trails</h2>
          <InteractiveMap 
            style={{ width: '100%', height: '500px' }}
            initialViewState={{
              longitude: -118.10,
              latitude: 34.20,
              zoom: 11.5
            }}
            markers={[
              { longitude: -118.0966, latitude: 34.1958, label: "Eaton Canyon" },
              { longitude: -118.1226, latitude: 34.2115, label: "Echo Mountain" },
              { longitude: -118.1600, latitude: 34.2000, label: "Altadena" },
              { longitude: -118.1445, latitude: 34.1478, label: "Pasadena" }
            ]}
          />
          <p className="text-center text-xs text-gray-400 mt-4 uppercase tracking-widest">
            Interactive Preview • Zoom to explore terrain details
          </p>
        </div>
      </div>

      {/* Detailed Info Section */}
      <div className="bg-gray-50 py-16">
        <div className="container mx-auto px-4 max-w-4xl space-y-8 text-gray-700">
          <div>
            <h3 className="text-xl font-bold mb-4 text-black">How Each Trail Map Is Made</h3>
            <p className="leading-relaxed">
              We have put in great detail into each of the hiking trail maps we sell. Each map is made with a series of important parts that make a space attractive. First, we review the overall trail path alongside where it is located. We work to review how a path is organized so we can produce the trail exactly as it is laid out from one end to the next.
            </p>
          </div>
          
          <div>
            <h3 className="text-xl font-bold mb-4 text-black">What Trails Do We Highlight?</h3>
            <p className="leading-relaxed">
              The hiking trail maps that we sell include many of the most important trails in the world. Look at our prints to find some of the most important sites for hiking. Perhaps you might find a print depicting a trail you are familiar with or have already gone through.
            </p>
          </div>
        </div>
      </div>

      {/* Product Grid */}
      <div className="container mx-auto px-4 py-16">
        {/* Filters & Sort */}
        <div className="flex flex-col md:flex-row justify-between items-center mb-8 pb-4 border-b border-gray-100 gap-4">
          <div className="flex items-center gap-2">
            <span className="text-sm font-bold uppercase tracking-wide">Sort by:</span>
            <button className="flex items-center gap-1 text-sm font-medium hover:text-gray-600">
              Featured <ChevronDown className="w-4 h-4" />
            </button>
          </div>
          <div className="text-sm text-gray-500">
            12 Item(s)
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
          <ProductCard 
            id="eaton"
            title="Eaton Canyon Trail"
            price={29.99}
            image="/images/product-trail-eaton.jpg"
            category="Hiking Trail"
            isNew={true}
          />
          <ProductCard 
            id="echo"
            title="Echo Mountain Loop"
            price={29.99}
            image="/images/product-trail-echo.jpg"
            category="Hiking Trail"
          />
          <ProductCard 
            id="pct"
            title="Pacific Crest Trail"
            price={29.99}
            image="/images/hero-trails.jpg"
            category="Hiking Trail"
          />
          <ProductCard 
            id="at"
            title="Appalachian Trail"
            price={29.99}
            image="/images/product-map-regional.jpg"
            category="Hiking Trail"
          />
          {/* Placeholders */}
          {[1, 2, 3, 4, 5, 6, 7, 8].map((i) => (
            <div key={i} className="group cursor-pointer">
              <div className="aspect-[4/5] bg-gray-100 mb-4 overflow-hidden relative">
                <div className="absolute inset-0 flex items-center justify-center text-gray-300 font-bold uppercase tracking-widest">Coming Soon</div>
              </div>
              <h3 className="font-bold text-sm uppercase tracking-wide mb-1">Trail Map {i}</h3>
              <p className="text-gray-500 text-sm">$29.99</p>
            </div>
          ))}
        </div>
      </div>
    </Layout>
  );
}
