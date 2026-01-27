import { motion } from "framer-motion";
import { ShoppingCart, Eye } from "lucide-react";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

interface ProductCardProps {
  id: string | number;
  title: string;
  price: number;
  image: string;
  category: string;
  isNew?: boolean;
  className?: string;
}

export default function ProductCard({ id, title, price, image, category, isNew, className }: ProductCardProps) {
  return (
    <motion.div 
      initial={{ opacity: 0, y: 20 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, margin: "-50px" }}
      transition={{ duration: 0.5 }}
      className={cn("group cursor-pointer", className)}
    >
      <div className="relative aspect-[4/5] overflow-hidden bg-gray-100 mb-4">
        {/* Image */}
        <img 
          src={image} 
          alt={title} 
          className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
        />
        
        {/* Badges */}
        {isNew && (
          <div className="absolute top-3 left-3 bg-black text-white text-[10px] font-bold uppercase tracking-widest px-2 py-1">
            New
          </div>
        )}
        
        {/* Hover Actions */}
        <div className="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-2">
          <Button size="icon" variant="secondary" className="rounded-full bg-white text-black hover:bg-black hover:text-white transition-colors">
            <ShoppingCart className="w-4 h-4" />
          </Button>
          <Button size="icon" variant="secondary" className="rounded-full bg-white text-black hover:bg-black hover:text-white transition-colors">
            <Eye className="w-4 h-4" />
          </Button>
        </div>
      </div>
      
      {/* Info */}
      <div className="space-y-1">
        <p className="text-xs text-gray-500 uppercase tracking-wide">{category}</p>
        <h3 className="font-medium text-lg leading-tight group-hover:underline decoration-1 underline-offset-4">{title}</h3>
        <p className="text-sm font-semibold">${price.toFixed(2)}</p>
      </div>
    </motion.div>
  );
}
