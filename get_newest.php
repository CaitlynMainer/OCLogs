<?php
header('Content-Type: application/json');
include("config.php");
include("parser.php");
$chan = $_GET['chan'];
$escaped = basename($_GET['log']);
$inline = $_GET['line'];

$mcp = new MircColorParser();
$line_number = 1;
//echo $line;
if (file_exists("logs/#" . $chan . "/" . $escaped) && ($handle = fopen("logs/#" . $chan . "/" . $escaped, "r")) !== false) {
        if ($handle) {   
		$out = "[";
            while (($line = fgets($handle)) !== false) {
				if ($inline > $line_number) {
					//echo "Skipping line: " . $line_number . "\r\n";
					$line_number++;
					continue;	
				}
                $type = "unknown";
                $line = htmlspecialchars($line);
                if (!isset($_GET['plain'])) {
                    $line = $mcp->colorize($line);
                    list($line, $type) = parseLine($line, $line_number);
                }
                if (isset($_GET['linenum'])) {
                    $line = "$line_number: " . $line;
                }
                if (!isset($_GET['nolinks'])) {
                    $line = makeClickableLinks($line);
                }
                //if (isset($_GET['nocorded'])) {
                $line = str_replace("&lt;Corded&gt; &lt;", "<img height=\"16\" width=\"16\" src=\"https://discordapp.com/assets/2c21aeda16de354ba5334551a883b481.png\" title=\"<Corded> \">&lt;", $line);
                $line = str_replace("&lt;Discord&gt; &lt;", "<img height=\"16\" width=\"16\" src=\"https://discordapp.com/assets/2c21aeda16de354ba5334551a883b481.png\" title=\"<Discord> \">&lt;", $line);
                $line = str_replace("&lt;MrConductor&gt; &lt;", "<img height=\"16\" width=\"16\" src=\"https://discordapp.com/assets/2c21aeda16de354ba5334551a883b481.png\" title=\"<MrConductor> \">&lt;", $line);
                $line = str_replace("&lt;MrConductor1&gt; &lt;", "<img height=\"16\" width=\"16\" src=\"https://discordapp.com/assets/2c21aeda16de354ba5334551a883b481.png\" title=\"<MrConductor> \">&lt;", $line);
                //}
                $buffer .= "<div id='CL$line_number' class='full_line $type'>" . $line . "</div>";
                $line_number++;
                $buffer .= "<script>$('#pageContent').css('height', $(window).height() - 20); lineNum = $line_number;</script>";
				$out .= "{\"message\":".json_encode($buffer).",\"line\":\"".$line_number."\"},";
            }
			$out = rtrim($out, ',');
			$out .= "]";
            fclose($handle);
        } else {
            // error opening the file.
        }
} else {
	echo "AAAAA";
}

echo $out;
