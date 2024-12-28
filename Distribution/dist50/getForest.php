<?php
// Database connection
DEFINE ('DB_USER', 'root');
DEFINE ('DB_PASSWORD', '');
DEFINE ('DB_HOST', 'localhost');
DEFINE ('DB_NAME', 'forest');

// Connect to the database
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) OR die ('Could not connect to MySQL: ' . mysqli_connect_error());
mysqli_set_charset($dbc, 'utf8');

//$sql = "SELECT Diameter, status_tree, x, y FROM newforestori";
$sql = "SELECT tree_status, damage_crown, damage_stem, cut_angle, x_coordinate AS x, y_coordinate AS y FROM forest50 WHERE diameter > 30";
// Modified SQL query to filter by BlockX = 1 and BlockY = 1
//$sql = "SELECT status_tree, x, y FROM newforestori WHERE BlockX = 1 AND BlockY = 2 AND Diameter>30";
$result = mysqli_query($dbc, $sql);

$trees = array();
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $trees[] = $row;
    }
}


mysqli_close($dbc);

echo json_encode($trees);
?>
