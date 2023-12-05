<?php

return [
    // add
    'create_success' => 'Thêm mới thành công.',
    'create_failed' => 'Thêm mới thất bại.',

    // update
    'update_success' => 'Cập nhật thành công.',
    'update_failed' => 'Cập nhật thất bại.',

    // delete
    'delete_success' => 'Xóa thành công.',
    'delete_failed' => 'Xóa thất bại.',

    // search
    'no_result_found' => 'Không tìm thấy kết quả nào.',
    'db_not_connect' => 'Lỗi kết nối database.',
    'url_not_found' => 'Truy cập không tìm thấy.',
    'not_permission' => 'Không có quyền thực hiện.',
    'no_data' => 'Không có dữ liệu.',

    // download
    'download_failed' => 'Tải xuống thất bại',
    'no_file_download' => 'Không có tệp tin :type',

    // mail subject
    'mail_reset_password_title' => 'Thông báo đặt lại mật khẩu',
    'mail_register_complete_title' => 'Thông báo đăng ký thành viên',

    // send mail
    'send_success' => 'Gửi mail thành công',
    'send_failed' => 'Gửi mail thất bại',
    'reset_password_success' => 'Đặt lại mật khẩu thành công.',
    'reset_password_fail' => 'Đặt lại mật khẩu thất bại',
    'send_reset_link_success' => 'Một email yêu ầu đặt lại mật khẩu đã được gửi đến :email, vui lòng kiểm tra email của bạn.',

    // upload file
    'file_does_not_exist' => 'Tệp tin không tồn tại.',
    'file_upload_failed' => 'Tệp tin tải lên lỗi.',
    'file_upload_blacklist' => 'Tệp tin đã tải lên bị liệt vào danh sách đen.',

    // errors
    'system_error' => 'Một lỗi không mong muốn đã xảy ra. Xin vui lòng liên hệ với quản trị hệ thống.',

    // action
    'page_action' => [
        'index' => 'Danh sách',
        'edit' => 'Chỉnh sửa',
        'show' => 'Chi tiết',
        'valid' => 'Kiểm tra',
        'confirm' => 'Xác nhận',
        'create' => 'Thêm mới',
        'store' => 'Lưu trữ',
        'update' => 'Cập nhật',
        'destroy' => 'Xóa',
    ],

    // page title
    'page_title' => [
        'errors' => 'Lỗi',
        'admin' => [
            'login' => 'Đăng nhập',
            'home' => 'Trang chính',
            'administrators' => 'Quản lý admin',
            'users' => 'Quản lý người dùng',
            'permissions' => 'Quản lý phân quyền',
        ],
    ],

    // button
    'button' => [
        'login' => 'Đăng nhập',
        'logout' => 'Đăng xuất',
        'search' => 'Tìm kiếm',
        'search_clear' => 'Xóa tìm kiếm',
        'create' => 'Tạo mới',
        'edit' => 'Sửa',
        'delete' => 'Xóa',
        'export' => 'Xuất csv',
        'back' => 'Quay lại',
        'save' => 'Lưu',
    ],

    // menu
    'menu' => [
        'backend' => [
            'home' => 'Trang chủ',
            'administrators' => 'Quản lý admin',
            'users' => 'Quản lý người dùng',
            'permissions' => 'Quản lý phân quyền',
            'roles' => 'Quản lý vai trò',
        ],
    ],

    // http message code
    'http_code' => [
        200 => '',
        401 => "Phiên làm việc của bạn đã hết hiệu lực.\nXin vui lòng đăng nhập lại.",
        403 => "Truy cập bị từ chối.",
        404 => "Không thể tìm thấy trang bạn đang tìm kiếm.",
        405 => "Phương pháp không được phép.",
        500 => "Đã xảy ra lỗi hệ thống.",
    ],

    // messages
    'token_expiration' => 'Phiên làm việc của bạn đã hết hiệu lực. Vui lòng thử lại.',
    'curl_api_error' => 'Lỗi gọi API.',
];
