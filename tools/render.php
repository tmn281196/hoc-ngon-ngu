<?php

declare(strict_types=1);

// Chạy đúng một trang PHP như máy chủ web sẽ chạy, in HTML ra stdout.
// build.php gọi file này cho từng trang: php tools/render.php src/zh-svo/grammar.php

$page = realpath($argv[1] ?? '');
if ($page === false) {
    fwrite(STDERR, "Không thấy trang: " . ($argv[1] ?? '') . "\n");
    exit(1);
}
chdir(dirname($page));
require $page;
