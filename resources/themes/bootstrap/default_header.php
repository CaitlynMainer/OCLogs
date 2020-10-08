<?PHP
date_default_timezone_set(timezone_name_from_abbr("CST"));
$yest = date('Y-m-d', strtotime( '-1 days' ));
$today = date('Y-m-d');
$chan = str_replace("logs/#", "", $_GET['dir']);
?>
<?PHP if ($_GET['dir']) { ?> <a href="ssstats/<?PHP echo $chan ?>/">Stats</a> | <a href=".getlatest.php">Download All (.tgz)</a> | <a href="view.php?chan=<?PHP echo $chan; ?>&log=<?PHP echo $yest; ?>.log">Yesterday's Logs</a> | <a href="view.php?chan=<?PHP echo $chan; ?>&log=<?PHP echo $today; ?>.log">Today's Logs</a> | <form action="search" method="get" style="margin: 0; padding: 0; display:inline;"><input type="hidden" name="chan" value="<?= $chan ?>"/><input type="hidden" name="case" value="1" /><input style="display: inline;" type="text" name="search"><label><input style="display: inline;" type="submit" value="Search"></label></form> <?PHP } ?>