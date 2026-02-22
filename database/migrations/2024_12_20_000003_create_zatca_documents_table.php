<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZatcaDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('zatca_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('egs_unit_id');
            $table->string('invoice_uuid', 36);
            $table->string('invoice_number', 100);
            $table->enum('invoice_type', ['standard', 'simplified'])->default('standard');
            $table->longText('xml_content')->nullable();
            $table->longText('signed_xml')->nullable();
            $table->text('qr_code_tlv')->nullable();
            $table->enum('submission_status', ['pending', 'success', 'failed'])->default('pending');
            $table->string('zatca_uuid', 100)->nullable();
            $table->string('zatca_long_id', 255)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->timestamps();

            $table->index('order_id');
            $table->index('egs_unit_id');
            $table->index('invoice_uuid');
            $table->index('submission_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('zatca_documents');
    }
}
