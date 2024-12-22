<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stand Table 45</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            padding: 20px;
            background-color: #f0f2f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .filters {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-input, select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .search-input {
            flex: 1;
        }

        select {
            min-width: 150px;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px 8px;
            text-align: center;
        }

        th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #f0f0f0;
            transition: background-color 0.3s ease;
        }

        .numeric {
            text-align: right;
        }

        .species-name {
            text-align: left;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Stand Table 45</h1>
        <div class="filters">
            <input type="text" id="searchInput" class="search-input" placeholder="Search species...">
            <select id="metricSelect">
                <option value="volume">Volume</option>
                <option value="trees">Trees</option>
                <option value="production">Production</option>
                <option value="damage">Damage</option>
                <option value="growth30">Growth30</option>
                <option value="prod30">Prod30</option>
            </select>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th rowspan="2">Species Name</th>
                        <th rowspan="2">Species Group</th>
                        <?php
                        $categories = [
                            [5, 15],
                            [15, 30],
                            [30, 45],
                            [45, 60],
                            [60, 120]
                        ];
                        foreach ($categories as $category) {
                            echo "<th>Diameter {$category[0]} - {$category[1]} cm</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Database connection
                    $dbc = mysqli_connect("localhost", "root", "", "forest");

                    if (!$dbc) {
                        die("Database connection failed: " . mysqli_connect_error());
                    }

                    $speciesData = [
                        ['Mersawa', 1],
                        ['Keruing', 2],
                        ['Dip Marketable', 3],
                        ['Dip Non Market', 4],
                        ['Non Dip Market', 5],
                        ['Non Dip Non Market', 6],
                        ['Others', 7]
                    ];

                    foreach ($speciesData as $species) {
                        $speciesName = $species[0];
                        $spgroup = $species[1];

                        echo "<tr class='data-row'>";
                        echo "<td class='species-name'>$speciesName</td>";
                        echo "<td>Group $spgroup</td>";

                        foreach ($categories as $category) {
                            list($minDiameter, $maxDiameter) = $category;

                            $sqlMetrics = "SELECT 
                                            SUM(volume) AS totalVolume,
                                            COUNT(*) AS totalTrees,
                                            SUM(PROD) AS totalProduction,
                                            SUM(damage_crown + damage_stem) AS totalDamage,
                                            SUM(Growth_D30) AS totalGrowth30,
                                            SUM(PROD30) AS totalProduction30
                                           FROM tree_data
                                           WHERE spgroup = $spgroup
                                           AND diameter BETWEEN $minDiameter AND $maxDiameter
                                           AND blockx = 1 AND blocky = 1";

                            $resultMetrics = mysqli_query($dbc, $sqlMetrics);
                            $metrics = mysqli_fetch_assoc($resultMetrics);

                            // Store all metrics in data attributes
                            echo "<td class='numeric metric-cell' 
                                    data-volume='" . number_format(($metrics['totalVolume'] ?? 0), 2) . "'
                                    data-trees='" . number_format(($metrics['totalTrees'] ?? 0)) . "'
                                    data-production='" . number_format(($metrics['totalProduction'] ?? 0), 2) . "'
                                    data-damage='" . number_format(($metrics['totalDamage'] ?? 0), 2) . "'
                                    data-growth30='" . number_format(($metrics['totalGrowth30'] ?? 0), 2) . "'
                                    data-prod30='" . number_format(($metrics['totalProduction30'] ?? 0), 2) . "'>" .
                                    number_format(($metrics['totalVolume'] ?? 0), 2) . "</td>";
                        }
                        echo "</tr>";
                    }

                    mysqli_close($dbc);
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const metricSelect = document.getElementById('metricSelect');
        const rows = document.querySelectorAll('.data-row');

        function updateTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedMetric = metricSelect.value;
            
            rows.forEach(row => {
                const speciesName = row.querySelector('.species-name').textContent.toLowerCase();
                const shouldShow = speciesName.includes(searchTerm);
                row.style.display = shouldShow ? '' : 'none';

                // Update visible metric
                if (shouldShow) {
                    const metricCells = row.querySelectorAll('.metric-cell');
                    metricCells.forEach(cell => {
                        cell.textContent = cell.dataset[selectedMetric];
                    });
                }
            });
        }

        searchInput.addEventListener('input', updateTable);
        metricSelect.addEventListener('change', updateTable);

        // Initial display
        updateTable();
    </script>
</body>
</html>