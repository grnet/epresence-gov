<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(zoomPresenceSqlSeeder::class);
       // $this->call(zoomNamedUsersSeeder::class);
        $this->call(zoomPresenceDeptInstSeeder::class);
        $this->call(zoomPresenceUserSeeder::class);
        $this->call(ZoomDownloadsSeeder::class);
        $this->call(LanguageListSeeder::class);
        $this->call(SupportVideoSeeder::class);
        $this->call(DocumentSeeder::class);

    }
}
