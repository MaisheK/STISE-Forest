<?php
ini_set('max_execution_time', 900);

DEFINE ('DB_USER', 'root');
DEFINE ('DB_PASSWORD', '');
DEFINE ('DB_HOST', 'localhost');
DEFINE ('DB_NAME', 'forest');

// Connect to the database
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) OR die ('Could not connect to MySQL: ' . mysqli_connect_error());
mysqli_set_charset($dbc, 'utf8');

$sql = "SELECT * FROM Forest50";
$result = mysqli_query($dbc, $sql);

// Output display
if (mysqli_num_rows($result) > 0) {
    // Output data of each row in a table
    echo "<table border='1'>
            <tr>
                <th>ID</th>
                <th>Species Group</th>
                <th>Diameter</th>
                <th>Diameter 30</th>
                <th>Volume 30</th>
                <th>Status 30</th>
                <th>PROD 30</th>
            </tr>";
    while($row = mysqli_fetch_assoc($result)) {
        $id = $row["id"];
        $speciesgroup = $row["spgroup"];
        $originalDiameter = $row["diameter"];
        $newDiameter = calculateNewDiameter($originalDiameter);
        $volume30 = calculateVolume30($newDiameter, $speciesgroup);

        // Call status logic function
        [$status30, $prod30] = determineStatusLogic($newDiameter, $volume30, $speciesgroup);

        // Display data in table
        echo "<tr>
                <td>".$row["id"]."</td>
                <td>".$speciesgroup."</td>
                <td>".$originalDiameter."</td>
                <td>".number_format($newDiameter, 2)."</td>
                <td>".number_format($volume30, 2)."</td>
                <td>".$status30."</td>
                <td>".number_format($prod30, 2)."</td>
              </tr>";

        // Update query
        $sql1 = "
            UPDATE Forest50
            SET 
                Growth_D30 = ".number_format($newDiameter, 2).",
                Volume30 = ".number_format($volume30, 2).",
                PROD30 = ".number_format($prod30, 2).",
                Status30 = '$status30'
            WHERE Id = '$id' 
            AND tree_status NOT IN ('Cut', 'victim')
        ";

        $result1 = mysqli_query($dbc, $sql1);
    }
    echo "</table>";
} else {
    echo "0 results";
}

// Close the connection
mysqli_close($dbc);

function calculateNewDiameter($diameter) {
    // Calculate the new diameter
    for ($year = 1; $year <= 30; $year++) {
        if ($diameter >= 5 && $diameter <= 15) {
            $diameter += 0.4;
        } elseif ($diameter > 15 && $diameter <= 30) {
            $diameter += 0.6;
        } elseif ($diameter > 30 && $diameter <= 45) {
            $diameter += 0.5;
        } elseif ($diameter > 45 && $diameter <= 60) {
            $diameter += 0.4;
        } elseif ($diameter > 60) {
            $diameter += 0.5;
        } else {
            return "Invalid Diameter";
        }
    }
    return $diameter;
}

function calculateVolume30($newDiameter, $speciesgroup) {
    // Calculate the volume of the tree after 30 years
    if (in_array($speciesgroup, [1, 2, 3, 4])) {
        return -0.0971 + 9.503 * pow($newDiameter / 100, 2);
    } elseif (in_array($speciesgroup, [5, 6, 7])) {
        return -0.331 + 6.694 * pow($newDiameter / 100, 2);
    } else {
        return "Invalid Species group";
    }
}

function determineStatusLogic($newDiameter, $volume30, $speciesgroup) {
    // Determine the status and PROD based on logic
    $status30 = 'Keep';
    $prod30 = 0;

    if ($newDiameter > 50 && in_array($speciesgroup, [1, 2, 3, 5])) {
        $status30 = 'Cut';
        $prod30 = $volume30;
    }

    return [$status30, $prod30];
}
?>
