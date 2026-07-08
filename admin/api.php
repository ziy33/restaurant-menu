<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

// Get the data file path - use dirname for reliable cross-platform resolution
$dataFile = dirname(__DIR__) . '/data/menu.json';

// Resolve to absolute path, with fallback
$resolved = realpath(dirname($dataFile));
if ($resolved !== false) {
    $dataFile = $resolved . '/menu.json';
} else {
    // Fallback: normalize path manually for Windows
    $dataFile = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dataFile);
}

// Read menu data with file locking
function readMenuData($file) {
    clearstatcache(true, $file);
    if (!file_exists($file)) {
        return null;
    }
    $content = file_get_contents($file);
    if ($content === false) {
        return null;
    }
    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return null;
    }
    return $data;
}

// Write menu data with file locking
function writeMenuData($file, $data) {
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $result = file_put_contents($file, $json, LOCK_EX);
    return $result !== false;
}

// Get max ID from array
function getMaxId($items) {
    if (empty($items)) return 0;
    $maxId = 0;
    foreach ($items as $item) {
        if (isset($item['id']) && $item['id'] > $maxId) {
            $maxId = $item['id'];
        }
    }
    return $maxId;
}

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 'list' action is public (no login required), all others need auth
if ($action !== 'list') {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => '未授权访问，请先登录']);
        exit;
    }
}

// Handle different actions
switch ($action) {
    case 'list':
        // GET - Return all menu data (public, no auth needed)
        $data = readMenuData($dataFile);
        if ($data === null) {
            echo json_encode(['success' => false, 'error' => '无法读取菜单数据']);
        } else {
            echo json_encode(['success' => true, 'data' => $data]);
        }
        break;

    case 'add_dish':
        // POST - Add new dish
        if ($method !== 'POST') {
            echo json_encode(['success' => false, 'error' => '仅支持 POST 请求']);
            break;
        }
        
        $data = readMenuData($dataFile);
        if ($data === null) {
            echo json_encode(['success' => false, 'error' => '无法读取菜单数据']);
            break;
        }
        
        $name = trim($_POST['name'] ?? '');
        $name_id = trim($_POST['name_id'] ?? '');
        $price = intval($_POST['price'] ?? 0);
        $category_id = $_POST['category_id'] ?? '';
        $sort = intval($_POST['sort'] ?? 0);
        $image = $_POST['image'] ?? '';
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'error' => '菜品名称不能为空']);
            break;
        }
        
        if ($price <= 0) {
            echo json_encode(['success' => false, 'error' => '价格必须大于 0']);
            break;
        }
        
        if (empty($category_id)) {
            echo json_encode(['success' => false, 'error' => '必须选择分类']);
            break;
        }
        
        // Check if category exists
        $categoryExists = false;
        foreach ($data['categories'] as $cat) {
            if ($cat['id'] === $category_id) {
                $categoryExists = true;
                break;
            }
        }
        
        if (!$categoryExists) {
            echo json_encode(['success' => false, 'error' => '分类不存在']);
            break;
        }
        
        // Generate new ID
        $newId = getMaxId($data['dishes']) + 1;
        
        // Calculate sort order (default to last in category)
        if ($sort <= 0) {
            $maxSort = 0;
            foreach ($data['dishes'] as $dish) {
                if ($dish['category_id'] === $category_id && $dish['sort'] > $maxSort) {
                    $maxSort = $dish['sort'];
                }
            }
            $sort = $maxSort + 1;
        }
        
        $newDish = [
            'id' => $newId,
            'category_id' => $category_id,
            'name' => $name,
            'name_id' => $name_id,
            'price' => $price,
            'image' => $image,
            'sort' => $sort
        ];
        
        $data['dishes'][] = $newDish;
        
        if (writeMenuData($dataFile, $data)) {
            echo json_encode(['success' => true, 'data' => $newDish]);
        } else {
            echo json_encode(['success' => false, 'error' => '保存失败']);
        }
        break;

    case 'edit_dish':
        // POST - Edit dish
        if ($method !== 'POST') {
            echo json_encode(['success' => false, 'error' => '仅支持 POST 请求']);
            break;
        }
        
        $data = readMenuData($dataFile);
        if ($data === null) {
            echo json_encode(['success' => false, 'error' => '无法读取菜单数据']);
            break;
        }
        
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => '无效的菜品 ID']);
            break;
        }
        
        // Find and update dish
        $found = false;
        foreach ($data['dishes'] as &$dish) {
            if ($dish['id'] === $id) {
                $found = true;
                if (isset($_POST['name'])) $dish['name'] = trim($_POST['name']);
                if (isset($_POST['name_id'])) $dish['name_id'] = trim($_POST['name_id']);
                if (isset($_POST['price'])) $dish['price'] = intval($_POST['price']);
                if (isset($_POST['category_id'])) $dish['category_id'] = $_POST['category_id'];
                if (isset($_POST['sort'])) $dish['sort'] = intval($_POST['sort']);
                if (isset($_POST['image'])) $dish['image'] = $_POST['image'];
                break;
            }
        }
        
        if (!$found) {
            echo json_encode(['success' => false, 'error' => '菜品不存在']);
            break;
        }
        
        if (writeMenuData($dataFile, $data)) {
            echo json_encode(['success' => true, 'message' => '菜品已更新']);
        } else {
            echo json_encode(['success' => false, 'error' => '保存失败']);
        }
        break;

    case 'delete_dish':
        // POST - Delete dish
        if ($method !== 'POST') {
            echo json_encode(['success' => false, 'error' => '仅支持 POST 请求']);
            break;
        }
        
        $data = readMenuData($dataFile);
        if ($data === null) {
            echo json_encode(['success' => false, 'error' => '无法读取菜单数据']);
            break;
        }
        
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => '无效的菜品 ID']);
            break;
        }
        
        // Find dish and delete image if exists
        $imageToDelete = '';
        foreach ($data['dishes'] as $dish) {
            if ($dish['id'] === $id) {
                $imageToDelete = $dish['image'];
                break;
            }
        }
        
        // Remove dish from array
        $originalCount = count($data['dishes']);
        $data['dishes'] = array_values(array_filter($data['dishes'], function($dish) use ($id) {
            return $dish['id'] !== $id;
        }));
        
        if (count($data['dishes']) === $originalCount) {
            echo json_encode(['success' => false, 'error' => '菜品不存在']);
            break;
        }
        
        // Delete image file if exists
        if (!empty($imageToDelete)) {
            $imagePath = dirname(__DIR__) . '/uploads/' . $imageToDelete;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        if (writeMenuData($dataFile, $data)) {
            echo json_encode(['success' => true, 'message' => '菜品已删除']);
        } else {
            echo json_encode(['success' => false, 'error' => '删除失败']);
        }
        break;

    case 'add_category':
        // POST - Add new category
        if ($method !== 'POST') {
            echo json_encode(['success' => false, 'error' => '仅支持 POST 请求']);
            break;
        }
        
        $data = readMenuData($dataFile);
        if ($data === null) {
            echo json_encode(['success' => false, 'error' => '无法读取菜单数据']);
            break;
        }
        
        $name = trim($_POST['name'] ?? '');
        $name_id = trim($_POST['name_id'] ?? '');
        if (empty($name)) {
            echo json_encode(['success' => false, 'error' => '分类名称不能为空']);
            break;
        }
        
        // Generate ID from name (simplified Chinese to pinyin-like)
        $id = strtolower(preg_replace('/[^a-z0-9]/', '-', preg_replace('/[\x{4e00}-\x{9fa5}]+/u', 'category', $name)));
        $id = preg_replace('/-+/', '-', $id);
        $id = trim($id, '-');
        
        // Ensure unique ID
        $baseId = $id;
        $counter = 1;
        while (true) {
            $exists = false;
            foreach ($data['categories'] as $cat) {
                if ($cat['id'] === $id) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) break;
            $id = $baseId . '-' . $counter;
            $counter++;
        }
        
        // Calculate sort order
        $sort = 1;
        foreach ($data['categories'] as $cat) {
            if ($cat['sort'] >= $sort) {
                $sort = $cat['sort'] + 1;
            }
        }
        
        $newCategory = [
            'id' => $id,
            'name' => $name,
            'name_id' => $name_id,
            'sort' => $sort
        ];
        
        $data['categories'][] = $newCategory;
        
        if (writeMenuData($dataFile, $data)) {
            echo json_encode(['success' => true, 'data' => $newCategory]);
        } else {
            echo json_encode(['success' => false, 'error' => '保存失败']);
        }
        break;

    case 'edit_category':
        // POST - Edit category
        if ($method !== 'POST') {
            echo json_encode(['success' => false, 'error' => '仅支持 POST 请求']);
            break;
        }
        
        $data = readMenuData($dataFile);
        if ($data === null) {
            echo json_encode(['success' => false, 'error' => '无法读取菜单数据']);
            break;
        }
        
        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['success' => false, 'error' => '无效的分类 ID']);
            break;
        }
        
        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            echo json_encode(['success' => false, 'error' => '分类名称不能为空']);
            break;
        }
        
        // Find and update category
        $found = false;
        foreach ($data['categories'] as &$category) {
            if ($category['id'] === $id) {
                $found = true;
                $category['name'] = $name;
                if (isset($_POST['name_id'])) $category['name_id'] = trim($_POST['name_id']);
                if (isset($_POST['sort'])) {
                    $category['sort'] = intval($_POST['sort']);
                }
                break;
            }
        }
        
        if (!$found) {
            echo json_encode(['success' => false, 'error' => '分类不存在']);
            break;
        }
        
        if (writeMenuData($dataFile, $data)) {
            echo json_encode(['success' => true, 'message' => '分类已更新']);
        } else {
            echo json_encode(['success' => false, 'error' => '保存失败']);
        }
        break;

    case 'delete_category':
        // POST - Delete category and all its dishes
        if ($method !== 'POST') {
            echo json_encode(['success' => false, 'error' => '仅支持 POST 请求']);
            break;
        }
        
        $data = readMenuData($dataFile);
        if ($data === null) {
            echo json_encode(['success' => false, 'error' => '无法读取菜单数据']);
            break;
        }
        
        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['success' => false, 'error' => '无效的分类 ID']);
            break;
        }
        
        // Check if category exists
        $categoryExists = false;
        foreach ($data['categories'] as $cat) {
            if ($cat['id'] === $id) {
                $categoryExists = true;
                break;
            }
        }
        
        if (!$categoryExists) {
            echo json_encode(['success' => false, 'error' => '分类不存在']);
            break;
        }
        
        // Delete all dishes in this category (and their images)
        foreach ($data['dishes'] as $dish) {
            if ($dish['category_id'] === $id && !empty($dish['image'])) {
                $imagePath = dirname(__DIR__) . '/uploads/' . $dish['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }
        
        $data['dishes'] = array_values(array_filter($data['dishes'], function($dish) use ($id) {
            return $dish['category_id'] !== $id;
        }));
        
        // Remove category
        $data['categories'] = array_values(array_filter($data['categories'], function($cat) use ($id) {
            return $cat['id'] !== $id;
        }));
        
        if (writeMenuData($dataFile, $data)) {
            echo json_encode(['success' => true, 'message' => '分类及该分类下所有菜品已删除']);
        } else {
            echo json_encode(['success' => false, 'error' => '删除失败']);
        }
        break;

    case 'reorder':
        // POST - Reorder dishes or categories
        if ($method !== 'POST') {
            echo json_encode(['success' => false, 'error' => '仅支持 POST 请求']);
            break;
        }
        
        $data = readMenuData($dataFile);
        if ($data === null) {
            echo json_encode(['success' => false, 'error' => '无法读取菜单数据']);
            break;
        }
        
        $type = $_POST['type'] ?? '';
        $items = $_POST['items'] ?? [];
        
        if (empty($type) || empty($items)) {
            echo json_encode(['success' => false, 'error' => '参数错误']);
            break;
        }
        
        if ($type === 'categories') {
            foreach ($items as $item) {
                foreach ($data['categories'] as &$category) {
                    if ($category['id'] === $item['id']) {
                        $category['sort'] = intval($item['sort']);
                        break;
                    }
                }
            }
            // Sort categories by sort value
            usort($data['categories'], function($a, $b) {
                return $a['sort'] - $b['sort'];
            });
        } elseif ($type === 'dishes') {
            foreach ($items as $item) {
                foreach ($data['dishes'] as &$dish) {
                    if ($dish['id'] === intval($item['id'])) {
                        $dish['sort'] = intval($item['sort']);
                        break;
                    }
                }
            }
            // Sort dishes within each category
            usort($data['dishes'], function($a, $b) {
                if ($a['category_id'] !== $b['category_id']) {
                    return strcmp($a['category_id'], $b['category_id']);
                }
                return $a['sort'] - $b['sort'];
            });
        } else {
            echo json_encode(['success' => false, 'error' => '无效的排序类型']);
            break;
        }
        
        if (writeMenuData($dataFile, $data)) {
            echo json_encode(['success' => true, 'message' => '排序已更新']);
        } else {
            echo json_encode(['success' => false, 'error' => '保存失败']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => '未知的操作']);
        break;
}
