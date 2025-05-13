<?php
// Gelen POST ve FILES verilerini logla
file_put_contents("log_php_post.txt", "POST:\n" . print_r($_POST, true) . "\nFILES:\n" . print_r($_FILES, true));

// Hataları anında göstermek için
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CORS başlıkları (isteğe bağlı, tarayıcı hatası alırsanız açın)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// OPTIONS isteğini yönet (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require '../../baglanti.php';

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adi_soyadi = $_POST['adi_soyadi'];
    $cep_telefonu = $_POST['cep_telefonu'];
    $email = $_POST['email'];
    $sifre = $_POST['sifre'];
    $sifremd5 = md5($sifre); 
    $gercek_sifre = $sifre; 
    $rutbe = isset($_POST['rutbe']) ? $_POST['rutbe'] : 'yeni';
    $kurum = isset($_POST['kurum']) ? $_POST['kurum'] : 'yok';
    $aktiflik_durumu = isset($_POST['aktiflik_durumu']) && $_POST['aktiflik_durumu'] === 'true' ? 1 : 0;
    
    $avatar = '';

    // Resmi yeniden boyutlandırma fonksiyonu
    function resizeImage($sourcePath, $destinationPath, $maxWidth, $maxHeight) {
        list($width, $height, $type) = getimagesize($sourcePath);
        $ratio = $width / $height;

        if ($width > $maxWidth || $height > $maxHeight) {
            if ($maxWidth / $maxHeight > $ratio) {
                $maxWidth = $maxHeight * $ratio;
            } else {
                $maxHeight = $maxWidth / $ratio;
            }
        } else {
            // Uygun boyuttaysa küçültme yapmadan dosyayı kopyala
            copy($sourcePath, $destinationPath);
            return;
        }

        $srcImage = imagecreatefromstring(file_get_contents($sourcePath));
        $dstImage = imagecreatetruecolor($maxWidth, $maxHeight);

        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $maxWidth, $maxHeight, $width, $height);

        // JPEG olarak kaydet
        imagejpeg($dstImage, $destinationPath, 90);

        imagedestroy($srcImage);
        imagedestroy($dstImage);
    }

    // Form doğrulama
    if (empty($adi_soyadi)) {
        $response = [
            'success' => false,
            'message' => 'İsim alanı boş bırakılamaz'
        ];
        echo json_encode($response);
        exit;
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = [
            'success' => false,
            'message' => 'Geçerli bir email adresi girin'
        ];
        echo json_encode($response);
        exit;
    }

    if (empty($sifre) || strlen($sifre) < 6) {
        $response = [
            'success' => false,
            'message' => 'Şifre en az 6 karakter olmalıdır'
        ];
        echo json_encode($response);
        exit;
    }

    // Email benzersizliğini kontrol et
    $check_email = $conn->query("SELECT COUNT(*) as count FROM kullanicilar WHERE email = '$email'");
    $email_result = $check_email->fetch_assoc();
    
    if ($email_result['count'] > 0) {
        $response = [
            'success' => false,
            'message' => 'Bu email adresi ile kayıtlı bir kullanıcı zaten mevcut'
        ];
        echo json_encode($response);
        exit;
    }

    // Avatar dosyasını yükle
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../../../avatar/";
        $target_avatar = "avatar/";

        // Eğer klasör yoksa oluştur
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                $response = [
                    'success' => false,
                    'message' => 'Uploads klasörü oluşturulamadı!'
                ];
                echo json_encode($response);
                exit;
            }
        }

        $target_file = $target_dir . basename($_FILES["avatar"]["name"]);
        $resized_file = $target_dir .  $adi_soyadi . "_" . basename($_FILES["avatar"]["name"]);
        $avatar_adi =  $target_avatar . $adi_soyadi . "_" . basename($_FILES["avatar"]["name"]);

        // Dosya yükleme işlemi
        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
            // Yüklenen dosyayı küçült
            resizeImage($target_file, $resized_file, 150, 150);
            unlink($target_file); // Orijinal dosyayı sil
            $avatar = $avatar_adi; // Küçültülen dosya yolunu al
        } else {
            $response = [
                'success' => false,
                'message' => 'Avatar yüklenemedi! Lütfen dosya izinlerini kontrol edin.'
            ];
            echo json_encode($response);
            exit;
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'Profil resmi yüklemek zorunludur'
        ];
        echo json_encode($response);
        exit;
    }

    // Kullanıcıyı veritabanına kaydet
    $sql = "INSERT INTO kullanicilar (adi_soyadi, email, cep_telefonu, gercek_sifre, sifre, rutbe, kurum, aktiflik_durumu, avatar)
            VALUES ('$adi_soyadi', '$email',  '$cep_telefonu', '$gercek_sifre', '$sifremd5', '$rutbe', '$kurum', '$aktiflik_durumu', '$avatar')";

   if ($conn->query($sql) === TRUE) {
        $response = [
            'success' => true,
            'message' => 'Kullanıcı başarıyla kaydedildi!',
            'data' => [
                'adi_soyadi' => $adi_soyadi,
                'email' => $email,
                'rutbe' => $rutbe,
                'aktiflik_durumu' => $aktiflik_durumu,
                'avatar' => $avatar
            ]
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'SQL Hatası: ' . $conn->error
        ];
    }

    echo json_encode($response);
} else {
    $response = [
        'success' => false,
        'message' => 'Sadece POST istekleri kabul edilir'
    ];
    echo json_encode($response);
}
?>