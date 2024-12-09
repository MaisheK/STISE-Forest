<?php

echo "</table>";

DEFINE ('DB_USER', 'root');
DEFINE ('DB_PASSWORD', '');
DEFINE ('DB_HOST', 'localhost');
DEFINE ('DB_NAME', 'forest');

// Make the connection:
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) OR die ('Could not connect to MySQL: ' . mysqli_connect_error());

// Set the encoding...
mysqli_set_charset($dbc, 'utf8');

// Perform your calculations here using the existing data in the database
$sql = "SELECT treenum AS id, species, diameter, height, x_coordinate, y_coordinate, cut_angle 
        FROM tree_data 
        WHERE tree_status = 'Cut'";
$result = mysqli_query($dbc, $sql);

if (!$result) {
    die('Error executing query: ' . mysqli_error($dbc));
}

function insert_damagetree_data($cut_tree_coordinate, $victim_coordinate, $categoryDamage) {
    global $dbc;
    $insert_query = "INSERT INTO damagetree (cut_tree, victim, damage_category) VALUES ('$cut_tree_coordinate', '$victim_coordinate', $categoryDamage)";
    $result3 = mysqli_query($dbc, $insert_query);
    if (!$result3) {
        die('Error inserting damagetree data: ' . mysqli_error($dbc));
    }
}

// Update damage into tree_data
function update_tree_data_damage($cut_tree_coordinate, $victim_coordinate, $categoryDamage, $victim_x, $victim_y) {
    global $dbc;
    
    // Calculate damage based on category
    $damage = 0;
    if ($categoryDamage == 1) {
        // Category 1: Damage > 50%
        $damage = rand(51, 100);  // Random damage between 51 and 100 percent
    } elseif ($categoryDamage == 2) {
        // Category 2: Damage < 50%
        $damage = rand(10, 49);   // Random damage between 10 and 49 percent
    }

    // Update the tree_data table with the calculated damage
    $update_query = "UPDATE tree_data 
                     SET damage = $damage, tree_status = 'victim' 
                     WHERE x_coordinate = $victim_x AND y_coordinate = $victim_y";
                     
    $result3 = mysqli_query($dbc, $update_query);
    if (!$result3) {
        die('Error updating tree_data table: ' . mysqli_error($dbc));
    }
}

while ($row = mysqli_fetch_assoc($result)) {
    // Extracting coordinates and other parameters
    $cut_tree_coordinate = $row['x_coordinate'] . ',' . $row['y_coordinate'];
    $x0 = $row['x_coordinate'];
    $y0 = $row['y_coordinate'];
    $cutAngle = $row['cut_angle'];
    $stemHeight = $row['height'];
    $buffer = 10;  // 5 + 5 as described

    // Quadrant checks and SQL query formation
    if ($cutAngle >= 0 && $cutAngle < 90) {
        // Quadrant I: 0 - 90 degrees
        $x_upper = $x0 + $stemHeight + $buffer;
        $y_upper = $y0 + $stemHeight + $buffer;
        $count_query = "SELECT x_coordinate, y_coordinate FROM tree_data WHERE tree_status != 'Cut' AND x_coordinate > $x0 AND x_coordinate < $x_upper AND y_coordinate > $y0 AND y_coordinate < $y_upper";
    }
    elseif ($cutAngle >= 90 && $cutAngle < 180) {
        // Quadrant II: 90 - 180 degrees
        $x_upper = $x0 + $stemHeight + $buffer;
        $y_upper = $y0 - $stemHeight - $buffer;
        $count_query = "SELECT x_coordinate, y_coordinate FROM tree_data WHERE tree_status != 'Cut' AND x_coordinate > $x0 AND x_coordinate < $x_upper AND y_coordinate > $y0 AND y_coordinate < $y_upper";
    }
    elseif ($cutAngle >= 180 && $cutAngle < 270) {
        // Quadrant III: 180 - 270 degrees
        $x_upper = $x0 - $stemHeight - $buffer;
        $y_upper = $y0 - $stemHeight - $buffer;
        $count_query = "SELECT x_coordinate, y_coordinate FROM tree_data WHERE tree_status != 'Cut' AND x_coordinate > $x0 AND x_coordinate < $x_upper AND y_coordinate > $y0 AND y_coordinate < $y_upper";
    }
    elseif ($cutAngle >= 270 && $cutAngle < 360) {
        // Quadrant IV: 270 - 360 degrees
        $x_upper = $x0 - $stemHeight - $buffer;
        $y_upper = $y0 + $stemHeight + $buffer;
        $count_query = "SELECT x_coordinate, y_coordinate FROM tree_data WHERE tree_status != 'Cut' AND x_coordinate > $x0 AND x_coordinate < $x_upper AND y_coordinate > $y0 AND y_coordinate < $y_upper";
    }

    // Execute the query to find affected trees
    $result1 = mysqli_query($dbc, $count_query);

    if (!$result1) {
        die('Error executing query: ' . mysqli_error($dbc));
    }

    while ($row1 = mysqli_fetch_assoc($result1)) {
        $radian = deg2rad($cutAngle);
        $unknownTree_X = $row1['x_coordinate'];
        $unknownTree_Y = $row1['y_coordinate'];

        // Calculating the distance and checking conditions
        $y1 = ($unknownTree_X / tan($radian + 1));
        $y2 = ($unknownTree_X / tan($radian - 1));

        $x1_crown = $x0 + ($stemHeight + 5) * sin($radian);
        $y1_crown = $y0 + ($stemHeight + 5) * cos($radian);

        $distance = sqrt(pow(($x1_crown - $unknownTree_X), 2) + pow(($y1_crown - $unknownTree_Y), 2));

        if ($distance <= $stemHeight) {
            // Trees directly under the falling path or within the impact area
            insert_damagetree_data($cut_tree_coordinate, $row1['x_coordinate'] . ',' . $row1['y_coordinate'], 1);
            // Update tree_data and set status to 'victim'
            update_tree_data_damage($cut_tree_coordinate, $row1['x_coordinate'] . ',' . $row1['y_coordinate'], 1, $row1['x_coordinate'], $row1['y_coordinate']);
        } elseif ($distance <= ($stemHeight + 5)) {
            // Trees near the crown edge, affected but survivable
            insert_damagetree_data($cut_tree_coordinate, $row1['x_coordinate'] . ',' . $row1['y_coordinate'], 2);
            // Update tree_data and set status to 'victim'
            update_tree_data_damage($cut_tree_coordinate, $row1['x_coordinate'] . ',' . $row1['y_coordinate'], 2, $row1['x_coordinate'], $row1['y_coordinate']);
        }

        /*      if ($unknownTree_Y > $y1 && $unknownTree_Y < $y2) {
            insert_damagetree_data($cut_tree_coordinate, $row1['x_coordinate'] . ',' . $row1['y_coordinate'], 1);
        }

        if ($distance <= 5) {
            insert_damagetree_data($cut_tree_coordinate, $row1['x_coordinate'] . ',' . $row1['y_coordinate'], 2);
        }
*/
    }
}

// After all data is processed and inserted, output a success message
echo "The data has been successfully stored in the database.";

?>
