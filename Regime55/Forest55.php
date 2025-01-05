<?php
ini_set('max_execution_time', 900);

DEFINE ('DB_USER', 'root');
DEFINE ('DB_PASSWORD', '');
DEFINE ('DB_HOST', 'localhost');
DEFINE ('DB_NAME', 'forest');

// Connect to the database
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) OR die ('Could not connect to MySQL: ' . mysqli_connect_error());
mysqli_set_charset($dbc, 'utf8');

// Variables for forest generation
$NoBlockX = 10;
$NoBlockY = 10;

// ListSpecies array (taken from database)
$ListSpecies = [];
$sql = "SELECT No, species FROM speciesname";
$result = $dbc->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ListSpecies[$row['No']] = $row['species'];
    }
} else {
    die("No species found in the database.");
}

// Fetch tree data from the tree_data table
$sql = "SELECT * FROM tree_data";
$treeDataResult = $dbc->query($sql);

if ($treeDataResult->num_rows > 0) {
    while ($tree = $treeDataResult->fetch_assoc()) {
        $blockx = $tree['blockx'];
        $blocky = $tree['blocky'];
        $x = $tree['x_coordinate'];
        $y = $tree['y_coordinate'];
        $TreeNum = $tree['treenum'];
        $species = $tree['species'];
        $diameter = $tree['diameter'];
        $height = $tree['height'];
        $volume = $tree['volume'];
        $SPEC_Gr = $tree['spgroup'];
        $diameterclass = $tree['diameterclass'];
        $cut_angle = $tree['cut_angle'];

        // Determine Status
        $status = 'Keep';
        $prod = 0;

        if (in_array($SPEC_Gr, [1, 2, 3, 5]) && $diameter > 55) {
            $status = 'Cut';
            $prod = $volume;

            // Assign a random cut angle if it hasn't been set
            if ($cut_angle === null) {
                $cut_angle = rand(0, 360);
            }
        } else {
            // Ensure cut_angle is set to 0 if status is 'Keep'
            $cut_angle = 0;
        }

        // Insert updated tree data into the forest50 table
        $sqlInsert = "INSERT INTO forest55 (
            blockx, blocky, x_coordinate, y_coordinate, treenum, 
            species, diameter, height, volume, spgroup, 
            diameterclass, tree_status, PROD, cut_angle, damage_stem, damage_crown,
            Growth_D30, Volume30, PROD30
        ) VALUES (
            $blockx, $blocky, $x, $y, '$TreeNum', 
            '$species', $diameter, $height, $volume, $SPEC_Gr, 
            $diameterclass, '$status'," . number_format($prod, 2) . ", " . ($cut_angle !== null ? $cut_angle : "NULL") . ", NULL, NULL, NULL, NULL, NULL
        )";

        if ($dbc->query($sqlInsert) !== TRUE) {
            echo "Error: " . $sqlInsert . "<br>" . $dbc->error;
        }
    }
} else {
    die("No tree data found in the tree_data table.");
}

// Close the connection
mysqli_close($dbc);

?>
