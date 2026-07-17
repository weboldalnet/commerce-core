<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CommerceCoreCreateCommerceTables extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE TABLE IF NOT EXISTS public.commerce_payment_transactions (
                id SERIAL PRIMARY KEY,
                order_id INTEGER NULL,
                provider VARCHAR(100) NOT NULL,
                payment_method VARCHAR(100) NULL,
                transaction_id VARCHAR(255) NULL,
                provider_transaction_id VARCHAR(255) NULL,
                amount DECIMAL(12,2) NOT NULL DEFAULT 0,
                currency VARCHAR(10) NOT NULL DEFAULT 'HUF',
                status VARCHAR(50) NOT NULL DEFAULT 'pending',
                request_payload JSONB NULL,
                response_payload JSONB NULL,
                callback_payload JSONB NULL,
                paid_at TIMESTAMP NULL,
                failed_at TIMESTAMP NULL,
                cancelled_at TIMESTAMP NULL,
                refunded_at TIMESTAMP NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )
        ");

        DB::statement("CREATE INDEX IF NOT EXISTS idx_commerce_payment_transactions_order_id ON public.commerce_payment_transactions (order_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_commerce_payment_transactions_transaction_id ON public.commerce_payment_transactions (transaction_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_commerce_payment_transactions_provider_transaction_id ON public.commerce_payment_transactions (provider_transaction_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_commerce_payment_transactions_status ON public.commerce_payment_transactions (status)");

        DB::statement("
            CREATE TABLE IF NOT EXISTS public.commerce_invoice_documents (
                id SERIAL PRIMARY KEY,
                order_id INTEGER NULL,
                provider VARCHAR(100) NOT NULL,
                invoice_number VARCHAR(255) NULL,
                invoice_id VARCHAR(255) NULL,
                status VARCHAR(50) NOT NULL DEFAULT 'pending',
                gross_total DECIMAL(12,2) NULL,
                currency VARCHAR(10) NULL,
                pdf_path VARCHAR(500) NULL,
                request_payload JSONB NULL,
                response_payload JSONB NULL,
                issued_at TIMESTAMP NULL,
                voided_at TIMESTAMP NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )
        ");

        DB::statement("CREATE INDEX IF NOT EXISTS idx_commerce_invoice_documents_order_id ON public.commerce_invoice_documents (order_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_commerce_invoice_documents_invoice_number ON public.commerce_invoice_documents (invoice_number)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_commerce_invoice_documents_status ON public.commerce_invoice_documents (status)");

        DB::statement("
            CREATE TABLE IF NOT EXISTS public.commerce_shipments (
                id SERIAL PRIMARY KEY,
                order_id INTEGER NULL,
                provider VARCHAR(100) NOT NULL,
                shipping_method VARCHAR(100) NULL,
                tracking_number VARCHAR(255) NULL,
                tracking_url VARCHAR(500) NULL,
                label_path VARCHAR(500) NULL,
                status VARCHAR(50) NOT NULL DEFAULT 'pending',
                request_payload JSONB NULL,
                response_payload JSONB NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )
        ");

        DB::statement("CREATE INDEX IF NOT EXISTS idx_commerce_shipments_order_id ON public.commerce_shipments (order_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_commerce_shipments_tracking_number ON public.commerce_shipments (tracking_number)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_commerce_shipments_status ON public.commerce_shipments (status)");

        DB::statement("
            CREATE TABLE IF NOT EXISTS public.commerce_provider_logs (
                id SERIAL PRIMARY KEY,
                provider_type VARCHAR(50) NOT NULL,
                provider VARCHAR(100) NOT NULL,
                order_id INTEGER NULL,
                direction VARCHAR(20) NULL,
                endpoint VARCHAR(500) NULL,
                request_payload JSONB NULL,
                response_payload JSONB NULL,
                status_code INTEGER NULL,
                is_success BOOLEAN NOT NULL DEFAULT FALSE,
                error_message TEXT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )
        ");

        DB::statement("CREATE INDEX IF NOT EXISTS idx_commerce_provider_logs_provider_type ON public.commerce_provider_logs (provider_type)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_commerce_provider_logs_provider ON public.commerce_provider_logs (provider)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_commerce_provider_logs_order_id ON public.commerce_provider_logs (order_id)");
    }

    public function down()
    {
        DB::statement("DROP TABLE IF EXISTS public.commerce_provider_logs CASCADE");
        DB::statement("DROP TABLE IF EXISTS public.commerce_shipments CASCADE");
        DB::statement("DROP TABLE IF EXISTS public.commerce_invoice_documents CASCADE");
        DB::statement("DROP TABLE IF EXISTS public.commerce_payment_transactions CASCADE");
    }
}
