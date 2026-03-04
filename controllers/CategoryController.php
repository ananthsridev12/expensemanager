<?php

namespace Controllers;

use Models\Category;

class CategoryController extends BaseController
{
    private Category $categoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->categoryModel = new Category($this->database);
    }

    public function index(): string
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['form']) && $_POST['form'] === 'category') {
                $this->categoryModel->createCategory($_POST['name'] ?? '', $_POST['type'] ?? 'expense');
            }

            if (isset($_POST['form']) && $_POST['form'] === 'subcategory') {
                $this->categoryModel->createSubcategory((int) ($_POST['category_id'] ?? 0), $_POST['name'] ?? '');
            }

            header('Location: ?module=categories');
            exit;
        }

        $categories = $this->categoryModel->getAllWithSubcategories();

        return $this->render('categories/index.php', [
            'categories' => $categories,
        ]);
    }
}
