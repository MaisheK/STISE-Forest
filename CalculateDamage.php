<?php
ini_set('max_execution_time', 900); // Increase execution time if necessary

DEFINE ('DB_USER', 'root');
DEFINE ('DB_PASSWORD', '');
DEFINE ('DB_HOST', 'localhost');
DEFINE ('DB_NAME', 'forest');

// Database connection
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) OR die ('Could not connect to MySQL: ' . mysqli_connect_error());
mysqli_set_charset($dbc, 'utf8');

// Query to fetch data from the `victim` table
$query = "SELECT victim, damage_category FROM victim";
$result = mysqli_query($dbc, $query);

if (!$result) {
    die('Error fetching victim data: ' . mysqli_error($dbc));
}

while ($row = mysqli_fetch_assoc($result)) {
    // Extract victim coordinates and damage category
    $victim_coordinates = $row['victim'];
    $damage_category = $row['damage_category'];

    // Split coordinates into x and y
    list($victim_x, $victim_y) = explode(',', $victim_coordinates);

    // Generate random volumes
    $damage_crown = 0;
    $damage_stem = 0;

    if ($damage_category == 1) {
        // Assign a random crown volume between 1 and 10
        $damage_crown = rand(1, 10);
    } elseif ($damage_category == 2) {
        // Assign a random stem volume between 1 and 100
        $damage_stem = rand(1, 100);
    }

    // Update the `tree_data` table with the random volumes
    $update_query = "UPDATE tree_data 
                     SET damage_crown = $damage_crown, damage_stem = $damage_stem 
                     WHERE x_coordinate = $victim_x AND y_coordinate = $victim_y";

    $update_result = mysqli_query($dbc, $update_query);
    if (!$update_result) {
        die('Error updating tree data: ' . mysqli_error($dbc));
    }
}

echo "Random volume updates completed successfully.";

mysqli_close($dbc);
?>
