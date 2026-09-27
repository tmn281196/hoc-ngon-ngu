<?php
$PAGE  = 'reference';
require __DIR__ . '/chrome.php';
?>
  <header>
    <p class="eyebrow">Phân tích câu tiếng Anh · 03</p>
    <h1>Nòng cốt, <u>bổ ngữ</u>, và phần tháo ra được</h1>
    <p class="lede">Một phép thử tách đôi mọi thứ đứng quanh động từ: bỏ nó ra, câu còn đúng không? Còn đúng thì là bổ nghĩa, hỏng thì là bổ ngữ. Dưới cùng một phép thử đó là nòng cốt — chủ ngữ, động từ, tân ngữ — vốn không có chuyện tháo. Đây là đủ bộ nhãn mà hai trang đồ thị kia dùng, màu nào ứng với màu nấy trên sân.</p>
  </header>

  <section class="ref">
    <div class="ref-group">
      <h3>Nòng cốt — chủ ngữ, động từ, tân ngữ</h3>
      <p class="sub">Bộ khung không tháo được. Động từ là gốc câu, mọi thứ khác treo vào nó; chủ ngữ và tân ngữ là những chỗ danh từ do chính động từ mở ra.</p>
      <div class="rows">
        <div class="row k-s"><span class="tag">subj</span><span>chủ ngữ</span>
          <span class="ex"><b>She</b> is a teacher · <b>The old man in the corner</b> smiled happily — ai hay cái gì</span></div>
        <div class="row k-s"><span class="tag">expl</span><span>chủ ngữ giả</span>
          <span class="ex"><b>It</b> takes 30 minutes to cook — giữ chỗ chủ ngữ cho đủ khung, không trỏ vào ai cả</span></div>
        <div class="row k-v"><span class="tag">verb</span><span>động từ chính</span>
          <span class="ex">I <b>put</b> the keys on the table · She <b>runs</b> a business — gốc câu, quyết định câu cần gì</span></div>
        <div class="row k-v"><span class="tag">aux</span><span>trợ động từ</span>
          <span class="ex">The letter <b>was</b> written by John — đi kèm động từ chính, mang thì và thể</span></div>
        <div class="row k-v"><span class="tag">prt</span><span>tiểu từ động từ</span>
          <span class="ex">take <b>off</b> · give <b>up</b> · look <b>after</b> — dính vào động từ và đổi hẳn nghĩa nó, không phải giới từ</span></div>
        <div class="row k-o"><span class="tag">dObj</span><span>tân ngữ trực tiếp</span>
          <span class="ex">They have <b>two children</b> · She runs <b>a business</b> — cái chịu tác động</span></div>
        <div class="row k-o"><span class="tag">iObj</span><span>tân ngữ gián tiếp</span>
          <span class="ex">She gave <b>me</b> a book — người nhận, đứng trước tân ngữ trực tiếp</span></div>
      </div>
    </div>

    <div class="ref-group">
      <h3>Bổ ngữ — complement</h3>
      <p class="sub">Do động từ (hoặc giới từ) đòi hỏi. Bỏ ra thì câu hỏng, nên nó thuộc khung câu.</p>
      <div class="rows">
        <div class="row k-c"><span class="tag">sComp</span><span>bổ ngữ chủ ngữ</span>
          <span class="ex">She is <b>a teacher</b> · You look <b>tired</b> — nói về chủ ngữ</span></div>
        <div class="row k-c"><span class="tag">oComp</span><span>bổ ngữ tân ngữ</span>
          <span class="ex">You make me <b>happy</b> — nói về tân ngữ <em>me</em>, không phải về chủ ngữ</span></div>
        <div class="row k-c"><span class="tag">aComp</span><span>bổ ngữ trạng ngữ</span>
          <span class="ex">I put the keys <b>on the table</b> — trạng ngữ nhưng bắt buộc, không tháo được</span></div>
        <div class="row k-c"><span class="tag">cComp</span><span>bổ ngữ mệnh đề</span>
          <span class="ex">I want <b>to learn English</b> · I know <b>that she is right</b></span></div>
        <div class="row k-c"><span class="tag">pComp</span><span>bổ ngữ của giới từ</span>
          <span class="ex">on <b>the table</b> · by <b>John</b> — giới từ cũng đòi bổ ngữ của nó</span></div>
      </div>
    </div>

    <div class="ref-group">
      <h3>Bổ nghĩa — modifier</h3>
      <p class="sub">Thêm vào cho rõ nghĩa, không ai đòi. Bỏ ra câu vẫn đúng — trên đồ thị vẽ nét đứt.</p>
      <div class="rows">
        <div class="row k-m"><span class="tag">mod</span><span>bổ nghĩa danh từ</span>
          <span class="ex"><b>old</b> man · man <b>in the corner</b> · <b>two</b> children</span></div>
        <div class="row k-m"><span class="tag">adjunct</span><span>trạng ngữ tùy ý</span>
          <span class="ex">smiled <b>happily</b> · does homework <b>every morning</b> · written <b>by John</b></span></div>
        <div class="row k-m"><span class="tag">det</span><span>từ hạn định</span>
          <span class="ex"><b>the</b> keys · <b>a</b> book — từ chức năng, không phải bổ nghĩa thật sự</span></div>
        <div class="row k-m"><span class="tag">to</span><span>tiểu từ “to”</span>
          <span class="ex">I want <b>to</b> learn English — dính vào động từ nguyên thể, tự nó không mang nghĩa</span></div>
      </div>
    </div>
  </section>
<?php
require __DIR__ . '/chrome-foot.php';
