<?PHP
    $cmd = "git fetch origin 2>&1 && git reset --hard origin/master 2>&1";
	
    while (@ ob_end_flush()); // end all output buffers if any

    $proc = popen($cmd, 'r');
    echo '<pre>';
    while (!feof($proc)) {
        echo fread($proc, 4096);
        @ flush();
    }
    echo '</pre>';
?>