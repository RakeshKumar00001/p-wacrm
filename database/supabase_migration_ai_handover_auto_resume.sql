-- -----------------------------------------------------------------------------
-- SUPABASE / POSTGRESQL MIGRATION: AI Handover & Auto-Resume Timer
-- Run this script in your Supabase SQL Editor (https://supabase.com/dashboard)
-- -----------------------------------------------------------------------------

-- 1. Add columns to 'conversations' table for AI Auto Resume and Handover timestamps
ALTER TABLE conversations 
ADD COLUMN IF NOT EXISTS ai_auto_resume_at TIMESTAMPTZ DEFAULT NULL,
ADD COLUMN IF NOT EXISTS ai_handover_at TIMESTAMPTZ DEFAULT NULL;

-- 2. Add column to 'businesses' table for default auto-resume duration
ALTER TABLE businesses 
ADD COLUMN IF NOT EXISTS ai_auto_resume_minutes INTEGER DEFAULT 0;

-- Optional verification query:
SELECT table_name, column_name, data_type 
FROM information_schema.columns 
WHERE table_name IN ('conversations', 'businesses') 
  AND column_name IN ('ai_auto_resume_at', 'ai_handover_at', 'ai_auto_resume_minutes');
