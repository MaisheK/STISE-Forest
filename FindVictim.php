<?php
ini_set('max_execution_time', 300); // 300 seconds = 5 minutes
// Database connection settings
DEFINE ('DB_USER', 'root');
DEFINE ('DB_PASSWORD', '');
DEFINE ('DB_HOST', 'localhost');
DEFINE ('DB_NAME', 'forest');

// Make the connection:
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) OR die ('Could not connect to MySQL: ' . mysqli_connect_error());

// Set the encoding...
mysqli_set_charset($dbc, 'utf8');

// Fetching data from the damagetree table
$query = "SELECT cut_tree, victim, damage_category FROM damagetree";
$result = mysqli_query($dbc, $query);

if (!$result) {
    die('Error executing query: ' . mysqli_error($dbc));
}

// Inserting the data into the victim table
while ($row = mysqli_fetch_assoc($result)) {
    $cut_tree = $row['cut_tree'];
    $victim = $row['victim'];
    $damage_category = $row['damage_category'];

    // Prepare the insert query for the victim table
    $insert_query = "INSERT INTO victim (cut_tree, victim, damage_category) VALUES ('$cut_tree', '$victim', $damage_category)";
    $insert_result = mysqli_query($dbc, $insert_query);

    if (!$insert_result) {
        die('Error inserting into victim table: ' . mysqli_error($dbc));
    }

}

// After all data is inserted, output a success message
echo "The data has been successfully transferred to the victim table.";

// Close the database connection
mysqli_close($dbc);
?>
