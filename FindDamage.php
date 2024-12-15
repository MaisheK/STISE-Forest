<?php
ini_set('max_execution_time', 300); // 300 seconds = 5 minutes
echo "</table>";

DEFINE ('DB_USER', 'root');
DEFINE ('DB_PASSWORD', '');
DEFINE ('DB_HOST', 'localhost');
DEFINE ('DB_NAME', 'forest');

// Make the connection:
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) OR die ('Could not connect to MySQL: ' . mysqli_connect_error());

// Set the encoding...
mysqli_set_charset($dbc, 'utf8');

// Construct the SQL query to retrieve data for all "Cut" trees
$sql = "SELECT treenum AS id, species, diameter, height, x_coordinate AS x, y_coordinate AS y, cut_angle 
        FROM tree_data 
        WHERE tree_status = 'Cut'";
$result = mysqli_query($dbc, $sql);

// Check if the query executed successfully
if (!$result) {
    die('Error executing query: ' . mysqli_error($dbc));
}

while ($row = mysqli_fetch_assoc($result)) {
    // Extracting coordinates from the row
    $cut_tree_coordinate = $row['x'] . ',' . $row['y']; // Coordinate of the cut tree
    $x0 = $row['x'];
    $y0 = $row['y'];
    $cutAngle = $row['cut_angle'];
    $stemHeight = $row['height'];

    $buffer = 10;  // 5 + 5 as described

    // Determine the quadrant and set appropriate ranges
    if ($cutAngle >= 0 && $cutAngle < 90) {
        // Quadrant I
        $x_upper = $x0 + $stemHeight + $buffer;
        $y_upper = $y0 + $stemHeight + $buffer;
        $count_query = "SELECT x_coordinate AS x, y_coordinate AS y 
                        FROM tree_data 
                        WHERE tree_status != 'Cut' 
                        AND x_coordinate > $x0 AND x_coordinate < $x_upper 
                        AND y_coordinate > $y0 AND y_coordinate < $y_upper";
    } elseif ($cutAngle >= 90 && $cutAngle < 180) {
        // Quadrant II
        $x_upper = $x0 - $stemHeight + $buffer;
        $y_upper = $y0 + $stemHeight - $buffer;
        $count_query = "SELECT x_coordinate AS x, y_coordinate AS y 
                        FROM tree_data 
                        WHERE tree_status != 'Cut' 
                        AND x_coordinate < $x0 AND x_coordinate > $x_upper 
                        AND y_coordinate > $y0 AND y_coordinate < $y_upper";
    } elseif ($cutAngle >= 180 && $cutAngle < 270) {
        // Quadrant III
        $x_upper = $x0 - $stemHeight - $buffer;
        $y_upper = $y0 - $stemHeight - $buffer;
        $count_query = "SELECT x_coordinate AS x, y_coordinate AS y 
                        FROM tree_data 
                        WHERE tree_status != 'Cut' 
                        AND x_coordinate < $x0 AND x_coordinate > $x_upper 
                        AND y_coordinate < $y0 AND y_coordinate > $y_upper";
    } elseif ($cutAngle >= 270 && $cutAngle < 360) {
        // Quadrant IV
        $x_upper = $x0 - $stemHeight - $buffer;
        $y_upper = $y0 + $stemHeight + $buffer;
        $count_query = "SELECT x_coordinate AS x, y_coordinate AS y 
                        FROM tree_data 
                        WHERE tree_status != 'Cut' 
                        AND x_coordinate > $x0 AND x_coordinate < $x_upper 
                        AND y_coordinate < $y0 AND y_coordinate > $y_upper";
    }

    // Execute the count query for affected trees
    if (!empty($count_query)) {
        $result_count = mysqli_query($dbc, $count_query);
        if (!$result_count) {
            die('Error executing count query: ' . mysqli_error($dbc));
        }

        while ($row_count = mysqli_fetch_assoc($result_count)) {
            $victim_x = $row_count['x'];
            $victim_y = $row_count['y'];
            $victim_coordinate = $victim_x . ',' . $victim_y; // Victim tree coordinates

            // Calculate the radian value for the cut angle
            $radian = deg2rad($cutAngle);

            // Calculate the bounds for checking tree impact
            $y1 = $victim_x / tan($radian + 1);
            $y2 = $victim_x / tan($radian - 1);

            // Calculate crown position for the cut tree
            $x1_crown = $x0 + ($stemHeight + 5) * sin($radian);
            $y1_crown = $y0 + ($stemHeight + 5) * cos($radian);

            // Calculate distance between the victim tree and the crown of the cut tree
            $distance = sqrt(pow(($x1_crown - $victim_x), 2) + pow(($y1_crown - $victim_y), 2));

            // Determine the damage category
            if ($victim_y > $y1 && $victim_y < $y2) {
                $categoryDamage = 1;
            } elseif ($distance <= 5) {
                $categoryDamage = 2;
            }

            // If there's damage, insert into the database
            if (isset($categoryDamage)) {
                $insert_query = "INSERT INTO damagetree (cut_tree, victim, damage_category) 
                                 VALUES ('$cut_tree_coordinate', '$victim_coordinate', $categoryDamage)";
                $insert_result = mysqli_query($dbc, $insert_query);
                if (!$insert_result) {
                    die('Error inserting victim data: ' . mysqli_error($dbc));
                }
            }
        }
    }
}

?>
