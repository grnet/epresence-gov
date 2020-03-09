<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class AuditLog extends Model
{

    protected $guarded = [];

    /**
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    public static function createAudiLog($parameters = []){
       //toDo move this to the observer
       return self::create([
            'entity_code'=>'AuditEntityCode',
            'entity_subunit'=>'medion7',
            'transaction_id'=>Uuid::uuid1()->toString(),
            'transaction_reason'=>isset($parameters['transaction_reason']) ? $parameters['transaction_reason'] : 'NULL',
            'server_hostname'=>request()->getHost(),
            'server_host_ip'=>!empty(request()->server('LOCAL_ADDR')) ? request()->server('LOCAL_ADDR') : '127.0.0.1',
            'end_user_device_info'=>request()->server('REMOTE_ADDR'),
            'end_user_device_ip'=>request()->server('REMOTE_ADDR'),
        ]);
    }
}
