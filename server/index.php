<?php
// Ana giriş noktası - API isteklerini uygun dosyalara yönlendirme
require_once 'config.php';

// URL'den endpoint'i çıkar
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/server/api/';

// /server/api/ ile başlayan URL'leri işle
if (strpos($requestUri, $basePath) === 0) {
    $endpoint = substr($requestUri, strlen($basePath));
    $endpoint = strtok($endpoint, '?'); // Query parametrelerini kaldır
    
    // Endpoint'e göre yönlendirme
    switch ($endpoint) {
        case 'register':
            require_once 'api/register.php';
            break;
            
        case 'login':
            require_once 'api/login.php';
            break;
            
        case 'student':
            require_once 'api/student.php';
            break;
            
        case 'update_profile':
            require_once 'api/update_profile.php';
            break;
            
        case 'admin':
            require_once 'api/admin.php';
            break;
            
        case 'upload':
            require_once 'api/upload.php';
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'API endpoint bulunamadı']);
            break;
    }
} else {
    // API dışındaki istekler için 404 dön
    http_response_code(404);
    echo json_encode(['error' => 'Sayfa bulunamadı']);
}
