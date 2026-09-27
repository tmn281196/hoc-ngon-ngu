<?php

declare(strict_types=1);

/**
 * Tiếng Trung dùng chung cho www/zh-chunks và www/zh-svo.
 *
 * Đọc giáo trình HSK1 trong lib/hanyu-data (các note chép từ vault Obsidian
 * #study/#hanyu, giờ nằm hẳn trong repo) cùng phần HSK2–4 soạn thêm trong
 * lib/hanyu-plus (ngữ pháp HSK2–6, động từ, từ điển bổ sung), rồi:
 *   - gom từ điển: 500 từ thông dụng, 50 động từ, từ mới 15 bài, cộng bộ từ
 *     chức năng dựng sẵn ở cuối file (的 了 吗 很 在…) kèm từ loại;
 *   - gom câu: luyện đặt câu (18 mục ngữ pháp), đặt câu với 50 động từ, hội
 *     thoại 15 bài, luyện nói, tự giới thiệu;
 *   - tách câu thành từ: chữ **đậm** trong câu là từ vựng đã được đánh dấu
 *     (trong một cụm đậm, dấu cách tách các từ), phần còn lại tách bằng khớp
 *     dài nhất theo từ điển;
 *   - gắn pinyin cho từng chữ bằng cách chia dòng pinyin của chính câu đó
 *     thành âm tiết, nên giữ được biến điệu (不→bú, 一→yí) như giáo trình ghi.
 *
 * Nằm ngoài www: tắt một site trong webservice.ini không làm hỏng site kia.
 * Kết quả được lưu đệm theo thời điểm sửa của các file nguồn.
 */

const HY_VAULT = __DIR__ . '/hanyu-data';
const HY_PLUS  = __DIR__ . '/hanyu-plus';

const HY_PRACTICE = 'HSK1 - Luyện đặt câu (500 từ)';
const HY_COMMON   = 'Từ vựng thường gặp (500 từ)';
const HY_VERBS    = 'Top 50 động từ cơ bản';
const HY_VERB_EX  = 'Đặt câu với 50 động từ';
const HY_SPEAK    = 'HSK1 - Luyện nói - 5 đề';
const HY_SELF     = 'Giới thiệu bản thân - 陈明日';

// ------------------------------------------------------------------ nạp + đệm

/** @return array{data: ?array, error: ?string} */
function hy_load(string $cacheFile): array
{
    $vault = realpath(HY_VAULT);
    $sig   = null;
    if ($vault !== false) {
        $parts = [filemtime(__FILE__)];
        foreach (array_merge(glob($vault . '/*.md') ?: [], glob(HY_PLUS . '/*') ?: []) as $f) {
            $parts[] = basename($f) . ':' . filemtime($f) . ':' . filesize($f);
        }
        $sig = md5(implode('|', $parts));
    }

    $cached = is_file($cacheFile) ? json_decode((string) file_get_contents($cacheFile), true) : null;
    if (is_array($cached) && ($sig === null || ($cached['sig'] ?? '') === $sig)) {
        return ['data' => $cached['data'], 'error' => null];
    }
    if ($vault === false) {
        return ['data' => null, 'error' => 'Không thấy dữ liệu tiếng Trung ở ' . HY_VAULT];
    }
    try {
        $data = hy_build($vault);
    } catch (RuntimeException $e) {
        return is_array($cached) ? ['data' => $cached['data'], 'error' => null] : ['data' => null, 'error' => $e->getMessage()];
    }
    $json = json_encode(['sig' => $sig, 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json !== false && @file_put_contents($cacheFile . '.tmp', $json) !== false) {
        @rename($cacheFile . '.tmp', $cacheFile);
    }
    return ['data' => $data, 'error' => null];
}

// ------------------------------------------------------------------ đọc note

function hy_note(string $vault, string $name): string
{
    $path = $vault . DIRECTORY_SEPARATOR . $name . '.md';
    if (!is_file($path)) {
        throw new RuntimeException("Thiếu: $name");
    }
    $text = str_replace("\r\n", "\n", (string) file_get_contents($path));
    return (string) preg_replace('/\A---\n.*?\n---\n/s', '', $text);
}

/** Tiêu đề markdown thành chữ trơn: bỏ #, **, *, emoji đầu dòng. */
function hy_heading(string $line): string
{
    $t = (string) preg_replace('/^#+\s*/', '', $line);
    $t = str_replace(['**', '*', '`'], '', $t);
    $t = (string) preg_replace('/^[^\p{L}\p{N}]+/u', '', $t);
    return trim($t);
}

/**
 * Mọi bảng markdown trong note, kèm tiêu đề cấp 1-3 đang bao nó.
 * @return list<array{h: array{0:string,1:string,2:string}, head: list<string>, rows: list<list<string>>}>
 */
function hy_tables(string $text): array
{
    $lines  = explode("\n", $text);
    $h      = ['', '', ''];
    $tables = [];
    $n      = count($lines);
    for ($i = 0; $i < $n; $i++) {
        $line = rtrim($lines[$i]);
        if (preg_match('/^(#{1,3})\s/', $line, $m)) {
            $lvl = strlen($m[1]) - 1;
            $h[$lvl] = hy_heading($line);
            for ($k = $lvl + 1; $k < 3; $k++) {
                $h[$k] = '';
            }
            continue;
        }
        if ($line === '' || $line[0] !== '|' || $i + 1 >= $n || !preg_match('/^\|\s*:?-{2,}/', trim($lines[$i + 1]))) {
            continue;
        }
        $table = ['h' => $h, 'head' => hy_cells($line), 'rows' => []];
        for ($i += 2; $i < $n && str_starts_with(trim($lines[$i]), '|'); $i++) {
            $table['rows'][] = hy_cells(rtrim($lines[$i]));
        }
        $i--;
        $tables[] = $table;
    }
    return $tables;
}

function hy_cells(string $line): array
{
    $line = trim($line);
    $line = substr($line, 1, str_ends_with($line, '|') ? -1 : null);
    return array_map('trim', explode('|', $line));
}

function hy_plain(string $s): string
{
    $s = (string) preg_replace('/\[\[(?:[^\]|]*\|)?([^\]]+)\]\]/u', '$1', $s);
    $s = str_replace(['**', '`', '<br>'], ['', '', ' '], $s);
    $s = (string) preg_replace('/(?<![\w*])\*(?!\s)(.+?)(?<!\s)\*(?![\w*])/u', '$1', $s);
    return trim($s);
}

// Chữ Hán, ghi tường minh theo dải mã: \p{Han} của PCRE2 dùng script
// extensions nên nhận cả dấu câu CJK như 。 là chữ Hán.
const HY_HAN = '\x{3007}\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{F900}-\x{FAFF}';
const HY_CJK_PUNC = '，。！？；：、…“”‘’（）「」';
const HY_GRAM_SUB = 'Ngữ pháp trong bài (语法)';

function hy_is_han(string $s): bool
{
    return (bool) preg_match('/[' . HY_HAN . ']/u', $s);
}

// ------------------------------------------------------- câu ví dụ ngữ pháp

/**
 * Một ô / một dòng có phải là câu trọn vẹn thuần chữ Hán không.
 * Phần ngữ pháp của bài viết ví dụ theo nhiều kiểu, nên chỉ nhận câu kết thúc
 * bằng 。？！ và có ít nhất 3 chữ: bỏ mẩu câu, từ rời và số đếm.
 */
function hy_gram_sentence(string $s): ?string
{
    $s = trim(str_replace('`', '', $s));
    $s = (string) preg_replace('/^\s*(?:[（(]\s*\d+\s*[)）]|\d+[.、)])\s*/u', '', $s);
    $s = (string) preg_replace('/^(?:Ví dụ trong bài|Ví dụ|VD)\s*[:：]\s*/u', '', $s);
    $s = (string) preg_replace('/^[QA]\s*[:：]\s*/u', '', trim($s));
    $s = (string) preg_replace('/\s+/u', '', $s);
    $bare = str_replace('**', '', $s);
    if ($s === '' || preg_match('/[a-zA-ZÀ-ỹ]/u', $bare)) {
        return null;
    }
    if (!preg_match('/^(?:\*\*|[' . HY_HAN . HY_CJK_PUNC . '0-9])+$/u', $s) || !preg_match('/[。！？]$/u', $bare)) {
        return null;
    }
    return mb_strlen((string) preg_replace('/[' . HY_CJK_PUNC . '0-9]/u', '', $bare)) >= 3 ? $s : null;
}

/**
 * Câu ví dụ trong phần "Ngữ pháp (语法)" / "Chú thích (注释)" của một bài.
 * Bỏ câu sai (đánh dấu ✗) và bảng khung câu ("Chủ ngữ | 会 | Động từ"), vì
 * bảng đó chia câu ra từng cột nên mỗi ô chỉ là một mẩu.
 * @return list<array{0:string,1:string,2:string}>  [câu, pinyin (có thể rỗng), nghĩa]
 */
function hy_grammar(string $text): array
{
    $out   = [];
    $sec   = '';
    $lines = preg_split('/\R/u', $text) ?: [];
    $n     = count($lines);
    for ($i = 0; $i < $n; $i++) {
        $ln = rtrim($lines[$i]);
        if (preg_match('/^(#{2,3})\s/u', $ln, $m)) {
            if (strlen($m[1]) === 2) {
                $sec = hy_heading($ln);
            }
            continue;
        }
        if (!preg_match('/Ngữ pháp|语法|Chú thích|注释/u', $sec) || preg_match('/[✗✘×]/u', $ln)) {
            continue;
        }
        if (str_starts_with(trim($ln), '|') && $i + 1 < $n && preg_match('/^\|\s*:?-{2,}/', trim($lines[$i + 1]))) {
            $head = array_map('mb_strtolower', hy_cells($ln));
            $keep = false;
            foreach ($head as $name) {
                $keep = $keep || preg_match('/ví dụ|chữ hán|câu|pinyin/u', $name);
            }
            $iPy = null;
            $iVi = null;
            foreach ($head as $k => $name) {
                if ($iPy === null && str_contains($name, 'pinyin')) {
                    $iPy = $k;
                }
                if ($iVi === null && (str_contains($name, 'nghĩa') || str_contains($name, 'dịch'))) {
                    $iVi = $k;
                }
            }
            for ($i += 2; $i < $n && str_starts_with(trim($lines[$i]), '|'); $i++) {
                $r = hy_cells(rtrim($lines[$i]));
                if (!$keep || preg_match('/[✗✘×]/u', implode('', $r))) {
                    continue;
                }
                $zh = null;
                $at = null;
                foreach ($r as $k => $c) {
                    if ($k !== $iPy && $k !== $iVi && ($z = hy_gram_sentence(hy_plain($c))) !== null) {
                        $zh = $z;
                        $at = $k;
                        break;
                    }
                }
                if ($zh === null) {
                    continue;
                }
                $vi = $iVi !== null ? hy_plain($r[$iVi] ?? '') : '';
                if ($vi === '') {
                    foreach ($r as $k => $c) {
                        $c = hy_plain($c);
                        if ($k !== $at && $k !== $iPy && $c !== '' && preg_match('/[a-zA-ZÀ-ỹ]/u', $c)) {
                            $vi = $c;
                            break;
                        }
                    }
                }
                $out[] = [$zh, $iPy !== null ? hy_plain($r[$iPy] ?? '') : '', hy_gram_gloss($vi)];
            }
            $i--;
            continue;
        }
        $t = trim($ln);
        if (!preg_match('/^(?:>\s*)*[-*]\s+/u', $t)) {
            continue;
        }
        $t = (string) preg_replace('/^(?:>\s*)*[-*]\s+/u', '', $t);
        $t = hy_plain((string) preg_replace('/^(?:[✓✔])\s*/u', '', $t));
        [$zh, $vi] = array_pad(preg_split('/\s+[—–]\s+/u', $t, 2) ?: [], 2, '');
        foreach (preg_split('#\s*(?:/|→|➜)\s*#u', (string) $zh) ?: [] as $cand) {
            if (($z = hy_gram_sentence($cand)) !== null) {
                $out[] = [$z, '', hy_gram_gloss(trim((string) $vi))];
            }
        }
    }
    return $out;
}

/** Nhãn cột ("Khẳng định", "Nghi vấn") không phải nghĩa của câu. */
function hy_gram_gloss(string $vi): string
{
    return preg_match('/^(khẳng định|phủ định|nghi vấn)$/iu', trim($vi)) ? '' : trim($vi);
}

// ------------------------------------------------------------------ dựng dữ liệu

function hy_build(string $vault): array
{
    $dict   = [];   // chữ Hán => ['py','vi','pos','n'(STT 500),'v'(nhóm động từ),'l'(bài)]
    $groups = [];
    $sents  = [];
    $gram   = [];   // bài => câu ví dụ trong phần ngữ pháp, nối vào cuối

    // 500 từ thông dụng
    foreach (hy_tables(hy_note($vault, HY_COMMON)) as $t) {
        foreach ($t['rows'] as $r) {
            if (count($r) >= 4 && ctype_digit($r[0]) && hy_is_han($r[1])) {
                hy_dict_add($dict, $r[1], ['py' => $r[2], 'vi' => hy_plain($r[3]), 'n' => (int) $r[0]]);
            }
        }
    }
    // 50 động từ, theo 5 nhóm
    $verbGroups = [];
    // Nhóm 6 trở đi soạn thêm: bảng từ và bảng câu nằm chung một file.
    $verbTables = hy_plus_tables('dong-tu.md');
    $verbLevel  = [];
    foreach (array_merge(hy_tables(hy_note($vault, HY_VERBS)), $verbTables) as $t) {
        foreach ($t['rows'] as $r) {
            if (count($r) >= 4 && ctype_digit($r[0]) && hy_is_han($r[1])) {
                $g = $t['h'][1];
                $verbGroups[$g][] = $r[1];
                $verbLevel[$g]    = hy_level($t['h'][0]);
                hy_dict_add($dict, $r[1], ['py' => $r[2], 'vi' => hy_plain($r[3]), 'pos' => 'VERB', 'v' => $g]);
            }
        }
    }
    // 15 bài: từ mới + hội thoại
    $lessons = glob($vault . '/HSK1 - Bài *.md') ?: [];
    sort($lessons);
    foreach ($lessons as $path) {
        $name = basename($path, '.md');
        if (!preg_match('/Bài (\d+) - (.+)$/u', $name, $m)) {
            continue;
        }
        $no    = (int) $m[1];
        $gid   = sprintf('bai-%02d', $no);
        $text  = hy_note($vault, $name);
        $words = [];
        $alias = preg_match('/^#\s.*?\*(.+?)\*\s*$/mu', $text, $am) ? trim($am[1]) : '';
        foreach (hy_tables($text) as $t) {
            $head = mb_strtolower(implode('|', $t['head']));
            if (!str_contains($head, 'chữ hán') || !str_contains($head, 'pinyin')) {
                continue;
            }
            // "Từ vựng (生词)": từ mới. "Từ vựng trên lớp (课堂用语)" là câu khẩu
            // lệnh có dịch nghĩa, nên xếp cùng hội thoại làm câu ví dụ.
            if (str_contains($t['h'][1], '生词')) {
                foreach ($t['rows'] as $r) {
                    if (count($r) < 3 || !hy_is_han($r[0])) {
                        continue;
                    }
                    // "少 / 不少" là hai từ trong một ô; pinyin chia theo cùng cách nếu khớp số.
                    $ws  = array_map('hy_clean_word', preg_split('/\s*\/\s*/u', $r[0]));
                    $pys = array_map('hy_clean_word', preg_split('/\s*\/\s*/u', $r[1]));
                    foreach ($ws as $i => $w) {
                        if ($w === '' || !hy_is_han($w)) {
                            continue;
                        }
                        $py = count($pys) === count($ws) ? $pys[$i] : ($i === 0 ? $pys[0] : '');
                        hy_dict_add($dict, $w, ['py' => $py, 'vi' => hy_plain(end($r)), 'l' => $no]);
                        $words[] = $w;
                    }
                }
            }
            elseif (str_contains($t['h'][1], 'Hội thoại') || str_contains($t['h'][1], '课堂用语')) {
                foreach ($t['rows'] as $r) {
                    if (count($r) >= 3 && hy_is_han($r[0])) {
                        $sents[] = hy_sentence_row($r, $gid, $t['h'][2] ?: $t['h'][1], false);
                    }
                }
            }
        }
        $groups[$gid] = ['id' => $gid, 'kind' => 'lesson', 'no' => $no, 'title' => trim($m[2]), 'sub' => $alias,
                         'level' => 'HSK1', 'words' => array_values(array_unique($words))];
        $gram[$gid] = hy_grammar($text);
    }

    // Luyện đặt câu: 18 mục của vault, phần A (HSK1) và phần B (HSK2–4), rồi
    // mục 19 trở đi soạn thêm theo HSK2 tới HSK6. Cấp lấy từ tiêu đề phần.
    foreach (array_merge(hy_tables(hy_note($vault, HY_PRACTICE)), hy_plus_tables('ngu-phap.md')) as $t) {
        if (!preg_match('/^(\d+)\.\s*(.+)$/u', $t['h'][1], $m) || !str_contains(mb_strtolower($t['head'][0] ?? ''), 'chữ hán')) {
            continue;
        }
        $no  = (int) $m[1];
        $gid = sprintf('muc-%02d', $no);
        if (!isset($groups[$gid])) {
            [$title, $sub] = array_pad(preg_split('/\s+—\s+/u', $m[2], 2), 2, '');
            $groups[$gid] = ['id' => $gid, 'kind' => 'practice', 'no' => $no, 'title' => trim($title), 'sub' => trim($sub),
                             'level' => hy_level($t['h'][0]), 'words' => []];
        }
        foreach ($t['rows'] as $r) {
            if (count($r) >= 3 && hy_is_han($r[0])) {
                $sents[] = hy_sentence_row($r, $gid, '', true);
            }
        }
    }
    // Đặt câu với 50 động từ
    foreach (array_merge(hy_tables(hy_note($vault, HY_VERB_EX)), $verbTables) as $t) {
        if (!str_contains($t['h'][1], 'Nhóm')) {
            continue;
        }
        $gid = 'dt-' . (preg_match('/Nhóm\s*(\d+)/u', $t['h'][1], $m) ? $m[1] : '0');
        if (!isset($groups[$gid])) {
            // "Nhóm 2: Hành động hàng ngày" → title "Hành động hàng ngày": số
            // nhóm đã nằm trong 'no', trang tự ghép lại.
            $groups[$gid] = ['id' => $gid, 'kind' => 'verbs', 'no' => (int) substr($gid, 3),
                             'title' => (string) preg_replace('/^Nhóm\s*\d+\s*:\s*/u', '', $t['h'][1]),
                             'sub' => '', 'level' => $verbLevel[$t['h'][1]] ?? 'HSK1', 'words' => $verbGroups[$t['h'][1]] ?? []];
        }
        foreach ($t['rows'] as $r) {
            if (count($r) >= 3 && hy_is_han($r[0])) {
                $sents[] = hy_sentence_row($r, $gid, '', true);
            }
        }
    }
    // Luyện nói 5 đề
    foreach (hy_tables(hy_note($vault, HY_SPEAK)) as $t) {
        if (!preg_match('/^Đề\s*(\d+)\s*[—-]\s*(.+)$/u', $t['h'][1], $m)) {
            continue;
        }
        $gid = 'de-' . $m[1];
        if (!isset($groups[$gid])) {
            $groups[$gid] = ['id' => $gid, 'kind' => 'speak', 'no' => (int) $m[1], 'title' => trim($m[2]), 'sub' => '',
                             'level' => 'HSK1', 'words' => []];
        }
        foreach ($t['rows'] as $r) {
            if (count($r) >= 3 && hy_is_han($r[0])) {
                $sents[] = hy_sentence_row($r, $gid, $t['h'][2], false);
            }
        }
    }
    // Tự giới thiệu: cả câu in đậm, nên bỏ đậm đi
    foreach (hy_tables(hy_note($vault, HY_SELF)) as $t) {
        if (!str_contains(mb_strtolower($t['head'][0] ?? ''), 'chữ hán')) {
            continue;
        }
        $groups['tu-gioi-thieu'] ??= ['id' => 'tu-gioi-thieu', 'kind' => 'speak', 'no' => 6, 'title' => 'Tự giới thiệu',
                                      'sub' => '', 'level' => 'HSK1', 'words' => []];
        foreach ($t['rows'] as $r) {
            if (count($r) >= 3 && hy_is_han($r[0])) {
                $r[0] = str_replace('**', '', $r[0]);
                $sents[] = hy_sentence_row($r, 'tu-gioi-thieu', '', false);
            }
        }
    }

    // Câu ví dụ của phần ngữ pháp, xếp sau cùng để câu hội thoại vẫn là câu
    // mẫu chính của mỗi từ. Câu đã có ở chỗ khác thì bỏ, khỏi trùng.
    $seenRaw = [];
    foreach ($sents as $s) {
        $seenRaw[str_replace('**', '', $s['raw'])] = true;
    }
    foreach ($gram as $gid => $rows) {
        foreach ($rows as $r) {
            $key = str_replace('**', '', $r[0]);
            if (isset($seenRaw[$key])) {
                continue;
            }
            $seenRaw[$key] = true;
            $row = hy_sentence_row([$r[0], $r[1], $r[2]], $gid, HY_GRAM_SUB, false);
            $row['gen'] = $r[1] === '';   // không có pinyin sẵn: ghép từ từ điển
            $sents[] = $row;
        }
    }

    // Từ chức năng dựng sẵn và từ điển của phần soạn thêm: chỉ thêm chỗ kho
    // chưa có, và bổ sung từ loại.
    foreach (hy_lexicon() + hy_plus_lexicon() as $w => [$py, $pos, $vi]) {
        if (isset($dict[$w])) {
            $dict[$w]['pos'] ??= $pos;
        }
        else {
            $dict[$w] = ['py' => $py, 'vi' => $vi, 'pos' => $pos, 'b' => 1];
        }
    }

    // Tách từ + pinyin cho mọi câu
    $charPy = hy_char_pinyin($dict);
    $maxLen = max(array_map('mb_strlen', array_keys($dict)));
    foreach ($sents as $i => &$s) {
        $s['id']   = $i;
        $s['t']    = hy_segment($s['raw'], $dict, $maxLen);
        $s['ok']   = hy_align($s['t'], $s['py'], $dict, $charPy);
        unset($s['raw']);
    }
    unset($s);

    // Từ của từng mục luyện đặt câu: từ in đậm, lần đầu xuất hiện ở mục đó.
    $seen = [];
    foreach ($sents as $s) {
        if (($groups[$s['g']]['kind'] ?? '') !== 'practice') {
            continue;
        }
        foreach ($s['t'] as $tok) {
            if (!empty($tok[2]) && $tok[3] === 'h' && !isset($seen[$tok[0]])) {
                $seen[$tok[0]] = true;
                $groups[$s['g']]['words'][] = $tok[0];
            }
        }
    }
    // Phần luyện đặt câu dùng đủ 500 từ, nhưng vài từ (phần lớn là từ chức năng:
    // 这个, 他们, 还是…) không được in đậm ở câu nào. Gắn mỗi từ đó vào mục có câu
    // đầu tiên chứa nó, để từ nào trong 500 từ cũng có thẻ.
    foreach ($dict as $w => $e) {
        if (empty($e['n']) || isset($seen[$w])) {
            continue;
        }
        foreach ($sents as $s) {
            if (($groups[$s['g']]['kind'] ?? '') !== 'practice') {
                continue;
            }
            foreach ($s['t'] as $tok) {
                if ($tok[0] === $w) {
                    $seen[$w] = true;
                    $groups[$s['g']]['words'][] = $w;
                    continue 3;
                }
            }
        }
    }

    // Pinyin từng âm tiết cho mỗi từ trong từ điển ("péng yǒu"), để trang tự
    // tách câu người dùng gõ vào mà vẫn gắn được pinyin cho từng chữ.
    foreach ($dict as $w => &$e) {
        $chars = mb_str_split($w);
        $syl   = hy_syllables((string) ($e['py'] ?? ''));
        $e['ps'] = count($syl) === count($chars) ? implode(' ', $syl)
                                                 : implode(' ', array_map(fn($c) => $charPy[$c] ?? '', $chars));
    }
    unset($e);

    return ['groups' => array_values($groups), 'sents' => $sents, 'dict' => $dict, 'charPy' => $charPy];
}

/** Bảng của một file trong lib/hanyu-plus; thiếu file thì coi như không có gì. */
function hy_plus_tables(string $file): array
{
    $path = HY_PLUS . '/' . $file;
    return is_file($path) ? hy_tables(str_replace("\r\n", "\n", (string) file_get_contents($path))) : [];
}

/** Từ điển của phần soạn thêm: mỗi dòng "chữ|pinyin|từ loại|nghĩa", dòng # là chú thích. */
function hy_plus_lexicon(): array
{
    $path = HY_PLUS . '/tu-dien.txt';
    $lex  = [];
    foreach (is_file($path) ? (file($path, FILE_IGNORE_NEW_LINES) ?: []) : [] as $line) {
        $f = explode('|', trim($line));
        if (count($f) === 4 && $f[0] !== '' && $f[0][0] !== '#') {
            $lex[$f[0]] = [$f[1], $f[2], $f[3]];
        }
    }
    return $lex;
}

/** Cấp HSK ghi trong tiêu đề phần: "Phần B — Mở rộng (HSK2–4)" → HSK2–4, "Phần D — HSK3" → HSK3; không ghi thì HSK1. */
function hy_level(string $heading): string
{
    if (!preg_match('/HSK\s*(\d)(?:\s*[–-]\s*(\d))?/u', $heading, $m)) {
        return 'HSK1';
    }
    return 'HSK' . $m[1] . (($m[2] ?? '') !== '' ? '–' . $m[2] : '');
}

/** Ô "Chữ Hán" / "Pinyin" của bảng từ mới: bỏ ghi chú trong ngoặc, dấu (*) và dấu câu cuối. "……" thành "…". */
function hy_clean_word(string $s): string
{
    $s = hy_plain($s);
    $s = (string) preg_replace('/\s*[（(][^）)]*[）)]|\s*\*+\s*$/u', '', $s);
    $s = str_replace(['……', '...'], '…', $s);
    // Không dùng trim(): danh sách ký tự của nó tính theo byte, cắt hỏng chữ UTF-8.
    return (string) preg_replace('/^[\s！!。？?，,]+|[\s！!。？?，,]+$/u', '', $s);
}

function hy_dict_add(array &$dict, string $w, array $e): void
{
    $w = trim($w);
    if ($w === '' || !hy_is_han($w)) {
        return;
    }
    if (!isset($dict[$w])) {
        $dict[$w] = $e;
        return;
    }
    foreach ($e as $k => $v) {
        $dict[$w][$k] ??= $v;
    }
}

/** Một hàng bảng câu: [chữ Hán, pinyin, nghĩa]. "A: …" là người nói; "(#377)" trong nghĩa là số tra 500 từ. */
function hy_sentence_row(array $r, string $gid, string $sub, bool $keepBold): array
{
    $zh  = trim($r[0]);
    $spk = null;
    if (preg_match('/^([A-Z])\s*[:：]\s*/u', $zh, $m)) {
        $spk = $m[1];
        $zh  = trim(substr($zh, strlen($m[0])));
    }
    if (!$keepBold) {
        $zh = str_replace('**', '', $zh);
    }
    $vi  = $r[2];
    $vi  = trim((string) preg_replace('/\(#\d+\)\s*/', '', $vi));
    return ['g' => $gid, 'sub' => $sub, 'spk' => $spk, 'raw' => $zh, 'py' => hy_plain($r[1]), 'vi' => hy_plain($vi)];
}

// ------------------------------------------------------------------ tách từ

/**
 * Câu → token [chữ, pinyin, đậm, loại]; loại: 'h' từ chữ Hán, 'x' số / chữ Latin, 'p' dấu câu.
 * Chữ in đậm là từ đã đánh dấu sẵn; trong một cụm đậm, dấu cách tách từ.
 */
function hy_segment(string $raw, array $dict, int $maxLen): array
{
    $toks = [];
    foreach (preg_split('/(\*\*.+?\*\*)/u', $raw, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $part) {
        if (str_starts_with($part, '**') && str_ends_with($part, '**') && mb_strlen($part) > 4) {
            foreach (preg_split('/\s+/u', trim(substr($part, 2, -2))) as $piece) {
                if ($piece === '') {
                    continue;
                }
                if (isset($dict[$piece]) || mb_strlen($piece) <= 2 || !preg_match('/^[' . HY_HAN . ']+$/u', $piece)) {
                    $toks[] = [$piece, '', 1, hy_is_han($piece) ? 'h' : 'x'];
                }
                else {
                    foreach (hy_maxmatch($piece, $dict, $maxLen) as $w) {
                        $toks[] = [$w, '', 1, 'h'];
                    }
                }
            }
            continue;
        }
        // phần thường: dấu câu, số/Latin, và chuỗi chữ Hán
        preg_match_all('/[' . HY_HAN . ']+|[0-9A-Za-z.%]+|[^' . HY_HAN . '0-9A-Za-z\s]|\s+/u', $part, $m);
        foreach ($m[0] as $chunk) {
            if (trim($chunk) === '') {
                continue;
            }
            if (preg_match('/^[' . HY_HAN . ']+$/u', $chunk)) {
                foreach (hy_maxmatch($chunk, $dict, $maxLen) as $w) {
                    $toks[] = [$w, '', 0, 'h'];
                }
            }
            else {
                $toks[] = [$chunk, '', 0, preg_match('/^[0-9A-Za-z]/', $chunk) ? 'x' : 'p'];
            }
        }
    }
    return $toks;
}

const HY_NUM = '零一二三四五六七八九十百千万两';

/** Khớp dài nhất từ trái sang; chuỗi chữ số Hán không có trong từ điển gộp thành một số. */
function hy_maxmatch(string $han, array $dict, int $maxLen): array
{
    $chars = mb_str_split($han);
    $n     = count($chars);
    $out   = [];
    for ($i = 0; $i < $n;) {
        $best = 1;
        for ($len = min($maxLen, $n - $i); $len > 1; $len--) {
            if (isset($dict[implode('', array_slice($chars, $i, $len))])) {
                $best = $len;
                break;
            }
        }
        if ($best === 1 && mb_strpos(HY_NUM, $chars[$i]) !== false) {
            while ($i + $best < $n && mb_strpos(HY_NUM, $chars[$i + $best]) !== false) {
                $best++;
            }
        }
        $out[] = implode('', array_slice($chars, $i, $best));
        $i += $best;
    }
    // 们 là hậu tố số nhiều: 孩子们, 同学们 là một từ.
    for ($i = count($out) - 1; $i > 0; $i--) {
        if ($out[$i] === '们' && mb_strpos(HY_NUM, mb_substr($out[$i - 1], -1)) === false) {
            $out[$i - 1] .= '们';
            array_splice($out, $i, 1);
        }
    }
    // Hai chữ liền nhau mà từ điển không biết chữ nào thì gần như chắc là một
    // từ hai chữ chưa có trong từ điển (风景); chuỗi dài hơn thì để nguyên.
    $unk = fn(string $w) => mb_strlen($w) === 1 && !isset($dict[$w]) && mb_strpos(HY_NUM, $w) === false;
    $res = [];
    for ($i = 0, $m = count($out); $i < $m; $i++) {
        if ($unk($out[$i]) && $i + 1 < $m && $unk($out[$i + 1])
            && !($i > 0 && $unk($out[$i - 1])) && !($i + 2 < $m && $unk($out[$i + 2]))) {
            $res[] = $out[$i] . $out[++$i];
            continue;
        }
        $res[] = $out[$i];
    }
    return $res;
}

// ------------------------------------------------------------------ pinyin

const HY_TONE_FROM = ['ā','á','ǎ','à','ē','é','ě','è','ī','í','ǐ','ì','ō','ó','ǒ','ò','ū','ú','ǔ','ù','ǖ','ǘ','ǚ','ǜ','ü'];
const HY_TONE_TO   = ['a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','u','u','u','u','v','v','v','v','v'];

function hy_py_norm(string $s): string
{
    return str_replace(HY_TONE_FROM, HY_TONE_TO, mb_strtolower($s));
}

function hy_is_syl(string $s, bool $first): bool
{
    if (!$first && preg_match('/^[aoe]/', $s)) {
        return false;   // âm tiết mở đầu bằng a/o/e giữa từ phải có dấu ' đứng trước
    }
    return (bool) preg_match('/^(?:zh|ch|sh|[bpmfdtnlgkhjqxrzcsyw])?(?:iang|iong|uang|ueng|iao|ian|ing|uai|uan|ang|eng|ong|ai|ei|ao|ou|an|en|er|ia|ie|iu|in|ua|uo|ui|un|ve|ue|a|o|e|i|u|v)r?$/', $s);
}

/**
 * Dòng pinyin → danh sách âm tiết, giữ nguyên dấu thanh. Mỗi cụm chữ Latin
 * được chia theo khớp dài nhất có quay lui.
 */
function hy_syllables(string $py): array
{
    $out = [];
    preg_match_all('/[\p{L}]+/u', $py, $m);   // dấu ' và dấu câu là ranh giới
    foreach ($m[0] as $word) {
        $chars = mb_str_split($word);
        $norm  = mb_str_split(hy_py_norm($word));
        if (count($chars) !== count($norm)) {
            return [];
        }
        $split = hy_syl_split($norm, 0, true, []);
        if ($split === null) {
            return [];
        }
        foreach ($split as [$a, $b]) {
            $out[] = implode('', array_slice($chars, $a, $b - $a));
        }
    }
    return $out;
}

function hy_syl_split(array $norm, int $at, bool $first, array $memo): ?array
{
    $n = count($norm);
    if ($at === $n) {
        return [];
    }
    for ($len = min(7, $n - $at); $len >= 1; $len--) {
        $cand = implode('', array_slice($norm, $at, $len));
        if (!hy_is_syl($cand, $first)) {
            continue;
        }
        $rest = hy_syl_split($norm, $at + $len, false, $memo);
        if ($rest !== null) {
            return array_merge([[$at, $at + $len]], $rest);
        }
    }
    return null;
}

/** Pinyin từng chữ, rút từ các từ nhiều chữ trong từ điển (dùng khi không chia được dòng pinyin của câu). */
function hy_char_pinyin(array $dict): array
{
    $map = [];
    foreach ($dict as $w => $e) {
        $chars = mb_str_split($w);
        $syl   = hy_syllables((string) ($e['py'] ?? ''));
        if (count($syl) === count($chars)) {
            foreach ($chars as $k => $c) {
                $map[$c] ??= $syl[$k];
            }
        }
    }
    return $map;
}

/**
 * Gắn pinyin vào token, từng chữ một, từ dòng pinyin của câu.
 *   - "儿" đọc dính (yìdiǎnr) không có âm tiết riêng;
 *   - một con số (50岁) nhận hết số âm tiết dư ra (wǔshí);
 *   - chữ Latin, hay số chữ không khớp số âm tiết: lấy pinyin từ từ điển.
 * Trả về true nếu dùng được pinyin của chính câu.
 */
function hy_align(array &$toks, string $py, array $dict, array $charPy): bool
{
    $syl   = hy_syllables($py);
    $units = [];            // [chỉ số token, 'c' chữ Hán | 'd' con số, chữ]
    $latin = false;
    foreach ($toks as $k => $t) {
        if ($t[3] === 'h') {
            foreach (mb_str_split($t[0]) as $c) {
                $units[] = [$k, 'c', $c];
            }
        }
        elseif ($t[3] === 'x') {
            if (preg_match('/^\d+$/', $t[0])) {
                $units[] = [$k, 'd', $t[0]];
            }
            else {
                $latin = true;
            }
        }
    }
    $nd = count(array_filter($units, fn($u) => $u[1] === 'd'));
    $nc = count($units) - $nd;
    $ns = count($syl);

    $assign = null;
    if ($syl && !$latin && $nd === 0) {
        $assign = [];
        $j = 0;
        foreach ($units as $i => [$k, , $c]) {
            $prev = $j > 0 ? hy_py_norm($syl[$j - 1]) : '';
            if ($c === '儿' && $prev !== '' && $prev !== 'er' && str_ends_with($prev, 'r') && ($nc - $i) > ($ns - $j)) {
                $assign[] = [$k, ''];
                continue;
            }
            if ($j >= $ns) {
                $assign = null;
                break;
            }
            $assign[] = [$k, $syl[$j++]];
        }
        if ($assign !== null && $j !== $ns) {
            $assign = null;
        }
    }
    elseif ($syl && !$latin && $nd === 1 && $ns - $nc >= 1 && !str_contains(implode('', array_column($units, 2)), '儿')) {
        $assign = [];
        $j = 0;
        foreach ($units as [$k, $type]) {
            $take = $type === 'd' ? $ns - $nc : 1;
            $assign[] = [$k, implode('', array_slice($syl, $j, $take))];
            $j += $take;
        }
    }

    // Pinyin của token là các âm tiết cách nhau một dấu cách, đúng một âm tiết
    // cho mỗi chữ ("péng yǒu"; "儿" đọc dính để trống), để trang còn tách được
    // về từng chữ. Con số giữ nguyên các âm tiết của nó.
    if ($assign !== null) {
        $per = [];
        foreach ($assign as [$k, $s]) {
            $per[$k][] = $s;
        }
        foreach ($per as $k => $list) {
            $toks[$k][1] = $toks[$k][3] === 'x' ? implode(' ', hy_syllables($list[0])) : implode(' ', $list);
        }
        return true;
    }
    foreach ($toks as &$t) {
        if ($t[3] !== 'h') {
            continue;
        }
        $chars = mb_str_split($t[0]);
        $syl   = isset($dict[$t[0]]) ? hy_syllables((string) $dict[$t[0]]['py']) : [];
        $t[1]  = count($syl) === count($chars) ? implode(' ', $syl)
                                               : implode(' ', array_map(fn($c) => $charPy[$c] ?? '', $chars));
    }
    unset($t);
    return false;
}

// ------------------------------------------------------------------ từ chức năng

/**
 * Từ HSK1 hay gặp mà danh sách từ vựng thường không liệt kê riêng: đại từ,
 * trợ từ, phó từ, giới từ, lượng từ, số, từ chỉ thời gian. Dùng để tách từ
 * phần không in đậm, và cho trang phân tích câu biết từ loại.
 * Từ loại theo Universal Dependencies, thêm CLF (lượng từ) và TIME (từ chỉ thời gian).
 * @return array<string, array{0:string,1:string,2:string}>  chữ => [pinyin, từ loại, nghĩa]
 */
function hy_lexicon(): array
{
    static $lex = null;
    if ($lex !== null) {
        return $lex;
    }
    $raw = <<<'LEX'
我|wǒ|PRON|tôi
你|nǐ|PRON|bạn
您|nín|PRON|ngài, ông, bà
他|tā|PRON|anh ấy
她|tā|PRON|cô ấy
它|tā|PRON|nó
我们|wǒmen|PRON|chúng tôi
你们|nǐmen|PRON|các bạn
他们|tāmen|PRON|họ
她们|tāmen|PRON|họ (nữ)
咱们|zánmen|PRON|chúng ta
大家|dàjiā|PRON|mọi người
自己|zìjǐ|PRON|tự mình
这|zhè|PRON|này, đây
那|nà|PRON|kia, đó
哪|nǎ|PRON|nào
这儿|zhèr|PRON|ở đây
那儿|nàr|PRON|ở đó
哪儿|nǎr|PRON|ở đâu
这里|zhèlǐ|PRON|ở đây
那里|nàlǐ|PRON|ở đó
哪里|nǎlǐ|PRON|ở đâu
这个|zhège|PRON|cái này
那个|nàge|PRON|cái kia
谁|shéi|PRON|ai
什么|shénme|PRON|cái gì
怎么|zěnme|PRON|thế nào, sao
怎么样|zěnmeyàng|PRON|thế nào
多少|duōshao|PRON|bao nhiêu
几|jǐ|NUM|mấy
为什么|wèishénme|PRON|tại sao
的|de|PART|(trợ từ kết cấu)
地|de|PART|(trợ từ trạng ngữ)
得|de|PART|(trợ từ bổ ngữ)
了|le|PART|rồi, đã
着|zhe|PART|đang (trạng thái)
过|guo|PART|đã từng
吗|ma|PART|không? (hỏi)
呢|ne|PART|còn…?; đang
吧|ba|PART|nhé, đi
啊|a|PART|à, nhỉ
呀|ya|PART|à
很|hěn|ADV|rất
太|tài|ADV|quá
真|zhēn|ADV|thật
也|yě|ADV|cũng
都|dōu|ADV|đều
不|bù|ADV|không
没|méi|ADV|không, chưa
没有|méiyǒu|ADV|không, chưa có
别|bié|ADV|đừng
还|hái|ADV|còn, vẫn
就|jiù|ADV|thì, liền, chính
才|cái|ADV|mới
再|zài|ADV|lại, nữa
又|yòu|ADV|lại
已经|yǐjīng|ADV|đã
常常|chángcháng|ADV|thường
经常|jīngcháng|ADV|thường xuyên
一起|yìqǐ|ADV|cùng nhau
非常|fēicháng|ADV|vô cùng
特别|tèbié|ADV|đặc biệt
最|zuì|ADV|nhất
更|gèng|ADV|hơn
一定|yídìng|ADV|nhất định
正在|zhèngzài|ADV|đang
总是|zǒngshì|ADV|luôn luôn
只|zhǐ|ADV|chỉ
多|duō|ADJ|nhiều
少|shǎo|ADJ|ít
在|zài|ADP|ở, tại
从|cóng|ADP|từ
跟|gēn|ADP|với
给|gěi|ADP|cho
对|duì|ADP|đối với
向|xiàng|ADP|hướng về
往|wǎng|ADP|về phía
离|lí|ADP|cách
比|bǐ|ADP|so với
把|bǎ|ADP|(đưa tân ngữ lên trước)
被|bèi|ADP|bị, được
和|hé|CCONJ|và
但是|dànshì|CCONJ|nhưng
可是|kěshì|CCONJ|nhưng
还是|háishi|CCONJ|hay là
或者|huòzhě|CCONJ|hoặc
而且|érqiě|CCONJ|hơn nữa
因为|yīnwèi|SCONJ|bởi vì
所以|suǒyǐ|SCONJ|cho nên
如果|rúguǒ|SCONJ|nếu
虽然|suīrán|SCONJ|tuy
会|huì|AUX|biết, sẽ
能|néng|AUX|có thể
可以|kěyǐ|AUX|có thể, được phép
想|xiǎng|AUX|muốn
要|yào|AUX|muốn, sẽ, phải
应该|yīnggāi|AUX|nên
是|shì|VERB|là
有|yǒu|VERB|có
叫|jiào|VERB|gọi, tên là
个|gè|CLF|cái, chiếc
本|běn|CLF|quyển
口|kǒu|CLF|(người trong nhà)
块|kuài|CLF|đồng (tiền)
岁|suì|CLF|tuổi
杯|bēi|CLF|cốc
件|jiàn|CLF|chiếc (áo, việc)
位|wèi|CLF|vị (người)
次|cì|CLF|lần
家|jiā|NOUN|nhà
点|diǎn|CLF|giờ
分|fēn|CLF|phút
号|hào|CLF|ngày (trong tháng)
月|yuè|NOUN|tháng
年|nián|NOUN|năm
天|tiān|NOUN|ngày
一|yī|NUM|một
二|èr|NUM|hai
两|liǎng|NUM|hai
三|sān|NUM|ba
四|sì|NUM|bốn
五|wǔ|NUM|năm
六|liù|NUM|sáu
七|qī|NUM|bảy
八|bā|NUM|tám
九|jiǔ|NUM|chín
十|shí|NUM|mười
百|bǎi|NUM|trăm
千|qiān|NUM|nghìn
万|wàn|NUM|vạn
今天|jīntiān|TIME|hôm nay
明天|míngtiān|TIME|ngày mai
昨天|zuótiān|TIME|hôm qua
现在|xiànzài|TIME|bây giờ
今年|jīnnián|TIME|năm nay
明年|míngnián|TIME|năm sau
去年|qùnián|TIME|năm ngoái
上午|shàngwǔ|TIME|buổi sáng
中午|zhōngwǔ|TIME|buổi trưa
下午|xiàwǔ|TIME|buổi chiều
早上|zǎoshang|TIME|sáng sớm
晚上|wǎnshang|TIME|buổi tối
星期|xīngqī|NOUN|tuần, thứ
星期一|xīngqīyī|TIME|thứ Hai
星期二|xīngqī'èr|TIME|thứ Ba
星期三|xīngqīsān|TIME|thứ Tư
星期四|xīngqīsì|TIME|thứ Năm
星期五|xīngqīwǔ|TIME|thứ Sáu
星期六|xīngqīliù|TIME|thứ Bảy
星期天|xīngqītiān|TIME|Chủ nhật
星期日|xīngqīrì|TIME|Chủ nhật
一共|yígòng|ADV|tổng cộng
时候|shíhou|NOUN|lúc
以后|yǐhòu|TIME|sau này
以前|yǐqián|TIME|trước đây
刚才|gāngcái|TIME|vừa nãy
上|shàng|NOUN|trên
下|xià|NOUN|dưới
里|lǐ|NOUN|trong
前面|qiánmiàn|NOUN|phía trước
后面|hòumiàn|NOUN|phía sau
中国|Zhōngguó|PROPN|Trung Quốc
越南|Yuènán|PROPN|Việt Nam
北京|Běijīng|PROPN|Bắc Kinh
汉语|Hànyǔ|NOUN|tiếng Hán
中文|Zhōngwén|NOUN|tiếng Trung
人|rén|NOUN|người
好|hǎo|ADJ|tốt, khỏe
大|dà|ADJ|to
小|xiǎo|ADJ|nhỏ
高兴|gāoxìng|ADJ|vui
认识|rènshi|VERB|quen biết
谢谢|xièxie|VERB|cảm ơn
不客气|bú kèqi|INTJ|đừng khách sáo
没关系|méi guānxi|INTJ|không sao
对不起|duìbuqǐ|INTJ|xin lỗi
再见|zàijiàn|INTJ|tạm biệt
请|qǐng|VERB|mời, xin
喂|wèi|INTJ|alô
嘿|hēi|INTJ|này, ê
李|Lǐ|PROPN|(họ) Lý
王|Wáng|PROPN|(họ) Vương
张|Zhāng|PROPN|(họ) Trương
陈明日|Chén Míngrì|PROPN|Trần Minh Nhật
明日|Míngrì|PROPN|Minh Nhật
胡志明市|Húzhìmíng Shì|PROPN|TP. Hồ Chí Minh
姐姐|jiějie|NOUN|chị gái
姐妹|jiěmèi|NOUN|chị em
黑板|hēibǎn|NOUN|bảng đen
图书馆|túshūguǎn|NOUN|thư viện
大学生|dàxuéshēng|NOUN|sinh viên
软件|ruǎnjiàn|NOUN|phần mềm
工程师|gōngchéngshī|NOUN|kỹ sư
爱好|àihào|NOUN|sở thích
技术|jìshù|NOUN|kỹ thuật
研究|yánjiū|VERB|nghiên cứu
军人|jūnrén|NOUN|quân nhân
小偷|xiǎotōu|NOUN|kẻ trộm
春天|chūntiān|TIME|mùa xuân
真相|zhēnxiàng|NOUN|sự thật
答案|dá'àn|NOUN|đáp án
信心|xìnxīn|NOUN|niềm tin
现实|xiànshí|NOUN|hiện thực
历史|lìshǐ|NOUN|lịch sử
旁边|pángbiān|NOUN|bên cạnh
梦|mèng|NOUN|giấc mơ
门|mén|NOUN|cửa
课|kè|NOUN|bài học, tiết học
班|bān|NOUN|lớp
案|àn|NOUN|vụ án
大声|dàshēng|ADV|to tiếng
先|xiān|ADV|trước
该|gāi|AUX|nên
新|xīn|ADJ|mới
早|zǎo|ADJ|sớm
快|kuài|ADJ|nhanh
慢|màn|ADJ|chậm
行|xíng|ADJ|được
正确|zhèngquè|ADJ|đúng
难忘|nánwàng|ADJ|khó quên
共同|gòngtóng|ADJ|chung
好玩|hǎowán|ADJ|vui, hay
见面|jiànmiàn|VERB|gặp mặt
前进|qiánjìn|VERB|tiến lên
开会|kāihuì|VERB|họp
出发|chūfā|VERB|xuất phát
答应|dāying|VERB|đồng ý, hứa
懂|dǒng|VERB|hiểu
带|dài|VERB|mang
偷|tōu|VERB|trộm
玩|wán|VERB|chơi
办|bàn|VERB|làm, xử lý
帮帮|bāngbang|VERB|giúp một chút
谢|xiè|VERB|cảm ơn
遍|biàn|CLF|lượt, lần
条|tiáo|CLF|(lượng từ) cái, con, tin
名|míng|CLF|(lượng từ) người, hạng
刻|kè|CLF|khắc (15 phút)
半|bàn|NUM|rưỡi, nửa
超出|chāochū|VERB|vượt quá
想像|xiǎngxiàng|VERB|tưởng tượng
十二|shí'èr|NUM|mười hai
二十|èrshí|NUM|hai mươi
三十|sānshí|NUM|ba mươi
一百|yībǎi|NUM|một trăm
们|men|PART|(hậu tố số nhiều)
孩子们|háizimen|NOUN|bọn trẻ, các con
先生们|xiānshengmen|NOUN|các quý ông
事|shì|NOUN|việc, chuyện
话|huà|NOUN|lời nói
意见|yìjiàn|NOUN|ý kiến
心|xīn|NOUN|trái tim, lòng
晚饭|wǎnfàn|NOUN|bữa tối
饭|fàn|NOUN|cơm, bữa ăn
病|bìng|NOUN|bệnh
城市|chéngshì|NOUN|thành phố
会议|huìyì|NOUN|cuộc họp
风景|fēngjǐng|NOUN|phong cảnh
句子|jùzi|NOUN|câu
梦想|mèngxiǎng|NOUN|ước mơ
打|dǎ|VERB|đánh; gọi (điện thoại)
到|dào|VERB|đến
等|děng|VERB|đợi
见|jiàn|VERB|gặp, thấy
要走|yào zǒu|VERB|phải đi, sắp đi
保密|bǎomì|VERB|giữ bí mật
后悔|hòuhuǐ|VERB|hối hận
做饭|zuò fàn|VERB|nấu cơm
长大|zhǎngdà|VERB|lớn lên
变|biàn|VERB|thay đổi
在乎|zàihu|VERB|để tâm, quan tâm
碰|pèng|VERB|chạm, đụng
管|guǎn|VERB|quản, để ý tới
像|xiàng|VERB|giống, như
忘|wàng|VERB|quên
赢|yíng|VERB|thắng
睡|shuì|VERB|ngủ
迟到|chídào|VERB|đến muộn
活|huó|VERB|sống
失败|shībài|VERB|thất bại
值得|zhídé|VERB|đáng
帮|bāng|VERB|giúp
为|wèi|ADP|vì, cho
美|měi|ADJ|đẹp
黑|hēi|ADJ|tối, đen
失望|shīwàng|ADJ|thất vọng
高|gāo|ADJ|cao
疼|téng|ADJ|đau
复杂|fùzá|ADJ|phức tạp
难|nán|ADJ|khó
善良|shànliáng|ADJ|lương thiện
宝贵|bǎoguì|ADJ|quý giá
棒|bàng|ADJ|giỏi, tuyệt
精彩|jīngcǎi|ADJ|đặc sắc, hay
错|cuò|ADJ|sai
晚|wǎn|ADJ|muộn
好久|hǎojiǔ|ADJ|lâu lắm
久|jiǔ|ADJ|lâu
着急|zháojí|ADJ|sốt ruột, vội
幸福|xìngfú|ADJ|hạnh phúc
穷|qióng|ADJ|nghèo
长|cháng|ADJ|dài
多大|duōdà|PRON|bao nhiêu tuổi, lớn cỡ nào
岁数|suìshu|NOUN|số tuổi
年纪|niánjì|NOUN|tuổi tác
LEX;
    $lex = [];
    foreach (explode("\n", trim($raw)) as $line) {
        [$w, $py, $pos, $vi] = explode('|', $line);
        $lex[$w] = [$py, $pos, $vi];
    }
    return $lex;
}
