<?php

declare(strict_types=1);

/**
 * Khung chung cho bốn trang en-svo: doctype, <head>, canvas nền, thanh điều hướng.
 *
 * Trước đây bốn trang chép tay cùng một đoạn đầu. Chúng đã lệch nhau thật: hai
 * file runbook bên requirements/ từng thiếu doctype và chạy ở quirks mode suốt
 * mà không ai biết. Gom về một chỗ thì sửa một lần là cả bốn trang cùng đúng.
 *
 * Cách dùng — đặt ở ngay đầu mỗi trang:
 *
 *     <?php
 *     $PAGE  = 'omnibus';                 // khớp khoá trong $NAV, để tô link hiện hành
 *     $TITLE = 'Động từ đặc biệt';        // tuỳ chọn, mặc định lấy nhãn trong $NAV
 *     require __DIR__ . '/chrome.php';
 *     ?>
 *
 * Đóng lại bằng chrome-foot.php ở cuối trang.
 *
 * $NAV vừa là thanh điều hướng vừa là danh sách trang: thêm một trang thì thêm
 * đúng một dòng ở đây, không phải sửa bốn file.
 */

$NAV = [
    'index'     => 'Mục lục',
    'omnibus'   => 'Động từ đặc biệt',
    'topics'    => 'Theo ngữ nghĩa',
    'reference' => 'Bảng tra nhãn',
];

$PAGE  = $PAGE  ?? 'index';
$TITLE = $TITLE ?? ($NAV[$PAGE] ?? 'Phân tích câu tiếng Anh');

if (!function_exists('svo_e')) {
    function svo_e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= svo_e($TITLE) ?></title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&family=Newsreader:opsz,wght@6..72,400;6..72,600&display=swap">
<link rel="stylesheet" href="style.css">
</head>
<body>

<canvas id="field" aria-hidden="true"></canvas>

<div class="wrap">
  <nav class="pagenav" aria-label="Các trang">
<?php foreach ($NAV as $slug => $label): ?>
    <a href="<?= svo_e($slug) ?>.php"<?= $slug === $PAGE ? ' aria-current="page"' : '' ?>><?= svo_e($label) ?></a>
<?php endforeach; ?>
  </nav>
