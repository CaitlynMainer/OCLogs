<?PHP

require("colorparser.php");

function makeClickableLinks($s)
{
  return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.:#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank" rel="nofollow">$1</a>', $s);
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
  } elseif (preg_match('/^\[(?<time>\d{2}:\d{2}(:\d{2})?)\] \*\*\* Quits: (?<nick>\S+)\s?\((?<host>\S+)\) \((?<reason>.*)\)$/', $line, $matches)) {
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
    //"Discord Actions" lines.
  } elseif (preg_match('/^\[(?<time>\d{2}:\d{2}(:\d{2})?)\] (?<nick_performing> \*\S+) (?<line>.+)$/', $line, $matches)) {
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
