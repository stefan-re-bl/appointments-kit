<?php

namespace App\Http\Controllers;

use App\Models\SessionType;
use App\Models\Therapist;
use App\Services\SlotGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    public function __construct(
        private SlotGenerationService $slotGenerationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'therapist_id' => 'required|exists:therapists,id',
            'date' => 'required|date_format:Y-m-d',
            'session_type_id' => 'required|exists:session_types,id',
        ]);

        $therapist = Therapist::findOrFail($validated['therapist_id']);
        $sessionType = SessionType::findOrFail($validated['session_type_id']);

        $slots = $this->slotGenerationService->generate(
            therapist: $therapist,
            date: $validated['date'],
            durationMinutes: $sessionType->duration_minutes
        );

        //dd($slots);

        return response()->json($slots);
    }
}