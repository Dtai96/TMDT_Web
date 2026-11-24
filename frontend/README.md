# Frontend React Native / Expo

Giao diện viết bằng React Native, đóng gói với Expo và Expo Web. Mã nguồn dùng chung cho trình duyệt, Android và iOS. Frontend lấy danh sách sản phẩm và gửi đơn đến Laravel API trong `../backend`.

Backend mã hóa các trường thông tin khách mua và đối tác bằng Laravel AES-256-CBC casts trước khi lưu database. Xem chi tiết và lưu ý bảo vệ `APP_KEY` tại `../backend/README.md`.

## Yêu cầu

- Node.js **24.3 trở lên trong nhánh 24**, hoặc Node.js 26 trở lên. Node 25 không nằm trong các phiên bản được Metro/React Native hiện tại hỗ trợ.
- npm đi kèm Node.js.
- Backend Laravel đã được cài đặt và đang chạy. Xem `../backend/README.md`.
- Nếu chạy Android native: Android Studio và một Android Emulator, hoặc điện thoại có Expo Go.
- iOS Simulator cần macOS + Xcode. Trên Windows có thể chạy web hoặc mở app trên iPhone bằng Expo Go.

Kiểm tra phiên bản:

```powershell
node --version
npm --version
```

## Cài đặt lần đầu

Mở Terminal tại thư mục dự án:

```powershell
cd frontend
npm install
if (!(Test-Path .env)) { Copy-Item .env.example .env }
```

`EXPO_PUBLIC_API_URL` trong `.env` cho Expo biết địa chỉ Laravel. Chọn đúng cấu hình dưới đây trước khi chạy frontend.

Khách cần đăng ký hoặc đăng nhập để thấy giỏ hàng và đặt hàng. Phiên web được giữ trong tab hiện tại; trên điện thoại phiên hiện chỉ tồn tại khi ứng dụng đang mở. Đăng xuất sẽ xóa giỏ hàng hiện tại.

## Chạy backend và frontend trên máy tính

Mở **Terminal 1**:

```powershell
cd backend
php artisan serve --host=127.0.0.1 --port=8000
```

Mở **Terminal 2**:

```powershell
cd frontend
Set-Content -Path .env -Value 'EXPO_PUBLIC_API_URL=http://127.0.0.1:8000/api' -Encoding ascii
npm run web
```

Mở địa chỉ Expo in ra trong Terminal, thường là `http://localhost:8081`. Kiểm tra API riêng tại `http://127.0.0.1:8000/api/products`.

## Chạy Android Emulator

Khởi động Android Emulator trong Android Studio trước. Backend cần bind ra máy host; trong **Terminal 1**:

```powershell
cd backend
php artisan serve --host=0.0.0.0 --port=8000
```

Trong **Terminal 2**:

```powershell
cd frontend
Set-Content -Path .env -Value 'EXPO_PUBLIC_API_URL=http://10.0.2.2:8000/api' -Encoding ascii
npm run android
```

`10.0.2.2` là địa chỉ máy tính chủ từ Android Emulator. Nếu chỉ muốn mở mã QR trong Expo Go thay vì khởi chạy emulator:

```powershell
npm start
```

## Chạy trên điện thoại thật

Máy tính và điện thoại cần cùng một mạng Wi-Fi. Xem IPv4 của máy tính bằng `ipconfig`, rồi thay `192.168.1.20` bên dưới bằng địa chỉ thật của máy:

**Terminal 1 — Laravel:**

```powershell
cd backend
php artisan serve --host=0.0.0.0 --port=8000
```

**Terminal 2 — Expo:**

```powershell
cd frontend
Set-Content -Path .env -Value 'EXPO_PUBLIC_API_URL=http://192.168.1.20:8000/api' -Encoding ascii
npm start
```

Cài Expo Go trên điện thoại và quét QR hiển thị trong Terminal. Nếu Expo Go hoặc điện thoại không kết nối được API, cho phép PHP/Laragon qua Windows Firewall trên mạng Private và kiểm tra lại IPv4. Sau khi đổi `.env`, khởi động lại Expo.

## Chạy iOS

Trên Mac có Xcode và iOS Simulator:

```bash
cd frontend
npm install
printf 'EXPO_PUBLIC_API_URL=http://127.0.0.1:8000/api\n' > .env
npm run ios
```

Trên iPhone thật, dùng địa chỉ IPv4 LAN của máy tính như hướng dẫn điện thoại ở trên, sau đó chạy `npm start` và quét QR bằng Expo Go.

## Build bản web

Đặt địa chỉ API sẽ được nhúng vào bản build, sau đó xuất web app:

```powershell
cd frontend
Set-Content -Path .env -Value 'EXPO_PUBLIC_API_URL=http://127.0.0.1:8000/api' -Encoding ascii
npm run build:web
```

Build được tạo tại `frontend/dist/`. Lệnh này chỉ tạo file tĩnh, không khởi động web server. Để host, đặt `dist/` làm document root của web server và cấu hình `EXPO_PUBLIC_API_URL` trỏ đến địa chỉ backend mà người dùng truy cập được. Nếu frontend và backend cùng domain thì có thể dùng `/api`, nhưng web server cần chuyển tiếp đường dẫn `/api` sang Laravel. Khi đưa lên production, giới hạn `allowed_origins` trong `../backend/config/cors.php` về domain frontend.

## Lệnh tiện ích

```powershell
npx expo start --clear   # khởi động Expo và xóa cache Metro
npm run web              # chạy trên trình duyệt
npm run android          # chạy Android Emulator đang bật
npm run build:web        # xuất web build vào dist/
```
