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
        Schema::create('detail_pergerakan_stok', function (Blueprint $table) {
            $table->id('id_detail'); // Primary Key
            $table->unsignedBigInteger('id_pergerakan');
            $table->unsignedBigInteger('id_satuan')->nullable(); // Boleh null untuk mengakomodasi SET NULL

            // Kolom Operasional
            $table->integer('kuantiti');
            
            // Kolom Snapshot (Nilai tidak akan berubah)
            $table->string('snapshot_nama_produk');
            $table->string('snapshot_kode_produk');
            $table->string('snapshot_nama_satuan');
            $table->decimal('snapshot_harga_beli', 15, 2)->nullable(); 
            
            $table->timestamps();

            // Definisi Foreign Key
            $table->foreign('id_pergerakan')
                  ->references('id_pergerakan')->on('pergerakan_stok')
                  ->onDelete('cascade'); // Jika header dihapus, detail ikut terhapus

            $table->foreign('id_satuan')
                  ->references('id_satuan')->on('satuan_produk')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pergerakan_stok');
    }
};
