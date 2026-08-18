<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fueling;
use App\Models\FuelTank;
use App\Models\Truck;
use App\Services\ChecklistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private ChecklistService $checklist)
    {
    }

    public function show(Request $request): JsonResponse
    {
        $tank = FuelTank::query()->firstOrFail();
        $trucks = Truck::query()->where('status', 'ativo')->with(['driver', 'verifier'])->get();

        $ready = 0;
        $pending = 0;

        $summaries = $trucks->map(function (Truck $truck) use (&$ready, &$pending) {
            $check = $this->checklist->forTruck($truck);
            if ($check['result'] === 'apto') {
                $ready++;
            } else {
                $pending++;
            }

            return [
                'id' => $truck->id,
                'plate' => $truck->plate,
                'model' => $truck->model,
                'status' => $check['result'],
                'label' => $check['label'],
            ];
        });

        $todayFuelings = Fueling::query()->whereDate('created_at', today())->get();

        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'role' => $request->user()->role,
            ],
            'tank' => [
                'name' => $tank->name,
                'capacity' => (float) $tank->capacity_liters,
                'current' => (float) $tank->current_liters,
                'percent' => $tank->percent(),
                'critical' => $tank->isCritical(),
            ],
            'fleet' => [
                'ready' => $ready,
                'pending' => $pending,
                'total' => $trucks->count(),
                'items' => $summaries,
            ],
            'today' => [
                'fuelings' => $todayFuelings->count(),
                'liters' => (float) $todayFuelings->sum('quantity_liters'),
            ],
        ]);
    }
}
