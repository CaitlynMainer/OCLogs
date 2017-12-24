<?PHP
include("config.php");
// Create connection
$mysqli = new mysqli($config["mysql_server"], $config["mysql_user"], $config["mysql_pass"], $config["mysql_db"]);
if (mysqli_connect_errno()) {
	die("Failed to connect to MySQL: " . mysqli_connect_error());
}
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
  
    <h1>OCLogs listing</h1>
    <?PHP
date_default_timezone_set(timezone_name_from_abbr("CST"));
$yest = date('Y-m-d', strtotime( '-1 days' ));
$today = date('Y-m-d');

?>
<a href="stats.html">Stats</a> | <a href=".getlatest.php">Download All (.tgz)</a> | <a href="parser.php?log=<?PHP echo $yest; ?>.log">Yesterday's Logs</a> | <a href="parser.php?log=<?PHP echo $today; ?>.log">Today's Logs</a> | <form action="search" method="get" style="margin: 0; padding: 0; display:inline;"><input type="hidden" name="case" value="1" /><input style="display: inline;" type="text" name="search"><input style="display: inline;" type="submit" value="Search"></form>
    <table class="sortable">
      <thead>
        <tr>
          <th>Filename</th>
        </tr>
      </thead>
      <tbody>
      <?php
$stmt = $mysqli->prepare("SELECT DISTINCT `date` FROM `logs` WHERE `channel`='#oc' ORDER BY `date` DESC");
$stmt->execute();
    $stmt->bind_result($date);

    $sqltime = (microtime(true) - $time_start);
    /* fetch value */
    $i = 0;
    while ($stmt->fetch()) {
          print("
          <tr class='$class'>
            <td><a href='/parser?log=$date.log'>$date.log</a></td>
          </tr>");
          }
          $stmt->close();
      ?>
      </tbody>
    </table>
  </div>
</body>
</html>
