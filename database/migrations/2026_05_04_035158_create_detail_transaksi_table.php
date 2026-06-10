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
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id('id_detail');
            $table->foreignId('id_transaksi')->constrained(table: 'transaksi', column: 'id_transaksi')->onDelete('cascade');
            // restrict agar satuan tidak bisa dihapus jika pernah ada di transaksi
            $table->foreignId('id_satuan')->constrained(table: 'satuan_produk', column: 'id_satuan')->onDelete('restrict'); 
            $table->integer('kuantiti');
            $table->integer('harga_beli'); // Snapshot Harga Beli
            $table->integer('harga_jual'); // Snapshot Harga Jual
            $table->integer('subtotal'); // Kolom fisik (qty * selling_price)
            $table->integer('keuntungan'); // Kolom fisik (qty * (selling_price - cost_price))
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};
