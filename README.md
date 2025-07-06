# Remote Uploader for WordPress

Upload WordPress media files directly to a remote FTP server (like a download host) while preserving Elementor paths and styles. This plugin ensures that media files (e.g., images) are uploaded to a secondary domain (e.g., `https://dl.example.com/uploads`) while keeping Elementor-generated CSS and assets untouched.

## 🔧 Features

- ✅ Automatically uploads new media to a remote FTP server.
- ✅ Keeps Elementor styles (`elementor/css/...`) working by excluding them.
- ✅ Updates media URLs to point to the remote download host.
- ✅ Automatically creates directory structures on the FTP server.
- ✅ Deletes the local copy after successful upload to save space.
- ✅ Cleanly integrated into WordPress's `upload_dir` and `wp_handle_upload` hooks.

## 📂 Directory Structure

Your WordPress media files (e.g., `/wp-content/uploads/2025/07/image.jpg`) will be served from:

```
https://dl.example.com/uploads/2025/07/image.jpg
```

But Elementor-generated files (like `/uploads/elementor/css/post-xxxx.css`) remain untouched and are loaded from the main site:

```
https://example.com/wp-content/uploads/elementor/css/post-xxxx.css
```

## 🚀 Installation

1. Upload the plugin folder to your WordPress site's `/wp-content/plugins/` directory.
2. Activate the plugin via the **Plugins** menu in WordPress.
3. Define your FTP credentials in `wp-config.php` (optional, see below).

## 🛠 Configuration

By default, FTP credentials are hardcoded in the plugin. To secure and externalize them, add the following constants in your `wp-config.php`:

```php
define('HOJREHDAR_FTP_HOST', 'ftp.dl.example.com');
define('HOJREHDAR_FTP_USERNAME', 'your_ftp_username');
define('HOJREHDAR_FTP_PASSWORD', 'your_ftp_password');
````

Replace `example.com` with your actual domain.

## ✅ Upload Behavior

* Files uploaded under `/uploads/20xx/` are moved to the remote FTP.
* Elementor-related uploads (containing `elementor` in path) are **excluded**.
* Files are removed from the local server after successful transfer.

## 🔒 Security Note

Make sure your FTP credentials are kept secure. Avoid committing them to version control. Prefer using `wp-config.php` as described above.

## 📄 License

MIT License

## 👨‍💻 Authors

* **[Max Base](https://github.com/BaseMax)**

GitHub: [https://github.com/BaseMax/wp-remote-uploader](https://github.com/BaseMax/wp-remote-uploader)

---

Happy uploading 🚀
