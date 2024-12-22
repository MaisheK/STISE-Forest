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

    // Initialize damage values
    $damage_crown = 0;
    $damage_stem = 0;

    if ($damage_category == 2) {
        // Assign crown damage: Random percentage between 0 and 50% of volume range 1-10 m³
        $percentage_crown = rand(0, 50); // Percentage between 0 and 50%
        $damage_crown = (rand(1, 10) * $percentage_crown) / 100; // Applying percentage to the volume
    } elseif ($damage_category == 1) {
        // Assign stem damage: Random percentage between 1 and 100% of volume range 1-100 m³
        $percentage_stem = rand(1, 100); // Percentage between 1 and 100%
        $damage_stem = (rand(1, 100) * $percentage_stem) / 100; // Applying percentage to the volume
    }

    // Update the `tree_data` table with the random volumes
    $update_query = "UPDATE tree_data 
                     SET 
                     damage_crown = $damage_crown, 
                     damage_stem = $damage_stem,
                     tree_status = IF($damage_crown > 0, 'victim', tree_status) 
                     WHERE x_coordinate = $victim_x AND y_coordinate = $victim_y";

    $update_result = mysqli_query($dbc, $update_query);
    if (!$update_result) {
        die('Error updating tree data: ' . mysqli_error($dbc));
    }
}

echo "Random volume updates completed successfully.";

mysqli_close($dbc);
?>
