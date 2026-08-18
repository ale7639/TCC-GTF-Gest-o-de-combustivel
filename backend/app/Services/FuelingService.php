<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Fueling;
use App\Models\FuelTank;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FuelingService
{
    public function register(Truck $truck, User $user, float $quantity, ?int $km, string $ip): Fueling
    {
        return DB::transaction(function () use ($truck, $user, $quantity, $km, $ip) {
            $tank = FuelTank::query()->lockForUpdate()->firstOrFail();
            $truck = Truck::query()->lockForUpdate()->findOrFail($truck->id);

            if ($truck->status !== 'ativo') {
                throw ValidationException::withMessages([
                    'truck_id' => 'Somente caminhões com status Ativo podem ser abastecidos.',
                ]);
            }

            $remaining = $truck->remainingCapacity();
            $max = min((float) $tank->current_liters, $remaining);

            if ($quantity <= 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Informe uma quantidade positiva em litros.',
                ]);
            }

            if ($quantity > $max) {
                $reason = $remaining < (float) $tank->current_liters
                    ? 'capacidade restante'
                    : 'saldo do tanque';

                throw ValidationException::withMessages([
                    'quantity' => 'Máximo permitido: '.number_format($max, 0, ',', '.').' L ('.$reason.').',
                    'max' => $max,
                ]);
            }

            $tankBefore = (float) $tank->current_liters;
            $truckBefore = (float) $truck->current_liters;

            $tank->current_liters = $tankBefore - $quantity;
            $tank->save();

            $truck->current_liters = $truckBefore + $quantity;
            if ($km !== null) {
                if ($km < $truck->current_km) {
                    throw ValidationException::withMessages([
                        'current_km' => 'A quilometragem não pode ser menor que a atual.',
                    ]);
                }
                $truck->current_km = $km;
            }
            $truck->save();

            $fueling = Fueling::query()->create([
                'truck_id' => $truck->id,
                'user_id' => $user->id,
                'quantity_liters' => $quantity,
                'tank_before' => $tankBefore,
                'tank_after' => (float) $tank->current_liters,
                'truck_before' => $truckBefore,
                'truck_after' => (float) $truck->current_liters,
                'km_at_fueling' => $truck->current_km,
                'ip_address' => $ip,
            ]);

            AuditLog::record($user, 'abastecimento.criar', Fueling::class, $fueling->id, [
                'placa' => $truck->plate,
                'litros' => $quantity,
            ], $ip);

            return $fueling->load(['truck', 'user']);
        });
    }
}
