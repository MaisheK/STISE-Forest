<?php
// Database connection
DEFINE('DB_USER', 'root');
DEFINE('DB_PASSWORD', '');
DEFINE('DB_HOST', 'localhost');
DEFINE('DB_NAME', 'forest');

// Make the connection:
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) or die('Could not connect to MySQL: ' . mysqli_connect_error());

// Set the encoding...
mysqli_set_charset($conn, 'utf8');

// Variables
$NoBlockX = 10;
$NoBlockY = 10;
$NoGroupSpecies = 7; // Species group: Mersawa, Keruing, etc.
$NumDclass = 5; // Diameter range class

// TreePerha array
$TreePerha = [
    [15, 12, 4, 2, 2], // group 1
    [21, 18, 6, 4, 4], // group 2
    [21, 18, 6, 4, 4], // group 3
    [30, 27, 9, 5, 3], // group 4
    [30, 27, 9, 4, 4], // group 5
    [39, 36, 12, 7, 4], // group 6
    [44, 42, 14, 9, 4]  // group 7
];

// ListSpecies array (taken from database)
$ListSpecies = [];
$sql = "SELECT No, species FROM speciesname";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ListSpecies[$row['No']] = $row['species'];
    }
} else {
    die("No species found in the database.");
}

// Loop through blocks and generate tree data
for ($IX = 1; $IX <= $NoBlockX; $IX++) {
    for ($JY = 1; $JY <= $NoBlockY; $JY++) {
        $blockx = $IX;
        $blocky = $JY;

        for ($I = 1; $I <= $NoGroupSpecies; $I++) {
            for ($J = 1; $J <= $NumDclass; $J++) {
                $NumTree = $TreePerha[$I - 1][$J - 1];

                for ($K = 1; $K <= $NumTree; $K++) {
                    // Determine Species
                    if ($I == 1) $SequenceSp = rand(1, 1);
                    else if ($I == 2) $SequenceSp = rand(2, 5);
                    else if ($I == 3) $SequenceSp = rand(6, 12);
                    else if ($I == 4) $SequenceSp = rand(13, 19);
                    else if ($I == 5) $SequenceSp = rand(20, 59);
                    else if ($I == 6) $SequenceSp = rand(60, 155);
                    else if ($I == 7) $SequenceSp = rand(156, 318);

                    // Check if species key exists
                    $species = array_key_exists($SequenceSp, $ListSpecies) ? $ListSpecies[$SequenceSp] : 'Unknown';

                    // Determine Diameter
                    if ($J == 1) $diameter = rand(500, 1500) / 100;
                    else if ($J == 2) $diameter = rand(1500, 3000) / 100;
                    else if ($J == 3) $diameter = rand(3000, 4500) / 100;
                    else if ($J == 4) $diameter = rand(4500, 6000) / 100;
                    else if ($J == 5) $diameter = rand(6000, 12000) / 100;

                    // Determine Height
                    if ($J == 1) $height = rand(250, 550) / 100;
                    else if ($J == 2) $height = rand(550, 1000) / 100;
                    else if ($J == 3) $height = rand(1000, 1500) / 100;
                    else if ($J == 4) $height = rand(1500, 4000) / 100;
                    else if ($J == 5) $height = rand(1500, 4000) / 100;

                    // Calculate Volume based on Species Group
                    if (in_array($I, [1, 2, 3, 4])) {
                        $volume = 0.015 + 2.137 * pow($diameter / 100, 2) + 0.513 * pow($diameter / 100, 2) * $height;
                    } else {
                        $volume = -0.0023 + 2.942 * pow($diameter / 100, 2) + 0.262 * pow($diameter / 100, 2) * $height;
                    }

                    // Determine Location
                    $locationx = rand(1, 100);
                    $locationy = rand(1, 100);
                    $x = ($blockx - 1) * 100 + $locationx;
                    $y = ($blocky - 1) * 100 + $locationy;

                    // Generate TreeNum
                    $TreeNum = 'T' . $blockx . $blocky . $x . $y;

                    $SPEC_Gr = $I;
                    $diameterclass = $J;

                    // Determine Status and Cut Angle
                    $status = 'Keep';
                    $prod = $volume;
                    $cut_angle = 0; // Default value
                    if (in_array($SPEC_Gr, [1, 2, 3, 5]) && $diameter > 45) {
                        $status = 'Cut';
                        $prod = 0;
                        $cut_angle = rand(0, 360); // Randomized cut angle
                    }

                    $sql = "INSERT INTO tree_data (
                        blockx, blocky, x_coordinate, y_coordinate, treenum, 
                        species, diameter, height, volume, spgroup, 
                        diameterclass, tree_status, PROD, cut_angle, damage_stem, damage_crown
                    ) VALUES (
                        $blockx, $blocky, $x, $y, '$TreeNum', 
                        '$species', $diameter, $height, $volume, $SPEC_Gr, 
                        $diameterclass, '$status', $prod, " . ($cut_angle !== null ? $cut_angle : "NULL") . ", NULL, NULL
                    )";                    
            
                    // Debugging output
                    echo "Generated SQL: " . $sql . "<br>";
                    
                    if ($conn->query($sql) !== TRUE) {
                        echo "Error: " . $sql . "<br>" . $conn->error;
                    }
            
                }
            }
        }
    }
}

$conn->close();

echo "Tree data has been successfully inserted.";
?>
