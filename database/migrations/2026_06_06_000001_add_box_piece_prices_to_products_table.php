<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->double('box_price', 8, 2)->nullable()->after('sale_price');
            $table->double('piece_price', 8, 2)->nullable()->after('box_price');
        });

        DB::table('products')->whereNull('box_price')->update([
            'box_price' => DB::raw('sale_price'),
        ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['box_price', 'piece_price']);
        });
    }
};
