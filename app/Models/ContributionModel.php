<?php

namespace App\Models;

use CodeIgniter\Model;

class ContributionModel extends Model
{
    protected $table = 'contributions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'title', 'description', 'amount', 'category', 'status', 'created_by', 'cost_price', 'profit_amount', 'contribution_code'
    ];
    protected $useTimestamps = true; // automatically fill created_at, updated_at
    
    /**
     * Get profit analytics
     * Calculate total profit and average profit margin
     */
    public function getProfitAnalytics()
    {
        $db = \Config\Database::connect();
        
        $result = $db->table('contributions')
            ->select('SUM((amount - cost_price)) as total_profit, AVG(((amount - cost_price) / amount) * 100) as avg_profit_margin')
            ->where('status', 'active')
            ->get()
            ->getRow();
        
        return [
            'total_profit' => floatval($result->total_profit ?? 0),
            'avg_profit_margin' => floatval($result->avg_profit_margin ?? 0)
        ];
    }
    
    /**
     * Get top profitable contributions
     * @param int $limit Number of top contributions to return
     */
    public function getTopProfitable($limit = 10)
    {
        $contributions = $this->select('id, title, amount, category, status, cost_price')
            ->where('status', 'active')
            ->orderBy('amount', 'DESC')
            ->limit($limit)
            ->findAll();
        
        // Calculate profit and profit margin for each contribution
        foreach ($contributions as &$contribution) {
            $amount = floatval($contribution['amount'] ?? 0);
            $cost = floatval($contribution['cost_price'] ?? 0);
            $profit = $amount - $cost;
            
            $contribution['profit_amount'] = $profit;
            $contribution['profit_margin'] = $amount > 0 ? round(($profit / $amount) * 100, 1) : 0;
        }
        
        // Sort by profit amount
        usort($contributions, function($a, $b) {
            return $b['profit_amount'] <=> $a['profit_amount'];
        });
        
        return $contributions;
    }
}
