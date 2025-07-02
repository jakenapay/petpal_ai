<?php

namespace App\Libraries;

use App\Models\UserModel;
use App\Models\GachaItemModel;
use App\Models\PlayerInventoryModel;
use App\Models\PityDataModel;
use Exception;

/**
 * Advanced Gacha RNG Engine for PetPal AI
 * 
 * Features:
 * - Cryptographically secure random generation
 * - Weighted probability system with pity mechanics
 * - Item rarity and type-specific logic
 * - Anti-manipulation and audit trail
 * - Performance optimized for high-volume pulls
 * 
 * @author NTEKSYSTEMS Inc.
 * @version 2.0
 */
class GachaRngEngine
{

    // RNG Configuration
    private $rng_seed_length = 32;
    private $max_entropy_sources = 5;

    // Pity System Configuration
    private $soft_pity_threshold = 70;
    private $hard_pity_threshold = 90;
    private $legendary_pity_threshold = 200;

    // Rate Boost Configuration
    private $soft_pity_multiplier = 1.5;
    private $hard_pity_multiplier = 3.0;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Execute multiple gacha pulls with advanced RNG
     * 
     * @param string $player_id Player identifier
     * @param string $pool_id Gacha pool identifier
     * @param int $pull_count Number of pulls to execute
     * @param array $pool_config Pool configuration and items
     * @return array Pull results with detailed breakdown
     */
    public function execute_pulls($player_id, $pool_id, $pull_count, $pool_config)
    {
        $results = [];
        $rarity_summary = [];
        $total_value = 0;

        // Get player's current pity counters
        $pity_data = $this->get_player_pity_data($player_id, $pool_id);

        // Pre-calculate entropy for all pulls (security measure)
        $entropy_batch = $this->generate_entropy_batch($pull_count);

        for ($i = 0; $i < $pull_count; $i++) {
            $pull_result = $this->execute_single_pull(
                $player_id,
                $pool_id,
                $pool_config,
                $pity_data,
                $entropy_batch[$i],
                $i + 1
            );

            $results[] = $pull_result;

            // Update rarity summary
            $rarity = $pull_result['rarity'];
            $rarity_summary[$rarity] = ($rarity_summary[$rarity] ?? 0) + 1;

            // Update pity counters
            $this->update_pity_counters($pity_data, $rarity);

            // Calculate total value
            $total_value += $pull_result['estimated_value'];

            // Log pull for audit trail
            $this->log_pull_event($player_id, $pool_id, $pull_result, $entropy_batch[$i]);
        }

        // Save updated pity data
        $this->save_player_pity_data($player_id, $pool_id, $pity_data);

        return [
            'success' => true,
            'results' => $results,
            'rarity_summary' => $rarity_summary,
            'total_pulls' => $pull_count,
            'total_estimated_value' => $total_value,
            'pity_status' => $pity_data,
            'entropy_verification' => $this->generate_entropy_hash($entropy_batch)
        ];
    }

    private function get_player_pity_data($playerId, $poolId): array
    {
        $db = \Config\Database::connect();

        $query = $db->table('player_pity_data')
            ->select('pulls_since_epic, pulls_since_legendary, total_pulls, last_epic_item, last_legendary_item')
            ->where('player_id', $playerId)
            ->where('pool_id', $poolId)
            ->get();

        $pityData = $query->getRowArray();

        return $pityData ?: [
            'pulls_since_epic' => 0,
            'pulls_since_legendary' => 0,
            'total_pulls' => 0,
            'last_epic_item' => null,
            'last_legendary_item' => null
        ];
    }

    /**
     * Generate cryptographically secure entropy batch
     * 
     * @param int $count Number of entropy values needed
     * @return array Array of entropy strings
     */
    private function generate_entropy_batch($count)
    {
        $entropy_batch = [];

        for ($i = 0; $i < $count; $i++) {
            $entropy_sources = [
                random_bytes(16),                                    // Primary cryptographic randomness
                hash('sha256', microtime(true) . $i),               // Time-based entropy
                hash('sha256', memory_get_usage() . $i),            // Memory-based entropy
                hash('sha256', getmypid() . $i),                    // Process-based entropy
                hash('sha256', $_SERVER['REQUEST_TIME_FLOAT'] . $i) // Request-based entropy
            ];

            // Combine all entropy sources
            $combined_entropy = hash('sha512', implode('', $entropy_sources));
            $entropy_batch[] = $combined_entropy;
        }

        return $entropy_batch;
    }

    /**
     * Execute a single gacha pull with weighted probability
     * 
     * @param string $player_id Player identifier
     * @param string $pool_id Pool identifier
     * @param array $pool_config Pool configuration
     * @param array $pity_data Current pity counters
     * @param string $entropy Cryptographic entropy for this pull
     * @param int $pull_sequence Sequence number in batch
     * @return array Single pull result
     */
    private function execute_single_pull($player_id, $pool_id, $pool_config, &$pity_data, $entropy, $pull_sequence)
    {
        // Generate primary random value (0.0 to 1.0)
        $primary_random = $this->entropy_to_float($entropy, 0);

        // Apply pity system adjustments
        $adjusted_rates = $this->apply_pity_system($pool_config['drop_rates'], $pity_data);

        // Determine rarity based on adjusted rates
        $selected_rarity = $this->select_rarity($primary_random, $adjusted_rates);

        // Get items of selected rarity
        $rarity_items = $this->get_items_by_rarity($pool_config['items'], $selected_rarity);

        // Apply item-specific selection logic
        $selected_item = $this->select_item_with_logic($rarity_items, $entropy, $pull_sequence, $player_id);

        // Generate additional item properties
        $item_properties = $this->generate_item_properties($selected_item, $entropy);

        return [
            'item_id' => $selected_item['item_id'],
            // 'name' => $selected_item['name'],
            'rarity' => $selected_rarity,
            'item_type' => $selected_item['item_type'],
            // 'image_url' => $selected_item['image_url'],
            'is_featured' => $selected_item['is_featured'] ?? false,
            'is_new' => $this->is_new_item_for_player($player_id, $selected_item['item_id']),
            'properties' => $item_properties,
            'estimated_value' => $this->calculate_item_value($selected_item, $item_properties),
            'pull_sequence' => $pull_sequence,
            'entropy_hash' => hash('sha256', $entropy)
        ];
    }

    /**
     * Convert entropy string to float value (0.0 to 1.0)
     * 
     * @param string $entropy Entropy string
     * @param int $offset Offset for multiple random values from same entropy
     * @return float Random value between 0.0 and 1.0
     */
    private function entropy_to_float($entropy, $offset = 0)
    {
        $hash_segment = substr($entropy, $offset * 8, 8);
        $hex_value = hexdec($hash_segment);
        return $hex_value / 0xFFFFFFFF; // Normalize to 0.0-1.0
    }

    /**
     * Apply pity system to modify drop rates
     * 
     * @param array $base_rates Base drop rates by rarity
     * @param array $pity_data Current pity counters
     * @return array Adjusted drop rates
     */
    // private function apply_pity_system($base_rates, $pity_data)
    // {
    //     $adjusted_rates = $base_rates;

    //     // Soft pity: Increase Epic/Legendary rates
    //     if ($pity_data['pulls_since_epic'] >= $this->soft_pity_threshold) {
    //         $multiplier = min(
    //             $this->soft_pity_multiplier,
    //             1 + (($pity_data['pulls_since_epic'] - $this->soft_pity_threshold) * 0.1)
    //         );

    //         $adjusted_rates['Epic'] *= $multiplier;
    //         $adjusted_rates['Legendary'] *= $multiplier;
    //     }

    //     // Hard pity: Guarantee Epic
    //     if ($pity_data['pulls_since_epic'] >= $this->hard_pity_threshold) {
    //         $adjusted_rates['Epic'] = max($adjusted_rates['Epic'], 50.0);
    //         $adjusted_rates['Common'] *= 0.5;
    //         $adjusted_rates['Uncommon'] *= 0.5;
    //     }

    //     // Legendary pity: Guarantee Legendary
    //     if ($pity_data['pulls_since_legendary'] >= $this->legendary_pity_threshold) {
    //         $adjusted_rates['Legendary'] = 100.0;
    //         $adjusted_rates['Epic'] = 0.0;
    //         $adjusted_rates['Rare'] = 0.0;
    //         $adjusted_rates['Uncommon'] = 0.0;
    //         $adjusted_rates['Common'] = 0.0;
    //     }

    //     // Normalize rates to ensure they sum to 100%
    //     return $this->normalize_rates($adjusted_rates);
    // }

    private function normalize_keys(array $array)
    {
        $normalized = [];
        foreach ($array as $key => $value) {
            $normalized[ucfirst(strtolower(trim($key)))] = $value;
        }
        return $normalized;
    }

    private function apply_pity_system($base_rates, $pity_data)
    {
        $adjusted_rates = $this->normalize_keys($base_rates);

        // Soft pity
        if ($pity_data['pulls_since_epic'] >= $this->soft_pity_threshold) {
            $multiplier = min(
                $this->soft_pity_multiplier,
                1 + (($pity_data['pulls_since_epic'] - $this->soft_pity_threshold) * 0.1)
            );

            if (isset($adjusted_rates['Epic'])) {
                $adjusted_rates['Epic'] *= $multiplier;
            }
            if (isset($adjusted_rates['Legendary'])) {
                $adjusted_rates['Legendary'] *= $multiplier;
            }
        }

        // Hard pity
        if ($pity_data['pulls_since_epic'] >= $this->hard_pity_threshold) {
            $adjusted_rates['Epic'] = max($adjusted_rates['Epic'] ?? 0, 50.0);
            foreach (['Common', 'Uncommon'] as $r) {
                if (isset($adjusted_rates[$r])) {
                    $adjusted_rates[$r] *= 0.5;
                }
            }
        }

        // Legendary pity
        if ($pity_data['pulls_since_legendary'] >= $this->legendary_pity_threshold) {
            $adjusted_rates = [
                'Legendary' => 100.0,
                'Epic' => 0.0,
                'Rare' => 0.0,
                'Uncommon' => 0.0,
                'Common' => 0.0,
            ];
        }

        return $this->normalize_rates($adjusted_rates);
    }


    private function normalize_rates($rates)
    {
        $total = array_sum($rates);
        if ($total <= 0)
            return $rates;

        foreach ($rates as $rarity => $rate) {
            $rates[$rarity] = ($rate / $total) * 100.0;
        }
        return $rates;
    }

    /**
     * Select rarity based on weighted probability
     * 
     * @param float $random_value Random value (0.0 to 1.0)
     * @param array $rates Weighted rates by rarity
     * @return string Selected rarity
     */
    private function select_rarity($random_value, $rates)
    {
        $cumulative = 0.0;
        $scaled_random = $random_value * 100.0; // Scale to percentage

        foreach ($rates as $rarity => $rate) {
            $cumulative += $rate;
            if ($scaled_random <= $cumulative) {
                return $rarity;
            }
        }

        // Fallback to most common rarity
        return 'Common';
    }

    /**
     * Select specific item with advanced logic
     * 
     * @param array $items Available items of selected rarity
     * @param string $entropy Entropy for selection
     * @param int $pull_sequence Pull sequence number
     * @param string $player_id Player identifier
     * @return array Selected item
     */
    private function select_item_with_logic($items, $entropy, $pull_sequence, $player_id)
    {
        if (empty($items)) {
            throw new Exception("No items available for selected rarity");
        }

        // Get player preferences and history
        $player_preferences = $this->get_player_preferences($player_id);
        $player_inventory = $this->get_player_inventory($player_id);

        // Calculate weighted selection based on multiple factors
        $weighted_items = [];
        foreach ($items as $item) {
            $weight = $this->calculate_item_weight($item, $player_preferences, $player_inventory, $pull_sequence);
            $weighted_items[] = [
                'item' => $item,
                'weight' => $weight
            ];
        }

        // Sort by weight (descending)
        usort($weighted_items, function ($a, $b) {
            return $b['weight'] <=> $a['weight'];
        });

        // Select item using weighted random selection
        $total_weight = array_sum(array_column($weighted_items, 'weight'));
        $random_weight = $this->entropy_to_float($entropy, 1) * $total_weight;

        $cumulative_weight = 0;
        foreach ($weighted_items as $weighted_item) {
            $cumulative_weight += $weighted_item['weight'];
            if ($random_weight <= $cumulative_weight) {
                return $weighted_item['item'];
            }
        }

        // Fallback to first item
        return $weighted_items[0]['item'];
    }

    /**
     * Calculate item selection weight based on player data
     * 
     * @param array $item Item data
     * @param array $preferences Player preferences
     * @param array $inventory Player inventory
     * @param int $pull_sequence Current pull sequence
     * @return float Item weight
     */
    private function calculate_item_weight($item, $preferences, $inventory, $pull_sequence)
    {
        $base_weight = 1.0;

        // Featured item bonus
        if ($item['is_featured'] ?? false) {
            $base_weight *= 1.5;
        }

        // Player preference bonus
        $preferred_types = $preferences['preferred_item_types'] ?? [];
        if (in_array($item['item_type'], $preferred_types)) {
            $base_weight *= 1.3;
        }

        // Duplicate penalty (encourage variety)
        $duplicate_count = $inventory[$item['item_id']] ?? 0;
        if ($duplicate_count > 0) {
            $base_weight *= (1.0 / (1 + $duplicate_count * 0.2));
        }

        // Sequential pull bonus (anti-clustering)
        if ($pull_sequence > 1) {
            $base_weight *= (1.0 + ($pull_sequence * 0.05));
        }

        // Item type specific logic
        $base_weight *= $this->get_item_type_modifier($item, $preferences);

        return max($base_weight, 0.1); // Minimum weight
    }

    /**
     * Generate additional item properties (stats, variants, etc.)
     * 
     * @param array $item Base item data
     * @param string $entropy Entropy for property generation
     * @return array Generated properties
     */
    private function generate_item_properties($item, $entropy)
    {
        $properties = [];

        switch ($item['item_type']) {
            case 'pet':
                $properties = $this->generate_pet_properties($item, $entropy);
                break;

            case 'accessory':
                $properties = $this->generate_accessory_properties($item, $entropy);
                break;

            case 'furniture':
                $properties = $this->generate_furniture_properties($item, $entropy);
                break;

            case 'food':
                $properties = $this->generate_food_properties($item, $entropy);
                break;

            default:
                $properties = $this->generate_default_properties($item, $entropy);
        }

        return $properties;
    }

    /**
     * Generate pet-specific properties
     * 
     * @param array $item Pet item data
     * @param string $entropy Entropy for generation
     * @return array Pet properties
     */
    private function generate_pet_properties($item, $entropy)
    {
        $properties = [];

        // Generate personality traits
        $personality_traits = ['playful', 'calm', 'energetic', 'gentle', 'mischievous', 'loyal'];
        $trait_index = intval($this->entropy_to_float($entropy, 2) * count($personality_traits));
        $properties['personality'] = $personality_traits[$trait_index];

        // Generate physical variations
        $size_variations = ['tiny', 'small', 'medium', 'large', 'giant'];
        $size_index = intval($this->entropy_to_float($entropy, 3) * count($size_variations));
        $properties['size_variant'] = $size_variations[$size_index];

        // Generate color variations
        $color_variations = ['normal', 'shiny', 'albino', 'melanistic', 'rainbow'];
        $color_random = $this->entropy_to_float($entropy, 4);
        if ($color_random < 0.7) {
            $properties['color_variant'] = 'normal';
        } elseif ($color_random < 0.95) {
            $properties['color_variant'] = 'shiny';
        } else {
            $color_index = intval($color_random * count($color_variations));
            $properties['color_variant'] = $color_variations[$color_index];
        }

        // Generate initial stats
        $properties['initial_stats'] = [
            'happiness' => 50 + intval($this->entropy_to_float($entropy, 5) * 30),
            'energy' => 60 + intval($this->entropy_to_float($entropy, 6) * 25),
            'intelligence' => 40 + intval($this->entropy_to_float($entropy, 7) * 35)
        ];

        return $properties;
    }

    /**
     * Generate accessory-specific properties
     * 
     * @param array $item Accessory item data
     * @param string $entropy Entropy for generation
     * @return array Accessory properties
     */
    private function generate_accessory_properties($item, $entropy)
    {
        $properties = [];

        // Generate stat bonuses
        $stat_types = ['happiness', 'energy', 'cuteness', 'intelligence'];
        $bonus_count = 1 + intval($this->entropy_to_float($entropy, 2) * 2); // 1-2 bonuses

        for ($i = 0; $i < $bonus_count; $i++) {
            $stat_index = intval($this->entropy_to_float($entropy, 3 + $i) * count($stat_types));
            $stat_name = $stat_types[$stat_index];
            $bonus_value = 1 + intval($this->entropy_to_float($entropy, 5 + $i) * 9); // 1-10 bonus

            $properties['stat_bonuses'][$stat_name] = $bonus_value;
        }

        // Generate special effects
        $special_effects = ['sparkle', 'glow', 'particle_trail', 'sound_effect', 'animation'];
        if ($this->entropy_to_float($entropy, 8) < 0.3) { // 30% chance for special effect
            $effect_index = intval($this->entropy_to_float($entropy, 9) * count($special_effects));
            $properties['special_effect'] = $special_effects[$effect_index];
        }

        return $properties;
    }

    /**
     * Generate furniture-specific properties
     * 
     * @param array $item Furniture item data
     * @param string $entropy Entropy for generation
     * @return array Furniture properties
     */
    private function generate_furniture_properties($item, $entropy)
    {
        $properties = [];

        // Generate comfort bonus
        $properties['comfort_bonus'] = 1 + intval($this->entropy_to_float($entropy, 2) * 19); // 1-20

        // Generate durability
        $properties['durability'] = 80 + intval($this->entropy_to_float($entropy, 3) * 20); // 80-100

        // Generate interactive features
        $interactive_features = ['toy_dispenser', 'food_bowl', 'sleeping_spot', 'play_area', 'decoration'];
        $feature_count = 1 + intval($this->entropy_to_float($entropy, 4) * 2); // 1-2 features

        $properties['features'] = [];
        for ($i = 0; $i < $feature_count; $i++) {
            $feature_index = intval($this->entropy_to_float($entropy, 5 + $i) * count($interactive_features));
            $properties['features'][] = $interactive_features[$feature_index];
        }

        return $properties;
    }

    /**
     * Generate food-specific properties
     * 
     * @param array $item Food item data
     * @param string $entropy Entropy for generation
     * @return array Food properties
     */
    private function generate_food_properties($item, $entropy)
    {
        $properties = [];

        // Generate nutritional values
        $properties['nutrition'] = [
            'hunger_restore' => 10 + intval($this->entropy_to_float($entropy, 2) * 40), // 10-50
            'happiness_boost' => 5 + intval($this->entropy_to_float($entropy, 3) * 20),  // 5-25
            'energy_boost' => 3 + intval($this->entropy_to_float($entropy, 4) * 15)      // 3-18
        ];

        // Generate expiration time (in hours)
        $properties['expiration_hours'] = 24 + intval($this->entropy_to_float($entropy, 5) * 168); // 1-7 days

        // Generate taste preference
        $taste_types = ['sweet', 'savory', 'bitter', 'sour', 'umami'];
        $taste_index = intval($this->entropy_to_float($entropy, 6) * count($taste_types));
        $properties['taste_type'] = $taste_types[$taste_index];

        return $properties;
    }

    /**
     * Get player's item preferences and history
     * 
     * @param string $player_id Player identifier
     * @return array Player preferences
     */
    private function get_player_preferences($player_id)
    {
        // This would typically query the database
        // For now, returning default preferences
        return [
            'preferred_item_types' => ['pet', 'accessory'],
            'preferred_rarities' => ['Epic', 'Legendary'],
            'collected_items_count' => 0,
            'favorite_categories' => []
        ];
    }

    private function get_items_by_rarity($items, $rarity)
    {
        return array_filter($items, function ($item) use ($rarity) {
            return $item['rarity'] === $rarity;
        });
    }

    // private function update_pity_counters(&$pity_data, $rarity)
    // {
    //     $pity_data['total_pulls']++;

    //     if ($rarity === 'Legendary') {
    //         $pity_data['pulls_since_legendary'] = 0;
    //         $pity_data['pulls_since_epic'] = 0;
    //     } elseif ($rarity === 'Epic') {
    //         $pity_data['pulls_since_epic'] = 0;
    //         $pity_data['pulls_since_legendary']++;
    //     } else {
    //         $pity_data['pulls_since_epic']++;
    //         $pity_data['pulls_since_legendary']++;
    //     }
    // }

    private function update_pity_counters(&$pity_data, $rarity)
    {
        $rarity = ucfirst(strtolower(trim($rarity)));

        $pity_data['total_pulls']++;

        if ($rarity === 'Legendary') {
            $pity_data['pulls_since_legendary'] = 0;
            $pity_data['pulls_since_epic'] = 0;
        } elseif ($rarity === 'Epic') {
            $pity_data['pulls_since_epic'] = 0;
            $pity_data['pulls_since_legendary']++;
        } else {
            $pity_data['pulls_since_epic']++;
            $pity_data['pulls_since_legendary']++;
        }
    }


    private function save_player_pity_data($playerId, $poolId, array $pityData): void
    {
        $db = \Config\Database::connect();

        $db->table('player_pity_data')->replace([
            'player_id' => $playerId,
            'pool_id' => $poolId,
            'pulls_since_epic' => $pityData['pulls_since_epic'] ?? 0,
            'pulls_since_legendary' => $pityData['pulls_since_legendary'] ?? 0,
            'total_pulls' => $pityData['total_pulls'] ?? 0,
            'last_epic_item' => $pityData['last_epic_item'] ?? null,
            'last_legendary_item' => $pityData['last_legendary_item'] ?? null,
        ]);
    }


    private function is_new_item_for_player($playerId, $itemId): bool
    {
        $db = \Config\Database::connect();

        $exists = $db->table('player_inventory')
            ->where('player_id', $playerId)
            ->where('item_id', $itemId)
            ->countAllResults();

        return $exists === 0;
    }

    private function get_player_inventory($playerId): array
    {
        $db = \Config\Database::connect();

        $query = $db->table('player_inventory')
            ->select('item_id, COUNT(*) as quantity')
            ->where('player_id', $playerId)
            ->groupBy('item_id')
            ->get();

        $inventory = [];
        foreach ($query->getResultArray() as $row) {
            $inventory[$row['item_id']] = (int) $row['quantity'];
        }

        return $inventory;
    }

    // private function calculate_item_value($item, $properties)
    // {
    //     // Calculate estimated coin value based on rarity and properties
    //     $base_values = [
    //         'Common' => 50,
    //         'Uncommon' => 150,
    //         'Rare' => 500,
    //         'Epic' => 1500,
    //         'Legendary' => 5000
    //     ];

    //     $base_value = $base_values[$item['rarity']] ?? 50;

    //     // Add property bonuses
    //     $property_bonus = 0;
    //     if (isset($properties['special_effect']))
    //         $property_bonus += 100;
    //     if (isset($properties['color_variant']) && $properties['color_variant'] !== 'normal') {
    //         $property_bonus += 200;
    //     }

    //     return $base_value + $property_bonus;
    // }

    private function calculate_item_value($item, $properties)
    {
        $rarity = ucfirst(strtolower(trim($item['rarity'])));
        $base_values = [
            'Common' => 50,
            'Uncommon' => 150,
            'Rare' => 500,
            'Epic' => 1500,
            'Legendary' => 5000
        ];

        $base_value = $base_values[$rarity] ?? 50;

        $property_bonus = 0;
        if (isset($properties['special_effect']))
            $property_bonus += 100;
        if (!empty($properties['color_variant']) && $properties['color_variant'] !== 'normal')
            $property_bonus += 200;

        return $base_value + $property_bonus;
    }


    private function get_item_type_modifier($item, $preferences)
    {
        // Item type specific modifiers
        $modifiers = [
            'pet' => 1.2,
            'accessory' => 1.0,
            'furniture' => 0.9,
            'food' => 0.8
        ];

        return $modifiers[$item['item_type']] ?? 1.0;
    }

    private function generate_default_properties($item, $entropy)
    {
        return [
            'quality' => 50 + intval($this->entropy_to_float($entropy, 2) * 50),
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    private function log_pull_event($playerId, $poolId, $pullResult, $entropy): void
    {
        // if ($this->debug_mode) {
        $db = \Config\Database::connect();

        $db->table('gacha_audit_log')->insert([
            'id' => $this->generate_uuid(), // Assuming you have a UUID generator
            'player_id' => $playerId,
            'pool_id' => $poolId,
            'item_id' => $pullResult['item_id'],
            'rarity' => $pullResult['rarity'],
            'entropy_hash' => hash('sha256', $entropy),
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            // 'created_at' auto-filled by MySQL
        ]);
        // }
    }

    private function generate_uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    private function generate_entropy_hash($entropy_batch)
    {
        return hash('sha256', implode('', $entropy_batch));
    }
}