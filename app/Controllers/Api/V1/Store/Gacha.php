<?php

namespace App\Controllers\API\V1\Store;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\GachaTypeModel;
use App\Models\GachaPoolModel;
use App\Models\PullOptionsModel;
use App\Models\GachaPullModel;
use App\Models\ItemModel;
use App\Models\UserModel;
use App\Models\GachaItemModel;
use App\Models\InventoryModel;

class Gacha extends BaseController
{
    public function __construct() {
        date_default_timezone_set('Asia/Manila');
    }
    public function index()
    {


    }


    public function gachaTypes()
    {
        //auth check to be followed.
        $gachaTypeModel = new GachaTypeModel();
        $gachaTypes = $gachaTypeModel->getGachaTypes();
        return $this->response->setJSON([
            'message' => 'Successfully retrieved gacha types',
            'data' => $gachaTypes
        ]);
    }
    public function gachaPool($pool_id = null) {
        $gachaPoolModel = new GachaPoolModel();
        $gachaPool = $gachaPoolModel->getGachaPools($pool_id);

        if ($pool_id && !$gachaPool) {
            return $this->response->setStatusCode(404)->setJSON([
                'message' => 'Gacha pool not found',
                'data' => null
            ]);
        }

        return $this->response->setJSON([
            'message' => $pool_id ? 'Successfully retrieved gacha pool' : 'Successfully retrieved all gacha pools',
            'data' => $gachaPool
        ]);
    }

    public function pullOptions($pool_id = null) {
        //user auth
        $PullOptionsModel = new PullOptionsModel();
        $pullOptions = $PullOptionsModel->getPullOptions($pool_id);
        if(!$pullOptions){
            return $this->response->setStatusCode(404)->setJSON([
                'message' => 'Pull options not found',
                'data' => null
            ]);
        }
        return $this->response->setJSON([
            'message' => $pool_id ? 'Successfully retrieved pull options' : 'Successfully retrieved all pull options',
            'data' => $pullOptions
        ]);
    }
    private function generateUUIDv4()
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); // set version to 0100
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }


    public function gachaPull()
    {
        $user_id = 43; // Replace with authenticated user
        $data = $this->request->getJSON(true);
        $pool_option_id = $data['pool_option_id'] ?? null;
        $db = \Config\Database::connect();
        $db->transStart();

        if (!$pool_option_id) {
            return $this->response->setJSON(['error' => 'Pool option id is required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $pullOptionsModel = new PullOptionsModel();
        $pullOptions = $pullOptionsModel->getSpecificPullOption($pool_option_id);
        if (!$pullOptions) {
            return $this->response->setJSON(['error' => 'Pull option not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        $pool_id = $pullOptions['pool_id'];
        $pull_count = $pullOptions['pulls_count'];
        $cost = $pullOptions['cost'];
        $currency = $pullOptions['currency'];

        $userModel = new UserModel();
        $user = $userModel->getUserBalance($user_id);
        if (!$user) {
            return $this->response->setJSON(['error' => 'User not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        $userCoins = $user['coins'];
        $userDiamonds = $user['diamonds'];
        $totalCost = $cost;

        if (($currency === 'coins' && $userCoins < $totalCost) || 
            ($currency === 'diamonds' && $userDiamonds < $totalCost)) {
            return $this->response->setJSON([
                'error' => 'Insufficient currency',
                'message' => 'You need ' . $totalCost . ' ' . $currency . ' to pull ' . $pull_count . ' times'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $poolModel = new GachaPoolModel();
        $pool = $poolModel->getGachaPools($pool_id);
        if (!$pool) {
            return $this->response->setJSON(['error' => 'Gacha pool not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        $pity_limit = $pool['pity_limit'];
        $gachaPullModel = new GachaPullModel();
        $userPullCount = $gachaPullModel->getPityCount($user_id, $pool_id);


        $GachaItemModel = new GachaItemModel();
        $poolItems = $GachaItemModel->getPoolItems($pool_id);
        if (!$poolItems) {
            return $this->response->setJSON(['error' => 'Pool items not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        // Perform pulls with accurate pity tracking
        $pullResults = [];
        for ($i = 0; $i < $pull_count; $i++) {
            if ($userPullCount + 1 >= $pity_limit) {
                $item = $this->getGuaranteedLegendary($poolItems);
                $userPullCount = 0; // Reset pity after legendary
            } else {
                $item = $this->getRandomDrop($poolItems);
                $userPullCount++;
            }
            $pullResults[] = [
                'item' => $item,
                'pity_count' => $userPullCount
            ];
        }

        // Insert each pull result
        $insertedItems = [];
        foreach ($pullResults as $pull) {
            $data = [
                // 'id' => $this->generateUUIDv4(),
                'player_id' => $user_id,
                'pool_id' => $pool_id,
                'item_received' => json_encode($pull['item']),
                'spent' => $pullOptions['cost'],
                'pull_timestamp' => date('Y-m-d H:i:s'),
                'pity_count' => $pull['pity_count']
            ];

            $insert = $gachaPullModel->playerPull($data);
            if ($insert === false) {
                $db->transRollback();
                log_message('error', 'Failed to insert individual pull: ' . json_encode($data));
                return $this->response->setJSON(['error' => 'Failed to insert pull history'])
                    ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }

            $insertedItems[] = $pull['item'];
        }
        $totalSpent = $pullOptions['cost'];

        // Deduct currency
        if ($currency === 'coins') {
            $userModel->updateCoins($user_id, $userCoins - $totalSpent);
            if ($userModel === false || $userModel === null || !$userModel) {
                $db->transRollback();
                log_message('error', 'Failed to update user coins');
                return $this->response->setJSON(['error' => 'Failed to update user coins'])
                    ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $userModel->updateDiamonds($user_id, $userDiamonds - $totalSpent);
            if ($userModel === false || $userModel === null || !$userModel) {
                $db->transRollback();
                log_message('error', 'Failed to update user diamonds');
                return $this->response->setJSON(['error' => 'Failed to update user diamonds'])
                    ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // TODO: Add items to inventory
        // $this->addItemsToInventory($user_id, $insertedItems);
        
        // Commit the transaction

        $db->transCommit();

        return $this->response->setJSON([
            'pity_count' => $userPullCount,
            'remaining_pulls' => $pity_limit - $userPullCount,
            'pull_results' => $insertedItems,
            'currency_spent' => $totalSpent,
        ]);
    }



    private function getGuaranteedLegendary(array $items)
    {
        foreach ($items as $item) {
            if ($item['rarity'] === 'Legendary' && $item['is_featured'] == 1) {
                return $item;
            }
        }
        // Fallback if none found
        return $this->getRandomDrop($items);
    }

    private function getRandomDrop(array $items)
    {
        $weighted = [];
        foreach ($items as $item) {
            $rate = floatval($item['drop_rate']);
            for ($i = 0; $i < $rate * 1000; $i++) {
                $weighted[] = $item;
            }
        }
        if (empty($weighted)) return null;
        return $weighted[array_rand($weighted)];
    }







}
