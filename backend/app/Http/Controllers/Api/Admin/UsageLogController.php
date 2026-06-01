<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentLog;
use App\Models\AuditLog;

class UsageLogController extends Controller
{
    public function agentLogs()
    {
        return response()->json(['data' => AgentLog::latest('created_at')->limit(100)->get()]);
    }

    public function auditLogs()
    {
        return response()->json(['data' => AuditLog::latest('created_at')->limit(100)->get()]);
    }
}
