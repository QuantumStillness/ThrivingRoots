import { useState } from "react";
import Layout from "@/components/Layout";
import { Button } from "@/components/ui/button";
import { BookOpen, Github, Globe, Clock, Play, X } from "lucide-react";
import { meditationGuides } from "@/data/meditationGuides";
import { MeditationGuide } from "@/types/meditation";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/components/ui/dialog";

export default function StayCurious() {
  const [selectedMeditation, setSelectedMeditation] = useState<MeditationGuide | null>(null);

  return (
    <Layout>
      <div className="bg-gray-50 py-24">
        <div className="container mx-auto px-4 text-center">
          <h1 className="text-4xl md:text-6xl font-bold tracking-tighter mb-6 uppercase">Stay Curious</h1>
          <p className="text-xl text-gray-600 max-w-2xl mx-auto">
            Open source resources, community knowledge, and the stories behind our maps.
          </p>
        </div>
      </div>

      <div className="container mx-auto px-4 py-16">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-24">
          <div className="p-8 border border-gray-200 hover:border-black transition-colors group">
            <BookOpen className="w-10 h-10 mb-6 text-gray-400 group-hover:text-black transition-colors" />
            <h3 className="text-2xl font-bold mb-4">Map Data Sources</h3>
            <p className="text-gray-600 mb-6">
              Learn about the OpenStreetMap data we use to create our detailed city and trail maps.
            </p>
            <Button variant="link" className="p-0 text-black font-bold uppercase tracking-widest">Read More</Button>
          </div>

          <div className="p-8 border border-gray-200 hover:border-black transition-colors group">
            <Github className="w-10 h-10 mb-6 text-gray-400 group-hover:text-black transition-colors" />
            <h3 className="text-2xl font-bold mb-4">Open Source</h3>
            <p className="text-gray-600 mb-6">
              Explore our code contributions and tools for generating map art.
            </p>
            <Button variant="link" className="p-0 text-black font-bold uppercase tracking-widest">View Repo</Button>
          </div>

          <div className="p-8 border border-gray-200 hover:border-black transition-colors group">
            <Globe className="w-10 h-10 mb-6 text-gray-400 group-hover:text-black transition-colors" />
            <h3 className="text-2xl font-bold mb-4">Community Stories</h3>
            <p className="text-gray-600 mb-6">
              Read stories from the Altadena and Pasadena community about rebuilding and resilience.
            </p>
            <Button variant="link" className="p-0 text-black font-bold uppercase tracking-widest">Read Stories</Button>
          </div>
        </div>

        {/* Meditations and Breathwork Section */}
        <div className="border-t border-gray-200 pt-24">
          <div className="max-w-4xl mx-auto text-center mb-16">
            <h2 className="text-3xl md:text-5xl font-bold tracking-tight mb-6">Meditations & Breathwork</h2>
            <p className="text-xl text-gray-600 font-light">
              Tools for grounding, focus, and finding your center.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {meditationGuides.map((meditation) => (
              <div 
                key={meditation.id}
                className="border border-gray-200 p-8 hover:border-black transition-all duration-300 cursor-pointer group bg-white"
                onClick={() => setSelectedMeditation(meditation)}
              >
                <div className="flex justify-between items-start mb-6">
                  <span className="text-xs font-bold uppercase tracking-widest text-gray-400 group-hover:text-black transition-colors">
                    {meditation.type}
                  </span>
                  <div className="flex items-center text-gray-400 group-hover:text-black transition-colors">
                    <Clock className="w-4 h-4 mr-1" />
                    <span className="text-xs font-bold">{meditation.duration} min</span>
                  </div>
                </div>
                
                <h3 className="text-2xl font-bold mb-3 group-hover:translate-x-1 transition-transform duration-300">
                  {meditation.title}
                </h3>
                
                <p className="text-gray-600 mb-8 line-clamp-3">
                  {meditation.description}
                </p>
                
                <div className="flex items-center text-black font-bold uppercase tracking-widest text-sm group-hover:underline decoration-1 underline-offset-4">
                  <Play className="w-4 h-4 mr-2" />
                  Start Practice
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Meditation Detail Dialog */}
      <Dialog open={!!selectedMeditation} onOpenChange={(open) => !open && setSelectedMeditation(null)}>
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto bg-white rounded-none border-black p-0">
          {selectedMeditation && (
            <div className="p-8 md:p-12">
              <div className="flex justify-between items-start mb-8">
                <div>
                  <span className="text-xs font-bold uppercase tracking-widest text-gray-400 mb-2 block">
                    {selectedMeditation.type} • {selectedMeditation.duration} min
                  </span>
                  <DialogTitle className="text-3xl md:text-4xl font-bold tracking-tight">
                    {selectedMeditation.title}
                  </DialogTitle>
                </div>
              </div>

              <DialogDescription className="text-lg text-gray-600 mb-10 font-light leading-relaxed">
                {selectedMeditation.description}
              </DialogDescription>

              <div className="space-y-8">
                {selectedMeditation.steps.map((step, index) => (
                  <div key={index} className="flex items-start gap-6 group">
                    <span className="text-2xl font-serif font-bold text-gray-200 group-hover:text-black transition-colors">
                      {(index + 1).toString().padStart(2, '0')}
                    </span>
                    <p className="text-gray-800 leading-relaxed pt-1">
                      {step}
                    </p>
                  </div>
                ))}
              </div>

              <div className="mt-12 pt-8 border-t border-gray-100 flex justify-center">
                <Button 
                  className="rounded-none px-8 py-6 uppercase tracking-widest font-bold bg-black text-white hover:bg-gray-800"
                  onClick={() => setSelectedMeditation(null)}
                >
                  Complete Practice
                </Button>
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>
    </Layout>
  );
}
