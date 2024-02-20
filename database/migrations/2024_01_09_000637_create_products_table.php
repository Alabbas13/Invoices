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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name', 999);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('section_id'); // هون رقم كبير و م لازم يكون سالب و هو عبارة عن رقم القسم المرتبط به المنتج
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
            //ربطت عمود رقم القسم الموجود في جدول المنتجات مع رقم القسم الموجود في جدول الأقسام و عند حذف القسم يتم حذف جميع المنتجات المرتبطة به
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
