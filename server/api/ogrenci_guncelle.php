
<?php
// Hata ayıklama
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CORS başlıkları
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// OPTIONS isteği için erken yanıt
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Veritabanı bağlantısı
require_once "../config.php";

// JSON gövdesini al
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kullanıcı kimliği kontrol et
    if (!isset($data['ogrenci_id']) || empty($data['ogrenci_id'])) {
        echo json_encode(['success' => false, 'error' => 'Öğrenci ID gerekli']);
        exit;
    }

    $ogrenci_id = intval($data['ogrenci_id']);
    
    // Temel bilgileri güncelle
    if (isset($data['temel_bilgiler']) && !empty($data['temel_bilgiler'])) {
        $temel_bilgiler = $data['temel_bilgiler'];
        
        $sql = "UPDATE ogrenciler SET ";
        $params = [];
        $updateFields = [];
        
        // Hangi alanların güncelleneceğini belirle
        if (isset($temel_bilgiler['adi_soyadi']) && !empty($temel_bilgiler['adi_soyadi'])) {
            $updateFields[] = "adi_soyadi = ?";
            $params[] = $temel_bilgiler['adi_soyadi'];
        }
        
        if (isset($temel_bilgiler['email']) && !empty($temel_bilgiler['email'])) {
            $updateFields[] = "email = ?";
            $params[] = $temel_bilgiler['email'];
        }
        
        if (isset($temel_bilgiler['sifre']) && !empty($temel_bilgiler['sifre'])) {
            $updateFields[] = "sifre = ?";
            $params[] = password_hash($temel_bilgiler['sifre'], PASSWORD_DEFAULT);
        }
        
        if (isset($temel_bilgiler['rutbe'])) {
            $updateFields[] = "rutbe = ?";
            $params[] = $temel_bilgiler['rutbe'];
        }
        
        if (isset($temel_bilgiler['aktif'])) {
            $updateFields[] = "aktif = ?";
            $params[] = $temel_bilgiler['aktif'] ? 1 : 0;
        }
        
        // Eğer güncellenecek alan varsa
        if (!empty($updateFields)) {
            $sql .= implode(", ", $updateFields);
            $sql .= " WHERE id = ?";
            $params[] = $ogrenci_id;
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        }
    }

    // Güncellenmiş verileri döndür
    $sql = "SELECT * FROM ogrenciler WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$ogrenci_id]);
    $ogrenci = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ogrenci) {
        echo json_encode(['success' => false, 'error' => 'Öğrenci bulunamadı']);
        exit;
    }
    
    // Şifreyi kaldır
    unset($ogrenci['sifre']);
    
    // Yanıt oluştur
    $response = [
        'success' => true,
        'data' => $ogrenci,
        'message' => 'Öğrenci bilgileri başarıyla güncellendi'
    ];
    
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'error' => 'Geçersiz istek metodu']);
}
