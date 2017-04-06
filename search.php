<?php
$matches = array();
$error = null;
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
  $search_string = htmlspecialchars($_GET['search']);
  if (isset($_GET['case']))
    $ignore_case = (($_GET['case'] == 0) ? false : true);
  else
    $ignore_case = false;
  $test = false;
  $file_types = array(
    "log"
  );
  $file_counter = 0;

  foreach (new DirectoryIterator(".") as $file)
  {
    if (in_array($file->getExtension(), $file_types) && $file->getBasename() != "cron.log" && (!isset($_GET['file']) || $_GET['file'] == $file))
    {
      $file_contents = file_get_contents($file->getPathname());
      $file_contents = explode("\n", $file_contents);
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

        $re = '/(.*)('.$test_string.')(.*)/'.(($ignore_case) ? "i" : "");
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
  .result_display
  {
    border        : 1px solid cornflowerblue;
    padding       : 10px;
    margin        : 5px;
    margin-bottom : 15px;
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
  </form>
</div>
<?php
$template = "
<div class=\"result_display\">
  <div class='result_display_title'><a href='https://oclogs.pc-logix.com/parser.php?log={file_name}'> > </a>{file_name}</div>
  {lines}
  </div>
</div>";
$template_line = "
<div>
  <div class='line_number'><a href='https://oclogs.pc-logix.com/parser.php?log={filename}#L{line_number}'>L{line_number}</a></div>
  <span>{line}</span>
</div>";
$template_line_hidden = "
<div class='{filename}' style='display: none;'>
  <div class='line_number'><a href='https://oclogs.pc-logix.com/parser.php?log={filename}#L{line_number}'>L{line_number}</a></div>
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