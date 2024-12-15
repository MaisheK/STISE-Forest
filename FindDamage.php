<?php
ini_set('max_execution_time', 300); 
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

// For example, you can retrieve data from the 'tree_data' table and perform calculations:
// Construct the SQL query to retrieve data for all "Cut" trees
$sql = "SELECT treenum, cut_angle, x_coordinate AS x, y_coordinate AS y, height FROM tree_data WHERE tree_status = 'Cut'";
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

    $count_query = ""; // Initialize count_query to empty string

    if ($cutAngle >= 0 && $cutAngle < 90) {
        // Quadrant I: 0 - 90 degrees
        $x_upper = $x0 + $stemHeight + $buffer;
        $y_upper = $y0 + $stemHeight + $buffer;

        $count_query = "SELECT x_coordinate AS x, y_coordinate AS y FROM tree_data WHERE tree_status != 'Cut' AND x_coordinate > $x0 AND x_coordinate < $x_upper AND y_coordinate > $y0 AND y_coordinate < $y_upper";
    }
    elseif ($cutAngle >= 90 && $cutAngle < 180) {
        // Quadrant II: 90 - 180 degrees
        $x_upper = $x0 + $stemHeight + $buffer;
        $y_upper = $y0 - $stemHeight - $buffer;
        $count_query2 = "SELECT x_coordinate AS x, y_coordinate AS y FROM tree_data WHERE tree_status != 'Cut' AND x_coordinate > $x0 AND x_coordinate < $x_upper AND y_coordinate > $y0 AND y_coordinate < $y_upper";
    }
    elseif ($cutAngle >= 180 && $cutAngle < 270) {
        // Quadrant III: 180 - 270 degrees
        $x_upper = $x0 - $stemHeight - $buffer;
        $y_upper = $y0 - $stemHeight - $buffer;
        $count_query3 = "SELECT x_coordinate AS x, y_coordinate AS y FROM tree_data WHERE tree_status != 'Cut' AND x_coordinate > $x0 AND x_coordinate < $x_upper AND y_coordinate > $y0 AND y_coordinate < $y_upper";

    } elseif ($cutAngle >= 270 && $cutAngle < 360) {
        // Quadrant IV: 270 - 360 degrees
        $x_upper = $x0 - $stemHeight - $buffer;
        $y_upper = $y0 + $stemHeight + $buffer;
        $count_query4 = "SELECT x_coordinate AS x, y_coordinate AS y FROM tree_data WHERE tree_status != 'Cut' AND x_coordinate > $x0 AND x_coordinate < $x_upper AND y_coordinate > $y0 AND y_coordinate < $y_upper";
    }

    // Ensure count_query is not empty before executing the query
    if (!empty($count_query)) {
        // Execute the query to find affected trees
        $result1 = mysqli_query($dbc, $count_query);

        if (!$result1) {
            die('Error executing query: ' . mysqli_error($dbc));
        }

        while ($row1 = mysqli_fetch_assoc($result1)) {

            $radian = deg2rad($cutAngle);

            $unknownTree_X = $row1['x'];
            $unknownTree_Y = $row1['y'];

            $y1 =  ($unknownTree_X / tan($radian + 1));
            $y2 =  ($unknownTree_X / tan($radian - 1));

            $x1_crown = $x0 + ($stemHeight + 5) * sin($radian);
            $y1_crown = $y0 + ($stemHeight + 5) * cos($radian);

            $distance = sqrt(pow(($x1_crown - $unknownTree_X), 2) + pow(($y1_crown - $unknownTree_Y), 2));

            if (($unknownTree_Y > $y1 && $unknownTree_Y < $y2) ) {
                $victim_x = $row1['x'];
                $victim_y = $row1['y'];
                $victim_coordinate = $victim_x . ',' . $victim_y; // Coordinate of the victim tree
            
                // Determine the category of damage based on the conditions
                $categoryDamage = 1 ;

                // Insert the victim data into the database
                $insert_query = "INSERT INTO damagetree (cut_tree, victim, damage_category) VALUES ('$cut_tree_coordinate', '$victim_coordinate', $categoryDamage)";
                $result3 = mysqli_query($dbc, $insert_query);
                if (!$result3) {
                    die('Error inserting victim data: ' . mysqli_error($dbc));
                }

            } 
            if($distance <= 5){
                $victim_x = $row1['x'];
                $victim_y = $row1['y'];
                $victim_coordinate = $victim_x . ',' . $victim_y; // Coordinate of the victim tree
            
                // Determine the category of damage based on the conditions
                $categoryDamage = 2;
            
                // Insert the victim data into the database
                $insert_query = "INSERT INTO damagetree (cut_tree, victim, damage_category) VALUES ('$cut_tree_coordinate', '$victim_coordinate', $categoryDamage)";
                $result3 = mysqli_query($dbc, $insert_query);
                if (!$result3) {
                    die('Error inserting victim data: ' . mysqli_error($dbc));
                }

            }
        }
    } else if (!empty($count_query2)) {
        // Execute the query to find affected trees
        $result2 = mysqli_query($dbc, $count_query2);

        if (!$result2) {
            die('Error executing query: ' . mysqli_error($dbc));
        }

        while ($row2 = mysqli_fetch_assoc($result2)) {

            $cutAngle = 180 - $cutAngle;

            $radian = deg2rad($cutAngle);

            $unknownTree_X = $row2['x'];
            $unknownTree_Y = $row2['y'];

            $y1 =  (($y0 - $unknownTree_X) / tan($radian + 1));
            $y2 =  (($y0 - $unknownTree_X) / tan($radian - 1));

            $x1_crown = $x0 + ($stemHeight + 5) * sin($radian);
            $y1_crown = $y0 - ($stemHeight + 5) * cos($radian);

            $distance = sqrt(pow(($x1_crown - $unknownTree_X), 2) + pow(($y1_crown - $unknownTree_Y), 2));

            if (($unknownTree_Y > $y1 && $unknownTree_Y < $y2) ) {
                $victim_x = $row2['x'];
                $victim_y = $row2['y'];
                $victim_coordinate = $victim_x . ',' . $victim_y; // Coordinate of the victim tree
            
                // Determine the category of damage based on the conditions
                $categoryDamage = 1 ;

                
            
                // Insert the victim data into the database
                $insert_query = "INSERT INTO damagetree (cut_tree, victim, damage_category) VALUES ('$cut_tree_coordinate', '$victim_coordinate', $categoryDamage)";
                $result3 = mysqli_query($dbc, $insert_query);
                if (!$result3) {
                    die('Error inserting victim data: ' . mysqli_error($dbc));
                }
                

            } if($distance <= 5){
                $victim_x = $row2['x'];
                $victim_y = $row2['y'];
                $victim_coordinate = $victim_x . ',' . $victim_y; // Coordinate of the victim tree
            
                // Determine the category of damage based on the conditions
                $categoryDamage = 2;
                
            
                // Insert the victim data into the database
                $insert_query = "INSERT INTO damagetree (cut_tree, victim, damage_category) VALUES ('$cut_tree_coordinate', '$victim_coordinate', $categoryDamage)";
                $result3 = mysqli_query($dbc, $insert_query);
                if (!$result3) {
                    die('Error inserting victim data: ' . mysqli_error($dbc));
                }

            }
        }
        
    } else if (!empty($count_query2)) {
        // Execute the query to find affected trees
        $result3 = mysqli_query($dbc, $count_query2);
    
        if (!$result3) {
            die('Error executing query: ' . mysqli_error($dbc));
        }
    
        while ($row3 = mysqli_fetch_assoc($result3)) {

            $cutAngle = 180 + $cutAngle;
    
            $radian = deg2rad($cutAngle);
    
            $unknownTree_X = $row3['x'];
            $unknownTree_Y = $row3['y'];
    
            $y1 =  (($y0 - $unknownTree_X) / tan($radian + 1));
            $y2 =  (($y0 - $unknownTree_X) / tan($radian - 1));
    
            $x1_crown = $x0 - ($stemHeight + 5) * sin($radian);
            $y1_crown = $y0 - ($stemHeight + 5) * cos($radian);
    
            $distance = sqrt(pow(($x1_crown - $unknownTree_X), 2) + pow(($y1_crown - $unknownTree_Y), 2));
    
            if (($unknownTree_Y > $y1 && $unknownTree_Y < $y2) ) {
                $victim_x = $row3['x'];
                $victim_y = $row3['y'];
                $victim_coordinate = $victim_x . ',' . $victim_y; // Coordinate of the victim tree
            
                // Determine the category of damage based on the conditions
                $categoryDamage = 1 ;

               
            
                // Insert the victim data into the database
                $insert_query = "INSERT INTO damagetree (cut_tree, victim, damage_category) VALUES ('$cut_tree_coordinate', '$victim_coordinate', $categoryDamage)";
                $result3 = mysqli_query($dbc, $insert_query);
                if (!$result3) {
                    die('Error inserting victim data: ' . mysqli_error($dbc));
                
            }
            } if($distance <= 5){
                $victim_x = $row3['x'];
                $victim_y = $row3['y'];
                $victim_coordinate = $victim_x . ',' . $victim_y; // Coordinate of the victim tree
            
                // Determine the category of damage based on the conditions
                $categoryDamage = 2;

                
            
            
                // Insert the victim data into the database
                $insert_query = "INSERT INTO damagetree (cut_tree, victim, damage_category) VALUES ('$cut_tree_coordinate', '$victim_coordinate', $categoryDamage)";
                $result3 = mysqli_query($dbc, $insert_query);
                if (!$result3) {
                    die('Error inserting victim data: ' . mysqli_error($dbc));
                }

            }
        }
    }else if (!empty($count_query3)) {
        // Execute the query to find affected trees
        $result4 = mysqli_query($dbc, $count_query3);
    
        if (!$result4) {
            die('Error executing query: ' . mysqli_error($dbc));
        }
    
        while ($row4 = mysqli_fetch_assoc($result4)) {

            $cutAngle = 360 - $cutAngle;
    
            $radian = deg2rad($cutAngle);
    
            $unknownTree_X = $row2['x'];
            $unknownTree_Y = $row2['y'];
    
            $y1 =  (($y0 - $unknownTree_X) / tan($radian + 1));
            $y2 =  (($y0 - $unknownTree_X) / tan($radian - 1));
    
            $x1_crown = $x0 - ($stemHeight + 5) * sin($radian);
            $y1_crown = $y0 + ($stemHeight + 5) * cos($radian);
    
            $distance = sqrt(pow(($x1_crown - $unknownTree_X), 2) + pow(($y1_crown - $unknownTree_Y), 2));
    
            if (($unknownTree_Y > $y1 && $unknownTree_Y < $y2) ) {
                $victim_x = $row4['x'];
                $victim_y = $row4['y'];
                $victim_coordinate = $victim_x . ',' . $victim_y; // Coordinate of the victim tree
            
                // Determine the category of damage based on the conditions
                $categoryDamage = 1 ;

            
            
                // Insert the victim data into the database
                $insert_query = "INSERT INTO damagetree (cut_tree, victim, damage_category) VALUES ('$cut_tree_coordinate', '$victim_coordinate', $categoryDamage) ";
                $result3 = mysqli_query($dbc, $insert_query);
                if (!$result3) {
                    die('Error inserting victim data: ' . mysqli_error($dbc));
                
            }
            } if($distance <= 5){
                $victim_x = $row4['x'];
                $victim_y = $row4['y'];
                $victim_coordinate = $victim_x . ',' . $victim_y; // Coordinate of the victim tree
            
                // Determine the category of damage based on the conditions
                $categoryDamage = 2;

                
            
                // Insert the victim data into the database
                $insert_query = "INSERT INTO damagetree (cut_tree, victim, damage_category) VALUES ('$cut_tree_coordinate', '$victim_coordinate', $categoryDamage)";
                $result3 = mysqli_query($dbc, $insert_query);
                if (!$result3) {
                    die('Error inserting victim data: ' . mysqli_error($dbc));
                }

            }
        }
    
    }
}
?>
