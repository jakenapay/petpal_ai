<?php

namespace App\Controllers\API\V1\Store;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\GachaTypeModel;
use App\Models\GachaPoolModel;
class Gacha extends BaseController
{
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




}
