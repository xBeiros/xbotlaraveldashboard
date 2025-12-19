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
        Schema::table('welcome_configs', function (Blueprint $table) {
            $table->boolean('use_embed')->default(false)->after('enabled');
            $table->string('embed_title')->nullable()->after('use_embed');
            $table->text('embed_description')->nullable()->after('embed_title');
            $table->string('embed_color')->nullable()->after('embed_description');
            $table->string('embed_thumbnail')->nullable()->after('embed_color');
            $table->string('embed_image')->nullable()->after('embed_thumbnail');
            $table->boolean('embed_footer')->default(true)->after('embed_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('welcome_configs', function (Blueprint $table) {
            $table->dropColumn([
                'use_embed',
                'embed_title',
                'embed_description',
                'embed_color',
                'embed_thumbnail',
                'embed_image',
                'embed_footer',
            ]);
        });
    }
};
