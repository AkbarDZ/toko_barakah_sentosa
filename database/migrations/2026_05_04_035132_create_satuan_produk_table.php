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
        Schema::create('satuan_produk', function (Blueprint $table) {
            $table->id('id_satuan');
            $table->foreignId('id_produk')->constrained(table: 'produk', column: 'id_produk')->onDelete('cascade');
            $table->string('nama_satuan', 50);
            $table->integer('kuantiti_per_satuan');
            $table->integer('harga_beli');
            $table->integer('harga_jual');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satuan_produk');
    }
};
