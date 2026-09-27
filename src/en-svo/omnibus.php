<?php
$PAGE  = 'omnibus';
require __DIR__ . '/chrome.php';
?>
  <header>
    <p class="eyebrow">Phân tích câu tiếng Anh · 01</p>
    <h1><i>Động từ đặc biệt</i> — 21 từ gánh hàng chục nghĩa</h1>
    <p class="lede">Gom vào đây những động từ <strong>lệch khỏi lối thường</strong>, theo hai kiểu. Kiểu thứ nhất rỗng nghĩa: nghĩa rời khỏi động từ mà dồn vào danh từ đứng sau — <i>have a look</i> thật ra chỉ là <i>look</i>. Kiểu thứ hai làm <strong>động từ nối</strong>: không có tân ngữ nào, chỉ bắc một nhịp sang thứ nói về chủ ngữ — <i>go sour</i>, <i>come true</i>, <i>get cold</i>. Nhiều từ làm được cả hai. Chọn một từ ở thanh bên, đồ thị vẽ đúng khung mà nghĩa ấy đòi hỏi; hàng chấm dưới sân lật qua những khung còn lại của chính từ đó, và phần dưới mở ra trọn sức chứa của nó: cách dùng, phrasal verb, thành ngữ.</p>
  </header>

  <div class="board">
    <nav class="verbs" id="chips" role="tablist" aria-label="Chọn động từ"></nav>

    <div class="mid">
      <div class="stage-shell">
        <div id="stage">
          <svg id="wires" aria-hidden="true"></svg>
          <button class="helpbtn" id="helpbtn" type="button" aria-expanded="false" aria-controls="axes" title="Chú dẫn cách đọc đồ thị">?</button>
          <div class="axes" id="axes" hidden>
            <span><b>━</b> sống cá: chủ ngữ · động từ · tân ngữ / bổ ngữ</span>
            <span><b>╱</b> xương phụ mọc từ chính từ mà nó bổ sung, cùng một góc</span>
            <span><b>┄</b> nét đứt: thành phần tùy ý</span>
            <span class="hint">bấm từ để xem chi tiết · kéo để dời · lăn chuột để phóng</span>
          </div>
          <div class="ctrls">
            <button class="ctrl" id="zin" title="Phóng to" aria-label="Phóng to">+</button>
            <button class="ctrl" id="zout" title="Thu nhỏ" aria-label="Thu nhỏ">−</button>
            <button class="ctrl wide" id="reset">Về giữa</button>
          </div>
          <div class="pager" id="pager" hidden></div>
        </div>
        <aside class="panel" id="panel" hidden>
          <h2 id="panel-title">Chi tiết</h2>
          <div id="panel-body"></div>
        </aside>
      </div>

      <div class="toggles">
        <label class="toggle" title="Chỉ giữ chủ ngữ, động từ, tân ngữ và các bổ ngữ bắt buộc"><input type="checkbox" id="t-core"> Chỉ khung câu</label>
      </div>

      <div class="ribbon">
        <h2>Câu chia theo chức vụ</h2>
        <div class="rib-line" id="ribbon"></div>
        <p class="vi" id="vi"></p>
        <p class="snote" id="snote" hidden></p>

        <section class="omni">
          <h3>Một từ gánh được những gì</h3>
          <p class="sub">Đây là toàn bộ sức chứa của động từ đang chọn: nghĩa nào, khung nào, đi với giới từ nào. Rất nhiều cách dùng dưới đây chính là các khung mà đồ thị phía trên vừa vẽ ra. Phần này đi theo thanh bên, không phải chọn lại lần nữa.</p>
          <div class="obody" id="obody"></div>
        </section>
      </div>

      <div class="legend">
        <span><i class="sw s"></i> chủ ngữ</span>
        <span><i class="sw v"></i> động từ</span>
        <span><i class="sw o"></i> tân ngữ</span>
        <span><i class="sw c"></i> bổ ngữ</span>
        <span><i class="sw m"></i> từ phụ</span>
        <span><i class="sw dash"></i> chấm rỗng = tùy ý, bỏ được</span>
      </div>



    </div>

  </div>
<?php
$FOOT_EXTRA   = 'Toàn bộ câu trên trang này được chú giải <strong>bằng tay</strong>, nên đọc được là tin được.';
$DATA_SCRIPTS = ['verbs-data.js', 'omni-data.js'];
$PAGE_CFG     = '{start:3}';
require __DIR__ . '/chrome-foot.php';
