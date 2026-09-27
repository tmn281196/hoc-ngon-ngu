/* Đồ thị cây cho câu — dùng chung cho ba trang đồ thị.

   Script này không mang dữ liệu nào. Trang nạp file nào thì phần đó hiện ra:
     verbs-data.js  -> window.SVO_VERBS  {GROUPS, DATA}  câu mẫu + thanh bên
     omni-data.js   -> window.SVO_OMNI                   ghi chú Omnibus
     data.js        -> window.TOPICS                     chủ đề ngữ nghĩa
   Thiếu file nào thì phần tương ứng tự bỏ qua, nên cùng một engine chạy được
   cả trang chỉ có câu mẫu lẫn trang chỉ có chủ đề.

   window.SVO_PAGE khai báo trang cần gì:
     groups  mảng {name, from, to} cắt từ DATA cho thanh bên (mặc định: cả hai nhóm)
     start   chỉ số DATA vẽ sẵn khi mở trang
*/
(function(){
  "use strict";

  const PAGE   = window.SVO_PAGE  || {};
  const VERBS  = window.SVO_VERBS || {};
  const DATA   = VERBS.DATA || [];
  const GROUPS = PAGE.groups || VERBS.GROUPS || [];
  const OMNI   = window.SVO_OMNI || [];

  // k: màu · req: core (nòng cốt) | req (bắt buộc) | opt (tùy ý) | fn (từ chức năng)
  const FUNC_INFO = {
    subj:   {ab:"subj",  vi:"chủ ngữ",            k:"s", req:"core"},
    expl:   {ab:"expl",  vi:"chủ ngữ giả",         k:"s", req:"fn"},
    verb:   {ab:"verb",  vi:"động từ chính",      k:"v", req:"core"},
    dObj:   {ab:"dObj",  vi:"tân ngữ trực tiếp",  k:"o", req:"req"},
    iObj:   {ab:"iObj",  vi:"tân ngữ gián tiếp",  k:"o", req:"req"},
    sComp:  {ab:"sComp", vi:"bổ ngữ chủ ngữ",     k:"c", req:"req"},
    oComp:  {ab:"oComp", vi:"bổ ngữ tân ngữ",     k:"c", req:"req"},
    aComp:  {ab:"aComp", vi:"bổ ngữ trạng ngữ",   k:"c", req:"req"},
    cComp:  {ab:"cComp", vi:"bổ ngữ mệnh đề",     k:"c", req:"req"},
    pComp:  {ab:"pComp", vi:"bổ ngữ của giới từ", k:"c", req:"req"},
    mod:    {ab:"mod",   vi:"bổ nghĩa",           k:"m", req:"opt"},
    adjunct:{ab:"adjunct", vi:"trạng ngữ tùy ý",  k:"m", req:"opt"},
    det:    {ab:"det",   vi:"từ hạn định",        k:"m", req:"fn"},
    aux:    {ab:"aux",   vi:"trợ động từ",        k:"v", req:"fn"},
    prt:    {ab:"prt",   vi:"tiểu từ động từ",    k:"v", req:"fn"},
    to:     {ab:"to",    vi:"tiểu từ “to”",       k:"m", req:"fn"}
  };
  const REQ_VI = {
    core:{t:"nòng cốt", cls:"req"},
    req:{t:"bắt buộc — bỏ đi câu sai", cls:"req"},
    opt:{t:"tùy ý — bỏ đi câu vẫn đúng", cls:"opt"},
    fn:{t:"từ chức năng", cls:""}
  };
  const POS_VI = {PRON:"đại từ", NOUN:"danh từ", PROPN:"danh từ riêng", VERB:"động từ",
    AUX:"trợ động từ", DET:"từ hạn định", ADP:"giới từ", NUM:"số từ", PART:"tiểu từ",
    ADJ:"tính từ", ADV:"trạng từ"};
  const CHUNK_LAB = {subj:"S · chủ ngữ", expl:"S · chủ ngữ giả", verb:"V · động từ", dObj:"O · tân ngữ",
    iObj:"IO · tân ngữ gián tiếp", sComp:"C · bổ ngữ chủ ngữ", oComp:"C · bổ ngữ tân ngữ",
    aComp:"A · bổ ngữ trạng ngữ", cComp:"C · bổ ngữ mệnh đề", adjunct:"A · trạng ngữ tùy ý"};
  const MAJOR = new Set(["subj","expl","verb","dObj","iObj","sComp","oComp","aComp","cComp","adjunct"]);
  const DROPPABLE = new Set(["mod","adjunct","det"]);   // ẩn khi xem khung câu

  /* Dữ liệu chủ đề do build_topics.py sinh ra, nhãn của nó không chắc trùng
     bảng trên. Trước đây một nhãn lạ làm buildSentence() ném giữa chừng: đồ
     thị đứng nguyên ở câu cũ nên nút lật câu trông như hỏng. Giờ nhãn lạ chỉ
     rơi về nhóm "từ phụ" và hiện nguyên tên của nó. */
  function funcInfo(f){
    return FUNC_INFO[f] || {ab:f, vi:f, k:"m", req:"fn"};
  }

  // ---------- trạng thái ----------
  const stage = document.getElementById("stage");
  const wires = document.getElementById("wires");

  let cur = PAGE.start || 0, zoom = 1;
  const pan = {x:0, y:0};
  let sel = null;
  let nodes = [], edges = [], els = [], lines = [], rails = [], spineLn = null;
  const show = {core:false, free:false};
  const view = {cx:0, cy:0, ax:3, ay:2.4};

  // Sơ đồ cố định, không mô phỏng lực và không kéo được từng node: cùng một câu
  // thì luôn ra cùng một hình. Mọi xương xiên cùng một góc 60°:
  //   row  khoảng cách giữa hai tầng      run  độ lệch ngang của một xương
  //   gap0 chân xương đầu tiên cách từ (0: mọc ngay từ node)
  //   step khoảng cách giữa hai chân xương
  //   gap  khe giữa hai cụm xương kề nhau main khoảng cách tối thiểu trên sống
  const FISH = {row:1.9, run:1.9 / Math.tan(Math.PI / 3), gap0:0, step:1.9, gap:1.7, main:2.2};
  // Mạch chính: gốc câu (động từ) cùng các thành phần nòng cốt/bắt buộc treo
  // thẳng vào nó — S, V, O, C nằm trên một đường ngang theo thứ tự trong câu.
  // Mọi thứ còn lại (bổ nghĩa, trạng ngữ, trợ từ, bổ ngữ của giới từ...) là
  // nhánh phụ, treo xuống dưới từ mà nó bổ sung.
  const SPINE = new Set(["subj","expl","dObj","iObj","sComp","oComp","aComp","cComp"]);
  const CHAIN = new Set([]);   // từ trên mạch chính mà con của nó cũng được lên mạch

  function chunkOf(toks, i){
    let c = i, guard = 0;
    while(!MAJOR.has(toks[c].func) && toks[c].head >= 0 && guard++ < 20) c = toks[c].head;
    return toks[c].func;
  }
  function inFrame(toks, i){
    let c = i, guard = 0;
    while(guard++ < 20){
      if(DROPPABLE.has(toks[c].func)) return false;
      if(toks[c].head < 0) return true;
      c = toks[c].head;
    }
    return true;
  }

  let curSent = null;

  function build(idx){
    if(!DATA[idx]) return;
    cur = idx;
    buildSentence(DATA[idx]);
  }

  // Một động từ có thể có nhiều câu: câu chính nằm ngay trong DATA[idx], các
  // câu thêm nằm trong DATA[idx].ex — cùng hình dạng {en, vi, note, tokens}.
  // Gộp lại thành một danh sách để thanh chấm đi qua.
  //
  // Danh sách một phần tử là chuyện bình thường: setPager() tự ẩn khi n < 2,
  // nên động từ chưa có câu thêm vẫn chạy y như cũ.
  function variantsOf(idx){
    const d = DATA[idx];
    if(!d) return [];
    return [d].concat(Array.isArray(d.ex) ? d.ex : []);
  }

  function showVariant(idx, k){
    const list = variantsOf(idx);
    if(!list[k]) return;
    cur = idx; sel = null;
    buildSentence(list[k]);
    // Nhãn thanh chấm là KHUNG của câu đang vẽ, không phải tên động từ: tên động
    // từ đã nằm ở chip đang sáng bên cạnh, còn khung thì đổi theo từng câu.
    setPager(list.length, k, list[k].pattern || DATA[idx].verb, kk=>showVariant(idx, kk), list.map(s=>s.en));
  }

  function buildSentence(sent){
    const s = sent; curSent = s;
    const toks = s.tokens, n = toks.length;
    nodes = []; edges = [];
    stage.classList.toggle("tight", n > 6);

    toks.forEach((t,i)=>{
      const info = funcInfo(t.func);
      nodes.push({
        i, head:t.head, func:t.func, main:false, up:false, x:0, y:0, depth:0, fx:0, fy:0, rail:0,
        label:t.w, tag:info.ab, k:info.k, req:info.req, chunk:chunkOf(toks,i),
        frame:inFrame(toks,i), root:t.head < 0
      });
    });
    toks.forEach((t,i)=>{
      if(t.head >= 0) edges.push({a:i, b:t.head, k:nodes[i].k, opt:nodes[i].req === "opt"});
    });

    pan.x = 0; pan.y = 0;
    layout();
    renderDom(s);
  }

  function renderDom(s){
    stage.querySelectorAll(".node").forEach(el=>el.remove());
    els = nodes.map(nd=>{
      const el = document.createElement("div");
      el.className = "node k-" + nd.k + (nd.root ? " root" : "") + (nd.req === "opt" ? " opt" : "");
      el.innerHTML = '<span class="node-in"><i class="dot"></i>' +
                     '<span class="lbl"><span class="w"></span><span class="t"></span></span></span>';
      el.querySelector(".w").textContent = nd.label;
      el.querySelector(".t").textContent = nd.tag;
      stage.appendChild(el);
      return el;
    });

    while(wires.firstChild) wires.removeChild(wires.firstChild);
    spineLn = document.createElementNS("http://www.w3.org/2000/svg","path");
    spineLn.setAttribute("class", "spine");
    wires.appendChild(spineLn);
    // "Thanh" của một xương: đoạn ngang nối từ đó với chân các xương con.
    rails = nodes.map(nd=>{
      const ln = document.createElementNS("http://www.w3.org/2000/svg","path");
      ln.setAttribute("stroke", "var(--" + (nd.k === "m" ? "mod" : nd.k) + ")");
      ln.setAttribute("stroke-width", "1.6");
      wires.appendChild(ln);
      return ln;
    });
    lines = edges.map(e=>{
      const ln = document.createElementNS("http://www.w3.org/2000/svg","path");
      ln.setAttribute("stroke", "var(--" + (e.k === "m" ? "mod" : e.k) + ")");
      ln.setAttribute("stroke-width", "1.6");
      if(e.opt) ln.setAttribute("stroke-dasharray","5 4");
      wires.appendChild(ln);
      return ln;
    });

    buildRibbon(s);
    showNote();
    draw();
  }

  // ---------- bố cục xương cá ----------
  // Sống cá là mạch chính, nằm ngang, đầu cá ở bên phải. Mỗi thành phần phụ là
  // một xương xiên 60°, mọc ngay từ node mà nó bổ sung, ngọn chĩa ra sau về
  // phía đuôi. Từ có nhiều xương cùng một phía thì các xương sau mọc lùi dần
  // về bên trái dọc sống (hoặc dọc thanh của từ đó). Xương của một từ luân phiên trên / dưới. Từ
  // phụ lại có xương con thì từ đó kéo một thanh ngang về phía đuôi, xương con
  // mọc từ thanh ấy với cùng góc và đi tiếp ra xa sống. Chân xương gần từ nhất
  // thuộc về từ đứng sau cùng trong câu. Khi "Chỉ khung câu" bật thì chỉ xếp
  // các node còn hiện, không chừa lỗ.
  function layout(){
    const vis = nodes.filter(nodeVisible);
    if(!vis.length) return;
    const on = new Set(vis.map(nd=>nd.i));
    nodes.forEach(nd=>{ nd.main = false; });
    vis.forEach(nd=>{ if(nd.head < 0 || !on.has(nd.head)) nd.main = true; });
    for(let grew = true, guard = 0; grew && guard++ < 30;){
      grew = false;
      vis.forEach(nd=>{
        if(nd.main) return;
        const h = nodes[nd.head];
        if(h.main && SPINE.has(nd.func) && (h.root || CHAIN.has(h.func))){ nd.main = true; grew = true; }
      });
    }

    const kids = new Map(vis.map(nd=>[nd.i, []]));
    vis.forEach(nd=>{ if(!nd.main) kids.get(nd.head).push(nd.i); });

    // Xếp các xương ks mọc ra từ nd về phía s (+1 trên, -1 dưới), từ phải sang
    // trái. Chân xương sau lùi đủ xa để cả cụm của nó nằm lọt bên trái cụm
    // trước, nên hai cụm cùng tầng không bao giờ chồng nhau. Trả về mép trái.
    function bones(nd, ks, s){
      let left = nd.x, foot = nd.x - FISH.gap0;
      ks.forEach((k, j)=>{
        const c = nodes[k];
        if(j) foot = Math.min(foot - FISH.step, left - FISH.gap + FISH.run);
        c.fx = foot; c.fy = nd.y;
        nd.rail = Math.min(nd.rail, foot);
        c.x = foot - FISH.run; c.y = nd.y + s * FISH.row;
        c.depth = nd.depth + 1; c.up = s > 0; c.rail = c.x;
        left = Math.min(left, c.depth < 30 ? bones(c, kids.get(c.i).slice().reverse(), s) : c.x);
      });
      return left;
    }

    // Các từ trên sống xếp từ phải sang trái; mỗi từ đứng bên trái cả cụm
    // xương của từ kế sau nó, để xương chạm sống giữa hai từ luôn thuộc về từ
    // bên phải.
    const spine = vis.filter(nd=>nd.main);
    let next = null;
    for(let m = spine.length - 1; m >= 0; m--){
      const nd = spine[m];
      nd.x = next ? Math.min(next.x - FISH.main, next.left - FISH.gap) : 0;
      nd.y = 0; nd.depth = 0; nd.up = false; nd.rail = nd.x;
      const ks = kids.get(nd.i).slice().reverse();
      const left = Math.min(nd.x,
        bones(nd, ks.filter((k,j)=>j % 2 === 0), 1),
        bones(nd, ks.filter((k,j)=>j % 2 === 1), -1));
      next = {x:nd.x, left};
    }
    view.spL = Math.min(...spine.map(nd=>nd.rail));
    view.spR = Math.max(...spine.map(nd=>nd.x));

    let x0 = view.spL, x1 = view.spR, y0 = 0, y1 = 0;
    vis.forEach(nd=>{
      x0 = Math.min(x0, nd.x); x1 = Math.max(x1, nd.x);
      y0 = Math.min(y0, nd.y); y1 = Math.max(y1, nd.y);
    });
    view.cx = (x0 + x1) / 2; view.cy = (y0 + y1) / 2;
    view.ax = Math.max(0.9, (x1 - x0) / 2); view.ay = Math.max(0.7, (y1 - y0) / 2);
  }

  function unitPx(){
    const w = stage.clientWidth, h = stage.clientHeight;
    return Math.min(w/(2*view.ax + 3.4), h/(2*view.ay + 3.8)) * zoom;
  }

  function project(p){
    const w = stage.clientWidth, h = stage.clientHeight, unit = unitPx();
    return {
      x: w/2 + (p.x - view.cx)*unit + pan.x,
      y: h/2 - (p.y - view.cy)*unit + pan.y,
      s: 1
    };
  }

  function nodeVisible(nd){ return show.core ? nd.frame : true; }

  function draw(){
    const pr = nodes.map(project);
    nodes.forEach((nd,i)=>{
      const el = els[i], p = pr[i];
      if(!nodeVisible(nd)){ el.hidden = true; return; }
      el.hidden = false;
      el.classList.toggle("main", nd.main);
      el.classList.toggle("up", !!nd.up);
      el.style.transform = "translate(" + p.x.toFixed(1) + "px," + p.y.toFixed(1) + "px)";
    });

    // Sống cá: từ chân xương xa nhất bên trái tới quá từ cuối một chút.
    if(spineLn){
      const a = project({x:view.spL - 0.4, y:0}), b = project({x:view.spR + 0.6, y:0});
      spineLn.setAttribute("d", "M" + a.x.toFixed(1) + " " + a.y.toFixed(1) + "H" + b.x.toFixed(1));
    }

    const near = i => sel === null || sel === i || nodes[sel].head === i;
    nodes.forEach((nd,i)=>{
      const rl = rails[i];
      if(!rl) return;
      if(nd.main || !nodeVisible(nd) || nd.rail > nd.x - 0.01 || !near(i)){ rl.setAttribute("visibility","hidden"); return; }
      const p = pr[i], r = project({x:nd.rail, y:nd.y});
      rl.setAttribute("visibility","visible");
      rl.setAttribute("d", "M" + (p.x - 6).toFixed(1) + " " + p.y.toFixed(1) + "H" + r.x.toFixed(1));
    });

    edges.forEach((e,i)=>{
      const ln = lines[i], c = nodes[e.a];
      // quan hệ giữa hai từ cùng trên sống đã nằm trong đường ngang
      let vis = nodeVisible(c) && nodeVisible(nodes[e.b]) && !c.main;
      if(vis && sel !== null) vis = (e.a === sel || e.b === sel);
      if(!vis){ ln.setAttribute("visibility","hidden"); return; }
      ln.setAttribute("visibility","visible");
      // Xương: từ chân trên sống / thanh của từ chi phối, xiên tới chấm của từ
      // phụ thuộc, dừng trước chấm một chút.
      const f = project({x:c.fx, y:c.fy}), q = pr[e.a], p = pr[e.b];
      const dx = q.x - f.x, dy = q.y - f.y, L = Math.sqrt(dx*dx + dy*dy) || 1, t = Math.max(0, 1 - 7 / L);
      const s0 = Math.abs(f.x - p.x) + Math.abs(f.y - p.y) < 1 ? Math.min(0.5, 7 / L) : 0;   // mọc từ chấm: chừa chấm ra
      ln.setAttribute("d", "M" + (f.x + dx*s0).toFixed(1) + " " + (f.y + dy*s0).toFixed(1) +
                           "L" + (f.x + dx*t).toFixed(1) + " " + (f.y + dy*t).toFixed(1));
    });
  }

  // ---------- phân trang câu ----------
  const pagerEl = document.getElementById("pager");

  function mkPg(txt, cls, on, title){
    const b = document.createElement("button");
    b.type = "button"; b.className = "pg" + (cls ? " " + cls : ""); b.textContent = txt;
    if(title) b.title = title;
    if(on) b.addEventListener("click", on); else b.disabled = true;
    return b;
  }

  function sentenceOf(btn){
    const el = btn.querySelector(".s");
    return el ? el.textContent : (btn.title || "");
  }

  function setPager(n, idx, label, onPick, titles){
    pagerEl.innerHTML = "";
    if(!n || n < 2){ pagerEl.hidden = true; return; }
    pagerEl.hidden = false;
    pagerEl.appendChild(mkPg("‹", "", idx > 0 ? ()=>onPick(idx - 1) : null));
    for(let k = 0; k < n; k++){
      const d = mkPg("", "pgdot", ()=>onPick(k), (k + 1) + ". " + (titles && titles[k] ? titles[k] : ""));
      if(k === idx) d.setAttribute("aria-current", "true");
      pagerEl.appendChild(d);
    }
    pagerEl.appendChild(mkPg("›", "", idx < n - 1 ? ()=>onPick(idx + 1) : null));
    // Nhãn đứng SAU cụm nút, không phải trước. Nó là thứ mô tả câu đang xem chứ
    // không phải nút bấm, nên để lẫn vào đầu hàng thì dễ tưởng là bấm được --
    // nhất là khi nó cũng là một thẻ .pg như mấy nút kia.
    if(label){ const l = mkPg(label, "lab", null); l.disabled = false; pagerEl.appendChild(l); }
  }

  function select(nd){ sel = (sel === nd.i) ? null : nd.i; applySel(); }

  function applySel(){
    const s = curSent;
    if(sel === null){
      els.forEach(el=>el.classList.remove("sel","dim"));
      showNote(); draw(); return;
    }
    const near = new Set([sel]);
    edges.forEach(e=>{ if(e.a === sel) near.add(e.b); if(e.b === sel) near.add(e.a); });
    els.forEach((el,i)=>{
      el.classList.toggle("sel", i === sel);
      el.classList.toggle("dim", !near.has(i));
    });
    showDetail(s); draw();
  }

  const panelEl = document.getElementById("panel");

  function showNote(){
    panelEl.hidden = true;
    document.getElementById("panel-body").innerHTML = "";
  }

  function showDetail(s){
    panelEl.hidden = false;
    const body = document.getElementById("panel-body");
    const t = s.tokens[sel], info = funcInfo(t.func), nd = nodes[sel], rq = REQ_VI[info.req];
    const kids = s.tokens.map((x,i)=>x.head === sel ? x.w : null).filter(Boolean);
    document.getElementById("panel-title").textContent = "Chi tiết";
    let html = '<div class="detail"><p class="word">' + t.w + '</p><dl>';
    html += '<dt>Từ loại</dt><dd>' + (POS_VI[t.pos] || t.pos) + ' <span class="code">' + t.pos + '</span></dd>';
    html += '<dt>Chức vụ</dt><dd>' + info.vi + ' <span class="code">' + info.ab + '</span></dd>';
    html += '<dt>Vai trò</dt><dd><span class="pill ' + rq.cls + '">' + rq.t + '</span></dd>';
    html += '<dt>Thuộc cụm</dt><dd>' + (CHUNK_LAB[nd.chunk] || "—") + '</dd>';
    html += '<dt>Gắn vào</dt><dd>' + (t.head >= 0 ? '<em>' + s.tokens[t.head].w + '</em>' : 'gốc câu') + '</dd>';
    html += '<dt>Kéo theo</dt><dd>' + (kids.length ? kids.join(", ") : "—") + '</dd>';
    html += '</dl></div>';
    body.innerHTML = html;
    const back = document.createElement("button");
    back.className = "backlink"; back.textContent = "← bỏ chọn";
    back.addEventListener("click", ()=>{ sel = null; applySel(); });
    body.appendChild(back);
  }

  function buildRibbon(s){
    const rib = document.getElementById("ribbon");
    rib.innerHTML = "";
    const groups = [];
    s.tokens.forEach((t,i)=>{
      const f = chunkOf(s.tokens, i);
      const last = groups[groups.length-1];
      if(last && last.f === f) last.words.push(t.w);
      else groups.push({f, words:[t.w]});
    });
    groups.forEach(g=>{
      const info = funcInfo(g.f);
      const div = document.createElement("div");
      div.className = "chunk k-" + info.k + (info.req === "opt" ? " opt" : "");
      const txt = document.createElement("span"); txt.className = "txt"; txt.textContent = g.words.join(" ");
      const lab = document.createElement("span"); lab.className = "lab"; lab.textContent = CHUNK_LAB[g.f] || "";
      div.appendChild(txt); div.appendChild(lab);
      rib.appendChild(div);
    });
    document.getElementById("vi").textContent = s.vi;

    // Ghi chú của câu. Trước đây trường note: trong verbs-data.js không có chỗ
    // nào đọc tới -- bảy mươi ghi chú viết tay nằm vô hình. Tên showNote() dễ
    // gây hiểu nhầm: hàm đó đi ẩn bảng chi tiết token, không liên quan gì.
    //
    // Chèn bằng innerHTML vì ghi chú có sẵn <strong>/<em> để nhấn đúng từ đang
    // bàn. Nội dung do chính repo này viết, không phải do người dùng nhập.
    //
    // Trang ngữ nghĩa không khai #snote và câu của nó cũng không có note, nên
    // cả hai vế đều phải chịu được giá trị rỗng.
    const noteEl = document.getElementById("snote");
    if(noteEl){
      if(s.note){ noteEl.innerHTML = s.note; noteEl.hidden = false; }
      else { noteEl.innerHTML = ""; noteEl.hidden = true; }
    }
  }

  const chips = document.getElementById("chips");
  const chipEls = [], groupHeads = [], groupWraps = [];

  function openGroup(g){
    groupWraps.forEach((w,k)=>{
      const on = k === g;
      w.hidden = !on;
      groupHeads[k].setAttribute("aria-expanded", on ? "true" : "false");
    });
  }

  function addGroup(name){
    const h = document.createElement("button");
    h.className = "vgroup"; h.type = "button";
    h.setAttribute("aria-expanded","false");
    h.textContent = name;
    const w = document.createElement("div");
    w.className = "gwrap"; w.hidden = true;
    const g = groupHeads.length;
    // luôn giữ đúng một nhóm mở: bấm lại nhóm đang mở thì không đóng
    h.addEventListener("click", ()=>openGroup(g));
    chips.appendChild(h); chips.appendChild(w);
    groupHeads.push(h); groupWraps.push(w);
    return w;
  }

  GROUPS.forEach(g=>{
    const wrap = addGroup(g.name);
    for(let i = g.from; i < g.to; i++){
      const d = DATA[i];
      const b = document.createElement("button");
      b.className = "chip"; b.type = "button"; b.setAttribute("role","tab");
      b.setAttribute("aria-selected", i === cur ? "true" : "false");
      b.innerHTML = '<span class="verb"></span><span class="pat"></span>';
      b.querySelector(".verb").textContent = d.verb;
      // Một động từ có nhiều câu thì cũng có nhiều khung, nên dán mỗi khung của
      // câu đầu lên chip là nói sai. "+n" cho biết còn n khung nữa ở thanh chấm
      // dưới sân; khung của câu ĐANG vẽ thì hiện ngay trên thanh chấm đó.
      const nvar = variantsOf(i).length;
      b.querySelector(".pat").textContent = d.pattern + (nvar > 1 ? "  +" + (nvar - 1) : "");
      b.title = variantsOf(i).map((s,k)=>(k + 1) + ". " + s.en).join("\n");
      b.addEventListener("click", ()=>{
        chipEls.forEach((c,j)=>c.setAttribute("aria-selected", j === i ? "true" : "false"));
        showVariant(i, 0);
        if(exbar) exbar.hidden = true;
        if(i < OMNI.length) renderOmni(i);
      });
      chipEls[i] = b;
      wrap.appendChild(b);
    }
  });

  // Kéo ở đâu cũng là dời cả cây; bấm (không kéo) vào một từ thì chọn từ đó.
  let drag = null;
  stage.addEventListener("pointerdown", ev=>{
    // các nút nổi trong sân (thanh chấm, nút zoom) phải tự nhận click:
    // nếu để stage bắt con trỏ thì nút không bao giờ nhận được sự kiện
    if(ev.target.closest && ev.target.closest(".pager, .ctrls, .panel, .helpbtn, .axes")) return;
    const nEl = ev.target.closest && ev.target.closest(".node");
    stage.setPointerCapture(ev.pointerId);
    drag = {x:ev.clientX, y:ev.clientY, px:pan.x, py:pan.y, i:nEl ? els.indexOf(nEl) : -1, moved:false};
  });
  stage.addEventListener("pointermove", ev=>{
    if(!drag) return;
    const dx = ev.clientX - drag.x, dy = ev.clientY - drag.y;
    if(!drag.moved && Math.abs(dx) + Math.abs(dy) > 4){ drag.moved = true; stage.classList.add("dragging"); }
    if(!drag.moved) return;
    pan.x = drag.px + dx;
    pan.y = drag.py + dy;
    draw();
  });
  function endDrag(){
    if(!drag) return;
    if(!drag.moved){
      if(drag.i >= 0) select(nodes[drag.i]);
      else { sel = null; applySel(); }
    }
    drag = null;
    stage.classList.remove("dragging");
  }
  stage.addEventListener("pointerup", endDrag);
  stage.addEventListener("pointercancel", endDrag);
  stage.addEventListener("wheel", ev=>{
    ev.preventDefault();
    zoom = Math.max(0.55, Math.min(2.2, zoom * (ev.deltaY > 0 ? 0.92 : 1.08)));
    draw();
  }, {passive:false});

  document.getElementById("zin").addEventListener("click", ()=>{ zoom = Math.min(2.2, zoom*1.15); draw(); });
  document.getElementById("zout").addEventListener("click", ()=>{ zoom = Math.max(0.55, zoom/1.15); draw(); });
  document.getElementById("reset").addEventListener("click", ()=>{
    zoom = 1; pan.x = 0; pan.y = 0; draw();
  });
  document.getElementById("t-core").addEventListener("change", e=>{
    show.core = e.target.checked;
    if(show.core && sel !== null && !nodes[sel].frame) sel = null;
    layout();
    applySel();
  });


  // ---------- chủ đề ngữ nghĩa: xổ ngay trên thanh bên ----------
  const TOPICS = window.TOPICS || [];
  let topen = -1, vcur = -1, ecur = -1;
  const tpChips = [], tpWraps = [];

  const exbar = document.getElementById("exbar");
  const exrows = document.getElementById("exrows");

  function renderExRows(){
    const v = TOPICS[topen].v[vcur];
    document.getElementById("exbar-t").innerHTML =
      TOPICS[topen].n + " · <b>" + v.v + "</b> · " + v.ex.length + " câu";
    exrows.innerHTML = "";
    v.ex.forEach((e,k)=>{
      const b = document.createElement("button");
      b.className = "exrow"; b.type = "button";
      if(k === ecur) b.setAttribute("aria-current", "true");
      b.innerHTML = '<span class="i"></span><span class="s"></span>';
      b.querySelector(".i").textContent = String(k + 1).padStart(2,"0");
      b.querySelector(".s").textContent = e.en;
      b.addEventListener("click", ()=>showEx(k));
      exrows.appendChild(b);
    });
    exbar.hidden = false;
  }

  function showEx(k){
    const v = TOPICS[topen].v[vcur];
    ecur = k;
    chipEls.forEach(c=>c.setAttribute("aria-selected","false"));
    buildSentence({en:v.ex[k].en, vi:"", tokens:v.ex[k].t});
    setPager(v.ex.length, k, v.v, showEx, v.ex.map(e=>e.en));
    renderExRows();
  }

  function pickVerb(i, j){
    topen = i; vcur = j;
    tpWraps[i].querySelectorAll(".tv").forEach((t,k)=>
      t.setAttribute("aria-selected", k === j ? "true" : "false"));
    showEx(0);
  }

  function toggleTopic(i){
    const open = topen === i && tpWraps[i] && !tpWraps[i].hidden;
    tpWraps.forEach((wr,k)=>{ if(wr){ wr.hidden = true; tpChips[k].setAttribute("aria-expanded","false"); } });
    if(open){ return; }
    topen = i; vcur = -1;
    tpChips[i].setAttribute("aria-expanded","true");
    const wr = tpWraps[i];
    wr.hidden = false;
    if(!wr.childElementCount){
      TOPICS[i].v.forEach((v,j)=>{
        const b = document.createElement("button");
        b.className = "tv"; b.type = "button"; b.setAttribute("role","tab");
        b.setAttribute("aria-selected","false");
        b.textContent = v.v;
        b.title = v.ex.length + " câu";
        b.addEventListener("click", ()=>pickVerb(i, j));
        wr.appendChild(b);
      });
    }
  }

  if(TOPICS.length){
    const twrap = addGroup("Chủ đề ngữ nghĩa");
    TOPICS.forEach((t,i)=>{
      const b = document.createElement("button");
      b.className = "chip tp"; b.type = "button";
      b.setAttribute("aria-expanded","false");
      b.innerHTML = '<span class="verb"></span>';
      b.querySelector(".verb").textContent = t.n;
      b.title = t.b || t.n;
      b.addEventListener("click", ()=>toggleTopic(i));
      twrap.appendChild(b);
      const wr = document.createElement("div");
      wr.className = "tvwrap"; wr.hidden = true;
      twrap.appendChild(wr);
      tpChips.push(b); tpWraps.push(wr);
    });
  }

  window.addEventListener("resize", draw);

  // Chú dẫn cách đọc đồ thị: giấu sau nút "?" thay vì hiện thường trực.
  // Bấm ra ngoài thì đóng lại -- nếu không, nó che mất góc sân mà người dùng
  // phải quay lại bấm đúng cái nút nhỏ kia mới tắt được.
  const helpBtn = document.getElementById("helpbtn"), axesEl = document.getElementById("axes");
  if(helpBtn && axesEl){
    const moHelp = on => { axesEl.hidden = !on; helpBtn.setAttribute("aria-expanded", on ? "true" : "false"); };
    helpBtn.addEventListener("click", ev=>{ ev.stopPropagation(); moHelp(axesEl.hidden); });
    document.addEventListener("click", ev=>{
      if(!axesEl.hidden && !axesEl.contains(ev.target) && ev.target !== helpBtn) moHelp(false);
    });
    document.addEventListener("keydown", ev=>{ if(ev.key === "Escape" && !axesEl.hidden) moHelp(false); });
  }
  const otabs = document.getElementById("otabs"), obody = document.getElementById("obody");
  let ocur = 0;

  function renderOmni(i){
    // Không đòi #otabs nữa: omnibus.html đã bỏ thanh tab đó đi, và nếu vẫn bắt
    // buộc phải có thì ghi chú lặng lẽ không bao giờ được vẽ.
    if(!obody || !OMNI[i]) return;
    ocur = i;
    if(otabs) [...otabs.children].forEach((t,j)=>t.setAttribute("aria-selected", j === i ? "true" : "false"));
    const o = OMNI[i];
    let left = '<div class="ohead"><span class="ov">' + o.v + '</span><span class="gl">' + o.gloss + '</span></div>';
    left += '<p class="ointro">' + o.intro + '</p>';
    left += '<div class="ocol"><h4>Cách dùng phổ biến</h4><ul class="ouse">';
    o.use.forEach(u=>{ left += '<li><span class="lab">' + u[0] + '</span> — <span class="ex">' + u[1] + '</span></li>'; });
    left += '</ul></div>';
    if(o.tb){
      left += '<div class="ocol"><h4>Bảng tóm tắt nhanh</h4><table class="otable"><thead><tr>' +
              '<th>Cấu trúc</th><th>Ý nghĩa</th><th>Ví dụ</th></tr></thead><tbody>';
      o.tb.forEach(r=>{ left += '<tr><td>' + r[0] + '</td><td>' + r[1] + '</td><td>' + r[2] + '</td></tr>'; });
      left += '</tbody></table></div>';
    }

    let right = "";
    if(o.st){
      right += '<div class="ocol"><h4>Cấu trúc đặc biệt</h4><div class="ostruct">';
      o.st.forEach(t=>{
        right += '<div class="st"><code>' + t[0] + '</code><span class="mn">' + t[1] +
                 (t[2] ? ' <em>' + t[2] + '</em>' : '') + '</span></div>';
      });
      right += '</div></div>';
    }
    if(o.ph){
      right += '<div class="ocol"><h4>Phrasal verbs</h4><div class="oph">';
      // Phần tử thứ ba là câu ví dụ, tuỳ chọn. Danh sách phrasal verb mà chỉ
      // có nghĩa dịch thì người học vẫn không biết đặt nó vào câu kiểu gì —
      // nhất là chỗ tân ngữ chen vào giữa hay đứng sau.
      o.ph.forEach(t=>{
        right += '<div><span class="pv">' + t[0] + '</span><span class="mn">' + t[1] +
                 (t[2] ? '<em class="pvx">' + t[2] + '</em>' : '') + '</span></div>';
      });
      right += '</div></div>';
    }
    if(o.id){
      right += '<div class="ocol"><h4>Thành ngữ</h4><div class="oph">';
      o.id.forEach(t=>{ right += '<div><span class="pv">' + t[0] + '</span><span class="mn">' + t[1] + '</span></div>'; });
      right += '</div></div>';
    }
    // Trước đây chỗ này có nút "Xem khung câu trên đồ thị ↑" để cuộn ngược lên
    // sân. Nó sinh ra khi phần ghi chú còn là một khối riêng nằm xa phía dưới;
    // từ lúc ghi chú dọn vào chung panel với dải chức vụ thì đồ thị đã ở ngay
    // trên đầu, cuộn một nhịp là thấy — nút thành thừa.
    obody.innerHTML = '<div>' + left + '</div><div>' + right + '</div>';
  }

  // ---------- khởi động ----------
  // Thanh tab động từ trong phần ghi chú chỉ dựng nếu trang còn khai #otabs.
  // omnibus.html đã bỏ: chip ở thanh bên đã chọn động từ rồi, để hai chỗ cùng
  // chọn một thứ thì sớm muộn chúng cũng lệch nhau.
  if(otabs && OMNI.length){
    OMNI.forEach((o,i)=>{
      const b = document.createElement("button");
      b.className = "otab"; b.type = "button"; b.setAttribute("role","tab");
      b.setAttribute("aria-selected", i === 0 ? "true" : "false");
      b.textContent = o.v;
      b.addEventListener("click", ()=>renderOmni(i));
      otabs.appendChild(b);
    });
  }
  // Vẽ ghi chú lần đầu — tách khỏi nhánh trên, vì trước đây nó nằm lọt bên
  // trong nên bỏ #otabs là mất luôn cả lần vẽ đầu tiên.
  if(OMNI.length) renderOmni(cur);

  openGroup(0);

  if(DATA.length){
    // Qua showVariant chứ không qua build(), để câu mở đầu cũng dựng sẵn thanh
    // chấm nếu động từ đó có câu thêm.
    showVariant(cur, 0);
  }else if(TOPICS.length){
    // Trang ngữ nghĩa không có câu chú giải tay, nên mở sẵn chủ đề đầu tiên
    // thay vì để sân trống.
    toggleTopic(0);
    pickVerb(0, 0);
  }
})();
