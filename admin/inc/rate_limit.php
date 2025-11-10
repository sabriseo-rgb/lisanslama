<?php
function rate_limit($key, $limit = 5, $seconds = 60) {
    $file = sys_get_temp_dir() . "/rate_" . md5($key);
    $data = @json_decode(@file_get_contents($file), true) ?: ['count' => 0, 'time' => time()];
    if (time() - $data['time'] > $seconds) $data = ['count' => 0, 'time' => time()];
    $data['count']++;
    file_put_contents($file, json_encode($data));
    return $data['count'] > $limit;
}
