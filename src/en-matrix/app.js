/* Ma trận nói — luyện nói theo bộ Speaking Matrix.
   Dữ liệu: data.json (câu, khối, tiêu đề; dựng bằng tools/speaking-matrix.py) và vi.json
   (nghĩa tiếng Việt, khóa là câu tiếng Anh). Đọc lúc mở trang, nên cần mở qua http.

   data.json: [ { id, name, parts: [ { name: INPUT|OUTPUT, days: [ day ] } ] } ]
     day:  { id, no, kind: input|output|review|test, title, titleEn?, uses?, items?: [item], blocks?: [block],
             script?: { en } }
     block: { title, kind?: key|drill|apply, items: [item] }
     item: { en, title?, tag?, chunks?, words?, notes?: [[term, nghĩa]] }
   vi.json: { s: { en: { vi, c?: [nghĩa từng khối], cc?: { "khối|khối": [..] }, w?: [nghĩa từng từ] } } }
   Tiêu đề đã là tiếng Việt sẵn trong data.json (trống nếu chưa dịch). */
(function () {
  "use strict";

  const BOOK_INFO = {
    zero: { short: "Zero", desc: "Câu ba bốn chữ: tư duy theo trật tự tiếng Anh, từng từ rồi từng khối." },
    "30s": { short: "30 giây", desc: "Mẫu câu cơ bản, rồi ghép thành bài nói 30 giây." },
    "1m": { short: "1 phút", desc: "Cụm diễn đạt đời thường; nối lại thành bài nói 1 phút." },
    "2m": { short: "2 phút", desc: "Mỗi bài sáu mẩu chuyện ngắn; ráp thành bài nói 2 phút." },
    "3m": { short: "3 phút", desc: "Hai chủ đề một ngày; trả lời câu hỏi mở trong 3 phút." },
  };
  const BLOCK = { key: "Tóm tắt trọng tâm", drill: "Luyện tập tập trung", apply: "Nói ứng dụng" };
  const KIND = { input: "INPUT", output: "OUTPUT", review: "Ôn tập", test: "Kiểm tra" };

  const $ = s => document.querySelector(s);
  const esc = s => String(s == null ? "" : s).replace(/[&<>"]/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c]));
  const store = {
    get(k, d) { try { const v = localStorage.getItem("matrix:" + k); return v == null ? d : JSON.parse(v); } catch (e) { return d; } },
    set(k, v) { try { localStorage.setItem("matrix:" + k, JSON.stringify(v)); } catch (e) { /* bỏ qua */ } },
  };

  Promise.all([
    fetch("data.json").then(r => { if (!r.ok) throw new Error("data.json: HTTP " + r.status); return r.json(); }),
    fetch("vi.json").then(r => (r.ok ? r.json() : {})).catch(() => ({})),
  ])
    .then(([data, vi]) => main(data, vi))
    .catch(e => { $("#main").innerHTML = '<p class="empty">Không đọc được dữ liệu: ' + esc(e.message) + "</p>"; });

  function main(BOOKS, VI) {
    const S = VI.s || {};
    const tr = s => s || "";
    const dayTitle = d => d.title || d.titleEn || "Day " + d.no;

    // ------------------------------------------------------------ chỉ mục
    const DAYS = [];   // mọi bài, theo thứ tự
    let total = 0;
    BOOKS.forEach(b => {
      b.days = [];
      b.parts.forEach(p => p.days.forEach(d => {
        d.book = b; d.part = p.name; d.key = b.id + "/" + d.id;
        d.all = (d.items || []).concat(...(d.blocks || []).map(bl => bl.items));
        d.all.forEach(it => { it.v = S[it.en] || {}; });
        total += d.all.length;
        b.days.push(d); DAYS.push(d);
      }));
    });
    $("#total").textContent = total.toLocaleString("vi-VN");


    // ------------------------------------------------------------ năm cuốn, danh sách bài
    function renderBooks(cur) {
      $("#books").innerHTML = BOOKS.map(b => {
        const info = BOOK_INFO[b.id] || { short: b.id, desc: "" };
        const n = b.days.reduce((s, d) => s + d.all.length, 0);
        return `<button type="button" class="b-${b.id}${b.id === cur ? " on" : ""}" data-book="${b.id}">
          <span class="n">${b.days.length} bài · ${n} câu</span>
          <h2>${esc(info.short)}</h2><p>${esc(info.desc)}</p>
        </button>`;
      }).join("");
    }
    function renderDays(book, cur) {
      const el = $("#days");
      el.className = "days b-" + book.id;
      el.innerHTML = book.parts.map(p => `<h3>${p.name === "INPUT" && book.id === "zero" ? "Thực hành" : p.name}</h3>` +
        p.days.map(d => `<a href="#${d.key}" class="${d.kind}"${d.key === cur ? ' aria-current="page"' : ""}>
          <span class="no">${esc(d.no)}</span>
          <span class="t">${esc(dayTitle(d))}</span></a>`).join("")).join("");
      const on = el.querySelector('[aria-current="page"]');
      if (on) {
        const top = on.offsetTop - el.clientHeight / 2;
        el.scrollTop = Math.max(0, top);
      }
    }

    // ------------------------------------------------------------ một câu
    function chunkRow(ens, vis, cls) {
      return `<div class="chunks ${cls || ""}">` + ens.map((e, i) => {
        const g = vis && vis[i] ? vis[i] : "";
        return `<span class="ck"><span class="e">${esc(e)}</span>` +
          (g ? `<span class="g">${esc(g)}</span>` : "") +
          `</span>`;
      }).join("") + "</div>";
    }
    function itemHTML(it, n) {
      const v = it.v;
      let h = `<div class="item" id="it-${n}"><div class="top"><span class="n">${n}</span>`;
      h += `<div style="flex:1;min-width:0">`;
      if (it.title) h += `<h4>${esc(tr(it.title))}</h4>`;
      if (it.tag) h += `<span class="tag">${esc(it.tag)}</span> `;
      if (it.words) h += chunkRow(it.words, v.w, "words");
      // Cùng một câu có thể được ngắt khối khác nhau ở hai chỗ: cc giữ nghĩa theo từng cách ngắt.
      if (it.chunks && it.chunks.length > 1) h += chunkRow(it.chunks, (v.cc && v.cc[it.chunks.join("|")]) || v.c);
      else h += `<p class="en-s">${esc(it.en)}</p>`;
      h += `<p class="vi-s">${esc(v.vi || "")}</p>`;
      if (it.notes) h += `<div class="notes">${it.notes.map(([t, k]) => `<span><b>${esc(t)}</b> ${esc(k)}</span>`).join("")}</div>`;
      h += `</div></div></div>`;
      return h;
    }

    // ------------------------------------------------------------ một bài
    function renderDay(d) {
      const b = d.book, info = BOOK_INFO[b.id] || {};
      document.body.className = "b-" + b.id;
      let n = 0;
      let h = `<h2 class="dayh"><span class="no">${esc(info.short)} · ${d.kind === "test" ? "" : "Day " + esc(d.no) + " · "}${KIND[d.kind] || d.part}</span>${esc(dayTitle(d))}</h2>`;
      if (d.titleEn) h += `<p class="sub"><span class="en" lang="en">${esc(d.titleEn)}</span></p>`;
      if (d.uses) h += `<p class="sub">Dùng lại mẫu câu của INPUT: ${esc(d.uses)}</p>`;
      h += `<div class="bar2"><span class="sub">${d.all.length} câu</span></div>`;

      if (d.items) h += `<div class="items">${d.items.map(it => itemHTML(it, ++n)).join("")}</div>`;
      (d.blocks || []).forEach((bl, bi) => {
        h += `<section class="blk"><h3 class="blkh"><span class="i">${String(bi + 1).padStart(2, "0")}</span>${esc(tr(bl.title) || BLOCK[bl.kind] || "")}</h3>`;
        if (bl.kind === "key") {
          h += `<div class="pics">${bl.items.map(it => {
            n++;
            return `<div class="pic" id="it-${n}">` +
              `<span class="e">${esc(it.en)}</span><span class="g">${esc(it.v.vi || "")}</span>` +
              `</div>`;
          }).join("")}</div>`;
        } else {
          h += `<div class="items">${bl.items.map(it => itemHTML(it, ++n)).join("")}</div>`;
        }
        h += `</section>`;
      });

      if (d.script) {
        const vi = (d.items || []).map(it => it.v.vi || "").join(" ");
        h += `<section class="script"><h3>Cả bài</h3>
          <p class="vi-p">${esc(vi)}</p>
          <p class="en-p" lang="en">${esc(d.script.en)}</p></section>`;
      }

      const i = DAYS.indexOf(d), prev = DAYS[i - 1], next = DAYS[i + 1];
      h += `<nav class="nav">` +
        (prev ? `<a href="#${prev.key}"><b>← ${esc((BOOK_INFO[prev.book.id] || {}).short)} · ${esc(prev.no)}</b>${esc(dayTitle(prev))}</a>` : "") +
        (next ? `<a class="next" href="#${next.key}"><b>${esc((BOOK_INFO[next.book.id] || {}).short)} · ${esc(next.no)} →</b>${esc(dayTitle(next))}</a>` : "") +
        `</nav>`;

      const main = $("#main");
      main.innerHTML = h;

    }

    // ------------------------------------------------------------ điều khiển
    $("#books").addEventListener("click", e => {
      const b = e.target.closest("button[data-book]"); if (!b) return;
      const book = BOOKS.find(x => x.id === b.dataset.book);
      const last = store.get("last:" + book.id, null);
      location.hash = last && DAYS.some(d => d.key === last) ? last : book.days[0].key;
    });

    // ------------------------------------------------------------ định tuyến: #book/day[/câu]
    function route() {
      const [bid, did, n] = decodeURIComponent(location.hash.slice(1)).split("/");
      let d = DAYS.find(x => x.key === bid + "/" + did);
      if (!d) {
        const book = BOOKS.find(x => x.id === bid) || BOOKS.find(x => x.id === "30s") || BOOKS[0];
        d = book.days[0];
      }
      store.set("last:" + d.book.id, d.key);
      renderBooks(d.book.id); renderDays(d.book, d.key); renderDay(d);
      document.title = dayTitle(d) + " · Ma trận nói tiếng Anh";
      if (n) {
        const el = document.getElementById("it-" + n);
        if (el) { el.scrollIntoView({ block: "center" }); el.classList.add("flash"); setTimeout(() => el.classList.remove("flash"), 1600); }
      } else if (window.scrollY > $(".layout").offsetTop) {
        window.scrollTo(0, $(".layout").offsetTop - 16);
      }
    }
    window.addEventListener("hashchange", route);
    route();
  }
})();
