<?php

declare(strict_types=1);

/**
 * Khối tiếng Anh: học theo khối, nhìn thấy khối.
 *
 * Dữ liệu nằm trong chunks.json (data.php nạp). Trang chỉ dựng khung; app.js
 * vẽ thẻ và tách câu thành khối.
 */

require __DIR__ . '/data.php';

$load  = chunks_load();
$data  = $load['data'];
$total = $data['total'] ?? 0;

function ck_e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Học tiếng Anh theo khối</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,600;1,6..72,400&display=swap">
<link rel="stylesheet" href="style.css">
</head>
<body>

<canvas id="field" aria-hidden="true"></canvas>

<div class="wrap">
  <header>
    <p class="eyebrow">Học tiếng Anh theo khối · <?= (int) $total ?> khối</p>
    <h1>Nói theo <em>khối</em>, không theo từng <i>từ</i></h1>
    <p class="lede">Người bản xứ không ráp câu từ từng từ rời. Họ với lấy nguyên cụm dựng sẵn như
      <span class="ex">the thing is</span>, <span class="ex">you know</span>,
      <span class="ex">to be honest</span>, dùng mỗi cụm như <strong>một đơn vị</strong>.
      Trí nhớ làm việc chỉ giữ được chừng <strong>7±2</strong> đơn vị một lúc, nên mỗi đơn vị càng lớn
      thì câu nói càng trôi. Trang này tách từng câu thành khối để bạn <em>thấy</em> điều đó: đổi qua lại
      giữa <b>Từ</b> và <b>Khối</b> rồi nhìn thước bên dưới mỗi câu co lại.</p>
    <details class="howto">
      <summary>Cách học</summary>
      <ul>
        <li>Đi theo thứ tự nhóm <strong>01 → 08</strong>; trình độ ghi cạnh mỗi nhóm chỉ là gợi ý. Mỗi ngày
          năm tới mười khối là đủ.</li>
        <li>Nghĩa tiếng Việt là cách nói gần nhất hằng ngày, không phải dịch từng chữ. Học nguyên câu tiếng Anh;
          tiếng Việt chỉ để kiểm tra mình hiểu đúng.</li>
        <li>Đọc to từng câu ví dụ, rồi tự đặt một câu về chính cuộc sống của mình.</li>
        <li>Nhận ra một khối chưa phải là biết dùng nó: chỉ coi là thuộc khi đã dùng nó thật, trong một cuộc
          trò chuyện hay một tin nhắn.</li>
      </ul>
    </details>
  </header>

<?php if (!$data): ?>
  <p class="empty">Không đọc được dữ liệu: <?= ck_e((string) $load['error']) ?></p>
<?php else: ?>

  <nav class="hub" id="hub" aria-label="Các nhóm khối, theo thứ tự học"></nav>

  <div class="controls" id="controls">
    <input id="q" type="search" placeholder="Tìm khối, nghĩa, ví dụ…  ( / )" autocomplete="off">
    <div class="seg" id="levels" role="group" aria-label="Trình độ">
      <button type="button" data-level="all">Mọi trình độ</button>
      <button type="button" data-level="A2">A2</button>
      <button type="button" data-level="B1">B1</button>
      <button type="button" data-level="B2">B2</button>
    </div>
    <div class="seg" id="mode" role="group" aria-label="Cách nhìn câu">
      <button type="button" data-mode="words">Từ</button>
      <button type="button" data-mode="chunks">Khối</button>
    </div>
    <span class="stat" id="stat"></span>
  </div>

  <div id="list"></div>
<?php endif; ?>

  <footer>
    Khối trong câu được dò tự động: chịu được chia động từ (<code>make</code> → <code>made</code>),
    chỗ trống <code>…</code>, phần tuỳ chọn <code>(at)</code>, biến thể <code>a / b</code> và
    <code>someone</code> / <code>your</code>. Khối một từ như <code>well</code>, <code>like</code> chỉ được tính khi đứng tách
    bằng dấu phẩy. Bấm vào một khối để tới thẻ của nó.
  </footer>
</div>

<script src="field.js"></script>
<?php if ($data): ?>
<script>window.CHUNK_DATA = <?= json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<script src="app.js"></script>
<?php endif; ?>
</body>
</html>
