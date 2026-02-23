<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResponseJsonToZatcaDocumentsTable extends Migration
{
    public function up()
    {
        Schema::table('zatca_documents', function (Blueprint $table) {
            $table->json('response_json')->nullable()->after('error_message');
        });
    }

    public function down()
    {
        Schema::table('zatca_documents', function (Blueprint $table) {
            $table->dropColumn('response_json');
        });
    }
}
