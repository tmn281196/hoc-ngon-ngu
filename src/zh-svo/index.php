<?php
$PAGE  = 'index';
// Tiêu đề tab là tên của cả site, không phải nhãn "Mục lục" trên thanh nav.
$TITLE = 'Phân tích câu tiếng Trung';
require __DIR__ . '/chrome.php';
$load  = zs_data();
$st    = $load['data']['stats'] ?? null;
?>
  <header>
    <p class="eyebrow">Phân tích câu tiếng Trung</p>
    <h1><em>Chủ ngữ</em>, <i>vị ngữ</i>, <b>tân ngữ</b>, <u>bổ ngữ</u> — và trạng ngữ đứng trước động từ</h1>
    <p class="lede">Câu tiếng Trung không chia động từ, không có thì, không có số nhiều: nghĩa nằm ở <strong>trật tự từ</strong>.
      Khung cơ bản vẫn là chủ ngữ – vị ngữ – tân ngữ như tiếng Việt, nhưng mọi thứ bổ nghĩa cho động từ — thời gian,
      nơi chốn, cách thức, cụm giới từ — đứng <strong>trước</strong> động từ: <span lang="zh">我<b>在商店</b>工作</span>,
      tôi làm việc ở cửa hàng. Sau động từ là chỗ của tân ngữ và bổ ngữ: <span lang="zh">说<b>得很好</b></span>,
      <span lang="zh">听<b>懂</b>了</span>. Ba trang đồ thị dưới vẽ từng câu thành cây quan hệ, mỗi từ kèm pinyin;
      trang cuối là bảng tra nhãn.</p>
  </header>

<?php if (!$st): ?>
  <p class="empty">Không đọc được dữ liệu: <?= zs_e((string) $load['error']) ?></p>
<?php else: ?>
  <div class="hub">
    <a href="verbs.php">
      <span class="n">01</span>
      <h2>Động từ</h2>
      <p>50 động từ cơ bản HSK1 và <?= max(0, (int) $st['verbs'] - 50) ?> động từ thông dụng HSK2–4, chia nhóm theo công dụng, mỗi từ hai câu. Đồ thị cho thấy động từ mở ra những chỗ nào: ai làm, làm gì, làm thế nào.</p>
      <span class="cnt"><?= (int) $st['verbs'] ?> động từ · <?= (int) $st['verbSents'] ?> câu</span>
    </a>
    <a href="grammar.php">
      <span class="n">02</span>
      <h2>Theo ngữ pháp</h2>
      <p>Câu luyện xếp theo điểm ngữ pháp, từ <span lang="zh">是</span>, <span lang="zh">有</span>, <span lang="zh">在</span> tới <span lang="zh">因为…所以</span>, <span lang="zh">如果…就</span>, rồi lên HSK2–6: <span lang="zh">比</span>, <span lang="zh">把</span>, <span lang="zh">被</span>, <span lang="zh">连…都</span>.</p>
      <span class="cnt"><?= (int) $st['grammar'] ?> mục · <?= (int) $st['grammarSents'] ?> câu</span>
    </a>
    <a href="dialogue.php">
      <span class="n">03</span>
      <h2>Hội thoại</h2>
      <p>Hội thoại của 15 bài giáo trình HSK1 và các bài luyện nói, từng lượt lời thành một đồ thị, kèm câu ví dụ trong phần ngữ pháp của mỗi bài.</p>
      <span class="cnt"><?= (int) $st['dialogue'] ?> bài · <?= (int) $st['dialogueSents'] ?> câu</span>
    </a>
    <a href="reference.php">
      <span class="n">04</span>
      <h2>Bảng tra nhãn</h2>
      <p>Mười tám nhãn đồ thị dùng, đặt cạnh tên gọi quen thuộc trong ngữ pháp tiếng Trung, chia ba tầng: nòng cốt, bổ ngữ bỏ đi thì câu hỏng, và phần tháo ra câu vẫn đứng.</p>
      <span class="cnt">18 nhãn · 3 tầng</span>
    </a>
  </div>
<?php endif; ?>
<?php
require __DIR__ . '/chrome-foot.php';
