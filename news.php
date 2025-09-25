<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3rd Brigade News Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Lobster&family=Playfair+Display&family=Roboto&display=swap"
        rel="stylesheet">
    <link rel=" icon" href="./img/cadetlogo_prev_ui.png" type="image/png">
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Basic styles for the news grid - you can move this to your CSS file */
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .news-article {
            border: 1px solid #444;
            background-color: #333;
            color: #eee;
            border-radius: 5px;
            overflow: hidden;
        }

        .news-article img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .article-content {
            padding: 15px;
        }

        .article-content h3 {
            margin-top: 0;
        }

        .article-content a {
            color: #FFD700;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="logo">
        <a href="index.html"><img src="./img/cadetlogo_prev_ui.png" alt="Cadet Logo"></a>
    </div>
    <div class="logo-right">
        <a href="about.html"><img src="./img/image.png" alt="3rd Brigade Logo"></a>
    </div>
    <nav>
        <ul class="new">
            <li><a href="#home">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="careers.html">Careers</a></li>
            <li><a href="user_dashboard.php">DashBoard</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1>Latest Defense News</h1>
        <p>Updated automatically with the latest from around the globe.</p>

        <div class="news-grid">
            <?php
            // The path to the JSON file created by your Node.js script
            $jsonFilePath = __DIR__ . '/news-cache.json';

            if (file_exists($jsonFilePath)) {
                $jsonContent = file_get_contents($jsonFilePath);
                $articles = json_decode($jsonContent);

                if ($articles && !empty($articles)) {
                    foreach ($articles as $article) {
                        // Using htmlspecialchars to prevent XSS attacks
                        $title = htmlspecialchars($article->title);
                        $description = htmlspecialchars($article->description);
                        $url = htmlspecialchars($article->url);
                        $imageUrl = htmlspecialchars($article->urlToImage);

                        echo "
                        <div class='news-article'>
                            <img src='{$imageUrl}' alt='Image for {$title}'>
                            <div class='article-content'>
                                <h3>{$title}</h3>
                                <p>{$description}</p>
                                <a href='{$url}' target='_blank'>Read More</a>
                            </div>
                        </div>";
                    }
                } else {
                    echo '<p>No news articles are available at the moment.</p>';
                }
            } else {
                echo '<p>News feed is currently being prepared. Please check back in a moment.</p>';
            }
            ?>
        </div>

        <footer>© <?php echo date("Y"); ?> 3rd Brigade Headquarters. All Rights Reserved.</footer>

    </div>

    <?php
    // Include your website's footer if you have one
    // include 'includes/footer.php'; 
    ?>

</body>

</html>