<?php
// 关闭错误显示（防止污染图片数据）
error_reporting(0);

require 'connect.php';

// 验证参数
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("HTTP/1.0 400 Bad Request");
    exit("Invalid ID");
}

$id = intval($_GET['id']);

try {
    // 查询图片数据
    $stmt = $conn->prepare("SELECT image FROM school WHERE sid = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['image']) {
            $imageData = $row['image'];
            
            // 自动检测图片类型
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageData);
            
            // 清理输出缓冲区
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // 设置 HTTP 响应头（关键！）
            header("Content-Type: " . $mimeType);
            header("Content-Length: " . strlen($imageData));
            header("Cache-Control: public, max-age=86400"); // 缓存1天
            
            // 输出图片数据
            echo $imageData;
            exit;
        }
    }
    
    // 图片不存在
    header("HTTP/1.0 404 Not Found");
    exit("Image not found");
    
} catch (Exception $e) {
    header("HTTP/1.0 500 Internal Server Error");
    exit("Error loading image");
}
?>
