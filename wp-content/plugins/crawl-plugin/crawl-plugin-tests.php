<?php

require_once 'path/to/wp-load.php';

use PHPUnit\Framework\TestCase;

class CrawlPluginTest extends TestCase
{
    public function test_deletePreviousResults()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'crawl_results';
        $wpdb->insert($table_name, array('name' => 'Test URL', 'url' => 'http://example.com'));
        $this->assertEquals(1, $wpdb->get_var("SELECT COUNT(*) FROM $table_name"));

        deletePreviousResults();

        $this->assertEquals(0, $wpdb->get_var("SELECT COUNT(*) FROM $table_name"));
    }

    public function test_deleteSitemapFile()
    {
        $file_path = WP_CONTENT_DIR . '/sitemap.html';
        file_put_contents($file_path, 'Test content');
        $this->assertTrue(file_exists($file_path));

        deleteSitemapFile();

        $this->assertFalse(file_exists($file_path));
    }

    public function test_crawlWebsite()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'crawl_results';

        crawlWebsite('http://example.com');

        $results = $wpdb->get_results("SELECT * FROM $table_name");
        $this->assertNotEmpty($results);
    }

    public function test_startsWith()
    {
        $haystack = 'Hello World';
        $needle = 'Hello';
        $this->assertTrue(startsWith($haystack, $needle));

        $needle = 'World';
        $this->assertFalse(startsWith($haystack, $needle));
    }

    public function test_displayResults()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'crawl_results';
        $wpdb->insert($table_name, array('name' => 'Test URL', 'url' => 'http://example.com'));
        $this->expectOutputRegex('/<h2>Crawl Results:<\/h2>/');

        displayResults();

        $wpdb->query("TRUNCATE TABLE $table_name");
    }

    public function test_saveHomePage()
    {
        $file_path = WP_CONTENT_DIR . '/home.html';
        saveHomePage();
        $this->assertTrue(file_exists($file_path));
        unlink($file_path);
    }

    public function test_createSitemap()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'crawl_results';
        $wpdb->insert($table_name, array('name' => 'Test URL', 'url' => 'http://example.com'));
        $file_path = WP_CONTENT_DIR . '/sitemap.html';
        createSitemap();
        $this->assertTrue(file_exists($file_path));
        $sitemap_content = file_get_contents($file_path);
        $this->assertStringContainsString('<title>Sitemap</title>', $sitemap_content);
        $this->assertStringContainsString('<h1>Sitemap - <a href="http://example.com">WP Media Dev Test - Zeyad</a></h1>', $sitemap_content);
        $this->assertStringContainsString('<li><a href="http://example.com"> - Test URL</a></li>', $sitemap_content);
        unlink($file_path);
        $wpdb->query("TRUNCATE TABLE $table_name");
    }

    public function test_runCrawl()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'crawl_results';
        $this->assertEquals(0, $wpdb->get_var("SELECT COUNT(*) FROM $table_name"));

        runCrawl();

        $this->assertGreaterThan(0, $wpdb->get_var("SELECT COUNT(*) FROM $table_name"));
        $this->assertTrue(file_exists(WP_CONTENT_DIR . '/home.html'));
        $this->assertTrue(file_exists(WP_CONTENT_DIR . '/sitemap.html'));

        $wpdb->query("TRUNCATE TABLE $table_name");
        unlink(WP_CONTENT_DIR . '/home.html');
        unlink(WP_CONTENT_DIR . '/sitemap.html');
    }

    // Integration tests

    public function test_crawl_plugin_hourly_event()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'crawl_results';
        $this->assertEquals(0, $wpdb->get_var("SELECT COUNT(*) FROM $table_name"));

        do_action('crawl_plugin_hourly_event');

        $this->assertGreaterThan(0, $wpdb->get_var("SELECT COUNT(*) FROM $table_name"));
        $this->assertTrue(file_exists(WP_CONTENT_DIR . '/home.html'));
        $this->assertTrue(file_exists(WP_CONTENT_DIR . '/sitemap.html'));

        $wpdb->query("TRUNCATE TABLE $table_name");
        unlink(WP_CONTENT_DIR . '/home.html');
        unlink(WP_CONTENT_DIR . '/sitemap.html');
    }

    public function test_crawl_plugin_menu()
    {
        global $submenu;

        ob_start();
        crawl_plugin_menu();
        ob_get_clean();

        $this->assertArrayHasKey('crawl-plugin', $submenu['options-general.php']);
    }

    public function test_crawl_plugin_page()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'crawl_results';

        ob_start();
        crawl_plugin_page();
        $output = ob_get_clean();

        // Test the presence of certain HTML elements
        $this->assertStringContainsString('<h1>Crawl Plugin</h1>', $output);
        $this->assertStringContainsString('<form method="post" action="">', $output);

        // Test when there are no crawl results
        $this->assertStringContainsString('<p>No crawl results available.', $output);
        $this->assertStringContainsString('Click the button below to trigger the crawl.</p>', $output);

        // Test when there are crawl results
        $wpdb->insert($table_name, array('name' => 'Test URL', 'url' => 'http://example.com'));
        ob_start();
        crawl_plugin_page();
        $output = ob_get_clean();
        $this->assertStringContainsString('<h2>Crawl Results:</h2>', $output);
        $this->assertStringContainsString('<li>http://example.com</li>', $output);

        $wpdb->query("TRUNCATE TABLE $table_name");
    }
}
