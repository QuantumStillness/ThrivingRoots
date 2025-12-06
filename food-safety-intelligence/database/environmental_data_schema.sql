-- Environmental Data Schema for Supabase
-- Version 1.0
-- Author: Manus AI for 48AXIOM

-- This schema is adapted from the ThrivingRoots WordPress plugin for use with Supabase/PostgreSQL.

-- 1. Environmental Sites Table
-- Stores information about Superfund sites and other contaminated locations.
CREATE TABLE public.env_sites (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  name TEXT NOT NULL,
  description TEXT,
  epa_id TEXT UNIQUE,
  npl_status TEXT,
  latitude DECIMAL(9, 6),
  longitude DECIMAL(9, 6),
  lead_agency TEXT,
  site_status TEXT,
  remediation_technology TEXT[],
  projected_completion_date DATE,
  created_at TIMESTAMPTZ DEFAULT NOW(),
  updated_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE INDEX idx_env_sites_epa_id ON public.env_sites(epa_id);

-- 2. Contaminants Table
-- Stores information about environmental contaminants.
CREATE TABLE public.env_contaminants (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  name TEXT NOT NULL UNIQUE,
  description TEXT,
  health_effects TEXT,
  epa_regulated_limit TEXT,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 3. Site-Contaminant Junction Table
-- Many-to-many relationship between sites and contaminants.
CREATE TABLE public.env_site_contaminants (
  site_id UUID NOT NULL REFERENCES public.env_sites(id) ON DELETE CASCADE,
  contaminant_id UUID NOT NULL REFERENCES public.env_contaminants(id) ON DELETE CASCADE,
  PRIMARY KEY (site_id, contaminant_id)
);

-- 4. Scraper Jobs Table
-- Configuration for web scraping jobs.
CREATE TABLE public.env_scraper_jobs (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  source_name TEXT NOT NULL UNIQUE,
  source_type TEXT CHECK (source_type IN (
'html
', 
'json
', 
'xml
', 
'csv
')),
  base_url TEXT NOT NULL,
  last_run TIMESTAMPTZ,
  next_run TIMESTAMPTZ,
  is_active BOOLEAN DEFAULT TRUE,
  run_frequency TEXT DEFAULT 'daily',
  config JSONB,
  user_agent TEXT,
  rate_limit_delay INT DEFAULT 2,
  max_retries INT DEFAULT 3,
  timeout INT DEFAULT 30,
  created_at TIMESTAMPTZ DEFAULT NOW(),
  updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 5. Data Log Table
-- Audit trail for scraper operations.
CREATE TABLE public.env_data_log (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  scraper_job_id UUID REFERENCES public.env_scraper_jobs(id),
  source_url TEXT NOT NULL,
  data_type TEXT NOT NULL,
  fetch_timestamp TIMESTAMPTZ NOT NULL,
  status TEXT CHECK (status IN (
'success
', 
'error
', 
'partial
', 
'skipped
')),
  records_processed INT DEFAULT 0,
  records_created INT DEFAULT 0,
  records_updated INT DEFAULT 0,
  error_message TEXT,
  source_hash TEXT,
  response_code INT,
  execution_time FLOAT,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Function to automatically update timestamps
CREATE OR REPLACE FUNCTION public.handle_updated_at() 
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Triggers for env_sites and env_scraper_jobs
CREATE TRIGGER on_env_sites_update
  BEFORE UPDATE ON public.env_sites
  FOR EACH ROW
  EXECUTE PROCEDURE public.handle_updated_at();

CREATE TRIGGER on_env_scraper_jobs_update
  BEFORE UPDATE ON public.env_scraper_jobs
  FOR EACH ROW
  EXECUTE PROCEDURE public.handle_updated_at();
