<?php
// Connecting, selecting database
$db_host = '10.20.25.105';
$db_name = 'postgres';
$db_user = 'postgres';
$db_pass = 'postgres';

$dbconn = pg_connect("host={$db_host} dbname={$db_name} user={$db_name} password={$db_name}")
    or die('Could not connect: ' . pg_last_error());

// Performing SQL query
$query = 'SELECT datname from pg_database';
$result = pg_query($query) or die('Query failed: ' . pg_last_error());

// var_dump($db_host);

// Printing results in HTML
echo "<table>\n";
while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
    echo "\t<tr>\n";
    foreach ($line as $col_value) {
        echo "\t\t<td>$col_value</td>\n";
    }
    echo "\t</tr>\n";
}
echo "</table>\n";

// Free resultset
pg_free_result($result);

// Closing connection
pg_close($dbconn);
?>
