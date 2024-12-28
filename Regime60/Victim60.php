<?php
ini_set('max_execution_time', 900); // Increase execution time if necessary

// Database connection settings
DEFINE('DB_USER', 'root');
DEFINE('DB_PASSWORD', '');
DEFINE('DB_HOST', 'localhost');
DEFINE('DB_NAME', 'forest');

// Make the database connection
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) OR die('Could not connect to MySQL: ' . mysqli_connect_error());
mysqli_set_charset($dbc, 'utf8');

// Step 1: Transfer data from `damagetree60` to `victim60`
$query = "SELECT cut_tree, victim, damage_category, treenum FROM damagetree60";
$result = mysqli_query($dbc, $query);

if (!$result) {
    die('Error fetching data from damagetree60: ' . mysqli_error($dbc));
}

while ($row = mysqli_fetch_assoc($result)) {
    $cut_tree = $row['cut_tree'];
    $victim = $row['victim'];
    $damage_category = $row['damage_category'];
    $treenum = $row['treenum'];

    // Insert data into `victim60` table
    $insert_query = "INSERT INTO victim60 (cut_tree, victim, damage_category, treenum) 
                     VALUES ('$cut_tree', '$victim', $damage_category, '$treenum')";
    $insert_result = mysqli_query($dbc, $insert_query);

    if (!$insert_result) {
        die('Error inserting into victim60 table: ' . mysqli_error($dbc));
    }
}

// Output success message for data transfer
echo "The data has been successfully transferred to the victim60 table.<br>";

// Step 2: Update `forest60` table with random volumes based on `treenum` from `victim60` data
$query = "SELECT treenum, damage_category FROM victim60";
$result = mysqli_query($dbc, $query);

if (!$result) {
    die('Error fetching victim60 data: ' . mysqli_error($dbc));
}

while ($row = mysqli_fetch_assoc($result)) {
    // Extract `treenum` and damage category
    $treenum = $row['treenum'];
    $damage_category = $row['damage_category'];

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

    // Update the `forest60` table using `treenum`
    $update_query = "UPDATE forest60 
                     SET 
                     damage_crown = $damage_crown, 
                     damage_stem = $damage_stem,
                     tree_status = IF($damage_stem > 0, 'victim', tree_status)
                     WHERE treenum = '$treenum'";

    $update_result = mysqli_query($dbc, $update_query);
    if (!$update_result) {
        die('Error updating tree data: ' . mysqli_error($dbc));
    }
}

// Output success message for updates
echo "Random volume updates completed successfully.";

// Close the database connection
mysqli_close($dbc);
?>
