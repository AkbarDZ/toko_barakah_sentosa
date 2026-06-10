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
        Schema::table('detail_transaksi', function (Blueprint $table) {
            // drop existing foreign key on id_satuan if exists
            try {
                $table->dropForeign(['id_satuan']);
            } catch (\Exception $e) {
                // ignore if not exists
            }

            $table->foreign('id_satuan')
                ->references('id_satuan')
                ->on('satuan_produk')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            try {
                $table->dropForeign(['id_satuan']);
            } catch (\Exception $e) {
            }

            $table->foreign('id_satuan')
                ->references('id_satuan')
                ->on('satuan_produk')
                ->onDelete('restrict');
        });
    }
};
