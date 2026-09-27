<?php

declare(strict_types=1);

/**
 * Dữ liệu cho ba trang đồ thị của zh-svo, dựng một lần rồi lưu đệm.
 *
 *   zs_data()['verbs']    {GROUPS, DATA}  động từ HSK1–4, mỗi từ hai câu     → window.SVO_VERBS
 *   zs_data()['grammar']  TOPICS          58 mục ngữ pháp HSK1–6             → window.TOPICS
 *   zs_data()['dialogue'] TOPICS          15 bài hội thoại + 6 bài luyện nói → window.TOPICS
 *
 * Câu lấy từ lib/hanyu.php (đã tách từ, căn pinyin), cú pháp từ
 * lib/hanyu_syntax.php. Kết quả phân tích lưu trong .parse.json, dựng lại khi
 * dữ liệu câu (.cache.json) hay bộ phân tích thay đổi.
 *
 * Mỗi câu có dạng {zh, py, vi, spk, p, note?, tokens:[{w, py, pos, func, head, vi}]}
 * — graph.js của zh-svo đọc đúng hình dạng này.
 */

require_once dirname(__DIR__, 2) . '/lib/hanyu_syntax.php';

function zs_e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/** @return array{data: ?array, error: ?string} */
function zs_data(): array
{
    $load = hy_load(__DIR__ . '/.cache.json');
    if (!$load['data']) {
        return ['data' => null, 'error' => $load['error']];
    }
    $cache = __DIR__ . '/.parse.json';
    $sig   = md5(implode('|', [
        @filemtime(__DIR__ . '/.cache.json'),
        filemtime(dirname(__DIR__, 2) . '/lib/hanyu_syntax.php'),
        filemtime(__FILE__),
    ]));
    if (is_file($cache)) {
        $c = json_decode((string) file_get_contents($cache), true);
        if (is_array($c) && ($c['sig'] ?? '') === $sig) {
            return ['data' => $c['data'], 'error' => null];
        }
    }
    $data = zs_build($load['data']);
    $json = json_encode(['sig' => $sig, 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json !== false) {
        $tmp = $cache . '.' . getmypid() . '.tmp';
        if (@file_put_contents($tmp, $json) !== false) {
            @rename($tmp, $cache);
        }
    }
    return ['data' => $data, 'error' => null];
}

function zs_build(array $d): array
{
    $POS  = hs_pos_table($d['dict'], $d['sents']);
    $byG  = [];
    foreach ($d['sents'] as $s) {
        $byG[$s['g']][] = $s;
    }
    $sent = function (array $s) use ($POS, $d): array {
        $W = hs_parse($s['t'], $POS, $d['dict']);
        foreach ($W as &$t) {
            // Pinyin lấy từ câu nên chữ đầu câu viết hoa: hạ xuống, trừ tên riêng.
            if ($t['pos'] !== 'PROPN') {
                $t['py'] = mb_strtolower($t['py']);
            }
            $t['vi'] = zs_gloss($t['vi']);
        }
        unset($t);
        $row = [
            'zh'     => implode('', array_map(fn($t) => $t[0], $s['t'])),
            'py'     => (string) $s['py'],
            'vi'     => (string) $s['vi'],
            'spk'    => (string) ($s['spk'] ?? ''),
            'p'      => hs_pattern($W),
            'tokens' => $W,
        ];
        if (!empty($s['gen'])) {
            // Câu ví dụ trong phần ngữ pháp của bài không kèm pinyin: ghép theo
            // từ điển (âm tiết cách nhau, số giữ nguyên) nên đọc theo thanh gốc.
            $py = [];
            foreach ($s['t'] as $t) {
                if ($t[3] === 'p') {
                    continue;
                }
                // Âm tiết dính nhau trong một từ (lǎo shī → lǎoshī), trừ tên
                // riêng nhiều chữ vẫn viết rời (Lǐ Yuè).
                $syl  = explode(' ', (string) $t[1]);
                $name = count($syl) > 1 && preg_match('/^\p{Lu}/u', $syl[1]);
                $py[] = $t[3] === 'x' ? $t[0] : implode($name ? ' ' : '', $syl);
            }
            $row['py']   = mb_strtoupper(mb_substr($p = implode(' ', array_filter($py)), 0, 1)) . mb_substr($p, 1);
            $row['note'] = 'Pinyin ghép theo từ điển, chưa chỉnh <em>biến điệu</em>.';
        }
        return $row;
    };

    // ---- 01 · động từ: mỗi động từ hai câu, câu đầu là câu chính ----
    $GROUPS = [];
    $DATA   = [];
    foreach ($d['groups'] as $g) {
        if ($g['kind'] !== 'verbs') {
            continue;
        }
        $from = count($DATA);
        $rows = $byG[$g['id']] ?? [];
        $used = [];
        foreach ($g['words'] as $i => $v) {
            $mine = [];
            foreach ($rows as $k => $s) {
                if (isset($used[$k])) {
                    continue;
                }
                foreach ($s['t'] as $t) {
                    if ($t[2] && $t[0] === $v) {
                        $mine[] = $k;
                        break;
                    }
                }
            }
            if (!$mine) {   // không thấy từ in đậm: câu thứ 2i, 2i+1 theo thứ tự
                $mine = array_values(array_filter([2 * $i, 2 * $i + 1], fn($k) => isset($rows[$k]) && !isset($used[$k])));
            }
            if (!$mine) {
                continue;
            }
            $e    = $d['dict'][$v] ?? [];
            $note = '<strong lang="zh">' . zs_e($v) . '</strong> <em>' . zs_e((string) ($e['py'] ?? '')) . '</em> — ' . zs_e(zs_gloss((string) ($e['vi'] ?? '')));
            $list = [];
            foreach ($mine as $k) {
                $used[$k] = true;
                $x = $sent($rows[$k]);
                $x['note'] = $note;
                // Khung quanh chính động từ này, không phải vị ngữ của cả câu;
                // động từ năng nguyện (会 能 可以) thì lấy khung của động từ nó đi kèm.
                foreach ($x['tokens'] as $ti => $t) {
                    if ($t['w'] === $v) {
                        $x['p'] = hs_pattern($x['tokens'], $t['func'] === 'aux' ? $t['head'] : $ti);
                        break;
                    }
                }
                $list[] = $x;
            }
            $main = array_shift($list);
            $DATA[] = ['verb' => $v, 'pattern' => $main['p']] + $main + ['ex' => $list];
        }
        // Nhóm HSK1 giữ tên cũ; nhóm soạn thêm ghi cấp ở đầu cho thanh bên.
        $name     = preg_replace('/^Nhóm\s*\d+\s*:\s*/u', '', $g['title']);
        $GROUPS[] = ['name' => ($g['level'] !== 'HSK1' ? $g['level'] . ' · ' : '') . $name, 'from' => $from, 'to' => count($DATA)];
    }

    // ---- 02 · theo ngữ pháp: mỗi mục một danh sách câu ----
    $grammar = [];
    foreach ($d['groups'] as $g) {
        if ($g['kind'] !== 'practice') {
            continue;
        }
        $ex = array_map($sent, $byG[$g['id']] ?? []);
        if (!$ex) {
            continue;
        }
        $grammar[] = [
            // Tên nhóm trên thanh bên: HSK1, rồi mục 15–18 (mở rộng chung HSK2–4
            // của vault), rồi HSK2 tới HSK6 soạn thêm.
            'g' => $g['level'] === 'HSK2–4' ? 'Mở rộng HSK2–4' : $g['level'],
            'n' => sprintf('%02d · %s', $g['no'], $g['title']),
            'b' => (string) $g['sub'],
            'v' => [['v' => $g['title'], 'ex' => $ex]],
        ];
    }

    // ---- 03 · hội thoại: bài → từng đoạn hội thoại → từng lượt lời ----
    $dialogue = [];
    foreach ($d['groups'] as $g) {
        if (!in_array($g['kind'], ['lesson', 'speak'], true)) {
            continue;
        }
        $subs = [];
        foreach ($byG[$g['id']] ?? [] as $s) {
            $subs[zs_sub((string) $s['sub'])][] = $sent($s);
        }
        if (!$subs) {
            continue;
        }
        $v = [];
        foreach ($subs as $name => $ex) {
            $v[] = ['v' => $name, 'ex' => $ex];
        }
        $dialogue[] = [
            'g' => $g['kind'] === 'lesson' ? 'Giáo trình HSK1' : 'Luyện nói',
            'n' => $g['kind'] === 'lesson' ? sprintf('Bài %d · %s', $g['no'], $g['title']) : $g['title'],
            'b' => (string) $g['title'],
            'v' => $v,
        ];
    }

    $count = fn(array $topics) => array_sum(array_map(fn($t) => array_sum(array_map(fn($v) => count($v['ex']), $t['v'])), $topics));
    return [
        'verbs'    => ['GROUPS' => $GROUPS, 'DATA' => $DATA],
        'grammar'  => $grammar,
        'dialogue' => $dialogue,
        'stats'    => [
            'verbs'         => count($DATA),
            'verbSents'     => array_sum(array_map(fn($x) => 1 + count($x['ex']), $DATA)),
            'grammar'       => count($grammar),
            'grammarSents'  => $count($grammar),
            'dialogue'      => count($dialogue),
            'dialogueSents' => $count($dialogue),
        ],
    ];
}

/** "Hội thoại 1 — Ở trường (在学校)" → "Hội thoại 1 · Ở trường (在学校)"; "4. Từ vựng trên lớp (课堂用语)" → "Câu dùng trên lớp (课堂用语)". */
function zs_sub(string $sub): string
{
    if ($sub === '') {
        return 'Bài mẫu';
    }
    if (str_contains($sub, '课堂用语')) {
        return 'Câu dùng trên lớp (课堂用语)';
    }
    return preg_replace('/\s*—\s*/u', ' · ', $sub);
}

/** Nghĩa gọn cho bảng chi tiết: bỏ nhãn từ loại "(đgt.)" đứng đầu. */
function zs_gloss(string $vi): string
{
    $vi = preg_replace('/^\(?\s*(đgt|đg|tt|đt|dt|pht|st|lt|gt)\.\s*\)?\s*/u', '', trim($vi));
    return (string) $vi;
}
