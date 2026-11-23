# Trại Gà Siêu Môn

Hai phần của demo được tách riêng:

- `frontend/` — Expo + React Native, dùng chung mã giao diện cho web, Android và iOS.
- `backend/` — API Laravel, xác thực dữ liệu, lưu đơn hàng và yêu cầu hợp tác vào SQLite.

## Chạy demo trên Laragon

Mở hai Terminal:

```powershell
# Terminal 1
cd backend
php artisan serve
```

```powershell
# Terminal 2
cd frontend
npm install
Copy-Item .env.example .env
npm run web
```

Mở địa chỉ Expo hiển thị trong Terminal, thường là `http://localhost:8081`. Muốn mở trên điện thoại thật, đổi `EXPO_PUBLIC_API_URL` trong `frontend/.env` thành IP LAN của máy chạy Laragon. Android emulator dùng `http://10.0.2.2:8000/api`.

Xem [frontend README](frontend/README.md) và [backend README](backend/README.md) để biết thêm cách chạy và build.
