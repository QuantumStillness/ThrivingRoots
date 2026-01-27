import Layout from "@/components/Layout";
import { Button } from "@/components/ui/button";
import { ArrowRight } from "lucide-react";
import { Link } from "wouter";

const cities = [
  { name: "San Gabriel", image: "/images/map-san-gabriel.jpg", price: "$25.00" },
  { name: "Pasadena", image: "/images/product-map-pasadena.jpg", price: "$25.00" },
  { name: "Altadena", image: "/images/product-map-altadena.jpg", price: "$25.00" },
  { name: "Monrovia", image: "/images/map-monrovia.jpg", price: "$25.00" },
  { name: "Claremont", image: "/images/map-claremont.jpg", price: "$25.00" },
  { name: "Duarte", image: "/images/map-duarte.jpg", price: "$25.00" },
  { name: "Arcadia", image: "/images/map-arcadia.jpg", price: "$25.00" },
  { name: "Eagle Rock", image: "/images/map-eagle-rock.jpg", price: "$25.00" },
  { name: "El Sereno", image: "/images/map-el-sereno.jpg", price: "$25.00" },
];

export default function SanGabrielValley() {
  return (
    <Layout>
      {/* Hero Section */}
      <div className="relative h-[40vh] bg-black text-white flex items-center justify-center overflow-hidden">
        <div className="absolute inset-0 bg-[url('/images/hero-altadena-mountains.jpg')] bg-cover bg-center opacity-40" />
        <div className="relative z-10 text-center space-y-4 px-4">
          <h1 className="text-5xl md:text-7xl font-bold tracking-tighter">San Gabriel Valley</h1>
          <p className="text-xl md:text-2xl font-light tracking-wide max-w-2xl mx-auto">
            A curated collection of minimalist maps celebrating the cities of the SGV.
          </p>
        </div>
      </div>

      {/* Collection Grid */}
      <div className="container mx-auto px-4 py-24">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-16">
          {cities.map((city) => (
            <Link key={city.name} href="/product" className="group cursor-pointer block">
              <div className="aspect-[4/5] bg-gray-100 overflow-hidden mb-6 relative">
                <img 
                  src={city.image} 
                  alt={`${city.name} Map Print`}
                  className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                />
                <div className="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-500" />
                <div className="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                  <Button size="icon" className="rounded-full bg-white text-black hover:bg-black hover:text-white">
                    <ArrowRight className="w-4 h-4" />
                  </Button>
                </div>
              </div>
              <div className="space-y-1">
                <h3 className="text-xl font-bold tracking-tight">{city.name}</h3>
                <p className="text-gray-500">California Series</p>
                <p className="font-medium mt-2">{city.price}</p>
              </div>
            </Link>
          ))}
        </div>
      </div>

      {/* Collection Info */}
      <div className="bg-gray-50 py-24">
        <div className="container mx-auto px-4 text-center max-w-3xl">
          <h2 className="text-3xl font-bold mb-6">About the Collection</h2>
          <p className="text-lg text-gray-600 leading-relaxed">
            The San Gabriel Valley Collection honors the diverse communities nestled against the San Gabriel Mountains. 
            Each map is meticulously designed to highlight the unique street grids and geography that define these 
            historic neighborhoods. From the winding roads of the foothills to the structured avenues of the valley floor, 
            these prints capture the essence of home.
          </p>
        </div>
      </div>
    </Layout>
  );
}
