<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PetAdoption extends BaseController
{
    public function index()
    {
        //
    }

    public function showAllSpecies()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('Species');
        $builder->orderBy('species_id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function showAllDogBreeds() {
        $db = \Config\Database::connect();
        $builder = $db->table('DogBreeds');
        $builder->orderBy('breed_id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function showAllDogPersonalities(){
        $db = \Config\Database::connect();
        $builder = $db->table('DogPersonalities');
        $builder->orderBy('personality_id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function showAllCatBreeds() {
        $db = \Config\Database::connect();
        $builder = $db->table('CatBreeds');
        $builder->orderBy('breed_id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function showAllCatPersonalities(){
        $db = \Config\Database::connect();
        $builder = $db->table('CatPersonalities');
        $builder->orderBy('personality_id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }
    
    public function generateName() {
        $faker = \Faker\Factory::create();
        $name = $faker->firstName();
        return $this->response->setJSON(['name' => $name])->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function getEyeColors() {
        $db = \Config\Database::connect();
        $builder = $db->table('EyeColor');
        $builder->orderBy('id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function getFurColors() {
        $db = \Config\Database::connect();
        $builder = $db->table('FurColor');
        $builder->orderBy('id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function getCatPatterns() {
        $db = \Config\Database::connect();
        $builder = $db->table('CatPatterns');
        $builder->orderBy('pattern_id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function getDogPatterns() {
        $db = \Config\Database::connect();
        $builder = $db->table('DogPatterns');
        $builder->orderBy('pattern_id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
