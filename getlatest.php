<?PHP

include("config.php");
$channel = "#" . $_GET[chan];
$chan    = $_GET[chan];
// Create connection
$mysqli  = new mysqli($config["mysql_server"], $config["mysql_user"], $config["mysql_pass"], $config["mysql_db"]);
if (mysqli_connect_errno()) {
    die("Failed to connect to MySQL: " . mysqli_connect_error());
}

ini_set('display_errors', 1);
$filename = "latest.tar";
if (file_exists("latest.tgz")) {
    unlink("latest.tgz");
}
if (file_exists($filename)) {
    unlink($filename);
}
$phar = new PharData($filename);

$sql  = "SELECT `date`,`timestamp`, `message`, `linenum` FROM `logs` WHERE `channel`=?";
//die(printf( str_replace('?', '%s', $sql), str_replace(".log", "", $escaped),$channel));
$stmt = $mysqli->prepare($sql);

$stmt->bind_param(s, $channel);
$stmt->execute();
$stmt->bind_result($date, $timestamp, $line, $linenum);
$i = 0;
$stmt->store_result();
$numrows = $stmt->num_rows;
$lastDate;
if ($numrows > 1) {
    while ($stmt->fetch()) {
        if ($lastDate == $date) {
            $buffer .= $line . "\r\n";
            $phar->addFromString($date . '.log', $buffer);
        } else {
            $buffer   = "";
            $lastDate = $date;
        }
    }
    $stmt->close();
} else {
    die("error opening the file.");
}
$phar->compress(Phar::GZ, ".tgz");
unset($phar);
$handle = @fopen("latest.tgz", "rb");
header('Content-type: archive/tar');
header('Content-Disposition: attachment; filename="' . basename("latest.tgz") . '"');
header('Content-Transfer-Encoding: binary');
ob_end_clean();//required here or large files will not work
@fpassthru($handle);//works fine now
unlink("latest.tgz");
unlink("latest.tar");
?>
