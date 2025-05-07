<?php 
$pageTitle = "Available Drugs";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?> - Hanan Pharmacy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0; padding: 0;
            background-color: #f9f9f9;
        }
        header, footer {
            background-color: #00796B;
            color: white;
            padding: 15px 0;
            text-align: center;
        }
        .container {
            width: 90%;
            max-width: 1100px;
            margin: 0 auto;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .search-bar {
            max-width: 500px;
            margin: 20px auto;
            display: flex;
        }
        .search-bar input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px 0 0 4px;
        }
        .search-bar button {
            padding: 10px 20px;
            border: none;
            background-color: #00796B;
            color: white;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        .drug-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
        }
        .drug-card {
            width: 200px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: white;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .drug-card:hover {
            transform: translateY(-5px);
        }
        .drug-image {
            height: 150px;
            margin-bottom: 10px;
        }
        .drug-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
            border-radius: 6px;
        }
        .drug-card h4 {
            margin: 10px 0 5px;
        }
        .drug-card p {
            margin: 0;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>

<header>
    <h1>Hanan Pharmacy</h1>
</header>

<!-- Drugs Section -->
<section class="drugs-section" style="padding: 40px 0;">
    <div class="container">
        <h2 style="text-align: center;">Available Drugs</h2>

        <!-- Search Bar -->
        <form method="GET" action="" class="search-bar">
            <input type="text" name="search" placeholder="Search for a drug...">
            <button type="submit">Search</button>
        </form>

        <!-- Drugs List -->
        <div class="drug-list">
            <?php
            // Mock drug data (10 items)
            $drugs = [
                ["name" => "Paracetamol", "price" => 25, "image" => "../assets/images/drug1.jpg"],
                ["name" => "Amoxicillin", "price" => 45, "image" => "../assets/images/drug2.jpg"],
                ["name" => "Ibuprofen", "price" => 35, "image" => "../assets/images/drug3.jpg"],
                ["name" => "Cough Syrup", "price" => 50, "image" => "../assets/images/drug4.jpg"],
                ["name" => "Cetirizine", "price" => 20, "image" => "../assets/images/drug5.jpg"],
                ["name" => "Metronidazole", "price" => 30, "image" => "../assets/images/drug6.jpg"],
                ["name" => "Vitamin C", "price" => 15, "image" => "../assets/images/drug7.jpg"],
                ["name" => "Loperamide", "price" => 18, "image" => "../assets/images/drug8.jpg"],
                ["name" => "Azithromycin", "price" => 55, "image" => "../assets/images/drug9.jpg"],
                ["name" => "Diclofenac", "price" => 22, "image" => "../assets/images/drug10.jpg"]
            ];

            // Filter with search query
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = strtolower(trim($_GET['search']));
                $drugs = array_filter($drugs, function($drug) use ($search) {
                    return strpos(strtolower($drug['name']), $search) !== false;
                });
            }

            // Show results
            if (count($drugs) > 0) {
                foreach ($drugs as $drug) {
                    echo '
                    <div class="drug-card">
                        <div class="drug-image">
                            <img src="' . $drug['image'] . '" alt="' . htmlspecialchars($drug['name']) . '">
                        </div>
                        <h4>' . htmlspecialchars($drug['name']) . '</h4>
                        <p>' . htmlspecialchars($drug['price']) . ' ETB</p>
                    </div>';
                }
            } else {
                echo "<p style='text-align: center;'>No drugs found matching your search.</p>";
            }
            ?>
        </div>
    </div>
</section>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Hanan Pharmacy. All rights reserved.</p>
</footer>

</body>
</html>
