<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('PT BPRS Bangka Belitung');
            $table->string('tagline')->nullable()->default('Terwujudnya Bank Daerah Dambaan Masyarakat Negeri Serumpun Sebalai yang Terpercaya, Sehat, dan Menguntungkan');
            $table->text('description')->nullable();
            $table->string('email')->nullable()->default('it@bprsbabel.id');
            $table->string('phone')->nullable()->default('+62 717 9103567');
            $table->string('mobile')->nullable()->default('+62 717 91035677');
            $table->string('website')->nullable()->default('https://bprsbabel.id');
            $table->string('tax_id')->nullable()->default('01.234.567.8-012.000');
            $table->text('address')->nullable()->default('Jl. Kampung Melayu, Ruko TJ Tower Nomor 21-22 Bukit Merapin, Pangkal Pinang, Prov. Kepulauan Bangka Belitung 33179');
            $table->string('city')->nullable()->default('Pangkal Pinang');
            $table->string('postal_code')->nullable()->default('33179');
            $table->string('country')->nullable()->default('Indonesia');
            $table->string('logo_path')->nullable();
            $table->string('office_photo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
