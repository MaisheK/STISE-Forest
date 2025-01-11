<?php

DEFINE('DB_USER', 'root');
DEFINE('DB_PASSWORD', '');
DEFINE('DB_HOST', 'localhost');
DEFINE('DB_NAME', 'forest');

// Make the connection:
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) OR die ('Could not connect to MySQL: ' . mysqli_connect_error());

// Set the encoding...
mysqli_set_charset($dbc, 'utf8');

$speciesData = [
    ['Mersawa', 1],
    ['Keruing', 2],
    ['Dip Marketable', 3],
    ['Dip Non Market', 4],
    ['Non Dip Market', 5],
    ['Non Dip Non Market', 6],
    ['Others', 7]
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forest Stand Table</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Add your custom styles */
        body {
            background-color: #f4f6f9;
            padding-top: 30px;
        }
        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .table-forest {
            background-color: white;
        }
        .table-forest thead {
            background-color: #2c8d3b;
            color: white;
        }
        .table-forest tbody tr:nth-child(even) {
            background-color: #f2f9f3;
        }
        .table-forest tbody tr:hover {
            background-color: #e6f3e7;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Final Output Regime 50 - 45</h1>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-forest">
                <thead>
                    <tr>
                        <th>Species Name</th>
                        <th>Species Group</th>
                        <th>Total Volume 0</th>
                        <th>Total Number 0</th>
                        <th>Production 0</th>
                        <th>Damage</th>
                        <th>Remaining Trees</th>
                        <th>Growth 30</th>
                        <th>Production 30</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($speciesData as $species) {
                        $speciesName = $species[0];
                        $spgroup = $species[1];

                        // Query for main metrics
                        $sql = "SELECT 
                                    SUM(volume) AS totalVolume, 
                                    COUNT(*) AS totalTree,
                                    SUM(prod) AS prod0,
                                    SUM(CASE WHEN tree_status = 'keep' THEN 1 ELSE 0 END) AS remainingTrees,
                                    SUM(Volume30) AS totalGrowth30
                                FROM regime5045
                                WHERE spgroup = $spgroup";
                        $result = mysqli_query($dbc, $sql);
                        $row = mysqli_fetch_assoc($result);

                        $totalVolume = $row['totalVolume'] / 100 ?? 0;
                        $totalTree = $row['totalTree'] / 100 ?? 0;
                        $prod0 = $row['prod0'] / 100 ?? 0;
                        $remainingTrees = $row['remainingTrees'] / 100 ?? 0;
                        $growth30 = $row['totalGrowth30'] / 100 ?? 0;

                        // Query for damage (tree_status = 'victim')
                        $sqlDamage = "SELECT 
                                        COUNT(*) AS totalDamage
                                      FROM regime5045
                                      WHERE spgroup = $spgroup
                                      AND tree_status = 'victim'";
                        $resultDamage = mysqli_query($dbc, $sqlDamage);
                        $damageRow = mysqli_fetch_assoc($resultDamage);
                        $damage = $damageRow['totalDamage'] / 100 ?? 0;

                        // Query for PROD30 based on Growth_D30
                        $sqlProd30 = "SELECT 
                                        SUM(PROD30) AS totalProd30
                                      FROM regime5045
                                      WHERE spgroup = $spgroup";
                        $resultProd30 = mysqli_query($dbc, $sqlProd30);
                        $prod30Row = mysqli_fetch_assoc($resultProd30);
                        $prod30 = $prod30Row['totalProd30'] / 100 ?? 0;

                        // Display the row
                        echo "<tr>";
                        echo "<td>$speciesName</td>";
                        echo "<td>Group $spgroup</td>";
                        echo "<td>" . number_format($totalVolume, 2) . "</td>";
                        echo "<td>" . number_format($totalTree) . "</td>";
                        echo "<td>" . number_format($prod0, 2) . "</td>";
                        echo "<td>" . number_format($damage) . "</td>";
                        echo "<td>" . number_format($remainingTrees) . "</td>";
                        echo "<td>" . number_format($growth30, 2) . "</td>";
                        echo "<td>" . number_format($prod30, 2) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close the database connection
mysqli_close($dbc);
?>
