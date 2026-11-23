<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kyc_submissions', function (Blueprint $table) {
            $table->string('document_mime', 120)->nullable()->after('document_path');
            $table->string('selfie_mime', 120)->nullable()->after('selfie_path');
        });
    }

    public function down(): void
    {
        Schema::table('kyc_submissions', function (Blueprint $table) {
            $table->dropColumn(['document_mime', 'selfie_mime']);
        });
    }
};
