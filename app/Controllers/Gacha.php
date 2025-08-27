<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ItemModel;

class Gacha extends BaseController
{
    public function index()
    {
        
    }

    public function list() {

        helper('url');
        $itemModel = new ItemModel();

        $data['items'] = $itemModel->where('category_id', 6)->findAll();

        return view('gacha/list', $data);
    }
}
