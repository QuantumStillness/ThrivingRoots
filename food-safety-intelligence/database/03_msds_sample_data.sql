-- Mindful Substance Data Series (MSDS) Database: Sample Data
-- Version 2.0
-- Author: Manus AI for 48AXIOM

-- 1. Populate Series
INSERT INTO public.msds_series (name, signal_word, score_min, score_max, description, color_hex)
VALUES
  (
'Mindful Avoid
', 
'DANGER
', 300, 400, 
'Highest health risk, high bioaccumulation, limited safe alternatives.
', 
'#e53e3e
'),
  (
'Mindful Caution
', 
'WARNING
', 200, 299, 
'Moderate health risk, context-dependent safety, alternatives available.
', 
'#dd6b20
'),
  (
'Mindful Choose
', 
'NOTICE
', 0, 199, 
'Low health risk, minimal contamination, safe for regular consumption.
', 
'#38a169
');

-- 2. Populate Categories
INSERT INTO public.msds_categories (name, description)
VALUES
  (
'Heavy Metal
', 
'Toxic metals that can bioaccumulate in the body.
'),
  (
'Food Additive
', 
'Substances added to food to preserve flavor or enhance taste and appearance.
'),
  (
'Process Contaminant
', 
'Chemicals formed in food during processing.
'),
  (
'Pathogen Risk
', 
'Foods with a higher risk of contamination with harmful bacteria or viruses.
'),
  (
'Endocrine Disruptor
', 
'Chemicals that can interfere with the endocrine (or hormone) systems.
');

-- 3. Populate Pictograms
INSERT INTO public.msds_pictograms (name, image_url, description)
VALUES
  (
'Bioaccumulation Hazard
', 
'/icons/bioaccumulation.svg
', 
'Substance can build up in the body over time.
'),
  (
'Health Hazard
', 
'/icons/health_hazard.svg
', 
'May cause or suspected of causing serious health effects.
'),
  (
'Acute Toxicity
', 
'/icons/acute_toxicity.svg
', 
'Can cause immediate harm if consumed in large quantities.
'),
  (
'Endocrine Disruptor
', 
'/icons/endocrine_disruptor.svg
', 
'May interfere with hormone systems.
');

-- 4. Populate Processing Methods
INSERT INTO public.msds_processing_methods (name, description)
VALUES
  (
'Refined
', 
'Processed to remove impurities, often stripping nutrients.
'),
  (
'Cold-Pressed
', 
'Oil extraction method that avoids high heat, preserving nutrients.
'),
  (
'Fermented
', 
'Food preservation process that creates beneficial probiotics.
');

-- 5. Populate Ingredients (with scores)
WITH series_ids AS (
  SELECT id, name FROM public.msds_series
), category_ids AS (
  SELECT id, name FROM public.msds_categories
)
INSERT INTO public.msds_ingredients (slug, name, category_id, series_id, signal_word, health_hazard_score, exposure_frequency_score, contamination_intensity_score, transparency_score, description, health_effects)
VALUES
  (
'lead-in-spices
', 
'Lead in Spices
', (SELECT id FROM category_ids WHERE name = 
'Heavy Metal
'), (SELECT id FROM series_ids WHERE name = 
'Mindful Avoid
'), 
'DANGER
', 95, 70, 85, 75, 
'Lead is a toxic heavy metal that can contaminate spices during processing.
', 
'Neurotoxin, developmental delays in children, kidney damage.
'),
  (
'mercury-in-tuna
', 
'Mercury in Tuna
', (SELECT id FROM category_ids WHERE name = 
'Heavy Metal
'), (SELECT id FROM series_ids WHERE name = 
'Mindful Caution
'), 
'WARNING
', 80, 60, 70, 50, 
'Mercury is a heavy metal that bioaccumulates in large predatory fish like tuna.
', 
'Neurotoxin, harmful to pregnant women and children.
');

-- 6. Link Ingredients to Pictograms
WITH ingredient_ids AS (
  SELECT id, slug FROM public.msds_ingredients
), pictogram_ids AS (
  SELECT id, name FROM public.msds_pictograms
)
INSERT INTO public.msds_ingredient_pictograms (ingredient_id, pictogram_id)
VALUES
  ((SELECT id FROM ingredient_ids WHERE slug = 
'lead-in-spices
'), (SELECT id FROM pictogram_ids WHERE name = 
'Health Hazard
')),
  ((SELECT id FROM ingredient_ids WHERE slug = 
'lead-in-spices
'), (SELECT id FROM pictogram_ids WHERE name = 
'Bioaccumulation Hazard
')),
  ((SELECT id FROM ingredient_ids WHERE slug = 
'mercury-in-tuna
'), (SELECT id FROM pictogram_ids WHERE name = 
'Health Hazard
')),
  ((SELECT id FROM ingredient_ids WHERE slug = 
'mercury-in-tuna
'), (SELECT id FROM pictogram_ids WHERE name = 
'Bioaccumulation Hazard
'));

-- 7. Add References
WITH ingredient_ids AS (
  SELECT id, slug FROM public.msds_ingredients
)
INSERT INTO public.msds_references (ingredient_id, title, url)
VALUES
  ((SELECT id FROM ingredient_ids WHERE slug = 
'lead-in-spices
'), 
'FDA: Lead in Food, Foodwares, and Dietary Supplements
', 
'https://www.fda.gov/food/metals-and-your-food/lead-food-foodwares-and-dietary-supplements
'),
  ((SELECT id FROM ingredient_ids WHERE slug = 
'mercury-in-tuna
'), 
'FDA: Advice about Eating Fish
', 
'https://www.fda.gov/food/consumers/advice-about-eating-fish
');
