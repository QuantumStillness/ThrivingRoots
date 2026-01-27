import Layout from "@/components/Layout";
import ProductCard from "@/components/ProductCard";
import { Button } from "@/components/ui/button";
import { ArrowRight, Star, Download, ShieldCheck, Zap } from "lucide-react";
import { motion } from "framer-motion";
import { Link } from "wouter";

// Animation variants
const fadeInUp = {
  hidden: { opacity: 0, y: 60 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.8 } }
};

const staggerContainer = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: {
      staggerChildren: 0.2
    }
  }
};

export default function Home() {
  return (
    <Layout>
      {/* Hero Section */}
      <section className="relative h-[90vh] w-full overflow-hidden flex items-center justify-center bg-gray-50">
        <div className="absolute inset-0 z-0">
          <img 
            src="/images/hero-altadena-mountains.jpg" 
            alt="San Gabriel Mountains overlooking Altadena and Pasadena" 
            className="w-full h-full object-cover opacity-90"
          />
          <div className="absolute inset-0 bg-black/20" />
        </div>
        
        <div className="container relative z-10 px-4 md:px-6">
          <motion.div 
            initial="hidden"
            animate="visible"
            variants={staggerContainer}
            className="max-w-3xl space-y-8"
          >
            <motion.h1 
              variants={fadeInUp}
              className="text-5xl md:text-7xl lg:text-8xl font-bold tracking-tighter text-white drop-shadow-lg"
            >
              REBUILDING <br />
              <span className="italic font-serif font-light">TOGETHER</span>
            </motion.h1>
            
            <motion.p 
              variants={fadeInUp}
              className="text-lg md:text-xl text-white/90 max-w-lg font-light leading-relaxed drop-shadow-md"
            >
              Featuring exclusive Altadena and Pasadena map prints. 
              Proceeds support local Eaton Fire recovery initiatives and community rebuilding efforts.
            </motion.p>
            
            <motion.div variants={fadeInUp} className="flex flex-col sm:flex-row gap-4 pt-4">
              <Button size="lg" className="bg-white text-black hover:bg-gray-100 rounded-none px-8 py-6 text-sm uppercase tracking-widest font-bold transition-all hover:scale-105">
                Shop Local Maps
              </Button>
              <Button size="lg" variant="outline" className="border-white text-white hover:bg-white hover:text-black rounded-none px-8 py-6 text-sm uppercase tracking-widest font-bold backdrop-blur-sm transition-all">
                Learn More
              </Button>
            </motion.div>
          </motion.div>
        </div>
      </section>

      {/* Value Props (Updated Icons) */}
      <section className="py-16 border-t border-gray-100 bg-white">
        <div className="container mx-auto px-4 md:px-6">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div className="flex flex-col items-center text-center space-y-4">
              <div className="p-4 rounded-full bg-orange-50 text-orange-500">
                <Zap className="w-8 h-8" />
              </div>
              <h3 className="font-bold text-lg uppercase tracking-wide">Instant Download</h3>
              <p className="text-gray-500 text-sm max-w-xs">Get your files immediately. No shipping wait times.</p>
            </div>
            
            <div className="flex flex-col items-center text-center space-y-4">
              <div className="p-4 rounded-full bg-orange-50 text-orange-500">
                <Star className="w-8 h-8" />
              </div>
              <h3 className="font-bold text-lg uppercase tracking-wide">Community Support</h3>
              <p className="text-gray-500 text-sm max-w-xs">Proceeds directly aid Eaton Fire recovery efforts.</p>
            </div>
            
            <div className="flex flex-col items-center text-center space-y-4">
              <div className="p-4 rounded-full bg-orange-50 text-orange-500">
                <ShieldCheck className="w-8 h-8" />
              </div>
              <h3 className="font-bold text-lg uppercase tracking-wide">High Quality</h3>
              <p className="text-gray-500 text-sm max-w-xs">Museum-grade resolution for large format printing.</p>
            </div>
          </div>
        </div>
      </section>

      {/* Best Sellers Section Placeholder */}
      <section id="best-sellers" className="py-24 bg-white">
        <div className="container mx-auto px-4 md:px-6">
          <div className="flex justify-between items-end mb-12">
            <div>
              <h2 className="text-3xl md:text-4xl font-bold tracking-tight mb-2">Best Sellers</h2>
              <p className="text-gray-500">Our most loved pieces this month.</p>
            </div>
            <a href="#" className="hidden md:flex items-center gap-2 text-sm font-bold uppercase tracking-widest hover:text-gray-500 transition-colors group">
              View All <ArrowRight className="w-4 h-4 transition-transform group-hover:translate-x-1" />
            </a>
          </div>
          
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <ProductCard 
              id="1"
              title="Altadena Custom Map"
              price={25.00}
              image="/images/product-map-altadena.jpg"
              category="Local Maps"
              isNew={true}
            />
            <ProductCard 
              id="2"
              title="Pasadena City Map"
              price={25.00}
              image="/images/product-map-pasadena.jpg"
              category="Local Maps"
              isNew={true}
            />
            <ProductCard 
              id="3"
              title="Eaton Canyon Topography"
              price={25.00}
              image="/images/product-map-1.jpg"
              category="Local Maps"
            />
            <ProductCard 
              id="4"
              title="San Gabriel Mountains"
              price={25.00}
              image="/images/hero-altadena-mountains.jpg"
              category="Photography"
            />
          </div>
          
          <div className="mt-12 text-center md:hidden">
            <Button variant="outline" className="rounded-none px-8 uppercase tracking-widest">View All</Button>
          </div>
        </div>
      </section>

      {/* Community Initiative Section */}
      <section id="community" className="py-24 bg-black text-white">
        <div className="container mx-auto px-4 md:px-6">
          <div className="flex flex-col md:flex-row items-center gap-12">
            <div className="w-full md:w-1/2 space-y-6">
              <h2 className="text-3xl md:text-5xl font-bold tracking-tight">Supporting Eaton Fire Recovery</h2>
              <p className="text-gray-400 leading-relaxed text-lg">
                We are committed to our community. A portion of every sale from our Altadena and Pasadena collections goes directly to local initiatives supporting recovery and rebuilding efforts after the Eaton Fire.
              </p>
              <ul className="space-y-4 text-gray-300">
                <li className="flex items-center gap-3">
                  <div className="w-2 h-2 bg-white rounded-full" />
                  Direct aid to affected families
                </li>
                <li className="flex items-center gap-3">
                  <div className="w-2 h-2 bg-white rounded-full" />
                  Reforestation of Eaton Canyon
                </li>
                <li className="flex items-center gap-3">
                  <div className="w-2 h-2 bg-white rounded-full" />
                  Community resilience workshops
                </li>
              </ul>
              <Button className="bg-white text-black hover:bg-gray-200 rounded-none px-8 py-6 uppercase tracking-widest font-bold mt-4">
                Read Our Mission
              </Button>
            </div>
            
            <div className="w-full md:w-1/2">
              <div className="relative aspect-square overflow-hidden">
                 <img 
                  src="/images/community-banner.jpg" 
                  alt="Community Rebuilding Art" 
                  className="w-full h-full object-cover opacity-90"
                />
                <div className="absolute inset-0 border border-white/20 m-4" />
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Collections Section Placeholder */}
      <section id="collections" className="py-24 bg-gray-50">
        <div className="container mx-auto px-4 md:px-6">
          <h2 className="text-3xl md:text-4xl font-bold tracking-tight mb-12 text-center">Curated Collections</h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="relative aspect-[3/4] md:aspect-auto md:h-[500px] group overflow-hidden cursor-pointer">
              <img src="/images/product-abstract-1.jpg" alt="Abstract Collection" className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
              <div className="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-colors" />
              <div className="absolute bottom-8 left-8 text-white">
                <h3 className="text-2xl font-bold mb-2">Abstract</h3>
                <p className="text-sm uppercase tracking-widest border-b border-white inline-block pb-1">View Collection</p>
              </div>
            </div>
            <Link href="/collections/san-gabriel-valley">
              <div className="relative aspect-[3/4] md:aspect-auto md:h-[500px] group overflow-hidden cursor-pointer block">
                <img src="/images/product-map-1.jpg" alt="San Gabriel Valley Collection" className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                <div className="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-colors" />
                <div className="absolute bottom-8 left-8 text-white">
                  <h3 className="text-2xl font-bold mb-2">San Gabriel Valley</h3>
                  <p className="text-sm uppercase tracking-widest border-b border-white inline-block pb-1">View Collection</p>
                </div>
              </div>
            </Link>
            <div className="relative aspect-[3/4] md:aspect-auto md:h-[500px] group overflow-hidden cursor-pointer">
              <img src="/images/product-typography-1.jpg" alt="Typography Collection" className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
              <div className="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-colors" />
              <div className="absolute bottom-8 left-8 text-white">
                <h3 className="text-2xl font-bold mb-2">Typography</h3>
                <p className="text-sm uppercase tracking-widest border-b border-white inline-block pb-1">View Collection</p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </Layout>
  );
}
