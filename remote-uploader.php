<?php
/*
Plugin Name: Remote Uploader
Description: آپلود رسانه‌ها روی هاست دانلود با حفظ مسیر المنتور + انتقال فایل از هاست اصلی به هاست دانلود
Version: 1.6.2
Author: Seyyed Ali Mohammadiyeh (Max Base)
Author URI: https://github.com/BaseMax
Plugin URI: https://github.com/BaseMax/wp-remote-uploader
License: MIT
*/

defined('ABSPATH') || exit;

/**
 * CONFIGURATION
 * Override these in wp-config.php if needed.
 */
defined('REMOTEUPLOADER_SUBDOMAIN_URL') || define('REMOTEUPLOADER_SUBDOMAIN_URL', 'https://dl.hojrehdar.com/uploads');
defined('REMOTEUPLOADER_FTP_HOST') || define('REMOTEUPLOADER_FTP_HOST', 'ftp.site.com');
defined('REMOTEUPLOADER_FTP_USERNAME') || define('REMOTEUPLOADER_FTP_USERNAME', 'user');
defined('REMOTEUPLOADER_FTP_PASSWORD') || define('REMOTEUPLOADER_FTP_PASSWORD', 'pass');
defined('REMOTEUPLOADER_FTP_BASEDIR') || define('REMOTEUPLOADER_FTP_BASEDIR', '/domains/site.com/public_html/uploads');

add_filter('upload_dir', 'remote_uploader_custom_upload_dir');
function remote_uploader_custom_upload_dir($dirs) {
    $path = $dirs['path'];
    $subdir = $dirs['subdir'];

    if (strpos($path, '/uploads/elementor/') !== false) {
        return $dirs;
    }

    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    foreach ($backtrace as $call) {
        if (!empty($call['class']) && strpos($call['class'], 'Elementor\\Core\\Files\\CSS') === 0) {
            return $dirs;
        }
    }
    
    if (preg_match('#/uploads/20\d{2}/#', $path)) {
        $dirs['url']     = REMOTEUPLOADER_SUBDOMAIN_URL . $subdir;
        $dirs['baseurl'] = REMOTEUPLOADER_SUBDOMAIN_URL;
    }

    return $dirs;
}

add_filter('wp_handle_upload', 'remote_uploader_move_to_ftp');
function remote_uploader_move_to_ftp($upload) {
    $file = $upload['file'];

    if (!preg_match('#/20\d{2}/\d{2}#', $file)) {
        return $upload;
    }

    $relative_path = str_replace(WP_CONTENT_DIR . '/uploads/', '', $file);
    $remote_file   = REMOTEUPLOADER_FTP_BASEDIR . '/' . $relative_path;

    $ftp_conn = ftp_connect(REMOTEUPLOADER_FTP_HOST, 21);
    if (!$ftp_conn) {
        error_log('❌ [Remote Uploader] اتصال به FTP برقرار نشد');
        return $upload;
    }

    $login = ftp_login($ftp_conn, REMOTEUPLOADER_FTP_USERNAME, REMOTEUPLOADER_FTP_PASSWORD);
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
