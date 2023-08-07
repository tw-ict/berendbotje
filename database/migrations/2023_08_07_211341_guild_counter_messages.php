<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guild_counts_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guild_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('current_count_thread');
            $table->foreign('current_count_thread')->references('id')->on('guild_count_threads');
            $table->foreign('guild_id')->references('id')->on('guilds');
            $table->foreign('user_id')->references('id')->on('discord_users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('guild_counts_messages');
    }
};
