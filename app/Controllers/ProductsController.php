<?php
namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class ProductsController extends ResourceController
{
    // Load the model
    protected $modelName = 'App\Models\ProductModel';
    protected $format    = 'json';

    protected $categoryModel;

    public function __contruct()
    {
        $this->categoryModel = new \App\Models\CategoryModel();
    }

    /**
     * Get a list of all products (index)
     * @return JSON
     */
    // public function index()
    // {
    //     $page = $this->request->getVar('page') ?? 1;
    //     $perPage = $this->request->getVar('perPage');
    //     if (!$perPage) {
    //         $perPage = null;
    //     }
    //     $code = $this->request->getVar('code');
    //     $title = $this->request->getVar('title');
    //     $description = $this->request->getVar('description');
    //     $where = [];
    //     if ($code) {
    //         $where['code like'] = '%' . $code . '%';
    //     }
    //     if ($title) {
    //         $where['title like'] = '%' . $title . '%';
    //     }
    //     if ($description) {
    //         $where['description like'] = '%' . $description . '%';
    //     }
    //     $totalProducts = $this->model->selectCount('id')->where($where)->get()->getRowArray()['id'];
    //     $products = $this->model->where($where)->orderBy('created_at', 'DESC')->paginate($perPage, 'products', $page);
    //     $data = [
    //         'status' => true,
    //         'data' => [
    //             'products' => $products,
    //             'total' => $totalProducts,
    //         ],
    //     ];

    //     return $this->respond($data);
    // }

    /**
     * Get a list of all products (index)
     * @return JSON
     */
    public function index()
    {
        $page        = $this->request->getVar('page') ?? 1;
        $perPage     = $this->request->getVar('perPage') ?? 10;
        $code        = $this->request->getVar('code');
        $title       = $this->request->getVar('title');
        $description = $this->request->getVar('description');
        $category_id = $this->request->getVar('category_id');

        $where = [];

        if ($code) {
            $where['products.code like'] = '%' . $code . '%';
        }
        if ($title) {
            $where['products.title like'] = '%' . $title . '%';
        }
        if ($description) {
            $where['products.description like'] = '%' . $description . '%';
        }
        if ($category_id) {
            $where['products.category_id'] = $category_id;
        }

        // Get total products count
        $totalProducts = $this->model
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->selectCount('products.id')
            ->where($where)
            ->get()
            ->getRowArray()['id'];

        // Get paginated products with category info
        $products = $this->model
            ->select('products.*, categories.title as category_name')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->where($where)
            ->orderBy('products.created_at', 'DESC')
            ->paginate($perPage, 'products', $page);

        // Format output to include category details
        foreach ($products as &$product) {
            $product['category'] = $product['category_name'] ?? null;
            unset($product['category_name']); // Clean up response
        }

        $data = [
            'status' => true,
            'data'   => [
                'products' => $products,
                'total'    => $totalProducts,
            ],
        ];

        return $this->respond($data);
    }

    /**
     * Return the properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function show($id = null)
    {
        $product = $this->model->find($id);

        if (! $product) {
            return $this->failNotFound("Product not found with ID: $id");
        }

        return $this->respond($product);
    }

    /**
     * Create a new product (create)
     * @return JSON
     */
    public function create()
    {
        $data = $this->request->getJSON(true);

        // Validate data before inserting
        if (! $this->validate($this->model->validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if ($this->model->save($data)) {
            $newId = $this->model->getInsertID();
            // Fetch the newly created product for the response
            $newProduct = $this->model->find($newId);
            $response   = [
                "status"  => true,
                "message" => "Product created successfully",
                "product" => $newProduct,
            ];
            return $this->respondCreated($response);
        }

        return $this->failServerError('Failed to create product.');
    }

    /**
     * Update a specific product (update)
     * @param string $id
     * @return JSON
     */
    public function update($id = null)
    {
        $data            = $this->request->getJSON(true);
        $existingProduct = $this->model->find($id);
        if ($existingProduct) {
            if ($this->model->update($id, $data)) {
                $updatedProduct = $this->model->find($id);
                return $this->respondUpdated($updatedProduct);
            }
        } else {
            return $this->failNotFound('Product not found');
        }
        return $this->failServerError('Failed to update product.');
    }

    /**
     * Delete a specific product (delete)
     * @param string $id
     * @return JSON
     */
    public function delete($id = null)
    {
        // Find the product
        $product = $this->model->find($id);
        if (! $product) {
            return $this->failNotFound("Product not found with ID: $id");
        }
        // Delete product
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Product deleted successfully']);
        }

        return $this->failServerError('Failed to delete product.');
    }
}
