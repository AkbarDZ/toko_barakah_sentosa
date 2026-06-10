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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id('id_transaksi');
            $table->string('kode_transaksi', 50)->unique();
            $table->integer('total_tagihan'); //total yg harus dibayar
            $table->integer('jumlah_bayar'); //brp yang dibayar pembeli
            $table->integer('kembalian'); //kembalinya ke pembeli
            $table->integer('total_keuntungan');
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
