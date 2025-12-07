-- Mindful Substance Data Series (MSDS) Database Schema
-- Version 2.0
-- Author: Manus AI for 48AXIOM

-- Enable UUID generation
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 1. Series Table
CREATE TABLE public.msds_series (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  name TEXT NOT NULL UNIQUE,
  signal_word TEXT NOT NULL,
  score_min INTEGER NOT NULL,
  score_max INTEGER NOT NULL,
  description TEXT,
  color_hex TEXT
);

-- 2. Categories Table
CREATE TABLE public.msds_categories (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  name TEXT NOT NULL UNIQUE,
  description TEXT,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 3. Pictograms Table
CREATE TABLE public.msds_pictograms (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  name TEXT NOT NULL UNIQUE,
  image_url TEXT NOT NULL,
  description TEXT,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 4. Processing Methods Table
CREATE TABLE public.msds_processing_methods (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  name TEXT NOT NULL UNIQUE,
  description TEXT,
  health_impact TEXT,
  environmental_impact TEXT,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 5. Ingredients Table
CREATE TABLE public.msds_ingredients (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  slug TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  common_names TEXT[],
  category_id UUID REFERENCES public.msds_categories(id),
  series_id UUID REFERENCES public.msds_series(id),
  signal_word TEXT CHECK (signal_word IN (
'DANGER', 'WARNING', 'CAUTION', 'NOTICE')),
  health_hazard_score INTEGER CHECK (health_hazard_score >= 0 AND health_hazard_score <= 100),
  exposure_frequency_score INTEGER CHECK (exposure_frequency_score >= 0 AND exposure_frequency_score <= 100),
  contamination_intensity_score INTEGER CHECK (contamination_intensity_score >= 0 AND contamination_intensity_score <= 100),
  transparency_score INTEGER CHECK (transparency_score >= 0 AND transparency_score <= 100),
  total_score INTEGER GENERATED ALWAYS AS (health_hazard_score + exposure_frequency_score + contamination_intensity_score + transparency_score) STORED,
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
CREATE INDEX idx_ingredients_slug ON public.msds_ingredients(slug);

-- 6. Ingredient-Pictogram Junction Table
CREATE TABLE public.msds_ingredient_pictograms (
  ingredient_id UUID NOT NULL REFERENCES public.msds_ingredients(id) ON DELETE CASCADE,
  pictogram_id UUID NOT NULL REFERENCES public.msds_pictograms(id) ON DELETE CASCADE,
  PRIMARY KEY (ingredient_id, pictogram_id)
);

-- 7. Ingredient-Processing Junction Table
CREATE TABLE public.msds_ingredient_processing (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  ingredient_id UUID NOT NULL REFERENCES public.msds_ingredients(id) ON DELETE CASCADE,
  processing_method_id UUID NOT NULL REFERENCES public.msds_processing_methods(id) ON DELETE CASCADE,
  notes TEXT
);

-- 8. References Table
CREATE TABLE public.msds_references (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  ingredient_id UUID NOT NULL REFERENCES public.msds_ingredients(id) ON DELETE CASCADE,
  title TEXT NOT NULL,
  url TEXT NOT NULL,
  author TEXT,
  publication_date DATE,
  created_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE INDEX idx_references_ingredient_id ON public.msds_references(ingredient_id);

-- 9. Recall Alerts Table
CREATE TABLE public.msds_recall_alerts (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  ingredient_id UUID REFERENCES public.msds_ingredients(id) ON DELETE CASCADE,
  recall_date DATE NOT NULL,
  product_name TEXT NOT NULL,
  reason TEXT,
  fda_url TEXT,
  severity TEXT,
  created_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE INDEX idx_recall_alerts_ingredient_id ON public.msds_recall_alerts(ingredient_id);

-- 10. Sticker Analytics Table
CREATE TABLE public.msds_sticker_analytics (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  sticker_id TEXT NOT NULL,
  ingredient_id UUID REFERENCES public.msds_ingredients(id),
  scanned_at TIMESTAMPTZ DEFAULT NOW(),
  ip_address INET,
  user_agent TEXT
);
CREATE INDEX idx_sticker_analytics_sticker_id ON public.msds_sticker_analytics(sticker_id);

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
  BEFORE UPDATE ON public.msds_ingredients
  FOR EACH ROW
  EXECUTE PROCEDURE public.handle_updated_at();
