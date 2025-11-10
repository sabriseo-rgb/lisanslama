<?php
function make_license($type='FULL') {
    $token = strtoupper(bin2hex(random_bytes(8)));
    return ($type === 'FULL' ? 'FULL-' : 'LIMIT-') . $token;
}
