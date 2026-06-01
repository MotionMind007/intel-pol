<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'modules' => [
                ['slug' => 'screening-tokoh', 'name' => 'Screening Tokoh', 'description' => 'Screening tokoh politik berbasis data publik dan analisis AI.', 'status' => 'active'],
                ['slug' => 'monitoring-isu', 'name' => 'Monitoring Isu', 'description' => 'Segera hadir.', 'status' => 'coming_soon'],
                ['slug' => 'analisis-sentimen', 'name' => 'Analisis Sentimen', 'description' => 'Segera hadir.', 'status' => 'coming_soon'],
                ['slug' => 'peta-elektoral', 'name' => 'Peta Elektoral', 'description' => 'Segera hadir.', 'status' => 'coming_soon'],
                ['slug' => 'media-monitoring', 'name' => 'Media Monitoring', 'description' => 'Segera hadir.', 'status' => 'coming_soon'],
                ['slug' => 'strategi-kampanye', 'name' => 'Strategi Kampanye', 'description' => 'Segera hadir.', 'status' => 'coming_soon'],
                ['slug' => 'quick-count-internal', 'name' => 'Quick Count Internal', 'description' => 'Segera hadir.', 'status' => 'coming_soon'],
            ],
            'admin_menus' => $request->user()->role === 'super_admin'
                ? ['Agent Settings', 'User Management', 'Role Management', 'API Provider Settings', 'Usage Logs']
                : [],
        ]);
    }
}
