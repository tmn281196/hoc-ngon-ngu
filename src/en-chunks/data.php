<?php

declare(strict_types=1);

/**
 * Nạp kho khối từ chunks.json, file duy nhất chứa dữ liệu của trang.
 *
 * Thêm, sửa khối là sửa thẳng chunks.json rồi tải lại trang. Mỗi nhóm:
 *
 *     { "id", "title", "level", "desc", "intro",
 *       "sections": [ { "title", "level", "desc",
 *                       "chunks": [ { "c", "vi", "use", "ex", "done" } ] } ] }
 *
 * c    khối; "a / b" là biến thể, "…" là chỗ trống, "(at)" là phần tuỳ chọn,
 *      someone / something / your là chỗ điền, "+ -ing" là chỗ điền ngữ pháp
 * vi   nghĩa tiếng Việt; use: dùng khi nào; ex: câu ví dụ có chứa khối
 * done true khi đã dùng khối đó thật (thẻ hiện ✓)
 *
 * Thứ tự nhóm là thứ tự học. Số thứ tự mục, các mức A2/B1/B2 tách từ "level"
 * và tổng số khối được tính ở đây, không ghi trong file.
 */

const CHUNKS_FILE = __DIR__ . '/chunks.json';

/** @return array{data: ?array, error: ?string} */
function chunks_load(): array
{
    if (!is_file(CHUNKS_FILE)) {
        return ['data' => null, 'error' => 'Không thấy chunks.json.'];
    }
    $raw = json_decode((string) file_get_contents(CHUNKS_FILE), true);
    if (!is_array($raw) || !isset($raw['groups']) || !is_array($raw['groups'])) {
        return ['data' => null, 'error' => 'chunks.json hỏng: ' . json_last_error_msg()];
    }

    $total = 0;
    foreach ($raw['groups'] as &$group) {
        $group['levels'] = chunks_levels((string) ($group['level'] ?? ''));
        foreach ($group['sections'] as $i => &$section) {
            $section['n']      = $i + 1;
            $section['levels'] = chunks_levels((string) ($section['level'] ?? ''));
            $total += count($section['chunks']);
        }
        unset($section);
    }
    unset($group);

    return ['data' => ['groups' => $raw['groups'], 'total' => $total], 'error' => null];
}

function chunks_levels(string $level): array
{
    preg_match_all('/[ABC][12]/', $level, $m);
    return array_values(array_unique($m[0]));
}
