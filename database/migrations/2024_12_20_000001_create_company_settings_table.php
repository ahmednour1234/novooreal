<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanySettingsTable extends Migration
{
    public function up()
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('vat_tin', 15)->unique();
            $table->string('cr_number', 50)->nullable();
            $table->string('company_name_ar', 255);
            $table->string('company_name_en', 255);
            $table->text('address_ar')->nullable();
            $table->text('address_en')->nullable();
            $table->enum('environment', ['simulation', 'production'])->default('simulation');
            $table->text('simulation_csid')->nullable();
            $table->text('production_csid')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('company_settings');
    }
}
