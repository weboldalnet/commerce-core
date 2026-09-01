<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='commerce_invoice_documents' AND column_name='error_code') THEN
                    ALTER TABLE public.commerce_invoice_documents ADD COLUMN error_code VARCHAR(50) DEFAULT NULL;
                END IF;
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='commerce_invoice_documents' AND column_name='error_message') THEN
                    ALTER TABLE public.commerce_invoice_documents ADD COLUMN error_message TEXT DEFAULT NULL;
                END IF;
            END
            $$
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE public.commerce_invoice_documents DROP COLUMN IF EXISTS error_code");
        DB::statement("ALTER TABLE public.commerce_invoice_documents DROP COLUMN IF EXISTS error_message");
    }
};
