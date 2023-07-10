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

    try {

        // Create a new DOMDocument instance
        $dom = new DOMDocument();
    
        // Suppress warnings and errors caused by malformed HTML
        libxml_use_internal_errors(true);
    
        // Load the HTML content from the specified URL
        $success = $dom->loadHTMLFile($url);
    
        // Check if the HTML load was successful
        if (!$success) {
            $error_message = "Error loading HTML from URL: " . $url;
            displayErrorMessage($error_message);
            return;
        }
    
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
    
            // Get the name from the text content of the anchor
            $name = $anchor->textContent;
    
            // Remove any query parameters or fragments from the URL
            $parsedUrl = parse_url($href);
            $cleanUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];
    
            // Add the name and URL to the array
            $urls[] = array(
                'name' => $name,
                'url' => $cleanUrl
            );
        }
    
        // Insert the extracted URLs into the database
        $table_name = $wpdb->prefix . 'crawl_results';
        foreach ($urls as $url) {
            $wpdb->insert($table_name, $url);
        }
    } catch (Exception $e) {
        $error_message = "An Unexpected Error has Occured Crawling the Links in this link: " . $url . " Please try again later or contact the plugin adminisrator.";
        displayErrorMessage($error_message);
        return;
    }

}

// Function to display an error message to the admin
function displayErrorMessage($message)
{
    echo '<div class="error notice">';
    echo '<p>' . $message . '</p>';
    echo '</div>';
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
    $results = $wpdb->get_results("SELECT name, url FROM $table_name");

    // Display the results on the admin page
    echo '<h2>Crawl Results:</h2>';
    echo '<strong>WPMedia Dev Test - Zeyad</strong>';
    echo '<ul>';
    foreach ($results as $result) {
        echo '<li>' . $result->name . ' - <a href="' . $result->url . '">' . $result->url . '</a></li>';
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
// Function to create the sitemap.html file
function createSitemap()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'crawl_results';
    $results = $wpdb->get_results("SELECT * FROM $table_name");

    // Create the sitemap.html file with the results as a sitemap list structure
    $sitemap = '<!DOCTYPE html>';
    $sitemap .= '<html>';
    $sitemap .= '<head>';
    $sitemap .= '<meta charset="UTF-8">';
    $sitemap .= '<title>Sitemap</title>';
    $sitemap .= '<style>';
    $sitemap .= 'body { font-family: Arial, sans-serif; margin: 20px 40px; }';
    $sitemap .= 'h1 { text-align: center; }';
    $sitemap .= 'ul { list-style-type: none; padding: 0; }';
    $sitemap .= 'li { margin-bottom: 5px; }';
    $sitemap .= 'a { color: #007bff; text-decoration: none; }';
    $sitemap .= 'a:hover { text-decoration: underline; }';
    $sitemap .= '</style>';
    $sitemap .= '</head>';
    $sitemap .= '<body>';
    $sitemap .= '<h1>Sitemap - <a href="' . home_url() . '">WP Media Dev Test - Zeyad</a></h1>';
    $sitemap .= '<ul>';
    $sitemap .= '<h3>WPMedia DevTest - Zeyad</h3>';
    $sitemap .= '<hr>';
    foreach ($results as $result) {
        $url = $result->url;
        $name = $result->name;
        $name = html_entity_decode($name, ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars($name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $sitemap .= '<li><a href="' . $url . '">' . ' - ' . $name . '</a></li>';
    }
    $sitemap .= '</ul>';
    $sitemap .= '</body>';
    $sitemap .= '</html>';


    $file_path = WP_CONTENT_DIR . '/sitemap.html';
    file_put_contents($file_path, $sitemap);
}

// Trigger the crawl when the admin initiates it
function runCrawl() {
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

function triggerCrawl()
{
    runCrawl();
    // Schedule the crawl to run every hour
    if (!wp_next_scheduled('crawl_plugin_hourly_event')) {
        wp_schedule_event(time(), 'hourly', 'crawl_plugin_hourly_event');
    }
}


// Function to be executed on the hourly event
function crawl_plugin_hourly_event()
{
    runCrawl();
}

// Register the hourly event
add_action('crawl_plugin_hourly_event', 'crawl_plugin_hourly_event');


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
