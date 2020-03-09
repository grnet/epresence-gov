**Migration facts:**

1. Fix final institutions/departments ids and make sure the match on both databases
2. If i find two users with the same email but different state ? -> merge keep sso
3. If i find two users against unconfirmed ? -> merge keep confirmed


**Database issues found:**

1. Sometimes on sso users email returned from idp is in capital letters and user is invited from an email written differently.
2. System creates a new extra email for the users where it shouldn't be we need to check the comparison of those to emails to be case insensitive
3. We need to fix confirmed and not deleted users with no department/institution in vidyo/zoom platform



* Migration strategy
* Migrate users
* Migrate extra emails
* 'applications (user_id) bring new only and update user_id' ,
* 'cdrs (user_id,conference_id) bring from new with new conference_id & user_id',
* 'demo_room_cdrs (user_id) bring from new with new user_id',
* 'demo_room_connections (user_id)',
* 'demo_room_join_urls (user_id)',
* 'demo_room_statistics_monthly insert from old db kai prosthetoume me to xeri ta kainouria'
* 'former_utilization_statistics bring from old db'
* 'utilization_statistics bring from new db(zoom)',
* 'statistics (conference_id) (department_id) merge the stats using conference relation table'
* 'statistics_monthly add vidyo-room to h323 from old db and insert as is from new db'

