<?php
$files = glob('../storage/framework/views/*');
foreach($files as $file){
  if(is_file($file) && basename($file) !== '.gitignore') unlink($file);
}
echo "Cache cleared.";
unlink(__FILE__);
?>