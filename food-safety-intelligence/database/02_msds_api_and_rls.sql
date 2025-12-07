-- Mindful Substance Data Series (MSDS) Database: RLS, Functions, and API Logic
-- Version 2.0
-- Author: Manus AI for 48AXIOM

-- PART 1: Row Level Security (RLS) Policies

-- Enable RLS for all tables
ALTER TABLE public.msds_series ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.msds_categories ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.msds_pictograms ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.msds_processing_methods ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.msds_ingredients ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.msds_ingredient_pictograms ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.msds_ingredient_processing ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.msds_references ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.msds_recall_alerts ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.msds_sticker_analytics ENABLE ROW LEVEL SECURITY;

-- Create read-only policies for public access
CREATE POLICY "Public read-only access" ON public.msds_series FOR SELECT USING (true);
CREATE POLICY "Public read-only access" ON public.msds_categories FOR SELECT USING (true);
CREATE POLICY "Public read-only access" ON public.msds_pictograms FOR SELECT USING (true);
CREATE POLICY "Public read-only access" ON public.msds_processing_methods FOR SELECT USING (true);
CREATE POLICY "Public read-only access" ON public.msds_ingredients FOR SELECT USING (true);
CREATE POLICY "Public read-only access" ON public.msds_ingredient_pictograms FOR SELECT USING (true);
CREATE POLICY "Public read-only access" ON public.msds_ingredient_processing FOR SELECT USING (true);
CREATE POLICY "Public read-only access" ON public.msds_references FOR SELECT USING (true);
CREATE POLICY "Public read-only access" ON public.msds_recall_alerts FOR SELECT USING (true);

-- Allow anonymous inserts for analytics
CREATE POLICY "Allow anonymous inserts for analytics" ON public.msds_sticker_analytics FOR INSERT WITH CHECK (true);

-- Create admin policies for full access
CREATE POLICY "Admin full access" ON public.msds_series FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Admin full access" ON public.msds_categories FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Admin full access" ON public.msds_pictograms FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Admin full access" ON public.msds_processing_methods FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Admin full access" ON public.msds_ingredients FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Admin full access" ON public.msds_ingredient_pictograms FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Admin full access" ON public.msds_ingredient_processing FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Admin full access" ON public.msds_references FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Admin full access" ON public.msds_recall_alerts FOR ALL USING (auth.role() = 'service_role');
CREATE POLICY "Admin full access" ON public.msds_sticker_analytics FOR ALL USING (auth.role() = 'service_role');

-- PART 2: PostgreSQL Functions (for RPC)

-- Function 1: Get all details for a single ingredient page.
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
    'total_score', i.total_score,
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
    'category', (SELECT c.name FROM public.msds_categories c WHERE c.id = i.category_id),
    'series', (SELECT s.name FROM public.msds_series s WHERE s.id = i.series_id),
    'pictograms', (
      SELECT jsonb_agg(jsonb_build_object('name', p.name, 'image_url', p.image_url))
      FROM public.msds_pictograms p
      JOIN public.msds_ingredient_pictograms ip ON p.id = ip.pictogram_id
      WHERE ip.ingredient_id = i.id
    ),
    'processing_methods', (
      SELECT jsonb_agg(jsonb_build_object('name', pm.name, 'description', pm.description, 'notes', ipm.notes))
      FROM public.msds_processing_methods pm
      JOIN public.msds_ingredient_processing ipm ON pm.id = ipm.processing_method_id
      WHERE ipm.ingredient_id = i.id
    ),
    'references', (
      SELECT jsonb_agg(jsonb_build_object('title', r.title, 'url', r.url, 'author', r.author, 'publication_date', r.publication_date))
      FROM public.msds_references r
      WHERE r.ingredient_id = i.id
    ),
    'recalls', (
      SELECT jsonb_agg(jsonb_build_object('recall_date', ra.recall_date, 'product_name', ra.product_name, 'reason', ra.reason, 'fda_url', ra.fda_url, 'severity', ra.severity))
      FROM public.msds_recall_alerts ra
      WHERE ra.ingredient_id = i.id
      ORDER BY ra.recall_date DESC
      LIMIT 5
    )
  )
  INTO ingredient_details
  FROM public.msds_ingredients i
  WHERE i.slug = ingredient_slug;

  RETURN ingredient_details;
END;
$$;

-- Function 2: Record a sticker scan event.
CREATE OR REPLACE FUNCTION public.record_sticker_scan(p_sticker_id TEXT, p_ingredient_slug TEXT)
RETURNS void
LANGUAGE plpgsql
AS $$
DECLARE
  v_ingredient_id UUID;
BEGIN
  SELECT id INTO v_ingredient_id FROM public.msds_ingredients WHERE slug = p_ingredient_slug;

  IF v_ingredient_id IS NOT NULL THEN
    INSERT INTO public.msds_sticker_analytics (sticker_id, ingredient_id, ip_address, user_agent)
    VALUES (p_sticker_id, v_ingredient_id, inet_client_addr(), http_user_agent());
  END IF;
END;
$$;

-- Function 3: Get a list of all ingredients for the main database page.
CREATE OR REPLACE FUNCTION public.get_all_ingredients_summary()
RETURNS TABLE(slug TEXT, name TEXT, category TEXT, series TEXT, total_score INTEGER, signal_word TEXT)
LANGUAGE plpgsql
AS $$
BEGIN
  RETURN QUERY
  SELECT i.slug, i.name, c.name as category, s.name as series, i.total_score, i.signal_word
  FROM public.msds_ingredients i
  JOIN public.msds_categories c ON i.category_id = c.id
  JOIN public.msds_series s ON i.series_id = s.id
  ORDER BY i.total_score DESC;
END;
$$;
