import Layout from "@/components/Layout";
import { Button } from "@/components/ui/button";

export default function AboutWireframe() {
  return (
    <Layout>
      {/* Hero */}
      <div className="relative h-[60vh] bg-black flex items-center justify-center overflow-hidden">
        <img src="/images/community-banner.jpg" alt="Community" className="absolute inset-0 w-full h-full object-cover opacity-50" />
        <div className="relative z-10 text-center text-white max-w-3xl px-4">
          <h1 className="text-5xl md:text-7xl font-bold tracking-tighter mb-6">MORE THAN <br/> JUST MAPS</h1>
          <p className="text-xl font-light text-gray-300">
            We are a design studio committed to rebuilding our community, one print at a time.
          </p>
        </div>
      </div>

      {/* Mission */}
      <div className="py-24 bg-white">
        <div className="container mx-auto px-4 md:px-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
            <div className="space-y-6">
              <h2 className="text-3xl font-bold tracking-tight">The Eaton Fire Initiative</h2>
              <p className="text-gray-600 leading-relaxed">
                The recent Eaton Fire devastated parts of our beloved Altadena and Pasadena foothills. 
                As locals, we felt the impact deeply. elv48.me was born not just as a creative outlet, 
                but as a vehicle for support.
              </p>
              <p className="text-gray-600 leading-relaxed">
                We pledge a significant portion of our proceeds to:
              </p>
              <ul className="space-y-4 border-l-2 border-black pl-6 py-2">
                <li className="font-medium">Direct financial aid to displaced families</li>
                <li className="font-medium">Native plant reforestation projects in Eaton Canyon</li>
                <li className="font-medium">Educational workshops on fire safety and resilience</li>
              </ul>
            </div>
            <div className="aspect-square bg-gray-100 relative">
               {/* Placeholder for mission image */}
               <div className="absolute inset-0 flex items-center justify-center text-gray-400">
                 Mission Image / Founder Photo
               </div>
            </div>
          </div>
        </div>
      </div>

      {/* Stats / Impact */}
      <div className="py-16 bg-gray-50 border-y border-gray-200">
        <div className="container mx-auto px-4">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div>
              <div className="text-5xl font-bold mb-2">15%</div>
              <div className="text-sm uppercase tracking-widest text-gray-500">Donated per Sale</div>
            </div>
            <div>
              <div className="text-5xl font-bold mb-2">50+</div>
              <div className="text-sm uppercase tracking-widest text-gray-500">Families Supported</div>
            </div>
            <div>
              <div className="text-5xl font-bold mb-2">100%</div>
              <div className="text-sm uppercase tracking-widest text-gray-500">Local Commitment</div>
            </div>
          </div>
        </div>
      </div>

      {/* CTA */}
      <div className="py-24 text-center">
        <div className="container mx-auto px-4 max-w-2xl">
          <h2 className="text-3xl font-bold mb-6">Join Us in Rebuilding</h2>
          <p className="text-gray-600 mb-8">
            Every purchase makes a difference. Explore our collection of local maps and bring a piece of home into your space while supporting your neighbors.
          </p>
          <Button className="h-14 px-8 text-lg uppercase tracking-widest font-bold rounded-none">
            Shop the Collection
          </Button>
        </div>
      </div>
    </Layout>
  );
}
