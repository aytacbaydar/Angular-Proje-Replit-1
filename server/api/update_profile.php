<?php
// Öğrenci profil güncelleme API'si
require_once '../config.php';

// Sadece POST isteklerine izin ver
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Sadece POST metoduna izin verilmektedir', 405);
}

try {
    // Kullanıcıyı doğrula
    $user = authorize();
    
    // JSON verilerini al
    $data = getJsonData();
    
    // Öğrenci ID'sini belirle
    $studentId = isset($data['id']) ? intval($data['id']) : $user['id'];
    
    // Admin değilse, sadece kendi bilgilerini güncelleyebilir
    if ($user['rutbe'] !== 'admin' && $studentId !== $user['id']) {
        errorResponse('Bu bilgileri güncelleme yetkiniz yok', 403);
    }
    
    $conn = getConnection();
    
    // İşlemi transaction içinde yap
    $conn->beginTransaction();
    
    // Temel öğrenci bilgilerini güncelle
    if (isset($data['temel_bilgiler']) && !empty($data['temel_bilgiler'])) {
        $temelBilgiler = $data['temel_bilgiler'];
        
        $updateFields = [];
        $params = [];
        
        // Güncellenebilir alanlar
        $allowedFields = ['adi_soyadi', 'cep_telefonu', 'avatar'];
        
        // Admin ise ekstra alanları güncelleyebilir
        if ($user['rutbe'] === 'admin') {
            $allowedFields = array_merge($allowedFields, ['rutbe', 'aktif', 'email']);
        }
        
        foreach ($allowedFields as $field) {
            if (isset($temelBilgiler[$field])) {
                $updateFields[] = "$field = :$field";
                $params[":$field"] = $temelBilgiler[$field];
            }
        }
        
        // Şifre değişikliği varsa
        if (isset($temelBilgiler['sifre']) && !empty($temelBilgiler['sifre'])) {
            // Şifre uzunluğunu kontrol et
            if (strlen($temelBilgiler['sifre']) < 6) {
                $conn->rollBack();
                errorResponse('Şifre en az 6 karakter olmalıdır');
            }
            
            $updateFields[] = "sifre = :sifre";
            $params[':sifre'] = md5($temelBilgiler['sifre']);
        }
        
        if (!empty($updateFields)) {
            $sql = "UPDATE ogrenciler SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $params[':id'] = $studentId;
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        }
    }
    
    // Detay bilgilerini güncelle
    if (isset($data['detay_bilgiler']) && !empty($data['detay_bilgiler'])) {
        $detayBilgiler = $data['detay_bilgiler'];
        
        $updateFields = [];
        $params = [];
        
        // Güncellenebilir alanlar
        $allowedFields = ['okulu', 'sinifi', 'grubu', 'ders_gunu', 'ders_saati', 'ucret', 'veli_adi', 'veli_cep'];
        
        foreach ($allowedFields as $field) {
            if (isset($detayBilgiler[$field])) {
                $updateFields[] = "$field = :$field";
                $params[":$field"] = $detayBilgiler[$field];
            }
        }
        
        if (!empty($updateFields)) {
            // Önce kayıt var mı kontrol et
            $stmt = $conn->prepare("SELECT id FROM ogrenci_bilgileri WHERE ogrenci_id = :ogrenci_id");
            $stmt->bindParam(':ogrenci_id', $studentId);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Güncelle
                $sql = "UPDATE ogrenci_bilgileri SET " . implode(', ', $updateFields) . " WHERE ogrenci_id = :ogrenci_id";
                $params[':ogrenci_id'] = $studentId;
                
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
            } else {
                // Oluştur
                $fields = array_keys($params);
                $fieldNames = str_replace(':', '', implode(', ', $fields)) . ', ogrenci_id';
                $placeholders = implode(', ', $fields) . ', :ogrenci_id';
                
                $sql = "INSERT INTO ogrenci_bilgileri ($fieldNames) VALUES ($placeholders)";
                $params[':ogrenci_id'] = $studentId;
                
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
            }
        }
    }
    
    // Transaction'ı commit et
    $conn->commit();
    
    // Güncellenmiş bilgileri getir
    $stmt = $conn->prepare("
        SELECT o.id, o.adi_soyadi, o.email, o.cep_telefonu, o.rutbe, o.aktif, o.avatar,
               ob.okulu, ob.sinifi, ob.grubu, ob.ders_gunu, ob.ders_saati, ob.ucret, 
               ob.veli_adi, ob.veli_cep
        FROM ogrenciler o
        LEFT JOIN ogrenci_bilgileri ob ON o.id = ob.ogrenci_id
        WHERE o.id = :id
    ");
    $stmt->bindParam(':id', $studentId);
    $stmt->execute();
    
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Admin ise yeni token üretme
    if ($user['rutbe'] === 'admin' && $studentId !== $user['id']) {
        successResponse($student, 'Profil başarıyla güncellendi');
    } else {
        // Yeni token oluştur (şifre değişmişse)
        $stmt = $conn->prepare("SELECT sifre FROM ogrenciler WHERE id = :id");
        $stmt->bindParam(':id', $studentId);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $token = md5($studentId . $student['email'] . $userData['sifre']);
        $student['token'] = $token;
        
        successResponse($student, 'Profil başarıyla güncellendi');
    }
    
} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    errorResponse('Veritabanı hatası: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    errorResponse('Beklenmeyen bir hata oluştu: ' . $e->getMessage(), 500);
}
