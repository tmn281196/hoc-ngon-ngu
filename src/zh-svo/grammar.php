<?php
$PAGE  = 'grammar';
require __DIR__ . '/chrome.php';
$load  = zs_data();
$data  = $load['data'];
?>
  <header>
    <p class="eyebrow">Phân tích câu tiếng Trung · 02</p>
    <h1>Câu luyện, xếp <i>theo ngữ pháp</i></h1>
    <p class="lede">Mỗi mục là một điểm ngữ pháp: <span lang="zh">是</span> nối hai danh từ, <span lang="zh">在</span>
      vừa là động từ vừa là giới từ, <span lang="zh">了</span> đứng sau động từ hay cuối câu. Mở một mục ở thanh bên rồi
      lật từng câu bằng nút dưới sân hoặc danh sách bên dưới; nhãn cạnh bộ đếm là khung của câu đang xem.
      Mục 1–14 là HSK1, 15–18 là phần mở rộng chung; từ mục 19 xếp theo HSK2 tới HSK6.</p>
  </header>

<?php if (!$data): ?>
  <p class="empty">Không đọc được dữ liệu: <?= zs_e((string) $load['error']) ?></p>
<?php else: ?>
<?php require __DIR__ . '/board.php'; ?>
<?php endif; ?>
<?php
$DATA_VARS = $data ? ['TOPICS' => $data['grammar']] : [];
$PAGE_CFG  = $data ? '{}' : null;
require __DIR__ . '/chrome-foot.php';
