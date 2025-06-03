<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\InteractionTypeModel;

class InteractionCategoriesModel extends Model
{
    protected $table            = 'interaction_categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    //Functions to retrieve interaction categories
    public function getInteractionCategories()
    {
        return $this->findAll();
    }
    public function getInteractionCategoryById($id, $interaction_id = null)
    {
        $interactionTypeModel = new InteractionTypeModel();

        $builder = $interactionTypeModel->select('interaction_categories.name as category_name, interaction_type.*')
            ->join('interaction_categories', 'interaction_type.category_id = interaction_categories.id', 'left')
            ->where('interaction_type.category_id', $id);

        if ($interaction_id !== null) {
            $builder->where('interaction_type.interaction_type_id', $interaction_id);
        }

        $interaction = $builder->findAll();

        return $interaction ?: null;
    }

}
