import Layout from "@/components/Layout";
import ProductCard from "@/components/ProductCard";
import { Button } from "@/components/ui/button";
import { ChevronDown } from "lucide-react";

export default function CollectionWireframe() {
  return (
    <Layout>
      {/* Collection Header */}
      <div className="bg-gray-50 py-16 md:py-24">
        <div className="container mx-auto px-4 text-center max-w-2xl">
          <h1 className="text-4xl md:text-5xl font-bold tracking-tight mb-4">Local Maps</h1>
          <p className="text-gray-500 text-lg">
            Explore our curated collection of Altadena, Pasadena, and San Gabriel Mountain maps. 
            Each purchase supports the Eaton Fire recovery initiative.
          </p>
        </div>
      </div>

      <div className="container mx-auto px-4 py-12">
        {/* Filters & Sort */}
        <div className="flex flex-col md:flex-row justify-between items-center mb-8 pb-4 border-b border-gray-100 gap-4">
          <div className="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0">
            <Button variant="outline" className="rounded-full text-sm border-gray-200">All Maps</Button>
            <Button variant="ghost" className="rounded-full text-sm text-gray-500">Hiking Trails</Button>
            <Button variant="ghost" className="rounded-full text-sm text-gray-500">City Grids</Button>
            <Button variant="ghost" className="rounded-full text-sm text-gray-500">Topographic</Button>
          </div>
          
          <div className="flex items-center gap-2">
            <span className="text-sm text-gray-500">Sort by:</span>
            <button className="flex items-center gap-1 text-sm font-medium">
              Featured <ChevronDown className="w-4 h-4" />
            </button>
          </div>
        </div>

        {/* Product Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
          <ProductCard 
            id="1"
            title="Altadena Custom Map"
            price={25.00}
            image="/images/product-map-altadena.jpg"
            category="City Grid"
            isNew={true}
          />
          <ProductCard 
            id="2"
            title="Pasadena City Map"
            price={25.00}
            image="/images/product-map-pasadena.jpg"
            category="City Grid"
          />
          <ProductCard 
            id="3"
            title="Eaton Canyon Trail"
            price={25.00}
            image="/images/product-trail-eaton.jpg"
            category="Hiking Trail"
            isNew={true}
          />
          <ProductCard 
            id="4"
            title="Echo Mountain Loop"
            price={25.00}
            image="/images/product-trail-echo.jpg"
            category="Hiking Trail"
          />
          <ProductCard 
            id="5"
            title="San Gabriel Regional"
            price={35.00}
            image="/images/product-map-regional.jpg"
            category="Regional Map"
          />
          {/* Placeholders for more products */}
          {[6, 7, 8].map((i) => (
            <div key={i} className="group cursor-pointer opacity-50">
              <div className="aspect-[4/5] bg-gray-100 mb-4 flex items-center justify-center text-gray-300">
                Coming Soon
              </div>
              <div className="h-4 w-2/3 bg-gray-100 mb-2" />
              <div className="h-4 w-1/4 bg-gray-100" />
            </div>
          ))}
        </div>

        {/* Pagination */}
        <div className="flex justify-center mt-16">
          <div className="flex gap-2">
            <Button variant="outline" className="w-10 h-10 p-0 border-black bg-black text-white">1</Button>
            <Button variant="outline" className="w-10 h-10 p-0 border-gray-200 text-gray-500 hover:border-black hover:text-black">2</Button>
            <Button variant="outline" className="w-10 h-10 p-0 border-gray-200 text-gray-500 hover:border-black hover:text-black">3</Button>
          </div>
        </div>
      </div>
    </Layout>
  );
}
