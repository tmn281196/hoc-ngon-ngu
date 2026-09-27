<?php

declare(strict_types=1);

/**
 * Chân trang chung cho các trang zh-svo, và là chỗ nạp script.
 *
 * Cách dùng — đặt ở cuối mỗi trang, các biến đều tuỳ chọn:
 *
 *     $FOOT_EXTRA = 'Câu thêm riêng của trang này.';          // nối sau đoạn chung
 *     $DATA_VARS  = ['TOPICS' => $data['grammar']];           // nhúng thành window.TOPICS
 *     $PAGE_CFG   = '{}';                                     // có thì mới nạp graph.js
 *     require __DIR__ . '/chrome-foot.php';
 *
 * Dữ liệu nhúng thẳng vào trang (không có file .js riêng) và phải đứng trước
 * graph.js, vì graph.js đọc window.SVO_VERBS / window.TOPICS ngay lúc chạy.
 */

$FOOT_EXTRA = $FOOT_EXTRA ?? '';
$DATA_VARS  = $DATA_VARS  ?? [];
$PAGE_CFG   = $PAGE_CFG   ?? null;

?>
  <footer>
    Phân tích theo lối ngữ pháp dạy tiếng Trung quen thuộc: <span lang="zh">主语</span> chủ ngữ ·
    <span lang="zh">谓语</span> vị ngữ · <span lang="zh">宾语</span> tân ngữ · <span lang="zh">补语</span> bổ ngữ ·
    <span lang="zh">定语</span> định ngữ · <span lang="zh">状语</span> trạng ngữ. Câu được tách từ và gán nhãn tự động
    bằng luật: đúng với phần lớn câu ngắn trình độ HSK1, câu dài nhiều vế thì chỉ là gần đúng. Pinyin lấy từ chính
    câu nên giữ đúng biến điệu.
<?php if ($FOOT_EXTRA !== ''): ?>
    <br><br><?= $FOOT_EXTRA ?>
<?php endif; ?>
  </footer>
</div>

<script src="field.js"></script>
<?php foreach ($DATA_VARS as $name => $value): ?>
<script>window.<?= $name ?> = <?= json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE) ?>;</script>
<?php endforeach; ?>
<?php if ($PAGE_CFG !== null): ?>
<script>window.SVO_PAGE = <?= $PAGE_CFG ?>;</script>
<script src="graph.js"></script>
<?php endif; ?>
</body>
</html>
