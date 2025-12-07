# MSDS Rebranding Summary

**Date:** December 6, 2025  
**Status:** Complete - Ready for Deployment

---

## What Changed

### Name Change
- **Old:** MISS (Mindful Ingredient Safety Standard)
- **New:** MSDS (Mindful Substance Data Series)

### Why MSDS?
1. **Familiar acronym** - Workers already know MSDS (Material Safety Data Sheets)
2. **"Series" enables indexing** - Like EWG's Dirty Dozen/Clean 15
3. **"Substance" is broader** - Covers ingredients, additives, contaminants, and processes
4. **Brand consistency** - Aligns with 48AXIOM naming (elv48, integr48, radi48)

---

## Files Updated

### Database Files (food-safety-intelligence/database/)
- ✅ `01_msds_schema.sql` - Complete MSDS database schema
- ✅ `02_msds_api_and_rls.sql` - RLS policies and API functions
- ✅ `03_msds_sample_data.sql` - Sample data for 2 ingredients

### Next.js Application (food-safety-intelligence/msds-nextjs-app/)
- ✅ App directory renamed: `app/miss/` → `app/msds/`
- ✅ Routes updated: `/miss/[slug]` → `/msds/[slug]`
- ✅ Homepage redirect created: `/` → `/msds`
- ✅ All component text updated to "MSDS"

### Documentation
- ✅ `README.md` - Module overview updated
- ✅ `MSDS_INTEGRATION_GUIDE.md` - Integration guide rebranded
- ✅ `MSDS_REBRAND_SUMMARY.md` - This file

---

## New Features Added

### 1. Series Indexing System
Inspired by EWG's Dirty Dozen/Clean 15, the MSDS now includes:

- **Mindful Avoid Series** - High-risk ingredients (score 7-10)
- **Mindful Choose Series** - Lower-risk alternatives (score 1-3)
- **Scoring system** based on 4 factors:
  - Health hazard severity
  - Exposure frequency
  - Contamination intensity
  - Transparency/labeling

### 2. Processing Methods Table
New table: `msds_processing_methods`

Tracks industrial processes that affect food safety:
- Refining (table salt vs sea salt)
- Hydrogenation (trans fats)
- Chemical treatment (pesticides, preservatives)
- Packaging (PFAS contamination)

### 3. Enhanced Ingredient Schema
New columns in `msds_ingredients`:
- `health_hazard_score` (1-10)
- `exposure_frequency_score` (1-10)
- `contamination_intensity_score` (1-10)
- `transparency_score` (1-10)
- `total_score` (calculated, 4-40)
- `series_assignment` ('MINDFUL_AVOID', 'MINDFUL_CHOOSE', 'MODERATE_CAUTION')

---

## Database Migration Required

### Current State
Your Supabase database currently has `miss_*` tables with the old schema.

### Migration Steps

**Option A: Fresh Install (Recommended for Development)**
1. Drop all `miss_*` tables
2. Execute `01_msds_schema.sql`
3. Execute `02_msds_api_and_rls.sql`
4. Execute `03_msds_sample_data.sql`

**Option B: Rename and Migrate (Preserves Data)**
1. Rename tables: `ALTER TABLE miss_categories RENAME TO msds_categories;`
2. Add new columns: `ALTER TABLE msds_ingredients ADD COLUMN health_hazard_score INTEGER;`
3. Create new tables: `msds_series`, `msds_processing_methods`
4. Migrate data

---

## Deployment Checklist

### Step 1: Update Database (Supabase)
- [ ] Navigate to Supabase SQL Editor
- [ ] Execute `01_msds_schema.sql` (creates tables)
- [ ] Execute `02_msds_api_and_rls.sql` (adds RLS and functions)
- [ ] Execute `03_msds_sample_data.sql` (loads sample data)
- [ ] Verify: Run `SELECT * FROM msds_ingredients;`

### Step 2: Update Next.js App (Vercel)
- [ ] Commit changes to GitHub: `git push origin main`
- [ ] Vercel will auto-deploy (or manually trigger)
- [ ] Update Root Directory in Vercel: `food-safety-intelligence/msds-nextjs-app`
- [ ] Verify environment variables are still set
- [ ] Test deployment: Visit `https://thrivingrootsdb.vercel.app/msds`

### Step 3: Update QR Codes (Optional)
- [ ] Regenerate QR codes with new `/msds/` URLs
- [ ] Update sticker designs if needed
- [ ] Reprint stickers with new branding

---

## URL Changes

### Old URLs (MISS)
- Homepage: `https://thrivingrootsdb.vercel.app/miss`
- Lead page: `https://thrivingrootsdb.vercel.app/miss/lead-in-spices`
- Mercury page: `https://thrivingrootsdb.vercel.app/miss/mercury-in-tuna`

### New URLs (MSDS)
- Homepage: `https://thrivingrootsdb.vercel.app/msds`
- Lead page: `https://thrivingrootsdb.vercel.app/msds/lead-in-spices`
- Mercury page: `https://thrivingrootsdb.vercel.app/msds/mercury-in-tuna`
- Root redirect: `https://thrivingrootsdb.vercel.app/` → `/msds`

---

## Domain Configuration (elv48.me)

### Option 1: Subdirectory (elv48.me/thriving)
Requires reverse proxy or rewrite rules on your main domain.

**Not recommended** - Complex setup, requires server control.

### Option 2: Subdomain (thriving.elv48.me)
Simple DNS configuration in Vercel.

**Recommended** - Clean, easy to set up.

#### Steps to Set Up Subdomain:
1. Go to Vercel project settings → Domains
2. Add domain: `thriving.elv48.me`
3. Vercel provides DNS records (CNAME)
4. Add CNAME record to your elv48.me DNS:
   - Type: `CNAME`
   - Name: `thriving`
   - Value: `cname.vercel-dns.com`
5. Wait for DNS propagation (5-60 minutes)
6. Visit `https://thriving.elv48.me/msds`

### Option 3: Keep Vercel URL
Simplest option - no DNS configuration needed.

**Good for testing** - Can always add custom domain later.

---

## Next Steps

### Immediate (Week 1)
1. Execute database migration
2. Deploy updated Next.js app
3. Test all pages and QR codes
4. Update any printed materials

### Short-term (Weeks 2-4)
1. Add 3 more ingredients (nitrites, PFAS, Listeria)
2. Populate processing methods table
3. Create "Mindful Avoid" and "Mindful Choose" index pages
4. Set up custom domain (thriving.elv48.me)

### Long-term (Months 2-3)
1. Add 10-15 more ingredients
2. Build series comparison pages (like EWG's lists)
3. Integrate with environmental data
4. Add search functionality
5. Create mobile app

---

## Support Files

All files are in the ThrivingRoots repository:

- **Database:** `food-safety-intelligence/database/`
- **Next.js App:** `food-safety-intelligence/msds-nextjs-app/`
- **Documentation:** Root directory (`MSDS_INTEGRATION_GUIDE.md`, etc.)
- **Learning Resources:** `learning_resources_guide.md`
- **Troubleshooting:** `deployment_troubleshooting_guide.md`

---

## Questions?

If you encounter issues during deployment:

1. Check the deployment troubleshooting guide
2. Verify all environment variables are set
3. Check Vercel build logs for errors
4. Test database queries in Supabase SQL Editor

**The MSDS system is ready to deploy. All files are prepared and documented.**

