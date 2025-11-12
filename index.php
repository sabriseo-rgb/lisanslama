<?php
/**
 * Foto Albüm Lisans Sistemi - Ana Sayfa
 * Dinamik olarak admin/inc/config.php dosyasını bulur.
 * Daha profesyonel, kurumsal tasarım -- sade ve güven veren görünüm.
 */

$baseDir = __DIR__;
$paths = [
    $baseDir . '/admin/inc/config.php',
    $baseDir . '/../admin/inc/config.php',
];

$configFound = false;
foreach ($paths as $p) {
    if (file_exists($p)) {
        require_once $p;
        $configFound = true;
        break;
    }
}

if (!$configFound) {
    // Kurumsal, sade hata sayfası
    echo <<<HTML
    <!doctype html>
    <html lang="tr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Yapılandırma Bulunamadı - Foto Albüm Lisans Sistemi</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            :root{
                --bg: #f6f7f9;
                --card: #ffffff;
                --muted: #6b7280;
                --accent: #0b5ed7;
            }
            body{
                background: var(--bg);
                font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
                color: #0f172a;
                min-height: 100vh;
                display:flex;
                align-items:center;
                justify-content:center;
                padding:2rem;
            }
            .box{
                max-width:820px;
                width:100%;
                background:var(--card);
                border-radius:10px;
                box-shadow: 0 6px 24px rgba(15,23,42,0.06);
                padding:2.25rem;
                display:flex;
                gap:1.5rem;
                align-items:center;
            }
            .logo {
                width:96px;
                height:96px;
                border-radius:8px;
                background: linear-gradient(180deg,#e9eef9,#dfe8fb);
                display:flex;
                align-items:center;
                justify-content:center;
                font-weight:700;
                color:var(--accent);
                font-size:20px;
                flex-shrink:0;
            }
            .content h1{ margin:0 0 .25rem 0; font-size:1.25rem; }
            .muted { color: var(--muted); margin-bottom: .75rem; }
            .code-box{
                display:inline-block;
                background:#f3f4f6;
                color:#0f172a;
                padding: .35rem .55rem;
                border-radius:6px;
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, "Roboto Mono", "Courier New", monospace;
                font-size: .9rem;
            }
            .actions { margin-top:1rem; }
            footer { margin-top:1.25rem; color:var(--muted); font-size:0.88rem; }
            @media (max-width:720px){
                .box { flex-direction:column; text-align:center; }
                .logo { margin-bottom:.25rem; }
            }
        </style>
    </head>
    <body>
        <div class="box">
            <div class="logo" aria-hidden="true">ALB</div>
            <div class="content">
                <h1>Yapılandırma dosyası bulunamadı</h1>
                <p class="muted">Sistem gerekli yapılandırma dosyasını bulamadı. Uygulama düzgün çalışabilmesi için <span class="code-box">admin/inc/config.php</span> dosyasının doğru konumda ve erişilebilir olması gerekir.</p>

                <div class="actions">
                    <a href="./" class="btn btn-outline-primary btn-sm">Ana Sayfaya Dön</a>
                    <a href="admin/" class="btn btn-primary btn-sm ms-2">Yönetim Panelini Kontrol Et</a>
                </div>

                <footer>
                    <div>Yardım: lütfen dosyanın sunucuya yüklendiğini, izinlerin (chmod) ve yolun doğru olduğunu kontrol edin.</div>
                    <div style="margin-top:.35rem">Destek için: <a href="mailto:destek@dugunalbum.com">destek@dugunalbum.com</a></div>
                </footer>
            </div>
        </div>
    </body>
    </html>
    HTML;
    exit;
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Foto Albüm Lisans Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root{
            --bg: #f7f9fb;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #6b7280;
            --primary: #0b5ed7;
        }
        body{
            background: var(--bg);
            color:var(--text);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:2rem;
        }
        .panel {
            background:var(--card);
            width:100%;
            max-width:900px;
            border-radius:10px;
            padding:2rem;
            box-shadow: 0 8px 30px rgba(15,23,42,0.06);
        }
        .brand {
            display:flex;
            gap:1rem;
            align-items:center;
        }
        .brand .logo {
            width:56px;
            height:56px;
            border-radius:8px;
            background:#eef6ff;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            color:var(--primary);
            font-weight:700;
            font-size:18px;
        }
        .brand h2 { margin:0; font-size:18px; font-weight:600; }
        .description { color:var(--muted); margin-top:.4rem; }
        .grid { display:grid; grid-template-columns: 1fr 320px; gap:1.25rem; margin-top:1.25rem; }
        .card-block {
            background:#fbfdff;
            border: 1px solid #eef2ff;
            border-radius:8px;
            padding:1rem;
        }
        .login-box {
            border-radius:8px;
            padding:1rem;
        }
        .login-box .btn { width:100%; }
        .support { margin-top:1rem; color:var(--muted); font-size:0.9rem; }
        @media (max-width:880px){
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <section class="panel">
        <div class="brand">
            <div class="logo" aria-hidden="true">ALB</div>
            <div>
                <h2>Foto Albüm Lisans Sistemi</h2>
                <div class="description">Lisans yönetimi ve doğrulama servisiniz. Güvenli ve merkezi lisans kontrolleri sağlar.</div>
            </div>
        </div>

        <div class="grid">
            <div>
                <div class="card-block">
                    <h4 style="margin-top:0">Sistem Hakkında</h4>
                    <p class="muted">Sunucunuz üzerindeki PHP tabanlı lisans sistemi; istemcileriniz (ör. Python uygulamaları) için lisans doğrulama, kayıt ve yönetim imkânı sağlar. Yönetim panelinden lisans, anahtar ve rapor yönetimini gerçekleştirebilirsiniz.</p>

                    <h5 style="margin-top:1rem">Öne çıkan özellikler</h5>
                    <ul style="margin-top:.5rem; color:var(--muted);">
                        <li>Merkezi lisans doğrulama API</li>
                        <li>Rol tabanlı yönetim (admin)</li>
                        <li>Güvenli konfigürasyon ve loglama</li>
                        <li>SMTP ile bildirim/uyarı desteği</li>
                    </ul>

                    <div class="support">
                        Destek: <a href="mailto:destek@dugunalbum.com">destek@dugunalbum.com</a> · Dokümantasyon: <a href="docs/">docs/</a>
                    </div>
                </div>
            </div>

            <aside>
                <div class="card-block login-box">
                    <h6 style="margin:0 0 .5rem 0">Yönetim Girişi</h6>
                    <p style="margin:0 0 1rem 0; color:var(--muted); font-size:.95rem">Yönetim paneline erişim için giriş yapın.</p>
                    <a href="admin/login.php" class="btn btn-primary mb-2">Yönetim Paneli</a>
                    <a href="admin/install_check.php" class="btn btn-outline-secondary">Kurulum Kontrolleri</a>
                </div>
            </aside>
        </div>
    </section>
</body>
</html>
