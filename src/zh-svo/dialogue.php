<?php
$PAGE  = 'dialogue';
require __DIR__ . '/chrome.php';
$load  = zs_data();
$data  = $load['data'];
?>
  <header>
    <p class="eyebrow">Phân tích câu tiếng Trung · 03</p>
    <h1><i>Hội thoại</i>, từng lượt lời một</h1>
    <p class="lede">Câu nói thật thì ngắn và hay bỏ bớt: <span lang="zh">明天呢？</span> không có động từ nào,
      <span lang="zh">去学校。</span> không có chủ ngữ. Chọn một bài ở thanh bên, chọn đoạn hội thoại, rồi lật từng
      lượt lời; A, B là người nói. Mỗi bài còn một mục <strong>Ngữ pháp trong bài</strong>: các câu ví dụ của chính
      điểm ngữ pháp bài đó dạy.</p>
  </header>

<?php if (!$data): ?>
  <p class="empty">Không đọc được dữ liệu: <?= zs_e((string) $load['error']) ?></p>
<?php else: ?>
<?php require __DIR__ . '/board.php'; ?>
<?php endif; ?>
<?php
$DATA_VARS = $data ? ['TOPICS' => $data['dialogue']] : [];
$PAGE_CFG  = $data ? '{}' : null;
require __DIR__ . '/chrome-foot.php';
