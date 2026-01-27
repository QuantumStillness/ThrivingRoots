import Layout from "@/components/Layout";
import ProductCard from "@/components/ProductCard";

export default function Sky() {
  return (
    <Layout>
      <div className="bg-black text-white py-24 text-center">
        <div className="container mx-auto px-4">
          <h1 className="text-4xl md:text-6xl font-bold tracking-tighter mb-6 uppercase">Keep Your Head to the Sky</h1>
          <p className="text-xl text-gray-400 max-w-2xl mx-auto font-light">
            Celestial maps and city skylines that remind us to look up and dream.
          </p>
        </div>
      </div>

      <div className="container mx-auto px-4 py-16">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
          <ProductCard 
            id="star-1"
            title="Pasadena Star Map"
            price={35.00}
            image="/images/product-abstract-1.jpg"
            category="Star Map"
          />
          <ProductCard 
            id="skyline-1"
            title="Los Angeles Skyline"
            price={35.00}
            image="/images/product-typography-1.jpg"
            category="Skyline"
          />
          <ProductCard 
            id="star-2"
            title="Altadena Night Sky"
            price={35.00}
            image="/images/product-map-1.jpg"
            category="Star Map"
          />
        </div>
      </div>
    </Layout>
  );
}
