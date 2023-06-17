<?php
/*
 * Plugin Name:       Sponsored Social Share Image - SSSI
 * Description:       Use this plugin to add a sponsored banner watermark to the featured image which will display on social media open graph image.
 * Version:           1.2
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Al Sayeed
 * Author URI:        https://sayeed.priyotrick.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sponsored-social-share-image
 */

add_theme_support("post-thumbnails");
add_action("admin_menu", "sssi_sayeed_register_custom_menu_page");
function sssi_sayeed_register_custom_menu_page() {
    add_menu_page(
        __("SSSI Settings", "sponsored-social-share-image"),
        __("SSSI Settings", "sponsored-social-share-image"),
        "manage_options",
        "sponsored-social-share-image-settings",
        "sssi_sayeed_sponsored_social_share_image_settings");
}

add_action("admin_init", "sssi_sayeed_register_plugin_settings");
function sssi_sayeed_register_plugin_settings() {
    register_setting("sssi_sayeed_plugin_settings", "enable_default_sponsor_banner");
    register_setting("sssi_sayeed_plugin_settings", "default_sponsor_banner");
    register_setting("sssi_sayeed_plugin_settings", "banner_position");
}


function sssi_sayeed_sponsored_social_share_image_settings() {
?>
    <div class="wrap">
        <h1>SSSI Settings</h1>
        <div><?php settings_errors(); ?></div>
        <form method="post" action="options.php">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label for="default_sponsor_banner">Enable Default Sponsor Banner</label></th>
                        <td>
                            <label for="enable_default_sponsor_banner">
                                <input name="enable_default_sponsor_banner" type="checkbox" id="enable_default_sponsor_banner" value="1" <?php checked(get_option("enable_default_sponsor_banner")); ?>>Enable Default Sponsor Banner
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="default_sponsor_banner_button">Default Sponsor Banner</label></th>
                        <td>
                            <input name="default_sponsor_banner" id="default_sponsor_banner" type="hidden" value="<?php echo esc_attr(get_option("default_sponsor_banner")); ?>"/>
                            <div class="default_image_preview">
                            <?php $default_sponsor_banner = wp_get_original_image_url(get_option("default_sponsor_banner")); ?>
                            <?php if ($default_sponsor_banner == ""): ?>
                                <img src="<?php echo esc_attr($default_sponsor_banner); ?>" id="default_sponsor_banner_preview_img"/>
                                <p class="no_default_image"><?php echo get_option("enable_default_sponsor_banner") ? "Please Select a Default Sponsor Banner!" : "No Default Sponsor Banner is Selected!"; ?></p>
                            <?php else: ?>
                                <img src="<?php echo esc_attr($default_sponsor_banner); ?>" id="default_sponsor_banner_preview_img"/>
                            <?php endif; ?>
                            </div>
                            <button id="default_sponsor_banner_button" class="button button-primary button-large">Select Image</button>
                            <p class="description">Recommended Size: 1200 by 80 pixels</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="banner_position">Banner Position</label></th>
                        <td>
                            <select name="banner_position" id="banner_position">
                                <option value="top" <?php selected(get_option("banner_position"), "top"); ?>>Top</option>
                                <option value="bottom" <?php selected(get_option("banner_position"), "bottom"); ?>>Bottom</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php settings_fields("sssi_sayeed_plugin_settings"); ?>
            <?php do_settings_sections("sssi_sayeed_plugin_settings"); ?>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}


add_action("admin_init", "sssi_sayeed_add_custom_post_field");
function sssi_sayeed_add_custom_post_field() {
    add_meta_box("post_meta_banner", "Sponsor Banner", "sssi_sayeed_post_meta_banner_cb", "post", "side");
}

function sssi_sayeed_post_meta_banner_cb() {
    global $post;
    $custom_meta = get_post_custom($post->ID);
    $has_post_thumbnail = has_post_thumbnail($post->ID);
    $sponsor_image_id = array_key_exists("sponsor_image_url", $custom_meta) ? $custom_meta["sponsor_image_url"][0] : "";
    $sponsor_image_url = $sponsor_image_id != "" ? wp_get_original_image_url($sponsor_image_id) : "";
    $default_sponsor_banner = get_option("enable_default_sponsor_banner") ? wp_get_original_image_url(get_option("default_sponsor_banner")) : "";
    echo '<input type="hidden" name="sponsor_image_url" id="sponsor_image_url" value="'.esc_attr($sponsor_image_url).'"/>';
    echo '<img src="'.esc_attr($sponsor_image_url).'" id="sponsor_banner_img" class="widefat"/>';
    echo '<button id="sponsor_select_button" class="button button-primary button-large">Select Image</button>';
    if ($has_post_thumbnail && ($sponsor_image_url || $default_sponsor_banner)) {
        echo '<div id="sponsor_banner_preview_section">';
        echo '<label id="sponsor_banner_preview_label">Preview:</label>';
        $thumbnail_path = wp_get_original_image_path(get_post_thumbnail_id($post->ID));
        $thumbnail_path = explode("uploads", $thumbnail_path)[1];
        if ($sponsor_image_url) {
            $sponsor_image_path = wp_get_original_image_path($sponsor_image_id);
            $sponsor_image_path = explode("uploads", $sponsor_image_path)[1];
        } else {
            $sponsor_image_path = wp_get_original_image_path(get_option("default_sponsor_banner"));
            $sponsor_image_path = explode("uploads", $sponsor_image_path)[1];
        }
        echo '<img src="'.plugin_dir_url(__FILE__).'thumbnail.php?p='.get_option("banner_position", "bottom").'&m='.$thumbnail_path.'&s='.$sponsor_image_path.'" id="sponsor_banner_preview" class="widefat"/>';
        echo '</div>';
    }
    if ($sponsor_image_url != "") {
        echo '<a href="#" id="remove_sponsor_banner">Remove Sponsor Banner</a>';
    }
}

add_action("admin_enqueue_scripts", "sssi_sayeed_sponsor_banner_select_script");
function sssi_sayeed_sponsor_banner_select_script() {
    wp_enqueue_media();
    wp_enqueue_script("mediaselect", plugin_dir_url(__FILE__)."/assets/js/mediaselect.js");
    wp_enqueue_style("sayeedstyle", plugin_dir_url(__FILE__)."/assets/css/style.css");
}

add_action("save_post", "sssi_sayeed_store_sponsor_banner");
function sssi_sayeed_store_sponsor_banner($post_id) {
    update_post_meta($post_id, "sponsor_image_url", sanitize_text_field($_POST["sponsor_image_url"] ?? ""));
}

add_action("sssi_sayeed_inert_image_to_header", "sssi_sayeed_inert_image_to_header_function");
add_filter("wpseo_opengraph_image", "sssi_sayeed_inert_image_to_header_function");
add_filter("rank_math/opengraph/facebook/image", "sssi_sayeed_inert_image_to_header_function");
add_filter("rank_math/opengraph/twitter/image", "sssi_sayeed_inert_image_to_header_function");
add_filter("aioseo_facebook_tags", "sssi_sayeed_inert_image_to_header_function");
add_filter("aioseo_twitter_tags", "sssi_sayeed_inert_image_to_header_function");


function sssi_sayeed_inert_image_to_header_function($url) {
    if (is_single()) {
        global $post;
        $custom_meta = get_post_custom($post->ID);
        $has_sponsor_image_id_in_post = array_key_exists("sponsor_image_url", $custom_meta) ? $custom_meta["sponsor_image_url"][0] : "";
        $has_image_in_id = $has_sponsor_image_id_in_post != "" ? wp_get_original_image_url($has_sponsor_image_id_in_post) : "";
        $has_post_thumbnail = has_post_thumbnail($post->ID);
        $is_default_enabled_a_has_image = get_option("enable_default_sponsor_banner") ? wp_get_original_image_url(get_option("default_sponsor_banner")) : "";
        if ($has_post_thumbnail && ($has_image_in_id || $is_default_enabled_a_has_image)) {
            $thumbnail_path = wp_get_original_image_path(get_post_thumbnail_id($post->ID));
            $thumbnail_path = explode("uploads", $thumbnail_path)[1];
            $position = get_option("banner_position", "bottom");
            if ($has_image_in_id) {
                $sponsor_image_path = wp_get_original_image_path($has_sponsor_image_id_in_post);
                $sponsor_image_path = explode("uploads", $sponsor_image_path)[1];
            } else {
                $sponsor_image_path = wp_get_original_image_path(get_option("default_sponsor_banner"));
                $sponsor_image_path = explode("uploads", $sponsor_image_path)[1];
            }
            $thumbnail_url = plugin_dir_url(__FILE__).'thumbnail.php?p='.$position.'&m='.$thumbnail_path.'&s='.$sponsor_image_path;
            if (current_action() == "sssi_sayeed_inert_image_to_header") {
                echo '<meta property="og:image" content="'.$thumbnail_url.'"/>';
                echo '<meta property="twitter:image" content="'.$thumbnail_url.'"/>';
            } elseif (current_filter() == "wpseo_opengraph_image" || current_filter() == "rank_math/opengraph/facebook/image" || current_filter() == "rank_math/opengraph/twitter/image") {
                $url = $thumbnail_url;
                return $url;
            } elseif (current_filter() == "aioseo_facebook_tags") {
                $url["og:image"] = $thumbnail_url;
                return $url;
            } elseif (current_filter() == "aioseo_twitter_tags") {
                $url["twitter:image"] = $thumbnail_url;
                return $url;
            }
        }
    }
}
add_action("wp_head", function() {   
    do_action("sssi_sayeed_inert_image_to_header");
});

function add_plugin_link($plugin_actions, $plugin_file) {
    $new_actions = [];
    if ($plugin_file == "sponsored-social-share-image/sponsored-social-share-image.php") {
        $url = admin_url("admin.php?page=sponsored-social-share-image-settings");
        $new_actions["settings"] = sprintf('<a href="%s">%s</a>', $url, __("SSSI Settings", "sponsored-social-share-image"));
    }
    return array_merge($plugin_actions, $new_actions);
}
add_filter("plugin_action_links", "add_plugin_link", 10, 2);
?>
