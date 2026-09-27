<?php

declare(strict_types=1);

/**
 * Học tiếng Trung theo từ: đọc theo từ, không theo từng chữ.
 *
 * Tiếng Trung viết liền, không có dấu cách: mắt phải tự gom chữ thành từ.
 * Trang này làm việc đó ra cho thấy: mỗi câu tách thành từ, mỗi chữ mang
 * pinyin của nó, đổi qua lại Chữ / Từ và nhìn thước bên dưới co lại.
 *
 * Dữ liệu dựng bởi lib/hanyu.php (đọc giáo trình HSK1, tách từ, căn pinyin),
 * lưu đệm trong .cache.json cạnh file này. Trang chỉ dựng khung; app.js vẽ
 * thẻ và câu.
 */

require dirname(__DIR__, 2) . '/lib/hanyu.php';

$load = hy_load(__DIR__ . '/.cache.json');
$data = $load['data'];

function zc_e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$payload = null;
$words   = 0;
if ($data) {
    // Chỉ gửi những gì trang dùng: từ điển rút gọn [pinyin từng âm tiết, nghĩa, STT trong 500 từ].
    $dict = [];
    foreach ($data['dict'] as $w => $e) {
        $dict[$w] = [$e['ps'] ?? '', $e['vi'] ?? '', $e['n'] ?? 0];
    }
    $groups = array_values(array_filter($data['groups'], fn($g) => in_array($g['kind'], ['lesson', 'practice', 'verbs'], true)));
    foreach ($groups as $g) {
        $words += count($g['words']);
    }
    $payload = ['groups' => $groups, 'sents' => $data['sents'], 'dict' => $dict, 'charPy' => $data['charPy']];
}
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Học tiếng Trung theo từ</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,600;1,6..72,400&display=swap">
<link rel="stylesheet" href="style.css">
</head>
<body>

<canvas id="field" aria-hidden="true"></canvas>

<div class="wrap">
  <header>
    <p class="eyebrow">Học tiếng Trung theo từ · <?= (int) $words ?> từ</p>
    <h1>Đọc theo <em>từ</em>, không theo từng <i>chữ</i></h1>
    <p class="lede">Tiếng Trung viết liền một mạch, không có dấu cách: <span class="ex" lang="zh">我是你的朋友</span>
      là sáu chữ, nhưng chỉ có năm từ, và mắt phải tự gom <span class="ex" lang="zh">朋</span> với
      <span class="ex" lang="zh">友</span> thành <span class="ex" lang="zh">朋友</span>. Trí nhớ làm việc giữ được chừng
      <strong>7±2</strong> đơn vị một lúc; đọc theo từ thì mỗi đơn vị gánh được nhiều chữ hơn. Trang này tách từng câu
      thành từ, mỗi chữ mang pinyin của nó. Đổi qua lại giữa <b>Chữ</b> và <b>Từ</b> rồi nhìn thước bên dưới mỗi câu
      co lại.</p>
    <details class="howto">
      <summary>Cách học</summary>
      <ul>
        <li>Người mới bắt đầu đi theo <strong>15 bài</strong> của giáo trình; từ trong mỗi bài đi kèm câu hội thoại của chính bài đó.</li>
        <li><strong>500 từ thông dụng</strong> xếp theo 18 mục ngữ pháp: mỗi từ đứng trong một câu luyện của mục mà nó xuất hiện lần đầu.
          Mục 15–18 là phần mở rộng chung HSK2–4; từ mục 19 là các điểm ngữ pháp HSK2 tới HSK6, học xong HSK1 rồi hẵng mở.</li>
        <li><strong>Động từ</strong>: 50 động từ cơ bản HSK1 chia 5 nhóm theo công dụng, thêm hàng trăm câu với động từ thông dụng HSK2–4;
          mỗi động từ đi kèm câu đặt sẵn của chính nó.</li>
        <li>Pinyin trên đầu chữ đã biến điệu đúng như khi đọc: <span lang="zh">不是</span> đọc <em>bú shì</em>,
          <span lang="zh">一个</span> đọc <em>yí gè</em>.</li>
        <li>Đọc to cả câu, rồi che pinyin đi đọc lại; nghĩa tiếng Việt chỉ để kiểm tra mình hiểu đúng.</li>
      </ul>
    </details>
  </header>

<?php if (!$data): ?>
  <p class="empty">Không đọc được dữ liệu: <?= zc_e((string) $load['error']) ?></p>
<?php else: ?>

  <nav class="hub" id="hub" aria-label="Các nhóm từ"></nav>

  <div class="controls" id="controls">
    <input id="q" type="search" placeholder="Tìm chữ Hán, pinyin, nghĩa…  ( / )" autocomplete="off">
    <div class="seg" id="levels" role="group" aria-label="Trình độ">
      <button type="button" data-level="all">Mọi trình độ</button>
      <button type="button" data-level="HSK1">HSK1</button>
      <button type="button" data-level="HSK2">HSK2</button>
      <button type="button" data-level="HSK3">HSK3</button>
      <button type="button" data-level="HSK4">HSK4</button>
      <button type="button" data-level="HSK5">HSK5</button>
      <button type="button" data-level="HSK6">HSK6</button>
    </div>
    <div class="seg" id="mode" role="group" aria-label="Cách nhìn câu">
      <button type="button" data-mode="chars">Chữ</button>
      <button type="button" data-mode="words">Từ</button>
    </div>
    <span class="stat" id="stat"></span>
  </div>

  <div id="list"></div>
<?php endif; ?>

  <footer>
    Câu được tách thành từ theo từ vựng đã đánh dấu trong câu, phần còn lại theo từ điển của giáo trình (khớp từ dài nhất).
    Pinyin từng chữ lấy từ chính câu đó nên giữ đúng biến điệu. Bấm vào một từ trong câu ví dụ để tới thẻ của nó.
  </footer>
</div>

<script src="field.js"></script>
<?php if ($payload): ?>
<script>window.ZH_DATA = <?= json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE) ?>;</script>
<script src="app.js"></script>
<?php endif; ?>
</body>
</html>
