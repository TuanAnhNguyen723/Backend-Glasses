# Laravel Backend - Optic Eyewear API

Backend API cho ứng dụng bán kính mắt được xây dựng với Laravel 12.

## Yêu cầu hệ thống

- PHP >= 8.2
- Composer
- MySQL/MariaDB hoặc SQLite
- Node.js & NPM (cho frontend assets)

## Cài đặt

### 1. Clone repository và cài đặt dependencies

```bash
composer install
```

### 2. Cấu hình môi trường

Sao chép file `.env.example` thành `.env`:

```bash
cp .env.example .env
```

Cấu hình database trong file `.env`:

```env
APP_NAME="Optic Eyewear"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=optic_eyewear
DB_USERNAME=root
DB_PASSWORD=

# API Configuration
FRONTEND_URL=http://localhost:5173
```

### 3. Tạo application key

```bash
php artisan key:generate
```

### 4. Chạy migrations

```bash
php artisan migrate
```

### 5. Chạy server

```bash
php artisan serve
```

API sẽ chạy tại: `http://localhost:8000`

## Cấu trúc API

### Public Routes (không cần authentication)

- `POST /api/v1/register` - Đăng ký tài khoản
- `POST /api/v1/login` - Đăng nhập
- `POST /api/v1/forgot-password` - Quên mật khẩu
- `GET /api/v1/products` - Danh sách sản phẩm
- `GET /api/v1/products/{id}` - Chi tiết sản phẩm
- `GET /api/v1/categories` - Danh sách danh mục
- `GET /api/v1/products/{id}/reviews` - Đánh giá sản phẩm

### Protected Routes (cần authentication)

#### Authentication
- `POST /api/v1/logout` - Đăng xuất
- `GET /api/v1/me` - Thông tin user hiện tại

#### User Profile
- `GET /api/v1/profile` - Lấy thông tin profile
- `PUT /api/v1/profile` - Cập nhật profile
- `POST /api/v1/profile/avatar` - Cập nhật avatar

#### Cart
- `GET /api/v1/cart` - Lấy giỏ hàng
- `POST /api/v1/cart` - Thêm vào giỏ hàng
- `PUT /api/v1/cart/{id}` - Cập nhật item trong giỏ hàng
- `DELETE /api/v1/cart/{id}` - Xóa item khỏi giỏ hàng
- `DELETE /api/v1/cart` - Xóa toàn bộ giỏ hàng

#### Orders
- `GET /api/v1/orders` - Danh sách đơn hàng
- `GET /api/v1/orders/{id}` - Chi tiết đơn hàng
- `POST /api/v1/orders` - Tạo đơn hàng
- `POST /api/v1/orders/{id}/cancel` - Hủy đơn hàng
- `GET /api/v1/orders/{id}/track` - Theo dõi đơn hàng

#### Prescriptions
- `GET /api/v1/prescriptions` - Danh sách đơn kính
- `POST /api/v1/prescriptions` - Tạo đơn kính
- `PUT /api/v1/prescriptions/{id}` - Cập nhật đơn kính
- `DELETE /api/v1/prescriptions/{id}` - Xóa đơn kính

#### Saved Styles (Wishlist)
- `GET /api/v1/saved-styles` - Danh sách style đã lưu
- `POST /api/v1/saved-styles` - Lưu style
- `DELETE /api/v1/saved-styles/{id}` - Xóa style đã lưu
- `DELETE /api/v1/saved-styles` - Xóa tất cả style đã lưu

#### Reviews
- `POST /api/v1/products/{id}/reviews` - Tạo đánh giá
- `PUT /api/v1/reviews/{id}` - Cập nhật đánh giá
- `DELETE /api/v1/reviews/{id}` - Xóa đánh giá

#### Promo Codes
- `POST /api/v1/promo-codes/validate` - Validate mã giảm giá

## Authentication

API sử dụng Laravel Sanctum cho authentication. Để sử dụng protected routes:

1. Đăng ký/Đăng nhập để nhận token
2. Gửi token trong header: `Authorization: Bearer {token}`

Ví dụ với cURL:

```bash
# Đăng nhập
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Sử dụng token
curl -X GET http://localhost:8000/api/v1/profile \
  -H "Authorization: Bearer {your-token}"
```

## Cấu trúc thư mục

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/          # API Controllers
│   │   └── Admin/        # Admin Controllers
│   ├── Requests/         # Form Requests (Validation)
│   ├── Resources/        # API Resources
│   └── Middleware/       # Custom Middleware
├── Models/               # Eloquent Models
├── Services/             # Business Logic Services
└── Jobs/                 # Queue Jobs

database/
├── migrations/           # Database Migrations
└── seeders/              # Database Seeders

routes/
├── api.php               # API Routes
└── web.php               # Web Routes
```

## Packages đã cài đặt

- **Laravel Sanctum** - API Authentication
- **Intervention Image** - Image manipulation

## Lưu ý

- CORS đã được cấu hình sẵn trong Laravel 12
- Sanctum đã được cấu hình với guard `['web', 'api']`
- Các controller hiện tại chỉ là skeleton, cần implement logic chi tiết

## Development

### Chạy tests

```bash
php artisan test
```

### Tạo migration mới

```bash
php artisan make:migration create_table_name
```

### Tạo model mới

```bash
php artisan make:model ModelName
```

### Tạo controller mới

```bash
php artisan make:controller Api/ControllerName
```

## License

MIT
# Backend-Glasses
