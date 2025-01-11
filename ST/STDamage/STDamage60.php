<?php

DEFINE ('DB_USER', 'root');
DEFINE ('DB_PASSWORD', '');
DEFINE ('DB_HOST', 'localhost');
DEFINE ('DB_NAME', 'forest');

// Make the connection:
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) OR die ('Could not connect to MySQL: ' . mysqli_connect_error());

// Set the encoding...
mysqli_set_charset($dbc, 'utf8');

$categories = [
    [5, 15],  // Category 1: Diameter 5-15
    [15, 30], // Category 2: Diameter 15-30
    [30, 45], // Category 3: Diameter 30-45
    [45, 60], // Category 4: Diameter 45-60
    [60, 100] // Category 5: Diameter 60+
];

$speciesGroups = [1, 2, 3, 4, 5, 6, 7]; // Spgroup IDs

// Initialize arrays to store totals
$totalTreesByCategory = [];
$totalVolumeByCategory = [];
foreach ($categories as $category) {
    list($minDiameter, $maxDiameter) = $category;
    $totalTreesByCategory["$minDiameter-$maxDiameter"] = 0;
    $totalVolumeByCategory["$minDiameter-$maxDiameter"] = 0;
}

// Prepare species data
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
    <title>Stand Table Damage 60</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
        .table-forest thead th {
            vertical-align: middle;
            text-align: center;
            font-weight: 600;
            border-bottom: 2px solid #218838;
        }
        .table-forest tbody tr:nth-child(even) {
            background-color: #f2f9f3;
        }
        .table-forest tbody tr:hover {
            background-color: #e6f3e7;
        }
        .table-forest tfoot {
            font-weight: bold;
            background-color: #e9ecef;
        }
        h1 {
            color: #2c8d3b;
            margin-bottom: 20px;
            text-align: center;
        }
        .volume-cell {
            background-color: #e9f5ea;
        }
        .trees-cell {
            background-color: #f2f9f3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Stand Table Damage 60</h1>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-forest">
                <thead>
                    <tr>
                        <th rowspan="2">Species Name</th>
                        <th rowspan="2">Species Group</th>
                        <th colspan="5">Diameter Categories</th>
                        <th rowspan="2">Total</th>
                    </tr>
                    <tr>
                        <th>5 - 15 cm</th>
                        <th>15 - 30 cm</th>
                        <th>30 - 45 cm</th>
                        <th>45 - 60 cm</th>
                        <th>60+ cm</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($speciesData as $species) {
                        $speciesName = $species[0];
                        $spgroup = $species[1];

                        $totalTreesSpecies = 0;
                        $totalVolumeSpecies = 0;

                        // Volume row
                        echo "<tr>";
                        echo "<td rowspan='2'>$speciesName</td>";
                        echo "<td rowspan='2'>Group $spgroup</td>";

                        foreach ($categories as $category) {
                            list($minDiameter, $maxDiameter) = $category;
                            $sql = "SELECT SUM(volume) AS totalVolume
                                    FROM forest60
                                    WHERE spgroup = $spgroup
                                    AND diameter BETWEEN $minDiameter AND $maxDiameter
                                    AND tree_status = 'victim'";
                            $result = mysqli_query($dbc, $sql);
                            $row = mysqli_fetch_assoc($result);

                            $totalVolume = $row['totalVolume'] / 100;
                            $totalVolumeSpecies += $totalVolume;

                            $totalVolumeByCategory["$minDiameter-$maxDiameter"] += $totalVolume;

                            echo "<td class='volume-cell'>" . number_format($totalVolume, 2) . "</td>";
                        }
                        // Total volume column
                        echo "<td class='volume-cell'>" . number_format($totalVolumeSpecies, 2) . "</td>";
                        echo "</tr>";

                        // Trees row
                        echo "<tr>";
                        foreach ($categories as $category) {
                            list($minDiameter, $maxDiameter) = $category;
                            $sql = "SELECT COUNT(*) AS totalTrees
                                    FROM forest60
                                    WHERE spgroup = $spgroup
                                    AND diameter BETWEEN $minDiameter AND $maxDiameter
                                    AND tree_status = 'victim'";
                            $result = mysqli_query($dbc, $sql);
                            $row = mysqli_fetch_assoc($result);

                            $totalTrees = $row['totalTrees'] / 100;
                            $totalTreesSpecies += $totalTrees;

                            $totalTreesByCategory["$minDiameter-$maxDiameter"] += $totalTrees;

                            echo "<td class='trees-cell'>" . number_format($totalTrees) . "</td>";
                        }
                        // Total trees column
                        echo "<td class='trees-cell'>" . number_format($totalTreesSpecies) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-center">Total Volume</td>
                        <?php
                        foreach ($categories as $category) {
                            list($minDiameter, $maxDiameter) = $category;
                            echo "<td class='volume-cell'>" . number_format($totalVolumeByCategory["$minDiameter-$maxDiameter"], 2) . "</td>";
                        }
                        echo "<td class='volume-cell'>" . number_format(array_sum($totalVolumeByCategory), 2) . "</td>";
                        ?>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-center">Total Trees</td>
                        <?php
                        foreach ($categories as $category) {
                            list($minDiameter, $maxDiameter) = $category;
                            echo "<td class='trees-cell'>" . number_format($totalTreesByCategory["$minDiameter-$maxDiameter"]) . "</td>";
                        }
                        echo "<td class='trees-cell'>" . number_format(array_sum($totalTreesByCategory)) . "</td>";
                        ?>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS (optional, for future enhancements) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close the database connection
mysqli_close($dbc);
?>