<?php

use Illuminate\Database\Seeder;
use App\Settings;
use Carbon\Carbon;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = array(
			array('title' => 'maintenance_mode','category' => 'application','option' => '0'),
			array('title' => 'maintenance_start','category' => 'application','option' => NULL),
			array('title' => 'maintenance_end','category' => 'application','option' => NULL),
			array('title' => 'maintenance_message','category' => 'application','option' => NULL),
			array('title' => 'maintenance_moderators','category' => 'application','option' => '0'),
			array('title' => 'conference_maxDesktop','category' => 'conference','option' => '15'),
			array('title' => 'conference_maxH323','category' => 'conference','option' => '10'),
			array('title' => 'conference_maxVidyoRoom','category' => 'conference','option' => '5'),
			array('title' => 'conference_maxDuration','category' => 'conference','option' => '400'),
			array('title' => 'conference_desktopResources','category' => 'conference','option' => '270'),
			array('title' => 'conference_H323Resources','category' => 'conference','option' => '75'),
			array('title' => 'conference_totalResources','category' => 'conference','option' => '400'),
			array('title' => 'maintenance_excludeIPs','category' => 'application','option' => NULL)
		);
		
		foreach($settings as $setting){
			Settings::create([
				'title' => $setting['title'],
				'category' => $setting['category'],
				'option' => $setting['option'],
				'created_at' => Carbon::now(),
				'updated_at' => Carbon::now(),
			]);
		}
    }
}
