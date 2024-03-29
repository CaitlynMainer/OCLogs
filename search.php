<?php
$matches = array();
$error = null;
$chan = $_GET['chan'];
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
  $search_string = $_GET['search'];

  if (isset($_GET['case']))
    $ignore_case = (($_GET['case'] == 0) ? false : true);
  else
    $ignore_case = false;

  $test = false;
  $file_types = array(
    "log"
  );
  $file_counter = 0;

  foreach (new DirectoryIterator("logs/#".$chan) as $file)
  {
    if (in_array($file->getExtension(), $file_types) && $file->getBasename() != "cron.log" && (!isset($_GET['file']) || $_GET['file'] == $file))
    {
      $handle = fopen($file->getPathname(), "r");
      $file_contents = array();

      while (!feof($handle))
      {
        $file_contents[] = fgets($handle);
      }

      fclose($handle);

      foreach ($file_contents as $number => $line)
      {
        $number += 1; //Array starts at 0 but lines start at 1
        $test_line = $line;
        $test_string = $search_string;

        if ($ignore_case)
        {
          $test_line = strtolower($line);
          $test_string = strtolower($search_string);
        }

        $re = '/(.*)('.htmlspecialchars($test_string).')(.*)/'.(($ignore_case) ? "i" : "");

        if (strpos($test_line, $test_string) !== false)
        {
          if (!is_array($matches[$file->getBasename()]["lines"]))
            $matches[$file->getBasename()]["lines"] = array();

          preg_match_all($re, htmlspecialchars($line), $regex_matches);
          $match = $regex_matches[1][0]."<span class='match'>".$regex_matches[2][0]."</span>".$regex_matches[3][0];
          array_push($matches[$file->getBasename()]["lines"], array("line" => $match, "number" => $number));
          $matches[$file->getBasename()]["file"] = $file->getFilename();
        }
      }
    }
  }

  ksort($matches);
  $matches = array_reverse($matches);
}
else
{
  $error = "ERROR: Search field left empty";
}
?>
<style>
a { color: rgb(140, 140, 250); }::-webkit-scrollbar-track-piece { background-color: rgba(255, 255, 255, 0.2) !important; }::-webkit-scrollbar-track { background-color: rgba(255, 255, 255, 0.3) !important; }::-webkit-scrollbar-thumb { background-color: rgba(255, 255, 255, 0.5) !important; }embed[type="application/pdf"] { filter: invert(90%); }

html { color: rgb(191, 191, 191); background: rgb(31, 31, 31) !important; }body { background-color: rgb(31, 31, 31); background-image: none !important; }input, select, textarea, button { color: rgb(191, 191, 191); background-color: rgb(31, 31, 31); }font { color: rgb(191, 191, 191); }

html { filter: contrast(100%) brightness(100%) saturate(100%); }.NIGHTEYE_Filter { width: 100%; height: 100%; position: fixed; left: 0px; top: 0px; pointer-events: none; z-index: 2147483647; }.NIGHTEYE_YellowFilter { background: rgba(255, 255, 0, 0.15); opacity: 0; }.NIGHTEYE_BlueFilter { background: rgba(0, 0, 255, 0.15); opacity: 0; }.NIGHTEYE_DimFilter { background: rgba(0, 0, 0, 0.5); opacity: 0; }.NIGHTEYE_TransformZ { transform: translateZ(0px); }

.result_display { border: 1px solid rgb(45, 92, 180); padding: 10px; margin: 5px 5px 15px; overflow-wrap: break-word; }.result_display:hover { background-color: rgb(13, 53, 53); }.result_display_title { font-weight: bold; font-size: 1em; margin-bottom: 10px; border-bottom: 1px solid rgb(45, 92, 180); }.line_number { display: inline-block; width: 45px; }.match { color: rgb(230, 153, 153); font-weight: bold; }label { cursor: pointer; }
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
    background-color: rgb(41, 41, 41);
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
      <label><input type="hidden" name="chan" value="<?= $chan ?>"/>
      <button type="submit">Search</button>
    </div>
    <div>
      <label><input type="checkbox" name="case" value="1" <?=((isset($ignore_case) && $ignore_case) ? "checked" : "")?>/> Ignore Case</label>
    </div>
  </form>
</div>
<?php
$template = "
<div class=\"result_display\">
  <div class='result_display_title'><a href='https://irclogs.pc-logix.com/view?chan={chan}&log={file_name}'> > </a>{file_name}</div>
  {lines}
  </div>
</div>";
$template_line = "
<div>
  <div class='line_number'><a href='https://irclogs.pc-logix.com/view?chan={chan}&log={filename}#L{line_number}'>L{line_number}</a></div>
  <span>{line}</span>
</div>";
$template_line_hidden = "
<div class='{filename}' style='display: none;'>
  <div class='line_number'><a href='https://irclogs.pc-logix.com/view?chan={chan}&log={filename}#L{line_number}'>L{line_number}</a></div>
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
    echo insert($template, array("file_name" => $match["file"], "lines" => $lines, "chan" => $chan));
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
