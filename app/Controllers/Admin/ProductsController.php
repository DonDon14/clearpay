<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProductModel;

class ProductsController extends BaseController
{
    public function save()
    {
        try {
            $rules = [
                'title' => 'required|min_length[3]|max_length[255]',
                'amount' => 'required|numeric|greater_than[0]',
                'cost_price' => 'required|numeric|greater_than_equal_to[0]',
                'status' => 'required|in_list[active,inactive]',
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $model = new ProductModel();
            $id = $this->request->getPost('id');
            $existing = $id ? $model->find($id) : null;
            $data = [
                'title' => $this->request->getPost('title'),
                'description' => $this->request->getPost('description'),
                'amount' => $this->request->getPost('amount'),
                'cost_price' => $this->request->getPost('cost_price') ?: 0,
                'category' => $this->request->getPost('category'),
                'status' => $this->request->getPost('status'),
                'created_by' => session()->get('user-id') ?: null,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $imageFile = $this->request->getFile('image');
            $removeImage = $this->request->getPost('remove_image') === '1';

            if ($imageFile && $imageFile->isValid() && $imageFile->getError() !== UPLOAD_ERR_NO_FILE) {
                $data['image_path'] = $this->storePublicImageUpload($imageFile, 'product_items', 'product', $existing['image_path'] ?? null);
            } elseif ($removeImage) {
                $oldImage = $this->normalizePublicUploadPath($existing['image_path'] ?? null, 'product_items');
                if ($oldImage) {
                    $oldFile = FCPATH . $oldImage;
                    if (is_file($oldFile)) {
                        @unlink($oldFile);
                    }
                }
                $data['image_path'] = null;
            }

            if (!$id) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }

            $result = $id ? $model->update($id, $data) : $model->insert($data);

            return $this->response->setJSON([
                'success' => (bool) $result,
                'message' => $result ? ($id ? 'Product updated successfully.' : 'Product added successfully.') : 'Failed to save product',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ]);
        }
    }

    public function get($id)
    {
        $product = (new ProductModel())->find($id);
        if ($product) {
            $product['image_path'] = $this->normalizePublicUploadPath($product['image_path'] ?? null, 'product_items');
        }

        return $this->response->setJSON([
            'success' => (bool) $product,
            'product' => $product,
            'message' => $product ? null : 'Product not found',
        ]);
    }

    public function update($id)
    {
        return $this->save();
    }

    public function delete($id)
    {
        $model = new ProductModel();
        $product = $model->find($id);
        if (!$product) {
            return $this->response->setJSON(['success' => false, 'message' => 'Product not found']);
        }

        return $this->response->setJSON([
            'success' => (bool) $model->delete($id),
            'message' => 'Product deleted successfully.',
        ]);
    }

    public function toggleStatus($id)
    {
        $model = new ProductModel();
        $product = $model->find($id);
        if (!$product) {
            return $this->response->setJSON(['success' => false, 'message' => 'Product not found']);
        }

        $newStatus = ($product['status'] ?? 'active') === 'active' ? 'inactive' : 'active';
        $model->update($id, ['status' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Product status updated successfully.',
            'newStatus' => $newStatus,
        ]);
    }
}
