# Food Safety Intelligence Module

**Part of the ThrivingRoots Platform**  
**Version:** 1.0.0  
**License:** Apache-2.0

---

## Overview

The **Food Safety Intelligence** module extends ThrivingRoots' environmental intelligence capabilities into the realm of food safety. Using the **Mindful Ingredient Safety Standard (MISS)** framework, this module provides workers and consumers with workplace-style safety information about food ingredients and contaminants.

### Mission

Empower workers to apply their safety training from the job site to their dinner table, using familiar OSHA-style hazard communication to make informed food choices.

---

## What's Included

### 1. MISS Next.js Web Application

A production-ready web app that displays food ingredient safety information in a format familiar to safety-trained workers.

**Location:** `miss-nextjs-app/`

**Features:**
- Dynamic ingredient pages with GHS-style hazard pictograms
- QR code-enabled sticker integration
- Scientific references and FDA data
- Mindful alternatives and consumption guidance
- Analytics tracking for community engagement

### 2. Database Schema

Complete PostgreSQL/Supabase schema for food safety data.

**Location:** `database/`

**Files:**
- `miss_database_schema.sql` - Core MISS tables
- `miss_api_and_rls.sql` - Row Level Security and API functions
- `miss_sample_data.sql` - 5 flagship ingredients with references
- `environmental_data_schema.sql` - Environmental data tables for cross-module integration

### 3. Sticker Designs

Production-ready sticker mockups and QR codes.

**Location:** `sticker-designs/`

**Includes:**
- 5 food safety warning stickers (lead, mercury, nitrites, PFAS, Listeria)
- 5 humor-based "Hazmat to Hazfood" stickers
- 5 mindfulness-themed workplace safety stickers
- QR codes for all 5 flagship ingredients

---

## Quick Start

### Prerequisites

- Supabase account
- Vercel account (or other Next.js hosting)
- Node.js 18+ installed locally (for development)

### Deployment Steps

1. **Set up database:**
   - Execute SQL files in Supabase SQL Editor (see `MISS_INTEGRATION_GUIDE.md`)

2. **Deploy web app:**
   - Connect ThrivingRoots repository to Vercel
   - Set root directory to `food-safety-intelligence/miss-nextjs-app`
   - Add environment variables (Supabase URL and anon key)

3. **Print stickers:**
   - Use designs in `sticker-designs/` directory
   - QR codes link to your deployed web app

**Full instructions:** See `../MISS_INTEGRATION_GUIDE.md`

---

## Architecture

### Database Tables

| Table | Purpose |
|-------|---------|
| `miss_categories` | Ingredient classifications (Heavy Metal, Additive, etc.) |
| `miss_pictograms` | GHS-style hazard symbols |
| `miss_ingredients` | Core ingredient safety data |
| `miss_ingredient_pictograms` | Links ingredients to pictograms |
| `miss_references` | Scientific citations |
| `miss_recall_alerts` | FDA recall data |
| `miss_sticker_analytics` | QR code scan tracking |

### API Endpoints (PostgreSQL Functions)

- `get_ingredient_details(slug)` - Returns complete ingredient data
- `record_sticker_scan(sticker_id, slug)` - Tracks QR code scans
- `get_recent_recalls(limit)` - Returns latest FDA recalls

---

## Cross-Module Integration

The MISS module shares a Supabase backend with the Environmental Intelligence module, enabling powerful cross-domain insights:

- **Link contaminated sites to affected food sources**
- **Track bioaccumulation from environment to food chain**
- **Unified analytics dashboard**
- **Shared authentication system**

Example: A Superfund site contaminating agricultural water supply can be linked to affected food ingredients, showing users the complete contamination pathway.

---

## Product Lines

### 1. Workplace Safety Stickers

**Target Market:** Construction workers, warehouse workers, laboratory technicians

**Features:**
- OSHA-style design aesthetic
- Mindfulness prompts in safety language
- Weatherproof for hard hats and equipment
- QR codes linking to MISS database

**Revenue Model:** $5 singles, $12 3-packs, $35 10-packs

### 2. Food Safety Warning Stickers

**Target Market:** Health-conscious consumers, parents, safety professionals

**Features:**
- GHS-style hazard pictograms
- Signal words (DANGER, WARNING, CAUTION)
- Specific hazard information (lead, mercury, etc.)
- QR codes linking to detailed MISS pages

**Revenue Model:** $7-12 per sticker, $25-35 for sets

### 3. HAZWOPER Mindfulness Guide (Future)

**Target Market:** HAZWOPER-certified workers

**Features:**
- Mental health tools for high-stress emergency response
- Practical mindfulness techniques for on-site operations
- Post-deployment protocols
- Peer-credible content from certified HAZWOPER professional

**Revenue Model:** $37-47 PDF guide

---

## Development Roadmap

### Phase 1: Launch (Weeks 1-4) ✅
- [x] Database schema created
- [x] Next.js app built
- [x] 5 flagship ingredients documented
- [x] QR codes generated
- [x] Sticker designs created

### Phase 2: Expansion (Months 2-3)
- [ ] Add 15 more ingredients
- [ ] Integrate FDA recall API
- [ ] Build environmental data pages
- [ ] Create unified homepage
- [ ] Implement search functionality

### Phase 3: Community (Months 4-6)
- [ ] User accounts and favorites
- [ ] Community contributions
- [ ] Gamification elements
- [ ] Mobile app (React Native)

---

## Contributing

This module is part of the 48AXIOM mindfulness platform and ThrivingRoots environmental justice initiative. Contributions should align with the mission of accessible, actionable safety information for working-class communities.

---

## License

Apache-2.0 - See LICENSE file in repository root.

---

## Support

For questions or issues:
- **Integration Guide:** `../MISS_INTEGRATION_GUIDE.md`
- **Deployment Help:** `../MISS_DEPLOYMENT_GUIDE.md`
- **Troubleshooting:** `../deployment_troubleshooting_guide.md`

---

**Built with love for workers, by a worker. From the job site to the dinner table.**
