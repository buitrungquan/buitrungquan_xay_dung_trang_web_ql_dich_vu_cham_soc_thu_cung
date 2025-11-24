# CSS Structure - Pet Care Management System

## Cấu trúc thư mục CSS

```
public/assets/css/
├── main.css          # CSS chính cho toàn bộ hệ thống (sidebar, cards, buttons, tables...)
├── login.css         # CSS riêng cho trang đăng nhập
└── register.css      # CSS riêng cho trang đăng ký
```

## Cách sử dụng

### Trong các file PHP:

```php
<!-- Trang đăng nhập -->
<link rel="stylesheet" href="assets/css/login.css">

<!-- Trang đăng ký -->
<link rel="stylesheet" href="assets/css/register.css">

<!-- Các trang khác (dashboard, customers, pets...) -->
<link rel="stylesheet" href="assets/css/main.css">
```

## Màu sắc chính (CSS Variables)

Được định nghĩa trong `main.css`:
- `--primary-color`: #667eea
- `--secondary-color`: #764ba2
- `--success-color`: #28a745
- `--danger-color`: #dc3545
- `--warning-color`: #ffc107
- `--info-color`: #17a2b8

## Responsive Design

Tất cả các file CSS đều hỗ trợ responsive:
- Desktop: > 768px
- Tablet: 576px - 768px
- Mobile: < 576px

## Bảo trì

- Mỗi file CSS có comment rõ ràng về mục đích
- Dễ dàng chỉnh sửa màu sắc thông qua CSS Variables
- Tách biệt rõ ràng giữa các component

