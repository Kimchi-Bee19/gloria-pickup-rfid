<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Test if this is running within erd:generate
        $isErdEnv = DB::getDriverName() == "sqlite";

        Schema::table('arrival_departure_trackings', function (Blueprint $table) use ($isErdEnv) {
            // Drop the announced_count and last_announced_at columns
            $table->dropColumn('announced_count');
            $table->dropColumn('last_announced_time');

            // Add pinned column
            $table->boolean('pinned')->default(false);

            if (!$isErdEnv) {
                // Create a sequence for the daily_absolute_position column
                DB::statement('CREATE SEQUENCE IF NOT EXISTS arrival_departure_trackings_daily_absolute_position_seq;');

                // Create a function to get the next value for the sequence, which resets daily
                DB::statement('CREATE OR REPLACE FUNCTION nextval_arrival_departure_trackings_daily_absolute_position_seq() RETURNS double precision AS $$
                SELECT
                    CASE WHEN NOT EXISTS (
                        SELECT 1
                        FROM public.arrival_departure_trackings
                        WHERE DATE(created_at) = DATE(TIMEZONE(\'Asia/Jakarta\', NOW()))
                    ) THEN
                        setval(\'arrival_departure_trackings_daily_absolute_position_seq\', 1)
                    ELSE
                        nextval(\'arrival_departure_trackings_daily_absolute_position_seq\')
                    END
                $$ LANGUAGE sql;'
                );
            }

            // Create a daily_absolute_position column
            // This assumes that we're on Asia/Jakarta Timezone
            // All entries in the database is in UTC

            if ($isErdEnv)
                $table
                    ->double('daily_absolute_position');
            else
                $table
                    ->double('daily_absolute_position')
                    ->default(
                        DB::raw(
                            'nextval_arrival_departure_trackings_daily_absolute_position_seq()'
                        )
                    );
        });
    }

    public function down(): void
    {
        Schema::table('arrival_departure_trackings', function (Blueprint $table) {
            // Add the announced_count and last_announced_at columns back
            $table->unsignedInteger('announced_count')->default(0);
            $table->timestamp('last_announced_time')->nullable();

            // Drop the pinned column
            $table->dropColumn('pinned');

            // Drop the daily_absolute_position column
            $table->dropColumn('daily_absolute_position');

            // Drop the sequence for the daily_absolute_position column
            DB::statement('DROP SEQUENCE arrival_departure_trackings_daily_absolute_position_seq;');

            // Drop the function for the daily_absolute_position column
            DB::statement('DROP FUNCTION nextval_arrival_departure_trackings_daily_absolute_position_seq() CASCADE;');
        });
    }
};
