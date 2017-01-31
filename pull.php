<?PHP
    $cmd = "git fetch origin && git merge origin/master";
    while (@ ob_end_flush()); // end all output buffers if any

    $proc = popen($cmd, 'r');
    echo '<pre>';
    while (!feof($proc)) {
        echo fread($proc, 4096);
        @ flush();
    }
    echo '</pre>';
?>