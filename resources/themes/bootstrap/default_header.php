<?PHP
date_default_timezone_set(timezone_name_from_abbr("CST"));
$yest = date('Y-m-d', strtotime( '-1 days' ));
$today = date('Y-m-d');

?>
<a href="stats.html">Stats</a> | <a href=".getlatest.php">Download All (.tgz)</a> | <a href="parser.php?log=<?PHP echo $yest; ?>.log">Yesterday's Logs</a> | <a href="parser.php?log=<?PHP echo $today; ?>.log">Today's Logs</a> | <form action="search" method="get" style="margin: 0; padding: 0; display:inline;"><input type="hidden" name="case" value="1" /><input style="display: inline;" type="text" name="search"><input style="display: inline;" type="submit" value="Search"></form>