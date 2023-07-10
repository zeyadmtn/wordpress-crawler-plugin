<?php

/**
 * Plugin Name: Crawl Plugin
 * Plugin URI:
 * Description: WP Media PHP Developer test - Crawl Plugin
 * Version: 1.0
 * Author: Zeyad Naguib
 * Author URI:
 */

function deletePreviousResults()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'crawl_results';
    $wpdb->query("TRUNCATE TABLE $table_name");
}

function deleteSitemapFile()
{
    $file_path = WP_CONTENT_DIR . '/sitemap.html';
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

function crawlWebsite($url)
{
    global $wpdb;

    try {

        $dom = new DOMDocument();

        libxml_use_internal_errors(true);

        $success = $dom->loadHTMLFile($url);

        if (!$success) {
            $error_message = "Error loading HTML from URL: " . $url;
            displayErrorMessage($error_message);
            return;
        }

        libxml_clear_errors();
        $anchors = $dom->getElementsByTagName('a');

        $urls = [];

        foreach ($anchors as $anchor) {
            $href = $anchor->getAttribute('href');

            if (empty($href) || !startsWith($href, $url)) {
                continue;
            }

            $name = $anchor->textContent;

            $parsedUrl = parse_url($href);
            $cleanUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];

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

function displayResults()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'crawl_results';
    $results = $wpdb->get_results("SELECT name, url FROM $table_name");

    echo '<h2>Crawl Results:</h2>';
    echo '<strong>WPMedia Dev Test - Zeyad</strong>';
    echo '<ul>';
    foreach ($results as $result) {
        echo '<li>' . $result->name . ' - <a href="' . $result->url . '">' . $result->url . '</a></li>';
    }
    echo '</ul>';
}

function saveHomePage()
{
    $file_path = WP_CONTENT_DIR . '/home.html';
    $homepage_content = file_get_contents(home_url());
    file_put_contents($file_path, $homepage_content);
}

function createSitemap()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'crawl_results';
    $results = $wpdb->get_results("SELECT * FROM $table_name");

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

function runCrawl()
{
    deletePreviousResults();
    deleteSitemapFile();

    crawlWebsite(home_url());

    saveHomePage();

    createSitemap();
}

function triggerCrawl()
{
    runCrawl();
    if (!wp_next_scheduled('crawl_plugin_hourly_event')) {
        wp_schedule_event(time(), 'hourly', 'crawl_plugin_hourly_event');
    }
}


function crawl_plugin_hourly_event()
{
    runCrawl();
}

add_action('crawl_plugin_hourly_event', 'crawl_plugin_hourly_event');


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

function crawl_plugin_page()
{
    if (isset($_POST['crawl'])) {
        triggerCrawl();
    }

    echo '<div class="wrap">';
    echo '<h1>Crawl Plugin</h1>';

    global $wpdb;
    $table_name = $wpdb->prefix . 'crawl_results';
    $results_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    if ($results_count > 0) {
        displayResults();
    } else {
        echo '<p>No crawl results available. Click the button below to trigger the crawl.</p>';
    }

    echo '<form method="post" action="">';
    echo '<input type="submit" name="crawl" class="button button-primary" value="Trigger Crawl">';
    echo '</form>';

    echo '</div>';
}
