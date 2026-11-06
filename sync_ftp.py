import ftplib
import os

files_to_sync = [
    "app/Helpers/WhatsAppHelper.php",
    "app/Http/Controllers/PdfController.php",
    "app/Http/Controllers/PublicController.php",
    "app/Http/Controllers/PwaController.php",
    "app/Notifications/CustomerBookingNotification.php",
    "app/Notifications/NewBookingNotification.php",
    "lang/en.json",
    "lang/en/messages.php",
    "lang/id.json",
    "lang/id/messages.php",
    "lang/my.json",
    "public/build/manifest.json",
    "public/manifest.json",
    "resources/views/admin/blogs/create.blade.php",
    "resources/views/admin/blogs/edit.blade.php",
    "resources/views/admin/cms/index.blade.php",
    "resources/views/admin/cms/pages.blade.php",
    "resources/views/admin/cms/tour.blade.php",
    "resources/views/admin/install.blade.php",
    "resources/views/admin/layout.blade.php",
    "resources/views/admin/reports/financial.blade.php",
    "resources/views/admin/settings/index.blade.php",
    "resources/views/auth/login.blade.php",
    "resources/views/booking/lookup.blade.php",
    "resources/views/booking/track.blade.php",
    "resources/views/components/admin-image-guide-modal.blade.php",
    "resources/views/errors/404.blade.php",
    "resources/views/errors/500.blade.php",
    "resources/views/errors/503.blade.php",
    "resources/views/errors/maintenance.blade.php",
    "resources/views/invoice/show.blade.php",
    "resources/views/layouts/app.blade.php",
    "resources/views/layouts/partials/footer.blade.php",
    "resources/views/pages/about.blade.php",
    "resources/views/pages/payment.blade.php",
    "resources/views/pages/privacy.blade.php",
    "resources/views/pages/terms.blade.php",
    "resources/views/pdf/booking-report.blade.php",
    "resources/views/pdf/invoice.blade.php",
    "resources/views/pdf/itinerary.blade.php",
    "resources/views/pdf/package.blade.php",
    "resources/views/tour/blog-detail.blade.php",
    "resources/views/tour/blog.blade.php",
    "resources/views/tour/gallery.blade.php",
    "resources/views/tour/index.blade.php",
    "resources/views/tour/landing-origin.blade.php",
    "resources/views/tour/package-detail.blade.php",
    "resources/views/tour/packages.blade.php"
]

print("Connecting to FTP...")
ftp = ftplib.FTP('ftp.sujaitobasumatera.com')
ftp.login('admin@sujaitobasumatera.com', 'Laketoba_1')

for file in files_to_sync:
    if os.path.exists(file):
        print(f"Uploading {file}...")
        try:
            with open(file, 'rb') as f:
                ftp.storbinary(f'STOR {file}', f)
        except Exception as e:
            print(f"Error on {file}: {e}")
            # Ensure directory exists?
            parts = file.split('/')[:-1]
            path = ""
            for p in parts:
                path += p + "/"
                try:
                    ftp.mkd(path.rstrip('/'))
                except:
                    pass
            with open(file, 'rb') as f:
                ftp.storbinary(f'STOR {file}', f)

print("Uploading built assets...")
for root, dirs, files in os.walk('public/build/assets'):
    for file in files:
        path = os.path.join(root, file).replace('\\', '/')
        print(f"Uploading {path}...")
        try:
            with open(path, 'rb') as f:
                ftp.storbinary(f'STOR {path}', f)
        except Exception as e:
            try:
                ftp.mkd('public/build/assets')
            except:
                pass
            with open(path, 'rb') as f:
                ftp.storbinary(f'STOR {path}', f)

# Clear view cache
print("Uploading cache clearer...")
clearer = '''<?php
$files = glob('../storage/framework/views/*');
foreach($files as $file){
  if(is_file($file) && basename($file) !== '.gitignore') unlink($file);
}
echo "Cache cleared.";
unlink(__FILE__);
?>'''
with open('clear_cache.php', 'w') as f:
    f.write(clearer)

with open('clear_cache.php', 'rb') as f:
    ftp.storbinary('STOR public/clear_cache.php', f)

ftp.quit()
print("Done!")
