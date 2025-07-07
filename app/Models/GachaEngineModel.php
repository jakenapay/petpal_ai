<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;
use App\Libraries\GachaRngEngine;
use App\Models\GachaRngEngineModel;

class GachaEngineModel
{
    protected $db;
    protected $rng;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->rng = new GachaRngEngine();
    }

    public function executePull($playerId, $poolId, $pullCount, $totalCost)
    {
        // $this->db->transStart();

        try {
            $this->deductCoins($playerId, $totalCost);
            $config = $this->getPoolConfig($poolId);
            $results = $this->rng->execute_pulls($playerId, $poolId, $pullCount, $config);

            foreach ($results['results'] as &$r) {
                $this->addToObtainedGachaInventory($playerId, $r);
                $this->addToInventory($playerId, $r);
            }

            // $this->logTransaction($playerId, $poolId, $results, $totalCost);
            // $this->db->transComplete();

            $results['new_balance'] = $this->getBalance($playerId);
            return $results;

        } catch (Exception $e) {
            // $this->db->transRollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function getPoolConfig($poolId)
    {
        return [
            'items' => $this->getItems($poolId),
            'drop_rates' => $this->getDropRates($poolId)
        ];
    }

    private function getItems($poolId)
    {
        return $this->db->table('items i')
            ->select('i.item_id, i.pool_id, i.category_id AS item_type, i.rarity, i.drop_rate, i.is_featured, c.category_name, c.category_description, c.img_url')
            ->join('item_categories c', 'c.category_id = i.category_id', 'left')
            ->where('i.pool_id', $poolId)
            ->get()->getResultArray();
    }

    private function getDropRates($poolId)
    {
        // $rows = $this->db->table('items')
        //     ->select('rarity, SUM(drop_rate) as total_rate')
        //     ->where('pool_id', $poolId)
        //     ->groupBy('rarity')
        //     ->get()->getResultArray();

        $rows = $this->db->table('items')
            ->select('LOWER(rarity) as rarity, SUM(drop_rate) as total_rate')
            ->where('pool_id', $poolId)
            ->where('rarity IS NOT NULL')
            ->groupBy('LOWER(rarity)')
            ->get()->getResultArray();

        $rates = [];
        // foreach ($rows as $r) {
        //     $rates[$r['rarity']] = (float) $r['total_rate'];
        // }

        foreach ($rows as $r) {
            $rarity = ucfirst(strtolower($r['rarity'] ?? 'Unknown'));
            $rates[$rarity] = (float) $r['total_rate'];
        }

        log_message('error', 'DropRates Debug: ' . json_encode($rates));
        return $rates;
    }


    private function deductCoins($userId, $amount)
    {
        // Get current balance
        $user = $this->db->table('users')
            ->select('coins')
            ->where('user_id', $userId)
            ->get()
            ->getRow();

        if (!$user) {
            throw new Exception('User not found');
        }

        if ($user->coins < $amount) {
            throw new Exception('Insufficient balance');
        }

        // Proceed with deduction
        $this->db->table('users')
            ->where('user_id', $userId)
            ->set('coins', "coins - $amount", false)
            ->update();

        if ($this->db->affectedRows() === 0) {
            throw new Exception('Failed to deduct coins');
        }
    }


    private function addToObtainedGachaInventory($playerId, &$item)
    {
        $data = [
            'id' => $this->generateUUID(),
            'player_id' => $playerId,
            'item_id' => $item['item_id'],
            'item_type' => $item['item_type'],
            'rarity' => $item['rarity'],
            'properties' => json_encode($item['properties']),
            'is_equipped' => 0,
            'obtained_from' => 'gacha',
            'obtained_at' => date('Y-m-d H:i:s'),
            'estimated_value' => $item['estimated_value']
        ];

        $this->db->table('player_inventory')->insert($data);
    }

    private function addToInventory($playerId, &$item)
    {
        $data = [
            'user_id' => $playerId,
            'item_id' => $item['item_id'],
            'acquisition_type_id' => 4, // Gacha
            'quantity' => 1,
            'acquisition_date' => date('Y-m-d H:i:s'),
            'expiration_date' => null,
            'is_equipped' => 0
        ];

        $this->db->table('user_inventory')->insert($data);
    }

    private function logTransaction($playerId, $poolId, $results, $cost)
    {
        $count = count($results['results']);
        foreach ($results['results'] as $r) {
            $this->db->table('gacha_pulls')->insert([
                'id' => $this->generateUUID(),
                'player_id' => $playerId,
                'pool_id' => $poolId,
                'item_received' => $r['item_id'],
                'spent' => $cost / $count,
                'pull_timestamp' => date('Y-m-d H:i:s'),
                'pity_count' => $results['pity_status']['pulls_since_epic'],
                // 'entropy_hash' => $r['entropy_hash'],
                // 'properties' => json_encode($r['properties'])
            ]);
        }
    }

    private function getBalance($userId)
    {
        $row = $this->db->table('users')
            ->select('coins')
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        return $row ? (int) $row['coins'] : 0;
    }


    private function generateUUID()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }


    public function getPityData($playerId, $poolId)
    {
        return $this->db->table('player_pity_data')
            ->where(['player_id' => $playerId, 'pool_id' => $poolId])
            ->get()->getRowArray();
    }

    public function savePityData($playerId, $poolId, $data)
    {
        $payload = [
            'player_id' => $playerId,
            'pool_id' => $poolId,
            'pulls_since_epic' => $data['pulls_since_epic'],
            'pulls_since_legendary' => $data['pulls_since_legendary'],
            'total_pulls' => $data['total_pulls'],
            'last_epic_item' => $data['last_epic_item'],
            'last_legendary_item' => $data['last_legendary_item'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('player_pity_data')->replace($payload);
    }

    public function logAudit($data)
    {
        $this->db->table('gacha_audit_log')->insert([
            'id' => $this->generateUUID(),
            'player_id' => $data['player_id'],
            'pool_id' => $data['pool_id'],
            'item_id' => $data['item_id'],
            'rarity' => $data['rarity'],
            'entropy_hash' => $data['entropy_hash'],
            'created_at' => $data['timestamp'],
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
}
