# Laravel API backend

Backend xử lý danh mục sản phẩm, kiểm tra giỏ hàng, tạo đơn và lưu yêu cầu hợp tác. API nằm trong `routes/api.php`, chạy theo middleware stateless của Laravel. Cơ sở dữ liệu mặc định là SQLite; có thể đổi sang MySQL của Laragon.

## Yêu cầu

- Laragon với PHP 8.3 trở lên.
- Composer 2.
- Với SQLite, PHP cần bật `pdo_sqlite` và `sqlite3` (Laragon thường đã bật sẵn).
- Với MySQL, khởi động MySQL từ Laragon và PHP cần bật `pdo_mysql`.

Kiểm tra công cụ trong Terminal của Laragon:

```powershell
php -v
composer --version
```

## Cài đặt lần đầu — SQLite

Mở Terminal tại thư mục dự án, sau đó chạy:

```powershell
cd backend
composer install
if (!(Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
if (!(Test-Path database/database.sqlite)) { New-Item -ItemType File database/database.sqlite | Out-Null }
php artisan config:clear
php artisan migrate --seed
```

Các lệnh trên tạo cấu hình và dữ liệu mẫu. Nếu database đã được tạo, có thể bỏ qua bước cài đặt và chỉ chạy server.

## Khởi động API

Trong Terminal thứ nhất, từ thư mục `backend/`:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

API dùng địa chỉ `http://127.0.0.1:8000/api` trên máy này. Mở thử `http://127.0.0.1:8000/api/products`; kết quả là danh sách JSON sản phẩm. Bind `0.0.0.0` để điện thoại trong cùng Wi-Fi có thể gọi API. Khi đó, thay `127.0.0.1` bằng IPv4 LAN của máy; nếu Windows Firewall hỏi, cho phép PHP trên mạng Private.

Các endpoint:

- `GET /api/products` — danh sách; có thể lọc bằng `?category=Gà%20tươi` hoặc tìm bằng `?q=gà`.
- `POST /api/checkout` — tạo đơn hàng và các dòng sản phẩm.
- `POST /api/wholesale` — ghi yêu cầu hợp tác/giá sỉ.
- `POST /api/register`, `POST /api/login` — đăng ký và đăng nhập, trả về bearer token.
- `GET /api/me`, `POST /api/logout` — đọc thông tin tài khoản hoặc thu hồi token hiện tại; cần header `Authorization: Bearer <token>`.

`POST /api/checkout` yêu cầu bearer token hợp lệ. Tên và email tài khoản được mã hóa AES; mật khẩu được băm. Email được tra cứu thông qua giá trị HMAC dựa trên `APP_KEY`: khi xoay khóa, cần chuyển đổi lại chỉ mục này cùng dữ liệu mã hóa; chỉ thiết lập `APP_PREVIOUS_KEYS` không tự cập nhật chỉ mục đăng nhập.

Đơn D2C mới chỉ có tiền hàng; `shipping_fee` và `total` để trống cho đến khi Admin nhập phí sau khi liên hệ khách. Admin phải xác nhận khách đồng ý trước khi đánh dấu đơn đã xác nhận. Yêu cầu B2B được cấp mã và lưu riêng; Admin có thể ghi báo giá và trạng thái trong khu vực quản trị. Báo giá, thay đổi đơn và phản ánh đều cần được trại thông báo cho khách qua kênh liên lạc riêng; website chưa gửi thông báo tự động.

## Phân quyền User, Manager và Admin

Tài khoản đăng ký mới mặc định là `user`. Để cấp quyền Admin đầu tiên, đăng ký một tài khoản trên website, rồi chạy trong thư mục `backend/`:

```powershell
php artisan migrate --no-interaction
php artisan app:assign-admin "email-cua-ban@example.com"
```

Thay email bằng email đã đăng ký. Đăng xuất và đăng nhập lại để giao diện cập nhật quyền. Trong mục **Quản trị**, Admin có thể đổi vai trò, khóa hoặc mở khóa tài khoản; Manager và Admin có thể thêm, sửa và ngừng bán sản phẩm. Tài khoản đang đăng nhập không thể tự khóa hoặc tự hạ quyền Admin. Khi khóa một tài khoản, các token đăng nhập của tài khoản đó bị thu hồi.

Các API quản trị nằm dưới `/api/management/products` và `/api/management/users`. Backend kiểm tra bearer token và quyền trên mỗi yêu cầu. Không có mật khẩu Admin mặc định.

Kiểm tra route hoặc trạng thái migration:

```powershell
php artisan route:list --path=api
php artisan migrate:status
```

## Dùng MySQL của Laragon (tùy chọn)

Khởi động MySQL trong Laragon, tạo database tên `sieu_mon` bằng HeidiSQL/phpMyAdmin. Mở `backend/.env`, đổi cấu hình:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sieu_mon
DB_USERNAME=root
DB_PASSWORD=
```

Sau đó chạy:

```powershell
php artisan config:clear
php artisan migrate --seed
```

Điền `DB_PASSWORD` nếu MySQL của bạn có mật khẩu khác cấu hình Laragon mặc định.

## Lệnh bảo trì thường dùng

```powershell
php artisan migrate --seed       # cập nhật cấu trúc và nạp dữ liệu mẫu
php artisan migrate:status       # xem migration nào đã chạy
php artisan optimize:clear       # xóa cache cấu hình/route/view
```

Lưu ý: `migrate --seed` dùng `updateOrCreate` cho sản phẩm mẫu, nên có thể chạy lại mà không nhân đôi sản phẩm. Đơn và yêu cầu đối tác đã tạo sẽ vẫn được giữ lại.

## Mã hóa dữ liệu cá nhân bằng AES

Laravel dùng encrypted Eloquent casts để mã hóa dữ liệu trước khi ghi xuống database. Cipher được cấu hình là **AES-256-CBC**; Laravel ký thêm MAC để phát hiện ciphertext bị sửa. Model tự giải mã khi ứng dụng đọc trường dữ liệu.

Các trường được mã hóa:

- `orders`: `customer_name`, `phone`, `email`, `address`, `note`.
- `wholesale_leads`: `business_name`, `contact_name`, `phone`, `email`, `message`.

Migration `2026_11_23_000005_encrypt_personal_data` đổi các cột này sang `TEXT` để chứa ciphertext và mã hóa dữ liệu cũ đang có. Khi cài mới, các cột được tạo đúng kiểu ngay từ đầu. Chạy migration trước khi khởi động ứng dụng sau khi cập nhật code:

```powershell
php artisan migrate
```

Mã hóa dùng `APP_KEY` trong `backend/.env`. Giữ khóa này bí mật, sao lưu an toàn và không tạo lại khóa sau khi đã có dữ liệu mã hóa; nếu mất khóa thì dữ liệu cũ không thể giải mã. Dữ liệu đã mã hóa không thể tìm kiếm trực tiếp bằng SQL, và demo hiện không lọc đơn theo các trường này. Đây là mã hóa dữ liệu lưu trong database; khi triển khai thật cần bật HTTPS để bảo vệ dữ liệu trên đường truyền. Password (nếu bổ sung tài khoản sau này) cần được băm một chiều thay vì mã hóa AES.

## CORS khi chạy Expo

`config/cors.php` cho phép mọi origin trong môi trường demo để Expo Web/điện thoại truy cập API. Khi đưa website lên mạng, giới hạn `allowed_origins` về domain frontend và không bật credentials trừ khi có nhu cầu đăng nhập bằng cookie.
