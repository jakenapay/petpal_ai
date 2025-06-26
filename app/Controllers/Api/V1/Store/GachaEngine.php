<?php

namespace App\Controllers\Api\V1\Store;

use App\Models\GachaTypeModel;
use App\Models\GachaPoolModel;
use App\Models\PullOptionsModel;
use App\Models\GachaPullModel;
use App\Models\ItemModel;
use App\Models\UserModel;
use App\Models\GachaItemModel;
use App\Models\InventoryModel;

use App\Models\GachaEngineModel;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class GachaEngine extends BaseController
{
    public function index()
    {
        //
    }

    public function gachaTypes()
    {
        $user_id = authorizationCheck($this->request);
        if (!$user_id) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        //auth check to be followed.
        $gachaTypeModel = new GachaTypeModel();
        $gachaTypes = $gachaTypeModel->getGachaTypes();
        return $this->response->setJSON([
            'message' => 'Successfully retrieved gacha types',
            'data' => $gachaTypes
        ]);
    }

    public function gachaPool($pool_id = null)
    {
        $user_id = authorizationCheck($this->request);
        if (!$user_id) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
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

    public function pullOptions($pool_id = null)
    {
        //user auth
        $PullOptionsModel = new PullOptionsModel();
        $pullOptions = $PullOptionsModel->getPullOptions($pool_id);
        if (!$pullOptions) {
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

    public function getItems($pool_id = null)
    {
        // User authentication
        $user_id = authorizationCheck($this->request);
        if (!$user_id) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $data = $this->request->getJSON(true);
        $pool_id = $data['pool_id'] ?? null;

        // Validate pool_id
        if (!$pool_id) {
            return $this->response->setJSON(['error' => 'Pool ID is required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('gacha_items');
        $builder->select('id, pool_id, item_type, item_id, rarity, drop_rate, is_featured');
        $builder->where('pool_id', $pool_id);

        // Execute the query and get the result
        $items = $builder->get()->getResultArray();

        // Check if items were found
        if (empty($items)) {
            return $this->response->setStatusCode(404)->setJSON([
                'message' => 'No items found for this pool',
                'total' => 0,
                'data' => null,
            ]);
        }

        // Return the items along with the total count
        return $this->response->setJSON([
            'message' => 'Successfully retrieved items for the pool',
            'total' => count($items),
            'data' => $items,
        ]);
    }


    public function pullGacha()
    {
        // User authentication check
        $user_id = authorizationCheck($this->request);
        // $user_id = 43;
        if (!$user_id) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // Get the pool option ID from the request
        $data = $this->request->getJSON(true);
        $pool_option_id = $data['pool_option_id'] ?? null;

        // Validate the pool option ID
        if (!$pool_option_id) {
            return $this->response->setJSON(['error' => 'Invalid pool option ID'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        // Check if the pool option exists
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

        // test return for specific pull option
        // return $this->response->setJSON(['message' => 'pull options', 'data' => $pullOptions]);

        // Initialize the Gacha RNG Engine
        $gachaEngineModel = new GachaEngineModel();
        $pullResult = $gachaEngineModel->executePull($user_id, $pool_id, $pull_count, $cost);
        return $this->response->setJSON($pullResult);
    }


}
