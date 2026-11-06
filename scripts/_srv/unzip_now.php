<?php
 = new ZipArchive;
 = ->open('../update.zip');
if ( === TRUE) {
  ->extractTo('../');
  ->close();
  echo 'OK Extracted. ';
  
   = glob('../storage/framework/views/*');
  foreach( as ){
    if(is_file() && basename() !== '.gitignore') unlink();
  }
  echo 'Cache cleared.';
} else {
  echo 'Failed to unzip. Error code: ' . ;
}
?>