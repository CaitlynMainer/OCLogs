<?php
header('Content-Type: application/json');
include("config.php");
include("parser.php");
$channel = "#".$_GET[chan];
$date = str_replace(".log", "", basename($_GET['log']));
$line = $_GET['line'];
// Create connection
$mysqli = new mysqli($config["mysql_server"], $config["mysql_user"], $config["mysql_pass"], $config["mysql_db"]);
if (mysqli_connect_errno()) {
	die("Failed to connect to MySQL: " . mysqli_connect_error());
}

$query = "SELECT `message`, `linenum`, `timestamp` FROM `logs` WHERE `channel`=? AND `date`=? AND `linenum` >= ?";
//die(printf( str_replace('?', '%s', $query), $channel,$date,$line));
$stmt = $mysqli->prepare($query);

$stmt->bind_param(sss,$channel,$date,$line);
$stmt->execute();
$stmt->bind_result($line, $line_number, $timestamp);
$stmt->store_result();
$numrows = $stmt->num_rows;
$mcp = new MircColorParser();
if ($numrows >= 1) {    
    $out = "[";
    while ($stmt->fetch()) {
            $type = "unknown";
            $line = htmlspecialchars("[".$timestamp."] ".$line);
            $line = $mcp->colorize($line);
            list($line, $type) = parseLine($line, $line_number);
            //$line = "$line_number: " . $line;
            $line = makeClickableLinks($line);
            $line = str_replace("&lt;Corded&gt; &lt;", "<img height=\"16\" width=\"16\" src=\"https://discordapp.com/assets/2c21aeda16de354ba5334551a883b481.png\" title=\"<Corded> \">&lt;", $line);
            $line = str_replace("&lt;Discord&gt; &lt;", "<img height=\"16\" width=\"16\" src=\"https://discordapp.com/assets/2c21aeda16de354ba5334551a883b481.png\" title=\"<Discord> \">&lt;", $line);
            $line = str_replace("&lt;MrConductor&gt; &lt;", "<img height=\"16\" width=\"16\" src=\"https://discordapp.com/assets/2c21aeda16de354ba5334551a883b481.png\" title=\"<MrConductor> \">&lt;", $line);
            $line = str_replace("&lt;MrConductor1&gt; &lt;", "<img height=\"16\" width=\"16\" src=\"https://discordapp.com/assets/2c21aeda16de354ba5334551a883b481.png\" title=\"<MrConductor> \">&lt;", $line);
            $line = "<div id='CL$line_number' class='full_line $type'>" . $line . "</div>";
        $out .= "{\"message\":".json_encode($line).",\"line\":\"".$line_number."\"},";
    }
    $out = rtrim($out, ',');
    $out .= "]";
} else {
    echo '{"empty":"empty"}';
}
echo $out;