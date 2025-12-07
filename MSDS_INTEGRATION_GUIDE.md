# MSDS Integration Guide for ThrivingRoots

**Version:** 1.0  
**Author:** Manus AI for 48AXIOM  
**Date:** December 6, 2025

---

## Overview

This guide explains how to integrate the **Mindful Substance Data Series (MSDS)** food safety module into the ThrivingRoots environmental intelligence platform. The integration creates a unified system where environmental contamination data and food safety information coexist in a shared Supabase backend.

---

## Architecture Overview

### Unified Platform Vision

**ThrivingRoots** becomes a comprehensive safety intelligence platform serving two interconnected domains:

1. **Environmental Intelligence** - Superfund sites, contamination tracking, remediation data
2. **Food Safety Intelligence** - Ingredient hazards, recalls, mindful eating guidance

### Shared Infrastructure

| Component | Technology | Purpose |
|-----------|------------|---------|
| Database | Supabase (PostgreSQL) | Unified data storage |
| Frontend | Next.js | Web application serving both modules |
| Authentication | Supabase Auth | Single user system |
| Analytics | Shared analytics tables | Unified engagement tracking |
| Hosting | Vercel | Serverless deployment |

---

## Database Schema Integration

### Namespace Strategy

To avoid table name conflicts, we use prefixed naming:

- **Environmental tables:** `env_*` prefix
- **MSDS tables:** `miss_*` prefix
- **Shared tables:** No prefix (e.g., `users`, `shared_analytics`)

### Complete Schema Structure

```
Supabase Database: zewfpzlmacefmgwsifda
├── Environmental Module (env_*)
│   ├── env_sites                    (Superfund sites)
│   ├── env_contaminants             (Environmental pollutants)
│   ├── env_site_contaminants        (Junction table)
│   ├── env_scraper_jobs             (Web scraping configuration)
│   └── env_data_log                 (Scraper audit trail)
│
├── MSDS Module (miss_*)
│   ├── miss_categories              (Ingredient categories)
│   ├── miss_pictograms              (GHS-style hazard symbols)
│   ├── miss_ingredients             (Core ingredient data)
│   ├── miss_ingredient_pictograms   (Junction table)
│   ├── miss_references              (Scientific citations)
│   ├── miss_recall_alerts           (FDA recall data)
│   └── miss_sticker_analytics       (QR code scan tracking)
│
└── Shared Tables
    ├── users                         (Supabase Auth)
    └── shared_analytics              (Cross-module engagement)
```

---

## Step-by-Step Integration

### Phase 1: Database Setup (30 minutes)

#### 1.1 Access Your Supabase Project

1. Go to [https://supabase.com/dashboard](https://supabase.com/dashboard)
2. Select your project: `zewfpzlmacefmgwsifda`
3. Navigate to **SQL Editor** in the left sidebar

#### 1.2 Execute Schema Files in Order

Execute these SQL files in the Supabase SQL Editor:

**Order matters!** Execute in this sequence:

1. **Environmental Schema** (`environmental_data_schema.sql`)
   - Creates `env_*` tables for Superfund site data
   - Sets up web scraping infrastructure

2. **MSDS Schema** (`miss_database_schema.sql`)
   - Creates `miss_*` tables for food ingredient data
   - Sets up GHS-style hazard framework

3. **MSDS API & RLS** (`miss_api_and_rls.sql`)
   - Creates Row Level Security policies
   - Creates PostgreSQL functions for API endpoints

4. **MSDS Sample Data** (`miss_sample_data.sql`)
   - Populates 5 flagship ingredients
   - Adds pictograms and references

#### 1.3 Verify Installation

Run this query to verify all tables exist:

```sql
SELECT table_name 
FROM information_schema.tables 
WHERE table_schema = 'public' 
  AND (table_name LIKE 'env_%' OR table_name LIKE 'miss_%')
ORDER BY table_name;
```

You should see 12 tables total (5 env + 7 miss).

---

### Phase 2: Repository Structure (15 minutes)

#### 2.1 Create MSDS Module Directory

In the ThrivingRoots repository root, create:

```bash
mkdir -p food-safety-intelligence/miss-nextjs-app
mkdir -p food-safety-intelligence/database
mkdir -p food-safety-intelligence/sticker-designs
```

#### 2.2 Copy MSDS Files

Copy the following files into the new directory structure:

```
food-safety-intelligence/
├── database/
│   ├── miss_database_schema.sql
│   ├── miss_api_and_rls.sql
│   ├── miss_sample_data.sql
│   └── environmental_data_schema.sql
├── miss-nextjs-app/
│   ├── app/
│   │   ├── miss/[slug]/page.js
│   │   ├── miss/page.js
│   │   ├── layout.js
│   │   └── globals.css
│   ├── lib/
│   │   └── supabase.js
│   ├── package.json
│   └── tailwind.config.js
└── sticker-designs/
    ├── sticker-01-lead.png
    ├── sticker-02-mercury.png
    ├── sticker-03-nitrite.png
    ├── sticker-04-pfas.png
    └── sticker-05-listeria.png
```

#### 2.3 Update Supabase Client Configuration

Edit `food-safety-intelligence/miss-nextjs-app/lib/supabase.js`:

```javascript
import { createClient } from '@supabase/supabase-js'

const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL || 'https://zewfpzlmacefmgwsifda.supabase.co'
const supabaseAnonKey = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY || 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inpld2ZwemxtYWNlZm1nd3NpZmRhIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjUwMzU5MTIsImV4cCI6MjA4MDYxMTkxMn0.AmpSAHw49e8Isukz7yXoDVWwb44l-2qujHmFUXhwbck'

export const supabase = createClient(supabaseUrl, supabaseAnonKey)
```

---

### Phase 3: Deployment to Vercel (30 minutes)

#### 3.1 Prepare for Deployment

1. Commit all changes to the ThrivingRoots repository:

```bash
cd /path/to/ThrivingRoots
git add .
git commit -m "Add MSDS food safety module"
git push origin main
```

#### 3.2 Deploy to Vercel

1. Go to [https://vercel.com/dashboard](https://vercel.com/dashboard)
2. Click **"Add New Project"**
3. Import your **ThrivingRoots** repository
4. Configure the project:
   - **Framework Preset:** Next.js
   - **Root Directory:** `food-safety-intelligence/miss-nextjs-app`
   - **Build Command:** `npm run build`
   - **Output Directory:** `.next`

#### 3.3 Set Environment Variables

In Vercel project settings, add:

| Variable Name | Value |
|---------------|-------|
| `NEXT_PUBLIC_SUPABASE_URL` | `https://zewfpzlmacefmgwsifda.supabase.co` |
| `NEXT_PUBLIC_SUPABASE_ANON_KEY` | (Your anon key from Supabase dashboard) |

#### 3.4 Deploy

Click **"Deploy"** and wait for the build to complete (~2-3 minutes).

---

### Phase 4: Cross-Module Integration (Advanced)

#### 4.1 Link Environmental Sites to Food Hazards

Create a new junction table to link contaminated sites to affected food sources:

```sql
CREATE TABLE public.env_site_food_impacts (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  env_site_id UUID REFERENCES public.env_sites(id) ON DELETE CASCADE,
  miss_ingredient_id UUID REFERENCES public.miss_ingredients(id) ON DELETE CASCADE,
  impact_type TEXT, -- e.g., 'water_supply', 'agricultural_land', 'fishing_area'
  confidence_level TEXT CHECK (confidence_level IN ('HIGH', 'MODERATE', 'LOW')),
  evidence_source TEXT,
  created_at TIMESTAMPTZ DEFAULT NOW()
);
```

#### 4.2 Example Query: Sites Affecting Food

```sql
-- Find all Superfund sites that may affect food safety
SELECT 
  es.name AS site_name,
  es.epa_id,
  mi.name AS affected_ingredient,
  esfi.impact_type,
  esfi.confidence_level
FROM public.env_sites es
JOIN public.env_site_food_impacts esfi ON es.id = esfi.env_site_id
JOIN public.miss_ingredients mi ON esfi.miss_ingredient_id = mi.id
WHERE esfi.confidence_level IN ('HIGH', 'MODERATE')
ORDER BY es.name;
```

#### 4.3 Update MSDS Ingredient Pages

Modify `miss-nextjs-app/app/miss/[slug]/page.js` to display nearby environmental hazards:

```javascript
// Fetch nearby environmental sites
const { data: nearbySites } = await supabase
  .from('env_site_food_impacts')
  .select(`
    impact_type,
    confidence_level,
    env_sites (name, epa_id, latitude, longitude)
  `)
  .eq('miss_ingredient_id', ingredient.id)
```

---

## QR Code Integration

### Sticker-to-Database Flow

1. **Worker scans QR code** on hard hat sticker
2. **QR code URL format:** `https://yourdomain.com/miss/lead-in-spices?sticker=sticker-01-lead`
3. **Analytics tracking:** Page load triggers `record_sticker_scan()` function
4. **Data display:** Ingredient page shows hazard information from `miss_ingredients` table

### Generating QR Codes

The QR codes have already been generated and are in the `qr-codes/` directory. Each QR code links to:

- `sticker-01-lead.png` → `https://yourdomain.com/miss/lead-in-spices?sticker=sticker-01-lead`
- `sticker-02-mercury.png` → `https://yourdomain.com/miss/mercury-in-tuna?sticker=sticker-02-mercury`
- `sticker-03-nitrite.png` → `https://yourdomain.com/miss/sodium-nitrite?sticker=sticker-03-nitrite`
- `sticker-04-pfas.png` → `https://yourdomain.com/miss/pfas-in-packaging?sticker=sticker-04-pfas`
- `sticker-05-listeria.png` → `https://yourdomain.com/miss/listeria-in-leafy-greens?sticker=sticker-05-listeria`

---

## Testing the Integration

### Test Checklist

- [ ] All database tables created successfully
- [ ] Sample data loaded (5 ingredients, 9 pictograms)
- [ ] Next.js app builds without errors
- [ ] Vercel deployment successful
- [ ] Environment variables set correctly
- [ ] Ingredient pages load and display data
- [ ] QR codes scan and navigate to correct pages
- [ ] Analytics tracking records scans

### Test Queries

**Test 1: Verify ingredient data**
```sql
SELECT name, signal_word, category_id FROM public.miss_ingredients;
```

**Test 2: Verify pictograms are linked**
```sql
SELECT 
  i.name AS ingredient,
  p.name AS pictogram
FROM public.miss_ingredients i
JOIN public.miss_ingredient_pictograms ip ON i.id = ip.ingredient_id
JOIN public.miss_pictograms p ON ip.pictogram_id = p.pictogram_id;
```

**Test 3: Check analytics**
```sql
SELECT 
  sticker_id,
  COUNT(*) AS scan_count
FROM public.miss_sticker_analytics
GROUP BY sticker_id
ORDER BY scan_count DESC;
```

---

## Maintenance & Updates

### Adding New Ingredients

1. Insert into `miss_ingredients` table
2. Link to appropriate `miss_pictograms` via `miss_ingredient_pictograms`
3. Add scientific references to `miss_references`
4. Create new sticker design
5. Generate QR code linking to new ingredient page

### Updating Environmental Data

The web scraping engine will automatically update `env_sites` and `env_contaminants` tables based on the configured `env_scraper_jobs`.

To manually trigger a scrape:

```bash
# Using WordPress WP-CLI (if still using WordPress)
wp env-scraper run EPA_SEMS

# Or via Supabase Edge Functions (future implementation)
```

---

## Troubleshooting

### Issue: Tables not appearing in Supabase

**Solution:** Ensure you executed the SQL files in the correct order and checked for error messages in the SQL Editor.

### Issue: Next.js app can't connect to Supabase

**Solution:** Verify environment variables are set correctly in Vercel. The anon key must match exactly.

### Issue: QR codes not tracking analytics

**Solution:** Check that the `record_sticker_scan()` function was created successfully. Test it manually:

```sql
SELECT record_sticker_scan('sticker-01-lead', 'lead-in-spices');
```

---

## Next Steps

1. **Expand ingredient library:** Add 10-20 more common food hazards
2. **Build environmental data pages:** Create Next.js pages for `env_sites`
3. **Create cross-module landing page:** Unified homepage showing both modules
4. **Implement search:** Allow users to search across both environmental and food data
5. **Add user accounts:** Enable users to save favorite sites/ingredients

---

## Support

For questions or issues with this integration, refer to:

- **MSDS Deployment Guide:** `MSDS_DEPLOYMENT_GUIDE.md`
- **Learning Resources:** `learning_resources_guide.md`
- **Troubleshooting:** `deployment_troubleshooting_guide.md`

---

**Congratulations! You've successfully integrated the MSDS food safety module into ThrivingRoots.**
