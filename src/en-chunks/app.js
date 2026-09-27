/* Khối tiếng Anh — trang học theo khối.
   Dữ liệu: window.CHUNK_DATA, index.php nạp từ chunks.json.

   Phần lõi là bộ dò khối. Mỗi khối trong kho thành một regex chịu được:
     - chia động từ:            make up your mind  ~  made up my mind
     - chỗ trống "…":           I'd rather… than…  ~  I'd rather walk than drive
     - phần tuỳ chọn "(at)":    take a look (at)
     - biến thể "a / b":        kind of / sort of
     - chỗ giữ vị trí:          someone, something, your, yourself
   Câu ví dụ trong thẻ đi qua bộ dò này, nên
   một câu bất kỳ cũng tách được thành khối. */
(function () {
  "use strict";
  const DATA = window.CHUNK_DATA;
  if (!DATA) return;

  // ------------------------------------------------------------------ dữ liệu
  const CHUNKS = [];
  DATA.groups.forEach((note, ni) => {
    note.idx = ni;
    note.count = 0;
    note.doneCount = 0;
    note.sections.forEach(sec => sec.chunks.forEach(ch => {
      ch.id = CHUNKS.length;
      ch.note = note;
      ch.sec = sec;
      note.count++;
      if (ch.done) note.doneCount++;
      CHUNKS.push(ch);
    }));
  });

  // ------------------------------------------------------------------ bộ dò
  const IRREG = {
    be: "be am is are was were been being", have: "have has had having", do: "do does did done doing",
    go: "go goes went gone going", get: "get gets got gotten getting", make: "make makes made making",
    take: "take takes took taken taking", come: "come comes came coming", give: "give gives gave given giving",
    keep: "keep keeps kept keeping", put: "put puts putting", let: "let lets letting", say: "say says said saying",
    tell: "tell tells told telling", think: "think thinks thought thinking", feel: "feel feels felt feeling",
    find: "find finds found finding", know: "know knows knew known knowing", see: "see sees saw seen seeing",
    run: "run runs ran running", bring: "bring brings brought bringing", catch: "catch catches caught catching",
    break: "break breaks broke broken breaking", fall: "fall falls fell fallen falling", hold: "hold holds held holding",
    lose: "lose loses lost losing", mean: "mean means meant meaning", meet: "meet meets met meeting",
    pay: "pay pays paid paying", set: "set sets setting", sit: "sit sits sat sitting", speak: "speak speaks spoke spoken speaking",
    spend: "spend spends spent spending", stand: "stand stands stood standing", understand: "understand understands understood understanding",
    write: "write writes wrote written writing", leave: "leave leaves left leaving", buy: "buy buys bought buying",
    sell: "sell sells sold selling", send: "send sends sent sending", lend: "lend lends lent lending",
    drive: "drive drives drove driven driving", eat: "eat eats ate eaten eating", drink: "drink drinks drank drunk drinking",
    sleep: "sleep sleeps slept sleeping", wake: "wake wakes woke woken waking", forget: "forget forgets forgot forgotten forgetting",
    choose: "choose chooses chose chosen choosing", show: "show shows showed shown showing", hear: "hear hears heard hearing",
    begin: "begin begins began begun beginning", win: "win wins won winning", become: "become becomes became becoming",
    cut: "cut cuts cutting", hurt: "hurt hurts hurting", grow: "grow grows grew grown growing", throw: "throw throws threw thrown throwing",
    fly: "fly flies flew flown flying", try: "try tries tried trying", can: "can could", will: "will would", shall: "shall should"
  };
  // Từ chức năng: không chia, để khỏi khớp bừa.
  const KEEP = new Set(("the a an of to in on at for with from by up out off about into over and or but if so as than " +
    "that this these those it its you i me my your we us our they them their he him his she her not no what how why " +
    "when where who which all any some just very too more most much many there here then now well like").split(" "));

  const APOS = "['’]";
  const WCH = "[A-Za-z0-9'’-]";
  const POSS = "(?:my|your|his|her|its|our|their|one" + APOS + "s|[A-Za-z-]+" + APOS + "s)";
  const REFL = "(?:myself|yourself|himself|herself|itself|ourselves|yourselves|themselves|oneself)";
  const SLOT1 = WCH + "+(?:\\s+" + WCH + "+)??";       // someone: một hai từ
  const SLOT3 = WCH + "+(?:\\s+" + WCH + "+){0,2}?";   // something: tới ba từ
  const PLACE = { someone: SLOT1, somebody: SLOT1, sb: SLOT1, something: SLOT3, sth: SLOT3,
                  your: POSS, "one's": POSS, "someone's": POSS, yourself: REFL, oneself: REFL };
  const SEP = "(?:\\s*[,;:]\\s*|\\s+)";
  const GAP = "[^.!?\\n]{1,80}?";
  const L = "(?<![A-Za-z0-9'’])", R = "(?![A-Za-z0-9'’])";

  // Mạo từ trong khối là chỗ cho mọi từ hạn định: "the flight" ~ "our flight",
  // "check a bag" ~ "check one bag".
  const DETS = "a an the one my your his her its our their this that these those some any every another each no".split(" ");
  const DET = "(?:" + DETS.join("|") + ")";
  // "be" gồm cả dạng dính vào từ trước: we're, the printer's, I'm.
  const BE = "(?:(?<![A-Za-z0-9'’])(?:be|am|is|are|was|were|been|being)|(?<=[A-Za-z])" + APOS + "(?:s|re|m))";
  // Sau "be" hay có trạng từ chen vào: "I'm really into jazz".
  const ADV = "(?:\\s+(?:really|so|very|quite|totally|just|still|also|always|never|not|already))?";
  const BE_LIKE = new Set(["i'm", "it's", "you're", "we're", "they're", "he's", "she's", "that's", "there's",
                           "am", "is", "are", "was", "were"]);
  // Động từ mở đầu collocation / phrasal verb: sau nó cho chen tới ba từ, vì
  // "make a big difference", "spend a lot of time", "pick you up" đều là khối đó.
  const REGVERB = new Set(("miss check book call visit pick drop sort look turn fill work try change ask wait save waste " +
    "raise reach figure carry hang calm cheer switch help need want love hate enjoy finish start stop play watch listen " +
    "talk plan move open close follow join apply order cook clean wash fix use").split(" "));
  const NO_FLEX = new Set(["be", "can", "will", "shall"]);
  // Chữ chen vào là tân ngữ, hạn định, tính từ — không phải từ nối, không thì
  // "have a go" khớp nhầm "have to go".
  const FILL = "(?:\\s+(?!(?:to|and|or|but|if|than|because|when|so)(?![A-Za-z0-9'’]))" + WCH + "+){0,3}?";
  // "(time)" là chỗ điền, không phải chữ "time": "It takes an hour to get there".
  const SLOTWORD = new Set(["time", "money", "place", "sth", "something", "someone", "sb", "number"]);

  const reEsc = s => s.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

  function forms(w) {
    if (IRREG[w]) return IRREG[w].split(" ");
    if (!/^[a-z]{3,}$/.test(w) || KEEP.has(w)) return [w];
    const f = new Set([w, w + "s", w + "ed", w + "ing"]);
    if (/(s|x|z|ch|sh|o)$/.test(w)) f.add(w + "es");
    if (/[^aeiou]y$/.test(w)) { f.add(w.slice(0, -1) + "ies"); f.add(w.slice(0, -1) + "ied"); }
    if (/e$/.test(w)) { f.add(w + "d"); f.add(w.slice(0, -1) + "ing"); }
    if (/^[^aeiou]*[aeiou][bdgklmnprt]$/.test(w)) { f.add(w + w.slice(-1) + "ed"); f.add(w + w.slice(-1) + "ing"); }
    // "It turns out" cũng phải khớp "It turned out": lấy lại gốc của dạng -s.
    if (/[^su]s$/.test(w) && w.length > 3) forms(w.slice(0, -1)).forEach(x => f.add(x));
    return [...f];
  }

  function tokPat(t, prev) {
    const w = t.replace(/’/g, "'");
    if (w === "{be}" || w === "be") return BE + ADV;
    // "or something" là chữ thật, không phải chỗ điền: nó không được khớp "or coffee".
    if (PLACE[w] && prev !== "or") return PLACE[w];
    if (w === "a" || w === "an" || w === "the") return DET;
    const alts = forms(w).map(x => reEsc(x).replace(/'/g, APOS));
    const p = alts.length > 1 ? "(?:" + alts.join("|") + ")" : alts[0];
    return BE_LIKE.has(w) ? p + ADV : p;
  }

  // Một đoạn liền mạch của khối (giữa hai chỗ trống "…") thành regex.
  // selfBound: từ đầu tự lo ranh giới trái (be có thể dính vào từ trước).
  function segPat(seg, flex) {
    const toks = seg.match(/\{be\}|\([^)]*\)|[a-z0-9'’-]+/g) || [];
    let out = "", lead = "", words = 0, selfBound = false, prev = "";   // lead: phần tuỳ chọn trước từ bắt buộc đầu tiên
    for (const t of toks) {
      if (t[0] === "(") {
        const inner = t.slice(1, -1).trim().split(/\s+/).filter(Boolean);
        if (!inner.length) continue;
        const p = inner.length === 1 && SLOTWORD.has(inner[0]) ? SLOT3 : inner.map(x => tokPat(x)).join(SEP);
        if (out) out += "(?:" + SEP + p + ")?";
        else lead += "(?:" + p + SEP + ")?";
        continue;
      }
      // Mạo từ giữa khối được phép vắng: "make a mistake" ~ "makes mistakes".
      if (out && (t === "a" || t === "an" || t === "the")) {
        out += "(?:" + SEP + DET + ")?";
        prev = t;
        continue;
      }
      if (!out && !lead && (t === "{be}" || t === "be")) selfBound = true;
      out += (out ? SEP : lead) + tokPat(t, prev);
      prev = t;
      if (!words && flex && !NO_FLEX.has(t) && (IRREG[t] || REGVERB.has(t))) out += FILL;
      lead = "";
      words++;
    }
    return { pat: out, words, selfBound };
  }

  function variantsOf(c) {
    let vs = c.toLowerCase().replace(/’/g, "'").replace(/\.\.\./g, "…")
      // "+ -ing", "+ noun / -ing" là chỗ điền ngữ pháp, tức một chỗ trống.
      .replace(/\s*\+\s*(?:noun|-ing|verb|infinitive|adj(?:ective)?|sb|sth)(?:\s*\/\s*(?:noun|-ing|verb|infinitive))*/g, "…")
      .replace(/[?!"“”]/g, "")
      .split(/\s+\/\s+/).map(v => v.trim()).filter(Boolean);
    // Vế sau ngắn hơn chỉ thay phần cuối của vế đầu: "miss a flight / a train"
    // là "miss a train", "angry with someone / about something" là "angry about
    // something", "it's on your left / right" là "it's on your right". Để riêng
    // thì "about something" và "right" khớp bừa mọi câu.
    const base = vs.length ? vs[0].split(/\s+/) : [];
    vs = vs.map((v, i) => {
      const w = v.split(/\s+/);
      return i && w.length < base.length && w[0] !== base[0] ? base.slice(0, base.length - w.length).concat(w).join(" ") : v;
    });
    // "it's not working" cũng là "the printer's not working": thêm một vế bỏ chủ ngữ.
    for (const v of [...vs]) {
      const m = v.match(/^(?:it|that|there)'s\s+(.+)$/);
      if (m && !m[1].includes("…") && m[1].split(/\s+/).length >= 2) vs.push("{be} " + m[1]);
    }
    return vs;
  }

  // Chữ bắt buộc dài nhất mà mọi dạng chia đều chứa: "out" trong "carry out",
  // "carr" cho carry / carried. Câu không chứa chữ đó thì khỏi chạy regex, nên bộ
  // dò không chậm đi theo bình phương số khối. Bỏ qua chỗ trống, mạo từ, "be" và
  // phần tuỳ chọn vì chúng không bắt buộc có mặt nguyên văn.
  const NO_ANCHOR = new Set(["a", "an", "the", "be", "{be}"]);
  function anchorOf(v) {
    let best = "";
    for (const t of v.replace(/\([^)]*\)/g, " ").match(/\{be\}|[a-z0-9'’-]+/g) || []) {
      const w = t.replace(/’/g, "'");
      if (NO_ANCHOR.has(w) || PLACE[w]) continue;
      const fs = forms(w);
      let p = fs[0];
      for (const f of fs) while (!f.startsWith(p)) p = p.slice(0, -1);
      if (p.length > best.length) best = p;
    }
    return best;
  }

  CHUNKS.forEach(ch => {
    ch.res = [];
    ch.solo = [];
    ch.need = [];
    let single = true;
    variantsOf(ch.c).forEach(v => {
      const required = (v.replace(/\([^)]*\)/g, " ").match(/\{be\}|[a-z0-9'’-]+/g) || []).length;
      const parts = v.split("…").map(s => s.replace(/^[\s,;:]+|[\s,;:.]+$/g, "")).filter(Boolean)
        .map((s, i) => segPat(s, i === 0 && required >= 2)).filter(p => p.pat);
      if (!parts.length) return;
      const words = parts.reduce((a, p) => a + p.words, 0);
      if (words > 1 || parts.length > 1) single = false;
      const piece = p => (p.selfBound ? "" : L) + "(" + p.pat + ")" + R;
      try {
        const re = new RegExp(parts.map(piece).join(GAP), "dgi");
        // Khối một từ (well, like, actually…) chỉ tính khi đứng tách bằng dấu câu,
        // không thì "I like coffee" cũng bị coi là có khối "like".
        const solo = new RegExp("(?<=(?:^|[,;:—–\"“]\\s*))" + piece(parts[0]) + "(?=\\s*(?:[,;:—–.!?…\"”]|$))", "dgi");
        ch.res.push(re);
        ch.solo.push(solo);
        ch.need.push(anchorOf(v));
      } catch (e) {
        console.warn("Không dựng được regex cho khối", ch.c, e);
      }
    });
    ch.single = single;
  });

  const cache = new Map();
  // Mọi khối có trong câu, không chồng lên nhau. Khối của chính thẻ được ưu tiên,
  // rồi tới khối phủ nhiều chữ hơn.
  function analyze(text, targetId) {
    const key = targetId + "|" + text;
    if (cache.has(key)) return cache.get(key);
    const cands = [];
    const low = text.toLowerCase().replace(/’/g, "'");
    for (const ch of CHUNKS) {
      const isT = ch.id === targetId;
      const list = ch.single && !isT ? ch.solo : ch.res;
      for (let i = 0; i < list.length; i++) {
        if (ch.need[i] && !low.includes(ch.need[i])) continue;
        const re = list[i];
        re.lastIndex = 0;
        let m;
        while ((m = re.exec(text))) {
          if (!m[0]) { re.lastIndex++; continue; }
          const pieces = [];
          for (let g = 1; g < m.indices.length; g++) if (m.indices[g]) pieces.push(m.indices[g]);
          cands.push({ ch, pieces, target: isT, start: m.index,
                       cover: pieces.reduce((a, p) => a + p[1] - p[0], 0) });
        }
      }
    }
    cands.sort((a, b) => (b.target - a.target) || (b.cover - a.cover) || (a.start - b.start));
    const taken = [], out = [];
    for (const c of cands) {
      if (c.pieces.some(p => taken.some(t => p[0] < t[1] && t[0] < p[1]))) continue;
      out.push(c);
      taken.push(...c.pieces);
    }
    out.sort((a, b) => a.start - b.start);
    cache.set(key, out);
    return out;
  }

  // ------------------------------------------------------------------ vẽ câu
  const h = s => String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
  const WORD = /[A-Za-z0-9][A-Za-z0-9'’-]*/g;
  const countWords = s => (s.match(WORD) || []).length;

  function tokens(str, cls) {
    return str.replace(/([A-Za-z0-9][A-Za-z0-9'’-]*)|(\s+)|([^A-Za-z0-9\s]+)/g, (all, w, s, p) =>
      w ? '<span class="w' + (cls ? " " + cls : "") + '">' + h(w) + "</span>" : s ? " " : '<span class="p">' + h(p) + "</span>");
  }

  // Câu → ô: khối là một ô màu theo nhóm, từ lẻ là ô nhỏ. Trả thêm số từ, số
  // đơn vị (khối + từ lẻ) và dãy ô cho thước trí nhớ.
  function sentence(text, targetId) {
    const found = analyze(text, targetId);
    const marks = [];
    found.forEach(f => f.pieces.forEach((p, pi) => marks.push({ s: p[0], e: p[1], f, pi })));
    marks.sort((a, b) => a.s - b.s);
    let html = "", pos = 0;
    const slots = [];
    const loose = str => { html += tokens(str, "loose"); for (let i = countWords(str); i > 0; i--) slots.push(null); };
    for (const mk of marks) {
      loose(text.slice(pos, mk.s));
      const ch = mk.f.ch, frame = mk.f.pieces.length > 1;
      html += '<span class="ck c' + ch.note.idx + (mk.f.target ? " target" : "") + (frame ? " frame" : "") +
              '" data-id="' + ch.id + '" title="' + h(ch.c + " — " + ch.vi) + '">' + tokens(text.slice(mk.s, mk.e), "") +
              (frame ? "<sup>" + (mk.pi + 1) + "</sup>" : "") + "</span>";
      if (mk.pi === 0) slots.push({ c: ch.note.idx, t: mk.f.target,
                                    n: mk.f.pieces.reduce((a, p) => a + countWords(text.slice(p[0], p[1])), 0) });
      pos = mk.e;
    }
    loose(text.slice(pos));
    const words = countWords(text);
    return { html, found, words, units: slots.length, slots };
  }

  // Thước trí nhớ làm việc: một ô cho mỗi từ (chế độ Từ) hoặc mỗi đơn vị (chế
  // độ Khối), trên nền dải 7±2 — sức chứa của trí nhớ làm việc.
  function meter(s, big) {
    const wordCells = [], unitCells = [];
    s.slots.forEach(sl => {
      if (!sl) { wordCells.push("<i></i>"); unitCells.push("<i></i>"); return; }
      const cls = "c" + sl.c + (sl.t ? " t" : "");
      for (let i = 0; i < sl.n; i++) wordCells.push('<i class="' + cls + '"></i>');
      unitCells.push('<i class="' + cls + ' u"></i>');
    });
    const row = (cells, n, label, cls) =>
      '<div class="slots ' + cls + (n > 9 ? " over" : "") + '"><span class="band" aria-hidden="true"></span>' + cells.join("") +
      '<span class="lbl"><b>' + n + "</b> " + label + "</span></div>";
    return '<div class="meter' + (big ? " big" : "") + '">' +
      row(wordCells, s.words, "từ", "as-words") + row(unitCells, s.units, "khối", "as-units") + "</div>";
  }

  // ------------------------------------------------------------------ trạng thái
  const fold = s => s.normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/đ/gi, "d").toLowerCase();
  // Không còn mục "Tất cả": vẽ cả nghìn thẻ một lần làm trang ì. Mỗi lần chỉ
  // một nhóm, và chia trang PER thẻ một; tìm kiếm vẫn quét cả tám nhóm nhưng
  // kết quả cũng chia trang. Địa chỉ #nhóm/trang để quay lại đúng chỗ.
  const PER = 40;
  const state = { note: DATA.groups[0].id, level: "all", q: "", mode: "chunks", page: 0 };
  const fromHash = () => {
    const [id, pg] = decodeURIComponent(location.hash.slice(1)).split("/");
    if (DATA.groups.some(n => n.id === id)) state.note = id;
    state.page = Math.max(0, (parseInt(pg, 10) || 1) - 1);
  };
  const toHash = () => history.replaceState(null, "", "#" + state.note + (state.page ? "/" + (state.page + 1) : ""));
  fromHash();
  try { if (localStorage.getItem("chunks.mode") === "words") state.mode = "words"; } catch (e) { /* không có storage */ }

  const $ = s => document.querySelector(s);
  const hub = $("#hub"), list = $("#list"), q = $("#q"), stat = $("#stat");

  // ------------------------------------------------------------------ mục lục 8 nhóm
  function renderHub() {
    hub.innerHTML = DATA.groups.map((n, i) =>
      '<button type="button" class="c' + i + '" data-note="' + h(n.id) + '"><span class="n">' + String(i + 1).padStart(2, "0") +
      " · " + h(n.level) + "</span><h2>" + h(n.title) + "</h2><p>" + h(n.desc) + '</p><span class="cnt">' + n.count + " khối" +
      (n.doneCount ? " · " + n.doneCount + " ✓" : "") + "</span></button>").join("");
    hub.addEventListener("click", e => {
      const b = e.target.closest("button[data-note]");
      if (!b) return;
      state.note = b.dataset.note;
      state.q = ""; q.value = ""; state.page = 0;
      toHash();
      render();
      $("#controls").scrollIntoView({ behavior: "smooth", block: "start" });
    });
  }

  // ------------------------------------------------------------------ thẻ
  // Thẻ "lazy" là bản nháp rẻ: câu để nguyên từng từ, thước rỗng nhưng giữ đúng
  // chiều cao. Dò khối (hơn nghìn regex mỗi câu) chỉ chạy khi thẻ sắp cuộn tới.
  const EMPTY_METER = '<div class="meter"><div class="slots as-words"></div><div class="slots as-units"></div></div>';
  function card(ch, lazy) {
    let body;
    if (lazy) body = '<p class="sent">' + tokens(ch.ex, "loose") + "</p>" + EMPTY_METER;
    else {
      const s = sentence(ch.ex, ch.id);
      const hit = s.found.some(f => f.target);
      body = '<p class="sent' + (hit ? "" : " miss") + '">' + s.html + "</p>" + meter(s, false);
    }
    return '<article class="card c' + ch.note.idx + (lazy ? " lazy" : "") + '" id="k' + ch.id + '">' +
      '<div class="head"><h3>' + h(ch.c) + '</h3><span class="lv">' + h(ch.sec.level) + "</span>" +
      (ch.done ? '<span class="done" title="Đã dùng thật">✓</span>' : "") + "</div>" +
      '<p class="vi">' + h(ch.vi) + '</p><p class="use">' + h(ch.use) + "</p>" + body + "</article>";
  }

  // Thay bản nháp bằng thẻ thật. Trả về phần tử mới (phần tử cũ đã bị thay).
  function hydrate(el) {
    if (!el.classList.contains("lazy")) return el;
    if (io) io.unobserve(el);
    const tpl = document.createElement("template");
    tpl.innerHTML = card(CHUNKS[+el.id.slice(1)], false);
    const fresh = tpl.content.firstChild;
    el.replaceWith(fresh);
    return fresh;
  }
  const io = "IntersectionObserver" in window
    ? new IntersectionObserver(es => es.forEach(e => e.isIntersecting && hydrate(e.target)), { rootMargin: "800px 0px" })
    : null;
  const EAGER = 24;   // chừng này thẻ đầu vẽ thật luôn, để lần hiện đầu tiên không có bản nháp

  function visible(ch) {
    if (state.level !== "all" && !ch.sec.levels.includes(state.level)) return false;
    if (state.q) return fold([ch.c, ch.vi, ch.use, ch.ex].join(" ")).includes(fold(state.q));
    return true;
  }

  function render() {
    document.body.classList.toggle("mode-words", state.mode === "words");
    document.querySelectorAll("#mode button").forEach(b => b.setAttribute("aria-pressed", String(b.dataset.mode === state.mode)));
    document.querySelectorAll("#levels button").forEach(b => b.setAttribute("aria-pressed", String(b.dataset.level === state.level)));
    const searching = state.q !== "";
    hub.querySelectorAll("button").forEach(b => b.classList.toggle("on", !searching && b.dataset.note === state.note));

    const notes = searching ? DATA.groups : DATA.groups.filter(n => n.id === state.note);
    const items = [];
    for (const n of notes) for (const sec of n.sections) for (const ch of sec.chunks) if (visible(ch)) items.push(ch);
    const pages = Math.max(1, Math.ceil(items.length / PER));
    state.page = Math.min(state.page, pages - 1);
    const part = items.slice(state.page * PER, (state.page + 1) * PER);

    // Dựng lại khung nhóm / mục chỉ quanh các thẻ của trang này.
    let html = "", shown = 0, i = 0;
    while (i < part.length) {
      const n = part[i].note;
      let body = "";
      while (i < part.length && part[i].note === n) {
        const sec = part[i].sec, cs = [];
        while (i < part.length && part[i].sec === sec) cs.push(part[i++]);
        body += '<section class="sec"><h3 class="sech"><span class="num">' + sec.n + ".</span>" + h(sec.title) +
                ' <span class="lv">' + h(sec.level) + "</span></h3>" + (sec.desc ? '<p class="secd">' + h(sec.desc) + "</p>" : "") +
                '<div class="cards">' + cs.map((ch, k) => card(ch, io && shown + k >= EAGER)).join("") + "</div></section>";
        shown += cs.length;
      }
      html += '<div class="note c' + n.idx + '"><h2 class="noteh"><span class="dot"></span>' + h(n.title) +
              "</h2>" + (searching || state.page ? "" : '<p class="notei">' + h(n.intro) + "</p>") + body + "</div>";
    }
    if (io) io.disconnect();
    list.innerHTML = html ? pager(pages) + html + pager(pages) : '<p class="empty">Không có khối nào khớp.</p>';
    if (io) list.querySelectorAll(".card.lazy").forEach(el => io.observe(el));
    stat.textContent = items.length + " khối" + (searching ? " khớp “" + state.q + "” trong cả " + DATA.groups.length + " nhóm" : "") +
      (pages > 1 ? " · trang " + (state.page + 1) + "/" + pages : "");
  }

  // Thanh trang: đầu, cuối, hai trang quanh trang đang xem; chỗ hở là "…".
  function pager(pages) {
    if (pages < 2) return "";
    const cur = state.page, btn = (p, txt, cls) =>
      '<button type="button" data-page="' + p + '"' + (cls ? ' class="' + cls + '"' : "") +
      (p === cur && !cls ? ' aria-current="page"' : "") + ">" + txt + "</button>";
    let out = cur > 0 ? btn(cur - 1, "‹", "step") : '<button type="button" class="step" disabled>‹</button>';
    let last = -1;
    for (let p = 0; p < pages; p++) {
      if (p !== 0 && p !== pages - 1 && Math.abs(p - cur) > 1) continue;
      if (p - last > 1) out += '<span class="gap">…</span>';
      out += btn(p, p + 1);
      last = p;
    }
    out += cur < pages - 1 ? btn(cur + 1, "›", "step") : '<button type="button" class="step" disabled>›</button>';
    return '<nav class="pages" aria-label="Trang">' + out + "</nav>";
  }
  list.addEventListener("click", e => {
    const b = e.target.closest(".pages button[data-page]");
    if (!b) return;
    state.page = +b.dataset.page;
    if (!state.q) toHash();
    render();
    $("#controls").scrollIntoView({ block: "start" });
  });

  let timer = 0;

  // Bấm vào một khối ở bất kỳ đâu: nhảy tới thẻ của nó.
  function jump(id) {
    const ch = CHUNKS[id];
    if (!ch) return;
    state.note = ch.note.id; state.q = ""; state.level = "all"; q.value = "";
    let k = 0;
    for (const sec of ch.note.sections) for (const c of sec.chunks) { if (c === ch) break; k++; }
    state.page = Math.floor(k / PER);
    toHash();
    render();
    let el = document.getElementById("k" + id);
    if (!el) return;
    el = hydrate(el);
    el.scrollIntoView({ behavior: "smooth", block: "center" });
    el.classList.remove("flash"); void el.offsetWidth; el.classList.add("flash");
  }
  document.addEventListener("click", e => {
    const t = e.target.closest(".ck[data-id], .chip[data-id]");
    if (t && !t.closest(".card")) jump(+t.dataset.id);
    else if (t && t.closest(".card") && +t.dataset.id !== +t.closest(".card").id.slice(1)) jump(+t.dataset.id);
  });

  // ------------------------------------------------------------------ điều khiển
  $("#mode").addEventListener("click", e => {
    const b = e.target.closest("button[data-mode]");
    if (!b) return;
    state.mode = b.dataset.mode;
    try { localStorage.setItem("chunks.mode", state.mode); } catch (err) { /* không có storage */ }
    render();
  });
  $("#levels").addEventListener("click", e => {
    const b = e.target.closest("button[data-level]");
    if (!b) return;
    state.level = b.dataset.level;
    state.page = 0;
    render();
  });
  q.addEventListener("input", () => { clearTimeout(timer); timer = setTimeout(() => { state.q = q.value.trim(); state.page = 0; render(); }, 120); });
  document.addEventListener("keydown", e => {
    if (e.key === "/" && document.activeElement !== q) { e.preventDefault(); q.focus(); }
  });
  window.addEventListener("hashchange", () => { fromHash(); render(); });

  renderHub();
  render();

  // Cho trang kiểm tra: tỉ lệ thẻ dò ra được chính khối của nó.
  window.CHUNKS_DEBUG = { CHUNKS, analyze, sentence };
})();
