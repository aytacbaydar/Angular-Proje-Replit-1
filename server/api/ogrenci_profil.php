
<?php
// Hata ayıklama
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CORS başlıkları
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
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
    
    // 1. TEMEL BİLGİLERİ GÜNCELLE
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

    // 2. DETAY BİLGİLERİ GÜNCELLE
    if (isset($data['detay_bilgiler']) && !empty($data['detay_bilgiler'])) {
        $detay_bilgiler = $data['detay_bilgiler'];
        
        // Önce detay bilgilerinin var olup olmadığını kontrol et
        $sql = "SELECT COUNT(*) FROM ogrenci_bilgileri WHERE ogrenci_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$ogrenci_id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            // Varsa, güncelle
            $sql = "UPDATE ogrenci_bilgileri SET ";
            $params = [];
            $updateFields = [];
            
            // Avatar işleme
            if (isset($detay_bilgiler['avatar']) && !empty($detay_bilgiler['avatar'])) {
                $updateFields[] = "avatar = ?";
                $params[] = $detay_bilgiler['avatar'];
            }
            
            // Diğer detay alanlar
            if (isset($detay_bilgiler['telefon'])) {
                $updateFields[] = "telefon = ?";
                $params[] = $detay_bilgiler['telefon'];
            }
            
            if (isset($detay_bilgiler['sinif'])) {
                $updateFields[] = "sinif = ?";
                $params[] = $detay_bilgiler['sinif'];
            }
            
            if (isset($detay_bilgiler['okul'])) {
                $updateFields[] = "okul = ?";
                $params[] = $detay_bilgiler['okul'];
            }
            
            if (isset($detay_bilgiler['adres'])) {
                $updateFields[] = "adres = ?";
                $params[] = $detay_bilgiler['adres'];
            }
            
            if (isset($detay_bilgiler['dogum_tarihi'])) {
                $updateFields[] = "dogum_tarihi = ?";
                $params[] = $detay_bilgiler['dogum_tarihi'];
            }
            
            // Eğer güncellenecek alan varsa
            if (!empty($updateFields)) {
                $sql .= implode(", ", $updateFields);
                $sql .= " WHERE ogrenci_id = ?";
                $params[] = $ogrenci_id;
                
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
            }
        } else {
            // Yoksa, oluştur
            $fields = ["ogrenci_id"];
            $values = ["?"];
            $params = [$ogrenci_id];
            
            // Avatar işleme
            if (isset($detay_bilgiler['avatar']) && !empty($detay_bilgiler['avatar'])) {
                $fields[] = "avatar";
                $values[] = "?";
                $params[] = $detay_bilgiler['avatar'];
            }
            
            // Diğer detay alanlar
            if (isset($detay_bilgiler['telefon'])) {
                $fields[] = "telefon";
                $values[] = "?";
                $params[] = $detay_bilgiler['telefon'];
            }
            
            if (isset($detay_bilgiler['sinif'])) {
                $fields[] = "sinif";
                $values[] = "?";
                $params[] = $detay_bilgiler['sinif'];
            }
            
            if (isset($detay_bilgiler['okul'])) {
                $fields[] = "okul";
                $values[] = "?";
                $params[] = $detay_bilgiler['okul'];
            }
            
            if (isset($detay_bilgiler['adres'])) {
                $fields[] = "adres";
                $values[] = "?";
                $params[] = $detay_bilgiler['adres'];
            }
            
            if (isset($detay_bilgiler['dogum_tarihi'])) {
                $fields[] = "dogum_tarihi";
                $values[] = "?";
                $params[] = $detay_bilgiler['dogum_tarihi'];
            }
            
            $sql = "INSERT INTO ogrenci_bilgileri (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $values) . ")";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        }
    }
    
    // 3. GÜNCELLENMİŞ VERİLERİ DÖNDÜR
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
            'detay_bilgiler' => $detay ?: []
        ],
        'message' => 'Profil başarıyla güncellendi'
    ];
    
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'error' => 'Geçersiz istek metodu']);
}
