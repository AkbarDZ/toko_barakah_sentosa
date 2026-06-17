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
        Schema::table('kategori', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('produk', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('satuan_produk', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('transaksi', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('pergerakan_stok', function (Blueprint $table) { $table->softDeletes(); });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('produk', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('satuan_produk', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('transaksi', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('pergerakan_stok', function (Blueprint $table) { $table->dropSoftDeletes(); });
    }
};
