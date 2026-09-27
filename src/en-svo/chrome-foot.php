<?php

declare(strict_types=1);

/**
 * Chân trang chung cho bốn trang en-svo, và là chỗ nạp script.
 *
 * Cách dùng — đặt ở cuối mỗi trang, các biến đều tuỳ chọn:
 *
 *     <?php
 *     $FOOT_EXTRA   = 'Câu thêm riêng của trang này.';   // nối sau đoạn chung
 *     $DATA_SCRIPTS = ['verbs-data.js', 'omni-data.js']; // dữ liệu trang cần
 *     $PAGE_CFG     = '{start:3}';                       // có thì mới nạp graph.js
 *     require __DIR__ . '/chrome-foot.php';
 *
 * Thứ tự nạp script là ràng buộc thật, không phải tuỳ tiện: graph.js đọc
 * window.SVO_VERBS / window.TOPICS / window.SVO_OMNI ngay lúc chạy, nên mọi
 * file dữ liệu phải đứng trước nó. Gom vào đây để không trang nào xếp sai.
 *
 * $PAGE_CFG rỗng nghĩa là trang không có đồ thị (Mục lục, Bảng tra nhãn) —
 * khi đó graph.js không được nạp, đỡ tải một file chẳng dùng đến.
 */

$FOOT_EXTRA   = $FOOT_EXTRA   ?? '';
$DATA_SCRIPTS = $DATA_SCRIPTS ?? [];
$PAGE_CFG     = $PAGE_CFG     ?? null;

?>
  <footer>
    Phân tích theo lối ngữ pháp trường học (subject – verb – object – complement – modifier), hợp cho người học.
    Ranh giới bổ ngữ / bổ nghĩa đôi khi còn tranh cãi ngay trong giới ngôn ngữ học — phép thử thực dụng là bỏ thử
    ra xem câu còn đúng không. Máy phân tích câu dùng hệ nhãn khác, chi tiết hơn: chuẩn Universal Dependencies với
    <code>nsubj</code>, <code>obj</code>, <code>xcomp</code>, <code>obl</code>, <code>amod</code>.
<?php if ($FOOT_EXTRA !== ''): ?>
    <br><br><?= $FOOT_EXTRA ?>
<?php endif; ?>
  </footer>
</div>

<script src="field.js"></script>
<?php foreach ($DATA_SCRIPTS as $src): ?>
<script src="<?= svo_e($src) ?>"></script>
<?php endforeach; ?>
<?php if ($PAGE_CFG !== null): ?>
<script>window.SVO_PAGE = <?= $PAGE_CFG ?>;</script>
<script src="graph.js"></script>
<?php endif; ?>
</body>
</html>
