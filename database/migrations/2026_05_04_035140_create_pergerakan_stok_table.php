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
        Schema::create('pergerakan_stok', function (Blueprint $table) {
            $table->id('id_pergerakan');
            $table->string('kode_pergerakan', 20)->unique(); // Kode unik untuk setiap detail pergerakan
            // $table->foreignId('id_produk')->constrained(table: 'produk', column: 'id_produk')->onDelete('cascade');
            // $table->foreignId('id_satuan')->constrained(table: 'satuan_produk', column: 'id_satuan')->onDelete('cascade');
            $table->enum('tipe_pergerakan', ['masuk', 'keluar']);
            // $table->integer('kuantiti');
            $table->dateTime('tanggal_pergerakan');
            $table->string('catatan', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pergerakan_stok');
    }
};
