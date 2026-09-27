<?php

declare(strict_types=1);

/**
 * Sân đồ thị dùng chung cho ba trang Động từ / Theo ngữ pháp / Hội thoại:
 * thanh bên, sân lực, bảng chi tiết, dải chức vụ, danh sách câu.
 * graph.js tìm các phần tử theo id nên cả ba trang phải có cùng một bộ.
 */
?>
  <div class="board">
    <nav class="verbs" id="chips" role="tablist" aria-label="Chọn câu"></nav>

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
        <label class="toggle" title="Chỉ giữ chủ ngữ, vị ngữ, tân ngữ và các bổ ngữ; ẩn định ngữ, trạng ngữ"><input type="checkbox" id="t-core"> Chỉ khung câu</label>
      </div>

      <div class="ribbon">
        <h2>Câu chia theo chức vụ</h2>
        <div class="rib-line" id="ribbon"></div>
        <div class="sline"><span class="spk" id="spk" hidden></span><p class="spy" id="spy"></p></div>
        <p class="vi" id="vi"></p>
        <p class="snote" id="snote" hidden></p>
      </div>

      <div class="legend">
        <span><i class="sw s"></i> chủ ngữ</span>
        <span><i class="sw v"></i> vị ngữ</span>
        <span><i class="sw o"></i> tân ngữ</span>
        <span><i class="sw c"></i> bổ ngữ</span>
        <span><i class="sw m"></i> định ngữ, trạng ngữ, trợ từ</span>
        <span><i class="sw dash"></i> chấm rỗng = tùy ý, bỏ được</span>
      </div>

      <div class="exbar" id="exbar" hidden>
        <h2 id="exbar-t"></h2>
        <div class="exrows" id="exrows"></div>
      </div>
    </div>
  </div>
