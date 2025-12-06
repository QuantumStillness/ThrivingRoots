-- Mindful Ingredient Safety Standard (MISS) Database Schema
-- Version 1.0
-- Author: Manus AI for 48AXIOM

-- Enable UUID generation
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 1. Categories Table
-- Stores the different types of ingredients (e.g., 'Heavy Metal', 'Additive').
CREATE TABLE public.miss_categories (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  name TEXT NOT NULL UNIQUE,
  description TEXT,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 2. Pictograms Table
-- Stores the GHS-style pictograms for food hazards.
CREATE TABLE public.miss_pictograms (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  name TEXT NOT NULL UNIQUE, -- e.g., 'Bioaccumulation Hazard'
  image_url TEXT NOT NULL, -- URL to the pictogram image
  description TEXT,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 3. Ingredients Table
-- The core table for all food ingredients and contaminants.
CREATE TABLE public.miss_ingredients (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  slug TEXT NOT NULL UNIQUE, -- URL-friendly identifier (e.g., 'lead-in-spices')
  name TEXT NOT NULL,
  common_names TEXT[],
  category_id UUID REFERENCES public.categories(id),
  signal_word TEXT CHECK (signal_word IN ('DANGER', 'WARNING', 'CAUTION', 'NOTICE')),
  hazard_statements TEXT[],
  precautionary_statements TEXT[],
  description TEXT,
  common_sources TEXT[],
  health_effects TEXT,
  bioaccumulation_potential TEXT CHECK (bioaccumulation_potential IN ('HIGH', 'MODERATE', 'LOW', 'NONE')),
  vulnerable_populations TEXT[],
  intake_limit TEXT,
  mindful_alternatives TEXT[],
  mindfulness_prompt TEXT,
  environmental_impact TEXT,
  fda_status TEXT,
  created_at TIMESTAMPTZ DEFAULT NOW(),
  updated_at TIMESTAMPTZ DEFAULT NOW()
);
-- Add index for faster lookups by slug
CREATE INDEX idx_ingredients_slug ON public.ingredients(slug);

-- 4. Ingredient-Pictogram Junction Table
-- Many-to-many relationship between ingredients and pictograms.
CREATE TABLE public.miss_ingredient_pictograms (
  ingredient_id UUID NOT NULL REFERENCES public.ingredients(id) ON DELETE CASCADE,
  pictogram_id UUID NOT NULL REFERENCES public.pictograms(id) ON DELETE CASCADE,
  PRIMARY KEY (ingredient_id, pictogram_id)
);

-- 5. References Table
-- Stores scientific references and links them to ingredients.
CREATE TABLE public.miss_references (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  ingredient_id UUID NOT NULL REFERENCES public.ingredients(id) ON DELETE CASCADE,
  title TEXT NOT NULL,
  url TEXT NOT NULL,
  author TEXT,
  publication_date DATE,
  created_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE INDEX idx_references_ingredient_id ON public.references(ingredient_id);

-- 6. Recall Alerts Table
-- Stores data on food recalls, potentially populated via FDA API.
CREATE TABLE public.miss_recall_alerts (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  ingredient_id UUID REFERENCES public.ingredients(id) ON DELETE CASCADE,
  recall_date DATE NOT NULL,
  product_name TEXT NOT NULL,
  reason TEXT, -- e.g., 'Listeria', 'Salmonella', 'Undeclared Allergen'
  fda_url TEXT,
  severity TEXT, -- e.g., 'Class I', 'Class II', 'Class III'
  created_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE INDEX idx_recall_alerts_ingredient_id ON public.recall_alerts(ingredient_id);

-- 7. Sticker Analytics Table
-- Tracks QR code scans and engagement.
CREATE TABLE public.miss_sticker_analytics (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  sticker_id TEXT NOT NULL, -- e.g., 'sticker-01-lead'
  ingredient_id UUID REFERENCES public.ingredients(id),
  scanned_at TIMESTAMPTZ DEFAULT NOW(),
  ip_address INET, -- For anonymized location data
  user_agent TEXT -- To track device types
);
CREATE INDEX idx_sticker_analytics_sticker_id ON public.sticker_analytics(sticker_id);

-- Function to automatically update the 'updated_at' timestamp
CREATE OR REPLACE FUNCTION public.handle_updated_at() 
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger to call the function before any update on the ingredients table
CREATE TRIGGER on_ingredients_update
  BEFORE UPDATE ON public.ingredients
  FOR EACH ROW
  EXECUTE PROCEDURE public.handle_updated_at();
