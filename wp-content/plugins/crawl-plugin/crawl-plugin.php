<?php

/**
 * Plugin Name: Crawl Plugin
 * Plugin URI:
 * Description: WP Media PHP Developer test - Crawl Plugin
 * Version: 1.0
 * Author: Zeyad Naguib
 * Author URI:
 */

// Function to delete the previous crawl results from the database
function deletePreviousResults()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'crawl_results';
    $wpdb->query("TRUNCATE TABLE $table_name");
}

// Function to delete the sitemap.html file
function deleteSitemapFile()
{
    $file_path = WP_CONTENT_DIR . '/sitemap.html';
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// Function to crawl the website and store results in the database
function crawlWebsite($url)
{
    global $wpdb;

    // Create a new DOMDocument instance
    $dom = new DOMDocument();

    // Suppress warnings and errors caused by malformed HTML
    libxml_use_internal_errors(true);

    // Load the HTML content from the specified URL
    $dom->loadHTMLFile($url);

    // Reset errors
    libxml_clear_errors();

    // Extract all anchor tags
    $anchors = $dom->getElementsByTagName('a');

    // Array to store the extracted URLs
    $urls = [];

    // Iterate over the anchor tags and extract the URLs
    foreach ($anchors as $anchor) {
        $href = $anchor->getAttribute('href');

        // Skip empty or non-internal URLs
        if (empty($href) || !startsWith($href, $url)) {
            continue;
        }

        // Remove any query parameters or fragments from the URL
        $parsedUrl = parse_url($href);
        $cleanUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];

        // Add the cleaned URL to the array
        $urls[] = $cleanUrl;
    }

    // Insert the extracted URLs into the database
    $table_name = $wpdb->prefix . 'crawl_results';
    foreach ($urls as $url) {
        $wpdb->insert($table_name, array('url' => $url));
    }
}

// Helper function to check if a string starts with a specific prefix
function startsWith($haystack, $needle)
{
    return strpos($haystack, $needle) === 0;
}

// Function to display the results on the admin page
function displayResults()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'crawl_results';
    $results = $wpdb->get_results("SELECT * FROM $table_name");

    // Display the results on the admin page
    echo '<h2>Crawl Results:</h2>';
    echo '<ul>';
    foreach ($results as $result) {
        echo '<li>' . $result->url . '</li>';
    }
    echo '</ul>';
}

// Function to save the home page as .html file
function saveHomePage()
{
    $file_path = WP_CONTENT_DIR . '/home.html';
    $homepage_content = file_get_contents(home_url());
    file_put_contents($file_path, $homepage_content);
}

// Function to create the sitemap.html file
function createSitemap()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'crawl_results';
    $results = $wpdb->get_results("SELECT * FROM $table_name");

    // Create the sitemap.html file with the results as a sitemap list structure
    $sitemap = '<ul>';
    foreach ($results as $result) {
        $sitemap .= '<li>' . $result->url . '</li>';
    }
    $sitemap .= '</ul>';

    $file_path = WP_CONTENT_DIR . '/sitemap.html';
    file_put_contents($file_path, $sitemap);
}

// Trigger the crawl when the admin initiates it
function triggerCrawl()
{
    // Delete previous results and sitemap file
    deletePreviousResults();
    deleteSitemapFile();

    // Crawl the website
    crawlWebsite(home_url());

    // Save the home page as .html file
    saveHomePage();

    // Create the sitemap.html file
    createSitemap();
}

// Register the menu page for the plugin under Settings
function crawl_plugin_menu()
{
    add_options_page(
        'Crawl Plugin',
        'Crawl Plugin',
        'manage_options',
        'crawl-plugin',
        'crawl_plugin_page'
    );
}

add_action('admin_menu', 'crawl_plugin_menu');

// Callback function to display the plugin page
function crawl_plugin_page()
{
    if (isset($_POST['crawl'])) {
        // Trigger the crawl process
        triggerCrawl();
    }

    // Display the plugin page content
    echo '<div class="wrap">';
    echo '<h1>Crawl Plugin</h1>';

    // Check if there are any crawl results
    global $wpdb;
    $table_name = $wpdb->prefix . 'crawl_results';
    $results_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    if ($results_count > 0) {
        // Display the crawl results
        displayResults();
    } else {
        // No crawl results available
        echo '<p>No crawl results available. Click the button below to trigger the crawl.</p>';
    }

    // Display the crawl trigger button
    echo '<form method="post" action="">';
    echo '<input type="submit" name="crawl" class="button button-primary" value="Trigger Crawl">';
    echo '</form>';

    echo '</div>';
}
