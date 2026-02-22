<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZatcaAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::create('zatca_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action', 100);
            $table->unsignedBigInteger('egs_unit_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('zatca_document_id')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('action');
            $table->index('egs_unit_id');
            $table->index('order_id');
            $table->index('zatca_document_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('zatca_audit_logs');
    }
}
