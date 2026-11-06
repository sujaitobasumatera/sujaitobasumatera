import ftplib
import os

zip_path = r'deploy/sujaitobasumatera-20261004-013755.zip'
print('Connecting to FTP...')
ftp = ftplib.FTP('ftp.sujaitobasumatera.com')
ftp.login('admin@sujaitobasumatera.com', 'Laketoba_1')

print('Uploading update.zip (this might take a few minutes)...')
with open(zip_path, 'rb') as f:
    ftp.storbinary('STOR update.zip', f)
print('Uploaded update.zip.')

php_script = """<?php
$zip = new ZipArchive;
$res = $zip->open('../update.zip');
if ($res === TRUE) {
  $zip->extractTo('../');
  $zip->close();
  echo "Extracted. ";
  
  $files = glob('../storage/framework/views/*');
  foreach($files as $file){
    if(is_file($file) && basename($file) !== '.gitignore') unlink($file);
  }
  echo "Cache cleared.";
} else {
  echo "Failed to unzip.";
}
unlink(__FILE__);
?>"""

with open('unzipper.php', 'w') as f:
    f.write(php_script)

print('Uploading unzipper.php...')
with open('unzipper.php', 'rb') as f:
    ftp.storbinary('STOR public/unzipper.php', f)

ftp.quit()
print('Done!')
