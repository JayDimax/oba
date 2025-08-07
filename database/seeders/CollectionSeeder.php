<?php

namespace Database\Seeders;

use App\Models\Bet;
use App\Models\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Get sample agent
        $agentId = \App\Models\User::where('role', 'agent')->first()->id;

        // Get 3 random stub_ids that belong to this agent
        $stubIds = Bet::where('agent_id', $agentId)
                      ->select('stub_id')
                      ->distinct()
                      ->limit(3)
                      ->pluck('stub_id');

        // Ensure we have stubs
        if ($stubIds->isEmpty()) {
            $this->command->warn('No stubs found for seeding.');
            return;
        }

        // Calculate sample amounts
        $bets = Bet::whereIn('stub_id', $stubIds)->get();
        $gross = $bets->sum('amount');
        $payouts = $bets->where('is_winner', true)->sum('winnings');
        $deductions = 50; // fixed sample
        $netRemit = $gross - $payouts + $deductions;

        // Create Collection
        $collection = Collection::create([
            'agent_id' => $agentId,
            'collection_date' => now()->toDateString(),
            'gross' => $gross,
            'payouts' => $payouts,
            'deductions' => $deductions,
            'net_remit' => $netRemit,
            'proof_file' => null,
            'is_remitted' => true,
            'status' => 'approved',
            'verified_by' => 1,
            'verified_at' => now(),
        ]);

        // Attach stub IDs to pivot
        foreach ($stubIds as $stubId) {
            DB::table('collection_stub')->insert([
                'collection_id' => $collection->id,
                'stub_id' => $stubId,
            ]);
        }

        $this->command->info("Sample collection created with stubs: " . $stubIds->implode(', '));
    }
}
