<?php

use Illuminate\Database\Seeder;


class demoRoomHourlyStatisticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('demo_room_statistics_hourly')->insert([
            ['hour' => '00:00:00', 'connections' => 0],
            ['hour' => '01:00:00', 'connections' => 0],
            ['hour' => '02:00:00', 'connections' => 0],
            ['hour' => '03:00:00', 'connections' => 0],
            ['hour' => '04:00:00', 'connections' => 0],
            ['hour' => '05:00:00', 'connections' => 0],
            ['hour' => '06:00:00', 'connections' => 0],
            ['hour' => '07:00:00', 'connections' => 0],
            ['hour' => '08:00:00', 'connections' => 0],
            ['hour' => '09:00:00', 'connections' => 0],
            ['hour' => '10:00:00', 'connections' => 0],
            ['hour' => '11:00:00', 'connections' => 0],
            ['hour' => '12:00:00', 'connections' => 0],
            ['hour' => '13:00:00', 'connections' => 0],
            ['hour' => '14:00:00', 'connections' => 0],
            ['hour' => '15:00:00', 'connections' => 0],
            ['hour' => '16:00:00', 'connections' => 0],
            ['hour' => '17:00:00', 'connections' => 0],
            ['hour' => '18:00:00', 'connections' => 0],
            ['hour' => '19:00:00', 'connections' => 0],
            ['hour' => '20:00:00', 'connections' => 0],
            ['hour' => '21:00:00', 'connections' => 0],
            ['hour' => '22:00:00', 'connections' => 0],
            ['hour' => '23:00:00', 'connections' => 0],
        ]);
    }
}
