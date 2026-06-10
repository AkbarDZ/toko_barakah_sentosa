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
        Schema::create('produk', function (Blueprint $table) {
            $table->id('id_produk');
            $table->char('kode_produk', 10)->unique();
            $table->foreignId('id_kategori')->constrained(table: 'kategori', column: 'id_kategori')->onDelete('restrict');
            $table->string('nama_produk', 100);
            $table->string('deskripsi', 255)->nullable();
            $table->string('direktori_gambar', 255)->nullable();
            $table->integer('total_stok_terkecil')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
