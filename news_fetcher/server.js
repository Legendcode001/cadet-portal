require('dotenv').config();
const axios = require('axios');
const cron = require('node-cron');
const fs = require('fs');
const path = require('path');

// CRITICAL CHANGE: We define the path to save the file in the PARENT directory.
// path.join(__dirname, '../news-cache.json') means "go up one level from where I am, and create the file there".
const NEWS_CACHE_PATH = path.join(__dirname, '../news-cache.json');

const fetchMilitaryNews = async () => {
    console.log('Fetching latest military news...');
    try {
        const keywords = 'military OR defense OR geopolitics OR conflict OR army';
        const url = `https://newsapi.org/v2/everything?q=${encodeURIComponent(keywords)}&language=en&sortBy=publishedAt&apiKey=${process.env.NEWS_API_KEY}`;
        const response = await axios.get(url);

        // Filter for higher quality articles
        const highQualityArticles = response.data.articles.filter(
            article => article.urlToImage && article.description && article.title
        );

        fs.writeFileSync(NEWS_CACHE_PATH, JSON.stringify(highQualityArticles.slice(0, 50), null, 2)); // Save top 50 articles
        console.log(`Successfully fetched and saved ${highQualityArticles.slice(0, 50).length} articles to ${NEWS_CACHE_PATH}`);

    } catch (error) {
        console.error('Error fetching news:', error.message);
    }
};

// Schedule to run every 2 hours. You can change this. '0 8 * * *' for once a day at 8am.
cron.schedule('0 */2 * * *', () => {
    console.log('Running scheduled news fetch...');
    fetchMilitaryNews();
});

// Run it once immediately when the script starts
console.log('Script started. Initial news fetch will begin shortly.');
fetchMilitaryNews();