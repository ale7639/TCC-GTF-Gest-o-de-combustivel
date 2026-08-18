<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fueling;
use App\Models\Truck;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function consumption(Request $request): JsonResponse
    {
        [$start, $end] = $this->period($request);

        $query = Fueling::query()
            ->with(['truck', 'user'])
            ->whereBetween('created_at', [$start, $end]);

        if ($request->filled('truck_id')) {
            $query->where('truck_id', $request->integer('truck_id'));
        }

        $fuelings = $query->orderBy('created_at')->get();
        $total = (float) $fuelings->sum('quantity_liters');
        $days = max(1, $start->diffInDays($end) + 1);

        $byDay = $fuelings
            ->groupBy(fn (Fueling $item) => $item->created_at->timezone(config('app.timezone'))->toDateString())
            ->map(fn ($group, $day) => [
                'date' => $day,
                'liters' => (float) $group->sum('quantity_liters'),
                'count' => $group->count(),
            ])
            ->values();

        $ranking = $fuelings
            ->groupBy('truck_id')
            ->map(function ($group) {
                /** @var Fueling $first */
                $first = $group->first();

                return [
                    'truck_id' => $first->truck_id,
                    'plate' => $first->truck?->plate,
                    'model' => $first->truck?->model,
                    'liters' => (float) $group->sum('quantity_liters'),
                ];
            })
            ->sortByDesc('liters')
            ->values();

        return response()->json([
            'period' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'label' => $start->translatedFormat('F/Y'),
            ],
            'total_liters' => $total,
            'daily_average' => round($total / $days, 1),
            'count' => $fuelings->count(),
            'by_day' => $byDay,
            'ranking' => $ranking,
            'empty' => $fuelings->isEmpty(),
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        [$start, $end] = $this->period($request);

        $query = Fueling::query()
            ->with(['truck', 'user'])
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at');

        if ($request->filled('truck_id')) {
            $query->where('truck_id', $request->integer('truck_id'));
        }

        $filename = 'gfc-consumo-'.$start->format('Y-m').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Data', 'Placa', 'Modelo', 'Litros', 'Responsável', 'KM'], ';');

            $query->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i'),
                        $row->truck?->plate,
                        $row->truck?->model,
                        number_format((float) $row->quantity_liters, 2, ',', '.'),
                        $row->user?->name,
                        $row->km_at_fueling,
                    ], ';');
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function period(Request $request): array
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();

        return [$start, $end];
    }
}
