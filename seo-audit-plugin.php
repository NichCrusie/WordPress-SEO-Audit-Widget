<?php
/*
Plugin Name: SEO Audit Toolbox
Description: Perform a comprehensive SEO audit on a specified URL.
Version: 1.0
Author: Trustworthy Digital
*/

// Enqueue custom styles
function seo_audit_enqueue_styles() {
    wp_enqueue_style('seo-audit-styles', plugins_url('seo-audit-styles.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'seo_audit_enqueue_styles');

// Define the shortcode function
function seo_audit_shortcode() {
    ob_start();

    // Check if the form has been submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
        $url = esc_url_raw($_POST['url']);
        $target_keyword = sanitize_text_field($_POST['target_keyword']); // Sanitize input

        // Retrieve HTML content from the specified URL
        $html = file_get_contents($url);

        // Extract information from the HTML content
        $title = get_html_title($html);
        $meta_description = get_meta_description($html);
        $h1_count = get_h1_count($html);
        $h2_count = get_h2_count($html);
        $images_alt_check = check_images_alt_tags($html);
        $keyword_usage_check = check_keyword_usage($html, $target_keyword);
        $xml_sitemap_robots_check = check_xml_sitemap_in_robots($html);
        $canonical_url_check = check_canonical_url($html, $url);
        $responsive_design_check = check_responsive_design($html);
        $ssl_certificate_check = check_ssl_certificate($url);
        $social_media_links_check = check_social_media_links($html);

        // Perform the SEO audit checks
        $audit_results = perform_seo_audit(
            $title,
            $meta_description,
            $h1_count,
            $h2_count,
            $images_alt_check,
            $keyword_usage_check,
            $xml_sitemap_robots_check,
            $canonical_url_check,
            $responsive_design_check,
            $ssl_certificate_check,
            $social_media_links_check
        );

        // Display audit results
        echo '<div class="seo-audit-container">';
        echo '<h2 class="seo-audit-title">SEO Audit Checklist</h2>';
        echo '<ul class="seo-audit-list">';

        // Display individual audit check results
        echo_audit_check($audit_results['title'], 'Meta Title');
        echo_audit_check($audit_results['description'], 'Meta Description');
        echo_audit_check($audit_results['h1'], 'H1 Tag');
        echo_audit_check($audit_results['h2'], 'H2 Tags');
        echo_audit_check($audit_results['images_alt'], 'Images Alt Tags');
        echo_audit_check($audit_results['keyword_usage'], 'Keyword Usage');
        echo_audit_check($audit_results['xml_sitemap_robots'], 'XML Sitemap in Robots.txt');
        echo_audit_check($audit_results['canonical_url'], 'Canonical URL');
        echo_audit_check($audit_results['responsive_design'], 'Responsive Design');
        echo_audit_check($audit_results['ssl_certificate'], 'SSL Certificate');
        echo_audit_check($audit_results['social_media_links'], 'Social Media Links');

        echo '</ul>';

        // Calculate and display SEO score
        $seo_score = calculate_seo_score($audit_results);
        echo '<div class="seo-audit-score">';
        echo 'SEO Score: ' . $seo_score . '/100';
        echo '</div>';

        echo '</div>'; // Closing tag for 'seo-audit-container'
    } else {
        // Display URL input form
        echo '<form id="seo-audit-form" method="post">
                  <label for="url">URL:</label>
                  <input type="url" name="url" placeholder="Enter URL" required>
                  <label for="target_keyword">Target Keyword:</label>
                  <input type="text" name="target_keyword" placeholder="Enter Target Keyword" required>
                  <button type="submit">Generate Audit</button>
              </form>';
    }

    return ob_get_clean();
}
add_shortcode('seo_audit', 'seo_audit_shortcode');

// Function to perform SEO audit checks
function perform_seo_audit(
    $title,
    $meta_description,
    $h1_count,
    $h2_count,
    $images_alt_check,
    $keyword_usage_check,
    $xml_sitemap_robots_check,
    $canonical_url_check,
    $responsive_design_check,
    $ssl_certificate_check,
    $social_media_links_check
) {
    // Define audit checks and store results
    $audit_results = array(
        'title' => mb_strlen($title) <= 60,
        'description' => mb_strlen($meta_description) <= 160,
        'h1' => $h1_count === 1,
        'h2' => $h2_count >= 2,
        'images_alt' => $images_alt_check,
        'keyword_usage' => $keyword_usage_check,
        'xml_sitemap_robots' => $xml_sitemap_robots_check,
        'canonical_url' => $canonical_url_check,
        'responsive_design' => $responsive_design_check,
        'ssl_certificate' => $ssl_certificate_check,
        'social_media_links' => $social_media_links_check
        // Add more checks here if needed
    );

    return $audit_results;
}

// Function to display individual audit check result
function echo_audit_check($check_result, $check_name) {
    $status_class = $check_result ? 'passed' : 'failed';
    $status_text = $check_result ? 'Passed' : 'Failed';
    echo '<li class="' . $status_class . '">' . $check_name . ': ' . $status_text . '</li>';
}

// Function to check images alt tags
function check_images_alt_tags($html) {
    $img_tags = preg_match_all('/<img[^>]+alt=["\'](.*?)["\']/', $html, $matches);
    return ($img_tags > 0);
}

// Function to check keyword usage in content
function check_keyword_usage($html, $keyword) {
    $content = strip_tags($html);
    $keyword_occurrences = substr_count(strtolower($content), strtolower($keyword));
    return ($keyword_occurrences > 0);
}

// Function to check for XML sitemap presence in robots.txt
function check_xml_sitemap_in_robots($html) {
    preg_match('/<meta\s+name=["\']robots["\']\s+content=["\'](.*?)["\']/', $html, $matches);
    return (isset($matches[1]) && strpos($matches[1], 'sitemap.xml') !== false);
}

// Function to check for canonical URL tag
function check_canonical_url($html, $url) {
    preg_match('/<link\s+rel=["\']canonical["\']\s+href=["\'](.*?)["\']/', $html, $matches);
    return (isset($matches[1]) && $matches[1] === $url);
}

// Function to check for responsive design
function check_responsive_design($html) {
    return (strpos($html, 'viewport') !== false);
}

// Function to check for SSL certificate (https)
function check_ssl_certificate($url) {
    return (strpos($url, 'https://') === 0);
}

// Function to check for presence of social media links
function check_social_media_links($html) {
    return (strpos($html, 'facebook.com') !== false || strpos($html, 'twitter.com') !== false);
}

// Function to calculate SEO score based on audit results
function calculate_seo_score($audit_results) {
    $passed_checks = count(array_filter($audit_results));
    $total_checks = count($audit_results);
    $score = ($passed_checks / $total_checks) * 100;
    return round($score);
}

// Function to extract the title from HTML
function get_html_title($html) {
    preg_match('/<title>(.*?)<\/title>/', $html, $matches);
    return isset($matches[1]) ? $matches[1] : '';
}

// Function to extract meta description from HTML
function get_meta_description($html) {
    preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/', $html, $matches);
    return isset($matches[1]) ? $matches[1] : '';
}

// Function to count H1 tags in HTML
function get_h1_count($html) {
    return preg_match_all('/<h1(.*?)<\/h1>/', $html, $matches);
}

// Function to count H2 tags in HTML
function get_h2_count($html) {
    return preg_match_all('/<h2(.*?)<\/h2>/', $html, $matches);
}
?>
