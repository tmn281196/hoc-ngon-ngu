<?php
$PAGE  = 'index';
// Tiêu đề tab là tên của cả site, không phải nhãn "Mục lục" trên thanh nav —
// đây là trang người ta lưu bookmark.
$TITLE = 'Phân tích câu tiếng Anh';
require __DIR__ . '/chrome.php';
?>
  <header>
    <p class="eyebrow">Phân tích câu tiếng Anh</p>
    <h1><em>Chủ ngữ</em>, <i>động từ</i>, <b>tân ngữ</b>, <u>bổ ngữ</u> — và phần tháo ra được</h1>
    <p class="lede"><strong>Động từ là thành phần trung tâm của câu</strong> — mọi thứ còn lại mọc ra quanh nó. Chính nó quyết định câu cần những gì: cái nó đòi hỏi là bổ ngữ, bỏ đi thì câu sai; phần còn lại chỉ là bổ nghĩa, tháo ra câu vẫn đứng. Đổi động từ là đổi luôn bộ khung, dù các từ khác giữ nguyên. Ba trang dưới đi từ đó: hai trang đầu vẽ đồ thị chạy bằng lực, trang cuối là bảng tra nhãn.</p>
  </header>

  <div class="hub">
    <a href="omnibus.php">
      <span class="n">01</span>
      <h2>Động từ đặc biệt</h2>
      <p>Hai mươi mốt động từ gánh hàng chục nghĩa, thay cho hàng chục động từ chuyên biệt. Mỗi từ một đồ thị khung câu chú giải bằng tay, kèm ghi chú cách dùng, phrasal verb và thành ngữ.</p>
      <span class="cnt">21 động từ · 139 câu · 117 phrasal có ví dụ</span>
    </a>
    <a href="topics.php">
      <span class="n">02</span>
      <h2>Theo ngữ nghĩa</h2>
      <p>Câu thật, xếp theo chủ đề rồi theo động từ. Được cái nhiều và đa dạng.</p>
      <span class="cnt">19 chủ đề · 212 động từ · 839 câu</span>
    </a>
    <a href="reference.php">
      <span class="n">03</span>
      <h2>Bảng tra nhãn</h2>
      <p>Đủ mười sáu nhãn mà đồ thị vẽ ra, chia ba tầng: nòng cốt không tháo được, bổ ngữ do động từ đòi hỏi nên bỏ đi thì câu hỏng, và bổ nghĩa chỉ thêm cho rõ nghĩa nên tháo ra câu vẫn đứng.</p>
      <span class="cnt">16 nhãn · 3 tầng</span>
    </a>
  </div>
<?php
$FOOT_EXTRA   = 'Các câu bên <strong>động từ đặc biệt</strong> được chú giải bằng tay.';
require __DIR__ . '/chrome-foot.php';
