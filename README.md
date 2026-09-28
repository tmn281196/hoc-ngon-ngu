# Học ngôn ngữ

Năm trang học tiếng Anh và tiếng Trung qua cấu trúc câu. Toàn bộ là file tĩnh (HTML, CSS, JS, JSON), không có
backend, không có bước dựng; đăng trên GitHub Pages.

| Trang | Nội dung |
|---|---|
| `en-svo` | Đồ thị khung câu tiếng Anh: mẫu động từ, nhóm theo ngữ nghĩa, bảng tra nhãn |
| `en-chunks` | ~1270 khối (cụm từ) tiếng Anh thông dụng, mỗi câu ví dụ tách thành khối |
| `en-matrix` | ~2900 câu luyện nói từ bộ Speaking Matrix, ngắt khối kèm nghĩa, giọng đọc, chế độ che để tự nói |
| `zh-svo` | Đồ thị câu tiếng Trung HSK1–6, pinyin trên từng từ: động từ, ngữ pháp, hội thoại |
| `zh-chunks` | Từ vựng tiếng Trung theo bài / mục ngữ pháp / nhóm động từ, câu tách sẵn thành từ |

## Cấu trúc

```
src/            cả site, đăng nguyên thư mục này
  index.html            trang mục lục
  en-svo/, en-chunks/   hai site tiếng Anh (en-chunks đọc chunks.json)
  en-matrix/            luyện nói Speaking Matrix: data.json (dựng từ epub), vi.json (nghĩa tiếng Việt)
  zh-svo/, zh-chunks/   hai site tiếng Trung
  hanyu/                dùng chung cho hai site tiếng Trung
    data.json           câu đã tách từ, pinyin, từ điển (dựng từ note, xem dưới)
    hanyu.js            đọc note, tách từ, căn pinyin; trên trình duyệt chỉ tải data.json
    hanyu-syntax.js     phân tích cú pháp (chủ ngữ, vị ngữ, tân ngữ, bổ ngữ…), chạy trên trình duyệt
tools/
  dung-du-lieu.js       dựng src/hanyu/data.json từ note (Node)
  speaking-matrix.py    dựng src/en-matrix/data.json từ năm cuốn epub Speaking Matrix (Python 3)
```

Trang zh-svo tải `data.json` rồi tự phân tích câu ngay trên trình duyệt (`zh-svo/data.js`); zh-chunks dùng thẳng
`data.json`.

## Xem thử

Trang đọc dữ liệu bằng `fetch`, nên phải mở qua http chứ không mở file trực tiếp:

```bash
python -m http.server -d src
```

rồi vào http://localhost:8000.

## Đăng lên GitHub Pages

Đẩy lên nhánh `main` là xong: `.github/workflows/pages.yml` đăng nguyên `src/`.

## Sửa nội dung

- Khối tiếng Anh: `src/en-chunks/chunks.json`.
- Speaking Matrix: epub không nằm trong repo. Dựng lại `data.json`:

  ```bash
  python tools/speaking-matrix.py <thư mục epub>
  ```

  Sách viết cho người Hàn, nhưng trang và `data.json` không giữ chữ Hàn nào. Nghĩa câu, nghĩa từng khối nằm ở
  `src/en-matrix/vi.json` (khóa là câu tiếng Anh), nên dựng lại `data.json` không mất bản dịch. Tên bài, tên mục,
  ghi chú từ vựng được thay bằng tiếng Việt lúc dựng, tra từ `vi-titles.json` đặt cạnh các file epub; chưa dịch
  thì để trống. Cần bản gốc tiếng Hàn để dịch phần mới thì thêm `--ko` (ghi `src/en-matrix/ko.json`, không đăng).
- Câu, từ vựng tiếng Trung: note giáo trình không nằm trong repo (để không bị công khai). Sửa note, rồi dựng lại
  `data.json` (cần Node 18+):

  ```bash
  node tools/dung-du-lieu.js <thư mục note>
  ```

  Thư mục note gồm `hanyu-data/` (note HSK1 chép từ vault Obsidian) và `hanyu-plus/` (`ngu-phap.md`,
  `dong-tu.md`, `tu-dien.txt`). Định dạng bảng: `Chữ Hán | Pinyin | Nghĩa`; chữ **đậm** là từ / điểm ngữ pháp
  của mục; dấu cách giữa các chữ Hán là ranh giới từ. Từ mới cần từ loại: thêm dòng `chữ|pinyin|từ loại|nghĩa`
  vào `tu-dien.txt`. Thêm bài HSK1 mới thì thêm tên note vào `HY_LESSONS` trong `src/hanyu/hanyu.js`.
