<?php

namespace App\Http\Controllers;

use App\Models\Tender;
use App\Services\SealedBiddingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WitnessController extends Controller
{
    public function __construct(
        protected SealedBiddingService $biddingService
    ) {}

    /**
     * Pengesahan Saksi Digital bagi Pembukaan Peti Tender
     */
    public function affirm(Request $request, Tender $tender)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $validated = $request->validate([
            'witness_remarks' => 'nullable|string|max:1000',
        ]);

        try {
            $result = $this->biddingService->affirmWitnessOpening(
                $tender,
                $user,
                $validated['witness_remarks'] ?? null
            );

            return redirect()->back()->with('success', $result['message']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
