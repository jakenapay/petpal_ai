<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\MotionTagsModel;
class SavedMotionsModel extends Model
{
    protected $table            = 'saved_motions';
    protected $primaryKey       = 'motion_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'pet_id',
        'motion_name',
        'original_prompt',
        'motion_tags',
        'motion_category',
        'difficulty_level',
        'bvh_data',
        'bvh_frames_count',
        'is_favorite',
        'user_rating',
        'quality_score',
        'is_public',
        'is_shareable',
        'share_count',
        'created_at',
        'updated_at'
    ];

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
    protected $validationRules      = [

    ];
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

    public function getSavedMotionsByUserId($user_id, $motionTags = null, $pet_id = null)
    {
        $motionTagsModel = new MotionTagsModel();

        // Start base query
        $builder = $this->builder();
        $builder->where('user_id', $user_id);

        if (!empty($pet_id)) {
            $builder->where('pet_id', $pet_id);
        }

        if (!empty($motionTags) && is_array($motionTags)) {
            // Group OR LIKE for all tagIds
            $builder->groupStart();
            foreach ($motionTags as $tagId) {
                $builder->orLike('motion_tags', '"' . $tagId . '"'); // match inside JSON string
            }
            $builder->groupEnd();
        }

        $query = $builder->get();
        $savedMotions = $query->getResultArray();

        // Append motion_tag_names
        foreach ($savedMotions as &$motion) {
            $tagIds = json_decode($motion['motion_tags'], true);
            $tagNames = [];

            if (is_array($tagIds)) {
                foreach ($tagIds as $tagId) {
                    $tag = $motionTagsModel->getMotionTagsById($tagId);
                    if ($tag) {
                        $tagNames[] = $tag['name'];
                    }
                }
            }

            $motion['motion_tag_names'] = $tagNames;
            unset($motion['motion_tags']);
        }

        return $savedMotions;
    }

    public function handleSaveMotions($user_id, $data)
    {
        log_message('error', 'Insert payload: ' . json_encode($data));

        if ($this->insert($data)) {
            return $this->getInsertID();
        }

        log_message('error', 'Insert failed: ' . json_encode($this->errors()));
        return false;
    }




}
