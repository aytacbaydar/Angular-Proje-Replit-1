
<?php
// Hata ayıklama
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CORS başlıkları
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// OPTIONS isteği için erken yanıt
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Veritabanı bağlantısı
require_once "../config.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Öğrenci ID'si kontrolü
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode(['success' => false, 'error' => 'Öğrenci ID gerekli']);
        exit;
    }
    
    $ogrenci_id = intval($_GET['id']);
    
    // Temel bilgileri al
    $sql = "SELECT * FROM ogrenciler WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$ogrenci_id]);
    $ogrenci = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ogrenci) {
        echo json_encode(['success' => false, 'error' => 'Öğrenci bulunamadı']);
        exit;
    }
    
    // Detay bilgileri al
    $sql = "SELECT * FROM ogrenci_bilgileri WHERE ogrenci_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$ogrenci_id]);
    $detay = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Şifreyi kaldır
    unset($ogrenci['sifre']);
    
    // Yanıt oluştur
    $response = [
        'success' => true,
        'data' => [
            'temel_bilgiler' => $ogrenci,
            'detay_bilgiler' => $detay ?: null
        ]
    ];
    
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'error' => 'Geçersiz istek metodu']);
}
