<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('nama_pt');
            $table->string('alamat_pt');
            $table->string('company_type');
            $table->string('nomor_wa');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('user');
            $table->boolean('is_activated')->default(false);
            $table->bigInteger('id_pt')->nullable()->index();
            $table->foreign('id_pt')->references('pt_id')->on('tbl_pt')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_users');
    }
};