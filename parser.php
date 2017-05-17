<?php
require("colorparser.php");

function makeClickableLinks($s)
{
  return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $s);
}

/**
 * @param $line
 * @param null $line_number
 * @return string
 */
function parseLine($line, $line_number = null)
{
  $type = "normal";
  /**
   * "Normal" lines.
   */
  if (preg_match('/^\[(?<time>\d{2}:\d{2}(:\d{2})?)\] <(?<nick>\S+)> (?<line>.+)$/', $line, $matches)) {
    $type = "normal";
    $line = "[$matches[time]] <$matches[nick]> $matches[line]";
    /**
     * "Join" lines.
     */
  } elseif (preg_match('/^\[(?<time>\d{2}:\d{2}(:\d{2})?)\] \*\*\* Joins: (?<nick>\S+) \((?<host>\S+)\)$/', $line, $matches)) {
    $type = "join";
    $line = "<span style=\"color: green;\">[$matches[time]] &#8680; Joins: $matches[nick] ($matches[host]) </span>";
    /**
     * "Quit" lines.
     */
  } elseif (preg_match('/^\[(?<time>\d{2}:\d{2}(:\d{2})?)\] \*\*\* Quits: (?<nick>\S+) \((?<host>\S+)\) \((?<reason>.*\))$/', $line, $matches)) {
    $type = "quit";
    $line = "<span style=\"color: red;\">[$matches[time]] &#8678; Quits: $matches[nick] ($matches[host]) ($matches[reason]) </span>";
    /**
     * "Mode" lines.
     */
  } elseif (preg_match('/^\[(?<time>\d{2}:\d{2}(:\d{2})?)\] \*\*\* (?<nick_performing>\S+) sets mode: (?<modes>[-+][ov]+([-+][ov]+)?) (?<nicks_undergoing>\S+( \S+)*)$/', $line, $matches)) {
    $type = "mode";
    $modenum          = 0;
    $nicks_undergoing = explode(' ', $matches['nicks_undergoing']);
    for ($i = 0, $j = strlen($matches['modes']); $i < $j; $i++) {
      $mode = substr($matches['modes'], $i, 1);
      if ($mode === '-' || $mode === '+') {
        $modesign = $mode;
      } else {
        $line = "<span style=\"color: darkgrey;\">[$matches[time]] $matches[nick_performing] sets mode: $modesign$mode on $nicks_undergoing[$modenum] </span>";
        $modenum++;
      }
    }
    //Actions
  } elseif (preg_match('/^\[(?<time>\d{2}:\d{2}(:\d{2})?)\] \* (?<line>(?<nick_performing>\S+) ((?<slap>[sS][lL][aA][pP][sS]( (?<nick_undergoing>\S+)( .+)?)?)|(.+)))$/', $line, $matches)) {
    $type = "action";
    $line = "<span style=\"color: purple;\">[$matches[time]] * $matches[line] </span>";
    //"Nickchange" lines.
  } elseif (preg_match('/^\[(?<time>\d{2}:\d{2}(:\d{2})?)\] \*\*\* (?<nick_performing>\S+) is now known as (?<nick_undergoing>\S+)$/', $line, $matches)) {
    $type = "nick";
    $line = "<span style=\"color: blue;\">[$matches[time]] *** $matches[nick_performing] is now known as $matches[nick_undergoing] </span>";
    //"Part" lines.
  } elseif (preg_match('/^\[(?<time>\d{2}:\d{2}(:\d{2})?)\] \*\*\* Parts: (?<nick>\S+) \((?<host>\S+)\) \((?<reason>.*\))$/', $line, $matches)) {
    $type = "part";
    $line = $line = "<span style=\"color: red;\">[$matches[time]] &#8678; Parts: $matches[nick] ($matches[host]) ($matches[reason]) </span>";
    /**$this->set_part($matches['time'], $matches['nick']);
     * "Topic" lines.
     */
  } elseif (preg_match('/^\[(?<time>\d{2}:\d{2}(:\d{2})?)\] \*\*\* (?<nick>\S+) changes topic to \'(?<line>.+)\'$/', $line, $matches)) {
    $type = "topic";
    if ($matches['line'] !== ' ') {
      //$this->set_topic($matches['time'], $matches['nick'], $matches['line']);
      $line = "<span style=\"color: orange;\">[$matches[time]] *** $matches[nick] changes topic to $matches[line] </span>";
    }
    /**
     * "Kick" lines.
     */
  } elseif (preg_match('/^\[(?<time>\d{2}:\d{2}(:\d{2})?)\] \*\*\* (?<line>(?<nick_undergoing>\S+) was kicked by (?<nick_performing>\S+) \((?<reason>.*\)))$/', $line, $matches)) {
    /** $this->set_kick($matches['time'], $matches['nick_performing'], $matches['nick_undergoing'], $matches['line']); */
    $type = "kick";
    $line = "<span style=\"color: red;\">[$matches[time]] *** $matches[nick_undergoing] was kicked by $matches[nick_performing] ($matches[reason]) </span>";
  }
  if (isset($line_number))
    $line_string = "<span id='L" . $line_number . "' class='line_number' onclick='highlight_line_by_hash()'><a href='#L" . $line_number . "'>L" . $line_number . "</a></span>";
  else
    $line_string = "";

  return array($line_string . $line, $type);
}

$target_log = $_GET['log'];

if (strtolower($target_log) == "today")
  $target_log = date("Y-m-d");
elseif (strtolower($target_log) == "yesterday")
  $target_log = date("Y-m-d", (time() - (60 * 60 * 24)));
elseif (strtolower($target_log) == "tomorrow")
  $target_log = date("Y-m-d", (time() + (60 * 60 * 24)));

$escaped = preg_replace('/[^A-Za-z0-9_\-]\./', '_', $target_log);

$buffer = ob_get_clean();
$tidy   = new tidy();
if (!isset($_GET['plain'])) {
  $buffer = "<head>
<title>OCLogs: $escaped</title>
    <script type=\"text/javascript\"
    src=\"//ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js\">
    </script>
     <script type=\"text/javascript\" language=\"javascript\">
         $(function () {
             $('#scrlBotm').click(function () {
                 $('html, body').animate({
                     scrollTop: $(document).height()
                 },
                 1500);
                 return false;
             });

             $('#scrlTop').click(function () {
                 $('html, body').animate({
                     scrollTop: '0px'
                 },
                 1500);
                 return false;
             });
         });
    </script>
    <style>
        * {
            font-family: courier;
        }
        .line_number
        {
            width: 40px;
            display: inline-block;
            text-align: right;
            margin-right: 5px;
        }
        
        .toggle_line_button
        {
            cursor: pointer;
            text-decoration: underline;
        }
    </style>
</head>
</body>";
}
$mcp = new MircColorParser();
$line_number = 1;
if ($escaped != "") {
  if (file_exists($escaped) && ($handle = fopen($escaped, "r")) !== false) {


    //$handle = @fopen($escaped, "r");
    if (!isset($_GET['plain'])) {
      date_default_timezone_set(timezone_name_from_abbr("CST"));
      $yesterday = date('Y-m-d', strtotime(str_replace(".log", "", $escaped) . ' -1 day'));
      $tomorrow  = date('Y-m-d', strtotime(str_replace(".log", "", $escaped) . ' +1 day'));
      if (file_exists($yesterday . ".log")) {
        $buffer .= "<a href=\"parser?log=$yesterday.log\"><<Prev</a> ";
      }
      if (file_exists($tomorrow . ".log")) {
        $buffer .= "<a href=\"parser?log=$tomorrow.log\">Next>></a> ";
      } else {
        $buffer .= "<a href=\"parser?log=$escaped&refresh#bottom\">Auto Refresh</a> ";
      }
      $buffer .= "<a id=\"scrlBotm\" href=\"#\">Scroll to Bottom</a><br>";
      $buffer .= "<div id='line_toggle_button_container'>Stuff goes here</div>";
    }
    if ($handle) {
      while (($line = fgets($handle)) !== false) {
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
        //}
        $buffer .= "<div id='CL$line_number' class='full_line $type'>" . $line . "</div>";
        $line_number++;
      }
      fclose($handle);
    } else {
      // error opening the file.
    }
    if (!isset($_GET['plain'])) {
      if (isset($yesterday) && file_exists($yesterday . ".log")) {
        $buffer .= "<a href=\"parser?log=$yesterday.log\"><<Prev</a> ";
      }
      if (isset($tomorrow) && file_exists($tomorrow . ".log")) {
        $buffer .= "<a href=\"parser?log=$tomorrow.log\">Next>></a> ";
      } else {
        if (!isset($_GET['refresh'])) {
          $buffer .= "<a href=\"parser?log=$escaped&refresh#bottom\">Auto Refresh</a> ";
        } else {
          $buffer .= "<a href=\"parser?log=$escaped#bottom\">Stop Auto Refresh</a> ";
          $buffer .= "<script type=\"text/javascript\">
					window.setInterval(function() {
						window.location.href = '\parser?log=$escaped&refresh#bottom';
						location.reload();
					}, 5000);
					</script>";
        }
      }
      $buffer .= "<a id=\"scrlTop\" href=\"#\">Scroll to Top</a> ";
      $buffer .= "<br><a name=\"bottom\">&nbsp;</a></body></html>";
      $buffer = $tidy->repairString($buffer, array(
        'preserve-entities' => true
      ), "utf8");
    }
    echo $buffer;
  } else {
    echo "Specified file '$escaped' doesn't exist";
  }
} else {
  echo "No log file specified";
}
?>
<script>
  function highlight_line_by_hash()
  {
    setTimeout(function() {
      var hash = window.location.hash.replace("#", "");

      var elements = document.getElementsByClassName("full_line");

      for (var i = 0; i < elements.length; i++)
        elements[i].style.backgroundColor = "";

      var element = document.getElementById("C" + hash);

      if (typeof element != "undefined")
        element.style.backgroundColor = "#c9c9c9";
    }, 100);
  }

  function generate_line_toggle_buttons() {
    var line_classes = {0: "normal", 1: "join", 2: "quit", 3: "mode", 4: "action", 5: "nick", 6: "part", 7: "topic"}; //Add new line types to this object to allow toggling them
    var container = document.getElementById("line_toggle_button_container");
    container.innerHTML = "";
    for (var type in line_classes) {
      type = line_classes[type];
      var lines = document.getElementsByClassName(type);
      container.innerHTML += "<span id='toggle_"+type+"' class='toggle_line_button' style='color: green;' onclick='toggle_lines(\""+type+"\");'>Hide "+type+" ("+lines.length+")</span> ";
    }
  }

  function toggle_lines(line_class) {
    if (typeof line_class == "string" && line_class != "") {
      var lines = document.getElementsByClassName(line_class);

      for (var i = 0; i < lines.length; i++) {
        var line = lines[i];
        if (line.style.display == "none") {
          line.style.display = "";
          document.getElementById("toggle_" + line_class).innerHTML = "Hide " + line_class + "(" + i + ")";
          document.getElementById("toggle_" + line_class).style.color = "green";
        }
        else {
          line.style.display = "none";
          document.getElementById("toggle_" + line_class).innerHTML = "Show " + line_class + "(" + i + ")";
          document.getElementById("toggle_" + line_class).style.color = "red";
        }
      }
    }
  }

  if (window.location.hash != "")
    highlight_line_by_hash();
  generate_line_toggle_buttons();
</script>
