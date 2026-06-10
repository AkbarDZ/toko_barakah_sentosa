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
        Schema::table('produk', function (Blueprint $table) {
            try {
                $table->dropForeign(['id_kategori']);
            } catch (\Exception $e) {
                // ignore
            }

            $table->foreign('id_kategori')
                ->references('id_kategori')
                ->on('kategori')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            try {
                $table->dropForeign(['id_kategori']);
            } catch (\Exception $e) {
                // ignore
            }

            $table->foreign('id_kategori')
                ->references('id_kategori')
                ->on('kategori')
                ->onDelete('restrict');
        });
    }
};
