-- Mindful Ingredient Safety Standard (MISS) Database: RLS, Functions, and API Logic
-- Version 1.0
-- Author: Manus AI for 48AXIOM

-- PART 1: Row Level Security (RLS) Policies
-- RLS ensures that your data is secure, enabling public read-only access while restricting write operations.

-- Enable RLS for all tables
ALTER TABLE public.categories ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.pictograms ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.ingredients ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.ingredient_pictograms ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.references ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.recall_alerts ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.sticker_analytics ENABLE ROW LEVEL SECURITY;

-- Create a read-only policy for public access on all tables
-- This allows anyone to read the data, which is necessary for the public-facing website.
CREATE POLICY "Public read-only access" ON public.categories FOR SELECT USING (true);
CREATE POLICY "Public read-only access" ON public.pictograms FOR SELECT USING (true);
CREATE POLICY "Public read-only access" ON public.ingredients FOR SELECT USING (true);
CREATE POLICY "Public read-only access" ON public.ingredient_pictograms FOR SELECT USING (true);
CREATE POLICY "Public read-only access" ON public.references FOR SELECT USING (true);
CREATE POLICY "Public read-only access" ON public.recall_alerts FOR SELECT USING (true);

-- Create a policy to allow anonymous inserts into the analytics table
-- This is crucial for tracking QR code scans from the public.
CREATE POLICY "Allow anonymous inserts for analytics" ON public.sticker_analytics FOR INSERT WITH CHECK (true);

-- Create policies to allow admin/service_role full access
-- This allows you (or your backend services) to insert, update, and delete data.
-- Replace `your_admin_role` with your actual admin role if you have one, or use the default service_role.
CREATE POLICY "Admin full access" ON public.categories FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Admin full access" ON public.pictograms FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Admin full access" ON public.ingredients FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Admin full access" ON public.ingredient_pictograms FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Admin full access" ON public.references FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Admin full access" ON public.recall_alerts FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Admin full access" ON public.sticker_analytics FOR ALL USING (auth.role() = 'service_role');

-- PART 2: PostgreSQL Functions (for RPC)
-- Functions allow you to bundle complex queries into a single API call, improving performance and simplifying frontend logic.

-- Function 1: Get all details for a single ingredient page.
-- This is the primary function your Next.js page will call.
CREATE OR REPLACE FUNCTION public.get_ingredient_details(ingredient_slug TEXT)
RETURNS JSONB
LANGUAGE plpgsql
AS $$
DECLARE
  ingredient_details JSONB;
BEGIN
  SELECT jsonb_build_object(
    'id', i.id,
    'slug', i.slug,
    'name', i.name,
    'common_names', i.common_names,
    'signal_word', i.signal_word,
    'hazard_statements', i.hazard_statements,
    'precautionary_statements', i.precautionary_statements,
    'description', i.description,
    'common_sources', i.common_sources,
    'health_effects', i.health_effects,
    'bioaccumulation_potential', i.bioaccumulation_potential,
    'vulnerable_populations', i.vulnerable_populations,
    'intake_limit', i.intake_limit,
    'mindful_alternatives', i.mindful_alternatives,
    'mindfulness_prompt', i.mindfulness_prompt,
    'environmental_impact', i.environmental_impact,
    'fda_status', i.fda_status,
    'updated_at', i.updated_at,
    'category', (SELECT c.name FROM public.categories c WHERE c.id = i.category_id),
    'pictograms', (
      SELECT jsonb_agg(jsonb_build_object('name', p.name, 'image_url', p.image_url))
      FROM public.pictograms p
      JOIN public.ingredient_pictograms ip ON p.id = ip.pictogram_id
      WHERE ip.ingredient_id = i.id
    ),
    'references', (
      SELECT jsonb_agg(jsonb_build_object('title', r.title, 'url', r.url, 'author', r.author, 'publication_date', r.publication_date))
      FROM public.references r
      WHERE r.ingredient_id = i.id
    ),
    'recalls', (
      SELECT jsonb_agg(jsonb_build_object('recall_date', ra.recall_date, 'product_name', ra.product_name, 'reason', ra.reason, 'fda_url', ra.fda_url, 'severity', ra.severity))
      FROM public.recall_alerts ra
      WHERE ra.ingredient_id = i.id
      ORDER BY ra.recall_date DESC
      LIMIT 5
    )
  )
  INTO ingredient_details
  FROM public.ingredients i
  WHERE i.slug = ingredient_slug;

  RETURN ingredient_details;
END;
$$;

-- Function 2: Record a sticker scan event.
-- This function will be called from the frontend every time a QR code is scanned.
CREATE OR REPLACE FUNCTION public.record_sticker_scan(p_sticker_id TEXT, p_ingredient_slug TEXT)
RETURNS void
LANGUAGE plpgsql
AS $$
DECLARE
  v_ingredient_id UUID;
BEGIN
  -- Get the ingredient ID from the slug
  SELECT id INTO v_ingredient_id FROM public.ingredients WHERE slug = p_ingredient_slug;

  -- Insert the scan event into the analytics table
  IF v_ingredient_id IS NOT NULL THEN
    INSERT INTO public.sticker_analytics (sticker_id, ingredient_id, ip_address, user_agent)
    VALUES (p_sticker_id, v_ingredient_id, inet_client_addr(), http_user_agent());
  END IF;
END;
$$;

-- Function 3: Get a list of all ingredients for the main database page.
CREATE OR REPLACE FUNCTION public.get_all_ingredients_summary()
RETURNS TABLE(slug TEXT, name TEXT, category TEXT, signal_word TEXT)
LANGUAGE plpgsql
AS $$
BEGIN
  RETURN QUERY
  SELECT i.slug, i.name, c.name as category, i.signal_word
  FROM public.ingredients i
  JOIN public.categories c ON i.category_id = c.id
  ORDER BY i.name;
END;
$$;
