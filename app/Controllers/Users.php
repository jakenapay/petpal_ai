<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Models\ItemModel;
use App\Models\InventoryModel;

class Users extends BaseController
{
    public function index()
    {
        helper('url');
        $userModel = new UserModel();


        $userData = $userModel->findAll();
        if (!$userData) {
            return redirect()->to('/main')->with('error', 'No users found');
        }

        $data = ['users' => $userData];
        // print_r($data);
        return view('user/list', $data);
    }

    public function profile()
    {
        $userModel = new UserModel();
        $userId = session()->get('user_id');
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to('/login')->with('error', 'User not found');
        }

        return view('user/profile', ['user' => $user]);
    }

    public function editProfile()
    {
        // Only allow POST requests
        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to('/profile');
        }

        $userModel = new UserModel();
        $userId = $this->request->getPost('user_id');

        // Gather form data
        $data = [
            'email' => $this->request->getPost('email'),
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'status' => $this->request->getPost('status')
            // 'role' is read-only; no update needed
        ];

        // If password was provided, hash and update it
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Handle profile image upload if available
        $file = $this->request->getFile('profile_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Check for a current image and delete it if exists
            $currentUser = $userModel->find($userId);
            if (!empty($currentUser) && !empty($currentUser['profile_image'])) {
                $currentImagePath = FCPATH . 'assets/images/users/' . basename($currentUser['profile_image']);
                if (file_exists($currentImagePath)) {
                    unlink($currentImagePath);
                }
            }

            // Generate a new file name and move the file into the users images directory
            $newName = $file->getRandomName();
            $destinationPath = FCPATH . 'assets/images/users/';
            $file->move($destinationPath, $newName);
            // Update the profile image path
            $data['profile_image'] = base_url('assets/images/users/' . $newName);
        }

        // Attempt to update the user data in the database
        if ($userModel->update($userId, $data)) {
            return redirect()->to('profile')->with('success', 'Profile updated successfully');
        } else {
            return redirect()->to('profile')->with('error', 'Unable to update profile');
        }
    }

    public function add()
    {
        $userModel = new UserModel();

        $data = [
            'username' => trim($this->request->getPost('username')),
            'email' => trim($this->request->getPost('email')),
            'password' => trim($this->request->getPost('password')),
            'first_name' => trim($this->request->getPost('firstname')),
            'last_name' => trim($this->request->getPost('lastname')),
            'mbti' => trim($this->request->getPost('mbti')),
            'role' => trim($this->request->getPost('role')),
        ];

        // Adding profile image soon
        // $data['profileImage'] = $this->request->getFile('profileImage');

        // Validations
        if (
            !$this->validate([
                'username' => [
                    'rules' => 'required|min_length[3]|max_length[20]|is_unique[users.username]',
                    'errors' => [
                        'required' => 'Username is required.',
                        'min_length' => 'Username must be at least 3 characters.',
                        'max_length' => 'Username cannot exceed 20 characters.',
                        'is_unique' => 'This username is already taken.'
                    ]
                ],
                'email' => [
                    'rules' => 'required|valid_email|is_unique[users.email]',
                    'errors' => [
                        'required' => 'Email is required.',
                        'valid_email' => 'Please enter a valid email address.',
                        'is_unique' => 'This email is already registered.'
                    ]
                ],
                'password' => [
                    'rules' => 'required|min_length[6]',
                    'errors' => [
                        'required' => 'Password is required.',
                        'min_length' => 'Password must be at least 6 characters.'
                    ]
                ],
                'firstname' => [
                    'rules' => 'required|alpha_space|min_length[2]|max_length[50]',
                    'errors' => [
                        'required' => 'First name is required.',
                        'alpha_space' => 'First name can only contain letters and spaces.',
                        'min_length' => 'First name must be at least 2 characters.',
                        'max_length' => 'First name cannot exceed 50 characters.'
                    ]
                ],
                'lastname' => [
                    'rules' => 'required|alpha_space|min_length[2]|max_length[50]',
                    'errors' => [
                        'required' => 'Last name is required.',
                        'alpha_space' => 'Last name can only contain letters and spaces.',
                        'min_length' => 'Last name must be at least 2 characters.',
                        'max_length' => 'Last name cannot exceed 50 characters.'
                    ]
                ],
                'role' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Role is required.'
                    ]
                ]
            ])
        ) {
            $errorMessages = $this->validator->getErrors();
            $errorString = implode('<br>', $errorMessages);
            redirect()->back()->withInput()->with('error', $errorString);
        }

        // Hash the password before saving
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        // Save the user data
        if ($userModel->insert($data)) {
            return redirect()->to('users/list')->with('success', 'User added successfully');
        } else {
            $dbError = $userModel->errors() ?: ['db' => $userModel->db->error()['message']];
            return redirect()->back()->withInput()->with('error', 'Unable to add user: ' . implode(', ', $dbError));
        }
    }

    public function delete()
    {

    }

    public function edit($userId)
    {
        $userModel = new UserModel();

        // Fetch user data
        $userData = $userModel->find($userId);
        if (!$userData) {
            return redirect()->to('user/list')->with('error', 'User not found.');
        }
        // print_r($userData);

        return view('user/edit', ['user' => $userData]);
    }

    /**
     * @api {post} /users/update/:userId Update user information
     * @apiName UpdateUser
     * @apiGroup Users
     *
     * @apiParam {Number} userId User's unique ID.
     *
     * @apiSuccess {Object} user Updated user object.
     * @apiSuccess {Number} user.id User's unique ID.
     * @apiSuccess {String} user.name User's name.
     * @apiSuccess {String} user.email User's email address.
     *
     * @apiError UserNotFound The user with the specified ID was not found.
     * @apiError ValidationError One or more fields failed validation.
     *
     * Updates the information of an existing user identified by userId.
     *
     * @param int $userId The unique identifier of the user to update.
     * @return void
     */
    public function update($userId)
    {
        $userModel = new UserModel();

        $data = [
            'username' => trim($this->request->getPost('username')),
            'email' => trim($this->request->getPost('email')),
            'first_name' => trim($this->request->getPost('firstname')),
            'last_name' => trim($this->request->getPost('lastname')),
            'mbti' => trim($this->request->getPost('mbti')),
            'role' => trim($this->request->getPost('role')),
            'reward_point' => trim($this->request->getPost('rewardpoint')),
            'status' => trim($this->request->getPost('status')),
            'number_of_pet' => trim($this->request->getPost('number_of_pet')),
            'experience' => trim($this->request->getPost('experience')),
            'user_grade' => trim($this->request->getPost('user_grade')),
            'coins' => trim($this->request->getPost('coins')),
            'diamonds' => trim($this->request->getPost('diamonds')),
            'total_real_money_spent' => trim($this->request->getPost('total_real_money_spent')),
        ];

        // Validations
        if (
            !$this->validate([
                'username' => [
                    'rules' => 'required|min_length[3]|max_length[20]|is_unique[users.username, user_id, ' . $userId . ']',
                    'errors' => [
                        'required' => 'Username is required.',
                        'min_length' => 'Username must be at least 3 characters.',
                        'max_length' => 'Username cannot exceed 20 characters.',
                        'is_unique' => 'This username is already taken.'
                    ]
                ],
                'email' => [
                    'rules' => 'required|valid_email|is_unique[users.email, user_id, ' . $userId . ']',
                    'errors' => [
                        'required' => 'Email is required.',
                        'valid_email' => 'Please enter a valid email address.',
                        'is_unique' => 'This email is already registered.'
                    ]
                ],
                'firstname' => [
                    'rules' => 'required|alpha_space|min_length[2]|max_length[50]',
                    'errors' => [
                        'required' => 'First name is required.',
                        'alpha_space' => 'First name can only contain letters and spaces.',
                        'min_length' => 'First name must be at least 2 characters.',
                        'max_length' => 'First name cannot exceed 50 characters.'
                    ]
                ],
                'lastname' => [
                    'rules' => 'required|alpha_space|min_length[2]|max_length[50]',
                    'errors' => [
                        'required' => 'Last name is required.',
                        'alpha_space' => 'Last name can only contain letters and spaces.',
                        'min_length' => 'Last name must be at least 2 characters.',
                        'max_length' => 'Last name cannot exceed 50 characters.'
                    ]
                ],
                'role' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Role is required.'
                    ]
                ],
                'rewardpoint' => [
                    'rules' => 'required|integer',
                    'errors' => [
                        'required' => 'Reward point is required.',
                        'integer' => 'Reward point must be an integer.'
                    ]
                ],
                'status' => [
                    'rules' => 'required|in_list[active,inactive,suspended]',
                    'errors' => [
                        'required' => 'Status is required.',
                        'in_list' => 'Status must be either active or inactive.'
                    ]
                ],
                'number_of_pet' => [
                    'rules' => 'required|integer',
                    'errors' => [
                        'required' => 'Number of pets is required.',
                        'integer' => 'Number of pets must be an integer.'
                    ]
                ],
                'experience' => [
                    'rules' => 'required|integer',
                    'errors' => [
                        'required' => 'Experience is required.',
                        'integer' => 'Experience must be an integer.'
                    ]
                ],
                'user_grade' => [
                    'rules' => 'required|integer',
                    'errors' => [
                        'required' => 'User grade is required.',
                        'integer' => 'User grade must be an integer.'
                    ]
                ],
                'coins' => [
                    'rules' => 'required|integer',
                    'errors' => [
                        'required' => 'Coins are required.',
                        'integer' => 'Coins must be an integer.'
                    ]
                ],
                'diamonds' => [
                    'rules' => 'required|integer',
                    'errors' => [
                        'required' => 'Diamonds are required.',
                        'integer' => 'Diamonds must be an integer.'
                    ]
                ],
                'total_real_money_spent' => [
                    'rules' => 'required|decimal',
                    'errors' => [
                        'required' => 'Total real money spent is required.',
                        'decimal' => 'Total real money spent must be a decimal value.'
                    ]
                ]
            ])
        ) {
            $errorMessages = $this->validator->getErrors();
            $errorString = implode('<br>', $errorMessages);
            redirect()->back()->withInput()->with('error', $errorString);
        }

        // If password was provided, hash and update it
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        } else {
            // If no password is provided, remove it from the data array
            unset($data['password']);
        }

        // Attempt to update the user data in the database
        if ($userModel->update($userId, $data)) {
            session()->setFlashdata('success', 'User updated successfully');
            $this->response->redirect(site_url('users/list'));
            return;
        } else {
            $dbError = $userModel->errors() ?: ['db' => $userModel->db->error()['message']];
            // session()->setFlashdata('error', 'Unable to update user: ' . implode(', ', $dbError));
            session()->setFlashdata('error', 'Unable to update user. Please try again.');
            $this->response->redirect(previous_url());
            return;
        }
    }

    public function inventory($userId)
    {
        $userModel = new UserModel();
        $itemModel = new ItemModel();
        $inventoryModel = new InventoryModel();
        $user = $userModel->find($userId);

        $fieldsToRemove = ['password', 'role', 'coins', 'diamonds', 'user_grade', 'experience', 'reward_point', 'birth_date', 'mbti', 'email', 'otp', 'otp_expires', 'verification_code', 'verification_expiration_date', 'total_real_money_spent', 'created_at', 'updated_at', 'last_login', 'logout_time'];

        foreach ($fieldsToRemove as $field) {
            unset($user[$field]);
        }

        if (!$user) {
            return redirect()->to('users/list')->with('error', 'User not found.');
        }

        // Get acquisitions table
        $db = \Config\Database::connect();
        $getAcquisitions = $db->table('acquisition_types')->get()->getResultArray();

        // Get user inventory
        $inventory = $inventoryModel->getUserInventory($userId);
        $data = [
            'user' => $user,
            'inventory' => $inventory,
            'allItems' => $itemModel->findAll(),
            'allAcquisitions' => $getAcquisitions
        ];

        // print_r($user);
        return view('user/inventory', $data);
    }

    public function deleteInventoryItem($itemId)
    {
        $inventoryModel = new InventoryModel();

        if (empty($itemId)) {
            return redirect()->back()->with('error', 'Invalid item ID.');
        }

        // Attempt to delete the item
        $db = \Config\Database::connect();

        // start transactions
        $db->transStart();
        if ($inventoryModel->delete($itemId)) {
            $db->transComplete();
            return redirect()->back()->with('success', 'Item deleted successfully from inventory.');
        } else {
            $db->transRollback();
            return redirect()->back()->with('error', 'Unable to delete item from inventory.');
        }
    }

    public function addInventoryItem()
    {
        $inventoryModel = new InventoryModel();

        $data = [
            'user_id' => trim($this->request->getPost('user_id')),
            'item_id' => trim($this->request->getPost('item_id')),
            'acquisition_type_id' => trim($this->request->getPost('acquisition')),
            'quantity' => trim($this->request->getPost('quantity')),
            'acquisition_date' => trim($this->request->getPost('acquisition_date')),
            'expiration_date' => !empty(trim($this->request->getPost('expiration_date'))) ? trim($this->request->getPost('expiration_date')) : null,
        ];

        // Validate the data
        if (
            !$this->validate([
                'user_id' => [
                    'rules' => 'required|integer',
                    'errors' => [
                        'required' => 'User ID is required.',
                        'integer' => 'User ID must be a valid number.'
                    ]
                ],
                'item_id' => [
                    'rules' => 'required|integer',
                    'errors' => [
                        'required' => 'Please select an item.',
                        'integer' => 'Invalid item selected.'
                    ]
                ],
                'acquisition' => [
                    'rules' => 'required|integer',
                    'errors' => [
                        'required' => 'Please select how the item was acquired.',
                        'integer' => 'Invalid acquisition type selected.'
                    ]
                ],
                'quantity' => [
                    'rules' => 'required|integer|greater_than[0]',
                    'errors' => [
                        'required' => 'Quantity is required.',
                        'integer' => 'Quantity must be a whole number.',
                        'greater_than' => 'Quantity must be at least 1.'
                    ]
                ],
                'acquisition_date' => [
                    'rules' => 'required|valid_date',
                    'errors' => [
                        'required' => 'Acquisition date is required.',
                        'valid_date' => 'Please enter a valid acquisition date.'
                    ]
                ],
            ])
        ) {
            $errorMessages = $this->validator->getErrors();
            $errorString = implode('<br>', $errorMessages);
            return redirect()->back()->withInput()->with('error', $errorString);
        }

        // Insert the inventory item with transaction
        $db = \Config\Database::connect();
        $db->transStart();

        if ($inventoryModel->insert($data)) {
            $db->transComplete();
            return redirect()->back()->with('success', 'Item added to inventory successfully.');
        } else {
            $db->transRollback();
            return redirect()->back()->with('error', 'Unable to add item to inventory.');
        }
    }

    public function editInventoryItem($itemId)
    {
        $db = \Config\Database::connect();
        $inventoryModel = new InventoryModel();
        $userModel = new UserModel();

        if (empty($itemId)) {
            return redirect()->back()->with('error', 'Item not found.');
        }

        $inventoryItemData = $inventoryModel->select('user_inventory.*, items.item_name')
            ->join('items', 'user_inventory.item_id = items.item_id', 'left')
            ->where('user_inventory.id', $itemId)
            ->first();

        if (!$inventoryItemData['user_id']) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $user = $userModel->select('username')->find($inventoryItemData['user_id']);
        $getAcquisitions = $db->table('acquisition_types')->get()->getResultArray();

        // TESTING
        // echo '<pre>';
        // print_r($inventoryItemData);
        // echo '</pre>';

        if (!$inventoryItemData) {
            return redirect()->back()->with('error', 'Item not found in inventory.');
        }

        $data = [
            'inventoryItemData' => $inventoryItemData,
            'allAcquisitions' => $getAcquisitions,
            'user' => $user
        ];

        return view('user/editInventory', $data);
    }

    public function updateInventoryItem($itemId)
    {
        $inventoryModel = new InventoryModel();
        $db = \Config\Database::connect();

        if (empty($itemId)) {
            return redirect()->back()->with('error', 'Item not found.');
        }

        // Gather form data
        $data = [
            'user_id' => trim($this->request->getPost('user_id')),
            'item_id' => trim($this->request->getPost('item_id')),
            'acquisition_type_id' => trim($this->request->getPost('acquisition_type_id')),
            'quantity' => trim($this->request->getPost('quantity')),
            'acquisition_date' => trim($this->request->getPost('acquisition_date')),
            'expiration_date' => !empty(trim($this->request->getPost('expiration_date'))) ? trim($this->request->getPost('expiration_date')) : null,
        ];

        // Validate the data
        if (
            !$this->validate([
                'user_id' => [
                    'rules' => 'required|integer',
                    'errors' => [
                        'required' => 'User ID is required.',
                        'integer' => 'User ID must be a valid number.'
                    ]
                ],
                'item_id' => [
                    'rules' => 'required|integer',
                    'errors' => [
                        'required' => 'Please select an item.',
                        'integer' => 'Invalid item selected.'
                    ]
                ],
                'acquisition_type_id' => [
                    'rules' => 'required|integer',
                    'errors' => [
                        'required' => 'Please select how the item was acquired.',
                        'integer' => 'Invalid acquisition type selected.'
                    ]
                ],
                'quantity' => [
                    'rules' => 'required|integer|greater_than_equal_to[0]',
                    'errors' => [
                        'required' => 'Quantity is required.',
                        'integer' => 'Quantity must be a whole number.',
                        'greater_than_equal_to' => 'Quantity cannot be negative.'
                    ]
                ],
                'acquisition_date' => [
                    'rules' => 'required|valid_date',
                    'errors' => [
                        'required' => 'Acquisition date is required.',
                        'valid_date' => 'Please enter a valid acquisition date.'
                    ]
                ],
            ])
        ) {
            $errorMessages = $this->validator->getErrors();
            $errorString = implode('<br>', $errorMessages);
            return redirect()->back()->withInput()->with('error', $errorString);
        }

        $db->transStart();
        if ($inventoryModel->update($itemId, $data)) {
            $db->transComplete();
            return redirect()->back()->with('success', 'Inventory item updated successfully.');
        } else {
            $db->transRollback();
            $dbError = $inventoryModel->errors() ?: ['db' => $inventoryModel->db->error()['message']];
            log_message('error', 'Inventory update error: ' . implode(', ', $dbError));
            return redirect()->back()->withInput()->with('error', 'Unable to update inventory item. Please try again.');
        }
    }

}
