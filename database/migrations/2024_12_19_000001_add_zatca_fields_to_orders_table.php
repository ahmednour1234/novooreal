<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddZatcaFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('uuid', 36)->unique()->nullable()->after('id');
            $table->string('invoice_number')->nullable()->after('uuid');
            $table->unsignedBigInteger('invoice_counter')->nullable()->after('invoice_number');
            $table->string('previous_invoice_hash')->nullable()->after('invoice_counter');
            $table->boolean('zatca_submitted')->default(false)->after('previous_invoice_hash');
            $table->timestamp('zatca_submitted_at')->nullable()->after('zatca_submitted');
            $table->text('zatca_qr_code')->nullable()->after('zatca_submitted_at');
            $table->string('currency_code', 3)->default('SAR')->after('zatca_qr_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'uuid',
                'invoice_number',
                'invoice_counter',
                'previous_invoice_hash',
                'zatca_submitted',
                'zatca_submitted_at',
                'zatca_qr_code',
                'currency_code'
            ]);
        });
    }
}
