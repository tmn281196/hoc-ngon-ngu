# Học ngôn ngữ

Bốn trang học tiếng Anh và tiếng Trung qua cấu trúc câu, xuất bản dạng trang tĩnh trên GitHub Pages.

| Trang | Nội dung |
|---|---|
| `en-svo` | Đồ thị khung câu tiếng Anh: mẫu động từ, nhóm theo ngữ nghĩa, bảng tra nhãn |
| `en-chunks` | ~1270 khối (cụm từ) tiếng Anh thông dụng, mỗi câu ví dụ tách thành khối |
| `zh-svo` | Đồ thị câu tiếng Trung HSK1–6, pinyin trên từng từ: động từ, ngữ pháp, hội thoại |
| `zh-chunks` | Từ vựng tiếng Trung theo bài / mục ngữ pháp / nhóm động từ, câu tách sẵn thành từ |

## Cấu trúc

```
src/            bốn site + index.html (trang mục lục)
  en-svo/, en-chunks/   HTML + JS thuần, không cần dựng
  zh-svo/, zh-chunks/   trang PHP, chỉ chạy lúc dựng
lib/            thư viện tiếng Trung
  hanyu.php         đọc ghi chú, tách từ, căn pinyin
  hanyu_syntax.php  phân tích cú pháp (chủ ngữ, vị ngữ, tân ngữ, bổ ngữ…)
  hanyu-data/       ghi chú giáo trình HSK1 (chép từ vault Obsidian)
  hanyu-plus/       phần soạn thêm: ngữ pháp HSK2–6, động từ, từ điển bổ sung
build.php       dựng mọi trang thành HTML tĩnh trong dist/
tools/render.php  chạy một trang PHP, in HTML ra
```

Hai site tiếng Anh là file tĩnh sẵn (en-chunks tự đọc `chunks.json` khi mở trang). Hai site tiếng Trung cần PHP
lúc dựng để tách từ, căn pinyin và phân tích câu từ ghi chú; kết quả được nhúng thẳng vào trang. `dist/` chỉ còn
HTML, CSS, JS.

## Dựng và xem thử

```bash
php build.php                 # ra dist/
python -m http.server -d dist # mở http://localhost:8000
```

Cần PHP 8.1+ có `mbstring`.

## Đăng lên GitHub Pages

Đẩy lên nhánh `main` là xong: `.github/workflows/pages.yml` chạy `php build.php` rồi đăng `dist/`.

## Sửa nội dung

- Câu, từ vựng tiếng Trung: sửa các file `.md` trong `lib/hanyu-data/` và `lib/hanyu-plus/`. Định dạng bảng: `Chữ Hán | Pinyin | Nghĩa`; chữ **đậm** là từ / điểm ngữ pháp của mục; dấu cách giữa các chữ Hán là ranh giới từ.
- Từ mới cần từ loại: thêm dòng `chữ|pinyin|từ loại|nghĩa` vào `lib/hanyu-plus/tu-dien.txt`.
- Khối tiếng Anh: `src/en-chunks/chunks.json`.
