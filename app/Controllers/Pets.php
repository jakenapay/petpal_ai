<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PetModel;

class Pets extends BaseController
{
    public function index()
    {
        //
    }

    public function list()
    {
        helper('url');
        $petModel = new PetModel();

        $petData = $petModel->select('pets.*, CONCAT(users.first_name, " ", users.last_name) AS owner_name, pet_life_stages.stage_name AS life_stage')
            ->join('users', 'users.user_id = pets.user_id', 'left')
            ->join('pet_life_stages', 'pet_life_stages.stage_id = pets.life_stage_id', 'left')
            ->findAll();
        if (!$petData) {
            return redirect()->to('pet/list')->with('error', 'No pets found');
        }

        $data = ['pets' => $petData];
        // print_r($data);
        return view('pet/list', $data);
    }

    public function edit($petId)
    {
        helper('url');
        $db = \Config\Database::connect();

        $petModel = new PetModel();
        $petData = $petModel->select('pets.*, CONCAT(users.first_name, " ", users.last_name) AS owner_name, pet_life_stages.stage_name AS life_stage')
            ->join('users', 'users.user_id = pets.user_id', 'left')
            ->join('pet_life_stages', 'pet_life_stages.stage_id = pets.life_stage_id', 'left')
            ->where('pet_id', $petId)
            ->first();

        if (!$petData) {
            return redirect()->to('pets/list')->with('error', 'Pet not found');
        }

        // Test
        // print_r($petData);


        $data = [
            'pet' => $petData,
            'personalities' => [
                'cat' => $db->table('catpersonalities')->get()->getResultArray(),
                'dog' => $db->table('dogpersonalities')->get()->getResultArray()
            ],
            'life_stages' => $db->table('pet_life_stages')->get()->getResultArray()
        ];

        return view('pet/edit', $data);
    }

    public function update($petId)
    {
        $petModel = new PetModel();

        if (empty($petId)) {
            return redirect()->to('pets/list')->with('error', 'Pet not found');
        }

        $data = [
            'name' => trim($this->request->getPost('name')),
            'life_stage_id' => trim($this->request->getPost('life_stage')),
            'species' => trim($this->request->getPost('species')),
            'breed' => trim($this->request->getPost('breed')),
            'gender' => trim($this->request->getPost('gender')),
            'personality' => trim($this->request->getPost('personality')),
        ];

        // Validations
        if (
            !$this->validate(
                [
                    'name' => [
                        'rules' => 'required|min_length[2]|max_length[50]',
                        'errors' => [
                            'required' => 'Pet name is required.',
                            'min_length' => 'Pet name must be at least 2 characters long.',
                            'max_length' => 'Pet name cannot exceed 50 characters.'
                        ]
                    ],
                    'life_stage' => [
                        'rules' => 'required|integer',
                        'errors' => [
                            'required' => 'Please select a life stage.',
                            'integer' => 'Please select a valid life stage.'
                        ]
                    ],
                    'species' => [
                        'rules' => 'required|min_length[2]|max_length[50]',
                        'errors' => [
                            'required' => 'Species is required.',
                            'min_length' => 'Species must be at least 2 characters long.',
                            'max_length' => 'Species cannot exceed 50 characters.'
                        ]
                    ],
                    'breed' => [
                        'rules' => 'required|min_length[2]|max_length[50]',
                        'errors' => [
                            'required' => 'Breed is required.',
                            'min_length' => 'Breed must be at least 2 characters long.',
                            'max_length' => 'Breed cannot exceed 50 characters.'
                        ]
                    ],
                    'gender' => [
                        'rules' => 'required|in_list[0,1]',
                        'errors' => [
                            'required' => 'Please select a gender.',
                            'in_list' => 'Please select a valid gender option.'
                        ]
                    ],
                    'personality' => [
                        'rules' => 'required|min_length[2]|max_length[50]',
                        'errors' => [
                            'required' => 'Personality is required.',
                            'min_length' => 'Personality must be at least 2 characters long.',
                            'max_length' => 'Personality cannot exceed 50 characters.'
                        ]
                    ],
                ]
            )
        ) {
            $errorMessages = $this->validator->getErrors();
            $errorString = implode('<br>', $errorMessages);
            redirect()->back()->withInput()->with('error', $errorString);
        }

        // DB Pet Update
        if ($petModel->update($petId, $data)) {
            session()->setFlashData('success', 'Pet updated successfully');
            $this->response->redirect(site_url('pets/list'));
            return;
        } else {
            $error = $petModel->errors() ?: ['db' => $petModel->db->error()['message']];

            // Debugger:
            // session()->setFlashdata('error', 'Database Error: ' . implode(', ', $error));

            session()->setFlashData('error', 'Pet update failed.');
            $this->response->redirect(previous_url());
            return;
        }


    }
}
