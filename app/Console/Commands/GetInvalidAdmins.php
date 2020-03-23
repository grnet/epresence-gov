<?php

namespace App\Console\Commands;

use App\Institution;
use App\User;
use Illuminate\Console\Command;
use App\Traits\interactsWithEmploymentApi;

class GetInvalidAdmins extends Command
{
    use interactsWithEmploymentApi;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:invalid_admins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $admins = User::whereHas("roles",function($roleQuery){
            $roleQuery->whereIn("name",["DepartmentAdministrator","InstitutionAdministrator"]);
        })->get();
        $fp = fopen(storage_path('app/admins_report.csv'), 'w');

        foreach($admins as $admin){
            $attachedInstitution = $admin->institutions()->first();
            $attachedDepartment = $admin->departments()->first();
            $responseObject = $this->getEmploymentInfo($admin->tax_id);
            if($responseObject !== false){
                $matchedInstitutions = [];
                foreach ($responseObject->data->employmentInfos as $employmentInfo) {
                    $institutionMatched = Institution::where("ws_id", $employmentInfo->organicOrganizationId)->first();
                    if ($institutionMatched) {
                        $matchedInstitutions[] = $institutionMatched->id;
                    }
                }
                if(!in_array($attachedInstitution->id,$matchedInstitutions)){
                    $fields = [
                        $admin->id,
                        $attachedInstitution->title.' ('.$attachedInstitution->id.')',
                        $attachedDepartment->title.' ('.$attachedDepartment->id.')',
                        json_encode($responseObject->data->employmentInfos,JSON_UNESCAPED_UNICODE ),
                        implode(",",$matchedInstitutions)
                    ];
                    fputcsv($fp, $fields);
                }
            }else{
                $admin->update(['civil_servant'=>false]);
                $fields = [
                    $admin->id,
                    $attachedInstitution->title.' ('.$attachedInstitution->id.')',
                    $attachedDepartment->title.' ('.$attachedDepartment->id.')',
                    '-',
                    '-'
                ];
                fputcsv($fp, $fields);
            }
        }
    }
}
