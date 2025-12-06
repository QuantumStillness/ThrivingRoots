-- Mindful Ingredient Safety Standard (MISS) Database: Sample Data
-- Version 1.0
-- Author: Manus AI for 48AXIOM
-- Execute this file AFTER running miss_database_schema.sql and miss_api_and_rls.sql

-- PART 1: Categories
INSERT INTO public.categories (name, description) VALUES
('Heavy Metal', 'Bioaccumulative heavy metals like lead, mercury, and cadmium that persist in the body'),
('Additive', 'Artificial additives including colors, preservatives, and sweeteners'),
('Process Contaminant', 'Harmful chemicals formed during food processing or cooking'),
('Pathogen Risk', 'Foods with high contamination or recall frequency'),
('Endocrine Disruptor', 'Chemicals that interfere with hormonal systems'),
('Environmental Hazard', 'Ingredients with significant ecological impact');

-- PART 2: Pictograms
-- Note: Replace these placeholder URLs with your actual pictogram image URLs after uploading to Supabase Storage or CDN
INSERT INTO public.pictograms (name, image_url, description) VALUES
('Bioaccumulation Hazard', 'https://yourdomain.com/pictograms/bioaccumulation.png', 'Substances that build up in the body over time'),
('Health Hazard', 'https://yourdomain.com/pictograms/health-hazard.png', 'Long-term systemic health risks including carcinogenicity'),
('Acute Toxicity', 'https://yourdomain.com/pictograms/acute-toxicity.png', 'Immediate and severe health risks'),
('Irritant', 'https://yourdomain.com/pictograms/irritant.png', 'Short-term or less severe health effects'),
('Endocrine Disruptor', 'https://yourdomain.com/pictograms/endocrine.png', 'Chemicals that interfere with hormonal systems'),
('Process Contaminant', 'https://yourdomain.com/pictograms/process-contaminant.png', 'Harmful chemicals created during food processing'),
('Environmental Hazard', 'https://yourdomain.com/pictograms/environmental.png', 'Harm to the environment'),
('Recall Risk', 'https://yourdomain.com/pictograms/recall-risk.png', 'Ingredients frequently associated with food recalls');

-- PART 3: Ingredients (All 5 Flagship Products)

-- Ingredient 1: Lead in Spices
INSERT INTO public.ingredients (
  slug, name, common_names, category_id, signal_word,
  hazard_statements, precautionary_statements,
  description, common_sources, health_effects,
  bioaccumulation_potential, vulnerable_populations,
  intake_limit, mindful_alternatives, mindfulness_prompt,
  environmental_impact, fda_status
) VALUES (
  'lead-in-spices',
  'Lead',
  ARRAY['Pb', 'Lead(II)', 'Plumbum'],
  (SELECT id FROM public.categories WHERE name = 'Heavy Metal'),
  'DANGER',
  ARRAY[
    'H201: Bioaccumulates in bones and brain tissue over time',
    'H101: Neurotoxic, especially harmful to developing brains in children',
    'H301: May damage reproductive system and cause developmental delays'
  ],
  ARRAY[
    'P201: Choose products certified as "Lead-Free" or "Third-Party Tested"',
    'P102: Avoid imported spices from high-risk regions without testing',
    'P301: Pregnant women and children should avoid contaminated products',
    'P401: Check FDA recall alerts before purchasing spices'
  ],
  'Lead is a heavy metal that can contaminate spices during processing, storage, or through contaminated soil where spices are grown. It bioaccumulates in the body, particularly in bones and the brain, causing irreversible neurological damage. Even low-level chronic exposure can reduce IQ in children and impair cognitive function in adults.',
  ARRAY['Turmeric', 'Cinnamon', 'Chili powder', 'Paprika', 'Cumin', 'Ginger', 'Oregano'],
  'Lead exposure causes developmental delays in children, kidney damage, reproductive harm, and increased blood pressure. Even low-level chronic exposure can reduce IQ and impair cognitive function. Lead is particularly dangerous because it crosses the blood-brain barrier and the placental barrier, affecting fetal development.',
  'HIGH',
  ARRAY['Children under 6', 'Pregnant women', 'Nursing mothers', 'Individuals with kidney disease'],
  'No safe level of lead exposure exists. FDA action level: 2 ppm in spices (parts per million). The CDC recommends blood lead levels below 3.5 µg/dL for children.',
  ARRAY[
    'Organic spices from certified suppliers (e.g., Simply Organic, Frontier Co-op)',
    'Domestically sourced spices from tested farms',
    'Third-party tested brands with published test results',
    'Grow your own herbs when possible'
  ],
  'You wear PPE to avoid lead exposure at work. Are you checking your spice rack at home? Pause before adding spices to your meal. Do you know where they came from? Have they been tested for contaminants?',
  'Lead contamination often results from industrial pollution in growing regions, particularly in South Asia where environmental regulations are less stringent. Supporting organic and fair-trade spices helps reduce environmental contamination and protects farming communities.',
  'FDA monitors lead levels in spices and issues recalls when contamination is detected. Multiple recalls in 2024 for lead-contaminated cinnamon and turmeric products. FDA is working to establish stricter limits but current enforcement is reactive rather than preventive.'
);

-- Ingredient 2: Mercury in Tuna
INSERT INTO public.ingredients (
  slug, name, common_names, category_id, signal_word,
  hazard_statements, precautionary_statements,
  description, common_sources, health_effects,
  bioaccumulation_potential, vulnerable_populations,
  intake_limit, mindful_alternatives, mindfulness_prompt,
  environmental_impact, fda_status
) VALUES (
  'mercury-in-tuna',
  'Mercury (Methylmercury)',
  ARRAY['Hg', 'MeHg', 'Methylmercury'],
  (SELECT id FROM public.categories WHERE name = 'Heavy Metal'),
  'WARNING',
  ARRAY[
    'H201: Bioaccumulates in brain and nervous system tissue',
    'H101: Neurotoxic to developing fetuses and young children',
    'H301: May damage kidneys and cardiovascular system with chronic exposure'
  ],
  ARRAY[
    'P102: Limit consumption to 1-2 servings per week for adults',
    'P201: Choose smaller fish species with lower mercury levels',
    'P301: Pregnant women should avoid high-mercury fish entirely',
    'P401: Check EPA/FDA fish advisories for your region'
  ],
  'Methylmercury is a highly toxic form of mercury that accumulates in fish, particularly large predatory species like tuna. It bioaccumulates in the food chain, reaching dangerous levels in top predators. Mercury enters oceans through industrial pollution, particularly coal-fired power plants.',
  ARRAY['Albacore tuna', 'Yellowfin tuna', 'Bigeye tuna', 'Swordfish', 'King mackerel', 'Shark', 'Tilefish'],
  'Mercury exposure damages the developing brain and nervous system, particularly in fetuses and young children. In adults, it can cause neurological symptoms including tremors, memory problems, and mood changes. Chronic exposure is linked to kidney damage and cardiovascular effects.',
  'HIGH',
  ARRAY['Pregnant women', 'Nursing mothers', 'Children under 6', 'Women of childbearing age'],
  'FDA/EPA recommendation: 1 serving (4 oz) of albacore tuna per week for adults. Pregnant women should choose light tuna (skipjack) or avoid tuna entirely. Children should have no more than 1-2 servings per month.',
  ARRAY[
    'Light tuna (skipjack - lower mercury)',
    'Salmon (wild-caught preferred)',
    'Sardines',
    'Anchovies',
    'Trout',
    'Herring',
    'Mackerel (Atlantic, not King)'
  ],
  'You monitor air quality for mercury vapor at work. Are you monitoring mercury in your lunch? Before ordering tuna, pause and consider: Is this a large predatory fish? How often have I eaten high-mercury fish this week?',
  'Overfishing of tuna species threatens marine ecosystems and disrupts ocean food chains. Mercury pollution from coal-fired power plants contaminates oceans globally, creating an environmental justice issue for coastal communities dependent on fishing.',
  'FDA and EPA jointly issue fish consumption advisories. Methylmercury is not regulated as a food additive but is monitored in seafood. Current guidelines are based on protecting fetal brain development.'
);

-- Ingredient 3: Sodium Nitrite (Processed Meats)
INSERT INTO public.ingredients (
  slug, name, common_names, category_id, signal_word,
  hazard_statements, precautionary_statements,
  description, common_sources, health_effects,
  bioaccumulation_potential, vulnerable_populations,
  intake_limit, mindful_alternatives, mindfulness_prompt,
  environmental_impact, fda_status
) VALUES (
  'sodium-nitrite-processed-meats',
  'Sodium Nitrite',
  ARRAY['NaNO2', 'E250', 'Nitrite'],
  (SELECT id FROM public.categories WHERE name = 'Process Contaminant'),
  'CAUTION',
  ARRAY[
    'H501: Forms carcinogenic nitrosamines when heated or exposed to stomach acid',
    'H102: Linked to increased risk of colorectal cancer',
    'H301: May increase risk of stomach cancer with regular consumption'
  ],
  ARRAY[
    'P102: Limit consumption of processed meats to 1-2 servings per week',
    'P301: Avoid cooking processed meats at high temperatures (grilling, frying)',
    'P401: Choose nitrite-free or celery-powder-cured alternatives when possible',
    'P501: Pair with vitamin C-rich foods to inhibit nitrosamine formation'
  ],
  'Sodium nitrite is used as a preservative and color fixative in processed meats. When exposed to high heat or stomach acid, it forms nitrosamines, which are potent carcinogens. The WHO has classified processed meat as a Group 1 carcinogen (same category as tobacco).',
  ARRAY['Bacon', 'Hot dogs', 'Deli meats', 'Sausages', 'Ham', 'Pepperoni', 'Salami', 'Corned beef'],
  'Regular consumption of processed meats containing sodium nitrite is linked to increased risk of colorectal cancer, stomach cancer, and cardiovascular disease. The WHO estimates that eating 50g of processed meat daily increases colorectal cancer risk by 18%.',
  'LOW',
  ARRAY['Children', 'Individuals with family history of colorectal cancer', 'Pregnant women'],
  'WHO recommendation: Limit processed meat to less than 50g per day (about 2 slices of bacon or 1 hot dog). The American Institute for Cancer Research recommends avoiding processed meats entirely.',
  ARRAY[
    'Fresh, unprocessed meats',
    'Nitrite-free deli meats (check labels carefully)',
    'Plant-based alternatives (tempeh bacon, veggie sausages)',
    'Celery-powder-cured meats (naturally occurring nitrites, potentially safer)'
  ],
  'You avoid carcinogens at work. Are you checking your lunch meat? The same chemicals classified as Group 1 carcinogens in the lab are in your sandwich. Pause and consider: Is this serving your long-term health?',
  'Industrial meat processing contributes to environmental degradation through greenhouse gas emissions and water pollution. Choosing plant-based alternatives or sustainably raised meats reduces ecological impact.',
  'FDA allows sodium nitrite as a GRAS (Generally Recognized as Safe) additive at levels up to 200 ppm. However, the WHO has classified processed meats as carcinogenic, creating a regulatory disconnect.'
);

-- Ingredient 4: PFAS in Food Packaging
INSERT INTO public.ingredients (
  slug, name, common_names, category_id, signal_word,
  hazard_statements, precautionary_statements,
  description, common_sources, health_effects,
  bioaccumulation_potential, vulnerable_populations,
  intake_limit, mindful_alternatives, mindfulness_prompt,
  environmental_impact, fda_status
) VALUES (
  'pfas-food-packaging',
  'PFAS (Per- and Polyfluoroalkyl Substances)',
  ARRAY['Forever Chemicals', 'PFOA', 'PFOS', 'GenX', 'PFHxS'],
  (SELECT id FROM public.categories WHERE name = 'Environmental Hazard'),
  'WARNING',
  ARRAY[
    'H201: Bioaccumulates in blood and organs, persisting for years',
    'H301: Linked to cancer, thyroid disease, and immune suppression',
    'H401: Persists in environment for thousands of years, contaminating water and soil'
  ],
  ARRAY[
    'P201: Avoid microwave popcorn and fast food packaging',
    'P301: Use glass or stainless steel containers for food storage',
    'P401: Support legislation banning PFAS in food packaging',
    'P501: Filter drinking water with activated carbon or reverse osmosis'
  ],
  'PFAS are synthetic chemicals used to make food packaging grease-resistant and non-stick. They leach into food and bioaccumulate in the human body, persisting for years. They are called "forever chemicals" because they do not break down in the environment or the human body.',
  ARRAY[
    'Microwave popcorn bags',
    'Fast food wrappers (burgers, fries)',
    'Pizza boxes',
    'Non-stick cookware (Teflon)',
    'Water-resistant food containers',
    'Compostable food packaging (some brands)'
  ],
  'PFAS exposure is linked to cancer (kidney, testicular), thyroid disease, immune system suppression, liver damage, and developmental effects in children. They interfere with vaccine effectiveness and hormone regulation. PFAS are detected in the blood of 97% of Americans.',
  'HIGH',
  ARRAY['Pregnant women', 'Children', 'Individuals with compromised immune systems', 'Firefighters (occupational exposure)'],
  'No established safe level. EPA health advisory: 0.004 ppt (parts per trillion) in drinking water. Avoid food packaging sources entirely when possible.',
  ARRAY[
    'Glass containers for food storage',
    'Stainless steel containers',
    'Uncoated paper packaging',
    'Silicone baking mats instead of parchment paper',
    'Cast iron or stainless steel cookware'
  ],
  'The same chemicals banned in your workplace are in your microwave popcorn. You wear protective gear to avoid PFAS exposure on the job. Are you protecting yourself at home? Pause and ask: Is convenience worth the long-term health risk?',
  'PFAS contaminate soil and water near manufacturing sites, creating environmental justice issues for nearby communities. They persist in the environment for millennia, accumulating in drinking water supplies globally.',
  'FDA has phased out some PFAS in food packaging but many are still in use. No comprehensive ban exists in the US. The EPA has established health advisories for drinking water but food packaging remains largely unregulated.'
);

-- Ingredient 5: Listeria/E. coli in Leafy Greens
INSERT INTO public.ingredients (
  slug, name, common_names, category_id, signal_word,
  hazard_statements, precautionary_statements,
  description, common_sources, health_effects,
  bioaccumulation_potential, vulnerable_populations,
  intake_limit, mindful_alternatives, mindfulness_prompt,
  environmental_impact, fda_status
) VALUES (
  'listeria-ecoli-leafy-greens',
  'Listeria monocytogenes / E. coli O157:H7',
  ARRAY['Listeria', 'E. coli', 'Pathogenic bacteria'],
  (SELECT id FROM public.categories WHERE name = 'Pathogen Risk'),
  'CAUTION',
  ARRAY[
    'H601: Frequently associated with food recalls and outbreaks',
    'H101: Can cause severe illness or death in vulnerable populations',
    'H301: Contaminates leafy greens through irrigation water or processing'
  ],
  ARRAY[
    'P202: Rinse leafy greens thoroughly under running water before consumption',
    'P301: Avoid pre-washed salads if immunocompromised or pregnant',
    'P401: Check FDA recall alerts regularly at foodsafety.gov',
    'P501: Discard outer leaves and wash inner leaves separately'
  ],
  'Listeria and E. coli are pathogenic bacteria that frequently contaminate leafy greens through contaminated irrigation water, improper handling, or cross-contamination during processing. They are responsible for numerous food recalls and outbreaks each year, with hundreds of hospitalizations.',
  ARRAY[
    'Romaine lettuce',
    'Spinach',
    'Mixed salad greens',
    'Kale',
    'Arugula',
    'Raw sprouts',
    'Pre-cut salad mixes'
  ],
  'Listeria causes listeriosis, which can be fatal in pregnant women (causing miscarriage), newborns, elderly, and immunocompromised individuals. E. coli O157:H7 causes severe diarrhea, kidney failure (hemolytic uremic syndrome), and death. Both pathogens are responsible for hundreds of hospitalizations and dozens of deaths annually in the US.',
  'NONE',
  ARRAY['Pregnant women', 'Elderly (over 65)', 'Immunocompromised individuals', 'Young children (under 5)'],
  'No safe level of pathogenic bacteria. Proper washing and handling are critical. Vulnerable populations should avoid raw sprouts entirely and consider cooking leafy greens.',
  ARRAY[
    'Cooked greens (heat kills pathogens)',
    'Locally sourced greens from trusted farms with known practices',
    'Hydroponically grown greens (reduced contamination risk)',
    'Home-grown greens with controlled water sources'
  ],
  'The same contamination protocols used at hazmat sites apply to your salad. You decontaminate after exposure to hazardous materials. Are you decontaminating your produce? Pause and ask: Have I washed this thoroughly? Is it from a recalled batch?',
  'Industrial agriculture practices, including contaminated irrigation water and runoff from animal farms (CAFOs), contribute to pathogen contamination of leafy greens. Sustainable farming practices with proper water management reduce contamination risk.',
  'FDA monitors and issues recalls for contaminated produce. Frequent recalls highlight systemic issues in the produce supply chain. FDA has issued guidance on agricultural water standards but enforcement is limited.'
);

-- PART 4: Link Ingredients to Pictograms
INSERT INTO public.ingredient_pictograms (ingredient_id, pictogram_id) VALUES
-- Lead in Spices
((SELECT id FROM public.ingredients WHERE slug = 'lead-in-spices'), (SELECT id FROM public.pictograms WHERE name = 'Bioaccumulation Hazard')),
((SELECT id FROM public.ingredients WHERE slug = 'lead-in-spices'), (SELECT id FROM public.pictograms WHERE name = 'Health Hazard')),

-- Mercury in Tuna
((SELECT id FROM public.ingredients WHERE slug = 'mercury-in-tuna'), (SELECT id FROM public.pictograms WHERE name = 'Bioaccumulation Hazard')),
((SELECT id FROM public.ingredients WHERE slug = 'mercury-in-tuna'), (SELECT id FROM public.pictograms WHERE name = 'Health Hazard')),

-- Sodium Nitrite
((SELECT id FROM public.ingredients WHERE slug = 'sodium-nitrite-processed-meats'), (SELECT id FROM public.pictograms WHERE name = 'Health Hazard')),
((SELECT id FROM public.ingredients WHERE slug = 'sodium-nitrite-processed-meats'), (SELECT id FROM public.pictograms WHERE name = 'Process Contaminant')),

-- PFAS
((SELECT id FROM public.ingredients WHERE slug = 'pfas-food-packaging'), (SELECT id FROM public.pictograms WHERE name = 'Bioaccumulation Hazard')),
((SELECT id FROM public.ingredients WHERE slug = 'pfas-food-packaging'), (SELECT id FROM public.pictograms WHERE name = 'Environmental Hazard')),
((SELECT id FROM public.ingredients WHERE slug = 'pfas-food-packaging'), (SELECT id FROM public.pictograms WHERE name = 'Health Hazard')),

-- Listeria/E. coli
((SELECT id FROM public.ingredients WHERE slug = 'listeria-ecoli-leafy-greens'), (SELECT id FROM public.pictograms WHERE name = 'Recall Risk'));

-- PART 5: Add Scientific References
-- Lead in Spices References
INSERT INTO public.references (ingredient_id, title, url, author, publication_date) VALUES
((SELECT id FROM public.ingredients WHERE slug = 'lead-in-spices'), 
 'Lead in Spices, Herbal Remedies, and Ceremonial Powders Sampled from Home Investigations', 
 'https://www.ncbi.nlm.nih.gov/pmc/articles/PMC5933381/', 
 'Forsyth et al.', 
 '2018-04-01'),
((SELECT id FROM public.ingredients WHERE slug = 'lead-in-spices'), 
 'FDA Investigation Summary: Certain Ground Cinnamon Products', 
 'https://www.fda.gov/food/outbreaks-foodborne-illness/fda-investigation-summary-certain-ground-cinnamon-products', 
 'FDA', 
 '2024-03-15'),
((SELECT id FROM public.ingredients WHERE slug = 'lead-in-spices'), 
 'Lead Exposure in Children', 
 'https://www.cdc.gov/lead/prevention/index.html', 
 'CDC', 
 '2023-06-01');

-- Mercury in Tuna References
INSERT INTO public.references (ingredient_id, title, url, author, publication_date) VALUES
((SELECT id FROM public.ingredients WHERE slug = 'mercury-in-tuna'), 
 'Mercury Levels in Commercial Fish and Shellfish (1990-2012)', 
 'https://www.fda.gov/food/metals-and-your-food/mercury-levels-commercial-fish-and-shellfish-1990-2012', 
 'FDA', 
 '2022-01-01'),
((SELECT id FROM public.ingredients WHERE slug = 'mercury-in-tuna'), 
 'Advice about Eating Fish: For Those Who Might Become or Are Pregnant or Breastfeeding and Children Ages 1-11 Years', 
 'https://www.fda.gov/food/consumers/advice-about-eating-fish', 
 'FDA/EPA', 
 '2023-10-01'),
((SELECT id FROM public.ingredients WHERE slug = 'mercury-in-tuna'), 
 'Methylmercury Exposure and Health Effects', 
 'https://www.ncbi.nlm.nih.gov/pmc/articles/PMC3988285/', 
 'Clarkson & Magos', 
 '2006-09-01');

-- Sodium Nitrite References
INSERT INTO public.references (ingredient_id, title, url, author, publication_date) VALUES
((SELECT id FROM public.ingredients WHERE slug = 'sodium-nitrite-processed-meats'), 
 'IARC Monographs evaluate consumption of red meat and processed meat', 
 'https://www.iarc.who.int/wp-content/uploads/2018/07/pr240_E.pdf', 
 'WHO International Agency for Research on Cancer', 
 '2015-10-26'),
((SELECT id FROM public.ingredients WHERE slug = 'sodium-nitrite-processed-meats'), 
 'Red and Processed Meat Consumption and Risk of Colorectal Cancer', 
 'https://www.ncbi.nlm.nih.gov/pmc/articles/PMC4698595/', 
 'Bouvard et al.', 
 '2015-12-01'),
((SELECT id FROM public.ingredients WHERE slug = 'sodium-nitrite-processed-meats'), 
 'Nitrate and Nitrite in Food and Water', 
 'https://www.ncbi.nlm.nih.gov/books/NBK326534/', 
 'National Research Council', 
 '1981-01-01');

-- PFAS References
INSERT INTO public.references (ingredient_id, title, url, author, publication_date) VALUES
((SELECT id FROM public.ingredients WHERE slug = 'pfas-food-packaging'), 
 'Per- and Polyfluoroalkyl Substances (PFAS)', 
 'https://www.epa.gov/pfas', 
 'EPA', 
 '2024-01-01'),
((SELECT id FROM public.ingredients WHERE slug = 'pfas-food-packaging'), 
 'PFAS in Food Packaging', 
 'https://www.fda.gov/food/process-contaminants-food/authorized-uses-pfas-food-contact-applications', 
 'FDA', 
 '2023-07-01'),
((SELECT id FROM public.ingredients WHERE slug = 'pfas-food-packaging'), 
 'Health Effects of PFAS Exposure', 
 'https://www.ncbi.nlm.nih.gov/pmc/articles/PMC8157593/', 
 'Fenton et al.', 
 '2021-05-01');

-- Listeria/E. coli References
INSERT INTO public.references (ingredient_id, title, url, author, publication_date) VALUES
((SELECT id FROM public.ingredients WHERE slug = 'listeria-ecoli-leafy-greens'), 
 'Listeria (Listeriosis)', 
 'https://www.cdc.gov/listeria/index.html', 
 'CDC', 
 '2024-01-01'),
((SELECT id FROM public.ingredients WHERE slug = 'listeria-ecoli-leafy-greens'), 
 'E. coli (Escherichia coli)', 
 'https://www.cdc.gov/ecoli/index.html', 
 'CDC', 
 '2024-01-01'),
((SELECT id FROM public.ingredients WHERE slug = 'listeria-ecoli-leafy-greens'), 
 'Outbreak of E. coli Infections Linked to Leafy Greens', 
 'https://www.cdc.gov/ecoli/2022/o157h07-11-22/index.html', 
 'CDC', 
 '2022-11-01');

-- PART 6: Add Sample Recall Alerts (Optional - can be updated regularly)
INSERT INTO public.recall_alerts (ingredient_id, recall_date, product_name, reason, fda_url, severity) VALUES
((SELECT id FROM public.ingredients WHERE slug = 'lead-in-spices'), 
 '2024-03-15', 
 'Brand X Ground Cinnamon', 
 'Lead contamination exceeding FDA action level', 
 'https://www.fda.gov/safety/recalls-market-withdrawals-safety-alerts', 
 'Class I'),
((SELECT id FROM public.ingredients WHERE slug = 'listeria-ecoli-leafy-greens'), 
 '2024-11-20', 
 'Fresh Express Organic Spinach', 
 'Potential Listeria monocytogenes contamination', 
 'https://www.fda.gov/safety/recalls-market-withdrawals-safety-alerts', 
 'Class I'),
((SELECT id FROM public.ingredients WHERE slug = 'listeria-ecoli-leafy-greens'), 
 '2024-10-05', 
 'Dole Chopped Salad Kits', 
 'Potential E. coli O157:H7 contamination', 
 'https://www.fda.gov/safety/recalls-market-withdrawals-safety-alerts', 
 'Class I');

-- Verification Queries
-- Run these to verify data was inserted correctly:

-- Count ingredients by category
SELECT c.name, COUNT(i.id) as ingredient_count
FROM categories c
LEFT JOIN ingredients i ON c.id = i.category_id
GROUP BY c.name;

-- List all ingredients with their pictograms
SELECT i.name, ARRAY_AGG(p.name) as pictograms
FROM ingredients i
LEFT JOIN ingredient_pictograms ip ON i.id = ip.ingredient_id
LEFT JOIN pictograms p ON ip.pictogram_id = p.id
GROUP BY i.name;

-- Count references per ingredient
SELECT i.name, COUNT(r.id) as reference_count
FROM ingredients i
LEFT JOIN references r ON i.id = r.ingredient_id
GROUP BY i.name;
