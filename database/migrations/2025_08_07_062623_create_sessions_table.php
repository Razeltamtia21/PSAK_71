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
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // Primary key untuk session ID
            $table->bigInteger('user_id', 10)->nullable()->index(); // Ubah dari foreignId ke string agar sesuai dengan user_id di tbl_users
            $table->string('ip_address', 45)->nullable(); // Alamat IP (IPv4 atau IPv6)
            $table->text('user_agent')->nullable(); // String user agent
            $table->longText('payload')->nullable(false); // Payload session
            $table->integer('last_activity')->index(); // Timestamp aktivitas terakhir

            // Menambahkan foreign key constraint
            $table->foreign('user_id')->references('user_id')->on('tbl_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
public function down(): void
{
    Schema::table('sessions', function (Blueprint $table) {
        try {
            $table->dropForeign(['user_id']);
        } catch (\Exception $e) {
        }
    });

    Schema::dropIfExists('sessions');
    }
};