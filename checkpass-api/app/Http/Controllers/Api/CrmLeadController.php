<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CrmLeadController extends Controller
{
    /**
     * GET /api/v1/crm/leads
     * Retrieve pipeline leads and high-level KPI telemetry.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Lead::query();

        if ($request->filled('stage') && $request->stage !== 'all') {
            $query->where('stage', $request->stage);
        }

        if ($request->filled('city') && $request->city !== 'all') {
            $query->where('city', $request->city);
        }

        if ($request->filled('source') && $request->source !== 'all') {
            $query->where('source', $request->source);
        }

        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('estate_name', 'like', $term)
                  ->orWhere('name', 'like', $term)
                  ->orWhere('phone', 'like', $term)
                  ->orWhere('email', 'like', $term);
            });
        }

        $leads = $query->orderBy('created_at', 'desc')->get();

        // Calculate KPI Metrics across the entire database
        $allLeads = Lead::all();
        $activeDeals = $allLeads->filter(fn($l) => $l->stage !== 'won');

        $metrics = [
            'total_pipeline_value' => (float) $activeDeals->sum('deal_value'),
            'active_deals_count'   => $activeDeals->count(),
            'ai_inflow_count'      => $allLeads->where('source', 'AI Concierge')->count(),
            'sandbox_pilots_count' => $allLeads->where('stage', 'pilot')->count(),
            'closed_won_count'     => $allLeads->where('stage', 'won')->count(),
            'total_leads_count'    => $allLeads->count(),
        ];

        return response()->json([
            'success' => true,
            'metrics' => $metrics,
            'data'    => $leads,
        ]);
    }

    /**
     * POST /api/v1/crm/leads
     * Capture new inbound lead (from landing AI Concierge or manual entry).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'estate_name'     => 'required|string|max:255',
            'name'            => 'required|string|max:255',
            'role'            => 'nullable|string|max:100',
            'phone'           => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:255',
            'city'            => 'nullable|string|max:100',
            'units'           => 'nullable|integer|min:1',
            'deal_value'      => 'nullable|numeric|min:0',
            'stage'           => 'nullable|string|in:new,discovery,demo,proposal,pilot,won',
            'source'          => 'nullable|string|max:100',
            'chat_transcript' => 'nullable|string',
            'notes'           => 'nullable|string',
            'metadata'        => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $units = (int) ($data['units'] ?? 50);
        $dealValue = isset($data['deal_value']) && (float) $data['deal_value'] > 0
            ? (float) $data['deal_value']
            : ($units * 1200 * 12);

        $lead = Lead::create([
            'estate_name'     => $data['estate_name'],
            'name'            => $data['name'],
            'role'            => $data['role'] ?? 'HOA Representative',
            'phone'           => $data['phone'] ?? null,
            'email'           => $data['email'] ?? null,
            'city'            => $data['city'] ?? 'Lagos',
            'units'           => $units,
            'deal_value'      => $dealValue,
            'stage'           => $data['stage'] ?? 'new',
            'source'          => $data['source'] ?? 'AI Concierge',
            'chat_transcript' => $data['chat_transcript'] ?? null,
            'notes'           => $data['notes'] ?? 'Captured via CheckPass CRM System.',
            'metadata'        => $data['metadata'] ?? [],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead captured successfully.',
            'data'    => $lead,
        ], 201);
    }

    /**
     * GET /api/v1/crm/leads/{id}
     */
    public function show(string $id): JsonResponse
    {
        $lead = is_numeric($id) ? Lead::find($id) : Lead::where('ulid', $id)->first();

        if (!$lead) {
            return response()->json(['success' => false, 'message' => 'Lead not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $lead]);
    }

    /**
     * PATCH /api/v1/crm/leads/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $lead = is_numeric($id) ? Lead::find($id) : Lead::where('ulid', $id)->first();

        if (!$lead) {
            return response()->json(['success' => false, 'message' => 'Lead not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'stage'      => 'nullable|string|in:new,discovery,demo,proposal,pilot,won',
            'notes'      => 'nullable|string',
            'add_note'   => 'nullable|string',
            'role'       => 'nullable|string|max:100',
            'units'      => 'nullable|integer|min:1',
            'deal_value' => 'nullable|numeric|min:0',
            'admin_id'   => 'nullable|exists:admins,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        if (isset($data['stage'])) {
            $lead->stage = $data['stage'];
        }

        if (isset($data['notes'])) {
            $lead->notes = $data['notes'];
        }

        if (!empty($data['add_note'])) {
            $timestamp = now()->format('d/m/Y H:i');
            $newEntry = "\n[{$timestamp}] " . trim($data['add_note']);
            $lead->notes = ($lead->notes ? $lead->notes . "\n" : '') . $newEntry;
        }

        if (isset($data['role'])) {
            $lead->role = $data['role'];
        }

        if (isset($data['units'])) {
            $lead->units = (int) $data['units'];
            if (!isset($data['deal_value'])) {
                $lead->deal_value = $lead->units * 1200 * 12;
            }
        }

        if (isset($data['deal_value'])) {
            $lead->deal_value = (float) $data['deal_value'];
        }

        if (array_key_exists('admin_id', $data)) {
            $lead->admin_id = $data['admin_id'];
        }

        $lead->save();

        return response()->json([
            'success' => true,
            'message' => 'Lead updated successfully.',
            'data'    => $lead,
        ]);
    }

    /**
     * DELETE /api/v1/crm/leads/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $lead = is_numeric($id) ? Lead::find($id) : Lead::where('ulid', $id)->first();

        if (!$lead) {
            return response()->json(['success' => false, 'message' => 'Lead not found.'], 404);
        }

        $lead->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lead removed from CRM pipeline.',
        ]);
    }

    /**
     * POST /api/v1/crm/leads/{id}/provision-sandbox
     * Converts a lead into a 48-Hour Live Pilot.
     */
    public function provisionSandbox(string $id): JsonResponse
    {
        $lead = is_numeric($id) ? Lead::find($id) : Lead::where('ulid', $id)->first();

        if (!$lead) {
            return response()->json(['success' => false, 'message' => 'Lead not found.'], 404);
        }

        $lead->stage = 'pilot';
        $timestamp = now()->format('d/m/Y H:i');
        $lead->notes = ($lead->notes ? $lead->notes . "\n" : '') . "[{$timestamp}] ⚡ Provisioned 48-Hour Free 3-Unit Sandbox Pilot.";
        $lead->save();

        $queryParams = http_build_query([
            'estateName' => $lead->estate_name,
            'city'       => $lead->city,
            'units'      => min($lead->units, 3), // 3-unit free sandbox
            'contact'    => $lead->name,
            'email'      => $lead->email,
            'phone'      => $lead->phone,
            'pilot'      => 'true',
        ]);

        return response()->json([
            'success'     => true,
            'message'     => 'Sandbox pilot initialized.',
            'sandbox_url' => "/register/estate?{$queryParams}",
            'data'        => $lead,
        ]);
    }
}

