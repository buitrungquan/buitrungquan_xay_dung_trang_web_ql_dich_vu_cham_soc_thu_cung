<?php
/**
 * Cấu hình thanh toán cho Pet Care
 * - Đặt biến môi trường PETCARE_QR_API để override link QR (ví dụ trong .env hoặc cấu hình server)
 * - Có thể sử dụng placeholder {amount} và {order_code} trong URL để tự động chèn tổng tiền/mã đơn
 */
return [
    'qr_api_template' => getenv('PETCARE_QR_API') ?: 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=PetCare-{order_code}-vnd{amount}'
];

