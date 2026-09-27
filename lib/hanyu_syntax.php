<?php

declare(strict_types=1);

/**
 * Phân tích cú pháp câu tiếng Trung trình độ HSK1 cho www/zh-svo, bằng luật.
 *
 * Không có thư viện NLP tiếng Trung nào trên máy, nên đây là một bộ luật nhỏ
 * viết riêng cho câu ngắn của giáo trình, theo ngữ pháp dạy học quen thuộc
 * (主语 · 谓语 · 宾语 · 补语 · 定语 · 状语):
 *
 *   1. Từ loại: bộ từ chức năng dựng sẵn trong hanyu.php, gợi ý trong nghĩa của
 *      bảng từ mới ("đgt.", "tt.", "phó từ"…), còn lại bỏ phiếu theo ngữ cảnh
 *      trong cả kho câu (sau 很 là tính từ, sau 会/能 là động từ, sau lượng từ
 *      là danh từ…), cuối cùng đoán theo chữ cuối (…子, …人, …师 là danh từ).
 *   2. Mỗi vế câu (tách ở ，。！？；) có một vị ngữ trung tâm: động từ đầu tiên
 *      không nằm trong cụm giới từ; động từ năng nguyện nhường chỗ cho động từ
 *      theo sau nó; không có động từ thì lấy tính từ, rồi danh từ (今天星期一).
 *   3. Trước vị ngữ: chủ ngữ, và trạng ngữ — thời gian, phó từ, cụm giới từ —
 *      vì tiếng Trung đặt trạng ngữ TRƯỚC động từ. Sau vị ngữ: tân ngữ, bổ ngữ
 *      (得 + tính từ, kết quả, xu hướng, số lượng), động từ nối tiếp, trợ từ.
 *   4. Trong cụm danh từ: từ cuối là trung tâm; chỉ thị, số, lượng từ là hạn
 *      định; phần còn lại là định ngữ, 的 gắn vào định ngữ của nó.
 *
 * Kết quả mỗi câu là danh sách token {w, py, pos, func, head} theo đúng định
 * dạng graph.js của en-svo đọc, với bộ nhãn tiếng Trung khai ở zh-svo/graph.js.
 * Luật đúng với phần lớn câu HSK1; câu dài nhiều mệnh đề thì chỉ là gần đúng.
 */

require_once __DIR__ . '/hanyu.php';

const HS_NP   = ['NOUN', 'PRON', 'PROPN', 'NUM', 'CLF', 'TIME'];
const HS_DEM  = ['这', '那', '哪', '这个', '那个', '哪个', '这些', '那些', '每', '各', '一些', '有些', '整个', '所有', '别的', '任何', '每个', '多大',
                 '这种', '那种', '哪种', '这样的'];
const HS_ASP  = ['了', '过', '着'];
const HS_SFP  = ['吗', '呢', '吧', '啊', '呀', '嘛', '哦', '啦'];
const HS_DIR  = ['来', '去', '下', '回来', '回去', '上来', '下来', '起来', '出来', '进来', '过来', '出去', '进去', '上去', '下去', '过去'];
// Bổ ngữ xu hướng một chữ chỉ theo sau động từ một chữ: 关上, 吹开, 举起, 逃走;
// sau động từ hai chữ thì là động từ thứ hai (我决定走).
const HS_DIR1 = ['上', '起', '出', '进', '回', '开', '走'];
const HS_RES  = ['懂', '完', '到', '好', '见', '错', '清楚', '干净', '住', '会', '成', '干', '掉', '满', '光'];
// Sau các động từ này, động từ kế tiếp là việc được mời / sai làm, không phải bổ
// ngữ xu hướng của chúng: 请进, 让开 không bổ sung cho 请, 让.
const HS_CAUS = ['请', '让', '叫', '使'];
// 把 / 被 dẫn tân ngữ lên trước động từ chính; cụm của chúng luôn là trạng ngữ
// cho động từ phía sau, kể cả khi đứng sau một động từ khác (请把门关上).
const HS_BA   = ['把', '被', '将'];
const HS_NUM_CH = '一二两三四五六七八九十几';   // chữ mở đầu một cụm số lượng
const HS_TUNIT = ['点', '点钟', '号', '星期', '月', '年', '天', '周', '分钟', '小时', '秒', '会儿'];   // cụm số + đơn vị này là thời gian
const HS_PERS = ['我', '你', '您', '他', '她', '它', '我们', '你们', '他们', '她们', '咱们', '大家'];   // đại từ nhân xưng
// Giới từ đứng sau động từ làm bổ ngữ: 住在北京, 送给他, 走到门口, 飞往上海.
const HS_POSTP = ['在', '给', '到', '往', '向', '自', '于'];
const HS_QTY  = ['一下', '一点', '一点儿', '一会儿', '一下儿'];
const HS_DEG  = ['很', '太', '非常', '特别', '真', '最', '更', '挺', '比较', '有点儿', '有点', '十分', '越来越', '多么', '这么', '那么'];
const HS_DITR = ['给', '教', '问', '告诉', '送', '借', '还', '找', '请'];
const HS_CLV  = ['觉得', '认为', '希望', '知道', '听说', '说', '想', '以为', '发现', '相信', '看见', '记得', '忘了', '担心', '怕', '觉', '肯定', '同意',
                 '发誓', '保证', '承认', '感觉', '猜'];
const HS_PREP_VERB = ['在', '给', '到'];       // không có động từ nào sau thì chính là động từ
const HS_HOW  = ['怎么', '如何', '怎样', '这么', '那么', '这样', '那样'];   // đại từ đứng trước động từ làm trạng ngữ
const HS_SUB  = ['如果', '要是', '因为', '虽然', '除非', '只要', '即使', '既然', '尽管', '当',
                 '不管', '无论', '不论', '哪怕', '就算', '由于'];   // mở đầu vế phụ
const HS_COORD = ['和', '跟', '或者', '还是', '与', '以及'];   // nối hai danh từ trong một cụm
const HS_WHEN = ['后', '以后', '之后', '前', '以前', '之前', '时候', '时'];   // 下班后, 吃饭的时候: cả cụm là trạng ngữ thời gian
const HS_AUXW = ['会', '能', '想', '要', '可以', '应该', '愿意', '能够', '必须', '不能', '还要', '可能'];

// ------------------------------------------------------------------ từ loại

/** Gợi ý từ loại trong cột nghĩa của bảng từ mới: "đgt.", "(tt.)", "phó từ"… */
function hs_pos_hint(string $vi): ?string
{
    $v = mb_strtolower($vi);
    $map = [
        '/năng nguyện/u'                      => 'AUX',
        '/lượng từ/u'                          => 'CLF',
        '/^\(?\s*(đgt|đg|động từ)\b/u'        => 'VERB',
        '/^\(?\s*(tt|tính từ)\b/u'            => 'ADJ',
        '/^\(?\s*(đt|đại từ)\b/u'             => 'PRON',
        '/^\(?\s*(dt|danh từ)\b/u'            => 'NOUN',
        '/^\(?\s*(phó|pht|phó từ)\b/u'        => 'ADV',
        '/^\(?\s*(st|số từ)\b/u'              => 'NUM',
        '/^\(?\s*(trợ từ|tr)\b/u'             => 'PART',
        '/^\(?\s*(giới từ|gt)\b/u'            => 'ADP',
        '/^\(?\s*(liên từ|lt)\b/u'            => 'CCONJ',
        '/^\(?\s*(thán từ)\b/u'               => 'INTJ',
        '/^\(?\s*(danh từ riêng|dtr)\b/u'     => 'PROPN',
    ];
    foreach ($map as $re => $pos) {
        if (preg_match($re, $v)) {
            return $pos;
        }
    }
    return null;
}

/**
 * Từ loại gán tay cho từ vựng của giáo trình (500 từ thông dụng, 50 động từ,
 * từ mới 15 bài) — danh sách 500 từ không ghi từ loại, và phần lớn lỗi phân
 * tích bắt nguồn từ đó. Ưu tiên cao nhất: nhóm "50 động từ" xếp 累, 忙 vào động
 * từ, nhưng trong câu chúng chạy như tính từ (我很累).
 * Từ mới thêm vào kho mà chưa có ở đây thì rơi xuống gợi ý nghĩa và bỏ phiếu.
 */
function hs_pos_override(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    $lists = [
        'NOUN'  => '丈夫 上帝 上面 下面 世界 主意 之间 事儿 事实 事情 人们 人类 任务 伙计 信息 个人 家伙 兄弟 凶手 先生 儿子 '
                 . '全部 公司 分钟 博士 原因 名字 咖啡 哥哥 问题 国家 地方 报告 外面 大学 太太 夫人 女人 女儿 女士 女孩 妻子 '
                 . '姑娘 婚礼 妈妈 孩子 学校 家庭 家里 宝贝 小姐 小子 小孩 小时 屁股 尸体 弟弟 律师 情况 想法 意思 意义 房子 '
                 . '房间 手机 手术 政府 故事 新闻 方式 方法 时间 朋友 未来 东西 案子 样子 机会 武器 死亡 母亲 比赛 法官 消息 '
                 . '混蛋 照片 父母 父亲 爸爸 玩笑 现场 理由 生命 生意 生日 生活 男人 男孩 病人 白痴 监狱 目标 眼睛 礼物 秘密 '
                 . '节目 精神 系统 约会 组织 结果 经历 总统 美元 老兄 老师 声音 能力 自由 兴趣 行动 行为 衣服 里面 计划 证据 '
                 . '警察 身上 身边 身体 办法 游戏 选手 部分 医生 医院 错误 钥匙 长官 关系 电影 电视 电话 音乐 头发 飞机 学生 '
                 . '国 同学 菜 汉字 字 书 茶 米饭 商店 杯子 钱 猫 狗 椅子 桌子 电脑 前 天气 雨 水果 水 苹果 车 后 饭店 出租车 风景',
        'PROPN' => '李月 王方 谢朋 大卫 纽约 美国',
        'VERB'  => '下来 下去 了解 介意 代表 以为 来自 保持 保证 保护 信任 做到 伤害 出来 出去 出现 加入 加油 原谅 参加 同意 '
                 . '告诉 喜欢 回来 回到 回去 回家 回答 坚持 失去 存在 安排 完成 害怕 小心 工作 希望 带来 帮助 帮忙 建议 得到 '
                 . '忘记 想像 想到 想想 感到 感觉 感谢 成功 成为 打算 打开 找到 承认 抓住 投票 拜托 接受 控制 撒谎 拥有 担心 '
                 . '支持 收到 改变 放弃 放松 明白 有关 检查 欢迎 决定 治疗 注意 准备 照顾 犯罪 理解 留下 发现 发生 发誓 相信 '
                 . '看到 看看 看见 睡觉 知道 确定 等等 结婚 结束 继续 考虑 联系 听到 听说 处理 表演 表现 要求 见到 觉得 解决 '
                 . '解释 讨厌 记住 记得 记录 试试 认为 说话 调查 谈谈 谋杀 证明 变成 负责 起来 跳舞 进来 进入 进去 进行 遇到 '
                 . '过来 道歉 选择 还有 开始 开枪 关心 阻止 离开 需要 说 讲 问 听 看 读 写 吃 喝 做 去 来 回 走 坐 起床 学习 '
                 . '上班 下班 上课 下课 考试 练习 教 学 爱 让 找 买 卖 用 吃饭 住 下雨 打电话 开',
        'ADJ'   => '高兴 晚 一样 不同 不好 不行 不错 冷静 努力 危险 可爱 可怜 唯一 奇怪 安全 完美 容易 年轻 幸运 很多 必要 快乐 抱歉 '
                 . '整个 正常 清楚 漂亮 生气 痛苦 疯狂 直接 真正 简单 糟糕 紧张 聪明 亲爱 该死 重要 开心 随便 麻烦 累 忙 好吃 热 冷 不少',
        'ADV'   => '一下 一直 不再 不用 不要 也许 其实 到底 到处 刚刚 另外 只是 只有 可能 大概 好像 好好 如此 完全 实在 就是 '
                 . '很快 从来 从没 或许 是否 有点 本来 根本 比较 永远 无法 然后 甚至 当然 的确 看来 真是 真的 确实 突然 简直 '
                 . '终于 绝对 肯定 至少 这么 那么 重新 难道 显然 首先 马上 也是 太…了 一共 为什么',
        'PRON'  => '一切 任何 其中 其他 别人 别的 各位 多久 如何 它们 干吗 怎样 所有 有些 有人 每个 这些 这样 这种 这边 那些 那样 那种 那边',
        'NUM'   => '多少 一些 一个 一点 一点儿 第一 第二',
        'CLF'   => '些',
        'TIME'  => '之前 之后 今晚 昨晚 最后 最近 每天 当时 这次 那天 那时 过去 星期一 星期二 星期三 星期四 星期五 星期六 星期日 星期天',
        'AUX'   => '不能 必须 能够 愿意 还要',
        'ADP'   => '作为 对于 为了 直到 通过 关于 除了',
        'CCONJ' => '不过 并且 以及 否则',
        'SCONJ' => '不管 即使 只要 除非',
        'PART'  => '来说 极了 而已',
        'INTJ'  => '是的 晚安',
    ];
    $map = [];
    foreach ($lists as $pos => $words) {
        foreach (preg_split('/\s+/u', $words, -1, PREG_SPLIT_NO_EMPTY) as $w) {
            $map[$w] = $pos;
        }
    }
    return $map;
}

/** Từ loại cho mọi từ trong kho: gán tay, từ điển, gợi ý nghĩa, rồi bỏ phiếu theo ngữ cảnh. */
function hs_pos_table(array $dict, array $sents): array
{
    $over = hs_pos_override();
    $pos  = [];
    foreach ($dict as $w => $e) {
        $pos[$w] = $over[$w] ?? $e['pos'] ?? hs_pos_hint((string) ($e['vi'] ?? '')) ?? null;
    }
    foreach ($over as $w => $p) {
        $pos[$w] ??= $p;
    }
    $votes = [];
    $vote  = function (string $w, string $p, int $n = 1) use (&$votes): void {
        $votes[$w][$p] = ($votes[$w][$p] ?? 0) + $n;
    };
    foreach ($sents as $s) {
        $t = $s['t'];
        $n = count($t);
        for ($i = 0; $i < $n; $i++) {
            if ($t[$i][3] !== 'h') {
                continue;
            }
            $w    = $t[$i][0];
            $prev = $i > 0 && $t[$i - 1][3] === 'h' ? $t[$i - 1][0] : '';
            $next = $i + 1 < $n && $t[$i + 1][3] === 'h' ? $t[$i + 1][0] : '';
            if (in_array($prev, HS_DEG, true)) {
                $vote($w, 'ADJ', 3);
            }
            if (in_array($prev, ['会', '能', '想', '要', '可以', '应该', '别', '不要', '不想', '不会', '得', '去', '来', '一起'], true)) {
                $vote($w, 'VERB', 2);
            }
            if (in_array($next, HS_ASP, true) || in_array($next, ['得'], true)) {
                $vote($w, 'VERB', 1);
            }
            if (in_array($prev, ['个', '本', '位', '口', '件', '杯', '只', '张', '条', '种', '的', '这', '那', '这个', '那个', '一些', '每', '些', '家'], true)) {
                $vote($w, 'NOUN', 2);
            }
            if (in_array($prev, ['在', '从', '跟', '给', '对', '往', '离', '把', '被', '比'], true)) {
                $vote($w, 'NOUN', 1);
            }
            if ($next === '的' && $prev === '') {
                $vote($w, 'NOUN', 1);
            }
        }
    }
    foreach ($votes as $w => $vs) {
        if (!empty($pos[$w])) {
            continue;
        }
        arsort($vs);
        $pos[$w] = array_key_first($vs);
    }
    foreach ($pos as $w => $p) {
        $pos[$w] = $p ?? hs_guess($w);
    }
    return $pos;
}

/** Không có manh mối nào: đoán theo chữ cuối. */
function hs_guess(string $w): string
{
    $last = mb_substr($w, -1);
    if (preg_match('/^[0-9零一二三四五六七八九十百千万两]+$/u', $w)) {
        return 'NUM';
    }
    if ($last === '们') {
        return 'NOUN';
    }
    if (mb_strpos('子们人师生员家店院馆车机话室场园厂楼钱书饭菜水茶国市区路门天年月周期上下里边面', $last) !== false) {
        return 'NOUN';
    }
    return 'NOUN';
}

// ------------------------------------------------------------------ phân tích

/**
 * Câu (token của hanyu.php) → token đồ thị {w, py, pos, func, head}, bỏ dấu câu.
 * Vế sau gắn vào vị ngữ của vế đầu bằng nhãn conj.
 */
function hs_parse(array $toks, array $POS, array $dict): array
{
    $W       = [];
    $clauses = [];
    $cur     = [];
    $enum    = false;   // vừa gặp dấu 、: token sau là một vế liệt kê mới
    $enumAt  = [];
    foreach ($toks as $t) {
        if ($t[3] === 'p') {
            if (preg_match('/[，,。！!？?；;：:]/u', $t[0]) && $cur) {
                $clauses[] = $cur;
                $cur = [];
            }
            $enum = $t[0] === '、';
            continue;
        }
        if ($enum) {
            $enumAt[count($W)] = true;
            $enum = false;
        }
        $cur[] = count($W);
        $W[] = [
            'w'    => $t[0],
            'py'   => str_replace(' ', '', $t[1]),
            'pos'  => $t[3] === 'x' ? 'NUM' : ($POS[$t[0]] ?? hs_guess($t[0])),
            'vi'   => (string) ($dict[$t[0]]['vi'] ?? ''),
            'func' => '',
            'head' => -1,
        ];
    }
    if ($cur) {
        $clauses[] = $cur;
    }
    foreach ($enumAt as $i => $_) {
        $W[$i]['enum'] = true;   // chỉ dùng trong lúc phân tích, xóa trước khi trả về
    }
    // Vế chỉ là cụm giới từ hay từ chỉ thời gian (对我来说，… / 明天，…) làm trạng
    // ngữ cho vế có vị ngữ đứng sau nó; các vế có vị ngữ khác nối vào vế chính.
    $roots = [];
    $cix   = [];   // các token của từng vế
    $kind  = [];   // theo vế: '' thường, 'topic' chủ đề tách dấu phẩy, 'voc' lời gọi
    foreach ($clauses as $ix) {
        $r = hs_clause($W, $ix);
        if ($r !== null) {
            $onlyTime = true;
            foreach ($ix as $i) {
                $onlyTime = $onlyTime && in_array($W[$i]['pos'], ['TIME', 'ADV'], true);
            }
            $roots[] = [$r, $W[$r]['pos'] === 'ADP' || $onlyTime || in_array($W[$ix[0]]['w'], HS_SUB, true)];
            $kind[]  = hs_nominal_kind($W, $ix, $r);
            $cix[]   = $ix;
        }
    }
    // Vế chỉ là một cụm danh từ đứng cạnh vế có vị ngữ: 这么多菜，我们吃得完吗
    // (chủ đề), 老兄，好久不见 / 谢谢你，医生 (lời gọi). Chỉ tính khi câu còn vế
    // khác không phải loại này.
    $verbal = [];
    foreach ($roots as $k => [$r]) {
        if ($kind[$k] === '') {
            $verbal[] = $k;
        }
    }
    if (!$verbal) {
        $kind = array_fill(0, count($roots), '');
    }
    // Vế chính: vế thường đầu tiên có vị ngữ động từ / tính từ; không có thì vế
    // thường đầu tiên (他呢？他是你同学吗: vế chính là vế sau).
    $main = null;
    foreach ([true, false] as $needVerb) {
        foreach ($roots as $k => [$r, $adv]) {
            if ($main === null && !$adv && $kind[$k] === ''
                && (!$needVerb || !in_array($W[$r]['pos'], ['NOUN', 'PRON', 'PROPN', 'NUM', 'CLF'], true))) {
                $main = $r;
            }
        }
    }
    $main ??= $roots[0][0] ?? null;
    foreach ($roots as $k => [$r, $adv]) {
        if ($kind[$k] !== '') {
            // chủ đề gắn vào vế có vị ngữ ngay sau nó (hay trước, nếu nó đứng cuối)
            $to = $main;
            foreach ($verbal as $v) {
                if ($v > $k) {
                    $to = $roots[$v][0];
                    break;
                }
            }
            // cả vế là một cụm danh từ: dựng lại cho đúng (李 là định ngữ của 小姐,
            // 这么多 bổ nghĩa cho 菜), rồi gắn đầu cụm vào vế đích
            hs_np($W, $cix[$k], 0, count($cix[$k]), -1, $kind[$k]);
            $h = $cix[$k][hs_np_head($W, $cix[$k], 0, count($cix[$k]))];
            $W[$h]['func'] = $kind[$k];
            $W[$h]['head'] = $kind[$k] === 'voc' ? $main : $to;
            continue;
        }
        if ($r === $main) {
            $W[$r]['func'] = 'pred';
            $W[$r]['head'] = -1;
            continue;
        }
        $next = $main;
        for ($j = $k + 1; $j < count($roots); $j++) {
            if (!$roots[$j][1]) {
                $next = $roots[$j][0];
                break;
            }
        }
        // vế thường khác: bỏ qua các vế chủ đề / lời gọi khi tìm vế đích
        for ($j = $k + 1; $adv && $j < count($roots); $j++) {
            if (!$roots[$j][1] && $kind[$j] === '') {
                $next = $roots[$j][0];
                break;
            }
        }
        $W[$r]['func'] = $adv ? 'adv' : 'conj';
        $W[$r]['head'] = $adv ? $next : $main;
    }
    foreach ($W as &$w) {
        $w['func'] = $w['func'] ?: 'adv';
        unset($w['enum']);
    }
    unset($w);
    return $W;
}

/** Một vế câu: gán nhãn tại chỗ trong $W, trả về chỉ số vị ngữ trung tâm. */
function hs_clause(array &$W, array $ix): ?int
{
    $n = count($ix);
    if ($n === 0) {
        return null;
    }
    // $P đọc qua tham chiếu: vòng dưới sửa từ loại tại chỗ (会 → AUX, 在 → VERB).
    $P   = function (int $k) use (&$W, $ix): string {
        return $W[$ix[$k]]['pos'];
    };
    $T   = fn(int $k) => $W[$ix[$k]]['w'];
    $set = function (int $k, string $func, int $headK) use (&$W, $ix): void {
        $W[$ix[$k]]['func'] = $func;
        $W[$ix[$k]]['head'] = $headK < 0 ? -1 : $ix[$headK];
    };

    // Từ nối đầu vế: 因为, 所以, 但是, 如果… và thán từ 喂, 啊.
    $start = 0;
    $marks = [];
    while ($start < $n && (in_array($P($start), ['SCONJ', 'CCONJ', 'INTJ'], true))) {
        $marks[] = $start++;
    }
    if ($start >= $n) {
        foreach ($marks as $m) {
            $set($m, 'mark', -1);
        }
        return $ix[$marks[0]];
    }

    // Vế chỉ là một danh sách danh từ (爸爸、妈妈和我): cả vế là một cụm ngang hàng.
    $list = false;
    $allN = true;
    for ($k = $start; $k < $n; $k++) {
        $allN = $allN && (in_array($P($k), ['NOUN', 'PRON', 'PROPN'], true) || in_array($T($k), HS_COORD, true));
        $list = $list || in_array($T($k), HS_COORD, true) || !empty($W[$ix[$k]]['enum']);
    }
    if ($allN && $list && $n - $start > 1) {
        hs_np($W, $ix, $start, $n, -1, 'pred');
        foreach ($marks as $m) {
            $set($m, 'mark', hs_np_head($W, $ix, $start, $n));
        }
        foreach (array_slice($ix, $start) as $i) {
            if ($W[$i]['head'] === -1 && $W[$i]['func'] === 'pred') {
                return $i;
            }
        }
    }

    // Cấu trúc chữ 的 làm chủ ngữ: 他说的是事实 = [他说的] 是 [事实]. Phần trước
    // 的 là một vế riêng, cả cụm làm chủ ngữ cho 是.
    for ($k = $start + 1; $k + 1 < $n; $k++) {
        if ($T($k) === '的' && $T($k + 1) === '是') {
            $verb = false;
            for ($j = $start; $j < $k; $j++) {
                $verb = $verb || $P($j) === 'VERB';
            }
            if (!$verb) {
                break;
            }
            $r1 = hs_clause($W, array_slice($ix, $start, $k - $start));
            $r2 = hs_clause($W, array_slice($ix, $k + 1));
            $W[$r1]['func'] = 'subj';
            $W[$r1]['head'] = $r2;
            $W[$r2]['func'] = 'pred';
            $W[$r2]['head'] = -1;
            $set($k, 'de', array_search($r1, $ix, true));
            foreach ($marks as $m) {
                $set($m, 'mark', $k + 1);
            }
            return $r2;
        }
    }

    // 下班后我们一起吃饭 / 吃饭的时候…: một vế động từ + 后/时候 là trạng ngữ thời
    // gian cho phần sau. 后, 时候 là trung tâm; vế trước làm định ngữ của nó.
    for ($k = $start + 1; $k + 1 < $n; $k++) {
        if (!in_array($T($k), HS_WHEN, true) || !hs_any_pred($P, $T, $k + 1, $n)) {
            continue;
        }
        $end = $T($k - 1) === '的' ? $k - 1 : $k;
        $verb = false;
        for ($j = $start; $j < $end; $j++) {
            $verb = $verb || $P($j) === 'VERB';
        }
        if (!$verb || $end <= $start) {
            break;
        }
        $r1 = hs_clause($W, array_slice($ix, $start, $end - $start));
        $r2 = hs_clause($W, array_slice($ix, $k + 1));
        $W[$r1]['func'] = 'attr';
        $W[$r1]['head'] = $ix[$k];
        if ($end < $k) {
            $set($end, 'de', array_search($r1, $ix, true));
        }
        $set($k, 'adv', array_search($r2, $ix, true));
        foreach ($marks as $m) {
            $set($m, 'mark', array_search($r2, $ix, true));
        }
        return $r2;
    }

    // 他说的话我不相信: [chủ ngữ + động từ] 的 [danh từ] ở đầu vế mà phía sau
    // còn vị ngữ khác thì cả cụm là danh từ có định ngữ, đứng đầu phần sau.
    for ($k = $start; $k + 2 < $n; $k++) {
        if ($P($k) !== 'VERB') {
            continue;
        }
        if ($T($k) === '是' || $T($k + 1) !== '的' || !hs_np_like($P($k + 2), $T($k + 2)) || $T($k + 2) === '的'
            || $k > $start && $P($k - 1) === 'ADP') {
            break;   // chỉ xét động từ đầu tiên của vế; sau giới từ thì là tân ngữ của nó
        }
        // cụm danh từ sau 的 dừng trước đại từ (他说的话 | 我不相信)
        $j = $k + 2;
        while ($j < $n && hs_np_like($P($j), $T($j)) && !($j > $k + 2 && in_array($P($j), ['PRON', 'PROPN'], true))) {
            $j++;
        }
        $pred = false;
        for ($a = $j; $a < $n; $a++) {
            $pred = $pred || in_array($P($a), ['VERB', 'ADJ', 'AUX'], true);
        }
        if (!$pred) {
            break;
        }
        $head = $ix[hs_np_head($W, $ix, $k + 2, $j)];
        $r1 = hs_clause($W, array_slice($ix, $start, $k + 1 - $start));
        $r2 = hs_clause($W, array_slice($ix, $k + 2));
        $W[$r1]['func'] = 'attr';
        $W[$r1]['head'] = $head;
        $set($k + 1, 'de', array_search($r1, $ix, true));
        foreach ($marks as $m) {
            $set($m, 'mark', array_search($r2, $ix, true));
        }
        return $r2;
    }

    // 学汉语要花很多时间, 玩手机会影响学习: vế mở đầu bằng động từ (không có chủ
    // ngữ) mà sau đó còn "trợ động từ + động từ" thì cụm động từ đầu là chủ ngữ.
    if ($P($start) === 'VERB' && !in_array($T($start), array_merge(HS_CLV, HS_CAUS, ['是', '有', '在']), true)) {
        for ($a = $start + 1; $a + 1 < $n; $a++) {
            $neg = in_array($T($a), ['不要', '别'], true);
            if ((in_array($T($a), HS_AUXW, true) || $neg) && in_array($P($a + 1), ['VERB', 'ADJ'], true)) {
                $noV = true;
                for ($j = $start + 1; $j < $a; $j++) {
                    $noV = $noV && !in_array($P($j), ['VERB', 'AUX'], true);
                }
                if (!$noV) {
                    break;
                }
                $r1 = hs_clause($W, array_slice($ix, $start, $a - $start));
                $r2 = hs_clause($W, array_slice($ix, $a));
                $W[$r1]['func'] = $neg ? 'adv' : 'subj';
                $W[$r1]['head'] = $r2;
                foreach ($marks as $m) {
                    $set($m, 'mark', array_search($r2, $ix, true));
                }
                return $r2;
            }
        }
    }

    // Động từ năng nguyện (会 能 想 要 可以…) theo sau là động từ thì làm trợ động
    // từ; không thì chính nó là động từ (我要这个, 我想你). Quyết theo vị trí chứ
    // không theo từ điển, và ghi luôn từ loại đúng vào token.
    for ($k = $start; $k < $n; $k++) {
        if (in_array($T($k), HS_AUXW, true) || $P($k) === 'AUX') {
            $j = $k + 1;
            while ($j < $n && ($P($j) === 'ADV' || in_array($T($j), HS_AUXW, true) || in_array($T($j), HS_HOW, true))) {
                $j++;
            }
            $W[$ix[$k]]['pos'] = $j < $n && in_array($P($j), ['VERB', 'ADJ', 'ADP'], true) ? 'AUX' : 'VERB';
        }
    }
    // 没(有) trước động từ, tính từ là phó từ phủ định (我没去); trước danh từ là
    // động từ "không có" (我没有时间). Động từ đứng sau 的 hay sau chỉ thị là danh
    // từ: 我的想像, 别的选择 (trừ 是: 他说的是事实).
    for ($k = $start; $k < $n; $k++) {
        $w = $T($k);
        if ($w === '没有' || $w === '没') {
            $j = $k + 1;
            while ($j < $n && $P($j) === 'ADV') {
                $j++;
            }
            $W[$ix[$k]]['pos'] = $j < $n && in_array($P($j), ['VERB', 'ADJ', 'AUX', 'ADP'], true) ? 'ADV' : 'VERB';
        }
        // Có bổ ngữ hay trợ từ động thái theo sau thì vẫn là động từ: 把旧的修好.
        elseif ($P($k) === 'VERB' && $k > $start && ($T($k - 1) === '的' || in_array($T($k - 1), HS_DEM, true))
                && $w !== '是' && !in_array($w, HS_AUXW, true)
                && !($k + 1 < $n && (hs_np_like($P($k + 1), $T($k + 1))
                     || in_array($T($k + 1), HS_RES, true) || in_array($T($k + 1), HS_DIR, true) || in_array($T($k + 1), HS_ASP, true)))) {
            $W[$ix[$k]]['pos'] = 'NOUN';
        }
    }

    // 在 / 给 / 到 mà phía sau không còn động từ nào thì cũng là động từ (我在家).
    $isV = [];
    for ($k = $start; $k < $n; $k++) {
        $isV[$k] = $P($k) === 'VERB';
    }
    for ($k = $start; $k < $n; $k++) {
        // Đứng ngay sau động từ thì vẫn là giới từ, làm bổ ngữ: 坐在我后面, 送给他.
        if ($P($k) === 'ADP' && in_array($T($k), HS_PREP_VERB, true) && !($k > $start && $P($k - 1) === 'VERB')) {
            $later = false;
            for ($j = $k + 1; $j < $n; $j++) {
                if ($isV[$j] ?? false) {
                    $later = true;
                    break;
                }
            }
            if (!$later) {
                $isV[$k] = true;
                $W[$ix[$k]]['pos'] = 'VERB';
            }
        }
    }

    // Vài từ đổi từ loại theo vị trí:
    //   下大雨, 下功夫: 下 ở đầu vế hay sau phó từ, trước danh từ là động từ;
    //   以真实的故事为基础: 为 sau cụm 以 là động từ "làm", không phải giới từ;
    //   我肯定他会来: 肯定 trước một đại từ còn động từ theo sau là "chắc chắn rằng".
    $seenYi = false;
    for ($k = $start; $k < $n; $k++) {
        $w = $T($k);
        $seenYi = $seenYi || $w === '以';
        $toVerb = false;
        if ($w === '下' && $k + 1 < $n && $P($k + 1) === 'NOUN'
            && ($k === $start || in_array($P($k - 1), ['ADV', 'AUX', 'SCONJ', 'CCONJ'], true))) {
            $toVerb = true;
        }
        if ($w === '为' && $seenYi) {
            $toVerb = true;
            for ($j = $k - 1; $j >= $start && $T($j) !== '以'; $j--) {
                $isV[$j] = false;
                if ($P($j) === 'VERB') {
                    $W[$ix[$j]]['pos'] = 'NOUN';
                }
            }
        }
        // 是对的, 你说得对, 对了: 对 mà sau nó không có cụm danh từ là tính từ "đúng"
        if ($w === '对' && $P($k) === 'ADP') {
            $np = false;
            for ($j = $k + 1; $j < $n; $j++) {
                $np = $np || $T($j) !== '的' && hs_np_like($P($j), $T($j));
            }
            if (!$np) {
                $W[$ix[$k]]['pos'] = 'ADJ';
            }
        }
        // 我通过了考试: giới từ mà có 了 / 过 theo ngay sau là động từ
        if ($P($k) === 'ADP' && $k + 1 < $n && in_array($T($k + 1), HS_ASP, true)) {
            $toVerb = true;
        }
        // 天快黑了, 快来吃吧: 快 ngay trước động từ / tính từ là phó từ "sắp, mau"
        if ($w === '快' && $k + 1 < $n && in_array($P($k + 1), ['VERB', 'ADJ'], true)) {
            $isV[$k] = false;
            $W[$ix[$k]]['pos'] = 'ADV';
        }
        // 他病了: danh từ đứng ngay sau chủ ngữ đại từ và trước 了 là động từ
        if ($P($k) === 'NOUN' && $k > $start && $k + 1 < $n && $T($k + 1) === '了'
            && ($P($k - 1) === 'PROPN' || in_array($T($k - 1), HS_PERS, true))) {
            $toVerb = true;
        }
        if ($w === '肯定' && $k + 1 < $n && in_array($P($k + 1), ['PRON', 'PROPN'], true)) {
            for ($j = $k + 2; $j < $n; $j++) {
                $toVerb = $toVerb || ($isV[$j] ?? false) || $P($j) === 'AUX';
            }
        }
        if ($toVerb) {
            $isV[$k] = true;
            $W[$ix[$k]]['pos'] = 'VERB';
        }
    }

    // 医生给我检查身体, 我给你打电话: 给 + cụm danh từ + động từ thì 给 là giới từ
    // "cho"; còn 给我一杯水 (sau cụm là số lượng) thì 给 vẫn là động từ "đưa".
    // 用手指, 用剪刀剪: 用 cũng vậy, thành giới từ "bằng".
    for ($k = $start; $k + 2 < $n; $k++) {
        $cong = $T($k) === '到' && in_array('从', array_map($T, range($start, $k)), true);   // 从…到…
        if (!(in_array($T($k), ['给', '用'], true) || $cong) || !($isV[$k] ?? false)
            || !in_array($P($k + 1), ['PRON', 'PROPN', 'NOUN', 'TIME', 'NUM'], true)) {
            continue;
        }
        $j = $k + 1;
        while ($j < $n && (in_array($P($j), ['PRON', 'PROPN', 'NOUN'], true) || $cong && hs_np_like($P($j), $T($j)))) {
            $j++;
        }
        while ($j < $n && (in_array($P($j), ['ADV', 'AUX'], true)
                             || $P($j) === 'ADP' && $j + 1 < $n && hs_np_like($P($j + 1), $T($j + 1)))) {
            if ($P($j) === 'ADP') {   // cụm giới từ chen giữa: 用剪刀[把纸]剪开
                $j++;
                while ($j < $n && hs_np_like($P($j), $T($j)) && !($isV[$j] ?? false)) {
                    $j++;
                }
                continue;
            }
            $j++;
        }
        if ($j < $n && ($isV[$j] ?? false)) {
            $isV[$k] = false;
            $W[$ix[$k]]['pos'] = 'ADP';
        }
    }

    // 他不小心踢到了桌子: 不小心 (vô ý) đứng trước một động từ khác là trạng ngữ
    // cách thức, không phải vị ngữ "cẩn thận" bị phủ định.
    for ($k = $start + 1; $k + 1 < $n; $k++) {
        if ($T($k) !== '小心' || $T($k - 1) !== '不') {
            continue;
        }
        for ($j = $k + 1; $j < $n; $j++) {
            if ($isV[$j] ?? false) {
                $isV[$k] = false;
                $W[$ix[$k]]['pos'] = 'ADV';
                break;
            }
        }
    }

    // 把工作做完, 被批评: từ ngay sau 把 / 被 mà phía sau còn động từ khác thì là
    // tân ngữ của 把 (danh từ), không phải vị ngữ, dù từ điển ghi là động từ.
    for ($k = $start; $k + 1 < $n; $k++) {
        if ($P($k) !== 'ADP' || !in_array($T($k), HS_BA, true) || !($isV[$k + 1] ?? false)) {
            continue;
        }
        for ($j = $k + 2; $j < $n; $j++) {
            // 被翻译成: 成 là bổ ngữ của 翻译, không phải động từ thứ hai.
            if (($isV[$j] ?? false) && !in_array($T($j), HS_RES, true) && !in_array($T($j), HS_DIR, true)) {
                $isV[$k + 1] = false;
                $W[$ix[$k + 1]]['pos'] = 'NOUN';
                break;
            }
        }
    }

    // 连睡觉的时间: giới từ + [động từ 的 danh từ], động từ đó chỉ là định ngữ.
    for ($k = $start; $k + 3 < $n; $k++) {
        if ($P($k) === 'ADP' && !($isV[$k] ?? false) && ($isV[$k + 1] ?? false) && $T($k + 2) === '的'
            && hs_np_like($P($k + 3), $T($k + 3))) {
            $isV[$k + 1] = false;
        }
    }

    // Cụm giới từ trước động từ: giới từ + cụm danh từ ngay sau nó.
    $inPP = [];
    for ($k = $start; $k < $n; $k++) {
        if ($P($k) === 'ADP' && !($isV[$k] ?? false)) {
            // Tân ngữ của giới từ có thể là "tính từ + 的" (把旧的修好).
            $j = $k + 1;
            while ($j < $n && !($isV[$j] ?? false)
                   && (hs_np_like($P($j), $T($j)) || in_array($P($j), ['ADJ', 'VERB'], true) && $j + 1 < $n && $T($j + 1) === '的'
                       || $T($j) === '的' && $j > $k + 1)
                   && !($j > $k + 1 && hs_np_break($P($j - 1), $P($j), $j + 1 < $n ? $T($j + 1) : '', $T($j), $T($j - 1)))) {
                $inPP[$j] = $k;
                $j++;
            }
        }
    }

    // 是…的 nhấn mạnh (我是坐飞机来的, 你们是什么时候认识的): 是 chỉ đánh dấu,
    // vị ngữ thật là động từ đứng giữa.
    $skip = [];
    if ($n > 2 && $T($n - 1) === '的') {
        for ($k = $start; $k < $n - 1; $k++) {
            if ($T($k) === '是') {
                for ($j = $k + 1; $j < $n - 1; $j++) {
                    if ($isV[$j] ?? false) {
                        $skip[$k] = true;
                        break 2;
                    }
                }
            }
        }
    }

    // Vị ngữ trung tâm.
    $root = null;
    if (($isV[$start] ?? false) && $start + 1 < $n) {
        $a = $start + 1;
        while ($a < $n && ($P($a) === 'ADV' || in_array($T($a), HS_DEG, true))) {
            $a++;
        }
        if ($a > $start + 1 && $a < $n && $P($a) === 'ADJ' && hs_only_particles($T, $a + 1, $n)) {
            $isV[$start] = false;
            $W[$ix[$start]]['pos'] = 'NOUN';
        }
    }
    // 我忙得连睡觉的时间都没有: tính từ / động từ ngay trước 得 là vị ngữ, dù sau
    // 得 còn động từ khác (đó là bổ ngữ trình độ).
    for ($k = $start + 1; $k + 1 < $n && $root === null; $k++) {
        if ($T($k) === '得' && in_array($P($k - 1), ['ADJ', 'VERB'], true) && !isset($inPP[$k - 1])) {
            $before = false;
            for ($j = $start; $j < $k - 1; $j++) {
                $before = $before || (($isV[$j] ?? false) && !isset($inPP[$j]) && !isset($skip[$j]));
            }
            if (!$before) {
                $root = $k - 1;
            }
        }
    }
    for ($k = $start; $k < $n && $root === null; $k++) {
        if (($isV[$k] ?? false) && !isset($inPP[$k]) && !isset($skip[$k])) {
            $root = $k;
        }
    }
    // 很高兴认识你: tính từ có phó từ mức độ đứng ngay trước động từ mới là vị
    // ngữ, động từ phía sau bổ sung cho nó.
    if ($root !== null && $root - 2 >= $start && $P($root - 1) === 'ADJ' && in_array($T($root - 2), HS_DEG, true)
        && !(in_array($T($root - 1), ['好', '难'], true) && hs_only_particles($T, $root + 1, $n))) {
        $root--;
    }
    if ($root === null) {
        for ($k = $start; $k < $n && $root === null; $k++) {
            $nextW = $k + 1 < $n ? $T($k + 1) : '';
            $nextP = $k + 1 < $n ? $P($k + 1) : '';
            if ($P($k) === 'ADJ' && !isset($inPP[$k]) && $nextW !== '的' && !in_array($nextP, ['NOUN', 'CLF'], true)) {
                $root = $k;
            }
        }
    }
    $nominal = false;
    if ($root === null) {
        for ($k = $n - 1; $k >= $start && $root === null; $k--) {
            if (in_array($P($k), ['NOUN', 'TIME', 'NUM', 'PRON', 'PROPN', 'CLF', 'ADJ', 'VERB', 'AUX'], true) && !isset($inPP[$k])) {
                $root = $k;
            }
        }
        $root ??= $start;
        $nominal = true;
    }

    foreach ($marks as $m) {
        $set($m, 'mark', $root);
    }

    // Vị ngữ danh từ (那个杯子18块钱, 她今年四岁): số, lượng từ, chỉ thị và định
    // ngữ có 的 đứng liền trước thuộc về chính vị ngữ, không phải chủ ngữ.
    $preEnd = $root;
    if ($nominal) {
        while ($preEnd - 1 >= $start) {
            $pw = $T($preEnd - 1);
            $pp = $P($preEnd - 1);
            if (!in_array($pp, ['NUM', 'CLF'], true) && !in_array($pw, HS_DEM, true) && $pw !== '的') {
                break;
            }
            $preEnd--;
            // 我的杯子: gặp 的 thì lấy luôn cụm danh từ sở hữu đứng trước nó
            if ($pw === '的') {
                while ($preEnd - 1 >= $start && in_array($P($preEnd - 1), ['PRON', 'PROPN', 'NOUN'], true)) {
                    $preEnd--;
                }
            }
        }
        if ($preEnd < $root) {
            hs_np($W, $ix, $preEnd, $root + 1, -1, 'pred');
        }
    }

    // ---- trước vị ngữ ----
    $hasSubj = false;
    for ($k = $start; $k < $preEnd;) {
        $p = $P($k);
        $w = $T($k);
        if ($p === 'ADP' && !($isV[$k] ?? false)) {
            $set($k, 'adv', $root);
            $j = $k + 1;
            while ($j < $preEnd && ($inPP[$j] ?? -1) === $k) {
                $j++;
            }
            if ($j > $k + 1) {
                hs_np($W, $ix, $k + 1, $j, $k, 'pobj');
            }
            $k = $j;
            continue;
        }
        if ($p === 'AUX' || ($isV[$k] ?? false) && $k < $root) {
            $set($k, $p === 'AUX' ? 'aux' : 'adv', $root);
            $k++;
            continue;
        }
        // 这么简单的题: phó từ mức độ + tính từ + 的 mở đầu một cụm danh từ, không
        // phải trạng ngữ của vị ngữ.
        if (in_array($w, HS_HOW, true) && $k + 1 < $n && in_array($P($k + 1), ['VERB', 'AUX', 'ADV'], true)) {
            $set($k, 'adv', $root);   // 不知道怎么拒绝她: 怎么 bổ nghĩa cho động từ
            $k++;
            continue;
        }
        $degNp = hs_deg_attr($P, $T, $k, $preEnd);
        if (!$degNp && (in_array($p, ['ADV', 'TIME'], true) || in_array($w, HS_DEG, true))) {
            $set($k, 'adv', $root);
            $k++;
            continue;
        }
        if (in_array($p, ['SCONJ', 'CCONJ', 'INTJ'], true)) {
            $set($k, 'mark', $root);
            $k++;
            continue;
        }
        if ($p === 'PART') {
            $set($k, in_array($w, HS_ASP, true) ? 'asp' : 'de', $k > $start ? $k - 1 : $root);
            $k++;
            continue;
        }
        if (hs_np_like($p, $w) || $p === 'ADJ' || $degNp) {
            // Cụm chủ ngữ dừng trước từ chỉ thời gian (她 | 今年 | 四岁) và không
            // nối hai đại từ (你 | 为什么): chúng là hai thành phần riêng.
            $j = $k;
            while ($j < $preEnd && (hs_np_like($P($j), $T($j)) || $P($j) === 'ADJ' && $j + 1 < $preEnd && ($T($j + 1) === '的' || $P($j + 1) === 'NOUN')
                   || hs_deg_attr($P, $T, $j, $preEnd))) {
                if ($j > $k && hs_np_break($P($j - 1), $P($j), $j + 1 < $n ? $T($j + 1) : '', $T($j), $T($j - 1))) {
                    break;
                }
                $j++;
            }
            if ($j === $k) {
                $set($k, 'adv', $root);   // tính từ đứng trước động từ: 好好学习
                $k++;
                continue;
            }
            // Cụm có trung tâm là 后, 以前… (40分钟后) chỉ thời gian: trạng ngữ, không phải chủ ngữ.
            $hw   = $W[$ix[hs_np_head($W, $ix, $k, $j)]]['w'];
            // 十二点吃饭, 下个星期见: số / chỉ thị + đơn vị thời gian cũng là trạng ngữ
            $when = in_array($hw, HS_WHEN, true) || in_array($hw, HS_TUNIT, true) && $j - $k > 1;
            hs_np($W, $ix, $k, $j, $root, $hasSubj || $when ? 'adv' : 'subj');
            $hasSubj = $hasSubj || !$when;
            $k = $j;
            continue;
        }
        $set($k, 'adv', $root);
        $k++;
    }

    hs_pre_nps($W, $ix, $start, $preEnd, $root);

    // ---- vế chỉ có cụm giới từ: 对我来说, 在北京 (vế sau mới có vị ngữ) ----
    if ($P($root) === 'ADP') {
        $j = $root + 1;
        while ($j < $n && hs_np_like($P($j), $T($j))) {
            $j++;
        }
        if ($j > $root + 1) {
            hs_np($W, $ix, $root + 1, $j, $root, 'pobj');
        }
        for (; $j < $n; $j++) {
            $set($j, $P($j) === 'PART' ? 'de' : 'adv', $root);
        }
        return $ix[$root];
    }

    // ---- sau vị ngữ ----
    $cur  = $root;         // động từ đang mở (vị ngữ, hay động từ nối tiếp gần nhất)
    $objs = 0;
    $iobj = false;
    for ($k = $root + 1; $k < $n;) {
        $p    = $P($k);
        $w    = $T($k);
        $last = $k === $n - 1;

        if (in_array($w, HS_SFP, true) || ($last && ($w === '的' || $w === '了' && $k - 1 !== $cur))) {
            $set($k, $w === '的' ? 'de' : 'sfp', $root);
            $k++;
            continue;
        }
        // 他没来因为他病了, 是因为东西便宜: liên từ giữa vế (không có dấu phẩy) mở
        // một vế mới. Vế sau 是 là tân ngữ của 是; liên từ phụ (因为, 如果) làm
        // trạng ngữ; liên từ khác (所以, 但是) nối ngang hàng.
        if (in_array($p, ['SCONJ', 'CCONJ'], true) && !in_array($w, HS_COORD, true) && $k + 1 < $n
            && hs_any_pred($P, $T, $k + 1, $n)) {
            $sub = hs_clause($W, array_slice($ix, $k));
            if ($sub !== null) {
                $W[$sub]['func'] = $T($cur) === '是' && $k === $cur + 1 ? 'vcomp' : (in_array($w, HS_SUB, true) ? 'adv' : 'conj');
                $W[$sub]['head'] = $ix[$cur];
            }
            break;
        }
        // 认识你我也很高兴, 一喝酒就脸红: sau tân ngữ (hay sau 就 / 才) mà gặp một
        // cụm danh từ có vị ngữ riêng theo sau thì đó là một vế mới.
        if (in_array($p, ['PRON', 'PROPN', 'NOUN'], true) && ($objs > 0 || $k > $cur + 1 && in_array($T($k - 1), ['就', '才', '也', '都'], true))) {
            $j = $k + 1;
            while ($j < $n && hs_np_like($P($j), $T($j))) {
                $j++;
            }
            $a = $j;
            while ($a < $n && ($P($a) === 'ADV' || in_array($T($a), HS_DEG, true) || $P($a) === 'AUX')) {
                $a++;
            }
            if ($a < $n && in_array($P($a), ['ADJ', 'VERB'], true)
                && !(in_array($T($a), HS_DIR, true) && hs_only_particles($T, $a + 1, $n))) {
                $s = $k;
                while ($s - 1 > $cur && $P($s - 1) === 'ADV' && $W[$ix[$s - 1]]['head'] === $ix[$cur]) {
                    $s--;
                }
                $sub = hs_clause($W, array_slice($ix, $s));
                if ($sub !== null) {
                    $W[$sub]['func'] = 'conj';
                    $W[$sub]['head'] = $ix[$cur];
                }
                break;
            }
        }
        if ($w === '的' && $k === $cur + 1) {
            $set($k, 'de', $cur);   // 难的, 新的: tính từ + 的 là "cái …"
            $k++;
            continue;
        }
        if (in_array($w, HS_ASP, true)) {
            $set($k, 'asp', $cur);
            $k++;
            continue;
        }
        if ($w === '得') {
            // 说得很好: 得 nối động từ với bổ ngữ trình độ.
            $set($k, 'de', $cur);
            $j = $k + 1;
            $advs = [];
            while ($j < $n && ($P($j) === 'ADV' || in_array($T($j), HS_DEG, true))) {
                $advs[] = $j++;
            }
            // Sau 得 là cả một vế (连睡觉的时间都没有, 他笑得说不出话): phân tích
            // riêng, vị ngữ của nó làm bổ ngữ.
            $long = $j < $n && $P($j) === 'VERB' && !hs_only_particles($T, $j + 1, $n);   // 感动得流下了眼泪
            for ($a = $j + 1; $a < $n; $a++) {
                $long = $long || in_array($P($a), ['VERB', 'ADJ'], true) && !in_array($T($a), HS_SFP, true);
            }
            if ($long && !($P($j) === 'ADJ' && hs_only_particles($T, $j + 1, $n))) {
                $sub = hs_clause($W, array_slice($ix, $k + 1));
                if ($sub !== null) {
                    $W[$sub]['func'] = 'comp';
                    $W[$sub]['head'] = $ix[$cur];
                }
                break;
            }
            if ($j < $n) {
                $set($j, 'comp', $cur);
                foreach ($advs as $a) {
                    $set($a, 'adv', $j);
                }
                $k = $j + 1;
            }
            else {
                $k = $j;
            }
            continue;
        }
        if (in_array($w, HS_QTY, true) && !($k + 1 < $n && $P($k + 1) === 'NOUN')) {
            $set($k, 'comp', $cur);
            $k++;
            continue;
        }
        if ($k === $cur + 1 && in_array($w, HS_POSTP, true) && $P($cur) === 'VERB' && !in_array($T($cur), HS_CAUS, true)
            && $k + 1 < $n && hs_np_like($P($k + 1), $T($k + 1))) {
            // 扔给我, 走到门口: 给 / 到 dù từ điển ghi là động từ, đứng ngay sau
            // động từ và trước cụm danh từ thì là giới từ làm bổ ngữ. Còn động từ
            // phía sau (忘记给妈妈打电话) thì cụm đó là trạng ngữ của động từ ấy.
            $j = $k + 1;
            while ($j < $n && hs_np_like($P($j), $T($j))) {
                $j++;
            }
            $later = $w === '给' && $j < $n && $P($j) === 'VERB' && !in_array($T($j), HS_DIR, true);
            $set($k, $later ? 'adv' : 'comp', $later ? $j : $cur);
            hs_np($W, $ix, $k + 1, $j, $k, 'pobj');
            $k = $j;
            continue;
        }
        if ($k === $cur + 2 && $T($cur + 1) === '了' && in_array($w, HS_DIR, true) && mb_strlen($w) > 1 && $P($cur) === 'VERB') {
            $set($k, 'comp', $cur);   // 跳了起来, 走了进来
            $k++;
            continue;
        }
        if ($k === $cur + 1 && $P($cur) === 'VERB' && $objs === 0 && !in_array($T($cur), HS_CAUS, true)
            && (in_array($w, HS_RES, true) || in_array($w, HS_DIR, true)
                || in_array($w, HS_DIR1, true) && mb_strlen($T($cur)) === 1)) {
            $set($k, 'comp', $cur);   // 听懂, 看见, 走进来
            $k++;
            continue;
        }
        if ($p === 'ADJ' && $objs > 0 && $k + 1 < $n && $P($k + 1) === 'VERB') {
            $set($k, 'adv', $k + 1);   // 让他多休息, 建议我多喝水: 多 bổ nghĩa cho động từ sau
            $k++;
            continue;
        }
        if ($w === '多' && $k + 1 < $n && $P($k + 1) === 'ADJ') {
            $set($k, 'adv', $k + 1);   // 有多远, 多大: "bao nhiêu" bổ nghĩa cho tính từ sau
            $k++;
            continue;
        }
        if ($k === $cur + 1 && $p === 'ADJ' && $P($cur) === 'VERB' && $objs === 0
            && !($k + 1 < $n && ($T($k + 1) === '的' || in_array($P($k + 1), ['NOUN', 'PROPN'], true)))) {
            $set($k, 'comp', $cur);   // 说慢, 来晚, 做好, 洗干净 (không phải 买新衣服)
            $k++;
            continue;
        }
        if (in_array($w, ['来', '去'], true) && $objs > 0 && $k === $n - 1) {
            $set($k, 'comp', $cur);   // 带一本书来
            $k++;
            continue;
        }
        if ($p === 'ADP' && !($isV[$k] ?? false)
            && (!in_array($w, HS_POSTP, true) || $k !== $cur + 1 || in_array($T($cur), HS_CAUS, true))) {
            // 请把门关上, 用剪刀把纸剪开, 请往这边走: giới từ không bám ngay sau
            // động từ (hay bám sau 请 / 让) mà phía sau còn động từ thì cụm của nó
            // là trạng ngữ cho động từ đó.
            $j = $k + 1;
            while ($j < $n && hs_np_like($P($j), $T($j))) {
                $j++;
            }
            $v = $j;
            while ($v < $n && (in_array($P($v), ['ADV', 'AUX'], true) || $P($v) === 'ADP')) {
                if ($P($v) === 'ADP') {   // cụm giới từ khác chen giữa: 用剪刀[把纸]剪开
                    $v++;
                    while ($v < $n && hs_np_like($P($v), $T($v)) && !($isV[$v] ?? false)) {
                        $v++;
                    }
                    continue;
                }
                $v++;
            }
            if ($v < $n && ($P($v) === 'VERB' || ($isV[$v] ?? false))) {
                $set($k, 'adv', $v);
                if ($j > $k + 1) {
                    hs_np($W, $ix, $k + 1, $j, $k, 'pobj');
                }
                for ($a = $j; $a < $v;) {
                    if ($P($a) === 'ADP') {   // cụm giới từ khác chen giữa: [把纸]
                        $set($a, 'adv', $v);
                        $e = $a + 1;
                        while ($e < $v && hs_np_like($P($e), $T($e))) {
                            $e++;
                        }
                        if ($e > $a + 1) {
                            hs_np($W, $ix, $a + 1, $e, $a, 'pobj');
                        }
                        $a = $e;
                        continue;
                    }
                    $set($a, $P($a) === 'AUX' ? 'aux' : 'adv', $v);
                    $a++;
                }
                $k = $v;
                continue;
            }
        }
        if ($p === 'ADP' && !($isV[$k] ?? false)) {
            // 住在北京, 送给他: giới từ sau động từ là bổ ngữ, tân ngữ của nó là pobj.
            $set($k, 'comp', $cur);
            $j = $k + 1;
            while ($j < $n && hs_np_like($P($j), $T($j))) {
                $j++;
            }
            if ($j > $k + 1) {
                hs_np($W, $ix, $k + 1, $j, $k, 'pobj');
            }
            $k = $j;
            continue;
        }
        if (in_array($T($cur), HS_CLV, true) && $objs === 0 && !in_array($p, ['ADJ', 'VERB'], true) && hs_has_pred($P, $T, $k, $n)) {
            // 我觉得他很好: phần sau là cả một mệnh đề làm tân ngữ.
            $sub = hs_clause($W, array_slice($ix, $k));
            if ($sub !== null) {
                $W[$sub]['func'] = 'vcomp';
                $W[$sub]['head'] = $ix[$cur];
            }
            break;
        }
        if ($p === 'AUX') {
            $j = $k + 1;
            while ($j < $n && $P($j) === 'ADV') {
                $j++;
            }
            if ($j < $n && ($isV[$j] ?? false) || $j < $n && $P($j) === 'VERB') {
                for ($a = $k; $a < $j; $a++) {
                    $set($a, $a === $k ? 'aux' : 'adv', $j);
                }
                $set($j, 'vcomp', $cur);
                $cur  = $j;
                $objs = 0;
                $k    = $j + 1;
                continue;
            }
        }
        $resV = $k === $cur + 1 && mb_strlen($T($cur)) > 1
                && in_array(mb_substr($T($cur), -1), array_merge(HS_RES, HS_DIR1), true);
        if ($w === $T($cur) && $k - 1 > $cur && in_array($T($k - 1), ['了', '一'], true)) {
            $set($k, 'comp', $cur);   // 指了指, 看了看, 想一想: động từ lặp lại là bổ ngữ động lượng
            $k++;
            continue;
        }
        if ($p === 'VERB' && $objs === 0 && $P($cur) === 'VERB' && hs_only_particles($T, $k + 1, $n) && !in_array($w, HS_DIR, true)
            && ($resV || $k - 1 > $cur && $W[$ix[$k - 1]]['func'] === 'comp' && $W[$ix[$k - 1]]['head'] === $ix[$cur]
                || $k - 1 === $cur + 1 && in_array($T($k - 1), HS_ASP, true))) {
            // 找 到 工作 了 / 找到 工作 了, 学会 开车: sau bổ ngữ kết quả (tách rời hay
            // viết liền trong động từ) mà phía sau chỉ còn trợ từ thì từ này là tân
            // ngữ, dù từ điển ghi là động từ.
            $W[$ix[$k]]['pos'] = 'NOUN';
            $set($k, 'obj', $cur);
            $objs++;
            $k++;
            continue;
        }
        // 谈一下工作的事: động từ + 的 + danh từ sau vị ngữ là một cụm tân ngữ
        if ($p === 'VERB' && $k + 2 < $n && $T($k + 1) === '的' && hs_np_like($P($k + 2), $T($k + 2))) {
            $e = $k + 2;
            while ($e < $n && hs_np_like($P($e), $T($e)) && !($T($e) === '的' && $e === $n - 1)) {
                $e++;
            }
            hs_np($W, $ix, $k + 2, $e, $cur, $objs > 0 ? 'comp' : 'obj');
            $set($k, 'attr', hs_np_head($W, $ix, $k + 2, $e));
            $set($k + 1, 'de', $k);
            $objs++;
            $k = $e;
            continue;
        }
        // 读书和研究技术: động từ sau từ nối là vế ngang hàng với động từ trước
        if (($p === 'VERB' || ($isV[$k] ?? false)) && $k - 1 > $root && in_array($T($k - 1), HS_COORD, true)) {
            $set($k - 1, 'mark', $k);
            $set($k, 'conj', $cur);
            $cur  = $k;
            $objs = 0;
            $k++;
            continue;
        }
        $jiu = false;
        for ($a = $cur + 1; $a < $k; $a++) {
            $jiu = $jiu || in_array($T($a), ['就', '才'], true);
        }
        if (($p === 'VERB' || ($isV[$k] ?? false)) && $jiu && $objs > 0) {
            $set($k, 'conj', $cur);   // 一到家就[给你]打电话
            for ($a = $cur + 1; $a < $k; $a++) {
                if ($W[$ix[$a]]['head'] === $ix[$cur] && in_array($W[$ix[$a]]['func'], ['adv', 'aux'], true)) {
                    $W[$ix[$a]]['head'] = $ix[$k];
                }
            }
            $cur  = $k;
            $objs = 0;
            $k++;
            continue;
        }
        if ($p === 'VERB' || ($isV[$k] ?? false)) {
            $set($k, 'vcomp', $cur);   // 去商店买东西, 喜欢听音乐
            $cur  = $k;
            $objs = 0;
            $k++;
            continue;
        }
        if (in_array($p, ['ADV'], true) || in_array($w, HS_DEG, true)) {
            // phó từ trước một tính từ / động từ phía sau
            $j = $k + 1;
            while ($j < $n && ($P($j) === 'ADV' || in_array($T($j), HS_DEG, true))) {
                $j++;
            }
            $headK = $j < $n && in_array($P($j), ['ADJ', 'VERB'], true) ? $j : $cur;
            for ($a = $k; $a < $j; $a++) {
                $set($a, 'adv', $headK);
            }
            $k = $j;
            continue;
        }
        if ($p === 'TIME' && $k + 1 < $n && ($P($k + 1) === 'VERB' || ($isV[$k + 1] ?? false))) {
            $set($k, 'adv', $k + 1);   // 准备明年去北京: 明年 bổ nghĩa cho 去
            $k++;
            continue;
        }
        if (in_array($w, HS_HOW, true) && $k + 1 < $n && $P($k + 1) === 'VERB') {
            $set($k, 'adv', $k + 1);   // 打算如何做
            $k++;
            continue;
        }
        if (hs_np_like($p, $w) || $p === 'ADJ' && $k + 1 < $n && ($T($k + 1) === '的' || hs_np_like($P($k + 1), $T($k + 1)))) {
            $j = $k;
            while ($j < $n && (hs_np_like($P($j), $T($j)) || $P($j) === 'ADJ' && $j + 1 < $n && ($T($j + 1) === '的' || hs_np_like($P($j + 1), $T($j + 1)))
                   || $P($j) === 'ADJ' && $j + 2 < $n && $T($j + 1) === '一点' && $T($j + 2) === '的'
                   || $T($j) === '一点' && $j > $k && $P($j - 1) === 'ADJ' && $j + 1 < $n && $T($j + 1) === '的'
                   || $j > $k && hs_deg_attr($P, $T, $j, $n))
                   && !(in_array($T($j), HS_QTY, true)
                        && !($T($j) === '一点' && $j > $k && $P($j - 1) === 'ADJ' && $j + 1 < $n && $T($j + 1) === '的')
                        && !($j + 1 < $n && $P($j + 1) === 'NOUN'))
                   && !($T($j) === '的' && $j === $n - 1 && !($j > $k && ($P($j - 1) === 'ADJ' || $T($j - 1) === '一点')))) {
                if ($j > $k && hs_np_break($P($j - 1), $P($j), $j + 1 < $n ? $T($j + 1) : '', $T($j), $T($j - 1))
                    && !($T($j) === '一点' && $P($j - 1) === 'ADJ') && $T($j) !== '的') {
                    break;
                }
                $j++;
            }
            if ($j === $k) {
                $j = $k + 1;
            }
            // cụm không kết thúc bằng từ nối: 读书和[研究技术] — 和 nối hai cụm động từ
            while ($j - 1 > $k && in_array($T($j - 1), HS_COORD, true)) {
                $j--;
            }
            // 这是我自己选择的路: [cụm danh từ + động từ] 的 [danh từ] là một cụm
            if ($j + 2 < $n && $P($j) === 'VERB' && $T($j + 1) === '的' && hs_np_like($P($j + 2), $T($j + 2))) {
                $e = $j + 2;
                while ($e < $n && hs_np_like($P($e), $T($e)) && !($T($e) === '的' && $e === $n - 1)) {
                    $e++;
                }
                $r1 = hs_clause($W, array_slice($ix, $k, $j + 1 - $k));
                hs_np($W, $ix, $j + 2, $e, $cur, $objs > 0 ? 'comp' : 'obj');
                $W[$r1]['func'] = 'attr';
                $W[$r1]['head'] = $ix[hs_np_head($W, $ix, $j + 2, $e)];
                $set($j + 1, 'de', $j);
                $objs++;
                $k = $e;
                continue;
            }
            $ditrans = $objs === 0 && in_array($T($cur), HS_DITR, true) && hs_has_np($P, $T, $j, $n);
            // Cụm thứ hai sau động từ không phải loại hai tân ngữ là bổ ngữ
            // thời lượng / số lần: 等我五分钟, 读一遍.
            // Sau tính từ thường là bổ ngữ (累一点); riêng đại từ chỉ người là tân
            // ngữ: tính từ dùng như động từ (麻烦别人, 随便你).
            $adjObj = $P($cur) === 'ADJ' && in_array($P(hs_np_head($W, $ix, $k, $j)), ['PRON', 'PROPN'], true)
                      && !in_array($T(hs_np_head($W, $ix, $k, $j)), HS_HOW, true);
            // 排了半个小时, 等了三天: số + đơn vị thời lượng là bổ ngữ thời lượng
            $hd  = hs_np_head($W, $ix, $k, $j);
            $dur = in_array($T($hd), ['小时', '分钟', '天', '年', '星期', '个月', '秒', '会儿'], true) && $hd > $k
                   && ($P($k) === 'NUM' || in_array($T($k), ['半', '几'], true) || mb_strpos(HS_NUM_CH, mb_substr($T($k), 0, 1)) !== false);
            $func = $ditrans ? 'iobj' : ($adjObj ? 'obj' : ($dur || $P($cur) === 'ADJ' || $objs > 0 && !$iobj ? 'comp' : 'obj'));
            hs_np($W, $ix, $k, $j, $cur, $func);
            $iobj = $ditrans;
            $objs++;
            $k = $j;
            continue;
        }
        if ($p === 'ADJ') {
            // 又聪明又漂亮: tính từ sau vị ngữ tính từ là vế ngang hàng;
            // 让我很痛苦: sau 让 + tân ngữ, tính từ là vị ngữ của kiêm ngữ.
            $f = $P($cur) === 'ADJ' ? 'conj' : (in_array($T($cur), HS_CAUS, true) && $objs > 0 ? 'vcomp' : 'comp');
            $set($k, $f, $cur);
            $k++;
            continue;
        }
        if (in_array($p, ['SCONJ', 'CCONJ', 'INTJ'], true)) {
            $set($k, 'mark', $cur);
            $k++;
            continue;
        }
        $set($k, 'adv', $cur);
        $k++;
    }
    return $ix[$root];
}

/** Phó từ mức độ giữa cụm danh từ, trước tính từ + 的: 我<最>好的朋友, 一个<很>重要的问题. */
/**
 * Vế có phải chỉ là một cụm danh từ không, và là loại nào:
 *   'voc'   lời gọi: một hai từ chỉ người (老兄, 李小姐, 医生);
 *   'topic' chủ đề: cụm danh từ có định ngữ / số (这么多菜, 这么简单的题, 82304155);
 *   ''      vế thường, kể cả vị ngữ danh từ đủ thành phần (今天几号, 她今年二十岁)
 *           và câu hỏi tỉnh lược (你呢？).
 */
function hs_nominal_kind(array $W, array $ix, int $r): string
{
    $intj = true;
    foreach ($ix as $i) {
        $intj = $intj && $W[$i]['pos'] === 'INTJ';
    }
    if ($intj || count($ix) === 1 && $W[$ix[0]]['pos'] === 'ADJ') {
        return 'voc';   // 喂，… / 糟糕，我忘了: thán từ, tính từ cảm thán đứng riêng
    }
    $timeOnly = true;
    foreach ($ix as $i) {
        $timeOnly = $timeOnly && (in_array($W[$i]['pos'], ['TIME', 'NUM', 'CLF'], true) || $W[$i]['w'] === '点');
    }
    if ($timeOnly && count($ix) > 1) {
        return 'topic';   // 下午三点，好吗: cụm thời gian là cái được bàn tới
    }
    if (!in_array($W[$r]['pos'], ['NOUN', 'PRON', 'PROPN', 'NUM', 'CLF'], true)) {
        return '';
    }
    $allName = count($ix) <= 2;
    foreach ($ix as $i) {
        $allName = $allName && in_array($W[$i]['pos'], ['NOUN', 'PROPN', 'PRON'], true);
    }
    if ($allName) {
        return 'voc';   // 李小姐, 老兄: kiểm tra trước, vì 李 bị gán làm chủ ngữ của 小姐
    }
    foreach ($ix as $i) {
        $t = $W[$i];
        if (in_array($t['w'], HS_SFP, true) || in_array($t['pos'], ['VERB', 'AUX', 'SCONJ', 'CCONJ', 'ADP'], true)) {
            return '';
        }
        // một danh từ / thời gian khác làm chủ ngữ hay trạng ngữ: vị ngữ danh từ đủ câu
        if ($i !== $r && $t['head'] === $r && in_array($t['func'], ['subj', 'adv'], true)
            && in_array($t['pos'], ['NOUN', 'PRON', 'PROPN', 'TIME', 'NUM'], true)) {
            return '';
        }
    }
    return 'topic';
}

/**
 * Soát lại các cụm danh từ đứng trước vị ngữ sau lượt gán nhãn tuần tự, khi đã
 * thấy được cả dãy:
 *   和朋友一起开车 / 这和你有关: 和, 跟 trước cụm danh từ mà sau cụm chỉ còn phó
 *     từ là giới từ "cùng, với" — trạng ngữ, không phải nối hai chủ ngữ;
 *   因为你我才成功: 因为 + cụm danh từ + chủ ngữ khác thì 因为 là giới từ "vì";
 *   他一句话也没说: cụm mở đầu bằng số đứng trước 也 / 都 là tân ngữ đảo lên;
 *     các cụm mở đầu bằng số khác (两个人一起, 一个人来) giữ làm trạng ngữ;
 *   这件事我来做, 你的生日我不会忘的: hai cụm liền nhau mà cụm đầu không phải
 *     đại từ thì cụm đầu là chủ đề (主题), cụm sau là chủ ngữ;
 *   我明年大学毕业: đại từ làm chủ ngữ rồi mới tới danh từ thì danh từ đó là
 *     tân ngữ đưa lên trước động từ.
 */
function hs_pre_nps(array &$W, array $ix, int $start, int $end, int $root): void
{
    $P   = fn(int $k) => $W[$ix[$k]]['pos'];
    $T   = fn(int $k) => $W[$ix[$k]]['w'];
    $F   = fn(int $k) => $W[$ix[$k]]['func'];
    $set = function (int $k, string $func, int $headK) use (&$W, $ix): void {
        $W[$ix[$k]]['func'] = $func;
        $W[$ix[$k]]['head'] = $headK < 0 ? -1 : $ix[$headK];
    };
    $light = fn(int $k) => in_array($P($k), ['ADV', 'AUX', 'TIME'], true) || in_array($T($k), HS_DEG, true)
                           || in_array($T($k), ['一起', '是'], true);

    // 和 / 跟 làm giới từ
    for ($m = $start + 1; $m < $end; $m++) {
        if (!in_array($T($m), ['和', '跟', '同', '与'], true)) {
            continue;
        }
        $j = $m + 1;
        while ($j < $end && hs_np_like($P($j), $T($j)) && !in_array($T($j), HS_COORD, true)) {
            $j++;
        }
        if ($j === $m + 1) {
            continue;
        }
        $ok = true;
        for ($a = $j; $a < $end; $a++) {
            $ok = $ok && $light($a);
        }
        if ($ok) {
            $set($m, 'adv', $root);
            hs_np($W, $ix, $m + 1, $j, $m, 'pobj');
        }
    }

    // Đầu các cụm danh từ gắn thẳng vào vị ngữ, kèm chỗ bắt đầu của cụm.
    $nps = [];
    for ($k = $start; $k < $end; $k++) {
        if ($W[$ix[$k]]['head'] !== $ix[$root] || !in_array($F($k), ['subj', 'adv'], true)
            || !in_array($P($k), ['NOUN', 'PRON', 'PROPN'], true)
            || in_array($T($k), HS_WHEN, true) || in_array($T($k), HS_HOW, true)) {
            continue;
        }
        $s = $k;
        while ($s - 1 >= $start && (in_array($F($s - 1), ['det', 'attr', 'de'], true) || $W[$ix[$s - 1]]['head'] === $ix[$k])) {
            $s--;
        }
        $nps[] = [$s, $k];
    }

    // 因为 + cụm danh từ, rồi còn chủ ngữ khác
    if (count($nps) >= 2 && $nps[0][0] - 1 >= 0 && in_array($T($nps[0][0] - 1), ['因为', '由于'], true)) {
        $set($nps[0][0] - 1, 'adv', $root);
        $set($nps[0][1], 'pobj', $nps[0][0] - 1);
        array_shift($nps);
        $set($nps[0][1], 'subj', $root);
    }

    // Cụm mở đầu bằng số
    $rest = [];
    foreach ($nps as $i => [$s, $h]) {
        $num = $i > 0 && ($P($s) === 'NUM' || mb_strpos(HS_NUM_CH, mb_substr($T($s), 0, 1)) !== false);
        if (!$num) {
            $rest[] = [$s, $h];
            continue;
        }
        $next = $h + 1 < $end ? $T($h + 1) : '';
        $set($h, in_array($next, ['也', '都'], true) ? 'obj' : 'adv', $root);
    }

    // 明天是星期四: câu 是 chưa có chủ ngữ thì từ chỉ thời gian đứng trước là chủ ngữ
    if ($T($root) === '是') {
        $hasS = false;
        $time = null;
        for ($k = $start; $k < $end; $k++) {
            $hasS = $hasS || ($F($k) === 'subj' && $W[$ix[$k]]['head'] === $ix[$root]);
            if ($P($k) === 'TIME' && $F($k) === 'adv' && $W[$ix[$k]]['head'] === $ix[$root]) {
                $time = $k;
            }
        }
        if (!$hasS && $time !== null) {
            $set($time, 'subj', $root);
        }
    }

    if (count($rest) >= 2) {
        [, $h1] = $rest[0];
        [, $h2] = $rest[1];
        if ($P($h1) !== 'PRON') {
            $set($h1, 'topic', $root);
            $set($h2, 'subj', $root);
        }
        elseif ($P($h2) === 'NOUN') {
            $set($h1, 'subj', $root);
            $set($h2, 'obj', $root);
        }
    }
}

/** Từ vị trí $from tới hết vế chỉ còn trợ từ động thái / ngữ khí (了, 吗, 呢…) hay không còn gì. */
function hs_only_particles(callable $T, int $from, int $n): bool
{
    for ($j = $from; $j < $n; $j++) {
        if (!in_array($T($j), HS_ASP, true) && !in_array($T($j), HS_SFP, true)) {
            return false;
        }
    }
    return true;
}

function hs_deg_attr(callable $P, callable $T, int $j, int $end): bool
{
    return ($P($j) === 'ADV' || in_array($T($j), HS_DEG, true))
        && $j + 2 < $end && $P($j + 1) === 'ADJ' && $T($j + 2) === '的';
}

/** Hai token liền nhau trước vị ngữ không cùng một cụm danh từ. */
function hs_np_break(string $prevPos, string $pos, string $nextW, string $w = '', string $prevW = ''): bool
{
    if ($w === '自己') {
        return false;   // 我自己, 你们自己: đồng vị, cùng một cụm
    }
    if ($pos === 'TIME' && $nextW !== '的') {
        return true;
    }
    if ($prevPos === 'PRON' && in_array($pos, ['PRON', 'TIME', 'NUM'], true)) {
        return true;
    }
    if (in_array($prevPos, ['NOUN', 'PROPN'], true) && $pos === 'NUM' && !in_array($prevW, ['年', '月'], true)) {
        return true;   // 房间里 | 一个人 (nhưng 2014年5月11号 là một cụm ngày tháng)
    }
    if ($prevPos === 'TIME' && in_array($pos, ['PRON', 'PROPN'], true)) {
        return true;   // 直到现在 | 我
    }
    return in_array($prevPos, ['NOUN', 'PROPN'], true) && $pos === 'PRON' && !in_array($w, HS_DEM, true);
}

function hs_np_like(string $pos, string $w): bool
{
    return in_array($pos, HS_NP, true) || $w === '的' || in_array($w, HS_DEM, true) || ($pos === 'CCONJ' && in_array($w, HS_COORD, true));
}

function hs_has_pred(callable $P, callable $T, int $from, int $n): bool
{
    for ($j = $from; $j < $n; $j++) {
        if (in_array($P($j), ['VERB', 'ADJ', 'AUX'], true) || in_array($T($j), HS_DEG, true)) {
            return $j > $from || in_array($T($j), HS_DEG, true) || $P($j) === 'ADJ';
        }
    }
    return false;
}

/** Từ $from trở đi có động từ / tính từ / trợ động từ nào không (tính cả chính $from). */
function hs_any_pred(callable $P, callable $T, int $from, int $n): bool
{
    for ($j = $from; $j < $n; $j++) {
        if (in_array($P($j), ['VERB', 'ADJ', 'AUX'], true) || in_array($T($j), HS_DEG, true)) {
            return true;
        }
    }
    return false;
}

function hs_has_np(callable $P, callable $T, int $from, int $n): bool
{
    for ($j = $from; $j < $n; $j++) {
        if (in_array($P($j), ['NOUN', 'PROPN', 'NUM', 'CLF', 'PRON'], true) || in_array($T($j), HS_DEM, true)) {
            return true;
        }
        if (!in_array($P($j), ['ADJ', 'PART'], true)) {
            return false;
        }
    }
    return false;
}

/**
 * Cụm danh từ ix[$a .. $b): trung tâm là danh từ / đại từ cuối cùng; "和" nối
 * các vế ngang hàng; chỉ thị, số, lượng từ là hạn định; còn lại là định ngữ.
 */
function hs_np(array &$W, array $ix, int $a, int $b, int $headK, string $func): void
{
    $set = function (int $k, string $f, int $h) use (&$W, $ix): void {
        $W[$ix[$k]]['func'] = $f;
        $W[$ix[$k]]['head'] = $h < 0 ? -1 : $ix[$h];
    };
    // vế ngang hàng: 我和你, 爸爸跟妈妈, 爸爸、妈妈 (dấu 、 đánh dấu ở token sau nó)
    $parts = [];
    $s = $a;
    for ($k = $a; $k < $b; $k++) {
        if (!empty($W[$ix[$k]]['enum']) && $k > $s) {
            $parts[] = [$s, $k];
            $s = $k;
        }
        if (in_array($W[$ix[$k]]['w'], HS_COORD, true) && $k > $a && $k < $b - 1) {
            $parts[] = [$s, $k];
            $parts[] = [$k, $k + 1];   // chính từ nối
            $s = $k + 1;
        }
    }
    $parts[] = [$s, $b];
    $heads = [];
    foreach ($parts as [$x, $y]) {
        if ($y - $x === 1 && in_array($W[$ix[$x]]['w'], HS_COORD, true) && count($parts) > 1) {
            continue;
        }
        $heads[] = hs_np_head($W, $ix, $x, $y);
    }
    $first = $heads[0];
    foreach ($parts as $pi => [$x, $y]) {
        if ($y - $x === 1 && in_array($W[$ix[$x]]['w'], HS_COORD, true) && count($parts) > 1) {
            // từ nối gắn vào đầu của vế đứng sau nó (一个电脑和一本书: 和 → 书)
            $nx = $parts[$pi + 1] ?? null;
            $set($x, 'mark', $nx ? hs_np_head($W, $ix, $nx[0], $nx[1]) : $first);
            continue;
        }
        $h = hs_np_head($W, $ix, $x, $y);
        if ($h === $first) {
            $set($h, $func, $headK);
        }
        else {
            $set($h, 'conj', $first);
        }
        for ($k = $x; $k < $y; $k++) {
            if ($k === $h) {
                continue;
            }
            $w = $W[$ix[$k]]['w'];
            $p = $W[$ix[$k]]['pos'];
            if ($w === '的') {
                $set($k, 'de', $k > $x ? $k - 1 : $h);
            }
            elseif (in_array($w, HS_DEM, true) || in_array($p, ['NUM', 'CLF'], true)
                    || in_array($w, ['上', '下'], true) && $k + 1 < $y && $W[$ix[$k + 1]]['w'] === '个') {
                $set($k, 'det', $h);   // 下个星期, 上个月: 上 / 下 là từ hạn định
            }
            elseif ($p === 'ADV' || in_array($w, HS_DEG, true)) {
                $set($k, 'adv', $k + 1 < $y ? $k + 1 : $h);
            }
            else {
                $set($k, 'attr', $h);
            }
        }
    }
}

function hs_np_head(array $W, array $ix, int $a, int $b): int
{
    if ($b - 1 > $a && in_array($W[$ix[$b - 1]]['w'], ['号', '日'], true)) {
        return $b - 1;   // 9月2号, 2014年5月11号: ngày tháng lấy ngày làm đầu
    }
    for ($k = $b - 1; $k >= $a; $k--) {
        if (in_array($W[$ix[$k]]['pos'], ['NOUN', 'PRON', 'PROPN', 'TIME'], true) && $W[$ix[$k]]['w'] !== '的') {
            return $k;
        }
    }
    for ($k = $b - 1; $k >= $a; $k--) {
        if ($W[$ix[$k]]['pos'] === 'CLF') {
            return $k;   // 好几口, 三次: không có danh từ thì lượng từ làm đầu
        }
    }
    for ($k = $b - 1; $k >= $a; $k--) {
        if ($W[$ix[$k]]['pos'] === 'ADJ') {
            return $k;   // 大一点的: cụm không có danh từ thì tính từ làm đầu
        }
    }
    for ($k = $b - 1; $k >= $a; $k--) {
        if ($W[$ix[$k]]['w'] !== '的') {
            return $k;
        }
    }
    return $a;
}

/** Khung câu gọn cho chip: "S 想 喝 O", "S 很 好", "S 是 O". */
function hs_pattern(array $W, ?int $root = null): string
{
    // $root cho sẵn thì dựng khung quanh từ đó (trang 50 động từ: khung của chính
    // động từ đang học, dù nó không phải vị ngữ của cả câu).
    foreach ($W as $i => $t) {
        if ($root === null && $t['head'] === -1) {
            $root = $i;
            break;
        }
    }
    if ($root === null) {
        return '';
    }
    $lab = ['topic' => 'T', 'subj' => 'S', 'obj' => 'O', 'iobj' => 'O₁', 'comp' => 'C', 'vcomp' => 'V₂'];
    $out = [];
    foreach ($W as $i => $t) {
        if ($i === $root || ($t['head'] === $root && $t['func'] === 'aux')) {
            $out[] = $t['w'];
        }
        elseif ($t['head'] === $root && isset($lab[$t['func']])) {
            if (!$out || end($out) !== $lab[$t['func']]) {
                $out[] = $lab[$t['func']];
            }
        }
    }
    return implode(' ', $out);
}
