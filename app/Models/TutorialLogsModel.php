<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\TutorialsModel;

class TutorialLogsModel extends Model
{
    protected $table            = 'tutorial_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'tutorial_id',
        'is_done',
        'date_completed'
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

    public function getUserTutorialLog($userId)
    {
        $tutorialModel = new TutorialsModel();
        //get the name of the tutorial that corresponds to the tutorial_id
        $tutorial = $tutorialModel->findAll();
        $tutorialNames = [];
        foreach ($tutorial as $t) {
            $tutorialNames[$t['id']] = $t['name'];
        }
        //get the tutorial logs of the user
        $logs = $this->where('user_id', $userId)->findAll();
        $tutorialLogs = [];
        foreach ($logs as $log) {
            $tutorialLogs[] = [
                'tutorial_id'   => $log['tutorial_id'],
                'tutorial_name' => $tutorialNames[$log['tutorial_id']] ?? 'Unknown',
                'is_done'       => $log['is_done'],
                'date_completed'  => $log['date_completed']
            ];
        }
        return $tutorialLogs;
    }

    public function completeUserTutorial($userId, $tutorialId)
    {
        return $this->insert([
            'user_id'      => $userId,
            'tutorial_id'  => $tutorialId,
            'is_done'      => 1,
            'date_completed' => date('Y-m-d H:i:s')
        ]);
    }
}
