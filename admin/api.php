<?php
// api.php - Image Manager API
// -----------------------------------------------------------------------------
// Uploads stored in /cadetportal/uploads/
// Logs errors to /cadetportal/php_error.log
// -----------------------------------------------------------------------------

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php_error.log');
header('Content-Type: application/json; charset=UTF-8');

$DB_HOST = 'localhost';
$DB_NAME = 'cadetportal';
$DB_USER = 'root';
$DB_PASS = ''; // set if needed

define('MAX_UPLOAD_BYTES', 10 * 1024 * 1024); // 10MB
$ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    error_log('DB connection failed: ' . $e->getMessage());
    exit;
}

/** Helpers */
function getJsonBody(): array
{
    $raw = file_get_contents('php://input');
    $decoded = $raw ? json_decode($raw, true) : [];
    return is_array($decoded) ? $decoded : [];
}

function getInputParam(string $key, $default = null)
{
    if (isset($_POST[$key])) return $_POST[$key];
    $json = getJsonBody();
    if (isset($json[$key])) return $json[$key];
    if (isset($_GET[$key])) return $_GET[$key];
    return $default;
}

function sendJson($data, int $status = 200)
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

/** Router */
$action = $_GET['action'] ?? null;
if (!$action) sendJson(['success' => false, 'message' => 'No action specified.'], 400);

switch ($action) {
    case 'get_categories':
        sendJson(handleGetCategories($pdo));
        break;
    case 'add_subcategory':
        sendJson(handleAddSubcategory($pdo));
        break;
    case 'upload_image':
        sendJson(handleUploadImage($pdo));
        break;
    case 'delete_image':
        sendJson(handleDeleteImage($pdo));
        break;
    case 'delete_category':
        sendJson(handleDeleteCategory($pdo));
        break;
    case 'get_images':
        sendJson(handleGetImages($pdo));
        break;
    default:
        sendJson(['success' => false, 'message' => 'Invalid action.'], 400);
}

/* ============================================================================ 
   Functions
============================================================================ */

function handleGetCategories(PDO $pdo): array
{
    try {
        $sql = "
            SELECT c.id AS category_id, c.year,
                   s.id AS sub_id, s.name AS sub_name,
                   i.id AS img_id, i.title AS img_title, i.url AS img_url
            FROM image_categories c
            LEFT JOIN image_subcategories s ON s.category_id = c.id
            LEFT JOIN images i ON i.subcategory_id = s.id
            ORDER BY c.year DESC, s.name ASC, i.id DESC
        ";
        $rows = $pdo->query($sql)->fetchAll();
        $map = [];
        foreach ($rows as $r) {
            $cid = (int)$r['category_id'];
            if (!isset($map[$cid])) $map[$cid] = ['id' => $cid, 'year' => $r['year'], 'subcategories' => []];
            if (!empty($r['sub_id'])) {
                $subId = (int)$r['sub_id'];
                if (!isset($map[$cid]['subcategories'][$subId])) {
                    $map[$cid]['subcategories'][$subId] = ['id' => $subId, 'name' => $r['sub_name'], 'images' => []];
                }
                if (!empty($r['img_id'])) {
                    $map[$cid]['subcategories'][$subId]['images'][] = [
                        'id' => (int)$r['img_id'],
                        'title' => $r['img_title'],
                        'url' => $r['img_url']
                    ];
                }
            }
        }
        foreach ($map as &$cat) $cat['subcategories'] = array_values($cat['subcategories']);
        return ['success' => true, 'data' => array_values($map)];
    } catch (PDOException $e) {
        error_log('getCategories error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to fetch categories.'];
    }
}

function handleAddSubcategory(PDO $pdo): array
{
    $body = getJsonBody();
    $categoryId = $_POST['year_id'] ?? $_POST['category_id'] ?? $body['year_id'] ?? $body['category_id'] ?? null;
    $name = $_POST['name'] ?? $body['name'] ?? null;
    if (!$categoryId || !$name) return ['success' => false, 'message' => 'Missing year_id/category_id or name.'];

    try {
        $stmt = $pdo->prepare("INSERT INTO image_subcategories (category_id,name) VALUES (?,?)");
        $stmt->execute([$categoryId, $name]);
        return ['success' => true, 'message' => 'Subcategory added.'];
    } catch (PDOException $e) {
        error_log('addSubcategory error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to add subcategory.'];
    }
}

function handleUploadImage(PDO $pdo): array
{
    global $ALLOWED_MIME;
    if (!isset($_FILES['image_file'])) return ['success' => false, 'message' => 'No file received.'];

    $file = $_FILES['image_file'];
    if ($file['error'] !== UPLOAD_ERR_OK) return ['success' => false, 'message' => 'File upload error code: ' . $file['error']];
    if ($file['size'] > MAX_UPLOAD_BYTES) return ['success' => false, 'message' => 'File too large. Max 10MB.'];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $ALLOWED_MIME, true)) return ['success' => false, 'message' => 'Invalid file type.'];

    $subcategoryId = getInputParam('subcategory_id');
    if (!$subcategoryId) return ['success' => false, 'message' => 'Missing subcategory_id'];

    try {
        $check = $pdo->prepare("SELECT COUNT(*) FROM image_subcategories WHERE id=?");
        $check->execute([$subcategoryId]);
        if ($check->fetchColumn() == 0) return ['success' => false, 'message' => 'Invalid subcategory_id'];
    } catch (PDOException $e) {
        error_log('uploadImage subcategory check failed: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Internal error'];
    }

    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $safe = preg_replace('/[^A-Za-z0-9\._-]/', '_', basename($file['name']));
    $unique = time() . '_' . bin2hex(random_bytes(4)) . '_' . $safe;
    $target = $uploadDir . $unique;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        error_log('move_uploaded_file failed for ' . $target);
        return ['success' => false, 'message' => 'Failed to save uploaded file.'];
    }

    $urlPath = '/cadetportal/uploads/' . $unique;

    try {
        $stmt = $pdo->prepare("INSERT INTO images (title,url,subcategory_id) VALUES (?,?,?)");
        $stmt->execute([$unique, $urlPath, $subcategoryId]);
        $newId = (int)$pdo->lastInsertId();
        return ['success' => true, 'message' => 'Image uploaded.', 'data' => ['id' => $newId, 'title' => $unique, 'url' => $urlPath, 'subcategory_id' => (int)$subcategoryId]];
    } catch (PDOException $e) {
        error_log('uploadImage DB insert failed: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to save image record.'];
    }
}

function handleDeleteImage(PDO $pdo): array
{
    $body = getJsonBody();
    $id = $_POST['id'] ?? $body['id'] ?? null;
    if (!$id) return ['success' => false, 'message' => 'Missing image id.'];

    try {
        $stmt = $pdo->prepare("SELECT url FROM images WHERE id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return ['success' => false, 'message' => 'Image not found.'];

        $url = $row['url'];
        $pdo->prepare("DELETE FROM images WHERE id=?")->execute([$id]);

        $filePath = realpath(__DIR__ . '/../' . ltrim($url, '/'));
        if ($filePath && strpos($filePath, realpath(__DIR__ . '/../uploads')) === 0 && file_exists($filePath)) @unlink($filePath);

        return ['success' => true, 'message' => 'Image deleted.'];
    } catch (PDOException $e) {
        error_log('deleteImage error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete image.'];
    }
}

function handleDeleteCategory(PDO $pdo): array
{
    $body = getJsonBody();
    $id = $_POST['id'] ?? $body['id'] ?? null;
    if (!$id) return ['success' => false, 'message' => 'Missing category id.'];

    try {
        $stmt = $pdo->prepare("SELECT i.url FROM images i JOIN image_subcategories s ON i.subcategory_id=s.id WHERE s.category_id=?");
        $stmt->execute([$id]);
        foreach ($stmt->fetchAll() as $row) {
            $filePath = realpath(__DIR__ . '/../' . ltrim($row['url'], '/'));
            if ($filePath && strpos($filePath, realpath(__DIR__ . '/../uploads')) === 0 && file_exists($filePath)) @unlink($filePath);
        }

        $pdo->prepare("DELETE FROM images WHERE subcategory_id IN (SELECT id FROM image_subcategories WHERE category_id=?)")->execute([$id]);
        $pdo->prepare("DELETE FROM image_subcategories WHERE category_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM image_categories WHERE id=?")->execute([$id]);

        return ['success' => true, 'message' => 'Category deleted.'];
    } catch (PDOException $e) {
        error_log('deleteCategory error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete category.'];
    }
}

function handleGetImages(PDO $pdo): array
{
    try {
        $sql = "
            SELECT i.id,i.title,i.url,
                   s.id AS sub_id,s.name AS sub_name,
                   c.id AS category_id,c.year
            FROM images i
            JOIN image_subcategories s ON i.subcategory_id=s.id
            JOIN image_categories c ON s.category_id=c.id
            ORDER BY c.year DESC,s.name ASC,i.id DESC
        ";
        $rows = $pdo->query($sql)->fetchAll();
        $flat = [];
        $grouped = [];
        foreach ($rows as $r) {
            $flat[] = [
                'id' => (int)$r['id'],
                'filename' => $r['title'],
                'url' => $r['url'],
                'category_name' => $r['sub_name'],
                'year_value' => $r['year']
            ];
            $year = $r['year'];
            $sub = $r['sub_name'];
            if (!isset($grouped[$year])) $grouped[$year] = [];
            if (!isset($grouped[$year][$sub])) $grouped[$year][$sub] = [];
            $grouped[$year][$sub][] = ['id' => (int)$r['id'], 'title' => $r['title'], 'url' => $r['url']];
        }
        return ['success' => true, 'data' => ['items' => $flat, 'grouped' => $grouped]];
    } catch (PDOException $e) {
        error_log('getImages error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to fetch images.'];
    }
}
