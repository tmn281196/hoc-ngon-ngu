<?php

declare(strict_types=1);

/**
 * Khung chung cho các trang zh-svo: doctype, <head>, canvas nền, thanh điều hướng.
 *
 * Cách dùng — đặt ở ngay đầu mỗi trang:
 *
 *     <?php
 *     $PAGE  = 'verbs';          // khớp khoá trong $NAV, để tô link hiện hành
 *     $TITLE = 'Động từ';     // tuỳ chọn, mặc định lấy nhãn trong $NAV
 *     require __DIR__ . '/chrome.php';
 *
 * Đóng lại bằng chrome-foot.php ở cuối trang. $NAV vừa là thanh điều hướng vừa
 * là danh sách trang: thêm một trang thì thêm đúng một dòng ở đây.
 */

require_once __DIR__ . '/data.php';

$NAV = [
    'index'     => 'Mục lục',
    'verbs'     => 'Động từ',
    'grammar'   => 'Theo ngữ pháp',
    'dialogue'  => 'Hội thoại',
    'reference' => 'Bảng tra nhãn',
];

$PAGE  = $PAGE  ?? 'index';
$TITLE = $TITLE ?? ($NAV[$PAGE] ?? 'Phân tích câu tiếng Trung');

?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= zs_e($TITLE) ?></title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&family=Newsreader:opsz,wght@6..72,400;6..72,600&display=swap">
<link rel="stylesheet" href="style.css">
</head>
<body>

<canvas id="field" aria-hidden="true"></canvas>

<div class="wrap">
  <nav class="pagenav" aria-label="Các trang">
<?php foreach ($NAV as $slug => $label): ?>
    <a href="<?= zs_e($slug) ?>.php"<?= $slug === $PAGE ? ' aria-current="page"' : '' ?>><?= zs_e($label) ?></a>
<?php endforeach; ?>
  </nav>
