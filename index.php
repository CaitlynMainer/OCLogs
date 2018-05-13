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
  <link rel="stylesheet" href="style.css">
  <script src=".sorttable.js"></script>
</head>

<body>

  <div id="container">
  
	  <h1>PCL IRCLogs listing <a href="export.php">Export all</a></h1>
    <table class="sortable">
      <thead>
        <tr>
          <th>Channel Name</th>
          <th>Days Logged</th>
        </tr>
      </thead>
      <tbody>
      <?php
        $channel = "#oc";
        $stmt = $mysqli->prepare("SELECT DISTINCT `channel` FROM `logs` ORDER BY `channel` DESC");
        $stmt2 = $mysqli->prepare("SELECT DISTINCT `date` FROM `logs` WHERE `channel`=?");
        $stmt2->bind_param(s,$channel);
        $stmt->execute();
        $stmt->bind_result($channel);
        $stmt->store_result();
        $sqltime = (microtime(true) - $time_start);
        /* fetch value */
        while ($stmt->fetch()) {
            $stmt2->execute();
            $stmt2->store_result();
            $numrows = $stmt2->num_rows;
              print("
              <tr class='$class'>
                <td><a href='/export.php?channel=".str_replace("#","",$channel)."'>Export</a> <a href='/list?chan=".str_replace("#","",$channel)."'>$channel</a></td>
                <td>$numrows</td>
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
