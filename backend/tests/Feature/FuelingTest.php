<?php

namespace Tests\Feature;

use App\Models\FuelTank;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FuelingTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_quantity_above_tank_balance(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_MOTORISTA]);
        FuelTank::query()->create([
            'name' => 'Tanque Principal',
            'capacity_liters' => 20000,
            'current_liters' => 200,
        ]);
        $truck = Truck::query()->create([
            'plate' => 'ABC-1234',
            'name' => 'FH 01',
            'model' => 'Volvo FH',
            'fuel_type' => 'Diesel S10',
            'tank_capacity' => 1200,
            'current_liters' => 100,
            'current_km' => 1000,
            'sector' => 'Logística',
            'status' => 'ativo',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/fuelings', [
            'truck_id' => $truck->id,
            'quantity' => 500,
        ]);

        $response->assertStatus(422);
        $this->assertEquals(200, FuelTank::query()->first()->current_liters);
    }

    public function test_registers_fueling_in_a_single_transaction(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        FuelTank::query()->create([
            'name' => 'Tanque Principal',
            'capacity_liters' => 20000,
            'current_liters' => 8500,
        ]);
        $truck = Truck::query()->create([
            'plate' => 'ABC-1234',
            'name' => 'FH 01',
            'model' => 'Volvo FH',
            'fuel_type' => 'Diesel S10',
            'tank_capacity' => 1200,
            'current_liters' => 250,
            'current_km' => 1000,
            'sector' => 'Logística',
            'status' => 'ativo',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/fuelings', [
            'truck_id' => $truck->id,
            'quantity' => 300,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.quantity', 300);
        $this->assertEquals(8200, (float) FuelTank::query()->first()->current_liters);
        $this->assertEquals(550, (float) $truck->fresh()->current_liters);
    }
}
