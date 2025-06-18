<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\AdoptionModel;

class PetAdoption extends BaseController
{
    public function index()
    {
        //
    }

    public function showAllSpecies()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('species');
        $builder->orderBy('species_id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function showAllDogBreeds() {
        $db = \Config\Database::connect();
        $builder = $db->table('dogbreeds');
        $builder->orderBy('breed_id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function showAllDogPersonalities(){
        $db = \Config\Database::connect();
        $builder = $db->table('dogpersonalities');
        $builder->orderBy('personality_id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function showCatTexturebyBreed($breed_id){
        $adoptionModel = new AdoptionModel();
        $breed_id = (int) $breed_id;

        $result = $adoptionModel->getCatTexture($breed_id);

        if (!$result || empty($result)) {
            return $this->response->setJSON([
                'error' => 'No cat texture found for this breed'
            ])->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        return $this->response->setJSON([
            'breed_id' => $breed_id,
            'textures' => $result
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function showDogTexturebyBreed($breed_id){
        $adoptionModel = new AdoptionModel();
        $breed_id = (int) $breed_id;

        $result = $adoptionModel->getDogTexture($breed_id);

        if (!$result || empty($result)) {
            return $this->response->setJSON([
                'error' => 'No dog texture found for this breed'
            ])->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        return $this->response->setJSON([
            'breed_id' => $breed_id,
            'textures' => $result
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }



    public function showAllCatBreeds() {
        $db = \Config\Database::connect();
        $builder = $db->table('catbreeds');
        $builder->orderBy('breed_id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function showAllCatPersonalities(){
        $db = \Config\Database::connect();
        $builder = $db->table('catpersonalities');
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
        $builder = $db->table('eyecolor');
        $builder->orderBy('id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function getFurColors() {
        $db = \Config\Database::connect();
        $builder = $db->table('furcolor');
        $builder->orderBy('id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function getCatPatterns() {
        $db = \Config\Database::connect();
        $builder = $db->table('catpatterns');
        $builder->orderBy('pattern_id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function getDogPatterns() {
        $db = \Config\Database::connect();
        $builder = $db->table('dogpatterns');
        $builder->orderBy('pattern_id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function getDogColors() {
        $db = \Config\Database::connect();
        $builder = $db->table('dogcolors');
        $builder->orderBy('color_id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function getCatColors() {
        $db = \Config\Database::connect();
        $builder = $db->table('catcolors');
        $builder->orderBy('color_id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function getDogEyeColors() {
        $db = \Config\Database::connect();
        $builder = $db->table('dogeyecolors');
        $builder->orderBy('color_id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function getCatEyeColors() {
        $db = \Config\Database::connect();
        $builder = $db->table('cateyecolors');
        $builder->orderBy('color_id', 'ASC');
        $query = $builder->get();
        $result = $query->getResult();

        return $this->response->setJSON($result)->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
