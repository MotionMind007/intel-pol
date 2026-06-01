<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateScreeningReport;
use App\Models\Agent;
use App\Models\ScreeningReport;
use Illuminate\Http\Request;

class ScreeningReportController extends Controller
{
    public function index(Request $request)
    {
        $query = ScreeningReport::query()->latest();

        if (! in_array($request->user()->role, ['super_admin', 'admin'], true)) {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json(['data' => $query->limit(25)->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_name' => ['required', 'string', 'max:160'],
        ], [
            'subject_name.required' => 'Nama tokoh wajib diisi.',
        ]);

        $agent = Agent::with(['provider', 'model', 'skills'])
            ->where('status', 'active')
            ->firstOrFail();

        $report = ScreeningReport::create([
            'user_id' => $request->user()->id,
            'agent_id' => $agent->id,
            'subject_name' => $validated['subject_name'],
            'status' => 'pending',
            'queued_at' => now(),
        ]);

        GenerateScreeningReport::dispatch($report->id);

        return response()->json(['data' => $report], 202);
    }

    public function show(Request $request, ScreeningReport $screeningReport)
    {
        if (! in_array($request->user()->role, ['super_admin', 'admin'], true)
            && $screeningReport->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json(['data' => $screeningReport]);
    }

    public function destroy(Request $request, ScreeningReport $screeningReport)
    {
        if ($request->user()->role !== 'super_admin' && $screeningReport->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $screeningReport->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
