<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/utils.php';
check_auth();
require_once __DIR__ . '/templates/header.php';

$stats = [
    'total' => 0,
    'active' => 0,
    'passive' => 0,
    'full' => 0,
    'limited' => 0,
    'activations' => 0,
];

$statsQuery = "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) AS active_count,
        SUM(CASE WHEN active = 0 THEN 1 ELSE 0 END) AS passive_count,
        SUM(CASE WHEN type = 'FULL' THEN 1 ELSE 0 END) AS full_count,
        SUM(CASE WHEN type = 'LIMITED' THEN 1 ELSE 0 END) AS limited_count
    FROM license_keys";

if ($res = $mysqli->query($statsQuery)) {
    $row = $res->fetch_assoc();
    $stats['total'] = (int) ($row['total'] ?? 0);
    $stats['active'] = (int) ($row['active_count'] ?? 0);
    $stats['passive'] = (int) ($row['passive_count'] ?? 0);
    $stats['full'] = (int) ($row['full_count'] ?? 0);
    $stats['limited'] = (int) ($row['limited_count'] ?? 0);
}

if ($res = $mysqli->query('SELECT COUNT(*) AS total FROM license_activations')) {
    $row = $res->fetch_assoc();
    $stats['activations'] = (int) ($row['total'] ?? 0);
}

$recentLicenses = [];
$recentSql = "SELECT l.*, COUNT(a.id) AS activation_count
    FROM license_keys l
    LEFT JOIN license_activations a ON a.license_id = l.id
    GROUP BY l.id
    ORDER BY l.created_at DESC
    LIMIT 5";

if ($res = $mysqli->query($recentSql)) {
    while ($row = $res->fetch_assoc()) {
        $recentLicenses[] = $row;
    }
}
?>
<div class="container mt-5">
    <h2>📊 Lisans Sistemi Kontrol Paneli</h2>
    <p class="text-muted">Genel lisans durumunu hızlıca gözlemleyin.</p>

    <div class="row g-3 mt-4">
        <div class="col-md-4">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Toplam Lisans</h5>
                        <span class="fs-3">📦</span>
                    </div>
                    <p class="display-6 mt-3 mb-0"><?= number_format($stats['total']) ?></p>
                    <small class="text-muted">Sistemdeki toplam lisans sayısı</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Aktif Lisanslar</h5>
                        <span class="fs-3">✅</span>
                    </div>
                    <p class="display-6 mt-3 mb-0"><?= number_format($stats['active']) ?></p>
                    <small class="text-muted">Kullanımda olan lisanslar</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-danger shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Pasif Lisanslar</h5>
                        <span class="fs-3">⛔</span>
                    </div>
                    <p class="display-6 mt-3 mb-0"><?= number_format($stats['passive']) ?></p>
                    <small class="text-muted">İptal edilmiş lisanslar</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">FULL Lisanslar</h5>
                        <span class="fs-3">💼</span>
                    </div>
                    <p class="display-6 mt-3 mb-0"><?= number_format($stats['full']) ?></p>
                    <small class="text-muted">Tüm özellikleri aktif lisanslar</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-info shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">LIMITED Lisanslar</h5>
                        <span class="fs-3">🎯</span>
                    </div>
                    <p class="display-6 mt-3 mb-0"><?= number_format($stats['limited']) ?></p>
                    <small class="text-muted">Sınırlı yetkili lisanslar</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-secondary shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Aktivasyonlar</h5>
                        <span class="fs-3">🖥️</span>
                    </div>
                    <p class="display-6 mt-3 mb-0"><?= number_format($stats['activations']) ?></p>
                    <small class="text-muted">Cihaz bazlı toplam aktivasyon</small>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2 flex-wrap">
        <a href="licenses.php" class="btn btn-primary">📋 Tüm Lisanslar</a>
        <a href="generate.php" class="btn btn-success">➕ Yeni Lisans</a>
    </div>

    <div class="card mt-5 shadow-sm">
        <div class="card-header bg-dark text-white">Son Oluşturulan Lisanslar</div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Lisans</th>
                        <th>Sahip</th>
                        <th>Tür</th>
                        <th>Durum</th>
                        <th>Aktivasyon</th>
                        <th>Oluşturulma</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($recentLicenses): ?>
                    <?php foreach ($recentLicenses as $license): ?>
                        <tr>
                            <td><?= (int) $license['id'] ?></td>
                            <td><code><?= htmlspecialchars($license['license_key'], ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td><?= htmlspecialchars($license['owner'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($license['type'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= (int) $license['active'] === 1 ? '✅ Aktif' : '❌ Pasif' ?></td>
                            <td><?= (int) $license['activation_count'] ?> / <?= (int) $license['max_devices'] ?></td>
                            <td><?= htmlspecialchars($license['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Henüz lisans oluşturulmamış.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
