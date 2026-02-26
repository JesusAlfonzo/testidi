<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_quotes', function (Blueprint $table) {
            $table->foreignId('rfq_id')->nullable()->after('user_id')->constrained('request_for_quotations')->onDelete('set null');
            
            $table->string('supplier_name_temp')->nullable()->after('supplier_id');
            $table->string('supplier_email_temp')->nullable()->after('supplier_name_temp');
            $table->string('supplier_phone_temp')->nullable()->after('supplier_email_temp');
            
            $table->string('rejection_reason')->nullable()->after('notes');
            
            $table->foreignId('approved_by')->nullable()->after('rejection_reason')->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_quotes', function (Blueprint $table) {
            $table->dropForeign(['rfq_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'rfq_id',
                'supplier_name_temp',
                'supplier_email_temp',
                'supplier_phone_temp',
                'rejection_reason',
                'approved_by',
                'approved_at'
            ]);
        });
    }
};
