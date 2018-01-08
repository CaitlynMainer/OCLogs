<?PHP
include("config.php");
// Create connection
$mysqli = new mysqli($config["mysql_server"], $config["mysql_user"], $config["mysql_pass"], $config["mysql_db"]);
if (mysqli_connect_errno()) {
	die("Failed to connect to MySQL: " . mysqli_connect_error());
}
$channel=$_GET[chan];
?>
<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <title>OCLogs</title>
  <link rel="stylesheet" href=".style.css">
  <script src=".sorttable.js"></script>
</head>

<body>

  <div id="container">
  
    <h1>PCL IRCLogs listing</h1>
    <?PHP
date_default_timezone_set(timezone_name_from_abbr("CST"));
$yest = date('Y-m-d', strtotime( '-1 days' ));
$today = date('Y-m-d');

?>
<a href="stats/<?PHP echo $channel; ?>/index.html">Stats</a> | <a href=".getlatest.php?chan=<?PHP echo $channel; ?>">Download All (.tgz)</a> | <a href="view?chan=<?PHP echo $channel; ?>&log=<?PHP echo $yest; ?>.log">Yesterday's Logs</a> | <a href="view?chan=<?PHP echo $channel; ?>&log=<?PHP echo $today; ?>.log">Today's Logs</a> | <form action="search" method="get" style="margin: 0; padding: 0; display:inline;"><input type="hidden" name="case" value="1" /><input style="display: inline;" type="text" name="search"><input type="hidden" name="chan" value="<?PHP echo $channel; ?>"><input style="display: inline;" type="submit" value="Search"></form>
    <table class="sortable">
      <thead>
        <tr>
          <th>Filename</th>
          <th>Lines</th>
        </tr>
      </thead>
      <tbody>
      <?php
        $stmt  = $mysqli->prepare("SELECT DISTINCT `date` FROM `logs` WHERE `channel`=? ORDER BY `date` DESC");
        $stmt2 = $mysqli->prepare("SELECT count(*) FROM `logs` WHERE `channel`=? AND `date`=?");
        $stmt2->bind_param(ss,$chan, $date);
        $chan = "#".$channel;
        $stmt->bind_param(s,$chan);
        $stmt->execute();
        $stmt->bind_result($date);
        $stmt->store_result();
        $sqltime = (microtime(true) - $time_start);
        while ($stmt->fetch()) {
            //$stmt2->execute();
            //$stmt2->bind_result($count);
            //$stmt2->fetch();
              print("
              <tr class='$class'>
                <td><a href='/view?chan=".$channel."&log=$date.log'>$date.log</a></td>
                <td>$count</td>
              </tr>");
        }
        $stmt2->close();
        $stmt->close();
      ?>
      </tbody>
    </table>
  </div>
</body>
</html>
