<?php
/*
Plugin Name: Remote Uploader
Description: آپلود رسانه‌ها روی هاست دانلود با حفظ مسیر المنتور + انتقال فایل از هاست اصلی به هاست دانلود
Version: 1.6.1
Author: Seyyed Ali Mohammadiyeh (Max Base)
Author URI: https://github.com/BaseMax
Plugin URI: https://github.com/BaseMax/wp-remote-uploader
License: MIT
*/

defined('ABSPATH') || exit;

add_filter('upload_dir', 'remote_uploader_custom_upload_dir');
function remote_uploader_custom_upload_dir($dirs) {
    $subdir = $dirs['subdir'];
    $path = $dirs['path'];
    
    if (isset($_GET["log"])) { print "\n"; print_r($dirs); }

    if (
        preg_match('#/uploads/20\d{2}/#', $path) &&
        strpos($path, 'elementor') === false
    ) {
        $dirs['url']     = 'https://dl.site.com/uploads' . $subdir;
        $dirs['baseurl'] = 'https://dl.site.com/uploads';
    }

    return $dirs;
}

add_filter('wp_handle_upload', 'remote_uploader_move_to_ftp');
function remote_uploader_move_to_ftp($upload) {
    $file = $upload['file'];

    if (!preg_match('#/20\d{2}/\d{2}#', $file)) {
        return $upload;
    }

    $ftp_server     = defined('REMOTEUPLOADER_FTP_HOST')     ? REMOTEUPLOADER_FTP_HOST     : 'ftp.site.com';
    $ftp_user_name  = defined('REMOTEUPLOADER_FTP_USERNAME') ? REMOTEUPLOADER_FTP_USERNAME : 'user';
    $ftp_user_pass  = defined('REMOTEUPLOADER_FTP_PASSWORD') ? REMOTEUPLOADER_FTP_PASSWORD : 'pass';

    $relative_path = str_replace(WP_CONTENT_DIR . '/uploads/', '', $file);
    $remote_file   = '/domains/site.com/public_html/uploads/' . $relative_path;

    $ftp_conn = ftp_connect($ftp_server, 21);
    if (!$ftp_conn) {
        error_log('❌ [Remote Uploader] اتصال به FTP برقرار نشد');
        return $upload;
    }

    $login = ftp_login($ftp_conn, $ftp_user_name, $ftp_user_pass);
    if (!$login) {
        error_log('❌ [Remote Uploader] ورود به FTP ناموفق بود');
        ftp_close($ftp_conn);
        return $upload;
    }

    ftp_pasv($ftp_conn, true);

    $dirs = explode('/', dirname($remote_file));
    $path = '';
    foreach ($dirs as $dir) {
        if ($dir === '') continue;
        $path .= '/' . $dir;
        @ftp_mkdir($ftp_conn, $path);
    }

    if (ftp_put($ftp_conn, $remote_file, $file, FTP_BINARY)) {
        unlink($file);
        error_log('✅ [Remote Uploader] فایل روی FTP آپلود شد و فایل محلی حذف گردید: ' . $relative_path);
    } else {
        error_log('❌ [Remote Uploader] انتقال فایل به FTP ناموفق بود: ' . $relative_path);
    }

    ftp_close($ftp_conn);
    return $upload;
}
