"""Dựng src/en-matrix/data.json từ năm cuốn epub Speaking Matrix (Zero, 30s, 1m, 2m, 3m).

    python tools/speaking-matrix.py <thư mục epub> [--ko] [--mp3]

Chỉ lấy phần luyện nói (câu tiếng Anh, cách ngắt khối); bỏ phần lý thuyết, lời dẫn, bài giải thích. Sách viết cho
người Hàn nên chú thích gốc là tiếng Hàn; data.json không giữ chữ Hàn nào:
  - nghĩa câu và nghĩa từng khối nằm ở src/en-matrix/vi.json, khóa là câu tiếng Anh;
  - tên bài, tên mục, ghi chú từ vựng được thay bằng tiếng Việt lúc dựng, tra từ <thư mục epub>/vi-titles.json
    ({ "tiêu đề tiếng Hàn": "tiếng Việt", "term | nghĩa tiếng Hàn": "nghĩa tiếng Việt" }); chưa dịch thì để trống.
--mp3: tải lại danh sách MP3 của từng bài từ trang của NXB Gilbut (link QR trong sách), lưu ở
       <thư mục epub>/mp3.json; không có cờ này thì dùng lại mp3.json đã lưu. Trang phát MP3 thẳng từ máy chủ
       Gilbut, không chép file về. Bỏ bài giảng của tác giả và MP3 STEP 1 (có tiếng Hàn).
--ko: ghi thêm src/en-matrix/ko.json (bản gốc còn chữ Hàn, để dịch phần mới; không đăng).
"""
import html, json, re, sys, zipfile
from html.parser import HTMLParser
from pathlib import Path

OUT = Path(__file__).resolve().parent.parent / 'src' / 'en-matrix'
BOOKS = [  # (id, chuỗi nhận diện trong tên file, tên hiển thị)
    ('zero', '제로', 'Speaking Matrix Zero'),
    ('30s', '30초', 'Speaking Matrix 30 giây'),
    ('1m', '1분', 'Speaking Matrix 1 phút'),
    ('2m', '2분', 'Speaking Matrix 2 phút'),
    ('3m', '3분', 'Speaking Matrix 3 phút'),
]
HANGUL = re.compile(r'[가-힣ㄱ-ㆎ]')
BLOCK = ('p', 'h1', 'h2', 'h3', 'li')


class El:
    """Một đoạn văn: class, chữ thô (giữ dấu * ngắt khối), ảnh bên trong, các span có class."""
    def __init__(self, tag, cls):
        self.tag, self.cls, self.raw, self.imgs, self.spans = tag, cls or '', '', [], []

    @property
    def text(self):
        t = self.raw.replace(' ', ' ')
        return re.sub(r'\s+', ' ', t).strip()

    def has(self, *cs):
        return all(c in self.cls.split() for c in cs)

    def __repr__(self):
        return f'<{self.tag}.{self.cls} {self.text!r}>'


class Parser(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.els, self.cur, self.stack = [], None, []

    def handle_starttag(self, t, a):
        a = dict(a)
        if t in BLOCK:
            self.cur = El(t, a.get('class'))
        elif self.cur is not None:
            if t == 'img':
                self.cur.imgs.append(a.get('src', '').rsplit('/', 1)[-1])
            elif t == 'br':
                self.cur.raw += '\n'
            elif t == 'span':
                self.cur.spans.append([a.get('class') or '', ''])
        if t not in ('img', 'br'):
            self.stack.append(t)

    def handle_endtag(self, t):
        if self.stack:
            self.stack.pop()
        if t in BLOCK and self.cur is not None:
            if self.cur.text or self.cur.imgs:
                self.els.append(self.cur)
            self.cur = None

    def handle_data(self, d):
        if self.cur is not None:
            self.cur.raw += d
            # gom chữ vào các span đang mở (đủ dùng cho span không lồng nhau)
            if self.cur.spans and 'span' in self.stack:
                self.cur.spans[-1][1] += d


def parse(zf, name):
    p = Parser()
    p.feed(zf.read(name).decode('utf-8'))
    return p.els


def ko(s):
    return bool(HANGUL.search(s))


def clean(s):
    s = s.replace(' ', ' ').replace('\n', ' ')
    return re.sub(r'\s+', ' ', s).strip()


def chunks_of(s):
    """'I jog * in the park * early.' -> ['I jog', 'in the park', 'early.']"""
    return [c for c in (clean(x) for x in s.split('*')) if c]


def unstar(s):
    return clean(s.replace('*', ' '))


def is_step(e):
    return bool(re.match(r'Step \d', e.text))


def step_no(e):
    return int(re.match(r'Step (\d+)', e.text).group(1))


def toc(zf):
    """[(tiêu đề, file)] theo thứ tự trong toc.ncx."""
    x = zf.read('OEBPS/toc.ncx').decode('utf-8')
    out = []
    for m in re.finditer(r'<navLabel>\s*<text>(.*?)</text>\s*</navLabel>\s*<content src="([^"#]+)', x, re.S):
        out.append((clean(html.unescape(m.group(1))), m.group(2).rsplit('/', 1)[-1]))
    return out


def day_title(els):
    """Tiêu đề h2: 'DAY 01 / 조깅 Jogging' -> ('01', '조깅', 'Jogging')."""
    h = next(e for e in els if e.tag == 'h2')
    lines = [clean(x) for x in h.raw.split('\n') if clean(x)]
    head = lines[0]
    rest = ' '.join(lines[1:])
    no = re.sub(r'\D', '', head) or head
    m = re.match(r'^(.*?[가-힣)❶❷➊➋!?][^A-Za-z]*?)\s+([A-Z].*)$', rest)
    if m and not ko(m.group(2)):
        return no, clean(m.group(1)), clean(m.group(2))
    return no, rest, ''


# ---------- OUTPUT (chung cho 30s, 1m, 2m, 3m) ----------

def parse_output(els):
    """Mỗi câu: Step 1 tiếng Hàn, Step 2 tiếng Anh có dấu *; cuối bài có kịch bản liền mạch."""
    """3m: mỗi 'câu' là cả một đoạn, có tiêu đề ▶ riêng; không có kịch bản gộp."""
    items, script_en, script_ko, step, cur, title = [], '', '', 0, None, None
    for e in els:
        if is_step(e):
            step = step_no(e)
            continue
        t = e.text
        if step == 1 and t.startswith('▶'):
            title = clean(t[1:])
        elif step == 1 and ko(t) and 'dl' in e.cls.split():
            cur = {'ko': t}
            if title:
                cur['title'], title = title, None
            items.append(cur)
        elif step == 2 and cur is not None and 'en' not in cur and t and not ko(t):
            cur['chunks'] = chunks_of(t)
            cur['en'] = unstar(t)
        elif step == 3 and not ko(t) and len(t) > 40:
            script_en = (script_en + ' ' + clean(t)).strip()
        elif step == 4 and ko(t) and len(t) > 40:
            script_ko = (script_ko + ' ' + clean(t)).strip()
    items = [i for i in items if 'en' in i]
    day = {'items': items}
    if script_en and not any('title' in i for i in items):
        day['script'] = {'en': script_en, 'ko': script_ko}
    return day


# ---------- 1m INPUT ----------

def steps_groups(els, start):
    """Chia các đoạn thành từng mục (bắt đầu bằng đoạn khớp start), trong mục chia theo Step."""
    groups = []
    for e in els:
        if start(e):
            groups.append({'label': e.text, 'steps': {}})
            cur_step = None
            continue
        if not groups:
            continue
        if is_step(e):
            cur_step = step_no(e)
            groups[-1]['steps'][cur_step] = []
            continue
        if cur_step is not None and e.text:
            groups[-1]['steps'][cur_step].append(e)
    return groups


def item_from_steps(steps):
    en_lists = [[e.text for e in v] for k, v in sorted(steps.items()) if v and not any(ko(e.text) for e in v)]
    ko_lists = [[e.text for e in v] for k, v in sorted(steps.items()) if v and all(ko(e.text) for e in v)]
    if not en_lists or not ko_lists:
        return None
    item = {'en': clean(' '.join(en_lists[-1])), 'ko': clean(' '.join(ko_lists[-1]))}
    if len(en_lists[0]) > 1 and len(en_lists[0]) == len(ko_lists[0]):
        item['chunks'] = en_lists[0]
        item['koChunks'] = ko_lists[0]
    return item


def parse_1m_input(els):
    start = lambda e: e.has('txt1', 'dm', 'gray') and re.match(r'^(\d+|Day .*)$', e.text)
    items = []
    for g in steps_groups(els, start):
        it = item_from_steps(g['steps'])
        if it:
            if g['label'].startswith('Day'):
                it['tag'] = g['label']
            items.append(it)
    return {'items': items}


# ---------- 2m / 3m INPUT: khối Anh-Hàn xen kẽ ----------

TAG = re.compile(r"^[A-Z0-9][A-Z0-9.,%:\-]*$")  # số, chữ viết tắt: TV, ATM, 3D, 30…


def interleaved(e):
    """'I liked 나는 좋아했습니다 * playing games 게임 하는 것을' -> [('I liked', '나는 좋아했습니다'), …].

    Chia theo lượt chữ Anh / chữ Hàn chứ không chỉ theo dấu *, vì sách đôi chỗ quên dấu *. Chữ Hàn hay mở đầu
    bằng số hoặc chữ viết tắt lặp lại từ tiếng Anh ('for 5 days 5일 동안', 'an ATM ATM에서'): trả nó về phía Hàn.
    """
    pairs = []
    for seg in chunks_of(e.text):
        toks = seg.split(' ')
        runs = []  # [[lang, [tok…]]]
        for j, t in enumerate(toks):
            nxt = toks[j + 1] if j + 1 < len(toks) else ''
            if ko(t):
                lang = 'ko'
            elif runs and runs[-1][0] == 'ko' and TAG.match(t.strip('()')) and ko(nxt):
                lang = 'ko'
            else:
                lang = 'en'
            if runs and runs[-1][0] == lang:
                runs[-1][1].append(t)
            else:
                runs.append([lang, [t]])
        # 'at the ABC bank ABC' + '은행에서': chữ cuối của lượt Anh thực ra thuộc lượt Hàn sau nó
        for k in range(len(runs) - 1):
            lang, ws = runs[k]
            if lang == 'en' and len(ws) > 1 and TAG.match(ws[-1].strip('()')) and runs[k + 1][0] == 'ko':
                prev = ws[-2]
                if ws[-1] in ws[:-1] or prev[-1] in '.,' or ws[-1].strip('()') in prev:
                    runs[k + 1][1].insert(0, ws.pop())
        for lang, ws in runs:
            txt = clean(' '.join(ws))
            if lang == 'en':
                pairs.append([txt, ''])
            elif pairs:
                pairs[-1][1] = clean(pairs[-1][1] + ' ' + txt)
    return [tuple(p) for p in pairs if p[0]]


def parse_episode_input(els, book):
    """2m: mỗi tập là một đoạn; 3m: mỗi tập gồm nhiều câu đánh số."""
    blocks, cur, item = [], None, None
    for e in els:
        t = e.text
        if book == '2m' and e.has('txt1', 'dm') and e.spans and 'bg_gray2' in e.spans[0][0]:
            cur = {'title': clean(t[len(e.spans[0][1]):]), 'items': []}
            blocks.append(cur)
            item = None
        elif book == '3m' and e.has('txt1', 'dm') and '에피소드' in t:
            title = clean(re.sub(r'^에피소드\s*\d+\s*☆?', '', t))
            cur = {'title': title, 'items': []}
            blocks.append(cur)
            item = None
        elif cur is None:
            continue
        elif e.has('txt1', 'dl') and not e.has('gray') and ko(t) and '*' in t:
            pairs = interleaved(e)
            item = {'chunks': [p[0] for p in pairs], 'koChunks': [p[1] for p in pairs]}
            cur['items'].append(item)
        elif item is not None and e.has('txt1', 'dl', 'gray') and not ko(t):
            item['en'] = clean(t)
        elif item is not None and e.has('txt', 'dl') and ko(t) and 'ko' not in item:
            item['ko'] = clean(t)
        elif item is not None and e.has('txts', 'dl', 'tright') and not e.has('gray'):
            notes = []
            for part in t.split('|'):
                sp = [s for s in e.spans if s[1] and clean(s[1]) in part]
                if sp:
                    term = clean(sp[0][1])
                    notes.append([term, clean(part.replace(sp[0][1], '', 1))])
            if notes:
                item['notes'] = notes
    for b in blocks:
        b['items'] = [i for i in b['items'] if 'en' in i and 'ko' in i]
    return {'blocks': [b for b in blocks if b['items']]}


# ---------- 30s INPUT / TEST ----------

def parse_30s_input(els):
    blocks, mode, cur, item, step = [], None, None, None, 0
    for e in els:
        t = e.text
        if 'icon_1min.png' in e.imgs:
            mode, item, cur = 'key', None, {'title': '핵심 정리', 'kind': 'key', 'items': []}
            blocks.append(cur)
            continue
        if 'icon_3min.png' in e.imgs:
            mode, item, cur = 'drill', None, {'title': '집중 훈련', 'kind': 'drill', 'items': []}
            blocks.append(cur)
            continue
        if 'icon_2min.png' in e.imgs:
            mode, item, cur = 'apply', None, {'title': '응용 말하기', 'kind': 'apply', 'items': []}
            blocks.append(cur)
            continue
        if mode is None:
            continue
        if mode == 'key':
            if e.has('txtex', 'dl') and ko(t):
                item = {'ko': clean(re.sub(r'^\d+\s*', '', t)).replace('*', '').replace('  ', ' ')}
                cur['items'].append(item)
            elif item is not None and e.has('txt1', 'dl') and not ko(t):
                item['en'] = (item.get('en', '') + ' ' + t).strip()
        elif mode == 'drill':
            if e.has('txtex', 'dm', 'gray') and re.match(r'^\d+$', t):
                item = {'koChunks': [], 'chunks': []}
                cur['items'].append(item)
            elif is_step(e):
                step = step_no(e)
            elif item is None:
                continue
            elif step == 2 and ko(t):
                item['koChunks'].append(clean(re.sub(r'\((주체|행동|나머지|[^)]*)\)$', '', t)))
            elif step == 3 and not ko(t):
                item['chunks'].append(t)
            elif step == 4 and ko(t):
                item['ko'] = t
                item['en'] = ' '.join(item['chunks'])
        elif mode == 'apply':
            if e.has('txtex', 'dm', 'gray') and re.match(r'^\d+$', t):
                item = {}
                cur['items'].append(item)
            elif is_step(e):
                step = step_no(e)
            elif item is None:
                continue
            elif step == 1 and ko(t):
                item['ko'] = t
            elif step == 2 and not ko(t) and t:
                item['chunks'] = chunks_of(t)
                item['en'] = unstar(t)
    for b in blocks:
        for i in b['items']:
            if len(i.get('chunks', [])) != len(i.get('koChunks', i.get('chunks', []))):
                i.pop('koChunks', None)
        b['items'] = [i for i in b['items'] if i.get('en') and i.get('ko')]
    return {'blocks': [b for b in blocks if b['items']]}


def parse_30s_test(els):
    items, kos = [], []
    for e in els:
        t = e.text
        if e.has('txt', 'dl') and ko(t) and len(t) < 60:
            kos.append(t)
        elif 'icon_speak.png' in e.imgs and not ko(t):
            ens = [x for x in re.split(r'\s*\n\s*|\s+/\s+', e.raw) if clean(x)]
            for k, en in zip(kos, ens):
                items.append({'ko': k, 'en': unstar(en), 'chunks': chunks_of(en)})
            kos = []
    return {'items': items}


# ---------- Zero INPUT ----------

def parse_zero(els):
    items, item, step = [], None, 0
    for e in els:
        t = e.text
        if e.has('txt1', 'dm') and t == 'OUTPUT':
            break
        if e.has('txt', 'dm') and ko(t) and not e.imgs:
            item = {'ko': t, 'words': [], 'koWords': [], 'chunks': [], 'koChunks': []}
            items.append(item)
            step = 0
        elif item is None:
            continue
        elif is_step(e):
            step = step_no(e)
        elif 'icon_speak.png' in e.imgs:
            continue
        elif step == 1:
            (item['koWords'] if ko(t) else item['words']).append(t)
        elif step == 2:
            (item['koChunks'] if ko(t) else item['chunks']).append(t)
        elif step == 3 and not ko(t) and t and 'en' not in item and 'mp3' not in t:
            item['en'] = t
    out = []
    for i in items:
        if 'en' not in i:
            continue
        for a, b in (('words', 'koWords'), ('chunks', 'koChunks')):
            if len(i[a]) != len(i[b]) or len(i[a]) < 2:
                i.pop(a); i.pop(b)
        out.append(i)
    return {'items': out}


# ---------- dựng sách ----------

def build_book(zf, bid):
    entries = toc(zf)
    parts, part = [], None
    for title, f in entries:
        if re.search(r'(INPUT|OUTPUT)$', title) or title == '{ 실천편 }':
            part = {'name': 'OUTPUT' if 'OUTPUT' in title else 'INPUT', 'days': []}
            parts.append(part)
            continue
        if part is None or not re.match(r'^(DAY|TEST)', title):
            continue
        els = parse(zf, 'OEBPS/Text/' + f)
        no, t_ko, t_en = day_title(els)
        kind = part['name'].lower()
        if part['name'] == 'OUTPUT':
            body = parse_output(els)
        elif bid == 'zero':
            body = parse_zero(els)
        elif bid == '1m':
            body = parse_1m_input(els)
        elif bid in ('2m', '3m'):
            body = parse_episode_input(els, bid)
        elif title.startswith('TEST'):
            body, kind, no = parse_30s_test(els), 'test', 'TEST'
        else:
            body = parse_30s_input(els)
        if '중간 점검' in title:
            kind = 'review'
        day = {'id': f'{part["name"][0].lower()}{len(part["days"]) + 1:02d}', 'no': no, 'kind': kind,
               'title': t_ko}
        if t_en:
            day['titleEn'] = t_en
        m = next((e.text for e in els[:8] if re.match(r'^:?\s*:?\s*INPUT\s*:|^Day \d+ \+', e.text)), None)
        if m:
            day['uses'] = clean(re.sub(r'^.*?INPUT\s*:', '', m))
        link = re.search(r'href="(https?://[^"]+)"', zf.read('OEBPS/Text/' + f).decode('utf-8'))
        if link:
            day['mp3page'] = link.group(1)
        day.update(body)
        n = len(day.get('items', [])) + sum(len(b['items']) for b in day.get('blocks', []))
        if not n:
            print(f'  ! {bid} {title}: không lấy được câu nào', file=sys.stderr)
            continue
        part['days'].append(day)
    return [p for p in parts if p['days']]


def main():
    if len(sys.argv) < 2:
        sys.exit(__doc__)
    src = Path(sys.argv[1])
    books = []
    OUT.mkdir(parents=True, exist_ok=True)
    for bid, key, name in BOOKS:
        f = next(p for p in src.glob('*.epub') if key in p.name)
        zf = zipfile.ZipFile(f)
        parts = build_book(zf, bid)
        books.append({'id': bid, 'name': name, 'parts': parts})
        n = sum(len(d.get('items', [])) + sum(len(b['items']) for b in d.get('blocks', []))
                for p in parts for d in p['days'])
        print(f'{bid}: {sum(len(p["days"]) for p in parts)} bài, {n} câu')
    if '--ko' in sys.argv:
        with open(OUT / 'ko.json', 'w', encoding='utf-8') as fp:
            json.dump(books, fp, ensure_ascii=False, separators=(',', ':'))
    add_audio(books, src / 'mp3.json', '--mp3' in sys.argv)
    tf = src / 'vi-titles.json'
    titles = json.loads(tf.read_text(encoding='utf-8')) if tf.exists() else {}
    strip_korean(books, titles)
    with open(OUT / 'data.json', 'w', encoding='utf-8') as fp:
        json.dump(books, fp, ensure_ascii=False, separators=(',', ':'))


# Chỗ giữ vị trí tiếng Hàn trong mẫu ghi chú: 'be used to + (동)명사'
TERM_KO = {'(동)명사': '(V-ing / danh từ)', '(병)증': '(bệnh, chứng)', '비교급': 'so sánh hơn',
           '수치': 'con số', '기간': 'khoảng thời gian', '시점': 'mốc thời gian'}


def term_vi(term):
    for k, v in TERM_KO.items():
        term = term.replace(k, v)
    return term


# Nhãn nút MP3 trên trang của NXB -> tiếng Việt; nhãn trống hay lạ thì đánh số 'Phần n'.
MP3_LABEL = {'1분 핵심 정리': 'Tóm tắt trọng tâm', '3분 집중 훈련': 'Luyện tập tập trung',
             '2분 응용 말하기': 'Nói ứng dụng', 'INPUT': 'Luyện tập', 'OUTPUT': 'Nói',
             'STEP 3': 'Cả đoạn', '훈련용 MP3': 'Cả bài'}


def mp3_list(url):
    """Trang MP3 của một bài -> [[nhãn, url tuyệt đối]], bỏ bài giảng (L01.mp3, InL01.mp3, OutL01.mp3)."""
    import urllib.request
    from urllib.parse import urljoin
    with urllib.request.urlopen(url, timeout=30) as r:
        page, final = r.read().decode('utf-8', 'replace'), r.geturl()
    out = []
    for href, label in re.findall(r'<a href="([^"]+\.mp3)"[^>]*>(.*?)</a>', page, re.S):
        if re.match(r'^(In|Out)?L\d', href.rsplit('/', 1)[-1]):
            continue
        out.append([clean(html.unescape(re.sub(r'<[^>]+>', '', label))), urljoin(final, href)])
    return out


def add_audio(books, cache, refresh):
    audio = json.loads(cache.read_text(encoding='utf-8')) if cache.exists() else {}
    for b in books:
        for p in b['parts']:
            for d in p['days']:
                url = d.pop('mp3page', None)
                if not url:
                    continue
                if refresh or url not in audio:
                    try:
                        audio[url] = mp3_list(url)
                    except OSError as e:
                        print(f'  ! không tải được {url}: {e}', file=sys.stderr)
                        continue
                # STEP 1 (OUTPUT) là "nghe khi nhìn câu tiếng Hàn": có giọng tiếng Hàn, bỏ.
                files = [f for f in audio[url] if f[0] != 'STEP 1']
                d['audio'] = [[MP3_LABEL.get(l) or (l if l and not HANGUL.search(l) else ''), u] for l, u in files]
                for i, a in enumerate(d['audio']):
                    # 'Tập n': 에피소드 n (3 phút), 01-n (2 phút)
                    m = re.match(r'에피소드\s*(\d+)$|\d+-(\d+)$', files[i][0])
                    if m:
                        a[0] = f'Tập {m.group(1) or m.group(2)}'
                    a[0] = a[0] or (f'Tập {m.group(1)}' if m else f'Phần {i + 1}' if len(files) > 1 else 'Cả bài')
    cache.write_text(json.dumps(audio, ensure_ascii=False, indent=1), encoding='utf-8')


def strip_korean(books, vi):
    """Thay tiêu đề, ghi chú bằng tiếng Việt; bỏ mọi trường tiếng Hàn."""
    missing = set()

    def t(ko, fallback=''):
        if not ko or not HANGUL.search(ko):
            return ko
        if ko not in vi:
            missing.add(ko)
        return vi.get(ko, fallback)

    for b in books:
        for p in b['parts']:
            for d in p['days']:
                d['title'] = t(d['title'], d.get('titleEn', ''))
                if 'script' in d:
                    d['script'].pop('ko', None)
                for bl in d.get('blocks', []):
                    bl['title'] = t(bl['title'])
                for it in d.get('items', []) + [i for bl in d.get('blocks', []) for i in bl['items']]:
                    for k in ('ko', 'koChunks', 'koWords'):
                        it.pop(k, None)
                    if 'title' in it:
                        it['title'] = t(it['title'])
                    if 'notes' in it:
                        it['notes'] = [[term_vi(term), t(term + ' | ' + ko)] for term, ko in it['notes']]
    left = HANGUL.findall(json.dumps(books, ensure_ascii=False))
    if missing:
        print(f'  ! {len(missing)} tiêu đề/ghi chú chưa có trong vi-titles.json (để trống)', file=sys.stderr)
    if left:
        print(f'  ! còn {len(left)} chữ Hàn trong data.json', file=sys.stderr)


if __name__ == '__main__':
    main()
