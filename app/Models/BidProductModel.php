<?php

namespace App\Models;

use CodeIgniter\Model;

class BidProductModel extends Model
{
    protected $table = 'bid_products'; // Name of the table
    protected $primaryKey = 'id'; // Primary key of the table

    // Specify that the primary key is not auto-incrementing (since it's a VARCHAR)
    protected $useAutoIncrement = false;

    // Return type of the results (you can use 'object' if you prefer)
    protected $returnType = 'array';

    // Enable automatic timestamps for created_at and updated_at fields
    protected $useTimestamps = true;

    // Specify the date format for the timestamps
    protected $dateFormat = 'datetime';

    // List of fields that are allowed to be set during insert/update
    protected $allowedFields = [
        'id',
        'bid_id',
        'product_id',
        'unit_pack',
        'quantity_offered',
        'unit_price',
        'total_price',
        'lead_time',
        'manufacture',
        'country_of_origin',
        'registration_number',
        'medicine_regulatory_authority',
        'shipment_weight',
        'shipment_volume',
        'comments',
        'created_at',
        'updated_at',
    ];

    // Validation rules for the fields
    protected $validationRules = [
        // 'bid_id' => 'required|max_length[255]',
        // 'product_id' => 'required|max_length[255]',
    ];

    // Validation messages for custom error messages
    protected $validationMessages = [
        // 'bid_id' => [
        //     'required' => 'Bid ID is required',
        //     'alpha_numeric_space' => 'Bid ID can only contain alphanumeric characters and spaces',
        //     'max_length' => 'Bid ID cannot exceed 255 characters',
        // ],
        // 'product_id' => [
        //     'required' => 'Product ID is required',
        //     'alpha_numeric_space' => 'Product ID can only contain alphanumeric characters and spaces',
        //     'max_length' => 'Product ID cannot exceed 255 characters',
        // ],
    ];

    // Disables validation rules if set to true
    protected $skipValidation = false;

    // If you want to implement custom methods, you can do so below
    public function getProductsByBidId($bidId)
    {
        return $this->where('bid_id', $bidId)->findAll();
    }

    public function getBidsByProductId($productId)
    {
        return $this->where('product_id', $productId)->findAll();
    }

    /**
     * Automatically generate UUID v4 for the `id` field if it's not provided.
     *
     * @param array $data
     * @return array
     */
    protected function generateUuid(array $data)
    {
        if (empty($data['data']['id'])) {
            $data['data']['id'] = uuid_v4(); // Generate and set UUID v4
        }
        return $data;
    }

    protected $beforeInsert = ['generateUuid'];

    public function findWithDetails(string $id)
    {
        return $this
            ->select('bid_products.*, products.title, products.code, products.description') // Select columns you need
            ->join('products', 'products.id = bid_products.product_id')
            ->where('bid_products.id', $id)
            ->first();
    }

    /**
     * Retrieve a single attachment record by its ID.
     *
     * @param string $id
     * @return array|null
     */
    public function getRecordById(string $id): ?array
    {
        return $this->where('id', $id)->first();
    }

    /**
     * Retrieve a list of attachments with associated product details for a specific submission ID.
     *
     * @param string $bidId
     * @return array
     */
    public function getRecordsByBidId(string $bidId): array
    {
        return $this->select('bid_products.*, products.title as product_name, products.description as product_description')
            ->join('products', 'products.id = bid_products.product_id', 'left') // Join products table
            ->where('bid_products.bid_id', $bidId)
            ->orderBy('bid_products.created_at', 'ASC') // Optional: order by creation time
            ->findAll();
    }

}
