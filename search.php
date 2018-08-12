<?php
$time_start = microtime(true); 
$matches = array();
$error = null;
$chan = $_GET['chan'];
include("config.php");

// Create connection
$mysqli = new mysqli($config["mysql_server"], $config["mysql_user"], $config["mysql_pass"], $config["mysql_db"]);
if (mysqli_connect_errno()) {
	die("Failed to connect to MySQL: " . mysqli_connect_error());
}
/**
 * @param $target {String}
 * @param $stringOrList {Array|String}
 * @return String
 */
function insert($target, $stringOrList)
{
  if ($target == "")
    return "Err:Empty input string";

  if (is_array($stringOrList))
  {
    foreach ($stringOrList as $key => $value)
    {
      $rpl = "{" . $key . "}";
      $target = str_replace($rpl, $value, $target);
    }
  }
  else
  {
    $target = str_replace("{0}", $stringOrList, $target);
  }

  if ($target == "")
    return "Err:Empty output string";
  else
    return $target;
}

$ignore_case = true;
if (isset($_GET['search']) && !empty($_GET['search']))
{
  $search_string = urldecode($_GET['search']);
  if (isset($_GET['case']))
    $ignore_case = (($_GET['case'] == 0) ? false : true);
  else
    $ignore_case = false;
  $file_counter = 0;
$stmt = $mysqli->prepare("SELECT `date`, `timestamp`, `message`, `linenum` FROM `logs` WHERE MATCH(message) AGAINST(? IN BOOLEAN MODE) AND `channel`=?");
$search = "\"".$_GET['search']."\"";
$channel = "#".$chan;
$stmt->bind_param(ss,$search, $channel);
$stmt->execute();
    $stmt->bind_result($date, $timestamp, $line, $linenum);

    $sqltime = (microtime(true) - $time_start);
    /* fetch value */
    $i = 0;
    while ($stmt->fetch()) {
        $number += 1; //Array starts at 0 but lines start at 1
        $test_line = $line;
        $test_string = $search_string;
        if ($ignore_case) {
          $test_line = strtolower($line);
          $test_string = strtolower($search_string);
        }
echo htmlspecialchars($test_string);
	    htmlspecialchars($test_line);
	    echo "<br>";
        $re = '/(.*)('.htmlspecialchars($test_string).')(.*)/'.(($ignore_case) ? "i" : "");
        if (strpos($test_line, $test_string) !== false) {
          if (!is_array($matches[$date]["lines"]))
            $matches[$date]["lines"] = array();
          preg_match_all($re, htmlspecialchars($test_line), $regex_matches);
          $match = $regex_matches[1][0]."<span class='match'>".$regex_matches[2][0]."</span>".$regex_matches[3][0];
          array_push($matches[$date]["lines"], array("line" => $match, "number" => $linenum));
          $matches[$date]["file"] = $date.".log";
        }
        $i++;
      }
      $mainloop = (microtime(true) - $time_start);
      $stmt->close();
  ksort($matches);
  $matches = array_reverse($matches);
}
else
{
  $error = "ERROR: Search field left empty";
}
?>
<style>
  .result_display
  {
    border        : 1px solid cornflowerblue;
    padding       : 10px;
    margin        : 5px;
    margin-bottom : 15px;
    word-wrap: break-word;
  }

  .result_display:hover
  {
    background-color: azure;
  }

  .result_display_title
  {
    font-weight   : bold;
    font-size     : 1em;
    margin-bottom : 10px;
    border-bottom: 1px solid cornflowerblue;
  }

  .line_number
  {
    display: inline-block;
    width: 45px;
  }

  .match
  {
    color       : red;
    font-weight : bold;
  }

  label
  {
    cursor: pointer;
  }
</style>
<div>
  <span><button type="button" onclick="history.back()">Back</button></span>
  <form action="" method="get">
    <div>
      <label><input type="search" placeholder="Search" name="search" value="<?= ((isset($_GET['search'])) ? $_GET['search'] : "") ?>"/></label>
      <button type="submit">Search</button>
    </div>
    <div>
      <label><input type="checkbox" name="case" value="1" <?=((isset($ignore_case) && $ignore_case) ? "checked" : "")?>/> Ignore Case</label>
    </div>
    <input type="hidden" name="chan" value="<?PHP echo $chan; ?>"/>
  </form>
</div>
<?php
$template = "
<div class=\"result_display\">
  <div class='result_display_title'><a href='/view.php?chan=$chan&log={file_name}'> > </a>{file_name}</div>
  {lines}
  </div>
</div>";
$template_line = "
<div>
  <div class='line_number'><a href='/view.php?chan=$chan&log={filename}#L{line_number}'>L{line_number}</a></div>
  <span>{line}</span>
</div>";
$template_line_hidden = "
<div class='{filename}' style='display: none;'>
  <div class='line_number'><a href='/view.php?chan=$chan&log={filename}#L{line_number}'>L{line_number}</a></div>
  <span>{line}</span>
</div>";

if (count($matches) > 0)
{
  foreach ($matches as $match)
  {
    $lines = "";
    $line_counter = 0;
    foreach ($match["lines"] as $line)
    {
      if ($line_counter < 5)
        $lines .= insert($template_line, array("line" => $line["line"], "line_number" => $line["number"], "filename" => $match["file"]));
      else
        $lines .= insert($template_line_hidden, array("line" => $line["line"], "line_number" => $line["number"], "filename" => $match["file"]));;
      $line_counter++;
    }
    if ($line_counter > 5)
      $lines .= "<div id='toggle_button_".$match["file"]."' style='cursor: pointer; color: blue; text-decoration: underline;' onclick='toggle_lines_for_file(\"".$match["file"]."\")'><span></span>" . (count($match["lines"]) - 5) . "<span> more...</span></div>";
    echo insert($template, array("file_name" => $match["file"], "lines" => $lines));
  }
}
elseif ($error)
  echo "<div>".$error."</div>";
else
  echo "<div>No match found in $file_counter files.</div>";
?>
<div><button type="button" onclick="history.back()">Back</button></div>
<script>
  function toggle_lines_for_file(filename)
  {
    var lines = document.getElementsByClassName(filename);

    var display = "none";
    if (lines.length > 0 && lines[0].style.display == "none")
      display = "";
    for (var i = 0; i < lines.length; i++)
    {
      lines[i].style.display = display;
    }
    var hider = document.getElementById("toggle_button_"+filename);
    if (display == "")
    {
      hider.children[0].innerHTML = "Hide ";
      hider.children[1].innerHTML = "";
    }
    else
    {
      hider.children[0].innerHTML = "";
      hider.children[1].innerHTML = " more...";
    }
  }
</script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-66594701-2', 'auto');
  ga('send', 'pageview');

</script>
<?PHP
// Anywhere else in the script
echo 'Total execution time in seconds: ' . (microtime(true) - $time_start);
echo ' | SQL Query time in seconds: ' . $sqltime;
echo ' | Main loop time in seconds: ' . $mainloop;
echo ' | Total loops: ' . $i;
