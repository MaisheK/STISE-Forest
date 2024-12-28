<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stand Table 50</title>
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
        <h1>Stand Table 50</h1>
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
                            [60, 100]
                        ];
                        foreach ($categories as $category) {
                            echo "<th>Diameter {$category[0]} - {$category[1]} cm</th>";
                        }
                        echo "<th>Total</th>";
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

                    $grandTotal = 0;

                    foreach ($speciesData as $species) {
                        $speciesName = $species[0];
                        $spgroup = $species[1];
                        $rowTotal = 0;

                        echo "<tr class='data-row'>";
                        echo "<td class='species-name'>$speciesName</td>";
                        echo "<td>Group $spgroup</td>";

                        // Loop through diameter categories
                        foreach ($categories as $category) {
                            list($minDiameter, $maxDiameter) = $category;

                            // Query to get metrics based on diameter ranges
                            $sqlMetricsDiameter = "SELECT 
                                            SUM(volume) AS totalVolume,
                                            COUNT(*) AS totalTrees,
                                            SUM(PROD) AS totalProduction,
                                            COUNT(IF(tree_status = 'victim', 1, NULL)) AS totalDamage,
                                            SUM(Growth_D30) AS totalGrowth30
                                        FROM forest50
                                        WHERE spgroup = $spgroup
                                        AND diameter BETWEEN $minDiameter AND $maxDiameter
                                        AND blockx = 1 AND blocky = 1";

                            $resultMetricsDiameter = mysqli_query($dbc, $sqlMetricsDiameter);
                            $metricsDiameter = mysqli_fetch_assoc($resultMetricsDiameter);

                            // Second query for PROD30 based on Growth_D30
                            $sqlMetricsGrowthD30 = "SELECT 
                                            SUM(PROD30) AS totalProduction30
                                        FROM forest50
                                        WHERE spgroup = $spgroup
                                        AND Growth_D30 BETWEEN $minDiameter AND $maxDiameter
                                        AND blockx = 1 AND blocky = 1";

                            $resultMetricsGrowthD30 = mysqli_query($dbc, $sqlMetricsGrowthD30);
                            $metricsGrowthD30 = mysqli_fetch_assoc($resultMetricsGrowthD30);

                            // Store all metrics in data attributes
                            echo "<td class='numeric metric-cell' 
                                    data-volume='" . number_format(($metricsDiameter['totalVolume'] ?? 0), 2) . "' 
                                    data-trees='" . number_format(($metricsDiameter['totalTrees'] ?? 0)) . "' 
                                    data-production='" . number_format(($metricsDiameter['totalProduction'] ?? 0), 2) . "' 
                                    data-damage='" . number_format(($metricsDiameter['totalDamage'] ?? 0)) . "' 
                                    data-growth30='" . number_format(($metricsDiameter['totalGrowth30'] ?? 0), 2) . "' 
                                    data-prod30='" . number_format(($metricsGrowthD30['totalProduction30'] ?? 0), 2) . "'>" . 
                                    number_format(($metricsDiameter['totalVolume'] ?? 0), 2) . "</td>";
                        }

                        // Output total for the species
                        echo "<td class='numeric'>" . number_format($rowTotal, 2) . "</td>";
                        echo "</tr>";

                        // Add to grand total
                        $grandTotal += $rowTotal;
                    }

                    mysqli_close($dbc);
                    ?>
                    <tr id="totalRow">
                        <td colspan="2" style="font-weight: bold; text-align: right;">Total</td>
                        <?php
                        // Loop to display totals for each diameter range
                        foreach ($categories as $category) {
                            echo "<td class='numeric total-cell'>0</td>";
                        }
                        ?>
                        <td class="numeric" id="grandTotal"><?= number_format($grandTotal, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const metricSelect = document.getElementById('metricSelect');
        const rows = document.querySelectorAll('.data-row');
        const grandTotalCell = document.querySelector('#grandTotal');

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

        // Function to update the table and grand total
        function updateTable1() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedMetric = metricSelect.value;

            rows.forEach(row => {
                const speciesName = row.querySelector('.species-name').textContent.toLowerCase();
                const shouldShow = speciesName.includes(searchTerm);
                row.style.display = shouldShow ? '' : 'none';

                // Update metric cells for the selected metric
                if (shouldShow) {
                    const metricCells = row.querySelectorAll('.numeric');
                    let total = 0;

                    metricCells.forEach((cell, index) => {
                        // Calculate total based on the selected metric
                        let cellValue = parseFloat(cell.textContent) || 0; // Ensure valid number (default to 0)
                        if (selectedMetric === 'volume') {
                            total += parseFloat(cell.dataset.volume) || 0;
                        } else if (selectedMetric === 'trees') {
                            total += parseFloat(cell.dataset.trees) || 0;
                        } else if (selectedMetric === 'production') {
                            total += parseFloat(cell.dataset.production) || 0;
                        } else if (selectedMetric === 'damage') {
                            total += parseFloat(cell.dataset.damage) || 0;
                        } else if (selectedMetric === 'growth30') {
                            total += parseFloat(cell.dataset.growth30) || 0;
                        } else if (selectedMetric === 'prod30') {
                            total += parseFloat(cell.dataset.prod30) || 0;
                        }
                    });

                    // Format total to 2 decimal places
                    const formattedTotal = total.toFixed(2);

                    // Update the "Total" column with the formatted total
                    const totalCell = row.querySelector('td:last-child'); // Assuming the "Total" column is the last one
                    if (totalCell) {
                        totalCell.textContent = formattedTotal; // Use formatted total
                    }
                }
            });

            // Call to update grand total after updating the table
            updateGrandTotal(selectedMetric);
        }

        // Function to calculate and update the grand total
        function updateGrandTotal(selectedMetric) {
            let grandTotal = 0;

            // Loop over each row, and sum up the selected metric
            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    const metricCells = row.querySelectorAll('.metric-cell');
                    
                    // Get the correct metric value from data-attributes
                    metricCells.forEach(cell => {
                        const metricValue = parseFloat(cell.dataset[selectedMetric]) || 0;
                        grandTotal += metricValue;
                    });
                }
            });

            // Update the grand total cell
            if (grandTotalCell) {
                grandTotalCell.textContent = grandTotal.toFixed(2);  // Display grand total with 2 decimal places
            }
        }

        // Update totals for the metric (total for each category)
        function updateTotals() {
            const totalRow = document.getElementById('totalRow');
            const totalCells = totalRow.querySelectorAll('.total-cell');
            const selectedMetric = metricSelect.value;

            totalCells.forEach((cell, index) => {
                let total = 0;

                // Calculate the total for the selected metric based on visible rows
                rows.forEach(row => {
                    if (row.style.display !== 'none') {
                        const metricCells = row.querySelectorAll('.metric-cell');
                        const value = parseFloat(metricCells[index].dataset[selectedMetric]) || 0;
                        total += value;
                    }
                });

                // Update the total value in the respective cell
                cell.textContent = total.toFixed(2);
            });
        }

        // Event listeners for search and metric changes
        searchInput.addEventListener('input', () => {
            updateTable();
            updateTable1();
            updateTotals();
        });

        metricSelect.addEventListener('change', () => {
            updateTable();
            updateTable1();
            updateTotals();
        });

        // Initial call to update totals when the page loads
        updateTotals();

    </script>
</body>
</html>
