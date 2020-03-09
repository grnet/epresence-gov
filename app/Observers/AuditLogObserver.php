<?php

namespace App\Observers;

use App\AuditLog;
use Illuminate\Support\Facades\Log;

class AuditLogObserver
{

    /**
     * Dispatched when an audit log entry is creating
     *
     * @param AuditLog $log
     */
    public function creating(AuditLog $log)
    {
        Log::info("Creating audit log entry: ".json_encode($log));
    }

}