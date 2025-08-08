<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_pt', function (Blueprint $table) {
            $table->bigIncrements('pt_id');
            $table->string('nama_pt');
            $table->string('alamat_pt');
            $table->string('company_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_pt');
    }
};