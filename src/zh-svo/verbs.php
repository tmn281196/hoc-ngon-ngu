<?php
$PAGE  = 'verbs';
require __DIR__ . '/chrome.php';
$load  = zs_data();
$data  = $load['data'];
$nVerb = $data ? count($data['verbs']['DATA']) : 0;
?>
  <header>
    <p class="eyebrow">Phân tích câu tiếng Trung · 01</p>
    <h1><i><?= $nVerb ?: '' ?> động từ</i> — động từ quyết định câu cần gì</h1>
    <p class="lede">Động từ là trung tâm của câu: nó mở ra chỗ cho chủ ngữ, tân ngữ, bổ ngữ, và câu dài ngắn thế nào
      là do nó đòi. 50 động từ cơ bản HSK1 chia năm nhóm theo công dụng, thêm <?= max(0, $nVerb - 50) ?> động từ thông dụng HSK2–4
      xếp nhóm theo cấp.
      Chọn một từ ở thanh bên; hàng chấm dưới sân
      lật sang câu thứ hai của nó. Bấm vào một từ trên đồ thị để xem pinyin, nghĩa và chức vụ.</p>
  </header>

<?php if (!$data): ?>
  <p class="empty">Không đọc được dữ liệu: <?= zs_e((string) $load['error']) ?></p>
<?php else: ?>
<?php require __DIR__ . '/board.php'; ?>
<?php endif; ?>
<?php
$DATA_VARS = $data ? ['SVO_VERBS' => $data['verbs']] : [];
$PAGE_CFG  = $data ? '{start:0}' : null;
require __DIR__ . '/chrome-foot.php';
