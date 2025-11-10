# Foto Albüm Lisans Sistemi

Bu depo, foto albüm istemcileri için lisans doğrulaması yapan PHP tabanlı bir sunucu uygulamasını içerir.

## Kurulum

1. `admin/inc/config.local.php` dosyasını oluşturun ve gerçek ortam değerlerinizi bu dosyada tanımlayın. Örnek olarak `admin/inc/config.php.example.php` içindeki sabitlerden yararlanabilirsiniz.
   - Alternatif olarak ortam değişkenleri (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_PORT`, `SECRET_KEY`, `BASE_URL`) ile yapılandırma sağlayabilirsiniz.
2. `init_db.sql` betiğini çalıştırarak gerekli tabloları oluşturun.
3. Web sunucunuzu proje kök dizinine işaret edecek şekilde yapılandırın.

## Güvenlik Notları

- Yönetici paneli ve lisans kontrol uç noktası CSRF ve imza doğrulamaları ile korunmaktadır. İmzalama anahtarınızı (SECRET_KEY) güvenli bir yerde saklayın.
- Üçten fazla başarısız giriş denemesini 5 dakika boyunca engelleyen basit bir hız sınırlayıcı uygulanmıştır.
- Lisanslar cihaz sınırlarına uyması için takip edilir; aynı cihaz tekrar doğrulanırsa aktivasyon kaydı güncellenir.

## Geliştirme

- PHP 8.1 veya üstü tavsiye edilir.
- Yeni bir yönetici oluşturmak için `admins` tablosuna `password_hash()` ile üretilmiş şifreler ekleyin.
