<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FeeController extends Controller
{
    /**
     * GET /api/v1/fees
     * Resident-facing fees list scoped to the authenticated user's estate.
     */
    public function feed(Request $request)
    {
        /** @var User|null $user */
        $user = $request->user();

        $resident = $user?->resident()->first();
        if (! $resident) {
            return response()->json([
                'error' => true,
                'code' => 'NO_RESIDENT_PROFILE',
                'message' => 'No resident profile found for this account.',
            ], 403);
        }

        $fees = Fee::query()
            ->where('estate_id', $resident->estate_id)
            ->with(['users' => fn($q) => $q->where('user_id', $user->id)])
            ->latest()
            ->get()
            ->map(function (Fee $fee) {
                $pivot = $fee->users->first()?->pivot;
                $status = 'Due';

                if ($pivot) {
                    $pivotStatus = $pivot->payment_status;
                    if ($pivotStatus === 'paid') {
                        $status = 'Paid';
                    } elseif ($pivotStatus === 'pending') {
                        $status = 'Pending';
                    } elseif ($pivotStatus === 'rejected') {
                        $status = 'Due';
                    } elseif ($pivot->file_path) {
                        // Backward compatibility for older rows before status was added.
                        $status = 'Pending';
                    }
                }

                return [
                'id' => (string) $fee->id,
                'amount' => (float) $fee->amount,
                'title' => $fee->name,
                'date' => $fee->created_at?->format('M d Y'),
                'status' => $status,
            ];
            });

        return response()->json(['fees' => $fees]);
    }

    /**
     * POST /api/v1/fees/payment-details
     * Resident-facing payment details for selected fee IDs.
     */
    public function paymentDetails(Request $request)
    {
        /** @var User|null $user */
        $user = $request->user();

        $resident = $user?->resident()->with('estate')->first();
        if (! $resident || ! $resident->estate) {
            return response()->json([
                'error' => true,
                'code' => 'NO_RESIDENT_PROFILE',
                'message' => 'No resident profile found for this account.',
            ], 403);
        }

        $data = $request->validate([
            'feeIds' => 'required|array|min:1',
            'feeIds.*' => 'required',
        ]);

        $feeIds = collect($data['feeIds'])->map(fn($id) => (string) $id)->all();

        $fees = Fee::query()
            ->where('estate_id', $resident->estate_id)
            ->whereIn('id', $feeIds)
            ->latest()
            ->get()
            ->map(fn(Fee $fee) => [
                'id' => (string) $fee->id,
                'amount' => (float) $fee->amount,
                'title' => $fee->name,
                'date' => $fee->created_at?->format('M d Y'),
            ])
            ->values();

        if ($fees->isEmpty()) {
            return response()->json([
                'error' => true,
                'code' => 'NO_FEES_SELECTED',
                'message' => 'No valid fees found for this estate.',
            ], 422);
        }

        $total = $fees->sum('amount');
        $estate = $resident->estate;

        return response()->json([
            'totalAmount' => (float) $total,
            'fees' => $fees,
            'bankDetails' => [
                'bankName' => $estate->bank_name,
                'accountName' => $estate->account_name,
                'accountNumber' => $estate->account_number,
            ],
        ]);
    }

    /**
     * POST /api/v1/fees/payments
     * Resident-facing payment submission to save fee_user entries with receipt upload.
     *
     * Body (multipart/form-data):
     *   feeIds[] - selected fee IDs
     *   file     - payment receipt image
     */
    public function submitPayment(Request $request)
    {
        /** @var User|null $user */
        $user = $request->user();

        $resident = $user?->resident()->first();
        if (! $resident) {
            return response()->json([
                'error' => true,
                'code' => 'NO_RESIDENT_PROFILE',
                'message' => 'No resident profile found for this account.',
            ], 403);
        }

        $data = $request->validate([
            'feeIds' => 'required|array|min:1',
            'feeIds.*' => 'required',
            'file' => 'required|image|max:10240',
        ]);

        $feeIds = collect($data['feeIds'])->map(fn($id) => (string) $id)->all();

        $fees = Fee::query()
            ->where('estate_id', $resident->estate_id)
            ->whereIn('id', $feeIds)
            ->with(['users' => fn($q) => $q->where('user_id', $user->id)])
            ->get();

        if ($fees->count() !== count(array_unique($feeIds))) {
            return response()->json([
                'error' => true,
                'code' => 'INVALID_FEES',
                'message' => 'One or more selected fees are invalid for this estate.',
            ], 422);
        }

        $paidFeeIds = $fees
            ->filter(fn(Fee $fee) => $fee->users->first()?->pivot?->payment_status === 'paid')
            ->map(fn(Fee $fee) => (string) $fee->id)
            ->values();

        if ($paidFeeIds->isNotEmpty()) {
            return response()->json([
                'error' => true,
                'code' => 'FEE_ALREADY_PAID',
                'message' => 'One or more selected fees are already marked as paid.',
                'feeIds' => $paidFeeIds,
            ], 422);
        }

        DB::transaction(function () use ($fees, $request, $user) {
            foreach ($fees as $fee) {
                $existing = $fee->users()->where('user_id', $user->id)->first();

                if ($existing?->pivot?->file_path && Storage::disk('public')->exists($existing->pivot->file_path)) {
                    Storage::disk('public')->delete($existing->pivot->file_path);
                }

                $filePath = $request->file('file')
                    ->store("fees/{$fee->id}/users/{$user->id}", 'public');

                $pivotData = [
                    'file_path' => $filePath,
                    'payment_status' => 'pending',
                    'verified_at' => null,
                    'verified_by' => null,
                ];

                if ($existing) {
                    $fee->users()->updateExistingPivot($user->id, $pivotData);
                } else {
                    $fee->users()->attach($user->id, $pivotData);
                }
            }
        });

        return response()->json([
            'message' => 'Payment submitted successfully.',
        ], 201);
    }

    // ── CRUD ───────────────────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/fees
     */
    public function index(Request $request)
    {
        $query = Fee::query();

        if ($request->filled('estate_id')) {
            $query->where('estate_id', $request->estate_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        $fees = $query->latest()->get()->map(fn(Fee $f) => $this->formatFee($f));

        return response()->json(['fees' => $fees]);
    }

    /**
     * POST /api/v1/admin/fees
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'estate_id'   => 'required|exists:estates,id',
            'code'        => 'required|string|max:50|unique:fees,code',
            'name'        => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'type'        => 'required|in:one-time,recurring',
            'description' => 'nullable|string',
        ]);

        $fee = Fee::create($data);

        return response()->json(['fee' => $this->formatFee($fee)], 201);
    }

    /**
     * GET /api/v1/admin/fees/{id}
     */
    public function show(Request $request, int $id)
    {
        $fee = Fee::findOrFail($id);

        return response()->json(['fee' => $this->formatFee($fee)]);
    }

    /**
     * PATCH /api/v1/admin/fees/{id}
     */
    public function update(Request $request, int $id)
    {
        $fee = Fee::findOrFail($id);

        $data = $request->validate([
            'code'        => ['sometimes', 'string', 'max:50', Rule::unique('fees', 'code')->ignore($id)],
            'name'        => 'sometimes|string|max:255',
            'amount'      => 'sometimes|numeric|min:0',
            'type'        => 'sometimes|in:one-time,recurring',
            'description' => 'sometimes|nullable|string',
        ]);

        $fee->update($data);

        return response()->json(['fee' => $this->formatFee($fee->fresh())]);
    }

    /**
     * DELETE /api/v1/admin/fees/{id}
     */
    public function destroy(Request $request, int $id)
    {
        $fee = Fee::findOrFail($id);
        $fee->delete();

        return response()->json(['message' => 'Fee deleted.']);
    }

    // ── User–Fee pivot (file upload) ───────────────────────────────────────

    /**
     * GET /api/v1/admin/fees/{id}/users
     * List users attached to a fee, including their pivot file path.
     */
    public function listUsers(Request $request, int $id)
    {
        $fee = Fee::findOrFail($id);

        $users = $fee->users()->get()->map(fn($u) => $this->formatUserPivot($u));

        return response()->json(['users' => $users]);
    }

    /**
     * POST /api/v1/admin/fees/{id}/users/{userId}
     * Attach a user to a fee and optionally upload a file.
     *
     * Body (multipart/form-data):
     *   file? – the uploaded file (receipt, proof of payment, etc.)
     */
    public function attachUser(Request $request, int $id, int $userId)
    {
        $fee  = Fee::findOrFail($id);
        $user = User::findOrFail($userId);

        if ($fee->users()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'User already attached to this fee.'], 409);
        }

        $request->validate([
            'file' => 'nullable|file|max:10240',   // 10 MB max
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')
                ->store("fees/{$id}/users/{$userId}", 'public');
        }

        $fee->users()->attach($userId, ['file_path' => $filePath]);

        return response()->json([
            'user' => $this->formatUserPivot($fee->users()->find($userId)),
        ], 201);
    }

    /**
     * POST /api/v1/admin/fees/{id}/users/{userId}/file
     * Replace the uploaded file on an existing fee–user association.
     *
     * Body (multipart/form-data):
     *   file – the new uploaded file (required)
     */
    public function updateUserFile(Request $request, int $id, int $userId)
    {
        $fee  = Fee::findOrFail($id);
        $user = $fee->users()->where('user_id', $userId)->firstOrFail();

        $request->validate([
            'file' => 'required|file|max:10240',   // 10 MB max
        ]);

        // Remove old file if one exists
        $oldPath = $user->pivot->file_path;
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $filePath = $request->file('file')
            ->store("fees/{$id}/users/{$userId}", 'public');

        $fee->users()->updateExistingPivot($userId, [
            'file_path' => $filePath,
            'payment_status' => 'pending',
            'verified_at' => null,
            'verified_by' => null,
        ]);

        return response()->json([
            'user' => $this->formatUserPivot($fee->users()->find($userId)),
        ]);
    }

    /**
     * PATCH /api/v1/admin/fees/{id}/users/{userId}/status
     * Update a fee-user payment status (pending, paid, rejected).
     */
    public function updateUserPaymentStatus(Request $request, int $id, int $userId)
    {
        $fee = Fee::findOrFail($id);
        $fee->users()->where('user_id', $userId)->firstOrFail();

        $data = $request->validate([
            'status' => 'required|in:pending,paid,rejected',
        ]);

        $pivotData = [
            'payment_status' => $data['status'],
        ];

        if ($data['status'] === 'paid') {
            $pivotData['verified_at'] = now();
            $pivotData['verified_by'] = $request->user()?->id;
        } else {
            $pivotData['verified_at'] = null;
            $pivotData['verified_by'] = null;
        }

        $fee->users()->updateExistingPivot($userId, $pivotData);

        return response()->json([
            'user' => $this->formatUserPivot($fee->users()->find($userId)),
        ]);
    }

    /**
     * DELETE /api/v1/admin/fees/{id}/users/{userId}
     * Detach a user from a fee (also deletes the stored file).
     */
    public function detachUser(Request $request, int $id, int $userId)
    {
        $fee  = Fee::findOrFail($id);
        $user = $fee->users()->where('user_id', $userId)->firstOrFail();

        // Remove file from storage
        $filePath = $user->pivot->file_path;
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        $fee->users()->detach($userId);

        return response()->json(['message' => 'User detached from fee.']);
    }

    // ── Formatters ─────────────────────────────────────────────────────────

    private function formatFee(Fee $fee): array
    {
        return [
            'id'          => $fee->id,
            'estateId'    => $fee->estate_id,
            'code'        => $fee->code,
            'name'        => $fee->name,
            'amount'      => (float) $fee->amount,
            'type'        => $fee->type,
            'description' => $fee->description,
            'createdAt'   => $fee->created_at->toIso8601String(),
            'updatedAt'   => $fee->updated_at->toIso8601String(),
        ];
    }

    private function formatUserPivot(User $user): array
    {
        return [
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'filePath' => $user->pivot->file_path,
            'fileUrl'  => $user->pivot->file_path
                ? asset('storage/' . $user->pivot->file_path)
                : null,
            'paymentStatus' => $user->pivot->payment_status,
            'verifiedAt' => $user->pivot->verified_at,
            'verifiedBy' => $user->pivot->verified_by,
            'attachedAt' => $user->pivot->created_at?->toIso8601String(),
        ];
    }
}
