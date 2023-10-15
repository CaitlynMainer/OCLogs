<?php
include("config.php");
include("parser.php");
$channel = "#" . $_GET['chan'];
$chan    = $_GET['chan'];


$target_log = $_GET['log'];

if (strtolower($target_log) == "today")
    $target_log = date("Y-m-d");
elseif (strtolower($target_log) == "yesterday")
    $target_log = date("Y-m-d", (time() - (60 * 60 * 24)));
elseif (strtolower($target_log) == "tomorrow")
    $target_log = date("Y-m-d", (time() + (60 * 60 * 24)));

//$escaped = preg_replace('/[^A-Za-z0-9_\-]\./', '_', $target_log);
$escaped = basename($target_log);

$buffer = ob_get_clean();
$tidy   = new tidy();
if (!isset($_GET['plain'])) {
    $buffer = "<head>
<title>OCLogs: $escaped</title>
    <script type=\"text/javascript\"
    src=\"//ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js\">
    </script>
     <script type=\"text/javascript\" language=\"javascript\">
     var lineNum = 1;
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
    <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-66594701-2', 'auto');
  ga('send', 'pageview');
</script>
    <style>
    .box{
    display: none;
    width: 100%;
}

a:hover + .box,.box:hover{
    display: block;
    position: relative;
    z-index: 100;
}
    
    a { color: rgb(140, 140, 250); }::-webkit-scrollbar-track-piece { background-color: rgba(255, 255, 255, 0.2) !important; }::-webkit-scrollbar-track { background-color: rgba(255, 255, 255, 0.3) !important; }::-webkit-scrollbar-thumb { background-color: rgba(255, 255, 255, 0.5) !important; }embed[type='application/pdf'] { filter: invert(90%); }

html { color: rgb(191, 191, 191); background: rgb(31, 31, 31) !important; }body { background-color: rgb(31, 31, 31); background-image: none !important; }input, select, textarea, button { color: rgb(191, 191, 191); background-color: rgb(31, 31, 31); }font { color: rgb(191, 191, 191); }

html { filter: contrast(100%) brightness(100%) saturate(100%); }.NIGHTEYE_Filter { width: 100%; height: 100%; position: fixed; left: 0px; top: 0px; pointer-events: none; z-index: 2147483647; }.NIGHTEYE_YellowFilter { background: rgba(255, 255, 0, 0.15); opacity: 0; }.NIGHTEYE_BlueFilter { background: rgba(0, 0, 255, 0.15); opacity: 0; }.NIGHTEYE_DimFilter { background: rgba(0, 0, 0, 0.5); opacity: 0; }.NIGHTEYE_TransformZ { transform: translateZ(0px); }
    html, body {
        overflow:hidden;
    }
        * {
            font-family: monospace;
            
    margin-top: 0px;
    margin-bottom: 0px;
    margin-right: 0px;
    margin-left: 0px;
    padding-top: 0px;
    padding-bottom: 0px;
    padding-right: 0px;
    padding-left: 0px;
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
        #pageContent {
  overflow:auto;
  height:100%;
    margin-top: 0px;
    margin-bottom: 0px;
    margin-right: 0px;
    margin-left: 0px;
    padding-top: 0px;
    padding-bottom: 0px;
    padding-right: 0px;
    padding-left: 0px;
        }
    </style>
</head>
</body><div id='pageContent'>";
}
$mcp         = new MircColorParser();
$line_number = 1;
if ($escaped != "") {

    if (file_exists("logs/#" . $chan . "/" . $escaped) && ($handle = fopen("logs/#" . $chan . "/" . $escaped, "r")) !== false) {

        //$handle = @fopen($escaped, "r");
        if (!isset($_GET['plain'])) {
            date_default_timezone_set(timezone_name_from_abbr("CST"));
            $yesterday = date('Y-m-d', strtotime(str_replace(".log", "", $escaped) . ' -1 day'));
            $tomorrow  = date('Y-m-d', strtotime(str_replace(".log", "", $escaped) . ' +1 day'));
            if (file_exists("logs/#" . $chan . "/" . $yesterday . ".log")) {
                $buffer .= "<a href=\"view?chan=$chan&log=$yesterday.log\">&lt;&lt;Prev</a> ";
            }
            if (file_exists("logs/#" . $chan . "/" . $tomorrow . ".log")) {
                $buffer .= "<a href=\"view?chan=$chan&log=$tomorrow.log\">Next>></a> ";
            } else {
                $buffer .= "<a href=\"view?chan=$chan&log=$escaped&refresh#bottom\">Auto Refresh</a> ";
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
                $line = str_replace("&lt;Corded&gt; &lt;", "<img height=\"16\" width=\"16\" src=\"/resources/discord.png\" title=\"<Corded> \">&lt;", $line);
                $line = str_replace("&lt;Discord&gt; &lt;", "<img height=\"16\" width=\"16\" src=\"/resources/discord.png\" title=\"<Discord> \">&lt;", $line);
                $line = str_replace("&lt;MrConductor&gt; &lt;", "<img height=\"16\" width=\"16\" src=\"/resources/discord.png\" title=\"<MrConductor> \">&lt;", $line);
                $line = str_replace("&lt;MrConductor1&gt; &lt;", "<img height=\"16\" width=\"16\" src=\"/resources/discord.png\" title=\"<MrConductor> \">&lt;", $line);
                //}
                $buffer .= "<div id='CL$line_number' class='full_line $type'>" . $line . "</div>";
                $line_number++;
                $buffer .= "<script>$('#pageContent').css('height', $(window).height() - 20); lineNum = $line_number;</script>";
            }
            fclose($handle);
        } else {
            // error opening the file.
        }
		$buffer .= "</div>";
        if (!isset($_GET['plain'])) {
            if (isset($yesterday) && file_exists("logs/#" . $chan . "/" . $yesterday . ".log")) {
                $buffer .= "<a href=\"view?chan=$chan&log=$yesterday.log\">&lt;&lt;Prev</a> ";
            }
            if (isset($tomorrow) && file_exists("logs/#" . $chan . "/" . $tomorrow . ".log")) {
                $buffer .= "<a href=\"view?chan=$chan&log=$tomorrow.log\">Next>></a> ";
            } else {
                if (!isset($_GET['refresh'])) {
                    $buffer .= "<a href=\"view?chan=$chan&log=$escaped&refresh#bottom\">Auto Refresh</a> ";
                } else {
                    $buffer .= "<a href=\"view?chan=$chan&log=$escaped#bottom\">Stop Auto Refresh</a> ";
                    $buffer .= "<script type=\"text/javascript\">
                    window.setInterval(function() {
                        var dataString = 'chan=" . $chan . "&log=" . $escaped . "&line='+lineNum;
                        $.ajax({ 
                            type: 'GET', 
                            url: 'get_newest',
                            data: dataString,
                            contentType: 'application/json',
                            dataType: 'json',
                            success: function (json) {
                              if (json) {
                              data = $.parseJSON(json.message);
                              if(json.length > 0){
                                for(i=0; i<json.length; i++){
                                  if (json[i].message) {
                                    var out = document.getElementById('pageContent');
                                     // allow 1px inaccuracy by adding 1
                                     var isScrolledToBottom = out.scrollHeight - out.clientHeight <= out.scrollTop + 1;
                                     var newElement = document.createElement('div');
                                     newElement.innerHTML = json[i].message;
                                     out.appendChild(newElement);
                                     // scroll to bottom if isScrolledToBottom
                                     if(isScrolledToBottom)
                                       out.scrollTop = out.scrollHeight - out.clientHeight;
                                     //$('#pageContent').append('Meh</br>');
                                     lineNum++;
                                  }
                                }
                              }
                                }
                            }, 
                            error : function(xhr,status,error){
                                alert(status);
                            },
                        });
                        $('#pageContent').css('height', $(window).height() - 20);
                    }, 1000);
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
        element.style.backgroundColor = "#333333";
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
