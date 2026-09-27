<?php

declare(strict_types=1);

/**
 * Dựng bản tĩnh của cả site: mọi trang PHP trong src/ thành HTML trong dist/.
 *
 *     php build.php            -> dist/
 *     php build.php out/dir    -> thư mục khác
 *
 * File không phải PHP (en-svo, en-chunks, index.html, CSS, JS) chép nguyên. Trang
 * PHP (zh-svo, zh-chunks) chạy lúc dựng: dữ liệu trong lib/hanyu-data và
 * lib/hanyu-plus được phân tích rồi nhúng thẳng vào trang, nên bản trong dist/
 * là HTML + CSS + JS thuần.
 *
 * Mỗi trang chạy trong một tiến trình PHP riêng (tools/render.php): các trang
 * cùng một site nạp chung chrome.php, data.php, nên chạy chung một tiến trình
 * sẽ khai báo trùng hàm.
 */

const PARTIALS = ['chrome.php', 'chrome-foot.php', 'board.php', 'data.php'];   // mảnh ghép, không phải trang

$root = __DIR__;
$src  = $root . '/src';
$out  = rtrim($argv[1] ?? $root . '/dist', '/\\');

rrmdir($out);
mkdir($out, 0777, true);

$pages = 0;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS)) as $file) {
    /** @var SplFileInfo $file */
    $rel  = str_replace('\\', '/', substr($file->getPathname(), strlen($src) + 1));
    $name = $file->getFilename();
    if ($name[0] === '.') {
        continue;   // .cache.json, .parse.json: đệm của lần dựng trước
    }
    $dest = $out . '/' . $rel;
    if (!is_dir(dirname($dest))) {
        mkdir(dirname($dest), 0777, true);
    }
    if ($file->getExtension() !== 'php') {
        copy($file->getPathname(), $dest);
        continue;
    }
    if (in_array($name, PARTIALS, true)) {
        continue;
    }
    $html = render($file->getPathname());
    file_put_contents(substr($dest, 0, -4) . '.html', static_links($html));
    $pages++;
    fwrite(STDOUT, "  $rel\n");
}
// GitHub Pages mặc định chạy Jekyll, bỏ qua file bắt đầu bằng "_"; tắt đi.
touch($out . '/.nojekyll');
fwrite(STDOUT, "Đã dựng $pages trang vào $out\n");

/** Chạy một trang trong tiến trình PHP riêng, trả về HTML nó in ra. */
function render(string $page): string
{
    $cmd = [PHP_BINARY, '-d', 'display_errors=stderr', '-d', 'log_errors=0'];
    // PHP portable cần chỉ lại thư mục extension (mbstring) cho tiến trình con.
    if (($ext = ini_get('extension_dir')) !== false && $ext !== '') {
        array_push($cmd, '-d', 'extension_dir=' . $ext);
    }
    array_push($cmd, __DIR__ . '/tools/render.php', $page);
    $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($proc)) {
        fail("Không chạy được PHP cho $page");
    }
    $html = stream_get_contents($pipes[1]);
    $err  = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $code = proc_close($proc);
    // Cảnh báo nạp extension của PHP portable (xdebug, opcache…) không phải lỗi của trang.
    $err = trim((string) preg_replace('/^.*(Failed loading|Unable to load dynamic library|Startup).*$\R?/mi', '', $err));
    if ($code !== 0 || $err !== '' || trim($html) === '') {
        fail("Lỗi khi dựng $page (mã $code):\n$err");
    }
    return $html;
}

/** href="grammar.php#x" -> href="grammar.html#x"; bỏ qua link ngoài (có "://"). */
function static_links(string $html): string
{
    return (string) preg_replace('/\b(href|action)="(?![a-z]+:)([^"#?]*?)\.php((?:[#?][^"]*)?)"/i', '$1="$2.html$3"', $html);
}

function rrmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                                           RecursiveIteratorIterator::CHILD_FIRST) as $f) {
        $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
    }
    rmdir($dir);
}

function fail(string $msg): never
{
    fwrite(STDERR, $msg . "\n");
    exit(1);
}
