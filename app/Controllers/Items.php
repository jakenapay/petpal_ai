<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AffinityModel;
use App\Models\ItemAccessoriesModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ItemModel;
use App\Models\GachaPoolModel;
use App\Models\ItemCategoriesModel;
use App\Models\ItemRarityModel;
use App\Models\ItemTiersModel;
use App\Models\ItemSubCategoriesModel;
use App\Models\PetBreedModel;
use App\Models\SpecieModel;

use Exception;

class Items extends BaseController
{
    public function index()
    {
        //
    }

    public function itemAdd()
    {
        helper('url');

        $gachaPoolModel = new GachaPoolModel();
        $ItemCategoriesModel = new ItemCategoriesModel();
        $ItemRarityModel = new ItemRarityModel();
        $ItemTiersModel = new ItemTiersModel();
        $ItemSubCategoriesModel = new ItemSubCategoriesModel();
        $petBreedModel = new PetBreedModel();
        $specieModel = new SpecieModel();

        $data = [
            'poolData' => $gachaPoolModel->findAll(),
            'itemCategoriesData' => $ItemCategoriesModel->findAll(),
            'ItemRarityData' => $ItemRarityModel->findAll(),
            'ItemTiersData' => $ItemTiersModel->findAll(),
            'ItemSubCategoriesData' => $ItemSubCategoriesModel->findAll(),
            'petBreedData' => $petBreedModel->getBreeds(),
            'specieData' => $specieModel->findAll(),
        ];
        // echo "<pre>";
        // print_r($data['petBreedData']);
        // echo "</pre>";        return view('templates/sidebar') .
            view('items/items', $data);
    }

    public function itemDelete()
    {
        helper('url');
        return view('templates/sidebar') .
            view('items/delete');
    }

    public function addItem()
    {
        helper('url');
        $itemModel = new ItemModel();
        $validationRules = [
            'category_id' => [
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => 'Category ID is required.',
                    'is_natural_no_zero' => 'Category ID must be a positive number.',
                ]
            ],
            'item_name' => [
                'rules' => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required' => 'Item name is required.',
                    'min_length' => 'Item name must be at least 3 characters.',
                ]
            ],
            'description' => ['rules' => 'permit_empty|string'],
            'image_url' => [
                'rules' => 'permit_empty|valid_url',
                'errors' => [
                    'valid_url' => 'Image URL must be a valid URL.',
                ]
            ],
            'base_price' => [
                'rules' => 'required|decimal',
                'errors' => [
                    'required' => 'Base price is required.',
                    'decimal' => 'Base price must be a decimal number.',
                ]
            ],
            'rarity' => ['rules' => 'required|is_numeric'],
            'is_tradable' => [
                'rules' => 'required|in_list[0,1]',
                'errors' => [
                    'in_list' => 'Invalid value for is_tradable.',
                ]
            ],
            'is_buyable' => ['rules' => 'required|in_list[0,1]'],
            'is_consumable' => ['rules' => 'required|in_list[0,1]'],
            'is_stackable' => ['rules' => 'required|in_list[0,1]'],
            'duration' => ['rules' => 'permit_empty|integer'],
            'korean_name' => ['rules' => 'permit_empty|string'],
            'tier_id' => ['rules' => 'permit_empty|integer'],
            'real_price' => ['rules' => 'permit_empty|decimal'],
            'discount_percentage' => ['rules' => 'permit_empty|integer'],
            'is_featured' => ['rules' => 'required|in_list[0,1]'],
            'is_on_sale' => ['rules' => 'required|in_list[0,1]'],
            'quantity_available' => ['rules' => 'permit_empty|integer'],
            'release_date' => [
                'rules' => 'permit_empty|valid_date[Y-m-d\TH:i]',
                'errors' => [
                    'valid_date' => 'Release date must be in datetime-local format.',
                ]
            ],
            'end_date' => ['rules' => 'permit_empty|valid_date[Y-m-d\TH:i]'],
            'thumbnail_url' => ['rules' => 'permit_empty|valid_url'],
            'detail_images' => ['rules' => 'permit_empty|string'],
            'preview_3d_model' => ['rules' => 'permit_empty|valid_url'],
            'attributes' => ['rules' => 'permit_empty|string'],
            'tags' => ['rules' => 'permit_empty|string'],
            'currency_type' => [
                'rules' => 'required|in_list[coins,diamonds]',
                'errors' => [
                    'in_list' => 'Currency type must be either coins or diamonds.',
                ]
            ],
            'hunger_level' => ['rules' => 'permit_empty|decimal'],
            'energy_level' => ['rules' => 'permit_empty|decimal'],
            'hygiene_level' => ['rules' => 'permit_empty|decimal'],
            'health_level' => ['rules' => 'permit_empty|decimal'],
            'happiness_level' => ['rules' => 'permit_empty|decimal'],
            'stress_level' => ['rules' => 'permit_empty|decimal'],
            'affinity' => ['rules' => 'permit_empty|integer'],
            'experience' => ['rules' => 'permit_empty|integer'],
            'pool_id' => ['rules' => 'permit_empty|string'],
            'drop_rate' => ['rules' => 'permit_empty|decimal'],
            // Item accessories
            'subCategory' => ['rules' => 'permit_empty|integer'],
            'specie' => ['rules' => 'permit_empty|integer'],
            'breed' => ['rules' => 'permit_empty|integer'],
            'iconUrl' => ['rules' => 'permit_empty|valid_url'],
            'addressableUrl' => ['rules' => 'permit_empty|valid_url'],
            'rgbColor' => ['rules' => 'permit_empty'],
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->validator->getErrors()));
        }

        foreach (['detail_images', 'attributes'] as $field) {
            $json = trim($this->request->getPost($field));
            $data[$field] = ($json === '' || $json === null) ? null : (json_decode($json) !== null ? $json : null);
            if ($json && $data[$field] === null) {
                return redirect()->back()->withInput()->with('error', 'Invalid JSON in ' . $field);
            }
        }

        $data = [
            'category_id' => $this->request->getPost('category_id') ?: null,
            'item_name' => $this->request->getPost('item_name') ?: null,
            'description' => $this->request->getPost('description') ?: null,
            'image_url' => $this->request->getPost('image_url') ?: null,
            'base_price' => $this->request->getPost('base_price') ?: null,
            'rarity' => $this->request->getPost('rarity') ?: null,
            'is_tradable' => $this->request->getPost('is_tradable') ?: null,
            'is_buyable' => $this->request->getPost('is_buyable') ?: null,
            'is_consumable' => $this->request->getPost('is_consumable') ?: null,
            'is_stackable' => $this->request->getPost('is_stackable') ?: null,
            'duration' => $this->request->getPost('duration') ?: null,
            'effect_id' => $this->request->getPost('effect_id') ?: null,
            'korean_name' => $this->request->getPost('korean_name') ?: null,
            'tier_id' => $this->request->getPost('tier_id') ?: null,
            'real_price' => $this->request->getPost('real_price') ?: null,
            'discount_percentage' => $this->request->getPost('discount_percentage') ?: null,
            'is_featured' => $this->request->getPost('is_featured') ?: null,
            'is_on_sale' => $this->request->getPost('is_on_sale') ?: null,
            'quantity_available' => $this->request->getPost('quantity_available') ?: null,
            'release_date' => $this->request->getPost('release_date') ?: null,
            'end_date' => $this->request->getPost('end_date') ?: null,
            'thumbnail_url' => $this->request->getPost('thumbnail_url') ?: null,
            'detail_images' => $data['detail_images'] ?? null,
            'preview_3d_model' => $this->request->getPost('preview_3d_model') ?: null,
            'attributes' => $data['attributes'] ?? null,
            'tags' => $this->request->getPost('tags') ?: null,
            'final_price' => $this->request->getPost('final_price') ?: null,
            'currency_type' => $this->request->getPost('currency_type') ?: null,
            'hunger_level' => $this->request->getPost('hunger_level') ?: 0,
            'energy_level' => $this->request->getPost('energy_level') ?: 0,
            'hygiene_level' => $this->request->getPost('hygiene_level') ?: 0,
            'health_level' => $this->request->getPost('health_level') ?: 0,
            'happiness_level' => $this->request->getPost('happiness_level') ?: 0,
            'stress_level' => $this->request->getPost('stress_level') ?: 0,
            'affinity' => $this->request->getPost('affinity') ?: 5,
            'experience' => $this->request->getPost('experience') ?: 0,
            'pool_id' => $this->request->getPost('pool_id') ?: null,
            'drop_rate' => $this->request->getPost('drop_rate') ?: null,
        ];

        // print_r($data);
        $dataItemAccessories = [
            'iconURL' => $this->request->getPost('iconUrl') ?: null,
            'AddressableURL' => $this->request->getPost('addressableUrl') ?: null,
            'subcategory_id' => $this->request->getPost('subCategory') ?: null,
            'breed_id' => $this->request->getPost('breed') ?: null,
            'species_id' => $this->request->getPost('specie') ?: null,
            'RGBColor' => $this->request->getPost('rgbColor') ?: null,
        ];

        print_r($dataItemAccessories);

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Insert the item first
            $insertedId = $itemModel->insert($data);

            if (!$insertedId) {
                $db->transRollback();
                return redirect()->to('item/add')->withInput()->with('error', 'Item not added.');
            }

            // If accessories data is provided, insert it
            if (
                !empty($dataItemAccessories['subcategory_id']) ||
                !empty($dataItemAccessories['breed_id']) ||
                !empty($dataItemAccessories['species_id'])
            ) {
                $ItemAccessoriesModel = new ItemAccessoriesModel();

                // Attach the item ID to the accessories data (if needed)
                $dataItemAccessories['item_id'] = $insertedId;

                if (!empty($dataItemAccessories['RGBColor'])) {
                    $hex = $dataItemAccessories['RGBColor'];

                    if (!preg_match('/^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $hex)) {
                        $db->transRollback();
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Invalid RGB color format. Must be 3 or 6-digit hex code.');
                    }

                    $hex = ltrim($hex, '#');
                    if (strlen($hex) === 3) {
                        $hex = $hex[0] . $hex[0] .
                            $hex[1] . $hex[1] .
                            $hex[2] . $hex[2];
                    }

                    // Format: (R,G,B)
                    $r = hexdec(substr($hex, 0, 2));
                    $g = hexdec(substr($hex, 2, 2));
                    $b = hexdec(substr($hex, 4, 2));

                    $dataItemAccessories['RGBColor'] = "($r,$g,$b)";
                }

                $insertedItemAccessories = $ItemAccessoriesModel->insert($dataItemAccessories);

                if (!$insertedItemAccessories) {
                    $db->transRollback();
                    return redirect()->to('item/add')->withInput()->with('error', 'Item accessories not added.');
                }
            }

            // Commit if everything succeeded
            $db->transCommit();
            return redirect()->to('item/add')
                ->with('success', 'Item added successfully.');

        } catch (Exception $e) {
            // 4. Roll back on any exception
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteItem()
    {
        helper('url');
        $itemModel = new ItemModel();
        $itemId = $this->request->getPost('item_id');



        try {
            // Check if item exists
            $item = $itemModel->find($itemId);
            $isDeleted = $item['is_deleted'] ?? 0;

            if (!$item || $item === 0) {
                return redirect()->back()->with('error', 'Item not found.');
            }

            if ($isDeleted) {
                return redirect()->back()->with('error', 'Item is already deleted.');
            }

            // Soft delete (manual flag)
            $updated = $itemModel->update($itemId, ['is_deleted' => 1]);

            if ($updated) {
                return redirect()->to('item/delete')->with('success', 'Item deleted successfully.');
            } else {
                return redirect()->to('item/delete')->with('error', 'Failed to delete the item.');
            }
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
