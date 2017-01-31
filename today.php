<?php
date_default_timezone_set(timezone_name_from_abbr("CST"));
$date = date('Y-m-d');
echo "<pre>";
echo htmlspecialchars(file_get_contents( $date . ".log" )); // get the contents, and echo it out.
?>