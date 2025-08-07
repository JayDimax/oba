<?php
namespace App\Services;

use App\Models\Bet;
use App\Models\AgentBalance;
use App\Models\Collection;
use App\Models\Remittance;

class ReportService
{
    protected $cashierId;

    public function __construct($cashierId)
    {
        $this->cashierId = $cashierId;
    }

    // Apply common filters
    protected function applyFilters($query, array $filters)
    {
        if (!empty($filters['filter_date'])) {
            $query->whereDate('created_at', $filters['filter_date']);
        }
        if (!empty($filters['agent_id'])) {
            $query->where('agent_id', $filters['agent_id']);
        }
        return $query;
    }

    public function getBetsReport(array $filters = [])
    {
        $query = Bet::whereHas('agent', function ($q) {
            $q->where('cashier_id', $this->cashierId);
        });

        $query = $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    public function getBalancesReport(array $filters = [])
    {
        $query = AgentBalance::where('cashier_id', $this->cashierId);

        $query = $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    public function getCollectionsReport(array $filters = [])
    {
        $query = Collection::where('cashier_id', $this->cashierId);

        $query = $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    public function getRemittanceReport(array $filters = [])
    {
        $query = Remittance::where('cashier_id', $this->cashierId);

        $query = $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }
}
