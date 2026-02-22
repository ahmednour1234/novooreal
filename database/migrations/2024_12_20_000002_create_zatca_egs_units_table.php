<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZatcaEgsUnitsTable extends Migration
{
    public function up()
    {
        Schema::create('zatca_egs_units', function (Blueprint $table) {
            $table->id();
            $table->string('egs_id', 50)->unique();
            $table->string('name', 255);
            $table->enum('type', ['branch', 'cashier'])->default('branch');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('private_key_path', 500)->nullable();
            $table->string('public_key_path', 500)->nullable();
            $table->string('certificate_path', 500)->nullable();
            $table->string('csr_path', 500)->nullable();
            $table->text('compliance_csid')->nullable();
            $table->text('production_csid')->nullable();
            $table->enum('status', ['pending', 'active'])->default('pending');
            $table->timestamp('onboarded_at')->nullable();
            $table->timestamps();

            $table->index('egs_id');
            $table->index('branch_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('zatca_egs_units');
    }
}
