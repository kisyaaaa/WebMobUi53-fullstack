<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->unique(
                ['poll_id', 'user_id', 'poll_option_id'],
                'poll_votes_user_option_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropUnique('poll_votes_user_option_unique');
        });
    }
};