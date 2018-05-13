<?PHP
ini_set('display_errors', 1);
$filename = "latest.tgz";
if (file_exists("latest.tar.gz")) { unlink("latest.tar.gz"); }
if (file_exists($filename)) { unlink($filename); }
$phar = new PharData($filename);
// add all files in the project, only include log files
$phar->buildFromDirectory(dirname(__FILE__), '/\.log$/');
$phar->compress(Phar::GZ);
unset($phar); 
  header( 'Content-type: archive/tar' );
  header( 'Content-Disposition: attachment; filename="' . basename( $filename ) . '"'  );
  header( 'Content-Transfer-Encoding: binary' );
  header( 'Content-Length: ' . filesize( $filename ) );
  readfile($filename);
  unlink($filename);
  unlink("latest.tar.gz");
?>
