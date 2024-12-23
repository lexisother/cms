<?php

use Delight\Auth\Auth;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations
     */
    public function up(): void
    {
        Schema::create('oauth_config', function (Blueprint $table) {
            $table->id();
            $table->string('client_private_key');
            $table->string('client_public_key');
            $table->string('key_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_config');
    }
};
