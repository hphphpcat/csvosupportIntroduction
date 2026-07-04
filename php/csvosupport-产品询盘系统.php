<?php
/**
 * Site Workbench Snippet Export
 * Name: csvosupport 产品询盘系统
 * Enabled: yes
 * Shortcodes: csop_products, ca_products, ca_products_w1, csop_header, csop_footer, csop_home, csop_page_blog, csop_page_about, csop_page_price, csop_page_contact, csop_page_en, csop_page_zh_tw
 * Updated At: 2026-07-02 21:53:01
 * Original ID: snippet_6e6a91a9-789d-484b-9486-6d830bc3a423
 */
/**
 * csvosupport product inquiry system.
 * Shortcode: [csop_products]
 */
defined('ABSPATH') || exit;

add_action('admin_menu', 'csop_visual_admin_menu', 1);
add_action('admin_menu', 'csop_ai_seo_admin_menu', 2);
add_action('send_headers', 'csop_send_security_headers', 5);
add_action('init', 'csop_disable_external_wp_emoji', 1);
add_action('init', 'csop_pretty_page_lang_redirect_early', 2);
add_action('init', 'csop_register_product_system');
add_action('init', 'csop_register_pretty_article_rewrites', 20);
add_action('init', 'csop_apply_csvosupport_content_brand', 8);
add_action('init', 'csop_apply_oavo_contacts', 9);
add_action('init', 'csop_apply_local_external_media_urls', 12);
add_action('template_redirect', 'csop_seo_render_sitemap', 0);
add_action('template_redirect', 'csop_pretty_article_redirect_legacy', 1);
add_action('template_redirect', 'csop_pretty_page_lang_redirect_legacy', 2);
add_action('add_meta_boxes', 'csop_register_meta_boxes');
add_action('save_post_csop_product', 'csop_save_product_meta', 10, 2);
add_action('save_post_post', 'csop_ml_save_post_translations', 10, 2);
add_action('admin_enqueue_scripts', 'csop_admin_assets');
add_action('wp_head', 'csop_front_css', 30);
add_action('admin_post_csop_submit_inquiry', 'csop_handle_inquiry');
add_action('admin_post_nopriv_csop_submit_inquiry', 'csop_handle_inquiry');
add_filter('manage_csop_inquiry_posts_columns', 'csop_inquiry_admin_columns');
add_action('manage_csop_inquiry_posts_custom_column', 'csop_inquiry_admin_column_content', 10, 2);
add_action('wp_head', 'csop_ai_seo_dedupe_core_meta', 1);
add_action('wp_head', 'csop_ai_seo_output_meta', 2);
add_filter('manage_post_posts_columns', 'csop_ml_post_columns');
add_action('manage_post_posts_custom_column', 'csop_ml_post_column_content', 10, 2);
add_filter('the_title', 'csop_ml_filter_post_title', 9, 2);
add_filter('the_content', 'csop_ml_filter_post_content', 1);
add_filter('get_the_excerpt', 'csop_ml_filter_post_excerpt', 9, 2);
add_filter('document_title_parts', 'csop_ml_document_title_parts', 20);
add_filter('document_title_parts', 'csop_ai_seo_document_title_parts', 35);
add_filter('robots_txt', 'csop_seo_robots_txt', 20, 2);
add_filter('query_vars', 'csop_pretty_article_query_vars');
add_filter('redirect_canonical', 'csop_pretty_article_disable_canonical_redirect', 10, 2);
add_filter('wp_get_attachment_image_attributes', 'csop_seo_attachment_image_alt', 20, 3);
add_shortcode('csop_products', 'csop_products_shortcode');
add_shortcode('ca_products', 'csop_products_shortcode');
add_shortcode('ca_products_w1', 'csop_products_shortcode');
add_filter('the_content', 'csop_render_exported_shortcodes', 20);
add_filter('the_content', 'csop_article_layout_wrap', 25);
add_action('wp_head', 'csop_article_layout_css', 32);

function csop_visual_admin_menu() {
    if (!function_exists('cc_site_visual_dashboard_page')) {
        function cc_site_visual_dashboard_page() {
            csop_visual_dashboard_page();
        }
    }

    add_menu_page(
        '网站可视化编辑',
        '网站可视化编辑',
        'manage_options',
        'cc-site-visual',
        'cc_site_visual_dashboard_page',
        'dashicons-admin-customizer',
        3
    );
}

function csop_visual_dashboard_page() {
    if (!current_user_can('manage_options')) return;

    $products = wp_count_posts('csop_product');
    $inquiries = wp_count_posts('csop_inquiry');
    $product_count = isset($products->publish) ? intval($products->publish) : 0;
    $inquiry_count = isset($inquiries->publish) ? intval($inquiries->publish) : 0;

    $modules = array(
        array(
            'title' => '首页',
            'desc' => '按 demo 首页结构渲染，适合检查首屏、服务范围、案例和评价。',
            'edit' => admin_url('admin.php?page=csop-homepage'),
            'preview' => home_url('/'),
            'shortcode' => '[csop_home]',
            'badge' => 'Hero + Demo Blocks',
        ),
        array(
            'title' => '眉页脚页',
            'desc' => '统一维护 Logo、导航、语言菜单、页脚二维码、联系方式和版权。',
            'edit' => admin_url('admin.php?page=csop-header-footer'),
            'preview' => home_url('/'),
            'shortcode' => '[csop_header] / [csop_footer]',
            'badge' => 'Global Layout',
        ),
        array(
            'title' => '产品与询盘',
            'desc' => '维护服务产品、列表页文案、详情参数，并查看客户提交的询盘。',
            'edit' => admin_url('admin.php?page=csop-product-settings'),
            'preview' => home_url('/products/'),
            'shortcode' => '[csop_products]',
            'badge' => $product_count . ' 个产品 / ' . $inquiry_count . ' 条询盘',
        ),
        array(
            'title' => '普通界面',
            'desc' => '管理博客、关于、价格、联系等中文基准界面；英文和繁中从同一界面翻译生成。',
            'edit' => admin_url('admin.php?page=csop-menu-pages'),
            'preview' => home_url('/about_us/'),
            'shortcode' => '[csop_page_*]',
            'badge' => '中文基准界面',
        ),
        array(
            'title' => '博客文章',
            'desc' => '面试真题 / 博客现在是真实文章系统，支持简体中文、繁体中文、English 三个文章版本。',
            'edit' => admin_url('admin.php?page=csop-post-languages'),
            'preview' => home_url('/blog/'),
            'shortcode' => '[csop_page_blog]',
            'badge' => (function () { $c = wp_count_posts('post'); return (isset($c->publish) ? (int) $c->publish : 0) . ' 篇已发布'; })(),
        ),
    );

    $quick = array(
        array('label' => '写新文章', 'url' => admin_url('post-new.php'), 'icon' => 'dashicons-edit'),
        array('label' => '文章三语言', 'url' => admin_url('admin.php?page=csop-post-languages'), 'icon' => 'dashicons-translation'),
        array('label' => '管理博客文章', 'url' => admin_url('edit.php'), 'icon' => 'dashicons-admin-post'),
        array('label' => '管理服务产品', 'url' => admin_url('edit.php?post_type=csop_product'), 'icon' => 'dashicons-products'),
        array('label' => '查看服务询盘', 'url' => admin_url('edit.php?post_type=csop_inquiry'), 'icon' => 'dashicons-email-alt2'),
        array('label' => '前台博客', 'url' => home_url('/blog/'), 'icon' => 'dashicons-external'),
    );
    ?>
    <div class="wrap csop-dashboard-wrap">
        <div class="csop-dashboard-hero">
            <div>
                <p class="csop-dashboard-kicker">csvosupport Visual Console</p>
                <h1>网站可视化编辑</h1>
                <p>从这里进入首页、眉页脚、产品询盘和普通界面的可视化设置。每个模块都保留右侧预览或前台预览入口，客户可以按模块逐项维护。</p>
            </div>
            <div class="csop-dashboard-hero-actions">
                <a class="button button-primary" href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener">打开前台首页</a>
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=csop-product-settings')); ?>">产品页设置</a>
            </div>
        </div>

        <div class="csop-dashboard-grid">
            <?php foreach ($modules as $module): ?>
                <div class="csop-dashboard-card">
                    <span class="csop-dashboard-badge"><?php echo esc_html($module['badge']); ?></span>
                    <h2><?php echo esc_html($module['title']); ?></h2>
                    <p><?php echo esc_html($module['desc']); ?></p>
                    <code><?php echo esc_html($module['shortcode']); ?></code>
                    <div class="csop-dashboard-actions">
                        <a class="button button-primary" href="<?php echo esc_url($module['edit']); ?>">进入编辑</a>
                        <a class="button" href="<?php echo esc_url($module['preview']); ?>" target="_blank" rel="noopener">前台预览</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="csop-dashboard-lower">
            <div class="csop-dashboard-panel">
                <h2>快速管理</h2>
                <div class="csop-dashboard-quick">
                    <?php foreach ($quick as $item): ?>
                        <a href="<?php echo esc_url($item['url']); ?>" class="csop-dashboard-quick-item" <?php echo strpos($item['url'], home_url('/')) === 0 ? 'target="_blank" rel="noopener"' : ''; ?>>
                            <span class="dashicons <?php echo esc_attr($item['icon']); ?>"></span>
                            <strong><?php echo esc_html($item['label']); ?></strong>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="csop-dashboard-panel">
                <h2>推荐页面块顺序</h2>
                <p><code>[csop_header]</code> → 页面主体短代码 → <code>[csop_footer]</code></p>
                <p>首页主体：<code>[csop_home]</code>；产品主体：<code>[csop_products]</code>；普通界面从“普通界面设置”选择对应短代码。</p>
            </div>
        </div>
    </div>
    <?php
}

function csop_option_name() {
    return 'csop_product_page_options_v1';
}

function csop_send_security_headers() {
    if (!is_ssl() || headers_sent()) return;

    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');

    if (!is_admin()) {
        header('Content-Security-Policy: upgrade-insecure-requests; block-all-mixed-content');
    }
}

function csop_disable_external_wp_emoji() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    add_filter('emoji_svg_url', '__return_false');
}

function csop_pretty_article_category_slugs() {
    $slugs = array('oavo', 'oa');
    $terms = get_terms(array(
        'taxonomy' => 'category',
        'hide_empty' => false,
        'fields' => 'slugs',
    ));

    if (!is_wp_error($terms) && is_array($terms)) {
        foreach ($terms as $slug) {
            $slug = sanitize_title($slug);
            if ($slug !== '' && $slug !== 'uncategorized') $slugs[] = $slug;
        }
    }

    return array_values(array_unique($slugs));
}

function csop_pretty_page_slugs() {
    return array('products', 'blog', 'about_us', 'price', 'contact');
}

function csop_register_pretty_article_rewrites() {
    $slugs = csop_pretty_article_category_slugs();

    $front_page_id = (int) get_option('page_on_front');
    if ($front_page_id > 0) {
        add_rewrite_rule('^zh_tw/?$', 'index.php?page_id=' . $front_page_id . '&csop_lang=zh_tw', 'top');
        add_rewrite_rule('^en/?$', 'index.php?page_id=' . $front_page_id . '&csop_lang=en', 'top');
    } else {
        add_rewrite_rule('^zh_tw/?$', 'index.php?csop_lang=zh_tw', 'top');
        add_rewrite_rule('^en/?$', 'index.php?csop_lang=en', 'top');
    }

    foreach ($slugs as $slug) {
        $pattern = preg_quote($slug, '#');
        add_rewrite_rule('^' . $pattern . '/([^/]+)/?$', 'index.php?post_type=post&name=$matches[1]&csop_lang=zh', 'top');
        add_rewrite_rule('^zh_tw/' . $pattern . '/([^/]+)/?$', 'index.php?post_type=post&name=$matches[1]&csop_lang=zh_tw', 'top');
        add_rewrite_rule('^en/' . $pattern . '/([^/]+)/?$', 'index.php?post_type=post&name=$matches[1]&csop_lang=en', 'top');
    }

    $page_pattern = implode('|', array_map(function ($slug) {
        return preg_quote($slug, '#');
    }, csop_pretty_page_slugs()));
    if ($page_pattern !== '') {
        add_rewrite_rule('^zh_tw/(' . $page_pattern . ')/?$', 'index.php?pagename=$matches[1]&csop_lang=zh_tw', 'top');
        add_rewrite_rule('^en/(' . $page_pattern . ')/?$', 'index.php?pagename=$matches[1]&csop_lang=en', 'top');
    }

    $hash = 'v7:' . md5(implode('|', array_merge($slugs, csop_pretty_page_slugs(), array('front-' . $front_page_id, 'en-home', 'zh_tw-home'))));
    if (get_option('csop_pretty_article_rewrite_hash') !== $hash) {
        flush_rewrite_rules(false);
        update_option('csop_pretty_article_rewrite_hash', $hash, false);
    }
}

function csop_pretty_article_query_vars($vars) {
    if (!in_array('csop_lang', $vars, true)) $vars[] = 'csop_lang';
    return $vars;
}

function csop_pretty_lang_from_request_path($path = '') {
    if ($path === '') {
        $uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
        $path = (string) parse_url($uri, PHP_URL_PATH);
    }

    $path = '/' . trim((string) $path, '/') . '/';
    if (strpos($path, '/zh_tw/') === 0) return 'zh_tw';
    if (strpos($path, '/en/') === 0) return 'en';
    return '';
}

function csop_pretty_is_default_zh_request_path($path = '') {
    if ($path === '') {
        $uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
        $path = (string) parse_url($uri, PHP_URL_PATH);
    }

    $path = trim((string) $path, '/');
    if ($path === '') return true;
    if (preg_match('#^(en|zh_tw)(/|$)#', $path)) return false;
    if (in_array($path, csop_pretty_page_slugs(), true)) return true;

    $parts = explode('/', $path);
    if (count($parts) === 2 && in_array($parts[0], csop_pretty_article_category_slugs(), true)) {
        return true;
    }

    return false;
}

function csop_pretty_article_section($post_id) {
    $categories = get_the_category($post_id);
    if (is_array($categories)) {
        foreach ($categories as $category) {
            if (!empty($category->slug) && $category->slug !== 'uncategorized') {
                return sanitize_title($category->slug);
            }
        }
    }

    return 'oavo';
}

function csop_pretty_article_url($post_id, $lang = 'zh') {
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'post') return '';

    $allowed = array('zh', 'zh_tw', 'en');
    if (!in_array($lang, $allowed, true)) $lang = 'zh';

    $slug = $post->post_name ? $post->post_name : sanitize_title($post->post_title);
    if ($slug === '') $slug = (string) $post->ID;

    $section = csop_pretty_article_section($post->ID);
    $prefix = '';
    if ($lang === 'zh_tw') $prefix = 'zh_tw/';
    if ($lang === 'en') $prefix = 'en/';

    return home_url('/' . $prefix . $section . '/' . $slug . '/');
}

function csop_pretty_page_lang_url($url, $lang = 'zh') {
    $allowed = array('zh', 'zh_tw', 'en');
    if (!in_array($lang, $allowed, true)) $lang = 'zh';

    $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
    if ($path === '' || in_array($path, array('en', 'zh_tw'), true)) {
        $target = ($lang === 'zh') ? home_url('/') : home_url('/' . $lang . '/');
        return csop_pretty_preserve_query_without_lang($target, $url);
    }

    if (preg_match('#^(?:en|zh_tw)/(.+)$#', $path, $matches)) {
        $path = trim($matches[1], '/');
    }

    if (!in_array($path, csop_pretty_page_slugs(), true)) return '';

    $prefix = ($lang === 'zh') ? '' : $lang . '/';
    $target = home_url('/' . $prefix . $path . '/');
    return csop_pretty_preserve_query_without_lang($target, $url);
}

function csop_pretty_preserve_query_without_lang($target, $source_url) {
    $query = (string) parse_url($source_url, PHP_URL_QUERY);
    if ($query === '') return $target;

    $args = array();
    parse_str($query, $args);
    unset($args['csop_lang']);
    if (!$args) return $target;

    return add_query_arg(array_map('sanitize_text_field', wp_unslash($args)), $target);
}

function csop_pretty_article_post_id_from_url($url) {
    $post_id = url_to_postid($url);
    if ($post_id && get_post_type($post_id) === 'post') return (int) $post_id;

    $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
    if ($path === '') return 0;

    $slug = '';
    if (preg_match('#^(?:zh_tw|en)/[^/]+/([^/]+)/?$#', $path, $matches)) {
        $slug = $matches[1];
    } elseif (preg_match('#^[^/]+/([^/]+)/?$#', $path, $matches)) {
        $slug = $matches[1];
    }

    if ($slug === '') return 0;

    $post = get_page_by_path(sanitize_title($slug), OBJECT, 'post');
    return ($post && $post->post_type === 'post') ? (int) $post->ID : 0;
}

function csop_pretty_article_disable_canonical_redirect($redirect_url, $requested_url) {
    if (csop_pretty_article_post_id_from_url($requested_url)) return false;
    return $redirect_url;
}

function csop_pretty_article_redirect_legacy() {
    if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST) || is_feed() || is_trackback()) return;
    if (!is_singular('post')) return;

    $post_id = get_queried_object_id();
    if (!$post_id) return;

    $lang = function_exists('csop_ml_current_lang') ? csop_ml_current_lang() : 'zh';
    $target = csop_pretty_article_url($post_id, $lang);
    if ($target === '') return;

    $uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
    $current_path = trailingslashit('/' . trim((string) parse_url($uri, PHP_URL_PATH), '/'));
    $target_path = trailingslashit((string) parse_url($target, PHP_URL_PATH));

    if (isset($_GET['csop_lang']) || $current_path !== $target_path) {
        wp_safe_redirect($target, 301);
        exit;
    }
}

function csop_pretty_page_lang_redirect_early() {
    if (is_admin() || (function_exists('wp_doing_ajax') && wp_doing_ajax()) || headers_sent()) return;
    if (!isset($_GET['csop_lang'])) return;

    $allowed = array('zh', 'zh_tw', 'en');
    $lang = sanitize_key(wp_unslash($_GET['csop_lang']));
    if (!in_array($lang, $allowed, true)) return;

    $uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
    $target = csop_pretty_page_lang_url(home_url($uri), $lang);
    if ($target === '') return;

    $current_without_lang = csop_pretty_preserve_query_without_lang(home_url((string) parse_url($uri, PHP_URL_PATH)), home_url($uri));
    if (untrailingslashit($target) !== untrailingslashit($current_without_lang)) {
        wp_safe_redirect($target, 301);
        exit;
    }
}

function csop_pretty_page_lang_redirect_legacy() {
    if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST) || is_feed() || is_trackback()) return;
    if (is_singular('post') || !isset($_GET['csop_lang'])) return;

    $allowed = array('zh', 'zh_tw', 'en');
    $lang = sanitize_key(wp_unslash($_GET['csop_lang']));
    if (!in_array($lang, $allowed, true)) return;

    $uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
    $target = csop_pretty_page_lang_url(home_url($uri), $lang);
    if ($target === '') return;

    $current_without_lang = csop_pretty_preserve_query_without_lang(home_url((string) parse_url($uri, PHP_URL_PATH)), home_url($uri));
    if (untrailingslashit($target) !== untrailingslashit($current_without_lang)) {
        wp_safe_redirect($target, 301);
        exit;
    }
}

function csop_seo_attachment_image_alt($attr, $attachment, $size) {
    if (!is_array($attr)) $attr = array();
    if (!empty($attr['alt'])) return $attr;

    $alt = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
    if (!is_string($alt) || trim($alt) === '') $alt = get_the_title($attachment);
    if (!is_string($alt) || trim($alt) === '') $alt = 'csvosupport interview support image';

    $attr['alt'] = csop_seo_normalize_image_alt($alt);
    return $attr;
}

function csop_seo_apply_image_alts($html) {
    if (!is_string($html) || stripos($html, '<img') === false) return $html;

    return preg_replace_callback('/<img\b[^>]*>/i', function ($matches) {
        $tag = $matches[0];
        if (preg_match('/\salt\s*=\s*(["\'])(.*?)\1/is', $tag, $alt_match) && trim(html_entity_decode($alt_match[2], ENT_QUOTES, 'UTF-8')) !== '') {
            return $tag;
        }

        $alt = csop_seo_image_alt_from_tag($tag);
        $alt_attr = ' alt="' . esc_attr($alt) . '"';

        if (preg_match('/\salt\s*=\s*(["\'])(.*?)\1/is', $tag)) {
            return preg_replace('/\salt\s*=\s*(["\'])(.*?)\1/is', $alt_attr, $tag, 1);
        }

        if (substr($tag, -2) === '/>') {
            return substr($tag, 0, -2) . $alt_attr . ' />';
        }

        return rtrim($tag, '>') . $alt_attr . '>';
    }, $html);
}

function csop_seo_img_attr($tag, $name) {
    if (preg_match('/\s' . preg_quote($name, '/') . '\s*=\s*(["\'])(.*?)\1/is', $tag, $matches)) {
        return html_entity_decode($matches[2], ENT_QUOTES, 'UTF-8');
    }

    return '';
}

function csop_seo_image_alt_from_tag($tag) {
    $title = trim(csop_seo_img_attr($tag, 'title'));
    if ($title !== '' && !preg_match('/^(wechatimg|image$|\d+$)/i', $title)) {
        return csop_seo_normalize_image_alt($title);
    }

    $aria = trim(csop_seo_img_attr($tag, 'aria-label'));
    if ($aria !== '') return csop_seo_normalize_image_alt($aria);

    $src = csop_seo_img_attr($tag, 'src');
    $filename = rawurldecode((string) basename((string) parse_url($src, PHP_URL_PATH)));
    $name = preg_replace('/\.(jpe?g|png|gif|webp|svg|avif)$/i', '', $filename);
    $name = preg_replace('/-\d+x\d+(?:-\d+)?$/', '', $name);
    $name = preg_replace('/^(demo|extra|local|main|oavo)-ext-\d+-/i', '', $name);
    $name = preg_replace('/-\d+$/', '', $name);

    if (preg_match('/logo/i', $name)) return 'csvosupport logo';
    if (preg_match('/qr|wechat[-_ ]?qr/i', $name)) return 'csvosupport WeChat contact QR code';
    if (preg_match('/review|wechatimg/i', $name)) return 'csvosupport client review screenshot';
    if (preg_match('/offer/i', $name)) return 'csvosupport successful offer case image';
    if (preg_match('/oa|hackerrank|codesignal|interview|leetcode/i', $name)) return 'csvosupport online assessment and interview support image';

    $name = preg_replace('/[-_]+/', ' ', $name);
    $name = trim(preg_replace('/\s+/', ' ', $name));
    if ($name === '') $name = 'interview support image';

    return csop_seo_normalize_image_alt($name);
}

function csop_seo_normalize_image_alt($alt) {
    $alt = wp_strip_all_tags((string) $alt);
    $alt = html_entity_decode($alt, ENT_QUOTES, 'UTF-8');
    $alt = trim(preg_replace('/\s+/', ' ', $alt));
    if ($alt === '') $alt = 'interview support image';
    if (stripos($alt, 'csvosupport') === false) $alt .= ' - csvosupport';
    return $alt;
}

/**
 * Some page templates print a raw <title> from the page name in addition to the
 * wp_head() title, so pages ship two <title> tags. Keep only the last one inside
 * <head> (the wp_head / AI SEO title, which document_title_parts filters control)
 * and drop the earlier duplicates so AI SEO stays authoritative.
 */
function csop_seo_dedupe_head_title($html) {
    if (!is_string($html) || stripos($html, '<title') === false) return $html;
    if (!preg_match('/<head\b[^>]*>(.*?)<\/head>/is', $html, $m)) return $html;

    $head = $m[1];
    if (!preg_match_all('/<title\b[^>]*>.*?<\/title>/is', $head, $titles) || count($titles[0]) < 2) {
        return $html;
    }

    $duplicates = $titles[0];
    array_pop($duplicates); // keep the last <title> (wp_head / AI SEO output)

    $new_head = $head;
    foreach ($duplicates as $dup) {
        $pos = strpos($new_head, $dup);
        if ($pos !== false) {
            $new_head = substr($new_head, 0, $pos) . substr($new_head, $pos + strlen($dup));
        }
    }

    if ($new_head === $head) return $html;

    $pos = strpos($html, $head);
    if ($pos === false) return $html;
    return substr_replace($html, $new_head, $pos, strlen($head));
}

/**
 * Soften "代写 / 代做 / 代面 / proxy / ghostwriting" wording into "辅助 / assistance"
 * across the three languages. Runs on the final HTML per current language so the
 * translation dictionaries don't each need editing. strtr does longest-key-first
 * matching with no re-processing, which handles overlapping keys like 代面试/代面.
 */
function csop_seo_soften_wording_map($lang) {
    if ($lang === 'en') {
        return array(
            '🔹 Option 2: full-substitution on-camera proxy interview' => '🔹 Option 2: full on-camera interview assistance',
            '-- Full-proxy interview: ideal when interviewer and role are in different orgs.' => '-- Full on-camera interview assistance: ideal when interviewer and role are in different orgs.',
            'OA ghostwriting' => 'OA assistance',
            'VO proxy' => 'VO assistance',
            'Proxy interview recordings' => 'Interview assistance recordings',
            'Live VO proxy interview' => 'Live VO interview assistance',
            'Silicon Valley proxy interview' => 'Silicon Valley interview assistance',
            'Pinterest proxy interview' => 'Pinterest interview assistance',
            'Bloomberg proxy interview' => 'Bloomberg interview assistance',
            'proxy interview services' => 'interview assistance services',
            'interview proxy services' => 'interview assistance services',
            'Interview proxy service' => 'Interview assistance service',
            'interview proxy service' => 'interview assistance service',
            'proxy interview' => 'interview assistance',
            'interview proxy' => 'interview assistance',
            'Snowflake Interview Proxy Service' => 'Snowflake Interview Assistance Service',
            'Interview Proxy Service' => 'Interview Assistance Service',
            'Interview Proxy' => 'Interview Assistance',
            'OA completion service' => 'OA assistance service',
            '(OA) completion service' => '(OA) assistance service',
            'OA completion' => 'OA assistance',
            'completion service' => 'assistance service',
            'Citadel OA Proxy' => 'Citadel OA Assistance',
            'OA Proxy' => 'OA Assistance',
            'OA Tutoring' => 'OA Assistance',
            'VO Tutoring' => 'VO Assistance',
            'OA writing' => 'OA assistance',
            'All proxy tutors' => 'All senior mentors',
        );
    }

    if ($lang === 'zh_tw') {
        return array(
            '代面試服務' => '面試輔助服務',
            '代面導師' => '輔助導師',
            '代面現場' => '輔助現場',
            '面試代面' => '面試輔助',
            '系統設計代面試' => '系統設計面試輔助',
            '系統設計代面' => '系統設計面試輔助',
            'VO代面' => 'VO輔助',
            'VO代做' => 'VO輔助',
            '代面試' => '面試輔助',
            '代面' => '面試輔助',
            '代寫OA' => 'OA輔助',
            'OA代寫' => 'OA輔助',
            'OA代做' => 'OA輔助',
            '代寫' => '輔助',
            '代做' => '輔助',
        );
    }

    return array(
        '代面试服务' => '面试辅助服务',
        '代面导师' => '辅助导师',
        '代面现场' => '辅助现场',
        '面试代面' => '面试辅助',
        '系统设计代面试' => '系统设计面试辅助',
        '系统设计代面' => '系统设计面试辅助',
        'VO代面' => 'VO辅助',
        'VO代做' => 'VO辅助',
        '代面试' => '面试辅助',
        '代面' => '面试辅助',
        '代写OA' => 'OA辅助',
        'OA代写' => 'OA辅助',
        'OA代做' => 'OA辅助',
        '代写' => '辅助',
        '代做' => '辅助',
    );
}

function csop_seo_soften_wording($html) {
    if (!is_string($html) || $html === '') return $html;
    $lang = function_exists('csop_hf_current_lang') ? csop_hf_current_lang() : 'zh';
    $map = csop_seo_soften_wording_map($lang);
    if (empty($map)) return $html;
    return strtr($html, $map);
}

function csop_seo_render_sitemap() {
    $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
    $path = trim((string) parse_url($request_uri, PHP_URL_PATH), '/');

    if ($path !== 'sitemap.xml') return;

    status_header(200);
    nocache_headers();
    header('Content-Type: application/xml; charset=UTF-8');
    echo csop_seo_sitemap_xml();
    exit;
}

function csop_seo_robots_txt($output, $public) {
    $sitemap_url = home_url('/sitemap.xml');

    if (strpos($output, $sitemap_url) === false) {
        $output = rtrim($output) . "\nSitemap: " . $sitemap_url . "\n";
    }

    return $output;
}

function csop_seo_sitemap_xml() {
    $urls = csop_seo_sitemap_urls();
    $xml = array('<?xml version="1.0" encoding="UTF-8"?>');
    $xml[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    foreach ($urls as $item) {
        $xml[] = '  <url>';
        $xml[] = '    <loc>' . csop_seo_xml_escape($item['loc']) . '</loc>';
        if (!empty($item['lastmod'])) {
            $xml[] = '    <lastmod>' . csop_seo_xml_escape($item['lastmod']) . '</lastmod>';
        }
        if (!empty($item['changefreq'])) {
            $xml[] = '    <changefreq>' . csop_seo_xml_escape($item['changefreq']) . '</changefreq>';
        }
        if (!empty($item['priority'])) {
            $xml[] = '    <priority>' . csop_seo_xml_escape($item['priority']) . '</priority>';
        }
        $xml[] = '  </url>';
    }

    $xml[] = '</urlset>';
    return implode("\n", $xml);
}

function csop_seo_sitemap_urls() {
    $urls = array();
    $site_lastmod = csop_seo_site_lastmod();

    $static_pages = array(
        array('/', '1.0', 'weekly'),
        array('/products/', '0.9', 'weekly'),
        array('/en/products/', '0.8', 'weekly'),
        array('/zh_tw/products/', '0.8', 'weekly'),
        array('/blog/', '0.8', 'weekly'),
        array('/en/blog/', '0.7', 'weekly'),
        array('/zh_tw/blog/', '0.7', 'weekly'),
        array('/about_us/', '0.7', 'monthly'),
        array('/en/about_us/', '0.6', 'monthly'),
        array('/zh_tw/about_us/', '0.6', 'monthly'),
        array('/price/', '0.7', 'monthly'),
        array('/en/price/', '0.6', 'monthly'),
        array('/zh_tw/price/', '0.6', 'monthly'),
        array('/contact/', '0.7', 'monthly'),
        array('/en/contact/', '0.6', 'monthly'),
        array('/zh_tw/contact/', '0.6', 'monthly'),
        array('/en/', '0.6', 'monthly'),
        array('/zh_tw/', '0.6', 'monthly'),
    );

    foreach ($static_pages as $page) {
        csop_seo_add_sitemap_url($urls, home_url($page[0]), $page[1], $page[2], csop_seo_page_lastmod($page[0], $site_lastmod));
    }

    $products = get_posts(array(
        'post_type' => 'csop_product',
        'post_status' => 'publish',
        'numberposts' => 200,
        'orderby' => 'menu_order title',
        'order' => 'ASC',
    ));

    foreach ($products as $product) {
        $slug = $product->post_name ? $product->post_name : sanitize_title($product->post_title);
        $url = add_query_arg('service', rawurlencode($slug), home_url('/products/'));
        csop_seo_add_sitemap_url($urls, $url, '0.8', 'monthly', get_post_modified_time('c', true, $product));
    }

    $posts = get_posts(array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => 200,
        'orderby' => 'modified',
        'order' => 'DESC',
    ));

    foreach ($posts as $post) {
        foreach (array('zh', 'zh_tw', 'en') as $lang) {
            csop_seo_add_sitemap_url($urls, csop_pretty_article_url($post->ID, $lang), '0.7', 'monthly', get_post_modified_time('c', true, $post));
        }
    }

    return array_values($urls);
}

function csop_seo_add_sitemap_url(&$urls, $loc, $priority, $changefreq, $lastmod = '') {
    $loc = esc_url_raw($loc);
    if (!$loc) return;

    $key = untrailingslashit($loc);
    $urls[$key] = array(
        'loc' => $loc,
        'lastmod' => $lastmod,
        'changefreq' => $changefreq,
        'priority' => $priority,
    );
}

function csop_seo_page_lastmod($path, $fallback) {
    $slug = trim($path, '/');

    if ($slug === '') {
        $front_id = (int) get_option('page_on_front');
        if ($front_id) {
            return get_post_modified_time('c', true, $front_id);
        }
        return $fallback;
    }

    $page = get_page_by_path($slug);
    if ($page) {
        return get_post_modified_time('c', true, $page);
    }

    return $fallback;
}

function csop_seo_site_lastmod() {
    $lastmod = get_lastpostmodified('GMT');
    if (!$lastmod) return gmdate('c');

    return mysql2date('c', $lastmod, false);
}

function csop_seo_xml_escape($value) {
    return htmlspecialchars((string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

function csop_ml_languages() {
    return array(
        'zh' => array('label' => '简体中文', 'hint' => '可选 Markdown 覆盖；留空则使用 WordPress 默认标题、正文和摘要'),
        'zh_tw' => array('label' => '繁體中文', 'hint' => '给繁体中文访客展示'),
        'en' => array('label' => 'English', 'hint' => 'For English visitors'),
    );
}

function csop_ml_meta_key($lang, $field) {
    return '_csop_ml_' . sanitize_key($lang) . '_' . sanitize_key($field);
}

function csop_ml_current_lang() {
    if (function_exists('csop_hf_current_lang')) {
        return csop_hf_current_lang();
    }

    $allowed = array('zh', 'zh_tw', 'en');
    $query_lang = function_exists('get_query_var') ? get_query_var('csop_lang') : '';
    if (is_string($query_lang) && in_array($query_lang, $allowed, true)) {
        return sanitize_key($query_lang);
    }
    $path_lang = csop_pretty_lang_from_request_path();
    if ($path_lang && in_array($path_lang, $allowed, true)) {
        return $path_lang;
    }
    if (isset($_GET['csop_lang']) && in_array($_GET['csop_lang'], $allowed, true)) {
        return sanitize_key(wp_unslash($_GET['csop_lang']));
    }
    if (csop_pretty_is_default_zh_request_path()) {
        return 'zh';
    }
    if (isset($_COOKIE['csop_lang']) && in_array($_COOKIE['csop_lang'], $allowed, true)) {
        return sanitize_key(wp_unslash($_COOKIE['csop_lang']));
    }

    return 'zh';
}

function csop_ml_should_translate_frontend() {
    if (is_admin()) return false;
    if (function_exists('wp_doing_ajax') && wp_doing_ajax()) return false;
    if (defined('REST_REQUEST') && REST_REQUEST) return false;
    if (is_feed() || is_trackback()) return false;
    return true;
}

function csop_ml_get_post_value($post_id, $field, $fallback = '') {
    $lang = csop_ml_current_lang();

    $value = get_post_meta($post_id, csop_ml_meta_key($lang, $field), true);
    if (is_string($value) && trim($value) !== '') {
        return ($field === 'content') ? csop_ml_format_post_content($value) : $value;
    }

    if ($field === 'excerpt') {
        $content = get_post_meta($post_id, csop_ml_meta_key($lang, 'content'), true);
        if (is_string($content) && trim($content) !== '') {
            return wp_trim_words(wp_strip_all_tags(csop_ml_format_post_content($content)), 55, '...');
        }
    }

    return $fallback;
}

function csop_ml_format_post_content($content) {
    $content = trim((string) $content);
    if ($content === '') return '';

    if (preg_match('/<!--\s*wp:|<\/?[a-z][\s\S]*>/i', $content)) {
        return $content;
    }

    return csop_ml_markdown_to_html($content);
}

function csop_ml_markdown_to_html($markdown) {
    $markdown = str_replace(array("\r\n", "\r"), "\n", (string) $markdown);
    $code_blocks = array();
    $markdown = preg_replace_callback('/```([A-Za-z0-9_-]*)\n(.*?)```/s', function ($m) use (&$code_blocks) {
        $key = "\x01CSOPMLCODE" . count($code_blocks) . "\x01";
        $lang = sanitize_html_class($m[1]);
        $class = $lang ? ' class="language-' . esc_attr($lang) . '"' : '';
        $code_blocks[$key] = '<pre><code' . $class . '>' . esc_html(rtrim($m[2])) . '</code></pre>';
        return "\n\n" . $key . "\n\n";
    }, $markdown);

    $blocks = preg_split('/\n{2,}/', trim($markdown));
    $html = array();

    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') continue;
        if (isset($code_blocks[$block])) {
            $html[] = $code_blocks[$block];
            continue;
        }

        $lines = preg_split('/\n/', $block);
        if (preg_match('/^\s{0,3}#{1,6}\s+/u', $block)) {
            $first = trim($lines[0]);
            preg_match('/^(#{1,6})\s+(.*)$/u', $first, $m);
            $level = min(6, strlen($m[1]));
            $html[] = '<h' . $level . '>' . csop_ml_markdown_inline($m[2]) . '</h' . $level . '>';
            continue;
        }

        if (preg_match('/^\s{0,3}[-*_]{3,}\s*$/', $block)) {
            $html[] = '<hr>';
            continue;
        }

        if (preg_match('/^\s{0,3}>\s?/m', $block)) {
            $quote = preg_replace('/^\s{0,3}>\s?/m', '', $block);
            $quote_lines = array_map('csop_ml_markdown_inline', preg_split('/\n/', trim($quote)));
            $html[] = '<blockquote><p>' . implode('<br>', $quote_lines) . '</p></blockquote>';
            continue;
        }

        $unordered = true;
        $ordered = true;
        foreach ($lines as $line) {
            if (!preg_match('/^\s*[-*+]\s+/', $line)) $unordered = false;
            if (!preg_match('/^\s*\d+\.\s+/', $line)) $ordered = false;
        }
        if ($unordered || $ordered) {
            $tag = $ordered ? 'ol' : 'ul';
            $items = array();
            foreach ($lines as $line) {
                $text = preg_replace($ordered ? '/^\s*\d+\.\s+/' : '/^\s*[-*+]\s+/', '', $line);
                $items[] = '<li>' . csop_ml_markdown_inline($text) . '</li>';
            }
            $html[] = '<' . $tag . '>' . implode('', $items) . '</' . $tag . '>';
            continue;
        }

        $paragraph_lines = array_map('csop_ml_markdown_inline', preg_split('/\n/', $block));
        $html[] = '<p>' . implode('<br>', $paragraph_lines) . '</p>';
    }

    return implode("\n", $html);
}

function csop_ml_markdown_inline($text) {
    $text = esc_html((string) $text);
    $text = preg_replace_callback('/!\[([^\]]*)\]\(([^)\\s]+)(?:\s+"([^"]*)")?\)/', function ($m) {
        $title = isset($m[3]) ? ' title="' . esc_attr($m[3]) . '"' : '';
        return '<img src="' . esc_url(html_entity_decode($m[2])) . '" alt="' . esc_attr(html_entity_decode($m[1])) . '"' . $title . '>';
    }, $text);
    $text = preg_replace_callback('/\[([^\]]+)\]\(([^)\\s]+)(?:\s+"([^"]*)")?\)/', function ($m) {
        $title = isset($m[3]) ? ' title="' . esc_attr($m[3]) . '"' : '';
        return '<a href="' . esc_url(html_entity_decode($m[2])) . '"' . $title . '>' . $m[1] . '</a>';
    }, $text);
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/__([^_]+)__/', '<strong>$1</strong>', $text);
    $text = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/', '<em>$1</em>', $text);
    $text = preg_replace('/(?<!_)_([^_]+)_(?!_)/', '<em>$1</em>', $text);
    return $text;
}

function csop_ml_filter_post_title($title, $post_id = 0) {
    if (!csop_ml_should_translate_frontend() || !$post_id || get_post_type($post_id) !== 'post') return $title;
    return csop_ml_get_post_value($post_id, 'title', $title);
}

function csop_ml_filter_post_content($content) {
    if (!csop_ml_should_translate_frontend()) return $content;
    global $post;
    if (!$post || $post->post_type !== 'post') return $content;
    return csop_ml_get_post_value($post->ID, 'content', $content);
}

function csop_ml_filter_post_excerpt($excerpt, $post = null) {
    if (!csop_ml_should_translate_frontend()) return $excerpt;
    $post = get_post($post);
    if (!$post || $post->post_type !== 'post') return $excerpt;
    return csop_ml_get_post_value($post->ID, 'excerpt', $excerpt);
}

function csop_ml_document_title_parts($parts) {
    if (!csop_ml_should_translate_frontend() || !is_singular('post')) return $parts;
    $post_id = get_queried_object_id();
    if (!$post_id) return $parts;

    $parts['title'] = csop_ml_get_post_value($post_id, 'title', isset($parts['title']) ? $parts['title'] : get_the_title($post_id));
    return $parts;
}

function csop_ml_has_translation($post_id, $lang) {
    if ($lang === 'zh') {
        $meta_title = trim((string) get_post_meta($post_id, csop_ml_meta_key($lang, 'title'), true));
        $meta_content = trim((string) get_post_meta($post_id, csop_ml_meta_key($lang, 'content'), true));
        if ($meta_title !== '' || $meta_content !== '') {
            return ($meta_title !== '' || get_post_field('post_title', $post_id) !== '') && ($meta_content !== '' || get_post_field('post_content', $post_id) !== '');
        }
        return get_post_field('post_title', $post_id) !== '' && get_post_field('post_content', $post_id) !== '';
    }

    $title = trim((string) get_post_meta($post_id, csop_ml_meta_key($lang, 'title'), true));
    $content = trim((string) get_post_meta($post_id, csop_ml_meta_key($lang, 'content'), true));
    return $title !== '' && $content !== '';
}

function csop_ml_status_badge($post_id, $lang) {
    $ok = csop_ml_has_translation($post_id, $lang);
    return '<span class="csop-ml-status ' . ($ok ? 'ok' : 'missing') . '">' . ($ok ? '已填写' : '待补') . '</span>';
}

function csop_ml_lang_url($url, $lang) {
    $allowed = array('zh', 'zh_tw', 'en');
    if (!in_array($lang, $allowed, true)) $lang = 'zh';

    $post_id = csop_pretty_article_post_id_from_url($url);
    if ($post_id) {
        return csop_pretty_article_url($post_id, $lang);
    }

    $page_url = csop_pretty_page_lang_url($url, $lang);
    if ($page_url !== '') return $page_url;

    return add_query_arg('csop_lang', $lang, $url);
}

function csop_ml_editor_url($post_id, $lang = 'zh') {
    return add_query_arg(array(
        'page' => 'csop-post-languages',
        'post_id' => intval($post_id),
        'lang' => sanitize_key($lang),
    ), admin_url('admin.php'));
}

function csop_ml_editor_field_value($post, $lang, $field) {
    $value = get_post_meta($post->ID, csop_ml_meta_key($lang, $field), true);
    if (is_string($value) && $value !== '') return $value;

    if ($lang === 'zh') {
        if ($field === 'title') return get_post_field('post_title', $post->ID);
        if ($field === 'excerpt') return get_post_field('post_excerpt', $post->ID);
        if ($field === 'content') return get_post_field('post_content', $post->ID);
    }

    return '';
}

function csop_ml_post_meta_box($post) {
    wp_nonce_field('csop_ml_save_post', 'csop_ml_nonce');
    $langs = csop_ml_languages();
    ?>
    <div class="csop-ml-box">
        <div class="csop-ml-note">
            <strong>使用方式：</strong>三个语言的正文都可直接粘贴 Markdown。简体中文字段不填时，会自动使用上方 WordPress 默认标题、正文、摘要；如果要用 Markdown 管理简体文章，就填下方简体字段。
        </div>
        <div class="csop-ml-grid">
            <?php foreach (array('zh', 'zh_tw', 'en') as $lang): ?>
                <section class="csop-ml-lang">
                    <h3><?php echo esc_html($langs[$lang]['label']); ?></h3>
                    <p><?php echo esc_html($langs[$lang]['hint']); ?></p>
                    <label>
                        <span>标题</span>
                        <input type="text" name="csop_ml[<?php echo esc_attr($lang); ?>][title]" value="<?php echo esc_attr(get_post_meta($post->ID, csop_ml_meta_key($lang, 'title'), true)); ?>">
                    </label>
                    <label>
                        <span>摘要</span>
                        <textarea name="csop_ml[<?php echo esc_attr($lang); ?>][excerpt]"><?php echo esc_textarea(get_post_meta($post->ID, csop_ml_meta_key($lang, 'excerpt'), true)); ?></textarea>
                    </label>
                    <label>
                        <span>正文，支持 Markdown / HTML / 区块源码</span>
                        <textarea class="csop-ml-content" name="csop_ml[<?php echo esc_attr($lang); ?>][content]"><?php echo esc_textarea(get_post_meta($post->ID, csop_ml_meta_key($lang, 'content'), true)); ?></textarea>
                    </label>
                    <?php if ($post->post_status === 'publish'): ?>
                        <a class="button" target="_blank" rel="noopener" href="<?php echo esc_url(csop_ml_lang_url(get_permalink($post), $lang)); ?>">预览<?php echo esc_html($langs[$lang]['label']); ?></a>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function csop_ml_save_post_translations($post_id, $post) {
    if (!isset($_POST['csop_ml_nonce']) || !wp_verify_nonce($_POST['csop_ml_nonce'], 'csop_ml_save_post')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $raw = isset($_POST['csop_ml']) && is_array($_POST['csop_ml']) ? wp_unslash($_POST['csop_ml']) : array();
    foreach (array('zh', 'zh_tw', 'en') as $lang) {
        $row = isset($raw[$lang]) && is_array($raw[$lang]) ? $raw[$lang] : array();
        update_post_meta($post_id, csop_ml_meta_key($lang, 'title'), isset($row['title']) ? sanitize_text_field($row['title']) : '');
        update_post_meta($post_id, csop_ml_meta_key($lang, 'excerpt'), isset($row['excerpt']) ? sanitize_textarea_field($row['excerpt']) : '');
        update_post_meta($post_id, csop_ml_meta_key($lang, 'content'), isset($row['content']) ? wp_kses_post($row['content']) : '');
    }
}

function csop_ml_post_columns($columns) {
    $out = array();
    foreach ($columns as $key => $label) {
        $out[$key] = $label;
        if ($key === 'title') $out['csop_ml_languages'] = '语言版本';
    }
    return $out;
}

function csop_ml_post_column_content($column, $post_id) {
    if ($column !== 'csop_ml_languages') return;
    echo '<div class="csop-ml-column">';
    foreach (csop_ml_languages() as $lang => $meta) {
        echo '<span><strong>' . esc_html($meta['label']) . '</strong>' . csop_ml_status_badge($post_id, $lang) . '</span>';
    }
    echo '</div>';
}

function csop_ml_posts_page() {
    if (!current_user_can('edit_posts')) return;

    $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
    if ($post_id) {
        csop_ml_single_post_editor_page($post_id);
        return;
    }

    $posts = get_posts(array(
        'post_type' => 'post',
        'post_status' => array('publish', 'draft', 'pending', 'future'),
        'numberposts' => 80,
        'orderby' => 'modified',
        'order' => 'DESC',
    ));
    ?>
    <div class="wrap csop-ml-page">
        <h1>文章三语言</h1>
        <p>从这里进入文章编辑页，补充简体中文、繁體中文、English 三个版本。三个语言的正文都支持直接粘贴 Markdown；简体中文字段留空时，前台会沿用文章默认标题和正文。</p>
        <p>
            <a class="button button-primary" href="<?php echo esc_url(admin_url('post-new.php')); ?>">写新文章</a>
            <a class="button" href="<?php echo esc_url(admin_url('edit.php')); ?>">管理全部文章</a>
            <a class="button" target="_blank" rel="noopener" href="<?php echo esc_url(home_url('/blog/')); ?>">前台博客</a>
        </p>
        <table class="widefat striped csop-ml-table">
            <thead>
                <tr>
                    <th>文章</th>
                    <th>语言状态</th>
                    <th>预览</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($posts): foreach ($posts as $post): ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html(get_the_title($post)); ?></strong>
                        <br><span><?php echo esc_html(get_post_status_object($post->post_status)->label); ?> / <?php echo esc_html(get_the_modified_date('Y-m-d H:i', $post)); ?></span>
                    </td>
                    <td>
                        <div class="csop-ml-column">
                            <?php foreach (csop_ml_languages() as $lang => $meta): ?>
                                <span><strong><?php echo esc_html($meta['label']); ?></strong><?php echo csop_ml_status_badge($post->ID, $lang); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td class="csop-ml-preview-links">
                        <?php if ($post->post_status === 'publish'): ?>
                            <a target="_blank" rel="noopener" href="<?php echo esc_url(csop_ml_lang_url(get_permalink($post), 'zh')); ?>">简体</a>
                            <a target="_blank" rel="noopener" href="<?php echo esc_url(csop_ml_lang_url(get_permalink($post), 'zh_tw')); ?>">繁中</a>
                            <a target="_blank" rel="noopener" href="<?php echo esc_url(csop_ml_lang_url(get_permalink($post), 'en')); ?>">English</a>
                        <?php else: ?>
                            <span>发布后可预览</span>
                        <?php endif; ?>
                    </td>
                    <td class="csop-ml-preview-links">
                        <a class="button button-primary" href="<?php echo esc_url(csop_ml_editor_url($post->ID, 'zh')); ?>">编辑简体</a>
                        <a class="button" href="<?php echo esc_url(csop_ml_editor_url($post->ID, 'zh_tw')); ?>">编辑繁中</a>
                        <a class="button" href="<?php echo esc_url(csop_ml_editor_url($post->ID, 'en')); ?>">编辑 English</a>
                        <a class="button" href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>">WP 编辑器</a>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="4">暂无文章，请先点击“写新文章”。</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function csop_ml_single_post_editor_page($post_id) {
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'post' || !current_user_can('edit_post', $post_id)) {
        echo '<div class="wrap"><h1>文章三语言</h1><div class="notice notice-error"><p>文章不存在或无权限编辑。</p></div><p><a class="button" href="' . esc_url(admin_url('admin.php?page=csop-post-languages')) . '">返回文章列表</a></p></div>';
        return;
    }

    $langs = csop_ml_languages();
    $allowed = array_keys($langs);
    $lang = isset($_GET['lang']) ? sanitize_key(wp_unslash($_GET['lang'])) : 'zh';
    if (!in_array($lang, $allowed, true)) $lang = 'zh';

    if (isset($_POST['csop_ml_single_nonce']) && wp_verify_nonce($_POST['csop_ml_single_nonce'], 'csop_ml_save_single_' . $post_id . '_' . $lang)) {
        $row = isset($_POST['csop_ml_single']) && is_array($_POST['csop_ml_single']) ? wp_unslash($_POST['csop_ml_single']) : array();
        $title = isset($row['title']) ? sanitize_text_field($row['title']) : '';
        $excerpt = isset($row['excerpt']) ? sanitize_textarea_field($row['excerpt']) : '';
        $content = isset($row['content']) ? wp_kses_post($row['content']) : '';

        update_post_meta($post_id, csop_ml_meta_key($lang, 'title'), $title);
        update_post_meta($post_id, csop_ml_meta_key($lang, 'excerpt'), $excerpt);
        update_post_meta($post_id, csop_ml_meta_key($lang, 'content'), $content);

        if ($lang === 'zh' && ($title !== '' || $content !== '' || $excerpt !== '')) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $title !== '' ? $title : $post->post_title,
                'post_excerpt' => $excerpt,
                'post_content' => $content !== '' ? $content : $post->post_content,
            ));
            $post = get_post($post_id);
        }

        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($langs[$lang]['label']) . ' 已保存。</p></div>';
    }

    $title = csop_ml_editor_field_value($post, $lang, 'title');
    $excerpt = csop_ml_editor_field_value($post, $lang, 'excerpt');
    $content = csop_ml_editor_field_value($post, $lang, 'content');
    $preview_url = ($post->post_status === 'publish') ? csop_ml_lang_url(get_permalink($post), $lang) : '';
    ?>
    <div class="wrap csop-ml-page">
        <h1>编辑文章语言版本</h1>
        <p>
            <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=csop-post-languages')); ?>">返回文章列表</a>
            <a class="button" href="<?php echo esc_url(get_edit_post_link($post_id)); ?>">打开 WP 编辑器</a>
            <?php if ($preview_url): ?><a class="button" target="_blank" rel="noopener" href="<?php echo esc_url($preview_url); ?>">预览当前语言</a><?php endif; ?>
        </p>
        <div class="csop-ml-editor-shell">
            <div class="csop-ml-editor-tabs">
                <?php foreach ($langs as $code => $meta): ?>
                    <a class="<?php echo $code === $lang ? 'active' : ''; ?>" href="<?php echo esc_url(csop_ml_editor_url($post_id, $code)); ?>">
                        <?php echo esc_html($meta['label']); ?>
                        <?php echo csop_ml_status_badge($post_id, $code); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <form method="post" class="csop-ml-editor-form">
                <?php wp_nonce_field('csop_ml_save_single_' . $post_id . '_' . $lang, 'csop_ml_single_nonce'); ?>
                <div class="csop-ml-editor-head">
                    <div>
                        <p class="csop-dashboard-kicker">Current Language</p>
                        <h2><?php echo esc_html($langs[$lang]['label']); ?></h2>
                        <p><?php echo esc_html($langs[$lang]['hint']); ?>。正文支持 Markdown / HTML / WordPress 区块源码。</p>
                    </div>
                    <?php submit_button('保存当前语言', 'primary', 'submit', false); ?>
                </div>
                <label class="csop-ml-wide-field">
                    <span>标题</span>
                    <input type="text" name="csop_ml_single[title]" value="<?php echo esc_attr($title); ?>">
                </label>
                <label class="csop-ml-wide-field">
                    <span>摘要</span>
                    <textarea name="csop_ml_single[excerpt]"><?php echo esc_textarea($excerpt); ?></textarea>
                </label>
                <label class="csop-ml-wide-field">
                    <span>正文</span>
                    <textarea class="csop-ml-markdown-editor" name="csop_ml_single[content]"><?php echo esc_textarea($content); ?></textarea>
                </label>
                <?php submit_button('保存当前语言'); ?>
            </form>
        </div>
    </div>
    <?php
}

function csop_ai_seo_admin_menu() {
    add_menu_page(
        'AI SEO 管理',
        'AI SEO 管理',
        'manage_options',
        'csop-ai-seo',
        'csop_ai_seo_page',
        'dashicons-search',
        4
    );
}

function csop_ai_seo_option_name() {
    return 'csop_ai_seo_options_v1';
}

function csop_ai_seo_defaults() {
    return array(
        'enabled' => '1',
        'site_name' => 'csvosupport',
        'title_suffix' => 'csvosupport',
        'home_title' => 'csvosupport | 北美 CS 求职面试与 OA 支持',
        'home_description' => 'csvosupport 提供北美 CS 求职、OA、VO、Mock Interview、简历与 BQ 梳理等支持服务。',
        'home_keywords' => 'csvosupport, OA, VO, Mock Interview, CS 求职, 面试辅导',
        'default_og_image' => '',
    );
}

function csop_ai_seo_get_options() {
    $saved = get_option(csop_ai_seo_option_name(), array());
    return array_merge(csop_ai_seo_defaults(), is_array($saved) ? $saved : array());
}

function csop_ai_seo_langs() {
    return csop_ml_languages();
}

function csop_ai_seo_url($args = array()) {
    return add_query_arg(array_merge(array('page' => 'csop-ai-seo'), $args), admin_url('admin.php'));
}

function csop_ai_seo_meta_key($lang, $field) {
    return '_csop_ai_seo_' . sanitize_key($lang) . '_' . sanitize_key($field);
}

function csop_ai_seo_page() {
    if (!current_user_can('manage_options')) return;

    $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
    if ($post_id) {
        csop_ai_seo_content_editor($post_id);
        return;
    }

    csop_ai_seo_dashboard_page();
}

function csop_ai_seo_dashboard_page() {
    $notice = '';
    if (isset($_POST['csop_ai_seo_options_nonce']) && wp_verify_nonce($_POST['csop_ai_seo_options_nonce'], 'csop_ai_seo_save_options')) {
        $raw = isset($_POST['csop_ai_seo_options']) && is_array($_POST['csop_ai_seo_options']) ? wp_unslash($_POST['csop_ai_seo_options']) : array();
        $out = array();
        foreach (csop_ai_seo_defaults() as $key => $default) {
            if ($key === 'enabled') {
                $out[$key] = !empty($raw[$key]) ? '1' : '';
            } elseif ($key === 'default_og_image') {
                $out[$key] = isset($raw[$key]) ? esc_url_raw($raw[$key]) : '';
            } else {
                $out[$key] = isset($raw[$key]) ? sanitize_textarea_field($raw[$key]) : $default;
            }
        }
        update_option(csop_ai_seo_option_name(), $out);
        $notice = 'AI SEO 全站设置已保存。';
    }

    $settings = csop_ai_seo_get_options();
    $items = csop_ai_seo_manageable_items();
    ?>
    <div class="wrap csop-ai-seo-page">
        <h1>AI SEO 管理</h1>
        <?php if ($notice): ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html($notice); ?></p></div><?php endif; ?>
        <div class="csop-ai-seo-layout">
            <form method="post" class="csop-ai-seo-card">
                <h2>全站 SEO 设置</h2>
                <p>这里控制首页默认 SEO，以及没有单独设置时的兜底信息。</p>
                <?php wp_nonce_field('csop_ai_seo_save_options', 'csop_ai_seo_options_nonce'); ?>
                <label class="csop-ai-field check"><input type="checkbox" name="csop_ai_seo_options[enabled]" value="1" <?php checked($settings['enabled'], '1'); ?>> 启用 AI SEO meta 输出</label>
                <?php foreach (array(
                    'site_name' => '站点品牌名',
                    'title_suffix' => '标题后缀',
                    'home_title' => '首页 SEO 标题',
                    'home_description' => '首页 SEO 描述',
                    'home_keywords' => '首页关键词',
                    'default_og_image' => '默认分享图 URL',
                ) as $key => $label): ?>
                    <label class="csop-ai-field">
                        <span><?php echo esc_html($label); ?></span>
                        <?php if (in_array($key, array('home_description', 'home_keywords'), true)): ?>
                            <textarea name="csop_ai_seo_options[<?php echo esc_attr($key); ?>]"><?php echo esc_textarea($settings[$key]); ?></textarea>
                        <?php else: ?>
                            <input type="text" name="csop_ai_seo_options[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($settings[$key]); ?>">
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
                <?php submit_button('保存全站 SEO 设置'); ?>
            </form>
            <div class="csop-ai-seo-card">
                <h2>内容 SEO 管理</h2>
                <p>对文章、页面、服务产品分别设置 SEO 标题、描述和关键词。进入后可使用“AI 智能建议”一键生成。</p>
                <table class="widefat striped csop-ai-table">
                    <thead><tr><th>内容</th><th>类型</th><th>SEO 状态</th><th>操作</th></tr></thead>
                    <tbody>
                    <?php if ($items): foreach ($items as $item): ?>
                        <tr>
                            <td><strong><?php echo esc_html(get_the_title($item)); ?></strong><br><span>ID: <?php echo intval($item->ID); ?></span></td>
                            <td><?php echo esc_html(csop_ai_seo_type_label($item->post_type)); ?></td>
                            <td>
                                <div class="csop-ml-column">
                                    <?php foreach (csop_ai_seo_langs() as $lang => $meta): ?>
                                        <span><strong><?php echo esc_html($meta['label']); ?></strong><?php echo csop_ai_seo_status_badge($item->ID, $lang); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="csop-ml-preview-links">
                                <a class="button button-primary" href="<?php echo esc_url(csop_ai_seo_url(array('post_id' => $item->ID, 'lang' => 'zh'))); ?>">编辑 SEO</a>
                                <a class="button" target="_blank" rel="noopener" href="<?php echo esc_url(get_permalink($item)); ?>">预览</a>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="4">暂无可管理内容。</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}

function csop_ai_seo_manageable_items() {
    return get_posts(array(
        'post_type' => array('post', 'page', 'csop_product'),
        'post_status' => array('publish', 'draft', 'pending', 'future'),
        'numberposts' => 100,
        'orderby' => 'modified',
        'order' => 'DESC',
    ));
}

function csop_ai_seo_type_label($type) {
    $labels = array('post' => '文章', 'page' => '页面', 'csop_product' => '服务产品');
    return isset($labels[$type]) ? $labels[$type] : $type;
}

function csop_ai_seo_has_meta($post_id, $lang) {
    $title = trim((string) get_post_meta($post_id, csop_ai_seo_meta_key($lang, 'title'), true));
    $description = trim((string) get_post_meta($post_id, csop_ai_seo_meta_key($lang, 'description'), true));
    return $title !== '' && $description !== '';
}

function csop_ai_seo_status_badge($post_id, $lang) {
    $ok = csop_ai_seo_has_meta($post_id, $lang);
    return '<span class="csop-ml-status ' . ($ok ? 'ok' : 'missing') . '">' . ($ok ? '已设置' : '待设置') . '</span>';
}

function csop_ai_seo_content_editor($post_id) {
    $post = get_post($post_id);
    if (!$post || !in_array($post->post_type, array('post', 'page', 'csop_product'), true)) {
        echo '<div class="wrap"><h1>AI SEO 管理</h1><div class="notice notice-error"><p>内容不存在或不支持。</p></div></div>';
        return;
    }

    $langs = csop_ai_seo_langs();
    $lang = isset($_GET['lang']) ? sanitize_key(wp_unslash($_GET['lang'])) : 'zh';
    if (!isset($langs[$lang])) $lang = 'zh';

    $suggestion = csop_ai_seo_generate_suggestion($post, $lang);
    $notice = '';

    if (isset($_POST['csop_ai_seo_content_nonce']) && wp_verify_nonce($_POST['csop_ai_seo_content_nonce'], 'csop_ai_seo_save_content_' . $post_id . '_' . $lang)) {
        $raw = isset($_POST['csop_ai_seo_meta']) && is_array($_POST['csop_ai_seo_meta']) ? wp_unslash($_POST['csop_ai_seo_meta']) : array();
        if (!empty($_POST['csop_ai_apply_suggestion'])) {
            $raw = $suggestion;
        }
        update_post_meta($post_id, csop_ai_seo_meta_key($lang, 'title'), isset($raw['title']) ? sanitize_text_field($raw['title']) : '');
        update_post_meta($post_id, csop_ai_seo_meta_key($lang, 'description'), isset($raw['description']) ? sanitize_textarea_field($raw['description']) : '');
        update_post_meta($post_id, csop_ai_seo_meta_key($lang, 'keywords'), isset($raw['keywords']) ? sanitize_text_field($raw['keywords']) : '');
        $notice = $langs[$lang]['label'] . ' SEO 已保存。';
    }

    $title = get_post_meta($post_id, csop_ai_seo_meta_key($lang, 'title'), true);
    $description = get_post_meta($post_id, csop_ai_seo_meta_key($lang, 'description'), true);
    $keywords = get_post_meta($post_id, csop_ai_seo_meta_key($lang, 'keywords'), true);
    if (isset($_GET['ai_suggest'])) {
        $title = $suggestion['title'];
        $description = $suggestion['description'];
        $keywords = $suggestion['keywords'];
    }
    ?>
    <div class="wrap csop-ai-seo-page">
        <h1>编辑内容 SEO</h1>
        <?php if ($notice): ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html($notice); ?></p></div><?php endif; ?>
        <p>
            <a class="button" href="<?php echo esc_url(csop_ai_seo_url()); ?>">返回 AI SEO 列表</a>
            <a class="button" target="_blank" rel="noopener" href="<?php echo esc_url(get_permalink($post)); ?>">前台预览</a>
        </p>
        <div class="csop-ml-editor-shell">
            <div class="csop-ml-editor-tabs">
                <?php foreach ($langs as $code => $meta): ?>
                    <a class="<?php echo $code === $lang ? 'active' : ''; ?>" href="<?php echo esc_url(csop_ai_seo_url(array('post_id' => $post_id, 'lang' => $code))); ?>">
                        <?php echo esc_html($meta['label']); ?>
                        <?php echo csop_ai_seo_status_badge($post_id, $code); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <form method="post" class="csop-ai-seo-card">
                <?php wp_nonce_field('csop_ai_seo_save_content_' . $post_id . '_' . $lang, 'csop_ai_seo_content_nonce'); ?>
                <h2><?php echo esc_html(get_the_title($post)); ?> - <?php echo esc_html($langs[$lang]['label']); ?></h2>
                <p>SEO 标题建议 50-60 字符，描述建议 120-155 字符。可手动编辑，也可以一键应用 AI 智能建议。</p>
                <label class="csop-ai-field"><span>SEO 标题</span><input type="text" name="csop_ai_seo_meta[title]" value="<?php echo esc_attr($title); ?>"></label>
                <label class="csop-ai-field"><span>SEO 描述</span><textarea name="csop_ai_seo_meta[description]"><?php echo esc_textarea($description); ?></textarea></label>
                <label class="csop-ai-field"><span>关键词，用英文逗号分隔</span><input type="text" name="csop_ai_seo_meta[keywords]" value="<?php echo esc_attr($keywords); ?>"></label>
                <div class="csop-ai-suggestion">
                    <h3>AI 智能建议</h3>
                    <p><strong>标题：</strong><?php echo esc_html($suggestion['title']); ?></p>
                    <p><strong>描述：</strong><?php echo esc_html($suggestion['description']); ?></p>
                    <p><strong>关键词：</strong><?php echo esc_html($suggestion['keywords']); ?></p>
                </div>
                <p>
                    <?php submit_button('保存当前 SEO', 'primary', 'submit', false); ?>
                    <button type="submit" class="button" name="csop_ai_apply_suggestion" value="1">一键应用 AI 建议并保存</button>
                    <a class="button" href="<?php echo esc_url(csop_ai_seo_url(array('post_id' => $post_id, 'lang' => $lang, 'ai_suggest' => 1))); ?>">把 AI 建议填入表单</a>
                </p>
            </form>
        </div>
    </div>
    <?php
}

function csop_ai_seo_generate_suggestion($post, $lang) {
    $source = csop_ai_seo_source_content($post, $lang);
    $settings = csop_ai_seo_get_options();
    $base_title = trim($source['title']) !== '' ? $source['title'] : get_the_title($post);
    $suffix = trim($settings['title_suffix']);
    $title = $base_title;
    if ($suffix !== '' && stripos($title, $suffix) === false) $title .= ' | ' . $suffix;
    $title = csop_ai_seo_trim($title, 62);

    $description_source = trim($source['excerpt']) !== '' ? $source['excerpt'] : $source['content'];
    $description = csop_ai_seo_trim(csop_ai_seo_clean_text($description_source), 155);
    if ($description === '') $description = $settings['home_description'];

    return array(
        'title' => $title,
        'description' => $description,
        'keywords' => csop_ai_seo_keywords($source['title'] . ' ' . $source['excerpt'] . ' ' . $source['content']),
    );
}

function csop_ai_seo_source_content($post, $lang) {
    $title = get_post_field('post_title', $post->ID);
    $excerpt = get_post_field('post_excerpt', $post->ID);
    $content = get_post_field('post_content', $post->ID);

    if ($post->post_type === 'post') {
        $ml_title = get_post_meta($post->ID, csop_ml_meta_key($lang, 'title'), true);
        $ml_excerpt = get_post_meta($post->ID, csop_ml_meta_key($lang, 'excerpt'), true);
        $ml_content = get_post_meta($post->ID, csop_ml_meta_key($lang, 'content'), true);
        if ($ml_title !== '') $title = $ml_title;
        if ($ml_excerpt !== '') $excerpt = $ml_excerpt;
        if ($ml_content !== '') $content = $ml_content;
    }

    if ($post->post_type === 'csop_product') {
        $meta = csop_get_product_meta($post->ID);
        $excerpt = $meta['short_desc'];
        $content = $meta['detail_text'] . "\n" . implode("\n", array_map(function ($row) { return isset($row['text']) ? $row['text'] : ''; }, $meta['features']));
    }

    return array(
        'title' => csop_ai_seo_clean_text($title),
        'excerpt' => csop_ai_seo_clean_text($excerpt),
        'content' => csop_ai_seo_clean_text($content),
    );
}

function csop_ai_seo_clean_text($text) {
    $text = strip_shortcodes((string) $text);
    $text = wp_strip_all_tags($text);
    $text = preg_replace('/[#*_>`\[\]\(\)!]+/u', ' ', $text);
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim($text);
}

function csop_ai_seo_trim($text, $limit) {
    $text = trim((string) $text);
    if ($text === '') return '';
    if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $limit) {
        return rtrim(mb_substr($text, 0, $limit - 1, 'UTF-8')) . '…';
    }
    return (strlen($text) > $limit) ? rtrim(substr($text, 0, $limit - 1)) . '…' : $text;
}

function csop_ai_seo_keywords($text) {
    $text = csop_ai_seo_clean_text($text);
    $seed = array('csvosupport', 'OA', 'VO', 'Mock Interview', 'Online Assessment', 'Virtual Onsite', 'CS 求职', '面试辅导');
    $matched = array();
    foreach (array('Databricks', 'Google', 'Meta', 'Amazon', 'Microsoft', 'CodeSignal', 'HackerRank', 'System Design', 'Coding Interview', 'Resume', 'BQ', '算法', '系统设计', '简历', '行为面试', '面经') as $term) {
        if (stripos($text, $term) !== false) $matched[] = $term;
    }
    preg_match_all('/[A-Za-z][A-Za-z0-9+-]{2,}/', $text, $english);
    foreach (array_slice(array_unique($english[0]), 0, 6) as $word) $matched[] = $word;
    preg_match_all('/[\x{4e00}-\x{9fff}]{2,8}/u', $text, $cjk);
    foreach (array_slice(array_unique($cjk[0]), 0, 6) as $word) $matched[] = $word;
    $keywords = array_values(array_unique(array_filter(array_merge($matched, $seed))));
    return implode(', ', array_slice($keywords, 0, 12));
}

function csop_ai_seo_self_canonical() {
    $path = isset($_SERVER['REQUEST_URI']) ? (string) parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH) : '/';
    $base = home_url($path === '' ? '/' : $path);

    // Preserve only query params that produce a distinct, indexable page; drop tracking/utility args.
    $keep = array();
    $query = isset($_SERVER['REQUEST_URI']) ? (string) parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_QUERY) : '';
    if ($query !== '') {
        $args = array();
        parse_str($query, $args);
        foreach (array('service', 'blog_page') as $allowed) {
            if (isset($args[$allowed]) && $args[$allowed] !== '') {
                $keep[$allowed] = sanitize_text_field($args[$allowed]);
            }
        }
    }

    return $keep ? add_query_arg($keep, $base) : $base;
}

function csop_ai_seo_lang_home_url($lang) {
    if ($lang === 'en') return home_url('/en/');
    if ($lang === 'zh_tw') return home_url('/zh_tw/');
    return home_url('/');
}

function csop_ai_seo_current_meta() {
    $settings = csop_ai_seo_get_options();
    $lang = function_exists('csop_ml_current_lang') ? csop_ml_current_lang() : 'zh';
    $title = '';
    $description = '';
    $keywords = '';
    $image = $settings['default_og_image'];
    $canonical = csop_ai_seo_self_canonical();
    $alternates = array();

    $post = null;
    if (isset($_GET['service']) && function_exists('csop_find_product')) {
        $product = csop_find_product(sanitize_title(wp_unslash($_GET['service'])));
        if ($product && !empty($product['id'])) $post = get_post($product['id']);
    }
    if (!$post && is_singular()) $post = get_post(get_queried_object_id());

    $is_lang_home = false;
    if (function_exists('csop_pretty_lang_from_request_path')) {
        $path = isset($_SERVER['REQUEST_URI']) ? trim((string) parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH), '/') : '';
        $is_lang_home = in_array($path, array('en', 'zh_tw'), true);
    }

    if ($post && in_array($post->post_type, array('post', 'page', 'csop_product'), true)) {
        $title = get_post_meta($post->ID, csop_ai_seo_meta_key($lang, 'title'), true);
        $description = get_post_meta($post->ID, csop_ai_seo_meta_key($lang, 'description'), true);
        $keywords = get_post_meta($post->ID, csop_ai_seo_meta_key($lang, 'keywords'), true);
        if ($title === '' || $description === '') {
            $suggestion = csop_ai_seo_generate_suggestion($post, $lang);
            if ($title === '') $title = $suggestion['title'];
            if ($description === '') $description = $suggestion['description'];
            if ($keywords === '') $keywords = $suggestion['keywords'];
        }
        $thumb = get_the_post_thumbnail_url($post, 'large');
        if ($thumb) $image = $thumb;

        if ($post->post_type === 'csop_product') {
            // CPT is non-public (rewrite=false); its canonical is the products page with the service query arg.
            $slug = $post->post_name ? $post->post_name : sanitize_title($post->post_title);
            $products_base = home_url('/products/');
            if ($lang !== 'zh' && function_exists('csop_pretty_page_lang_url')) {
                $lang_products = csop_pretty_page_lang_url($products_base, $lang);
                if ($lang_products !== '') $products_base = $lang_products;
            }
            $canonical = add_query_arg('service', rawurlencode($slug), $products_base);
            foreach (array('zh', 'en', 'zh_tw') as $alt_lang) {
                $alt_base = home_url('/products/');
                if ($alt_lang !== 'zh' && function_exists('csop_pretty_page_lang_url')) {
                    $lp = csop_pretty_page_lang_url($alt_base, $alt_lang);
                    if ($lp !== '') $alt_base = $lp;
                }
                $alternates[$alt_lang] = add_query_arg('service', rawurlencode($slug), $alt_base);
            }
        } else {
            $canonical = csop_ml_lang_url(get_permalink($post), $lang);
            foreach (array('zh', 'en', 'zh_tw') as $alt_lang) {
                $alternates[$alt_lang] = csop_ml_lang_url(get_permalink($post), $alt_lang);
            }
        }
    } elseif ($is_lang_home || is_front_page() || is_home()) {
        $title = $settings['home_title'];
        $description = $settings['home_description'];
        $keywords = $settings['home_keywords'];
        $canonical = csop_ai_seo_lang_home_url($lang);
        foreach (array('zh', 'en', 'zh_tw') as $alt_lang) {
            $alternates[$alt_lang] = csop_ai_seo_lang_home_url($alt_lang);
        }
    }

    return array(
        'title' => $title,
        'description' => $description,
        'keywords' => $keywords,
        'image' => $image,
        'canonical' => $canonical,
        'site_name' => $settings['site_name'],
        'lang' => $lang,
        'alternates' => $alternates,
    );
}

function csop_ai_seo_document_title_parts($parts) {
    $settings = csop_ai_seo_get_options();
    if ($settings['enabled'] !== '1' || is_admin()) return $parts;
    $meta = csop_ai_seo_current_meta();
    if (!empty($meta['title'])) $parts['title'] = $meta['title'];
    return $parts;
}

function csop_ai_seo_dedupe_core_meta() {
    $settings = csop_ai_seo_get_options();
    if ($settings['enabled'] !== '1' || is_admin()) return;
    // We emit our own canonical below; drop WordPress core's so pages don't ship two canonical tags.
    remove_action('wp_head', 'rel_canonical');
}

function csop_ai_seo_og_locale($lang) {
    $map = array('zh' => 'zh_CN', 'zh_tw' => 'zh_TW', 'en' => 'en_US');
    return isset($map[$lang]) ? $map[$lang] : 'zh_CN';
}

function csop_ai_seo_hreflang_code($lang) {
    $map = array('zh' => 'zh-Hans', 'zh_tw' => 'zh-Hant', 'en' => 'en');
    return isset($map[$lang]) ? $map[$lang] : $lang;
}

function csop_ai_seo_output_meta() {
    $settings = csop_ai_seo_get_options();
    if ($settings['enabled'] !== '1' || is_admin()) return;
    $meta = csop_ai_seo_current_meta();
    if (empty($meta['title']) && empty($meta['description'])) return;

    $lang = !empty($meta['lang']) ? $meta['lang'] : 'zh';
    $canonical = !empty($meta['canonical']) ? $meta['canonical'] : home_url('/');

    echo "\n" . '<!-- csvosupport AI SEO -->' . "\n";
    if (!empty($meta['description'])) echo '<meta name="description" content="' . esc_attr($meta['description']) . '">' . "\n";
    if (!empty($meta['keywords'])) echo '<meta name="keywords" content="' . esc_attr($meta['keywords']) . '">' . "\n";
    echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";

    if (!empty($meta['alternates']) && is_array($meta['alternates'])) {
        foreach ($meta['alternates'] as $alt_lang => $alt_url) {
            if (!$alt_url) continue;
            echo '<link rel="alternate" hreflang="' . esc_attr(csop_ai_seo_hreflang_code($alt_lang)) . '" href="' . esc_url($alt_url) . '">' . "\n";
        }
        if (!empty($meta['alternates']['zh'])) {
            echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($meta['alternates']['zh']) . '">' . "\n";
        }
    }

    if (!empty($meta['title'])) echo '<meta property="og:title" content="' . esc_attr($meta['title']) . '">' . "\n";
    if (!empty($meta['description'])) echo '<meta property="og:description" content="' . esc_attr($meta['description']) . '">' . "\n";
    echo '<meta property="og:type" content="' . (is_singular('post') ? 'article' : 'website') . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
    echo '<meta property="og:locale" content="' . esc_attr(csop_ai_seo_og_locale($lang)) . '">' . "\n";
    if (!empty($meta['site_name'])) echo '<meta property="og:site_name" content="' . esc_attr($meta['site_name']) . '">' . "\n";
    if (!empty($meta['image'])) echo '<meta property="og:image" content="' . esc_url($meta['image']) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    if (!empty($meta['title'])) echo '<meta name="twitter:title" content="' . esc_attr($meta['title']) . '">' . "\n";
    if (!empty($meta['description'])) echo '<meta name="twitter:description" content="' . esc_attr($meta['description']) . '">' . "\n";
    if (!empty($meta['image'])) echo '<meta name="twitter:image" content="' . esc_url($meta['image']) . '">' . "\n";
}

function csop_content_legacy_brand() {
    return 'CSOffer' . 'Prep';
}

function csop_replace_content_brand($value) {
    if (is_string($value)) {
        return str_replace(csop_content_legacy_brand(), 'csvosupport', $value);
    }
    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $value[$key] = csop_replace_content_brand($item);
        }
    }
    return $value;
}

function csop_apply_csvosupport_content_brand() {
    if (get_option('csop_content_brand_csvosupport_v1')) return;

    $option_names = array(csop_option_name());
    if (function_exists('csop_hf_option_name')) {
        $option_names[] = csop_hf_option_name();
    }

    foreach ($option_names as $option_name) {
        $saved = get_option($option_name, null);
        if ($saved !== null) {
            update_option($option_name, csop_replace_content_brand($saved));
        }
    }

    update_option('csop_content_brand_csvosupport_v1', 1);
}

function csop_apply_oavo_contacts() {
    if (get_option('csop_product_oavo_contacts_v1')) return;
    $saved = get_option(csop_option_name(), array());
    if (!is_array($saved)) $saved = array();
    $saved['email'] = 'catcstech@gmail.com';
    $saved['whatsapp'] = '+86 17863968105';
    $saved['wechat'] = 'Coding0201';
    update_option(csop_option_name(), array_merge(csop_defaults(), $saved));
    update_option('csop_product_oavo_contacts_v1', 1);
}

function csop_external_media_url_map() {
    return array(
        'https://main.mevia.site/demo/csofferprep/wp-content/plugins/translatepress-multilingual/assets/flags/4x3/zh_CN.svg' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-01-main-mevia-site-zh-cn.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/plugins/translatepress-multilingual/assets/flags/4x3/en_US.svg' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-02-main-mevia-site-en-us.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/plugins/translatepress-multilingual/assets/flags/4x3/zh_TW.svg' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-03-main-mevia-site-zh-tw.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/Microsoft-2026-SDE-Online-Assessment_cleanup-1.webp' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-04-main-mevia-site-microsoft-2026-sde-online-assessment-cleanup-1.webp',
        'https://csofferprep.com/wp-content/uploads/2026/06/6840541.webp' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-05-csofferprep-com-6840541.webp',
        'https://csofferprep.com/wp-content/uploads/2026/06/csofferprep_databricks_oa_2026-06-25.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-06-csofferprep-com-csofferprep-databricks-oa-2026-06-25.png',
        'https://csofferprep.com/wp-content/uploads/2026/06/csofferprep_microsoft_vo_20260623.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-07-csofferprep-com-csofferprep-microsoft-vo-20260623.png',
        'https://csofferprep.com/wp-content/uploads/2026/06/google-vo-two-rounds-latest.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-08-csofferprep-com-google-vo-two-rounds-latest.png',
        'https://csofferprep.com/wp-content/uploads/2026/05/2026-Google-NG-Interview-Review.webp' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-09-csofferprep-com-2026-google-ng-interview-review.webp',
        'https://csofferprep.com/wp-content/uploads/2026/06/csofferprep_meta_oa.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-10-csofferprep-com-csofferprep-meta-oa.png',
        'https://csofferprep.com/wp-content/uploads/2026/06/amazon-oa.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-11-csofferprep-com-amazon-oa.jpg',
        'https://csofferprep.com/wp-content/uploads/2026/06/snowflake-swe-vo-csofferprep.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-12-csofferprep-com-snowflake-swe-vo-csofferprep.png',
        'https://ui-avatars.com/api/?name=Cat&background=1b78e2&color=fff&size=80&font-size=0.4&bold=true' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-13-ui-avatars-com-image.png',
        'https://ui-avatars.com/api/?name=Sarah+Wang&background=2f4468&color=fff&size=80&font-size=0.4&bold=true' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-14-ui-avatars-com-image.png',
        'https://ui-avatars.com/api/?name=Michael+Li&background=1b78e2&color=fff&size=80&font-size=0.4&bold=true' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-15-ui-avatars-com-image.png',
        'https://ui-avatars.com/api/?name=Emily+Zhou&background=2f4468&color=fff&size=80&font-size=0.4&bold=true' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-16-ui-avatars-com-image.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2025/08/WechatIMG7956-16x12.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-004-wechatimg7956-16x12-1.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2025/08/WechatIMG7954-11x12.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-001-wechatimg7954-11x12-1.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2025/08/WechatIMG7954.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-003-wechatimg7954.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2025/08/WechatIMG7956-300x220.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-005-wechatimg7956-300x220-1.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2025/08/WechatIMG7954-285x300.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-002-wechatimg7954-285x300-1.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/05/2026-Google-NG-Interview-Review.webp' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-008-2026-google-ng-interview-review.webp',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2025/08/WechatIMG7956.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-006-wechatimg7956.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2025/10/WechatIMG9866.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-007-wechatimg9866.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/05/image-17.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-010-image-17.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/4685.webp' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-012-4685.webp',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/05/How-to-Pass-Coinbase-OA.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-009-how-to-pass-coinbase-oa.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/6840541-12x12.webp' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-013-6840541-12x12-1.webp',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/6840541-300x300.webp' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-015-6840541-300x300-1.webp',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/6840541-150x150.webp' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-014-6840541-150x150-1.webp',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/6840541.webp' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-016-6840541.webp',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-oa-15x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-018-amazon-oa-15x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-oa-300x240.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-019-amazon-oa-300x240-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-oa-1024x819.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-017-amazon-oa-1024x819-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-oa-768x614.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-020-amazon-oa-768x614-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-oa2-16x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-023-amazon-oa2-16x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-oa.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-021-amazon-oa.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/05/image-18.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-011-image-18.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-oa2-1024x764.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-022-amazon-oa2-1024x764-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-oa2-300x224.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-024-amazon-oa2-300x224-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-oa2-768x573.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-025-amazon-oa2-768x573-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-oa3-225x300.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-027-amazon-oa3-225x300-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-oa3-768x1024.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-028-amazon-oa3-768x1024-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-oa2.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-026-amazon-oa2.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-oa3.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-030-amazon-oa3.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-offer-768x986.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-032-amazon-offer-768x986-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-oa3-9x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-029-amazon-oa3-9x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-offer-9x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-034-amazon-offer-9x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-sde-oa-1024x659.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-036-amazon-sde-oa-1024x659-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-offer-798x1024.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-033-amazon-offer-798x1024-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-offer.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-035-amazon-offer.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-sde-oa-18x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-038-amazon-sde-oa-18x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-offer-234x300.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-031-amazon-offer-234x300-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-sde-oa-1536x989.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-037-amazon-sde-oa-1536x989-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-sde-oa-768x494.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-040-amazon-sde-oa-768x494-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-sde-oa-300x193.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-039-amazon-sde-oa-300x193-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-sde2-1024x619.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-042-amazon-sde2-1024x619-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-sde2-18x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-044-amazon-sde2-18x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-sde2-768x464.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-046-amazon-sde2-768x464-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-sde2-300x181.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-045-amazon-sde2-300x181-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/google-offer-13x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-049-google-offer-13x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/google-offer-1024x953.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-048-google-offer-1024x953-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/google-offer-300x279.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-050-google-offer-300x279-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-sde2.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-047-amazon-sde2.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/google-offer-768x715.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-051-google-offer-768x715-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-sde-oa.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-041-amazon-sde-oa.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/amazon-sde2-1536x928.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-043-amazon-sde2-1536x928-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/intuit-oa-13x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-054-intuit-oa-13x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/google-offer.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-052-google-offer.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/intuit-oa-768x698.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-056-intuit-oa-768x698-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/intuit-oa-1024x930.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-053-intuit-oa-1024x930-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/intuit-oa-300x273.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-055-intuit-oa-300x273-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/microsoft-offer-139x300.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-058-microsoft-offer-139x300-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/microsoft-offer-473x1024.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-059-microsoft-offer-473x1024-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/intuit-oa.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-057-intuit-oa.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/microsoft-offer-6x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-060-microsoft-offer-6x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/microsoft-offer-710x1536.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-061-microsoft-offer-710x1536-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/microsoft-offer-768x1662.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-062-microsoft-offer-768x1662-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/openai-offer-11x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-065-openai-offer-11x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/openai-offer-281x300.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-066-openai-offer-281x300-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/microsoft-offer-946x2048.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-063-microsoft-offer-946x2048-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/openai-offer-958x1024.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-068-openai-offer-958x1024-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/openai-offer.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-069-openai-offer.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/microsoft-offer.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-064-microsoft-offer.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/openai-offer-768x821.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-067-openai-offer-768x821-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review1-134x300.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-070-review1-134x300-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review1-5x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-072-review1-5x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review1-456x1024.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-071-review1-456x1024-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review1-684x1536.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-073-review1-684x1536-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review1-768x1726.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-074-review1-768x1726-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review2-186x300.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-077-review2-186x300-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review1-912x2048.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-075-review1-912x2048-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review2-7x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-080-review2-7x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review2-768x1236.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-079-review2-768x1236-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review2-636x1024.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-078-review2-636x1024-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review1.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-076-review1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review3-10x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-082-review3-10x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review2.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-081-review2.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review3-243x300.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-083-review3-243x300-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review3-768x947.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-084-review3-768x947-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review3-830x1024.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-085-review3-830x1024-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review4-223x300.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-087-review4-223x300-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review4-762x1024.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-088-review4-762x1024-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review3.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-086-review3.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review4-9x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-090-review4-9x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review4-768x1032.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-089-review4-768x1032-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review5-11x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-092-review5-11x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review5-273x300.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-093-review5-273x300-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review4.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-091-review4.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review5-768x844.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-094-review5-768x844-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review5.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-095-review5.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review6-133x300.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-096-review6-133x300-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review6-5x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-098-review6-5x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review6-679x1536.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-099-review6-679x1536-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review6-768x1737.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-100-review6-768x1737-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review6-453x1024.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-097-review6-453x1024-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review7-151x300.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-103-review7-151x300-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review7-6x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-105-review7-6x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review7-517x1024.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-104-review7-517x1024-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review6-905x2048.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-101-review6-905x2048-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review7-768x1522.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-106-review7-768x1522-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review6.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-102-review6.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review8-132x300.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-109-review8-132x300-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review8-450x1024.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-110-review8-450x1024-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review7-775x1536.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-107-review7-775x1536-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review7.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-108-review7.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review8-5x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-111-review8-5x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review8-675x1536.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-112-review8-675x1536-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review8-768x1747.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-113-review8-768x1747-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/stripe-oa1-229x300.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-116-stripe-oa1-229x300-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review8-901x2048.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-114-review8-901x2048-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/stripe-oa1-768x1005.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-117-stripe-oa1-768x1005-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/review8.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-115-review8.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/stripe-oa1-783x1024.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-118-stripe-oa1-783x1024-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/stripe-oa1-9x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-119-stripe-oa1-9x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/tiktok-oa-1024x760.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-121-tiktok-oa-1024x760-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/tiktok-oa-300x223.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-123-tiktok-oa-300x223-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/tiktok-oa-16x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-122-tiktok-oa-16x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/tiktok-oa-768x570.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-124-tiktok-oa-768x570-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/stripe-oa1.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-120-stripe-oa1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/tiktok-offer-7x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-128-tiktok-offer-7x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/tiktok-offer-637x1024.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-127-tiktok-offer-637x1024-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/tiktok-offer.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-129-tiktok-offer.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/two-sigma-oa-14x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-131-two-sigma-oa-14x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/tiktok-oa.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-125-tiktok-oa.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/two-sigma-oa-300x259.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-132-two-sigma-oa-300x259-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/two-sigma-oa-1024x883.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-130-two-sigma-oa-1024x883-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo1-165x300.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-135-vo1-165x300-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo1-563x1024.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-136-vo1-563x1024-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo1-7x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-137-vo1-7x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/two-sigma-oa-768x662.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-133-two-sigma-oa-768x662-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/two-sigma-oa.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-134-two-sigma-oa.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo1.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-138-vo1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo2-18x5.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-140-vo2-18x5-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo2-1024x273.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-139-vo2-1024x273-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo2-300x80.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-141-vo2-300x80-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/tiktok-offer-187x300.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-126-tiktok-offer-187x300-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo2-768x205.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-142-vo2-768x205-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo3-14x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-144-vo3-14x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo3-300x255.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-145-vo3-300x255-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo2.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-143-vo2.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo3.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-146-vo3.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo4-186x300.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-147-vo4-186x300-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo4-7x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-149-vo4-7x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo4-636x1024.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-148-vo4-636x1024-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo5-1024x375.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-151-vo5-1024x375-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo4.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-150-vo4.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo5-1536x562.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-152-vo5-1536x562-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo5-18x7.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-153-vo5-18x7-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo5-300x110.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-155-vo5-300x110-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo5-768x281.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-156-vo5-768x281-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo5-2048x750.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-154-vo5-2048x750-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo6-15x12.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-158-vo6-15x12-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo6-300x245.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-159-vo6-300x245-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo6-1024x838.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-157-vo6-1024x838-1.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo6.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-161-vo6.jpg',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2026/06/vo6-768x628.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-160-vo6-768x628-1.jpg',
        'https://csofferprep.com/wp-content/uploads/2026/05/How-to-Pass-Coinbase-OA.jpg' => 'https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-001-how-to-pass-coinbase-oa.jpg',
        'https://csofferprep.com/wp-content/uploads/2026/06/csofferprep_apple_vo_2026-06-24.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-002-csofferprep_apple_vo_2026-06-24.png',
        'https://csofferprep.com/wp-content/uploads/2026/06/google_csofferprep_vo.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-003-google_csofferprep_vo.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2025/08/cropped-logo-12x12.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-004-cropped-logo-12x12-1.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2025/08/cropped-logo-150x150.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-005-cropped-logo-150x150-1.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2025/08/cropped-logo-180x180.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-006-cropped-logo-180x180-1.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2025/08/cropped-logo-192x192.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-007-cropped-logo-192x192-1.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2025/08/cropped-logo-270x270.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-008-cropped-logo-270x270-1.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2025/08/cropped-logo-300x300.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-009-cropped-logo-300x300-1.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2025/08/cropped-logo-32x32.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-010-cropped-logo-32x32-1.png',
        'https://main.mevia.site/demo/csofferprep/wp-content/uploads/2025/08/cropped-logo.png' => 'https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-011-cropped-logo.png',
    );
}

function csop_replace_external_media_urls($value) {
    if (is_string($value)) {
        foreach (csop_external_media_url_map() as $old => $new) {
            $value = str_replace(array($old, esc_url($old), esc_attr($old)), $new, $value);
        }
        return $value;
    }
    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $value[$key] = csop_replace_external_media_urls($item);
        }
    }
    return $value;
}

function csop_apply_local_external_media_urls() {
    if (get_option('csop_external_media_localized_v3')) return;

    $option_names = array(csop_option_name());
    if (function_exists('csop_hf_option_name')) $option_names[] = csop_hf_option_name();
    if (function_exists('csop_home_option_name')) $option_names[] = csop_home_option_name();
    if (function_exists('csop_pages_option_name')) $option_names[] = csop_pages_option_name();

    foreach (array_unique($option_names) as $option_name) {
        $saved = get_option($option_name, null);
        if ($saved !== null) {
            update_option($option_name, csop_replace_external_media_urls($saved));
        }
    }

    $posts = get_posts(array(
        'post_type' => array('post', 'page', 'csop_product'),
        'post_status' => 'any',
        'numberposts' => -1,
        'fields' => 'ids',
        'suppress_filters' => true,
    ));

    foreach ($posts as $post_id) {
        $post = get_post($post_id);
        if ($post) {
            $new_content = csop_replace_external_media_urls($post->post_content);
            $new_excerpt = csop_replace_external_media_urls($post->post_excerpt);
            if ($new_content !== $post->post_content || $new_excerpt !== $post->post_excerpt) {
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_content' => $new_content,
                    'post_excerpt' => $new_excerpt,
                ));
            }
        }

        $product_meta = get_post_meta($post_id, '_csop_product_meta', true);
        if ($product_meta !== '') {
            $new_meta = csop_replace_external_media_urls($product_meta);
            if ($new_meta !== $product_meta) {
                update_post_meta($post_id, '_csop_product_meta', $new_meta);
            }
        }
    }

    update_option('csop_external_media_localized_v3', 1);
}

function csop_defaults() {
    return array(
        'hero_kicker' => 'Service Catalog',
        'hero_title' => 'csvosupport 服务产品中心',
        'hero_text' => '把老站的服务内容拆成可管理的产品卡片，保留 csvosupport 简洁、可信、转化导向的展示方式。用户不在线支付，先提交需求，再由顾问人工沟通。',
        'hero_bg' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-05-csofferprep-com-6840541.webp',
        'list_kicker' => 'Interview Support',
        'list_title' => '选择需要咨询的服务方向',
        'list_text' => 'OA、VO、Mock Interview、简历与 BQ 梳理都可以作为独立产品维护。每个产品都支持图片、参数、卖点、详情和询盘记录。',
        'detail_back' => '返回产品中心',
        'inquiry_title' => '提交服务需求',
        'email' => 'catcstech@gmail.com',
        'whatsapp' => '+86 17863968105',
        'wechat' => 'Coding0201',
    );
}

function csop_get_options() {
    $saved = get_option(csop_option_name(), array());
    if (!is_array($saved)) $saved = array();
    return array_merge(csop_defaults(), $saved);
}

function csop_sanitize_options($raw) {
    $raw = is_array($raw) ? $raw : array();
    $defaults = csop_defaults();
    $clean = array();
    foreach ($defaults as $key => $value) {
        if ($key === 'hero_bg') {
            $clean[$key] = isset($raw[$key]) ? esc_url_raw(wp_unslash($raw[$key])) : $value;
        } else {
            $clean[$key] = isset($raw[$key]) ? sanitize_textarea_field(wp_unslash($raw[$key])) : $value;
        }
    }
    return $clean;
}

function csop_register_product_system() {
    register_post_type('csop_product', array(
        'labels' => array(
            'name' => '服务产品',
            'singular_name' => '服务产品',
            'all_items' => '管理服务产品',
            'add_new' => '添加服务产品',
            'add_new_item' => '添加服务产品',
            'edit_item' => '编辑服务产品',
            'menu_name' => '服务产品',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'cc-site-visual',
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'page-attributes'),
        'has_archive' => false,
        'rewrite' => false,
    ));

    register_taxonomy('csop_product_cat', 'csop_product', array(
        'labels' => array(
            'name' => '产品分类',
            'singular_name' => '产品分类',
            'menu_name' => '产品分类',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'show_admin_column' => true,
    ));

    register_post_type('csop_inquiry', array(
        'labels' => array(
            'name' => '服务询盘',
            'singular_name' => '服务询盘',
            'all_items' => '所有服务询盘',
            'edit_item' => '查看询盘',
            'menu_name' => '服务询盘',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'cc-site-visual',
        'supports' => array('title'),
    ));

    if (get_option(csop_option_name()) === false) {
        add_option(csop_option_name(), csop_defaults());
    }

    csop_seed_default_products();
}

function csop_admin_menu() {
    add_submenu_page(
        'cc-site-visual',
        '产品页设置',
        '产品页设置',
        'manage_options',
        'csop-product-settings',
        'csop_settings_page'
    );

    add_submenu_page(
        'cc-site-visual',
        '文章三语言',
        '文章三语言',
        'edit_posts',
        'csop-post-languages',
        'csop_ml_posts_page'
    );
}
add_action('admin_menu', 'csop_admin_menu', 20);

function csop_product_meta_defaults() {
    return array(
        'subtitle' => '',
        'badge' => '',
        'image' => '',
        'gallery' => '',
        'duration' => '',
        'response_time' => '',
        'delivery_mode' => '',
        'suitable_for' => '',
        'price_note' => 'Manual quote',
        'short_desc' => '',
        'detail_text' => '',
        'featured' => '',
        'specs' => array(
            array('label' => '沟通方式', 'value' => '人工咨询'),
            array('label' => '交付形式', 'value' => '按需求定制'),
            array('label' => '响应时间', 'value' => '尽快回复'),
            array('label' => '报价方式', 'value' => '提交需求后确认'),
        ),
        'features' => array(
            array('text' => '根据目标岗位和阶段整理服务范围。'),
            array('text' => '先收集需求，再由顾问人工确认方案。'),
        ),
    );
}

function csop_merge_meta($defaults, $saved) {
    $saved = is_array($saved) ? $saved : array();
    foreach ($defaults as $key => $value) {
        if (!array_key_exists($key, $saved)) {
            $saved[$key] = $value;
        }
    }
    return $saved;
}

function csop_get_product_meta($post_id) {
    return csop_merge_meta(csop_product_meta_defaults(), get_post_meta($post_id, '_csop_product_meta', true));
}

function csop_lines($value) {
    $lines = is_array($value) ? $value : preg_split('/\r\n|\r|\n/', (string) $value);
    $lines = array_map('trim', $lines);
    return array_values(array_filter($lines, function($line) { return $line !== ''; }));
}

function csop_seed_default_products() {
    if (get_option('csop_products_seeded_v1')) return;

    $count = wp_count_posts('csop_product');
    if ($count && !empty($count->publish)) {
        update_option('csop_products_seeded_v1', 1);
        return;
    }

    $categories = array('Online Assessment', 'Virtual Onsite', 'Interview Coaching', 'Career Package');
    foreach ($categories as $cat) {
        if (!term_exists($cat, 'csop_product_cat')) {
            wp_insert_term($cat, 'csop_product_cat');
        }
    }

    foreach (csop_sample_products() as $item) {
        $post_id = wp_insert_post(array(
            'post_type' => 'csop_product',
            'post_status' => 'publish',
            'post_title' => $item['title'],
            'post_name' => $item['slug'],
            'post_content' => $item['detail_text'],
            'menu_order' => !empty($item['featured']) ? 0 : 10,
        ));
        if (!$post_id || is_wp_error($post_id)) continue;

        $term = term_exists($item['cat_label'], 'csop_product_cat');
        if (!$term) $term = wp_insert_term($item['cat_label'], 'csop_product_cat');
        if ($term && !is_wp_error($term)) {
            $term_id = is_array($term) ? intval($term['term_id']) : intval($term);
            wp_set_object_terms($post_id, array($term_id), 'csop_product_cat');
        }

        update_post_meta($post_id, '_csop_product_meta', array(
            'subtitle' => $item['subtitle'],
            'badge' => $item['badge'],
            'image' => $item['image'],
            'gallery' => implode("\n", $item['gallery']),
            'duration' => $item['duration'],
            'response_time' => $item['response_time'],
            'delivery_mode' => $item['delivery_mode'],
            'suitable_for' => $item['suitable_for'],
            'price_note' => $item['price_note'],
            'short_desc' => $item['short_desc'],
            'detail_text' => $item['detail_text'],
            'featured' => !empty($item['featured']) ? '1' : '',
            'specs' => $item['specs'],
            'features' => $item['features'],
        ));
    }

    update_option('csop_products_seeded_v1', 1);
}

function csop_sample_products() {
    return array(
        array(
            'slug' => 'online-assessment-support',
            'title' => 'OA Online Assessment 支持',
            'subtitle' => '面向 CodeSignal / HackerRank / OA 流程',
            'badge' => 'OA',
            'cat_label' => 'Online Assessment',
            'image' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-06-csofferprep-com-csofferprep-databricks-oa-2026-06-25.png',
            'gallery' => array('https://csvosupport.com/wp-content/uploads/2026/06/local-ext-06-csofferprep-com-csofferprep-databricks-oa-2026-06-25.png', 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-10-csofferprep-com-csofferprep-meta-oa.png', 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-11-csofferprep-com-amazon-oa.jpg'),
            'duration' => '按题目/场次评估',
            'response_time' => '需求确认后安排',
            'delivery_mode' => '远程沟通',
            'suitable_for' => 'New Grad / Intern / SWE',
            'price_note' => '提交需求后报价',
            'short_desc' => '用于展示 OA 相关咨询、题型梳理、时间安排和需求收集，不走在线支付。',
            'detail_text' => "适合正在准备 Online Assessment 的候选人，先填写目标公司、考试平台、时间窗口和题型信息。\n页面会把需求直接入库到后台询盘，方便顾问按情况人工回复。",
            'specs' => array(
                array('label' => '适用阶段', 'value' => 'OA / Coding Test'),
                array('label' => '平台', 'value' => 'CodeSignal / HackerRank'),
                array('label' => '沟通方式', 'value' => '远程确认'),
                array('label' => '报价', 'value' => 'Manual quote'),
            ),
            'features' => array(
                array('text' => '先收集公司、岗位、平台、考试时间等关键信息。'),
                array('text' => '后台可查看完整询盘和来源页面。'),
                array('text' => '适合做成转化入口，不需要购物车。'),
            ),
            'featured' => '1',
        ),
        array(
            'slug' => 'virtual-onsite-interview-support',
            'title' => 'VO Virtual Onsite 面试支持',
            'subtitle' => 'Coding / System Design / BQ 综合准备',
            'badge' => 'VO',
            'cat_label' => 'Virtual Onsite',
            'image' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-07-csofferprep-com-csofferprep-microsoft-vo-20260623.png',
            'gallery' => array('https://csvosupport.com/wp-content/uploads/2026/06/local-ext-07-csofferprep-com-csofferprep-microsoft-vo-20260623.png', 'https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-003-google_csofferprep_vo.png', 'https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-002-csofferprep_apple_vo_2026-06-24.png'),
            'duration' => '按轮次评估',
            'response_time' => '尽快回复',
            'delivery_mode' => '远程咨询',
            'suitable_for' => 'SWE / MLE / Data',
            'price_note' => '按轮次确认',
            'short_desc' => '为多轮 VO 面试准备展示服务范围，适配 Coding、系统设计和行为面试需求。',
            'detail_text' => "VO 详情页用于展示候选人可以提交的目标公司、轮次、面试时间、题型和薄弱环节。\n系统会把表单记录为服务询盘，便于后续人工跟进。",
            'specs' => array(
                array('label' => '轮次', 'value' => 'Coding / SD / BQ'),
                array('label' => '对象', 'value' => 'SWE / Data / MLE'),
                array('label' => '响应', 'value' => '人工确认'),
                array('label' => '交付', 'value' => '按需求定制'),
            ),
            'features' => array(
                array('text' => '支持按公司和轮次拆解服务展示。'),
                array('text' => '详情页包含参数、卖点、图片和询盘表单。'),
                array('text' => '后台可持续新增不同公司/岗位服务包。'),
            ),
            'featured' => '1',
        ),
        array(
            'slug' => 'mock-interview-coaching',
            'title' => 'Mock Interview 面试辅导',
            'subtitle' => '算法、系统设计、BQ 模拟练习',
            'badge' => 'Coaching',
            'cat_label' => 'Interview Coaching',
            'image' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-08-csofferprep-com-google-vo-two-rounds-latest.png',
            'gallery' => array('https://csvosupport.com/wp-content/uploads/2026/06/local-ext-08-csofferprep-com-google-vo-two-rounds-latest.png', 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-12-csofferprep-com-snowflake-swe-vo-csofferprep.png'),
            'duration' => '60-90 min',
            'response_time' => '预约确认',
            'delivery_mode' => 'Zoom / Google Meet',
            'suitable_for' => '面试前冲刺',
            'price_note' => '按小时/套餐',
            'short_desc' => '适合在面试前进行模拟、反馈和薄弱点整理。',
            'detail_text' => "Mock Interview 产品用于承接准备面试的用户，展示模拟面试、讲解反馈和复盘服务。\n用户提交目标岗位和时间后，后台生成询盘记录。",
            'specs' => array(
                array('label' => '时长', 'value' => '60-90 min'),
                array('label' => '形式', 'value' => 'Remote'),
                array('label' => '内容', 'value' => 'Coding / SD / BQ'),
                array('label' => '反馈', 'value' => '复盘建议'),
            ),
            'features' => array(
                array('text' => '适合展示模拟面试服务和预约入口。'),
                array('text' => '用户可提交目标公司、时间和希望训练的方向。'),
            ),
            'featured' => '1',
        ),
        array(
            'slug' => 'resume-bq-package',
            'title' => '简历包装与 BQ 梳理',
            'subtitle' => '项目经历、STAR 故事线、岗位匹配',
            'badge' => 'Resume / BQ',
            'cat_label' => 'Career Package',
            'image' => 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-09-csofferprep-com-2026-google-ng-interview-review.webp',
            'gallery' => array('https://csvosupport.com/wp-content/uploads/2026/06/local-ext-09-csofferprep-com-2026-google-ng-interview-review.webp', 'https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-001-how-to-pass-coinbase-oa.jpg'),
            'duration' => '按材料评估',
            'response_time' => '1 business day',
            'delivery_mode' => '文档 + 语音沟通',
            'suitable_for' => '投递/面试前',
            'price_note' => '按材料复杂度',
            'short_desc' => '用于承接简历、项目经历和行为面试故事线梳理需求。',
            'detail_text' => "该服务卡片适合展示简历、项目亮点、BQ 故事线和岗位匹配相关需求。\n后台产品字段可以继续补充更多参数和说明。",
            'specs' => array(
                array('label' => '材料', 'value' => 'Resume / BQ'),
                array('label' => '方式', 'value' => '人工梳理'),
                array('label' => '目标', 'value' => '岗位匹配'),
                array('label' => '输出', 'value' => '按需求确认'),
            ),
            'features' => array(
                array('text' => '适合和首页/联系页形成转化闭环。'),
                array('text' => '询盘会记录用户岗位、国家、联系方式和详细描述。'),
            ),
            'featured' => '',
        ),
    );
}

function csop_register_meta_boxes() {
    add_meta_box('csop_product_details', '服务产品资料', 'csop_product_meta_box', 'csop_product', 'normal', 'high');
    add_meta_box('csop_inquiry_details', '询盘详情', 'csop_inquiry_meta_box', 'csop_inquiry', 'normal', 'high');
    add_meta_box('csop_post_languages', '文章三语言版本', 'csop_ml_post_meta_box', 'post', 'normal', 'high');
}

function csop_product_meta_box($post) {
    wp_nonce_field('csop_product_meta_save', 'csop_product_meta_nonce');
    $m = csop_get_product_meta($post->ID);
    ?>
    <div class="csop-admin-panel">
        <div class="csop-admin-grid">
            <?php csop_admin_field('subtitle', '副标题', $m['subtitle']); ?>
            <?php csop_admin_field('badge', '卡片标签', $m['badge']); ?>
            <?php csop_admin_field('duration', '服务周期/时长', $m['duration']); ?>
            <?php csop_admin_field('response_time', '响应时间', $m['response_time']); ?>
            <?php csop_admin_field('delivery_mode', '交付/沟通方式', $m['delivery_mode']); ?>
            <?php csop_admin_field('suitable_for', '适合人群', $m['suitable_for']); ?>
            <?php csop_admin_field('price_note', '报价说明', $m['price_note']); ?>
            <label class="csop-check"><input type="checkbox" name="csop_meta[featured]" value="1" <?php checked($m['featured'], '1'); ?>> 推荐产品</label>
            <?php csop_admin_image('image', '主图 URL', $m['image']); ?>
            <?php csop_admin_textarea('gallery', '多图 URL，每行一张', $m['gallery']); ?>
            <?php csop_admin_textarea('short_desc', '短描述', $m['short_desc']); ?>
            <?php csop_admin_textarea('detail_text', '详情说明', $m['detail_text']); ?>
        </div>
        <h3>参数</h3>
        <div class="csop-repeat" data-repeat="specs">
            <?php foreach ($m['specs'] as $i => $row): ?>
                <div class="csop-repeat-row">
                    <input type="text" name="csop_meta[specs][<?php echo intval($i); ?>][label]" value="<?php echo esc_attr($row['label']); ?>" placeholder="参数名">
                    <input type="text" name="csop_meta[specs][<?php echo intval($i); ?>][value]" value="<?php echo esc_attr($row['value']); ?>" placeholder="参数值">
                    <button type="button" class="button csop-remove-row">删除</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button button-primary csop-add-spec">添加参数</button>
        <h3>卖点</h3>
        <div class="csop-repeat" data-repeat="features">
            <?php foreach ($m['features'] as $i => $row): ?>
                <div class="csop-repeat-row one">
                    <input type="text" name="csop_meta[features][<?php echo intval($i); ?>][text]" value="<?php echo esc_attr($row['text']); ?>" placeholder="卖点文字">
                    <button type="button" class="button csop-remove-row">删除</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button button-primary csop-add-feature">添加卖点</button>
    </div>
    <?php
}

function csop_admin_field($key, $label, $value) {
    echo '<label class="csop-field"><span>' . esc_html($label) . '</span><input type="text" name="csop_meta[' . esc_attr($key) . ']" value="' . esc_attr($value) . '"></label>';
}

function csop_admin_textarea($key, $label, $value) {
    echo '<label class="csop-field full"><span>' . esc_html($label) . '</span><textarea name="csop_meta[' . esc_attr($key) . ']">' . esc_textarea($value) . '</textarea></label>';
}

function csop_admin_image($key, $label, $value) {
    echo '<label class="csop-field full"><span>' . esc_html($label) . '</span><div class="csop-image-row"><input type="url" name="csop_meta[' . esc_attr($key) . ']" value="' . esc_attr($value) . '"><button type="button" class="button csop-pick-image">选择图片</button></div></label>';
}

function csop_save_product_meta($post_id, $post) {
    if (!isset($_POST['csop_product_meta_nonce']) || !wp_verify_nonce($_POST['csop_product_meta_nonce'], 'csop_product_meta_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $raw = isset($_POST['csop_meta']) && is_array($_POST['csop_meta']) ? wp_unslash($_POST['csop_meta']) : array();
    $defaults = csop_product_meta_defaults();
    $out = array();
    foreach ($defaults as $key => $value) {
        if ($key === 'specs' || $key === 'features') continue;
        if ($key === 'featured') {
            $out[$key] = !empty($raw[$key]) ? '1' : '';
        } elseif ($key === 'image') {
            $out[$key] = isset($raw[$key]) ? esc_url_raw($raw[$key]) : '';
        } else {
            $out[$key] = isset($raw[$key]) ? sanitize_textarea_field($raw[$key]) : (is_string($value) ? $value : '');
        }
    }

    $out['specs'] = array();
    if (!empty($raw['specs']) && is_array($raw['specs'])) {
        foreach ($raw['specs'] as $row) {
            $label = isset($row['label']) ? sanitize_text_field($row['label']) : '';
            $value = isset($row['value']) ? sanitize_text_field($row['value']) : '';
            if ($label !== '' || $value !== '') $out['specs'][] = array('label' => $label, 'value' => $value);
        }
    }
    $out['features'] = array();
    if (!empty($raw['features']) && is_array($raw['features'])) {
        foreach ($raw['features'] as $row) {
            $text = isset($row['text']) ? sanitize_text_field($row['text']) : '';
            if ($text !== '') $out['features'][] = array('text' => $text);
        }
    }
    update_post_meta($post_id, '_csop_product_meta', $out);
}

function csop_inquiry_meta_box($post) {
    $fields = array('name', 'country', 'contact', 'email', 'product', 'service_stage', 'target_company', 'deadline', 'message', 'landing_page', 'referrer');
    echo '<div class="csop-inquiry-view">';
    foreach ($fields as $field) {
        $value = get_post_meta($post->ID, '_' . $field, true);
        echo '<p><strong>' . esc_html($field) . ':</strong><br>' . nl2br(esc_html($value)) . '</p>';
    }
    echo '</div>';
}

function csop_inquiry_admin_columns($columns) {
    $date = isset($columns['date']) ? $columns['date'] : '日期';
    return array(
        'cb' => isset($columns['cb']) ? $columns['cb'] : '<input type="checkbox" />',
        'title' => '询盘标题',
        'product' => '产品',
        'name' => '客户',
        'contact' => '联系方式',
        'date' => $date,
    );
}

function csop_inquiry_admin_column_content($column, $post_id) {
    if ($column === 'product') {
        echo esc_html(get_post_meta($post_id, '_product', true));
    } elseif ($column === 'name') {
        echo esc_html(get_post_meta($post_id, '_name', true));
    } elseif ($column === 'contact') {
        $contact = get_post_meta($post_id, '_contact', true);
        $email = get_post_meta($post_id, '_email', true);
        echo esc_html($contact);
        if ($email) echo '<br><a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
    }
}

function csop_dashboard_admin_css() {
    return <<<'CSS'
.csop-dashboard-wrap{--csop-admin-blue:#2f4468;--csop-admin-accent:#1b78e2;--csop-admin-border:#dcdcde;--csop-admin-muted:#667085}.csop-dashboard-hero{display:flex;justify-content:space-between;gap:24px;align-items:flex-end;background:linear-gradient(135deg,#22262e 0%,#2f4468 58%,#1b78e2 100%);color:#fff;border-radius:12px;padding:28px 30px;margin:18px 0 22px;box-shadow:0 16px 40px rgba(15,23,42,.18)}.csop-dashboard-hero h1{color:#fff;font-size:32px;margin:4px 0 10px}.csop-dashboard-hero p{max-width:760px;color:rgba(255,255,255,.82);font-size:15px;line-height:1.7;margin:0}.csop-dashboard-kicker{margin:0!important;color:#9ec7ff!important;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.csop-dashboard-hero-actions{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}.csop-dashboard-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}.csop-dashboard-card,.csop-dashboard-panel{background:#fff;border:1px solid var(--csop-admin-border);border-radius:12px;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.06)}.csop-dashboard-card{display:flex;min-height:236px;flex-direction:column}.csop-dashboard-card h2,.csop-dashboard-panel h2{margin:8px 0 10px;color:var(--csop-admin-blue)}.csop-dashboard-card p,.csop-dashboard-panel p{color:var(--csop-admin-muted);line-height:1.65}.csop-dashboard-card code{display:inline-block;width:max-content;max-width:100%;white-space:normal;margin-top:auto;background:#f6f8fb;border:1px solid #e8ecf3;border-radius:6px;padding:6px 8px}.csop-dashboard-badge{align-self:flex-start;background:#eef6ff;color:#155fb4;border:1px solid #cbe2ff;border-radius:999px;padding:4px 9px;font-size:12px;font-weight:700}.csop-dashboard-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:16px}.csop-dashboard-lower{display:grid;grid-template-columns:1.1fr .9fr;gap:16px;margin-top:16px}.csop-dashboard-quick{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}.csop-dashboard-quick-item{display:flex;align-items:center;gap:10px;border:1px solid #e1e6ef;background:#f8fafc;border-radius:10px;padding:13px 14px;text-decoration:none}.csop-dashboard-quick-item .dashicons{color:var(--csop-admin-accent)}.csop-dashboard-quick-item strong{color:#1d2327}@media(max-width:1400px){.csop-dashboard-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.csop-dashboard-quick{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:782px){.csop-dashboard-hero{display:block}.csop-dashboard-hero-actions{justify-content:flex-start;margin-top:16px}.csop-dashboard-grid,.csop-dashboard-lower,.csop-dashboard-quick{grid-template-columns:1fr}}
CSS;
}

function csop_admin_assets($hook) {
    global $post_type;
    $is_dashboard = ($hook === 'toplevel_page_cc-site-visual');
    $is_ml_page = strpos((string) $hook, 'csop-post-languages') !== false;
    $is_ai_seo_page = strpos((string) $hook, 'csop-ai-seo') !== false;
    if (!$is_dashboard && !$is_ml_page && !$is_ai_seo_page && $post_type !== 'post' && $post_type !== 'csop_product' && $post_type !== 'csop_inquiry' && strpos((string) $hook, 'csop-product-settings') === false) return;
    wp_register_style('csop-admin-style', false, array(), '1.0.0');
    wp_enqueue_style('csop-admin-style');
    wp_add_inline_style('csop-admin-style', '.csop-admin-panel{font-family:Arial,sans-serif}.csop-admin-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.csop-field{display:block}.csop-field.full,.csop-check{grid-column:1/-1}.csop-field span{display:block;font-weight:800;margin-bottom:6px}.csop-field input,.csop-field textarea{width:100%;border:1px solid #ccd0d4;border-radius:8px;padding:9px}.csop-field textarea{min-height:110px}.csop-image-row{display:grid;grid-template-columns:1fr auto;gap:8px}.csop-repeat{display:grid;gap:8px;margin:12px 0}.csop-repeat-row{display:grid;grid-template-columns:1fr 1fr auto;gap:8px}.csop-repeat-row.one{grid-template-columns:1fr auto}.csop-repeat-row input{width:100%;border:1px solid #ccd0d4;border-radius:8px;padding:9px}.csop-settings-layout{display:grid;grid-template-columns:minmax(360px,520px) 1fr;gap:20px}.csop-admin-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px;margin-bottom:14px}.csop-preview-panel{position:sticky;top:40px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:14px;max-height:calc(100vh - 80px);overflow:auto}.csop-preview-canvas{width:1180px;transform:scale(.58);transform-origin:top left}.csop-inquiry-view p{padding:10px 12px;background:#f6f7f7;border-radius:8px}.csop-ml-box{font-family:Arial,sans-serif}.csop-ml-note{background:#f0f6fc;border-left:4px solid #1b78e2;padding:12px 14px;margin:0 0 14px}.csop-ml-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.csop-ml-lang{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:14px}.csop-ml-lang h3{margin:0 0 6px;color:#2f4468}.csop-ml-lang p{margin:0 0 12px;color:#667085}.csop-ml-lang label{display:block;margin:0 0 12px}.csop-ml-lang label span{display:block;font-weight:700;margin-bottom:5px}.csop-ml-lang input,.csop-ml-lang textarea{width:100%;border:1px solid #ccd0d4;border-radius:8px;padding:9px}.csop-ml-lang textarea{min-height:96px}.csop-ml-lang textarea.csop-ml-content{min-height:230px;font-family:Consolas,Monaco,monospace}.csop-ml-base input,.csop-ml-base textarea{background:#f6f7f7;color:#50575e}.csop-ml-column{display:flex;gap:8px;flex-wrap:wrap}.csop-ml-column span{display:inline-flex;align-items:center;gap:5px}.csop-ml-status{border-radius:999px;padding:2px 7px;font-size:12px;font-weight:700}.csop-ml-status.ok{background:#e7f7ed;color:#0a7a32}.csop-ml-status.missing{background:#fff2cc;color:#8a5b00}.csop-ml-page .csop-ml-table td{vertical-align:middle}.csop-ml-preview-links{display:flex;gap:8px;flex-wrap:wrap}.csop-ml-preview-links a{display:inline-flex;background:#f6f8fb;border:1px solid #dfe5ee;border-radius:999px;padding:4px 9px;text-decoration:none}.csop-ml-editor-shell{display:grid;grid-template-columns:220px minmax(0,1fr);gap:18px;margin-top:16px}.csop-ml-editor-tabs{display:flex;flex-direction:column;gap:10px}.csop-ml-editor-tabs a{display:flex;justify-content:space-between;align-items:center;gap:10px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:12px 14px;text-decoration:none;color:#1d2327}.csop-ml-editor-tabs a.active{border-color:#1b78e2;box-shadow:0 0 0 1px #1b78e2;background:#f0f6fc}.csop-ml-editor-form{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:18px}.csop-ml-editor-head{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;border-bottom:1px solid #eef0f2;margin-bottom:16px;padding-bottom:14px}.csop-ml-editor-head h2{margin:0 0 8px;color:#2f4468}.csop-ml-wide-field{display:block;margin-bottom:15px}.csop-ml-wide-field span{display:block;font-weight:800;margin-bottom:6px}.csop-ml-wide-field input,.csop-ml-wide-field textarea{width:100%;border:1px solid #ccd0d4;border-radius:8px;padding:10px}.csop-ml-wide-field textarea{min-height:110px}.csop-ml-wide-field textarea.csop-ml-markdown-editor{min-height:460px;font-family:Consolas,Monaco,monospace;line-height:1.55}.csop-ai-seo-layout{display:grid;grid-template-columns:minmax(320px,430px) minmax(0,1fr);gap:18px}.csop-ai-seo-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:18px}.csop-ai-seo-card h2{margin-top:0;color:#2f4468}.csop-ai-field{display:block;margin:0 0 14px}.csop-ai-field.check{font-weight:800}.csop-ai-field span{display:block;font-weight:800;margin-bottom:6px}.csop-ai-field input,.csop-ai-field textarea{width:100%;border:1px solid #ccd0d4;border-radius:8px;padding:10px}.csop-ai-field textarea{min-height:94px}.csop-ai-table td{vertical-align:middle}.csop-ai-suggestion{background:#f8fbff;border:1px solid #d8e7fb;border-radius:8px;padding:14px;margin:16px 0}.csop-ai-suggestion h3{margin-top:0;color:#155fb4}' . csop_dashboard_admin_css() . '@media(max-width:1100px){.csop-admin-grid,.csop-settings-layout,.csop-ml-grid,.csop-ml-editor-shell,.csop-ai-seo-layout{grid-template-columns:1fr}.csop-preview-canvas{transform:scale(.78)}}');
    if ($is_dashboard) return;
    wp_enqueue_media();
    wp_enqueue_script('jquery');
    $csop_admin_js = <<<'JS'
jQuery(function($){
  $('#post').attr('novalidate','novalidate').prop('noValidate', true);

  function renumber(box, type) {
    box.find('.csop-repeat-row').each(function(i){
      $(this).find('input').each(function(){
        var name = $(this).attr('name') || '';
        name = name.replace(new RegExp(type + '\\]\\[\\d+\\]'), type + '][' + i + ']');
        $(this).attr('name', name);
      });
    });
  }

  $('.csop-add-spec').on('click', function(){
    var box = $('[data-repeat="specs"]'), i = box.find('.csop-repeat-row').length;
    box.append('<div class="csop-repeat-row"><input type="text" name="csop_meta[specs][' + i + '][label]" placeholder="参数名"><input type="text" name="csop_meta[specs][' + i + '][value]" placeholder="参数值"><button type="button" class="button csop-remove-row">删除</button></div>');
  });

  $('.csop-add-feature').on('click', function(){
    var box = $('[data-repeat="features"]'), i = box.find('.csop-repeat-row').length;
    box.append('<div class="csop-repeat-row one"><input type="text" name="csop_meta[features][' + i + '][text]" placeholder="卖点文字"><button type="button" class="button csop-remove-row">删除</button></div>');
  });

  $(document).on('click', '.csop-remove-row', function(){
    var box = $(this).closest('.csop-repeat'), type = box.data('repeat');
    $(this).closest('.csop-repeat-row').remove();
    renumber(box, type);
  });

  $(document).on('click', '.csop-pick-image', function(e){
    e.preventDefault();
    var input = $(this).closest('.csop-image-row').find('input');
    var frame = wp.media({ title: '选择图片', button: { text: '使用图片' }, multiple: false });
    frame.on('select', function(){ input.val(frame.state().get('selection').first().toJSON().url); });
    frame.open();
  });
});
JS;
    wp_add_inline_script('jquery', $csop_admin_js);
}

function csop_settings_page() {
    if (!current_user_can('manage_options')) return;
    if (isset($_POST['csop_settings_nonce']) && wp_verify_nonce($_POST['csop_settings_nonce'], 'csop_save_settings')) {
        $raw = isset($_POST['csop_options']) && is_array($_POST['csop_options']) ? $_POST['csop_options'] : array();
        update_option(csop_option_name(), csop_sanitize_options($raw));
        echo '<div class="notice notice-success is-dismissible"><p>设置已保存。</p></div>';
    }
    $s = csop_get_options();
    ?>
    <div class="wrap">
        <h1>产品页设置</h1>
        <p>前台短代码：<code>[csop_products]</code></p>
        <form method="post" novalidate>
            <?php wp_nonce_field('csop_save_settings', 'csop_settings_nonce'); ?>
            <div class="csop-settings-layout">
                <div>
                    <div class="csop-admin-card">
                        <h2>Banner 与列表文案</h2>
                        <?php foreach (array('hero_kicker' => 'Banner 小标题', 'hero_title' => 'Banner 主标题', 'hero_text' => 'Banner 说明', 'hero_bg' => 'Banner 背景图 URL', 'list_kicker' => '列表小标题', 'list_title' => '列表标题', 'list_text' => '列表说明') as $key => $label): ?>
                            <label class="csop-field full"><span><?php echo esc_html($label); ?></span><textarea name="csop_options[<?php echo esc_attr($key); ?>]"><?php echo esc_textarea($s[$key]); ?></textarea></label>
                        <?php endforeach; ?>
                    </div>
                    <div class="csop-admin-card">
                        <h2>联系信息</h2>
                        <?php foreach (array('email' => '邮箱', 'whatsapp' => 'WhatsApp', 'wechat' => '微信') as $key => $label): ?>
                            <label class="csop-field full"><span><?php echo esc_html($label); ?></span><input type="text" name="csop_options[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($s[$key]); ?>"></label>
                        <?php endforeach; ?>
                    </div>
                    <?php submit_button('保存设置'); ?>
                </div>
                <div class="csop-preview-panel">
                    <h2>右侧预览</h2>
                    <div class="csop-preview-canvas"><?php echo csop_render_list($s, csop_get_products(3), true); ?></div>
                </div>
            </div>
        </form>
    </div>
    <?php
}

function csop_get_products($limit = 0, $featured = false) {
    $args = array(
        'post_type' => 'csop_product',
        'post_status' => 'publish',
        'posts_per_page' => $limit ? $limit : -1,
        'orderby' => array('menu_order' => 'ASC', 'date' => 'DESC'),
    );
    $query = new WP_Query($args);
    $items = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $m = csop_get_product_meta($post_id);
            if ($featured && empty($m['featured'])) continue;
            $terms = get_the_terms($post_id, 'csop_product_cat');
            $cat_slug = $terms && !is_wp_error($terms) ? $terms[0]->slug : 'service';
            $cat_label = $terms && !is_wp_error($terms) ? $terms[0]->name : 'Service';
            $image = $m['image'];
            if (!$image && has_post_thumbnail($post_id)) $image = get_the_post_thumbnail_url($post_id, 'large');
            if (!$image) $image = 'https://csvosupport.com/wp-content/uploads/2026/06/local-ext-05-csofferprep-com-6840541.webp';
            $gallery = csop_lines($m['gallery']);
            if (!$gallery) $gallery = array($image);
            $items[] = array(
                'id' => $post_id,
                'slug' => get_post_field('post_name', $post_id),
                'title' => get_the_title(),
                'subtitle' => $m['subtitle'],
                'badge' => $m['badge'] ? $m['badge'] : $cat_label,
                'cat' => $cat_slug,
                'cat_label' => $cat_label,
                'image' => $image,
                'gallery' => $gallery,
                'duration' => $m['duration'],
                'response_time' => $m['response_time'],
                'delivery_mode' => $m['delivery_mode'],
                'suitable_for' => $m['suitable_for'],
                'price_note' => $m['price_note'],
                'short_desc' => $m['short_desc'] ? $m['short_desc'] : wp_trim_words(get_the_content(null, false, $post_id), 26),
                'detail_text' => $m['detail_text'] ? $m['detail_text'] : get_the_content(null, false, $post_id),
                'specs' => $m['specs'],
                'features' => $m['features'],
                'featured' => $m['featured'],
            );
        }
        wp_reset_postdata();
    }
    if ($featured && !$items) $items = csop_get_products($limit, false);
    if ($limit) $items = array_slice($items, 0, $limit);
    return $items;
}

function csop_find_product($slug) {
    foreach (csop_get_products() as $product) {
        if ($product['slug'] === $slug) return $product;
    }
    return null;
}

function csop_product_detail_link($slug) {
    $base = get_permalink();
    if (!$base) $base = home_url('/products/');
    return esc_url(add_query_arg('service', rawurlencode($slug), $base));
}

function csop_products_shortcode() {
    $settings = csop_get_options();
    $slug = isset($_GET['service']) ? sanitize_title(wp_unslash($_GET['service'])) : '';
    ob_start();
    echo '<main class="csop-products-page">';
    if ($slug) {
        $product = csop_find_product($slug);
        echo $product ? csop_render_detail($settings, $product) : csop_render_list($settings, csop_get_products());
    } else {
        echo csop_render_list($settings, csop_get_products());
    }
    echo '</main>';
    echo csop_products_front_js();
    return ob_get_clean();
}

function csop_products_front_js() {
    static $done = false;
    if ($done) return '';
    $done = true;
    return <<<'JS'
<script id="csop-products-reveal-js">
(function(){
  function initReveal(){
    var root = document.querySelector('.csop-products-page');
    if (!root) return;
    var targets = root.querySelectorAll('.csop-hero .csop-container, .csop-head, .csop-filter, .csop-card, .csop-quote-band, .csop-main-image, .csop-panel, .csop-contact-panel, .csop-inquiry-form');
    if (!targets.length) return;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var observer = !reduce && 'IntersectionObserver' in window ? new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if (!entry.isIntersecting) return;
        entry.target.classList.add('csop-in');
        observer.unobserve(entry.target);
      });
    }, {threshold: 0.12, rootMargin: '0px 0px -8% 0px'}) : null;
    targets.forEach(function(el, index){
      el.classList.add('csop-reveal', 'csop-reveal-delay-' + (index % 4));
      if (reduce || !observer) el.classList.add('csop-in'); else observer.observe(el);
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initReveal); else initReveal();
})();
</script>
JS;
}

function csop_render_card($p) {
    ob_start();
    ?>
    <article class="csop-card" data-category="<?php echo esc_attr($p['cat']); ?>">
        <a class="csop-card-image" href="<?php echo csop_product_detail_link($p['slug']); ?>" style="background-image:url('<?php echo esc_url($p['image']); ?>')">
            <span><?php echo esc_html($p['badge']); ?></span>
        </a>
        <div class="csop-card-body">
            <p class="csop-card-kicker"><?php echo esc_html($p['subtitle']); ?></p>
            <h3><?php echo esc_html($p['title']); ?></h3>
            <p><?php echo esc_html($p['short_desc']); ?></p>
            <div class="csop-card-meta">
                <div><strong><?php echo esc_html($p['duration']); ?></strong><span>周期</span></div>
                <div><strong><?php echo esc_html($p['price_note']); ?></strong><span>报价</span></div>
            </div>
            <div class="csop-actions">
                <a class="csop-btn primary" href="<?php echo csop_product_detail_link($p['slug']); ?>">查看方案</a>
                <a class="csop-btn" href="<?php echo csop_product_detail_link($p['slug']); ?>#csopInquiry">提交询盘</a>
            </div>
        </div>
    </article>
    <?php
    return ob_get_clean();
}

function csop_render_list($s, $products, $preview = false) {
    $cats = array();
    foreach ($products as $p) $cats[$p['cat']] = $p['cat_label'];
    ob_start();
    ?>
    <section class="csop-hero" style="background-image:linear-gradient(90deg,rgba(20,34,45,.90),rgba(47,68,104,.72)),url('<?php echo esc_url($s['hero_bg']); ?>')">
        <div class="csop-container">
            <div class="csop-kicker"><?php echo esc_html($s['hero_kicker']); ?></div>
            <h1><?php echo esc_html($s['hero_title']); ?></h1>
            <p><?php echo esc_html($s['hero_text']); ?></p>
            <div class="csop-hero-stats">
                <div><strong><?php echo count($products); ?>+</strong><span>服务产品</span></div>
                <div><strong>Inquiry</strong><span>不走购物车</span></div>
                <div><strong>Admin</strong><span>后台可编辑</span></div>
            </div>
        </div>
    </section>
    <section class="csop-section" id="csopProductList">
        <div class="csop-container">
            <div class="csop-head">
                <div>
                    <div class="csop-kicker"><?php echo esc_html($s['list_kicker']); ?></div>
                    <h2><?php echo esc_html($s['list_title']); ?></h2>
                    <p><?php echo esc_html($s['list_text']); ?></p>
                </div>
            </div>
            <div class="csop-filter" data-csop-filter>
                <button type="button" class="active" data-filter="all">全部服务</button>
                <?php foreach ($cats as $slug => $label): ?>
                    <button type="button" data-filter="<?php echo esc_attr($slug); ?>"><?php echo esc_html($label); ?></button>
                <?php endforeach; ?>
            </div>
            <div class="csop-grid" data-csop-grid>
                <?php foreach ($products as $p) echo csop_render_card($p); ?>
            </div>
        </div>
    </section>
    <section class="csop-section soft">
        <div class="csop-container">
            <div class="csop-quote-band">
                <div>
                    <h2>需要先确认适合哪种服务？</h2>
                    <p>提交目标公司、岗位、面试阶段和时间窗口，后台会保存为询盘，方便顾问跟进。</p>
                </div>
                <a class="csop-btn primary" href="#csopProductList">浏览服务</a>
            </div>
        </div>
    </section>
    <?php if (!$preview): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
      document.querySelectorAll('.csop-products-page [data-csop-filter]').forEach(function(bar){
        var grid = bar.parentElement.querySelector('[data-csop-grid]');
        bar.querySelectorAll('[data-filter]').forEach(function(btn){
          btn.addEventListener('click', function(){
            bar.querySelectorAll('[data-filter]').forEach(function(item){ item.classList.remove('active'); });
            btn.classList.add('active');
            var filter = btn.dataset.filter;
            grid.querySelectorAll('[data-category]').forEach(function(card){
              card.hidden = !(filter === 'all' || card.dataset.category === filter);
            });
          });
        });
      });
    });
    </script>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

function csop_render_detail($s, $p) {
    $related = array_filter(csop_get_products(6), function($item) use ($p) { return $item['slug'] !== $p['slug']; });
    ob_start();
    ?>
    <section class="csop-hero detail" style="background-image:linear-gradient(90deg,rgba(20,34,45,.92),rgba(47,68,104,.76)),url('<?php echo esc_url($p['image']); ?>')">
        <div class="csop-container">
            <a class="csop-back" href="<?php echo esc_url(remove_query_arg('service')); ?>"><?php echo esc_html($s['detail_back']); ?></a>
            <div class="csop-kicker"><?php echo esc_html($p['badge']); ?></div>
            <h1><?php echo esc_html($p['title']); ?></h1>
            <p><?php echo esc_html($p['short_desc']); ?></p>
        </div>
    </section>
    <section class="csop-section">
        <div class="csop-container csop-detail-grid">
            <div>
                <div class="csop-main-image"><img src="<?php echo esc_url($p['image']); ?>" alt="<?php echo esc_attr($p['title']); ?>"></div>
                <div class="csop-gallery">
                    <?php foreach (array_slice($p['gallery'], 0, 3) as $img): ?>
                        <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($p['title']); ?>">
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="csop-panel">
                <div class="csop-kicker">Service Detail</div>
                <h2><?php echo esc_html($p['subtitle'] ? $p['subtitle'] : $p['title']); ?></h2>
                <?php foreach (csop_lines($p['detail_text']) as $line): ?><p><?php echo esc_html($line); ?></p><?php endforeach; ?>
                <div class="csop-specs">
                    <?php foreach (array_slice($p['specs'], 0, 6) as $row): ?>
                        <div><strong><?php echo esc_html($row['value']); ?></strong><span><?php echo esc_html($row['label']); ?></span></div>
                    <?php endforeach; ?>
                </div>
                <?php foreach ($p['features'] as $feature): ?>
                    <div class="csop-check"><b>✓</b><span><?php echo esc_html($feature['text']); ?></span></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <section class="csop-section soft" id="csopInquiry">
        <div class="csop-container csop-form-grid">
            <div class="csop-contact-panel">
                <h2><?php echo esc_html($s['inquiry_title']); ?></h2>
                <p>请留下服务方向、目标公司、时间窗口和联系方式。表单会进入后台“服务询盘”。</p>
                <div class="csop-contact-line"><strong>产品</strong><span><?php echo esc_html($p['title']); ?></span></div>
                <div class="csop-contact-line"><strong>Email</strong><span><?php echo esc_html($s['email']); ?></span></div>
                <div class="csop-contact-line"><strong>WeChat</strong><span><?php echo esc_html($s['wechat']); ?></span></div>
            </div>
            <?php echo csop_inquiry_form($p['title']); ?>
        </div>
    </section>
    <section class="csop-section">
        <div class="csop-container">
            <div class="csop-head"><div><div class="csop-kicker">Related</div><h2>其他可咨询服务</h2></div></div>
            <div class="csop-grid">
                <?php foreach (array_slice($related, 0, 3) as $item) echo csop_render_card($item); ?>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

function csop_inquiry_form($product = '') {
    ob_start();
    ?>
    <form class="csop-inquiry-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="csop_submit_inquiry">
        <?php wp_nonce_field('csop_submit_inquiry', 'csop_nonce'); ?>
        <input type="hidden" name="product" value="<?php echo esc_attr($product); ?>">
        <input type="hidden" name="landing_page" value="<?php echo esc_url(home_url(add_query_arg(array(), $GLOBALS['wp']->request ?? ''))); ?>">
        <input type="hidden" name="referrer" value="<?php echo isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : ''; ?>">
        <div class="csop-form-row">
            <label>姓名 / 称呼<input type="text" name="name" required></label>
            <label>国家 / 地区<input type="text" name="country" required></label>
            <label>微信 / WhatsApp<input type="text" name="contact" required></label>
            <label>Email<input type="email" name="email"></label>
            <label>面试阶段<select name="service_stage"><option>OA</option><option>VO</option><option>Mock Interview</option><option>Resume / BQ</option><option>Not sure yet</option></select></label>
            <label>目标公司 / 岗位<input type="text" name="target_company"></label>
            <label>时间窗口<input type="text" name="deadline" placeholder="例如：本周 / 下周一 / 待确认"></label>
            <label class="full">需求说明<textarea name="message" placeholder="请填写目标公司、岗位、面试平台、轮次、时间窗口和主要需求。"></textarea></label>
        </div>
        <button class="csop-submit" type="submit">提交需求</button>
    </form>
    <?php
    return ob_get_clean();
}

function csop_handle_inquiry() {
    if (!isset($_POST['csop_nonce']) || !wp_verify_nonce($_POST['csop_nonce'], 'csop_submit_inquiry')) wp_die('Invalid inquiry.');
    $fields = array('name', 'country', 'contact', 'email', 'product', 'service_stage', 'target_company', 'deadline', 'message', 'landing_page', 'referrer');
    $data = array();
    foreach ($fields as $field) {
        $data[$field] = isset($_POST[$field]) ? sanitize_textarea_field(wp_unslash($_POST[$field])) : '';
    }
    $title = trim($data['name'] . ' - ' . ($data['product'] ? $data['product'] : $data['service_stage']));
    $post_id = wp_insert_post(array('post_type' => 'csop_inquiry', 'post_status' => 'publish', 'post_title' => $title ? $title : '服务询盘'));
    if ($post_id && !is_wp_error($post_id)) {
        foreach ($data as $key => $value) update_post_meta($post_id, '_' . $key, $value);
    }
    $redirect = wp_get_referer() ? wp_get_referer() : home_url('/');
    wp_safe_redirect(add_query_arg('inquiry', 'sent', $redirect));
    exit;
}

function csop_front_css() {
    ?>
    <style>
    .csop-products-page{--csop-ink:#212121;--csop-muted:#687382;--csop-blue:#2f4468;--csop-accent:#1b78e2;--csop-soft:#f7f8f9;--csop-border:#e3e7eb;font-family:"Open Sans",Arial,sans-serif;color:var(--csop-ink);background:#fff}.csop-products-page *{box-sizing:border-box}.csop-products-page a{text-decoration:none}.csop-container{width:min(1180px,calc(100% - 32px));margin:0 auto}.csop-hero{min-height:430px;padding:90px 0;display:flex;align-items:center;background-size:cover;background-position:center;color:#fff}.csop-hero.detail{min-height:360px}.csop-kicker{margin:0 0 10px;color:var(--csop-accent);font-size:13px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.csop-hero .csop-kicker{color:#dbeaff}.csop-hero h1{max-width:820px;margin:0 0 16px;font-size:clamp(38px,5.6vw,70px);line-height:1.05;font-weight:800;letter-spacing:0}.csop-hero p{max-width:760px;margin:0;color:rgba(255,255,255,.86);font-size:18px;line-height:1.75}.csop-hero-stats{display:grid;grid-template-columns:repeat(3,minmax(0,150px));gap:12px;margin-top:28px}.csop-hero-stats div{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.24);border-radius:8px;padding:14px}.csop-hero-stats strong{display:block;color:#fff;font-size:22px}.csop-hero-stats span{color:rgba(255,255,255,.78);font-size:13px}.csop-section{padding:76px 0}.csop-section.soft{background:var(--csop-soft)}.csop-head{display:flex;justify-content:space-between;gap:24px;margin-bottom:28px}.csop-head h2,.csop-panel h2,.csop-contact-panel h2{margin:0 0 12px;color:var(--csop-blue);font-size:clamp(30px,3.5vw,46px);line-height:1.14;font-weight:800}.csop-head p,.csop-panel p{max-width:760px;margin:0;color:var(--csop-muted);font-size:16px;line-height:1.75}.csop-filter{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:26px}.csop-filter button,.csop-btn{display:inline-flex;align-items:center;justify-content:center;border:1px solid #ccd8e3;background:#fff;color:var(--csop-blue);border-radius:8px;padding:11px 16px;font-weight:800;cursor:pointer}.csop-filter button.active,.csop-btn.primary{background:var(--csop-blue);border-color:var(--csop-blue);color:#fff}.csop-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:24px}.csop-card{background:#fff;border:1px solid var(--csop-border);border-radius:8px;overflow:hidden;box-shadow:0 0 10px rgba(232,234,237,.75);transition:.2s ease}.csop-card:hover{transform:translateY(-3px);box-shadow:0 18px 45px rgba(47,68,104,.13)}.csop-card-image{position:relative;display:block;height:235px;background-size:cover;background-position:center}.csop-card-image:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,transparent 42%,rgba(33,33,33,.36))}.csop-card-image span{position:absolute;z-index:1;left:15px;top:15px;background:#fff;color:var(--csop-blue);border-radius:8px;padding:8px 12px;font-size:12px;font-weight:800}.csop-card-body{padding:22px}.csop-card-kicker{margin:0 0 8px!important;color:var(--csop-accent)!important;font-size:13px!important;font-weight:800}.csop-card h3{margin:0 0 10px;color:var(--csop-blue);font-size:23px;line-height:1.25}.csop-card p{margin:0 0 16px;color:var(--csop-muted);line-height:1.68}.csop-card-meta{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}.csop-card-meta div,.csop-specs div{background:#f7f8f9;border:1px solid var(--csop-border);border-radius:8px;padding:12px}.csop-card-meta strong,.csop-specs strong{display:block;color:var(--csop-blue);font-size:14px}.csop-card-meta span,.csop-specs span{display:block;color:var(--csop-muted);font-size:12px;margin-top:4px}.csop-actions{display:flex;gap:10px;flex-wrap:wrap}.csop-quote-band{display:flex;justify-content:space-between;gap:24px;align-items:center;background:#2f4468;color:#fff;border-radius:8px;padding:32px}.csop-quote-band h2{margin:0 0 8px;font-size:30px}.csop-quote-band p{margin:0;color:rgba(255,255,255,.78);line-height:1.7}.csop-back{display:inline-flex;margin-bottom:16px;color:#fff;font-weight:800}.csop-detail-grid{display:grid;grid-template-columns:1.04fr .96fr;gap:28px;align-items:start}.csop-main-image,.csop-panel,.csop-contact-panel,.csop-inquiry-form{border:1px solid var(--csop-border);border-radius:8px;box-shadow:0 0 10px rgba(232,234,237,.75);background:#fff}.csop-main-image{overflow:hidden}.csop-main-image img{display:block;width:100%;height:460px;object-fit:cover}.csop-gallery{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:12px}.csop-gallery img{width:100%;height:118px;object-fit:cover;border:1px solid var(--csop-border);border-radius:8px}.csop-panel{padding:26px}.csop-specs{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin:22px 0}.csop-check{display:flex;gap:10px;align-items:flex-start;background:#f7f8f9;border:1px solid var(--csop-border);border-radius:8px;padding:12px 14px;margin:10px 0;color:#405166}.csop-check b{color:var(--csop-accent)}.csop-form-grid{display:grid;grid-template-columns:.82fr 1.18fr;gap:24px}.csop-contact-panel{background:var(--csop-blue);color:#fff;padding:28px}.csop-contact-panel p{color:rgba(255,255,255,.78);line-height:1.7}.csop-contact-panel h2{color:#fff}.csop-contact-line{display:flex;justify-content:space-between;gap:16px;border-top:1px solid rgba(255,255,255,.18);padding:14px 0}.csop-inquiry-form{padding:24px}.csop-form-row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.csop-form-row label{display:block;color:var(--csop-blue);font-weight:800}.csop-form-row label.full{grid-column:1/-1}.csop-form-row input,.csop-form-row select,.csop-form-row textarea{width:100%;margin-top:6px;border:1px solid #ccd8e3;border-radius:8px;padding:13px;background:#fff}.csop-form-row textarea{min-height:118px}.csop-submit{width:100%;margin-top:16px;border:0;border-radius:8px;background:var(--csop-blue);color:#fff;font-weight:900;padding:15px 20px;cursor:pointer}.csop-card[hidden]{display:none!important}@media(max-width:980px){.csop-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.csop-detail-grid,.csop-form-grid{grid-template-columns:1fr}.csop-quote-band{align-items:flex-start;flex-direction:column}}@media(max-width:640px){.csop-hero{min-height:330px;padding:64px 0}.csop-section{padding:52px 0}.csop-grid,.csop-form-row,.csop-hero-stats{grid-template-columns:1fr}.csop-card-image{height:210px}.csop-main-image img{height:300px}.csop-gallery img{height:84px}.csop-card-body,.csop-panel,.csop-contact-panel,.csop-inquiry-form{padding:20px}}
    .csop-products-page .csop-reveal{opacity:0;transform:translateY(22px);transition:opacity .68s ease,transform .68s ease;will-change:opacity,transform}.csop-products-page .csop-reveal.csop-in{opacity:1;transform:none}.csop-products-page .csop-reveal-delay-1{transition-delay:.07s}.csop-products-page .csop-reveal-delay-2{transition-delay:.14s}.csop-products-page .csop-reveal-delay-3{transition-delay:.21s}@media (prefers-reduced-motion:reduce){.csop-products-page .csop-reveal{opacity:1;transform:none;transition:none}}
    </style>
    <?php
}

/**
 * csvosupport global header and footer system.
 * Shortcodes: [csop_header], [csop_footer]
 */
defined('ABSPATH') || exit;

add_action('admin_menu', 'csop_hf_admin_menu', 20);
add_action('admin_enqueue_scripts', 'csop_hf_admin_assets');
add_action('init', 'csop_hf_apply_csvosupport_brand', 5);
add_action('init', 'csop_hf_apply_oavo_contacts', 6);
add_action('init', 'csop_hf_apply_oavo_local_media', 7);
add_action('init', 'csop_hf_apply_text_logo', 8);
add_action('template_redirect', 'csop_hf_start_buffer', 0);
add_shortcode('csop_header', 'csop_hf_header_shortcode');
add_shortcode('csop_footer', 'csop_hf_footer_shortcode');

function csop_hf_option_name() {
    return 'csop_header_footer_options_v1';
}

function csop_hf_defaults() {
    return array(
        'enabled_header' => '1',
        'enabled_footer' => '1',
        'container_width' => '1200',
        'nav_height' => '65',
        'nav_bg' => '#242226',
        'nav_hover_bg' => '#35343a',
        'nav_text' => '#ffffff',
        'site_name' => 'csvosupport',
        'home_url' => '/',
        'logo_image' => '',
        'mobile_label' => '菜单',
        'search_label' => 'Open search',
        'search_placeholder' => 'Search...',
        'menu_items' => "🏚 网站首页|/\n📖 面试真题|/blog/\n⏺ 关于我们|/about_us/\n💰 服务&价格|/price/\n📬 联系学长|/contact/",
        'language_label' => 'Language',
        'language_items' => "简体中文|/|https://csvosupport.com/wp-content/uploads/2026/06/local-ext-01-main-mevia-site-zh-cn.png\nEnglish|/en/|https://csvosupport.com/wp-content/uploads/2026/06/local-ext-02-main-mevia-site-en-us.png\n繁體中文|/zh_tw/|https://csvosupport.com/wp-content/uploads/2026/06/local-ext-03-main-mevia-site-zh-tw.png",
        'footer_bg' => '#f6f6f6',
        'footer_border' => 'rgba(135,135,135,0.52)',
        'qr_wechat_image' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-wechat-qr-30.jpg',
        'qr_wechat_label' => '微信 Coding0201',
        'qr_whatsapp_image' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-wechat-qr-30.jpg',
        'qr_whatsapp_label' => '微信扫码联系',
        'quick_title' => 'Quick Links',
        'quick_links' => "🏚 网站首页|/\n📖 面试真题|/blog/\n⏺ 关于我们|/about_us/\n💰  服务&价格|/price/\n📬 联系学长|/contact/",
        'contact_title' => 'Get In Touch',
        'telegram_text' => '@OAVOProxy',
        'telegram_url' => 'https://t.me/OAVOProxy',
        'whatsapp_text' => '+86 17863968105',
        'whatsapp_url' => 'tel:+8617863968105',
        'email_text' => 'catcstech@gmail.com',
        'email_url' => 'mailto:catcstech@gmail.com',
        'wechat_text' => 'Coding0201',
        'copyright_text' => '© 2026 csvosupport • 版权所有',
        'footer_contact_text' => '联系我们',
        'footer_contact_url' => '/contact/',
    );
}

function csop_hf_apply_csvosupport_brand() {
    if (get_option('csop_hf_brand_csvosupport_v1')) return;
    $saved = get_option(csop_hf_option_name(), array());
    if (!is_array($saved)) $saved = array();
    $saved['site_name'] = 'csvosupport';
    $saved['logo_image'] = '';
    $saved['copyright_text'] = '© 2026 csvosupport • 版权所有';
    update_option(csop_hf_option_name(), array_merge(csop_hf_defaults(), $saved));
    update_option('csop_hf_brand_csvosupport_v1', 1);
}

function csop_hf_apply_oavo_contacts() {
    if (get_option('csop_hf_oavo_contacts_v1')) return;
    $saved = get_option(csop_hf_option_name(), array());
    if (!is_array($saved)) $saved = array();
    $saved = array_merge($saved, array(
        'qr_wechat_image' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-wechat-qr-30.jpg',
        'qr_wechat_label' => '微信 Coding0201',
        'qr_whatsapp_image' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-wechat-qr-30.jpg',
        'qr_whatsapp_label' => '微信扫码联系',
        'telegram_text' => '@OAVOProxy',
        'telegram_url' => 'https://t.me/OAVOProxy',
        'whatsapp_text' => '+86 17863968105',
        'whatsapp_url' => 'tel:+8617863968105',
        'email_text' => 'catcstech@gmail.com',
        'email_url' => 'mailto:catcstech@gmail.com',
        'wechat_text' => 'Coding0201',
    ));
    update_option(csop_hf_option_name(), array_merge(csop_hf_defaults(), $saved));
    update_option('csop_hf_oavo_contacts_v1', 1);
}

function csop_hf_apply_oavo_local_media() {
    if (get_option('csop_hf_oavo_local_media_v1')) return;
    $saved = get_option(csop_hf_option_name(), array());
    if (!is_array($saved)) $saved = array();
    $saved['qr_wechat_image'] = 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-wechat-qr-30.jpg';
    $saved['qr_whatsapp_image'] = 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-wechat-qr-30.jpg';
    update_option(csop_hf_option_name(), array_merge(csop_hf_defaults(), $saved));
    update_option('csop_hf_oavo_local_media_v1', 1);
}

function csop_hf_apply_text_logo() {
    if (get_option('csop_hf_text_logo_v1')) return;
    $saved = get_option(csop_hf_option_name(), array());
    if (!is_array($saved)) $saved = array();
    $saved['logo_image'] = '';
    update_option(csop_hf_option_name(), array_merge(csop_hf_defaults(), $saved));
    update_option('csop_hf_text_logo_v1', 1);
}

function csop_hf_tr_dict_en() {
    static $d = null;
    if ($d !== null) return $d;
    $d = array(
        '首页-NEW' => 'Home-NEW',
        '菜单' => 'menu',
        '🏚 网站首页' => 'Homepage',
        '📖 面试真题' => '📖 Interview Questions',
        '⏺ 关于我们' => '⏺ About Us',
        '💰 服务&amp;价格' => '💰 Services &amp; Prices',
        '📬 联系学长' => '📬 Contact Alumni',
        '简体中文' => 'Simplified Chinese',
        '繁體中文' => 'Traditional Chinese',
        'csvosupport 工作室｜OA 代写｜VO 代面｜面试辅导' => 'csvosupport Studio｜OA help｜VO mock｜Interview coaching',
        '靠谱面试辅助服务' => 'Reliable interview coaching services',
        '给你最优质的海外求职辅助' => 'Premium overseas job-seeking support for you',
        'csvosupport团队成员包括来自大厂科技公司的工程师、研究人员，以及有ACM算法竞赛背景的导师，致力于提供最优质的面试辅导、OA代做、VO辅助和代面试服务。' => 'The csvosupport team includes engineers and researchers from big tech companies, as well as mentors with ACM algorithm competition backgrounds, dedicated to providing top-quality interview coaching, OA completion, VO assistance, and proxy interview services.',
        '我们专注服务科技行业的求职全过程。自成立以来，我们坚持以高质量辅导和透明服务为核心，立志成为VO辅助和代面领域的领头羊。' => 'We focus on serving the entire job-seeking process in the tech industry. Since our founding, we have been committed to high-quality coaching and transparent service, aiming to become a leader in VO assistance and interview proxy services.',
        '联系我们' => 'Contact us',
        '北美最强的面试辅助团队' => 'North America\'s top interview coaching team',
        '我们凭借多年北美及海外求职实战经验，打造了业内极具口碑的OA代写、模拟面试、VO代面、面试辅助一体化方案。从笔试到视频面试，从技术细节到表达策略，我们深知大厂招聘的每一道关卡，能够为你量身定制最优解法。' => 'With years of hands-on experience in North American and overseas job hunting, we have built a highly regarded all-in-one solution covering OA ghostwriting, mock interviews, VO proxy, and interview assistance. From written tests to video interviews, from technical details to communication strategy, we know every hurdle of big-tech hiring and can tailor the optimal approach for you.',
        '过去几年，我们已帮助数百位客户成功拿下 Amazon、Bloomberg、Pinterest、Meta、Stripe、Coinbase、DoorDash、Optiver、Citadel 等顶级公司的 Offer，不仅稳稳进入目标公司，更收获了远超行业平均水平的高额薪资。我们的客户中，不乏年薪double的工程师，也有实现职业跨越、直接跃升为senior岗位的案例。' => 'Over the past few years, we have helped hundreds of clients secure offers from top companies like Amazon, Bloomberg, Pinterest, Meta, Stripe, Coinbase, DoorDash, Optiver, Citadel - not only landing their target roles but also achieving salaries far above industry average. Among our clients are engineers who doubled their annual salary, as well as those who made a career leap directly into senior positions.',
        '我们不是流水线服务，而是你的定制化求职伙伴：每一位客户都会配备专属顾问与技术导师，全程一对一指导，确保你的每一次OA与VO表现都能精准击中招聘方的需求。' => 'We are not an assembly-line service but your customized career partner: every client is assigned a dedicated consultant and technical mentor for one-on-one guidance, ensuring your OA and VO performance hits the mark every time.',
        '了解更多服务细节' => 'Learn more about our services',
        '服务范围' => 'Services',
        'OA代做' => 'OA Assistance',
        'OA 代做保过，竞赛大神带你满分通过' => 'OA Guaranteed Pass, Contest Experts Get You Full Score',
        '秒杀所有edge case，确保满分提交' => 'Crush every edge case for a guaranteed perfect-score submission.',
        '精通大厂当年最新题库，全覆盖！' => 'Master latest big tech question banks, full coverage',
        '最优解 + 高可读性，品质双重保证' => 'Optimal Solution + High Readability, Dual Quality Guarantee',
        'VO辅助' => 'VO Assistance',
        '实时传输高质答案，0破绽，满分体验' => 'Real-time high-quality answers, 0 flaws, full-score experience',
        '海量辅助案例，完美品质，眼见为实' => 'Extensive case library, flawless quality, seeing is believing',
        '多通道传输，语音/文字同步推送答案' => 'Multi-channel delivery, voice/text answers pushed simultaneously',
        'VO代面' => 'VO Mock Interview',
        '真人 VO代面，客户无需出镜' => 'Live VO proxy interview, client off-camera',
        '免费语音沟通，免费Mock展示' => 'Free voice consultation, free mock demo',
        '代面导师人均大厂senior+在职' => 'All proxy tutors are senior+ at big tech',
        '稳OFFER，不只是说说而已' => 'Guaranteed Offer, more than just talk',
        '简历润色' => 'Resume Polish',
        '直击工业技术栈，拒绝toy project' => 'Focus on real industry tech stacks; no toy projects.',
        '100%原创，围绕客户经历量身定做' => '100% original, tailored to your background',
        '技术深度挖掘，与招聘JD无缝匹配' => 'Deep technical insight, seamless match with job JD',
        '大厂视角审阅，HR + HM 二次优化' => 'Big-tech review, HR + HM dual polish',
        '面试辅导' => 'Interview Coaching',
        '大厂面试官一对一辅导，全链路覆盖' => 'Big tech interviewers 1-on-1 coaching, full coverage',
        '真题实战演练，模拟当下最热考点' => 'Real question drills, simulate hottest topics',
        '个性化刷题路线，传授独家技巧' => 'Customized practice roadmap, exclusive techniques',
        '面试官思维建模，教你识别提问意图' => 'Interviewer mindset modeling, learn to decode question intent',
        '我们有哪些优势？' => 'What sets us apart?',
        '代面现场录音供您参考，所见即所得' => 'Proxy interview recordings for reference, what you see is what you get',
        '我们的操作成功率及其高，能完全保证您的隐私' => 'Extremely high success rate; your privacy is fully protected.',
        '服务范围 – 专注求职面试' => 'Services – Focused on Job Interviews',
        'OA代写' => 'OA Tutoring',
        'VO代做' => 'VO Tutoring',
        '代面试' => 'proxy interview',
        '面试辅助' => 'Interview Assistance',
        'SDE代面试' => 'SDE Interview Proxy',
        'MLE代面试' => 'MLE Interview Proxy',
        '系统设计代面试' => 'System Design Interview',
        'CV修改' => 'CV Editing',
        '面试Mock' => 'Mock Interview',
        '面经分享' => 'Interview Experience',
        'VO助攻' => 'VO Support',
        'HackerRank代写' => 'HackerRank OA completion',
        'CodeSignal代做' => 'CodeSignal OA completion',
        'Amazon代面试' => 'Amazon Interview Proxy',
        '亚麻辅助' => 'Amazon Prep',
        'Meta代面试' => 'Meta Interview',
        'Pinterest代面试' => 'Pinterest proxy interview',
        'Bloomberg代面试' => 'Bloomberg proxy interview',
        'Uber代面试' => 'Uber Interview',
        'Citadel代做OA' => 'Citadel OA Proxy',
        'Optiver代面试' => 'Optiver Interview Proxy',
        'Stripe代面试' => 'Stripe Interview Proxy',
        'Snowflake代做面试' => 'Snowflake Interview Proxy Service',
        'Atlassian面试辅助' => 'Atlassian interview coaching',
        '北美大厂代面试' => 'Big Tech Interview',
        'Coderpad代面试' => 'Coderpad Interview Proxy',
        '技术面试辅助' => 'Tech Interview Aid',
        '北美求职辅导' => 'NA Career Coaching',
        '远程面试辅助' => 'Remote Interview Aid',
        '硅谷代面试' => 'Silicon Valley proxy interview',
        '美国面试辅导' => 'US Interview Coaching',
        'mock面试' => 'Mock Interview',
        '模拟面试' => 'Mock Interview',
        'BO辅导' => 'Bloomberg Coaching',
        '算法辅导' => 'Algorithm Coaching',
        '系统设计辅导' => 'System Design Coaching',
        'SDE培训' => 'SDE training',
        'MLE培训' => 'MLE training',
        '为什么选择我们' => 'Why Choose Us',
        '「业内口碑见证」' => 'Industry Recognition',
        '✓ 8年专注面试辅助，累计服务客户1000+' => '✓ 8 years in interview prep, 1000+ clients served',
        '✓ 免费语音咨询，先沟通后决策，0风险' => '✓ Free voice consult, talk first then decide, zero risk',
        '✓ 真实成功案例可查，支持现场demo演示' => '✓ Real success cases verifiable; live demos available.',
        '「严选导师团队」' => 'Elite Mentors',
        '✓ 导师均来自大厂在职/资深工程师背景' => '✓ Mentors are senior engineers from top tech companies',
        '✓ 覆盖SDE、MLE、Data、System Design等方向' => '✓ Covers SDE, MLE, Data, System Design, and more',
        '✓ 学术+职业履历全透明，支持背景核验' => '✓ Transparent academic + work history, background check supported',
        '「诚信服务保障」' => 'Trusted Service Guarantee',
        '✓ 客户信息严格保密，服务全程加密' => '✓ Client info strictly confidential, fully encrypted throughout',
        '✓ 交付不满意可协商，不敷衍不推诿' => '✓ Not satisfied? We negotiate, no excuses',
        '✓ 服务完成不代表结束，长期售后支持' => '✓ Service Done Doesn\'t Mean Goodbye, Lifetime Support',
        '客户评价' => 'Client Reviews',
        '100+ 成功案例，真实反馈见证我们的专业实力' => '100+ success cases; real feedback proves our expertise.',
        '真实案例' => 'Real Cases',
        'OA通过案例' => 'OA Pass Cases',
        'VO通过案例' => 'VO Pass Cases',
        '真实案例 1' => 'Real Case 1',
        '真实案例 2' => 'Real Case 2',
        '真实案例 3' => 'Real Case 3',
        'OA案例 1' => 'OA Case 1',
        'OA案例 2' => 'OA Case 2',
        'OA案例 3' => 'OA Case 3',
        'OA案例 4' => 'OA Case 4',
        'OA案例 5' => 'OA Case 5',
        'OA案例 6' => 'OA Case 6',
        'OA案例 7' => 'OA Case 7',
        'OA案例 8' => 'OA Case 8',
        'OA案例 9' => 'OA Case 9',
        'VO案例 1' => 'VO Case 1',
        'VO案例 2' => 'VO Case 2',
        'VO案例 3' => 'VO Mock Case 3',
        'VO案例 4' => 'VO Mock Case 4',
        'VO案例 5' => 'VO Mock Case 5',
        'VO案例 6' => 'VO Mock Case 6',
        'VO案例 7' => 'VO Mock Case 7',
        'VO案例 8' => 'VO Mock Case 8',
        'VO案例 9' => 'VO Mock Case 9',
        '客户评价 1' => 'Client Review 1',
        '客户评价 2' => 'Client Review 2',
        '客户评价 3' => 'Client Review 3',
        '客户评价 4' => 'Client Review 4',
        '客户评价 5' => 'Client Review 5',
        '客户评价 6' => 'Client Review 6',
        '客户评价 7' => 'Client Review 7',
        '客户评价 8' => 'Client Review 8',
        '微信扫码联系' => 'Scan WeChat to connect',
        '💰  服务&amp;价格' => '💰 Services &amp; Pricing',
        '© 2026 csvosupport • 版权所有' => '© 2026 csvosupport • All Rights Reserved',
        '真实案例 1 - csvosupport' => 'Real Case 1 - csvosupport',
        '真实案例 2 - csvosupport' => 'Real Case 2 - csvosupport',
        '真实案例 3 - csvosupport' => 'Real Case 3 - csvosupport',
        'OA案例 1 - csvosupport' => 'OA Case 1 - csvosupport',
        'OA案例 2 - csvosupport' => 'OA Case 2 - csvosupport',
        'OA案例 3 - csvosupport' => 'OA Case 3 - csvosupport',
        'OA案例 4 - csvosupport' => 'OA Case 4 - csvosupport',
        'OA案例 5 - csvosupport' => 'OA Case 5 - csvosupport',
        'OA案例 6 - csvosupport' => 'OA Case 6 - csvosupport',
        'OA案例 7 - csvosupport' => 'OA Case 7 - csvosupport',
        'OA案例 8 - csvosupport' => 'OA Case 8 - csvosupport',
        'OA案例 9 - csvosupport' => 'OA Case 9 - csvosupport',
        'VO案例 1 - csvosupport' => 'VO Case 1 - csvosupport',
        'VO案例 2 - csvosupport' => 'VO Case 2 - csvosupport',
        'VO案例 3 - csvosupport' => 'VO Case 3 - csvosupport',
        'VO案例 4 - csvosupport' => 'VO Case 4 - csvosupport',
        'VO案例 5 - csvosupport' => 'VO Case 5 - csvosupport',
        'VO案例 6 - csvosupport' => 'VO Case 6 - csvosupport',
        'VO案例 7 - csvosupport' => 'VO Case 7 - csvosupport',
        'VO案例 8 - csvosupport' => 'VO Case 8 - csvosupport',
        'VO案例 9 - csvosupport' => 'VO Case 9 - csvosupport',
        '客户评价 1 - csvosupport' => 'Client Review 1 - csvosupport',
        '客户评价 2 - csvosupport' => 'Client Review 2 - csvosupport',
        '客户评价 3 - csvosupport' => 'Client Review 3 - csvosupport',
        '客户评价 4 - csvosupport' => 'Client Review 4 - csvosupport',
        '客户评价 5 - csvosupport' => 'Client Review 5 - csvosupport',
        '客户评价 6 - csvosupport' => 'Client Review 6 - csvosupport',
        '客户评价 7 - csvosupport' => 'Client Review 7 - csvosupport',
        '客户评价 8 - csvosupport' => 'Client Reviews 8 - csvosupport',
        '关于我们' => 'About Us',
        '关于我们 &#8211; CSVO Support' => 'About Us – CSVO Support',
        '关于 csvosupport' => 'About csvosupport',
        '成立于2022年，专注于海外科技求职面试辅导与技术支持服务。
我们坚信' => 'Founded in 2022, focused on overseas tech career interview coaching and technical support services.
We believe',
        '成立于2022年，专注于海外科技求职面试辅导与技术支持服务。 我们坚信' => 'was founded in 2022 and focuses on interview coaching and technical support for overseas technology job seekers. We believe in ',
        '透明、专业、有温度' => 'Transparent, professional, warm',
        '的服务，能真正帮助求职者跨越面试门槛，拿下心仪Offer。' => ' services that truly help job seekers pass interviews and land desired offers.',
        '我们的使命' => 'Our mission',
        '从OA笔试到VO面试，从简历润色到系统设计，我们陪伴每一位客户走过求职全流程。
团队核心成员均来自一线大厂（Google、Meta、Amazon等），拥有丰富的面试官经验与算法竞赛背景。
我们不做流水线服务，而是为每一位客户匹配最合适的导师，提供' => 'From OA to VO, from resume polish to system design, we accompany every client through the entire job-seeking process. Our core team members all come from top-tier companies (Google, Meta, Amazon, etc.) with extensive interviewer experience and algorithm competition backgrounds. We don\'t offer cookie-cutter services; instead we match each client with a suitable mentor.',
        '从OA笔试到VO面试，从简历润色到系统设计，我们陪伴每一位客户走过求职全流程。 团队核心成员均来自一线大厂（Google、Meta、Amazon等），拥有丰富的面试官经验与算法竞赛背景。 我们不做流水线服务，而是为每一位客户匹配最合适的导师，提供' => 'From OA written assessments to virtual onsite interviews, from resume polishing to system design, we support every client throughout the full job search process. Core team members come from leading tech companies (Google, Meta, Amazon, etc.) and bring extensive interviewer experience and algorithm competition backgrounds. We do not run assembly-line services; instead, we match each client with the right mentor and provide ',
        '定制化、可落地' => 'Customized, Practical',
        '的面试解决方案。' => ' Interview Solutions.',
        '精准匹配' => 'Precise Matching',
        '根据目标岗位、面试轮次匹配最合适的导师' => 'Matches the best mentor by target role and interview rounds',
        '严格保密' => 'Strict Confidentiality',
        '客户信息与服务过程全程加密，隐私零泄露' => 'Client data and service process fully encrypted, zero privacy leaks',
        '透明沟通' => 'Transparent Communication',
        '免费语音咨询，先沟通再决策，0风险' => 'Free voice consultation, decide after talking, 0 risk',
        '核心团队' => 'Core Team',
        '团队成员均来自大厂在职或资深工程师背景，100%真实可核验。' => 'Our team members are all current or senior engineers from top tech companies, 100% real and verifiable.',
        '创始人 · 首席面试顾问' => 'Founder &amp; Chief Interview Consultant',
        '前Google Engineer，3年+大厂经验，ACM EC FINAL银牌，辅导800+学员进入北美大厂。' => 'Former Google Engineer, 3+ years at big tech, ACM EC FINAL Silver Medalist, coached 800+ students into top North American companies.',
        '技术导师 · System Design' => 'Tech Mentor · System Design',
        'Amazon Senior SDE，专注分布式系统与架构设计，擅长系统设计面试辅导与实战模拟。' => 'Amazon Senior SDE, focused on distributed systems and architecture design, skilled in system design interview coaching and mock interviews.',
        '技术导师 · Algorithm & MLE' => 'Tech mentors · Algorithm &amp; MLE',
        'Meta Research Scientist，ML/NLP方向博士，LeetCode全球Top 200，精通算法与机器学习面试。' => 'Meta Research Scientist, PhD in ML/NLP direction, LeetCode Global Top 200, expert in algorithms and ML interviews.',
        '面试辅导 · BQ & Resume' => 'Interview Coaching · BQ &amp; Resume',
        '前Google HRBP，6年+招聘经验，擅长行为面试辅导、简历优化与职业规划咨询。' => 'Former Google HRBP, 6+ years recruiting experience, skilled in behavioral interview coaching, resume optimization, and career planning consulting.',
        '服务客户' => 'Clients Served',
        '行业深耕（年）' => 'Years of Experience',
        '客户满意度' => 'Customer satisfaction',
        '覆盖大厂数量' => 'Major tech covered',
        '" 我们不是中介，是你求职路上的' => 'We are not an agency, we are your job-seeking',
        '技术伙伴' => 'Tech Partner',
        '用真实的能力，帮你拿到真实的Offer。"' => 'Real skills, real offers.',
        '—— csvosupport 团队' => '—— csvosupport team',
        '—— 微信扫码联系 ——' => 'Scan WeChat to connect',
        '📌 添加时请注明您的面试/作业需求，以便我们快速评估' => '📌 Please specify your interview/assignment needs when adding us for quick evaluation.',
        '✅ 100% 代码保证唯一' => '✅ 100% unique code guarantee',
        '✅ 100% 客户信息保密' => '✅ 100% client privacy protection',
        '✅ 100% 服务质量保障' => '✅ 100% service quality guarantee',
        '面试真题' => 'Real Interview Questions',
        '面试真题 &#8211; CSVO Support' => 'Interview Questions – CSVO Support',
        '发布日期: 26 June, 2026' => 'Published: 26 June, 2026',
        'Databricks SWE OA 面经｜SQL 执行图、日志窗口与 AI Coding 题型解析' => 'Databricks SWE OA Interview Experience｜SQL Execution Plan, Log Window &amp; AI Coding Question Analysis',
        'Databricks SWE OA 面经复盘，覆盖日志窗口聚合、SQL 执行依赖图、AI Coding 修复分区读取器，以及备考重点。' => 'Databricks SWE OA interview review covering log window aggregation, SQL execution dependency graph, AI Coding to fix partition reader, and exam prep focus areas.',
        '阅读更多' => 'Read More',
        '发布日期: 25 June, 2026' => 'Release Date: 25 June, 2026',
        'Google SWE VO 面经｜Virtual Onsite Coding + System Design + BQ 全流程复盘' => 'Google SWE VO Interview Experience | Virtual Onsite Coding + System Design + BQ Full Process Review',
        'Google SWE VO 面经复盘，覆盖 Virtual Onsite Coding、系统设计、Googliness/BQ、项目深挖和备考建议。' => 'Google SWE VO interview review covering Virtual Onsite Coding, system design, Googliness/BQ, project deep dive, and exam prep tips.',
        'Apple SWE VO 面经｜Coding、项目深挖、系统设计与 BQ 复盘' => 'Apple SWE VO interview experience｜Coding, deep dive on projects, System Design, and BQ review',
        'Apple SWE VO 面经复盘，覆盖数组事件合并、权限与缓存追问、照片同步系统设计、项目深挖和 BQ 准备。' => 'Apple SWE VO Interview Experience Review, covering array event merging, permissions and caching follow-ups, photo sync system design, project deep dive, and BQ preparation.',
        'Microsoft SWE VO 面经｜Coding + 代码设计 + System Design + BQ 全流程复盘' => 'Microsoft SWE VO Interview Experience | Coding + Code Design + System Design + BQ Full Process Review',
        'Microsoft SWE VO 面经复盘，覆盖区间 Coding、带 TTL 的 Key-Value Store、Teams 在线状态系统设计、HM Round 和 BQ 准备建议。' => 'Microsoft SWE VO interview review covering range Coding, Key-Value Store with TTL, Teams online status system design, HM Round, and BQ preparation tips.',
        '发布日期: 23 June, 2026' => 'Release Date: 23 June, 2026',
        'Meta SWE OA 面经｜CodeSignal 技术筛选 + AI Coding 题型解析' => 'Meta SWE OA Interview Experience｜CodeSignal Technical Screening + AI Coding Question Analysis',
        'Meta SWE OA 面经复盘，覆盖 CodeSignal 技术筛选、AI Coding 校验、文件系统、BFS、滑动窗口题型和备考建议。' => 'Meta SWE OA interview review covering CodeSignal technical screening, AI Coding validation, file system, BFS, sliding window problems, and exam prep tips.',
        '发布日期: 22 June, 2026' => 'Release Date: 22 June, 2026',
        'Snowflake SWE VO 面经｜Coding + 数据系统设计 + 项目深挖 + BQ 全流程复盘' => 'Snowflake SWE VO Interview Experience｜Coding + Data System Design + Deep Dive Projects + BQ Full Process Review',
        'Snowflake SWE VO 面经复盘，覆盖 Coding、SQL parser、query history system design、HM Round/BQ 与备考建议。' => 'Snowflake SWE VO interview review covering Coding, SQL parser, query history system design, HM Round/BQ, and exam prep tips.',
        '发布日期: 20 June, 2026' => 'Release Date: 20 June, 2026',
        'Google VO 两轮面经｜Coding 与 Googleyness 项目深挖复盘' => 'Google VO Two-Round Interview Experience｜Coding &amp; Googleyness Deep Dive Project Review',
        'Google VO 两轮面经：复盘第一面 Coding、第二面 Googleyness 与项目深挖，整理算法沟通、边界测试、行为问题和准备重点。' => 'Google VO two-round interview review: first round Coding, second round Googleyness and project deep dive, covering algorithm communication, edge-case testing, behavioral questions, and prep focus areas.',
        'Google NG 两轮 VO 真实复盘｜谷歌面试官太会“挖”了！' => 'Google NG two-round VO real review｜Google interviewers really know how to dig deep!',
        '刚带完一位学员拿下 Google ng VO 双轮全过，这场面试可以说把“基础+表达”考到了极致。没有花里胡哨的题，全是细节的考察。我们当天' => 'Just helped a student ace both rounds of Google ng VO. This interview really tested fundamentals + communication to the extreme. No fancy tricks - all about detail. We that day...',
        '发布日期: 21 June, 2026' => 'Release Date: 21 June, 2026',
        'Stripe VO 5轮面试分享 SDE的难度挺大的' => 'Stripe VO 5-round interview sharing - SDE difficulty is quite high.',
        'InterviewAid团队专注技术求职辅导多年 ，我们助力无数学员实现职业蜕变。学员 L 曾是饱受加班与低薪困扰的会计，因向往 tech' => 'The InterviewAid team has focused on tech career coaching for years, helping countless clients achieve career transformations. Client L was an accountant plagued by overtime and low pay, who aspired to tech...',
        '-------微信二维码↑-----' => '-------WeChat QR↑-----',
        '为了保证我尽快联系和评估您的面试，作业, 请注明您的面试，作业具体要求' => 'To help us quickly assess your interview or assignment, please specify your interview and assignment requirements.',
        '100% Plagiarism Free 代码保证唯一' => '100% Plagiarism Free - Code uniqueness guaranteed',
        '100% Confidentiality 完全保密' => '100% Confidentiality',
        '100% Quality Assurance 保证质量' => '100% Quality Assurance',
        'More on Google NG 两轮 VO 真实复盘｜谷歌面试官太会“挖”了！' => 'More on Google NG two-round VO real review｜Google interviewers really know how to dig deep!',
        'More on Stripe VO 5轮面试分享 SDE的难度挺大的' => 'More on Stripe VO 5-round interview sharing — SDE difficulty is quite high',
        '联系学长' => 'Contact Mentor',
        '联系学长 &#8211; CSVO Support' => 'Contact Us – CSVO Support',
        '联系学长 Contact Us' => 'Contact Us',
        '有需要随时联系我，面试代面，考试，作业，代写OA' => 'Reach out anytime: interview proxy, exams, assignments, OA writing',
        '&nbsp;为了保证我们尽快联系和评估您的需求，添加时请注明您的面试、笔试具体要求' => 'To help us quickly assess your needs, please specify your interview and written test requirements when adding us.',
        '&nbsp;注意:&nbsp;全年无休，24小时响应' => 'Note: 24/7, instant response',
        '服务&#038;价格' => 'Services &amp; Pricing',
        '服务&#038;价格 &#8211; CSVO Support' => 'Services &amp; Pricing - CSVO Support',
        '服务与价格' => 'Services &amp; pricing',
        '我们提供' => 'We offer',
        '透明、按需定制' => 'Transparent, On-Demand',
        '的服务报价。所有服务均先沟通需求、确认方案后报价，' => 'Service pricing. All services require discussing needs and confirming the plan before quoting.',
        '不满意不收费' => 'Free if unsatisfied',
        'OA辅助服务' => 'OA assistance',
        '热门' => 'Popular',
        '专业提供' => 'Professional service',
        '在线评测（OA）代写服务' => 'Online Assessment (OA) completion service',
        '，确保所有测试用例 100% 通过，不通过不收费。' => 'Ensuring all test cases pass 100%, no charge if not.',
        '🔹 服务方式：' => 'Service Options:',
        '通过远程控制软件（ToDesk / TeamViewer）无痕操作，适配 HackerRank、CodeSignal 等主流平台。' => 'Seamless operation via remote control software (ToDesk / TeamViewer), compatible with HackerRank, CodeSignal and other major platforms.',
        '$300 起' => 'From $300',
        '咨询详情 →' => 'Details →',
        '面试辅助（VO辅助）' => 'Interview Assistance (VO Support)',
        '明星服务' => 'Premier service',
        '大厂在职工程师为您提供' => 'Delivered by engineers from top tech companies',
        '实时提示与思路' => 'Real-Time Hints &amp; Strategies',
        '，效果远超AI辅助，助您在VO面试中脱颖而出。' => 'Far outperforms AI tools; helps you ace your VO interview.',
        '🔹 覆盖内容：' => 'Coverage:',
        'BQ问题 · Coding · Follow-up · 项目深挖 · 技术八股 · System Design' => 'BQ Questions · Coding · Follow-up · Deep Dive Projects · Technical Fundamentals · System Design',
        '代面试服务' => 'Interview proxy service',
        '高端定制' => 'Bespoke solutions',
        '通过' => 'Pass',
        '摄像头转接 + 变声技术' => 'Camera switching + voice modulation',
        '，由资深导师全程代面，助您直通Offer。' => 'Senior tutors handle the full interview for you, guiding you straight to the Offer.',
        '🔹 方式一：对口型面试' => '🔹 Option 1: Live Interview Coaching',
        '—— 您出镜，导师实时提供话术与思路，配合默契。' => '-- You appear on camera; mentor feeds you talking points and strategy in real time.',
        '🔹 方式二：全替出镜代面' => '🔹 Option 2: full-substitution on-camera proxy interview',
        '—— 导师全程代面，适用于面试官与岗位非同组场景。' => '-- Full-proxy interview: ideal when interviewer and role are in different orgs.',
        '$500 起' => 'From $500',
        '🚀 全套包过套餐' => '🚀 Full pass guarantee package',
        '从' => 'From',
        '到' => 'To',
        'VO面试' => 'VO Interview',
        '再到' => 'Next',
        '薪资谈判' => 'Salary Negotiation',
        '，一站式陪跑直到拿下Offer。' => 'One-stop coaching until you land the Offer',
        '服务周期：拿到Offer为止，不拿Offer可持续服务' => 'Service period: until Offer secured, continued service if not.',
        '咨询报价 →' => 'Get quote →',
        '💡 所有服务均支持' => '💡 Supported for all services',
        '免费语音咨询' => 'Free voice consultation',
        '，先沟通再决策。价格因需求复杂度浮动，以最终确认为准。' => 'Communicate first, then decide. Price varies by complexity; final confirmation prevails.',
        '产品中心' => 'Products',
        '产品中心 &#8211; CSVO Support' => 'Products – CSVO Support',
        'csvosupport 服务产品中心' => 'csvosupport Products',
        '把老站的服务内容拆成可管理的产品卡片，保留 csvosupport 简洁、可信、转化导向的展示方式。用户不在线支付，先提交需求，再由顾问人工沟通。' => 'Break down the old site\'s services into manageable product cards while retaining csvosupport\'s clean, trustworthy, conversion-oriented presentation. Users don\'t pay online; they submit requirements first, then consultants follow up manually.',
        '服务产品' => 'Services',
        '不走购物车' => 'Direct checkout',
        '后台可编辑' => 'Editable via admin panel',
        '选择需要咨询的服务方向' => 'Select the service you need',
        'OA、VO、Mock Interview、简历与 BQ 梳理都可以作为独立产品维护。每个产品都支持图片、参数、卖点、详情和询盘记录。' => 'OA, VO, Mock Interview, resume polish, and BQ prep can all be maintained as standalone products. Each product supports images, parameters, selling points, details, and inquiry records.',
        '全部服务' => 'All Services',
        '面向 CodeSignal / HackerRank / OA 流程' => 'For CodeSignal / HackerRank / OA process',
        'OA Online Assessment 支持' => 'OA Online Assessment support',
        '用于展示 OA 相关咨询、题型梳理、时间安排和需求收集，不走在线支付。' => 'For showcasing OA inquiries, problem type overview, scheduling, and requirement collection — no online payment involved.',
        '按题目/场次评估' => 'Per Problem / Session',
        '周期' => 'Period',
        '提交需求后报价' => 'Quote After Request',
        '报价' => 'Pricing',
        '查看方案' => 'View Plans',
        '提交询盘' => 'Submit Inquiry',
        'Coding / System Design / BQ 综合准备' => 'Coding / System Design / BQ comprehensive prep',
        'VO Virtual Onsite 面试支持' => 'VO Virtual Onsite support',
        '为多轮 VO 面试准备展示服务范围，适配 Coding、系统设计和行为面试需求。' => 'Service scope for multi-round VO prep, tailored for Coding, System Design, and Behavioral interviews.',
        '按轮次评估' => 'Evaluate by round',
        '按轮次确认' => 'Confirm by round',
        '算法、系统设计、BQ 模拟练习' => 'Algorithms, System Design, BQ mock practice',
        'Mock Interview 面试辅导' => 'Mock Interview Coaching',
        '适合在面试前进行模拟、反馈和薄弱点整理。' => 'Mock interviews, feedback, and weak-spot review before your interview.',
        '按小时/套餐' => 'Hourly / Package',
        '项目经历、STAR 故事线、岗位匹配' => 'Project Experience, STAR Storyline, Role Targeting',
        '简历包装与 BQ 梳理' => 'Resume Polish &amp; BQ Preparation',
        '用于承接简历、项目经历和行为面试故事线梳理需求。' => 'For resume, project experience and behavioral interview story structuring.',
        '按材料评估' => 'Evaluate by materials',
        '按材料复杂度' => 'Based on complexity',
        '需要先确认适合哪种服务？' => 'Not sure which service fits you?',
        '提交目标公司、岗位、面试阶段和时间窗口，后台会保存为询盘，方便顾问跟进。' => 'Submit your target company, role, interview stage, and time window. It will be saved as an inquiry in the backend for consultants to follow up.',
        '浏览服务' => 'Browse Services',
        '返回产品中心' => 'Back to products',
        '适合正在准备 Online Assessment 的候选人，先填写目标公司、考试平台、时间窗口和题型信息。' => 'For candidates preparing for Online Assessment, first fill in target company, test platform, time window, and question type info.',
        '页面会把需求直接入库到后台询盘，方便顾问按情况人工回复。' => 'The page saves requirements directly to the backend inquiry for consultants to reply manually.',
        '适用阶段' => 'Applicable Stage',
        '平台' => 'Platform',
        '远程确认' => 'Remote Confirmation',
        '沟通方式' => 'Communication Method',
        '先收集公司、岗位、平台、考试时间等关键信息。' => 'First gather key details: company, role, platform, exam schedule.',
        '后台可查看完整询盘和来源页面。' => 'View full inquiries and source pages in backend',
        '适合做成转化入口，不需要购物车。' => 'Best as conversion entry, no cart needed',
        '提交服务需求' => 'Submit request',
        '请留下服务方向、目标公司、时间窗口和联系方式。表单会进入后台“服务询盘”。' => 'Leave your service focus, target company, time window, and contact info. The form will be saved as a service inquiry in the backend.',
        '产品' => 'Service',
        '姓名 / 称呼' => 'Name / Title',
        '国家 / 地区' => 'Country / Region',
        '微信 / WhatsApp' => 'WeChat / WhatsApp',
        '面试阶段' => 'Interview Stage',
        '目标公司 / 岗位' => 'Target company / role',
        '时间窗口' => 'Time Window',
        '需求说明' => 'Requirements',
        '提交需求' => 'Submit Requirements',
        '其他可咨询服务' => 'Other Services',
        '例如：本周 / 下周一 / 待确认' => 'e.g. this week / next Mon / TBD',
        '请填写目标公司、岗位、面试平台、轮次、时间窗口和主要需求。' => 'Please fill in target company, position, interview platform, round, time window, and main requirements.',
        'Mock Interview 产品用于承接准备面试的用户，展示模拟面试、讲解反馈和复盘服务。' => 'Mock Interview product for interview-ready users, showcasing mock interviews, feedback, and review services.',
        '用户提交目标岗位和时间后，后台生成询盘记录。' => 'After users submit target role and time, backend generates an inquiry record.',
        '时长' => 'Duration',
        '形式' => 'Format',
        '内容' => 'Content',
        '复盘建议' => 'Review Suggestions',
        '反馈' => 'Feedback',
        '适合展示模拟面试服务和预约入口。' => 'Suitable for showcasing mock interview services and booking entry.',
        '用户可提交目标公司、时间和希望训练的方向。' => 'Users can submit target company, time, and desired training focus.',
        '该服务卡片适合展示简历、项目亮点、BQ 故事线和岗位匹配相关需求。' => 'This service card suits resume, project highlights, BQ storylines, and role-matching needs.',
        '后台产品字段可以继续补充更多参数和说明。' => 'Backend product fields can be extended with more parameters and descriptions.',
        '材料' => 'Materials',
        '人工梳理' => 'Manual Review',
        '方式' => 'Method',
        '岗位匹配' => 'Role Matching',
        '目标' => 'Target',
        '按需求确认' => 'Confirmed on demand',
        '输出' => 'Output',
        '适合和首页/联系页形成转化闭环。' => 'Forms a conversion loop with homepage/contact page.',
        '询盘会记录用户岗位、国家、联系方式和详细描述。' => 'Inquiry records role, country, contact info, and description.',
        'VO 详情页用于展示候选人可以提交的目标公司、轮次、面试时间、题型和薄弱环节。' => 'VO detail page shows candidate\'s target company, round, interview time, question types, and weak areas.',
        '系统会把表单记录为服务询盘，便于后续人工跟进。' => 'System records form as service inquiry for manual follow-up.',
        '轮次' => 'Round',
        '对象' => 'Target',
        '人工确认' => 'Manual confirmation',
        '响应' => 'Response',
        '按需求定制' => 'Customized on demand',
        '交付' => 'Delivery',
        '支持按公司和轮次拆解服务展示。' => 'Supports service breakdown by company and round.',
        '详情页包含参数、卖点、图片和询盘表单。' => 'Detail page includes specs, highlights, images, and inquiry form.',
        '后台可持续新增不同公司/岗位服务包。' => 'Continuously add new service packages for different companies/roles in the backend.',
    );
    return $d;
}

function csop_hf_tr_dict_tw() {
    static $d = null;
    if ($d !== null) return $d;
    $d = array(
        '首页-NEW' => '首頁-NEW',
        '菜单' => '菜單',
        '🏚 网站首页' => '🏚 網站首頁',
        '📖 面试真题' => '📖 面試真題',
        '⏺ 关于我们' => '⏺ 關於我們',
        '💰 服务&amp;价格' => '💰 服務&amp;價格',
        '📬 联系学长' => '📬 聯繫學長',
        '简体中文' => '簡體中文',
        'csvosupport 工作室｜OA 代写｜VO 代面｜面试辅导' => 'csvosupport 工作室｜OA 代寫｜VO 代面｜面試輔導',
        '靠谱面试辅助服务' => '靠譜面試輔助服務',
        '给你最优质的海外求职辅助' => '給你最優質的海外求職輔助',
        'csvosupport团队成员包括来自大厂科技公司的工程师、研究人员，以及有ACM算法竞赛背景的导师，致力于提供最优质的面试辅导、OA代做、VO辅助和代面试服务。' => 'csvosupport團隊成員包括來自大廠科技公司的工程師、研究人員，以及有ACM算法競賽背景的導師，致力於提供最優質的面試輔導、OA代做、VO輔助和代面試服務。',
        '我们专注服务科技行业的求职全过程。自成立以来，我们坚持以高质量辅导和透明服务为核心，立志成为VO辅助和代面领域的领头羊。' => '我們專注服務科技行業的求職全過程。自成立以來，我們堅持以高質量輔導和透明服務為核心，立志成為VO輔助和代面領域的領頭羊。',
        '联系我们' => '聯繫我們',
        '北美最强的面试辅助团队' => '北美最強的面試輔助團隊',
        '我们凭借多年北美及海外求职实战经验，打造了业内极具口碑的OA代写、模拟面试、VO代面、面试辅助一体化方案。从笔试到视频面试，从技术细节到表达策略，我们深知大厂招聘的每一道关卡，能够为你量身定制最优解法。' => '我們憑藉多年北美及海外求職實戰經驗，打造了業內極具口碑的OA代寫、模擬面試、VO代面、面試輔助一體化方案。從筆試到視頻面試，從技術細節到表達策略，我們深知大廠招聘的每一道關卡，能夠為你量身定製最優解法。',
        '过去几年，我们已帮助数百位客户成功拿下 Amazon、Bloomberg、Pinterest、Meta、Stripe、Coinbase、DoorDash、Optiver、Citadel 等顶级公司的 Offer，不仅稳稳进入目标公司，更收获了远超行业平均水平的高额薪资。我们的客户中，不乏年薪double的工程师，也有实现职业跨越、直接跃升为senior岗位的案例。' => '過去幾年，我們已幫助數百位客戶成功拿下 Amazon、Bloomberg、Pinterest、Meta、Stripe、Coinbase、DoorDash、Optiver、Citadel 等頂級公司的 Offer，不僅穩穩進入目標公司，更收穫了遠超行業平均水平的高額薪資。我們的客戶中，不乏年薪double的工程師，也有實現職業跨越、直接躍升為senior崗位的案例。',
        '我们不是流水线服务，而是你的定制化求职伙伴：每一位客户都会配备专属顾问与技术导师，全程一对一指导，确保你的每一次OA与VO表现都能精准击中招聘方的需求。' => '我們不是流水線服務，而是你的定製化求職夥伴：每一位客戶都會配備專屬顧問與技術導師，全程一對一指導，確保你的每一次OA與VO表現都能精準擊中招聘方的需求。',
        '了解更多服务细节' => '瞭解更多服務細節',
        '服务范围' => '服務範圍',
        'OA 代做保过，竞赛大神带你满分通过' => 'OA 代做保過，競賽大神帶你滿分通過',
        '秒杀所有edge case，确保满分提交' => '秒殺所有edge case，確保滿分提交',
        '精通大厂当年最新题库，全覆盖！' => '精通大廠當年最新題庫，全覆蓋！',
        '最优解 + 高可读性，品质双重保证' => '最優解 + 高可讀性，品質雙重保證',
        'VO辅助' => 'VO輔助',
        '实时传输高质答案，0破绽，满分体验' => '實時傳輸高質答案，0破綻，滿分體驗',
        '海量辅助案例，完美品质，眼见为实' => '海量輔助案例，完美品質，眼見為實',
        '多通道传输，语音/文字同步推送答案' => '多通道傳輸，語音/文字同步推送答案',
        '真人 VO代面，客户无需出镜' => '真人 VO代面，客戶無需出鏡',
        '免费语音沟通，免费Mock展示' => '免費語音溝通，免費Mock展示',
        '代面导师人均大厂senior+在职' => '代面導師人均大廠senior+在職',
        '稳OFFER，不只是说说而已' => '穩OFFER，不只是說說而已',
        '简历润色' => '簡歷潤色',
        '直击工业技术栈，拒绝toy project' => '直擊工業技術棧，拒絕toy project',
        '100%原创，围绕客户经历量身定做' => '100%原創，圍繞客戶經歷量身定做',
        '技术深度挖掘，与招聘JD无缝匹配' => '技術深度挖掘，與招聘JD無縫匹配',
        '大厂视角审阅，HR + HM 二次优化' => '大廠視角審閱，HR + HM 二次優化',
        '面试辅导' => '面試輔導',
        '大厂面试官一对一辅导，全链路覆盖' => '大廠面試官一對一輔導，全鏈路覆蓋',
        '真题实战演练，模拟当下最热考点' => '真題實戰演練，模擬當下最熱考點',
        '个性化刷题路线，传授独家技巧' => '個性化刷題路線，傳授獨家技巧',
        '面试官思维建模，教你识别提问意图' => '面試官思維建模，教你識別提問意圖',
        '我们有哪些优势？' => '我們有哪些優勢？',
        '代面现场录音供您参考，所见即所得' => '代面現場錄音供您參考，所見即所得',
        '我们的操作成功率及其高，能完全保证您的隐私' => '我們的操作成功率及其高，能完全保證您的隱私',
        '服务范围 – 专注求职面试' => '服務範圍 – 專注求職面試',
        'OA代写' => 'OA代寫',
        '代面试' => '代面試',
        '面试辅助' => '面試輔助',
        'SDE代面试' => 'SDE代面試',
        'MLE代面试' => 'MLE代面試',
        '系统设计代面试' => '系統設計代面試',
        '面试Mock' => '面試Mock',
        '面经分享' => '面經分享',
        'HackerRank代写' => 'HackerRank代寫',
        'Amazon代面试' => 'Amazon代面試',
        '亚麻辅助' => '亞麻輔助',
        'Meta代面试' => 'Meta代面試',
        'Pinterest代面试' => 'Pinterest代面試',
        'Bloomberg代面试' => 'Bloomberg代面試',
        'Uber代面试' => 'Uber代面試',
        'Optiver代面试' => 'Optiver代面試',
        'Stripe代面试' => 'Stripe代面試',
        'Snowflake代做面试' => 'Snowflake代做面試',
        'Atlassian面试辅助' => 'Atlassian面試輔助',
        '北美大厂代面试' => '北美大廠代面試',
        'Coderpad代面试' => 'Coderpad代面試',
        '技术面试辅助' => '技術面試輔助',
        '北美求职辅导' => '北美求職輔導',
        '远程面试辅助' => '遠程面試輔助',
        '硅谷代面试' => '硅谷代面試',
        '美国面试辅导' => '美國面試輔導',
        'mock面试' => 'mock面試',
        '模拟面试' => '模擬面試',
        'BO辅导' => 'BO輔導',
        '算法辅导' => '算法輔導',
        '系统设计辅导' => '系統設計輔導',
        'SDE培训' => 'SDE培訓',
        'MLE培训' => 'MLE培訓',
        '为什么选择我们' => '為什麼選擇我們',
        '「业内口碑见证」' => '「業內口碑見證」',
        '✓ 8年专注面试辅助，累计服务客户1000+' => '✓ 8年專注面試輔助，累計服務客戶1000+',
        '✓ 免费语音咨询，先沟通后决策，0风险' => '✓ 免費語音諮詢，先溝通後決策，0風險',
        '✓ 真实成功案例可查，支持现场demo演示' => '✓ 真實成功案例可查，支持現場demo演示',
        '「严选导师团队」' => '「嚴選導師團隊」',
        '✓ 导师均来自大厂在职/资深工程师背景' => '✓ 導師均來自大廠在職/資深工程師背景',
        '✓ 覆盖SDE、MLE、Data、System Design等方向' => '✓ 覆蓋SDE、MLE、Data、System Design等方向',
        '✓ 学术+职业履历全透明，支持背景核验' => '✓ 學術+職業履歷全透明，支持背景核驗',
        '「诚信服务保障」' => '「誠信服務保障」',
        '✓ 客户信息严格保密，服务全程加密' => '✓ 客戶信息嚴格保密，服務全程加密',
        '✓ 交付不满意可协商，不敷衍不推诿' => '✓ 交付不滿意可協商，不敷衍不推諉',
        '✓ 服务完成不代表结束，长期售后支持' => '✓ 服務完成不代表結束，長期售後支持',
        '客户评价' => '客戶評價',
        '100+ 成功案例，真实反馈见证我们的专业实力' => '100+ 成功案例，真實反饋見證我們的專業實力',
        '真实案例' => '真實案例',
        'OA通过案例' => 'OA通過案例',
        'VO通过案例' => 'VO通過案例',
        '真实案例 1' => '真實案例 1',
        '真实案例 2' => '真實案例 2',
        '真实案例 3' => '真實案例 3',
        '客户评价 1' => '客戶評價 1',
        '客户评价 2' => '客戶評價 2',
        '客户评价 3' => '客戶評價 3',
        '客户评价 4' => '客戶評價 4',
        '客户评价 5' => '客戶評價 5',
        '客户评价 6' => '客戶評價 6',
        '客户评价 7' => '客戶評價 7',
        '客户评价 8' => '客戶評價 8',
        '微信扫码联系' => '微信掃碼聯繫',
        '💰  服务&amp;价格' => '💰  服務&amp;價格',
        '© 2026 csvosupport • 版权所有' => '© 2026 csvosupport • 版權所有',
        '真实案例 1 - csvosupport' => '真實案例 1 - csvosupport',
        '真实案例 2 - csvosupport' => '真實案例 2 - csvosupport',
        '真实案例 3 - csvosupport' => '真實案例 3 - csvosupport',
        '客户评价 1 - csvosupport' => '客戶評價 1 - csvosupport',
        '客户评价 2 - csvosupport' => '客戶評價 2 - csvosupport',
        '客户评价 3 - csvosupport' => '客戶評價 3 - csvosupport',
        '客户评价 4 - csvosupport' => '客戶評價 4 - csvosupport',
        '客户评价 5 - csvosupport' => '客戶評價 5 - csvosupport',
        '客户评价 6 - csvosupport' => '客戶評價 6 - csvosupport',
        '客户评价 7 - csvosupport' => '客戶評價 7 - csvosupport',
        '客户评价 8 - csvosupport' => '客戶評價 8 - csvosupport',
        '关于我们' => '關於我們',
        '关于我们 &#8211; CSVO Support' => '關於我們 &#8211; CSVO Support',
        '关于 csvosupport' => '關於 csvosupport',
        '成立于2022年，专注于海外科技求职面试辅导与技术支持服务。
我们坚信' => '成立於2022年，專注於海外科技求職面試輔導與技術支持服務。
我們堅信',
        '成立于2022年，专注于海外科技求职面试辅导与技术支持服务。 我们坚信' => '成立於2022年，專注於海外科技求職面試輔導與技術支持服務。 我們堅信',
        '透明、专业、有温度' => '透明、專業、有溫度',
        '的服务，能真正帮助求职者跨越面试门槛，拿下心仪Offer。' => '的服務，能真正幫助求職者跨越面試門檻，拿下心儀Offer。',
        '我们的使命' => '我們的使命',
        '从OA笔试到VO面试，从简历润色到系统设计，我们陪伴每一位客户走过求职全流程。
团队核心成员均来自一线大厂（Google、Meta、Amazon等），拥有丰富的面试官经验与算法竞赛背景。
我们不做流水线服务，而是为每一位客户匹配最合适的导师，提供' => '從OA筆試到VO面試，從簡歷潤色到系統設計，我們陪伴每一位客戶走過求職全流程。
團隊核心成員均來自一線大廠（Google、Meta、Amazon等），擁有豐富的面試官經驗與算法競賽背景。
我們不做流水線服務，而是為每一位客戶匹配最合適的導師，提供',
        '从OA笔试到VO面试，从简历润色到系统设计，我们陪伴每一位客户走过求职全流程。 团队核心成员均来自一线大厂（Google、Meta、Amazon等），拥有丰富的面试官经验与算法竞赛背景。 我们不做流水线服务，而是为每一位客户匹配最合适的导师，提供' => '從OA筆試到VO面試，從簡歷潤色到系統設計，我們陪伴每一位客戶走過求職全流程。 團隊核心成員均來自一線大廠（Google、Meta、Amazon等），擁有豐富的面試官經驗與算法競賽背景。 我們不做流水線服務，而是為每一位客戶匹配最合適的導師，提供',
        '定制化、可落地' => '定製化、可落地',
        '的面试解决方案。' => '的面試解決方案。',
        '精准匹配' => '精準匹配',
        '根据目标岗位、面试轮次匹配最合适的导师' => '根據目標崗位、面試輪次匹配最合適的導師',
        '严格保密' => '嚴格保密',
        '客户信息与服务过程全程加密，隐私零泄露' => '客戶信息與服務過程全程加密，隱私零洩露',
        '透明沟通' => '透明溝通',
        '免费语音咨询，先沟通再决策，0风险' => '免費語音諮詢，先溝通再決策，0風險',
        '核心团队' => '核心團隊',
        '团队成员均来自大厂在职或资深工程师背景，100%真实可核验。' => '團隊成員均來自大廠在職或資深工程師背景，100%真實可核驗。',
        '创始人 · 首席面试顾问' => '創始人 · 首席面試顧問',
        '前Google Engineer，3年+大厂经验，ACM EC FINAL银牌，辅导800+学员进入北美大厂。' => '前Google Engineer，3年+大廠經驗，ACM EC FINAL銀牌，輔導800+學員進入北美大廠。',
        '技术导师 · System Design' => '技術導師 · System Design',
        'Amazon Senior SDE，专注分布式系统与架构设计，擅长系统设计面试辅导与实战模拟。' => 'Amazon Senior SDE，專注分佈式系統與架構設計，擅長系統設計面試輔導與實戰模擬。',
        '技术导师 · Algorithm & MLE' => '技術導師 · Algorithm & MLE',
        'Meta Research Scientist，ML/NLP方向博士，LeetCode全球Top 200，精通算法与机器学习面试。' => 'Meta Research Scientist，ML/NLP方向博士，LeetCode全球Top 200，精通算法與機器學習面試。',
        '面试辅导 · BQ & Resume' => '面試輔導 · BQ & Resume',
        '前Google HRBP，6年+招聘经验，擅长行为面试辅导、简历优化与职业规划咨询。' => '前Google HRBP，6年+招聘經驗，擅長行為面試輔導、簡歷優化與職業規劃諮詢。',
        '服务客户' => '服務客戶',
        '行业深耕（年）' => '行業深耕（年）',
        '客户满意度' => '客戶滿意度',
        '覆盖大厂数量' => '覆蓋大廠數量',
        '" 我们不是中介，是你求职路上的' => '" 我們不是中介，是你求職路上的',
        '技术伙伴' => '技術夥伴',
        '用真实的能力，帮你拿到真实的Offer。"' => '用真實的能力，幫你拿到真實的Offer。"',
        '—— csvosupport 团队' => '—— csvosupport 團隊',
        '—— 微信扫码联系 ——' => '—— 微信掃碼聯繫 ——',
        '📌 添加时请注明您的面试/作业需求，以便我们快速评估' => '📌 添加時請註明您的面試/作業需求，以便我們快速評估',
        '✅ 100% 代码保证唯一' => '✅ 100% 代碼保證唯一',
        '✅ 100% 客户信息保密' => '✅ 100% 客戶信息保密',
        '✅ 100% 服务质量保障' => '✅ 100% 服務質量保障',
        '面试真题' => '面試真題',
        '面试真题 &#8211; CSVO Support' => '面試真題 &#8211; CSVO Support',
        '发布日期: 26 June, 2026' => '發佈日期: 26 June, 2026',
        'Databricks SWE OA 面经｜SQL 执行图、日志窗口与 AI Coding 题型解析' => 'Databricks SWE OA 面經｜SQL 執行圖、日誌窗口與 AI Coding 題型解析',
        'Databricks SWE OA 面经复盘，覆盖日志窗口聚合、SQL 执行依赖图、AI Coding 修复分区读取器，以及备考重点。' => 'Databricks SWE OA 面經覆盤，覆蓋日誌窗口聚合、SQL 執行依賴圖、AI Coding 修復分區讀取器，以及備考重點。',
        '阅读更多' => '閱讀更多',
        '发布日期: 25 June, 2026' => '發佈日期: 25 June, 2026',
        'Google SWE VO 面经｜Virtual Onsite Coding + System Design + BQ 全流程复盘' => 'Google SWE VO 面經｜Virtual Onsite Coding + System Design + BQ 全流程覆盤',
        'Google SWE VO 面经复盘，覆盖 Virtual Onsite Coding、系统设计、Googliness/BQ、项目深挖和备考建议。' => 'Google SWE VO 面經覆盤，覆蓋 Virtual Onsite Coding、系統設計、Googliness/BQ、項目深挖和備考建議。',
        'Apple SWE VO 面经｜Coding、项目深挖、系统设计与 BQ 复盘' => 'Apple SWE VO 面經｜Coding、項目深挖、系統設計與 BQ 覆盤',
        'Apple SWE VO 面经复盘，覆盖数组事件合并、权限与缓存追问、照片同步系统设计、项目深挖和 BQ 准备。' => 'Apple SWE VO 面經覆盤，覆蓋數組事件合併、權限與緩存追問、照片同步系統設計、項目深挖和 BQ 準備。',
        'Microsoft SWE VO 面经｜Coding + 代码设计 + System Design + BQ 全流程复盘' => 'Microsoft SWE VO 面經｜Coding + 代碼設計 + System Design + BQ 全流程覆盤',
        'Microsoft SWE VO 面经复盘，覆盖区间 Coding、带 TTL 的 Key-Value Store、Teams 在线状态系统设计、HM Round 和 BQ 准备建议。' => 'Microsoft SWE VO 面經覆盤，覆蓋區間 Coding、帶 TTL 的 Key-Value Store、Teams 在線狀態系統設計、HM Round 和 BQ 準備建議。',
        '发布日期: 23 June, 2026' => '發佈日期: 23 June, 2026',
        'Meta SWE OA 面经｜CodeSignal 技术筛选 + AI Coding 题型解析' => 'Meta SWE OA 面經｜CodeSignal 技術篩選 + AI Coding 題型解析',
        'Meta SWE OA 面经复盘，覆盖 CodeSignal 技术筛选、AI Coding 校验、文件系统、BFS、滑动窗口题型和备考建议。' => 'Meta SWE OA 面經覆盤，覆蓋 CodeSignal 技術篩選、AI Coding 校驗、文件系統、BFS、滑動窗口題型和備考建議。',
        '发布日期: 22 June, 2026' => '發佈日期: 22 June, 2026',
        'Snowflake SWE VO 面经｜Coding + 数据系统设计 + 项目深挖 + BQ 全流程复盘' => 'Snowflake SWE VO 面經｜Coding + 數據系統設計 + 項目深挖 + BQ 全流程覆盤',
        'Snowflake SWE VO 面经复盘，覆盖 Coding、SQL parser、query history system design、HM Round/BQ 与备考建议。' => 'Snowflake SWE VO 面經覆盤，覆蓋 Coding、SQL parser、query history system design、HM Round/BQ 與備考建議。',
        '发布日期: 20 June, 2026' => '發佈日期: 20 June, 2026',
        'Google VO 两轮面经｜Coding 与 Googleyness 项目深挖复盘' => 'Google VO 兩輪面經｜Coding 與 Googleyness 項目深挖覆盤',
        'Google VO 两轮面经：复盘第一面 Coding、第二面 Googleyness 与项目深挖，整理算法沟通、边界测试、行为问题和准备重点。' => 'Google VO 兩輪面經：覆盤第一面 Coding、第二面 Googleyness 與項目深挖，整理算法溝通、邊界測試、行為問題和準備重點。',
        'Google NG 两轮 VO 真实复盘｜谷歌面试官太会“挖”了！' => 'Google NG 兩輪 VO 真實覆盤｜谷歌面試官太會“挖”了！',
        '刚带完一位学员拿下 Google ng VO 双轮全过，这场面试可以说把“基础+表达”考到了极致。没有花里胡哨的题，全是细节的考察。我们当天' => '剛帶完一位學員拿下 Google ng VO 雙輪全過，這場面試可以說把“基礎+表達”考到了極致。沒有花裡胡哨的題，全是細節的考察。我們當天',
        '发布日期: 21 June, 2026' => '發佈日期: 21 June, 2026',
        'Stripe VO 5轮面试分享 SDE的难度挺大的' => 'Stripe VO 5輪面試分享 SDE的難度挺大的',
        'InterviewAid团队专注技术求职辅导多年 ，我们助力无数学员实现职业蜕变。学员 L 曾是饱受加班与低薪困扰的会计，因向往 tech' => 'InterviewAid團隊專注技術求職輔導多年 ，我們助力無數學員實現職業蛻變。學員 L 曾是飽受加班與低薪困擾的會計，因嚮往 tech',
        '-------微信二维码↑-----' => '-------微信二維碼↑-----',
        '为了保证我尽快联系和评估您的面试，作业, 请注明您的面试，作业具体要求' => '為了保證我儘快聯繫和評估您的面試，作業, 請註明您的面試，作業具體要求',
        '100% Plagiarism Free 代码保证唯一' => '100% Plagiarism Free 代碼保證唯一',
        '100% Quality Assurance 保证质量' => '100% Quality Assurance 保證質量',
        'More on Google NG 两轮 VO 真实复盘｜谷歌面试官太会“挖”了！' => 'More on Google NG 兩輪 VO 真實覆盤｜谷歌面試官太會“挖”了！',
        'More on Stripe VO 5轮面试分享 SDE的难度挺大的' => 'More on Stripe VO 5輪面試分享 SDE的難度挺大的',
        '联系学长' => '聯繫學長',
        '联系学长 &#8211; CSVO Support' => '聯繫學長 &#8211; CSVO Support',
        '联系学长 Contact Us' => '聯繫學長 Contact Us',
        '有需要随时联系我，面试代面，考试，作业，代写OA' => '有需要隨時聯繫我，面試代面，考試，作業，代寫OA',
        '&nbsp;为了保证我们尽快联系和评估您的需求，添加时请注明您的面试、笔试具体要求' => '&nbsp;為了保證我們儘快聯繫和評估您的需求，添加時請註明您的面試、筆試具體要求',
        '&nbsp;注意:&nbsp;全年无休，24小时响应' => '&nbsp;注意:&nbsp;全年無休，24小時響應',
        '服务&#038;价格' => '服務&#038;價格',
        '服务&#038;价格 &#8211; CSVO Support' => '服務&#038;價格 &#8211; CSVO Support',
        '服务与价格' => '服務與價格',
        '我们提供' => '我們提供',
        '透明、按需定制' => '透明、按需定製',
        '的服务报价。所有服务均先沟通需求、确认方案后报价，' => '的服務報價。所有服務均先溝通需求、確認方案後報價，',
        '不满意不收费' => '不滿意不收費',
        'OA辅助服务' => 'OA輔助服務',
        '热门' => '熱門',
        '专业提供' => '專業提供',
        '在线评测（OA）代写服务' => '在線評測（OA）代寫服務',
        '，确保所有测试用例 100% 通过，不通过不收费。' => '，確保所有測試用例 100% 通過，不通過不收費。',
        '🔹 服务方式：' => '🔹 服務方式：',
        '通过远程控制软件（ToDesk / TeamViewer）无痕操作，适配 HackerRank、CodeSignal 等主流平台。' => '通過遠程控制軟件（ToDesk / TeamViewer）無痕操作，適配 HackerRank、CodeSignal 等主流平臺。',
        '咨询详情 →' => '諮詢詳情 →',
        '面试辅助（VO辅助）' => '面試輔助（VO輔助）',
        '明星服务' => '明星服務',
        '大厂在职工程师为您提供' => '大廠在職工程師為您提供',
        '实时提示与思路' => '實時提示與思路',
        '，效果远超AI辅助，助您在VO面试中脱颖而出。' => '，效果遠超AI輔助，助您在VO面試中脫穎而出。',
        '🔹 覆盖内容：' => '🔹 覆蓋內容：',
        'BQ问题 · Coding · Follow-up · 项目深挖 · 技术八股 · System Design' => 'BQ問題 · Coding · Follow-up · 項目深挖 · 技術八股 · System Design',
        '代面试服务' => '代面試服務',
        '高端定制' => '高端定製',
        '通过' => '通過',
        '摄像头转接 + 变声技术' => '攝像頭轉接 + 變聲技術',
        '，由资深导师全程代面，助您直通Offer。' => '，由資深導師全程代面，助您直通Offer。',
        '🔹 方式一：对口型面试' => '🔹 方式一：對口型面試',
        '—— 您出镜，导师实时提供话术与思路，配合默契。' => '—— 您出鏡，導師實時提供話術與思路，配合默契。',
        '🔹 方式二：全替出镜代面' => '🔹 方式二：全替出鏡代面',
        '—— 导师全程代面，适用于面试官与岗位非同组场景。' => '—— 導師全程代面，適用於面試官與崗位非同組場景。',
        '🚀 全套包过套餐' => '🚀 全套包過套餐',
        '从' => '從',
        'VO面试' => 'VO面試',
        '薪资谈判' => '薪資談判',
        '服务周期：拿到Offer为止，不拿Offer可持续服务' => '服務週期：拿到Offer為止，不拿Offer可持續服務',
        '咨询报价 →' => '諮詢報價 →',
        '💡 所有服务均支持' => '💡 所有服務均支持',
        '免费语音咨询' => '免費語音諮詢',
        '，先沟通再决策。价格因需求复杂度浮动，以最终确认为准。' => '，先溝通再決策。價格因需求複雜度浮動，以最終確認為準。',
        '产品中心' => '產品中心',
        '产品中心 &#8211; CSVO Support' => '產品中心 &#8211; CSVO Support',
        'csvosupport 服务产品中心' => 'csvosupport 服務產品中心',
        '把老站的服务内容拆成可管理的产品卡片，保留 csvosupport 简洁、可信、转化导向的展示方式。用户不在线支付，先提交需求，再由顾问人工沟通。' => '把老站的服務內容拆成可管理的產品卡片，保留 csvosupport 簡潔、可信、轉化導向的展示方式。用戶不在線支付，先提交需求，再由顧問人工溝通。',
        '服务产品' => '服務產品',
        '不走购物车' => '不走購物車',
        '后台可编辑' => '後臺可編輯',
        '选择需要咨询的服务方向' => '選擇需要諮詢的服務方向',
        'OA、VO、Mock Interview、简历与 BQ 梳理都可以作为独立产品维护。每个产品都支持图片、参数、卖点、详情和询盘记录。' => 'OA、VO、Mock Interview、簡歷與 BQ 梳理都可以作為獨立產品維護。每個產品都支持圖片、參數、賣點、詳情和詢盤記錄。',
        '全部服务' => '全部服務',
        '用于展示 OA 相关咨询、题型梳理、时间安排和需求收集，不走在线支付。' => '用於展示 OA 相關諮詢、題型梳理、時間安排和需求收集，不走在線支付。',
        '按题目/场次评估' => '按題目/場次評估',
        '周期' => '週期',
        '提交需求后报价' => '提交需求後報價',
        '报价' => '報價',
        '提交询盘' => '提交詢盤',
        'Coding / System Design / BQ 综合准备' => 'Coding / System Design / BQ 綜合準備',
        'VO Virtual Onsite 面试支持' => 'VO Virtual Onsite 面試支持',
        '为多轮 VO 面试准备展示服务范围，适配 Coding、系统设计和行为面试需求。' => '為多輪 VO 面試準備展示服務範圍，適配 Coding、系統設計和行為面試需求。',
        '按轮次评估' => '按輪次評估',
        '按轮次确认' => '按輪次確認',
        '算法、系统设计、BQ 模拟练习' => '算法、系統設計、BQ 模擬練習',
        'Mock Interview 面试辅导' => 'Mock Interview 面試輔導',
        '适合在面试前进行模拟、反馈和薄弱点整理。' => '適合在面試前進行模擬、反饋和薄弱點整理。',
        '按小时/套餐' => '按小時/套餐',
        '项目经历、STAR 故事线、岗位匹配' => '項目經歷、STAR 故事線、崗位匹配',
        '简历包装与 BQ 梳理' => '簡歷包裝與 BQ 梳理',
        '用于承接简历、项目经历和行为面试故事线梳理需求。' => '用於承接簡歷、項目經歷和行為面試故事線梳理需求。',
        '按材料评估' => '按材料評估',
        '按材料复杂度' => '按材料複雜度',
        '需要先确认适合哪种服务？' => '需要先確認適合哪種服務？',
        '提交目标公司、岗位、面试阶段和时间窗口，后台会保存为询盘，方便顾问跟进。' => '提交目標公司、崗位、面試階段和時間窗口，後臺會保存為詢盤，方便顧問跟進。',
        '浏览服务' => '瀏覽服務',
        '返回产品中心' => '返回產品中心',
        '适合正在准备 Online Assessment 的候选人，先填写目标公司、考试平台、时间窗口和题型信息。' => '適合正在準備 Online Assessment 的候選人，先填寫目標公司、考試平臺、時間窗口和題型信息。',
        '页面会把需求直接入库到后台询盘，方便顾问按情况人工回复。' => '頁面會把需求直接入庫到後臺詢盤，方便顧問按情況人工回覆。',
        '适用阶段' => '適用階段',
        '平台' => '平臺',
        '远程确认' => '遠程確認',
        '沟通方式' => '溝通方式',
        '先收集公司、岗位、平台、考试时间等关键信息。' => '先收集公司、崗位、平臺、考試時間等關鍵信息。',
        '后台可查看完整询盘和来源页面。' => '後臺可查看完整詢盤和來源頁面。',
        '适合做成转化入口，不需要购物车。' => '適合做成轉化入口，不需要購物車。',
        '提交服务需求' => '提交服務需求',
        '请留下服务方向、目标公司、时间窗口和联系方式。表单会进入后台“服务询盘”。' => '請留下服務方向、目標公司、時間窗口和聯繫方式。表單會進入後臺“服務詢盤”。',
        '产品' => '產品',
        '姓名 / 称呼' => '姓名 / 稱呼',
        '国家 / 地区' => '國家 / 地區',
        '面试阶段' => '面試階段',
        '目标公司 / 岗位' => '目標公司 / 崗位',
        '时间窗口' => '時間窗口',
        '需求说明' => '需求說明',
        '其他可咨询服务' => '其他可諮詢服務',
        '例如：本周 / 下周一 / 待确认' => '例如：本週 / 下週一 / 待確認',
        '请填写目标公司、岗位、面试平台、轮次、时间窗口和主要需求。' => '請填寫目標公司、崗位、面試平臺、輪次、時間窗口和主要需求。',
        '页' => '頁',
        '单' => '單',
        '网' => '網',
        '试' => '試',
        '题' => '題',
        '关' => '關',
        '于' => '於',
        '们' => '們',
        '务' => '務',
        '价' => '價',
        '联' => '聯',
        '学' => '學',
        '长' => '長',
        '简' => '簡',
        '体' => '體',
        '写' => '寫',
        '辅' => '輔',
        '导' => '導',
        '谱' => '譜',
        '给' => '給',
        '优' => '優',
        '质' => '質',
        '职' => '職',
        '团' => '團',
        '队' => '隊',
        '员' => '員',
        '来' => '來',
        '厂' => '廠',
        '师' => '師',
        '竞' => '競',
        '赛' => '賽',
        '专' => '專',
        '业' => '業',
        '过' => '過',
        '坚' => '堅',
        '为' => '為',
        '领' => '領',
        '头' => '頭',
        '强' => '強',
        '凭' => '憑',
        '实' => '實',
        '战' => '戰',
        '经' => '經',
        '验' => '驗',
        '内' => '內',
        '极' => '極',
        '拟' => '擬',
        '笔' => '筆',
        '视' => '視',
        '频' => '頻',
        '术' => '術',
        '细' => '細',
        '节' => '節',
        '达' => '達',
        '够' => '夠',
        '几' => '幾',
        '帮' => '幫',
        '数' => '數',
        '户' => '戶',
        '顶' => '頂',
        '级' => '級',
        '仅' => '僅',
        '稳' => '穩',
        '进' => '進',
        '标' => '標',
        '获' => '獲',
        '远' => '遠',
        '额' => '額',
        '资' => '資',
        '现' => '現',
        '跃' => '躍',
        '岗' => '崗',
        '线' => '線',
        '伙' => '夥',
        '会' => '會',
        '备' => '備',
        '属' => '屬',
        '顾' => '顧',
        '问' => '問',
        '与' => '與',
        '对' => '對',
        '确' => '確',
        '准' => '準',
        '击' => '擊',
        '范' => '範',
        '围' => '圍',
        '带' => '帶',
        '满' => '滿',
        '杀' => '殺',
        '当' => '當',
        '库' => '庫',
        '盖' => '蓋',
        '读' => '讀',
        '双' => '雙',
        '证' => '證',
        '时' => '時',
        '传' => '傳',
        '输' => '輸',
        '绽' => '綻',
        '见' => '見',
        '语' => '語',
        '无' => '無',
        '镜' => '鏡',
        '费' => '費',
        '沟' => '溝',
        '说' => '說',
        '历' => '歷',
        '润' => '潤',
        '栈' => '棧',
        '绝' => '絕',
        '创' => '創',
        '绕' => '繞',
        '缝' => '縫',
        '审' => '審',
        '阅' => '閱',
        '链' => '鏈',
        '练' => '練',
        '热' => '熱',
        '点' => '點',
        '个' => '個',
        '独' => '獨',
        '维' => '維',
        '识' => '識',
        '别' => '別',
        '图' => '圖',
        '势' => '勢',
        '场' => '場',
        '录' => '錄',
        '参' => '參',
        '隐' => '隱',
        '组' => '組',
        '统' => '統',
        '设' => '設',
        '计' => '計',
        '亚' => '亞',
        '国' => '國',
        '训' => '訓',
        '么' => '麼',
        '选' => '選',
        '择' => '擇',
        '键' => '鍵',
        '咨' => '諮',
        '询' => '詢',
        '后' => '後',
        '决' => '決',
        '风' => '風',
        '险' => '險',
        '严' => '嚴',
        '诚' => '誠',
        '协' => '協',
        '诿' => '諉',
        '结' => '結',
        '评' => '評',
        '馈' => '饋',
        '扫' => '掃',
        '码' => '碼',
        '权' => '權',
        '绍' => '紹',
        '块' => '塊',
        '温' => '溫',
        '门' => '門',
        '槛' => '檻',
        '仪' => '儀',
        '拥' => '擁',
        '丰' => '豐',
        '适' => '適',
        '据' => '據',
        '轮' => '輪',
        '泄' => '洩',
        '银' => '銀',
        '构' => '構',
        '机' => '機',
        '习' => '習',
        '规' => '規',
        '划' => '劃',
        '条' => '條',
        '侧' => '側',
        '边' => '邊',
        '栏' => '欄',
        '请' => '請',
        '执' => '執',
        '发' => '發',
        '复' => '復',
        '盘' => '盤',
        '赖' => '賴',
        '区' => '區',
        '项' => '項',
        '议' => '議',
        '并' => '並',
        '缓' => '緩',
        '间' => '間',
        '状' => '狀',
        '态' => '態',
        '筛' => '篩',
        '动' => '動',
        '两' => '兩',
        '测' => '測',
        '刚' => '剛',
        '这' => '這',
        '础' => '礎',
        '没' => '沒',
        '里' => '裡',
        '难' => '難',
        '蜕' => '蛻',
        '变' => '變',
        '饱' => '飽',
        '扰' => '擾',
        '尽' => '盡',
        '随' => '隨',
        '响' => '響',
        '应' => '應',
        '报' => '報',
        '认' => '認',
        '软' => '軟',
        '台' => '臺',
        '详' => '詳',
        '脱' => '脫',
        '颖' => '穎',
        '摄' => '攝',
        '转' => '轉',
        '声' => '聲',
        '话' => '話',
        '谈' => '談',
        '续' => '續',
        '杂' => '雜',
        '终' => '終',
        '产' => '產',
        '洁' => '潔',
        '购' => '購',
        '车' => '車',
        '编' => '編',
        '辑' => '輯',
        '护' => '護',
        '卖' => '賣',
        '记' => '記',
        '综' => '綜',
        '装' => '裝',
        '种' => '種',
        '阶' => '階',
        '浏' => '瀏',
        '览' => '覽',
        '况' => '況',
        '称' => '稱',
        'Mock Interview 产品用于承接准备面试的用户，展示模拟面试、讲解反馈和复盘服务。' => 'Mock Interview 產品用於承接準備面試的用戶，展示模擬面試、講解反饋和覆盤服務。',
        '用户提交目标岗位和时间后，后台生成询盘记录。' => '用戶提交目標崗位和時間後，後臺生成詢盤記錄。',
        '时长' => '時長',
        '内容' => '內容',
        '复盘建议' => '覆盤建議',
        '反馈' => '反饋',
        '适合展示模拟面试服务和预约入口。' => '適合展示模擬面試服務和預約入口。',
        '用户可提交目标公司、时间和希望训练的方向。' => '用戶可提交目標公司、時間和希望訓練的方向。',
        '该服务卡片适合展示简历、项目亮点、BQ 故事线和岗位匹配相关需求。' => '該服務卡片適合展示簡歷、項目亮點、BQ 故事線和崗位匹配相關需求。',
        '后台产品字段可以继续补充更多参数和说明。' => '後臺產品字段可以繼續補充更多參數和說明。',
        '岗位匹配' => '崗位匹配',
        '目标' => '目標',
        '按需求确认' => '按需求確認',
        '输出' => '輸出',
        '适合和首页/联系页形成转化闭环。' => '適合和首頁/聯繫頁形成轉化閉環。',
        '询盘会记录用户岗位、国家、联系方式和详细描述。' => '詢盤會記錄用戶崗位、國家、聯繫方式和詳細描述。',
        'VO 详情页用于展示候选人可以提交的目标公司、轮次、面试时间、题型和薄弱环节。' => 'VO 詳情頁用於展示候選人可以提交的目標公司、輪次、面試時間、題型和薄弱環節。',
        '系统会把表单记录为服务询盘，便于后续人工跟进。' => '系統會把表單記錄為服務詢盤，便於後續人工跟進。',
        '轮次' => '輪次',
        '对象' => '對象',
        '人工确认' => '人工確認',
        '响应' => '響應',
        '按需求定制' => '按需求定製',
        '支持按公司和轮次拆解服务展示。' => '支持按公司和輪次拆解服務展示。',
        '详情页包含参数、卖点、图片和询盘表单。' => '詳情頁包含參數、賣點、圖片和詢盤表單。',
        '后台可持续新增不同公司/岗位服务包。' => '後臺可持續新增不同公司/崗位服務包。',
    );
    return $d;
}

function csop_hf_get_options() {
    $saved = get_option(csop_hf_option_name(), array());
    if (!is_array($saved)) $saved = array();
    return array_merge(csop_hf_defaults(), $saved);
}

function csop_hf_sanitize_options($raw) {
    $raw = is_array($raw) ? $raw : array();
    $defaults = csop_hf_defaults();
    $clean = array();

    $checkboxes = array('enabled_header', 'enabled_footer');
    $urls = array(
        'home_url',
        'logo_image',
        'qr_wechat_image',
        'qr_whatsapp_image',
        'telegram_url',
        'whatsapp_url',
        'email_url',
        'footer_contact_url',
    );
    $textareas = array('menu_items', 'language_items', 'quick_links');
    $colors = array('nav_bg', 'nav_hover_bg', 'nav_text', 'footer_bg', 'footer_border');
    $numbers = array(
        'container_width' => array(900, 1800),
        'nav_height' => array(44, 90),
    );

    foreach ($defaults as $key => $value) {
        if (in_array($key, $checkboxes, true)) {
            $clean[$key] = isset($raw[$key]) ? '1' : '0';
        } elseif (isset($numbers[$key])) {
            $min = $numbers[$key][0];
            $max = $numbers[$key][1];
            $clean[$key] = isset($raw[$key]) ? (string) max($min, min($max, intval($raw[$key]))) : $value;
        } elseif (in_array($key, $urls, true)) {
            $url_value = isset($raw[$key]) ? trim((string) wp_unslash($raw[$key])) : $value;
            $clean[$key] = csop_hf_sanitize_url_or_path($url_value);
        } elseif (in_array($key, $textareas, true)) {
            $clean[$key] = isset($raw[$key]) ? sanitize_textarea_field(wp_unslash($raw[$key])) : $value;
        } elseif (in_array($key, $colors, true)) {
            $color = isset($raw[$key]) ? sanitize_text_field(wp_unslash($raw[$key])) : $value;
            $clean[$key] = preg_match('/^(#[0-9a-f]{3,8}|rgba?\([^)]+\))$/i', $color) ? $color : $value;
        } else {
            $clean[$key] = isset($raw[$key]) ? sanitize_text_field(wp_unslash($raw[$key])) : $value;
        }
    }

    return $clean;
}

function csop_hf_sanitize_url_or_path($value) {
    $value = trim((string) $value);
    if ($value === '' || $value === '#') return $value;
    if (strpos($value, '/') === 0 && strpos($value, '//') !== 0) return esc_url_raw($value);
    if (stripos($value, 'mailto:') === 0) return sanitize_text_field($value);
    return esc_url_raw($value);
}

function csop_hf_resolve_url($url) {
    $url = trim((string) $url);
    if ($url === '') return '#';
    if ($url === '#') return '#';
    if (preg_match('/^(https?:)?\/\//i', $url) || stripos($url, 'mailto:') === 0 || stripos($url, 'tel:') === 0) {
        return $url;
    }
    if (strpos($url, '/') === 0) {
        return home_url($url);
    }
    return home_url('/' . ltrim($url, '/'));
}

function csop_hf_parse_links($text) {
    $items = array();
    $lines = preg_split('/\r\n|\r|\n/', (string) $text);

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $parts = array_map('trim', explode('|', $line));
        $label = isset($parts[0]) ? $parts[0] : '';
        $url = isset($parts[1]) ? $parts[1] : '#';
        $image = isset($parts[2]) ? $parts[2] : '';
        if ($label === '') continue;

        $items[] = array(
            'label' => $label,
            'url' => $url,
            'image' => $image,
        );
    }

    return $items;
}

function csop_hf_can_render() {
    if (is_admin()) return false;
    if (function_exists('wp_doing_ajax') && wp_doing_ajax()) return false;
    if (defined('REST_REQUEST') && REST_REQUEST) return false;
    if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) return false;
    if (is_feed() || is_trackback()) return false;
    if (function_exists('is_customize_preview') && is_customize_preview()) return false;
    global $pagenow;
    if ($pagenow === 'wp-login.php') return false;
    return true;
}

function csop_hf_current_lang() {
    static $lang = null;
    if ($lang !== null) return $lang;
    $allowed = array('zh', 'en', 'zh_tw');
    $query_lang = function_exists('get_query_var') ? get_query_var('csop_lang') : '';
    if (is_string($query_lang) && in_array($query_lang, $allowed, true)) {
        $lang = $query_lang;
        if (!headers_sent()) {
            $path = (defined('COOKIEPATH') && COOKIEPATH) ? COOKIEPATH : '/';
            setcookie('csop_lang', $lang, time() + 31536000, $path);
        }
        $_COOKIE['csop_lang'] = $lang;
        return $lang;
    }
    if (function_exists('csop_pretty_lang_from_request_path')) {
        $path_lang = csop_pretty_lang_from_request_path();
        if ($path_lang && in_array($path_lang, $allowed, true)) {
            $lang = $path_lang;
            if (!headers_sent()) {
                $path = (defined('COOKIEPATH') && COOKIEPATH) ? COOKIEPATH : '/';
                setcookie('csop_lang', $lang, time() + 31536000, $path);
            }
            $_COOKIE['csop_lang'] = $lang;
            return $lang;
        }
    }
    if (isset($_GET['csop_lang']) && in_array($_GET['csop_lang'], $allowed, true)) {
        $lang = $_GET['csop_lang'];
        if (!headers_sent()) {
            $path = (defined('COOKIEPATH') && COOKIEPATH) ? COOKIEPATH : '/';
            setcookie('csop_lang', $lang, time() + 31536000, $path);
        }
        $_COOKIE['csop_lang'] = $lang;
        return $lang;
    }
    if (function_exists('csop_pretty_is_default_zh_request_path') && csop_pretty_is_default_zh_request_path()) {
        $lang = 'zh';
        if (!headers_sent()) {
            $path = (defined('COOKIEPATH') && COOKIEPATH) ? COOKIEPATH : '/';
            setcookie('csop_lang', $lang, time() + 31536000, $path);
        }
        $_COOKIE['csop_lang'] = $lang;
        return $lang;
    }
    if (isset($_COOKIE['csop_lang']) && in_array($_COOKIE['csop_lang'], $allowed, true)) {
        return $lang = $_COOKIE['csop_lang'];
    }
    return $lang = 'zh';
}

function csop_hf_lang_code_from_url($url) {
    if (strpos((string) $url, 'zh_tw') !== false) return 'zh_tw';
    if (preg_match('#/en(/|$)#', (string) $url)) return 'en';
    return 'zh';
}

function csop_hf_lang_switch_url($code) {
    $uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
    if (function_exists('csop_ml_lang_url')) {
        return esc_url(csop_ml_lang_url(home_url($uri), $code));
    }
    return esc_url(add_query_arg('csop_lang', $code, $uri));
}

function csop_hf_translate_output($html) {
    $lang = csop_hf_current_lang();
    if ($lang === 'zh') return $html;
    $dict = ($lang === 'en') ? csop_hf_tr_dict_en() : csop_hf_tr_dict_tw();
    if (empty($dict)) return $html;
    $is_tw = ($lang === 'zh_tw');
    $normalized_dict = array();
    foreach ($dict as $source => $target) {
        $normalized_source = preg_replace('/\s+/u', ' ', trim((string) $source));
        if ($normalized_source !== '') {
            $normalized_dict[$normalized_source] = $target;
        }
    }

    // Protect script/style/noscript/template blocks from translation.
    $protected = array();
    $html = preg_replace_callback('#<(script|style|noscript|template)\b[^>]*>.*?</\1>#is', function ($m) use (&$protected) {
        $key = "\x01CSOPTP" . count($protected) . "\x01";
        $protected[$key] = $m[0];
        return $key;
    }, $html);

    $has_cjk = '/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}]/u';

    // Translate visible text nodes whole; for zh_tw fall back to per-character conversion.
    $html = preg_replace_callback('/>([^<>]+)</u', function ($m) use ($dict, $normalized_dict, $is_tw, $has_cjk) {
        $raw = $m[1];
        if (!preg_match($has_cjk, $raw)) return $m[0];
        $key = trim($raw);
        if ($key !== '' && isset($dict[$key])) {
            return '>' . str_replace($key, $dict[$key], $raw) . '<';
        }
        $normalized_key = preg_replace('/\s+/u', ' ', $key);
        if ($normalized_key !== '' && isset($normalized_dict[$normalized_key])) {
            preg_match('/^\s*/u', $raw, $leading);
            preg_match('/\s*$/u', $raw, $trailing);
            return '>' . $leading[0] . $normalized_dict[$normalized_key] . $trailing[0] . '<';
        }
        if ($is_tw) return '>' . strtr($raw, $dict) . '<';
        return $m[0];
    }, $html);

    // Translate selected attribute values.
    $html = preg_replace_callback('/\b(alt|title|placeholder|aria-label)="([^"]*)"/u', function ($m) use ($dict, $normalized_dict, $is_tw, $has_cjk) {
        if (!preg_match($has_cjk, $m[2])) return $m[0];
        $key = trim($m[2]);
        if ($key !== '' && isset($dict[$key])) {
            return $m[1] . '="' . str_replace($key, $dict[$key], $m[2]) . '"';
        }
        $normalized_key = preg_replace('/\s+/u', ' ', $key);
        if ($normalized_key !== '' && isset($normalized_dict[$normalized_key])) {
            return $m[1] . '="' . esc_attr($normalized_dict[$normalized_key]) . '"';
        }
        if ($is_tw) return $m[1] . '="' . strtr($m[2], $dict) . '"';
        return $m[0];
    }, $html);

    if (!empty($protected)) {
        $html = strtr($html, $protected);
    }
    $code = $is_tw ? 'zh-Hant' : 'en';
    $html = preg_replace('/(<html\b[^>]*?)\s+lang="[^"]*"/i', '$1 lang="' . $code . '"', $html, 1);
    return $html;
}

function csop_hf_start_buffer() {
    if (!csop_hf_can_render()) return;
    csop_hf_current_lang();
    ob_start('csop_hf_inject_markup');
}

function csop_hf_inject_markup($html) {
    if (!is_string($html) || $html === '') return $html;
    if (stripos($html, '<html') === false && stripos($html, '<body') === false) return $html;

    $html = csop_render_exported_shortcodes($html);
    $settings = csop_hf_get_options();

    if ($settings['enabled_header'] === '1' && stripos($html, 'csop-hf-header') === false) {
        $header = csop_hf_render_header($settings, false);
        if (preg_match('/<body\b[^>]*>/i', $html)) {
            $html = preg_replace('/(<body\b[^>]*>)/i', '$1' . $header, $html, 1);
        } else {
            $html = $header . $html;
        }
    }

    if ($settings['enabled_footer'] === '1' && stripos($html, 'csop-hf-footer') === false) {
        $footer = csop_hf_render_footer($settings, false);
        if (stripos($html, '</body>') !== false) {
            $html = preg_replace('/<\/body>/i', $footer . '</body>', $html, 1);
        } else {
            $html .= $footer;
        }
    }

    $html = csop_hf_demote_hero_h1($html);
    $html = csop_hf_translate_output($html);
    if (function_exists('csop_seo_soften_wording')) {
        $html = csop_seo_soften_wording($html);
    }
    if (function_exists('csop_seo_apply_image_alts')) {
        $html = csop_seo_apply_image_alts($html);
    }
    if (function_exists('csop_seo_dedupe_head_title')) {
        $html = csop_seo_dedupe_head_title($html);
    }

    return $html;
}

/**
 * Demote the homepage hero heading (the .gb-text-e24a13ba element) from <h1> to
 * <h2>. That heading can come from the DB-saved homepage content, the demo
 * fallback, or the en/zh_tw pages, so we fix it on the final HTML rather than in
 * any single template. Runs on the whole-page output buffer and only touches
 * this specific hero element, so other headings are left untouched.
 */
function csop_hf_demote_hero_h1($html) {
    if (!is_string($html) || stripos($html, 'gb-text-e24a13ba') === false) return $html;

    return preg_replace_callback(
        '#<h1(\b[^>]*\bgb-text-e24a13ba\b[^>]*)>(.*?)</h1>#is',
        function ($m) {
            return '<h2' . $m[1] . '>' . $m[2] . '</h2>';
        },
        $html
    );
}

function csop_render_exported_shortcodes($content) {
    if (!is_string($content) || strpos($content, '[csop_') === false) return $content;
    if (!function_exists('do_shortcode')) return $content;

    $shortcodes = array(
        'csop_header',
        'csop_footer',
        'csop_home',
        'csop_products',
        'csop_page_blog',
        'csop_page_about',
        'csop_page_price',
        'csop_page_contact',
        'csop_page_en',
        'csop_page_zh_tw',
    );
    $pattern = get_shortcode_regex($shortcodes);

    return preg_replace_callback('/' . $pattern . '/s', function ($matches) {
        return do_shortcode($matches[0]);
    }, $content);
}

function csop_hf_header_shortcode() {
    return csop_hf_render_header(csop_hf_get_options(), false);
}

function csop_hf_footer_shortcode() {
    return csop_hf_render_footer(csop_hf_get_options(), false);
}

function csop_hf_render_header($settings, $preview = false) {
    $menu_items = csop_hf_parse_links($settings['menu_items']);
    $language_items = csop_hf_parse_links($settings['language_items']);
    $logo_label = strtolower(substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $settings['site_name']), 0, 3));
    if ($logo_label === '') $logo_label = 'csv';
    $style = '--csop-hf-width:' . intval($settings['container_width']) . 'px;';
    $style .= '--csop-hf-nav-height:' . intval($settings['nav_height']) . 'px;';
    $style .= '--csop-hf-nav-bg:' . esc_attr($settings['nav_bg']) . ';';
    $style .= '--csop-hf-nav-hover:' . esc_attr($settings['nav_hover_bg']) . ';';
    $style .= '--csop-hf-nav-text:' . esc_attr($settings['nav_text']) . ';';

    ob_start();
    echo csop_hf_front_css();
    ?>
    <div class="csop-hf-wrap <?php echo $preview ? 'csop-hf-preview' : ''; ?>" data-csop-hf-root style="<?php echo esc_attr($style); ?>">
        <a class="screen-reader-text skip-link csop-hf-skip" href="#content" title="Skip to content">Skip to content</a>
        <nav class="auto-hide-sticky has-branding main-navigation nav-align-right has-menu-bar-items sub-menu-right csop-hf-header" id="site-navigation" aria-label="Primary" itemtype="https://schema.org/SiteNavigationElement" itemscope>
            <div class="inside-navigation grid-container">
                <div class="navigation-branding">
                    <div class="site-logo">
                        <a href="<?php echo esc_url(csop_hf_resolve_url($settings['home_url'])); ?>" title="<?php echo esc_attr($settings['site_name']); ?>" rel="home" data-csop-href="home_url">
                            <?php if (!empty($settings['logo_image'])): ?>
                                <img class="header-image is-logo-image" alt="<?php echo esc_attr($settings['site_name']); ?>" src="<?php echo esc_url($settings['logo_image']); ?>" title="<?php echo esc_attr($settings['site_name']); ?>" width="334" height="286" data-csop-src="logo_image">
                            <?php else: ?>
                                <span class="csop-hf-text-logo" aria-label="<?php echo esc_attr($settings['site_name']); ?>"><?php echo esc_html($logo_label); ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    <p class="main-title" itemprop="headline">
                        <a href="<?php echo esc_url(csop_hf_resolve_url($settings['home_url'])); ?>" rel="home" data-csop-bind="site_name" data-csop-href="home_url"><?php echo esc_html($settings['site_name']); ?></a>
                    </p>
                </div>

                <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false" type="button" data-csop-menu-toggle>
                    <span class="gp-icon icon-menu-bars" aria-hidden="true">
                        <svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"><path d="M0 96c0-13.255 10.745-24 24-24h464c13.255 0 24 10.745 24 24s-10.745 24-24 24H24C10.745 120 0 109.255 0 96zm0 160c0-13.255 10.745-24 24-24h464c13.255 0 24 10.745 24 24s-10.745 24-24 24H24c-13.255 0-24-10.745-24-24zm0 160c0-13.255 10.745-24 24-24h464c13.255 0 24 10.745 24 24s-10.745 24-24 24H24c-13.255 0-24-10.745-24-24z"/></svg>
                        <svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"><path d="M71.029 71.029c9.373-9.372 24.569-9.372 33.942 0L256 222.059l151.029-151.03c9.373-9.372 24.569-9.372 33.942 0 9.372 9.373 9.372 24.569 0 33.942L289.941 256l151.03 151.029c9.372 9.373 9.372 24.569 0 33.942-9.373 9.372-24.569 9.372-33.942 0L256 289.941l-151.029 151.03c-9.373 9.372-24.569 9.372-33.942 0-9.372-9.373-9.372-24.569 0-33.942L222.059 256 71.029 104.971c-9.372-9.373-9.372-24.569 0-33.942z"/></svg>
                    </span>
                    <span class="mobile-menu" data-csop-bind="mobile_label"><?php echo esc_html($settings['mobile_label']); ?></span>
                </button>

                <div id="primary-menu" class="main-nav" data-csop-primary-menu>
                    <ul id="menu-primary-menu" class="menu sf-menu">
                        <?php foreach ($menu_items as $index => $item): ?>
                            <li class="menu-item <?php echo $index === 0 ? 'current-menu-item menu-item-home' : ''; ?>">
                                <a href="<?php echo esc_url(csop_hf_resolve_url($item['url'])); ?>"><?php echo esc_html($item['label']); ?></a>
                            </li>
                        <?php endforeach; ?>
                        <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children">
                            <a href="#">
                                <span data-csop-bind="language_label"><?php echo esc_html($settings['language_label']); ?></span>
                                <span role="presentation" class="dropdown-menu-toggle">
                                    <span class="gp-icon icon-arrow">
                                        <svg viewBox="0 0 330 512" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"><path d="M305.913 197.085c0 2.266-1.133 4.815-2.833 6.514L171.087 335.593c-1.7 1.7-4.249 2.832-6.515 2.832s-4.815-1.133-6.515-2.832L26.064 203.599c-1.7-1.7-2.832-4.248-2.832-6.514s1.132-4.816 2.832-6.515l14.162-14.163c1.7-1.699 3.966-2.832 6.515-2.832 2.266 0 4.815 1.133 6.515 2.832l111.316 111.317 111.316-111.317c1.7-1.699 4.249-2.832 6.515-2.832s4.815 1.133 6.515 2.832l14.162 14.163c1.7 1.7 2.833 4.249 2.833 6.515z"/></svg>
                                    </span>
                                </span>
                            </a>
                            <ul class="sub-menu">
                                <?php foreach ($language_items as $item): ?>
                                    <li class="trp-language-switcher-container trp-menu-ls-item trp-menu-ls-desktop menu-item">
                                        <a href="<?php echo csop_hf_lang_switch_url(csop_hf_lang_code_from_url($item['url'])); ?>">
                                            <span data-no-translation>
                                                <?php if (!empty($item['image'])): ?>
                                                    <img src="<?php echo esc_url($item['image']); ?>" class="trp-flag-image" alt="" role="presentation" loading="eager" decoding="async" width="18" height="14">
                                                <?php endif; ?>
                                                <span class="trp-ls-language-name"><?php echo esc_html($item['label']); ?></span>
                                            </span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    </ul>
                </div>

                <div class="menu-bar-items">
                    <span class="menu-bar-item">
                        <button type="button" aria-label="<?php echo esc_attr($settings['search_label']); ?>" data-csop-search-toggle>
                            <span class="gp-icon icon-search">
                                <svg viewBox="0 0 512 512" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em"><path fill-rule="evenodd" clip-rule="evenodd" d="M208 48c-88.366 0-160 71.634-160 160s71.634 160 160 160 160-71.634 160-160S296.366 48 208 48zM0 208C0 93.125 93.125 0 208 0s208 93.125 208 208c0 48.741-16.765 93.566-44.843 129.024l133.826 134.018c9.366 9.379 9.355 24.575-.025 33.941-9.379 9.366-24.575 9.355-33.941-.025L337.238 370.987C301.747 399.167 256.839 416 208 416 93.125 416 0 322.875 0 208z"/></svg>
                            </span>
                        </button>
                    </span>
                </div>
            </div>
            <div class="csop-hf-search-panel" data-csop-search-panel hidden>
                <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                    <input type="search" name="s" placeholder="<?php echo esc_attr($settings['search_placeholder']); ?>" data-csop-placeholder="search_placeholder">
                    <button type="submit">Search</button>
                </form>
            </div>
        </nav>
    </div>
    <?php
    return ob_get_clean();
}

function csop_hf_render_footer($settings, $preview = false) {
    $quick_links = csop_hf_parse_links($settings['quick_links']);
    $style = '--gb-container-width:' . intval($settings['container_width']) . 'px;';
    $style .= '--base-2:' . esc_attr($settings['footer_bg']) . ';';
    $style .= '--contrast-2:#2f4468;';
    $style .= '--contrast-3:#878787;';
    $style .= '--accent-2:#1b78e2;';
    $style .= '--csop-hf-footer-border:' . esc_attr($settings['footer_border']) . ';';

    ob_start();
    ?>
    <div class="site-footer footer-bar-active footer-bar-align-right csop-hf-footer <?php echo $preview ? 'csop-hf-preview' : ''; ?>" style="<?php echo esc_attr($style); ?>">
        <div class="gb-element-80d35441">
            <div class="gb-element-e3cf7d4a">
                <div class="gb-element-084a3b6e">
                    <div class="gb-element-b12f2b12">
                        <img loading="eager" decoding="async" width="300" height="301" class="gb-media-1aec2793" alt="QR code" title="<?php echo esc_attr($settings['qr_whatsapp_label']); ?>" src="<?php echo esc_url($settings['qr_whatsapp_image']); ?>" data-csop-src="qr_whatsapp_image">
                        <p class="gb-text" data-csop-bind="qr_whatsapp_label"><?php echo esc_html($settings['qr_whatsapp_label']); ?></p>
                    </div>

                    <div class="gb-element-13da7ed5">
                        <h3 class="gb-text" data-csop-bind="quick_title"><?php echo esc_html($settings['quick_title']); ?></h3>
                        <nav class="is-vertical wp-block-navigation is-layout-flex wp-container-core-navigation-is-layout-4fc3f8e1 wp-block-navigation-is-layout-flex" aria-label="programhelp">
                            <ul class="wp-block-navigation__container is-vertical wp-block-navigation">
                                <?php foreach ($quick_links as $item): ?>
                                    <li class="wp-block-navigation-item menu-item menu-item-type-post_type menu-item-object-page wp-block-navigation-link">
                                        <a class="wp-block-navigation-item__content" href="<?php echo esc_url(csop_hf_resolve_url($item['url'])); ?>" title="">
                                            <span class="wp-block-navigation-item__label"><?php echo esc_html($item['label']); ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </nav>
                    </div>

                    <div class="gb-element-d10d2533">
                        <h3 class="gb-text" data-csop-bind="contact_title"><?php echo esc_html($settings['contact_title']); ?></h3>
                        <?php echo csop_hf_contact_line('Telegram:', $settings['telegram_text'], $settings['telegram_url'], 'telegram_text'); ?>
                        <?php echo csop_hf_contact_line('Phone:', $settings['whatsapp_text'], $settings['whatsapp_url'], 'whatsapp_text'); ?>
                        <?php echo csop_hf_contact_line('Email:', $settings['email_text'], $settings['email_url'], 'email_text'); ?>
                        <p class="gb-text gb-text-1a21da79"><strong>Wechat:</strong>&nbsp;<span data-csop-bind="wechat_text"><?php echo esc_html($settings['wechat_text']); ?></span></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="gb-element-76365dcf"><div class="gb-element-6dacc793"></div></div>

        <div class="gb-element-7de49033">
            <div class="gb-element-d4b812ad">
                <div class="gb-element-511ef82e">
                    <div><div class="gb-text gb-text-c3994867" data-csop-bind="copyright_text"><?php echo esc_html($settings['copyright_text']); ?></div></div>
                    <div>
                        <p class="gb-text gb-text-7720e281">
                            <a href="<?php echo esc_url(csop_hf_resolve_url($settings['footer_contact_url'])); ?>" target="_blank" rel="noopener" title="<?php echo esc_attr($settings['footer_contact_text']); ?>" data-csop-bind="footer_contact_text" data-csop-href="footer_contact_url"><?php echo esc_html($settings['footer_contact_text']); ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php echo csop_hf_front_js(); ?>
    </div>
    <?php
    return ob_get_clean();
}

function csop_hf_contact_line($label, $text, $url, $bind_key) {
    $classes = array(
        'telegram_text' => 'gb-text-09cfc29e',
        'whatsapp_text' => 'gb-text-0897f7de',
        'email_text' => 'gb-text-b5d58ce0',
    );
    $class = isset($classes[$bind_key]) ? $classes[$bind_key] : '';

    ob_start();
    ?>
    <p class="gb-text <?php echo esc_attr($class); ?>">
        <strong><?php echo esc_html($label); ?></strong>&nbsp;
        <?php if (!empty($url)): ?>
            <a href="<?php echo esc_url(csop_hf_resolve_url($url)); ?>" target="_blank" rel="noreferrer noopener" data-csop-bind="<?php echo esc_attr($bind_key); ?>"><?php echo esc_html($text); ?></a>
        <?php else: ?>
            <span data-csop-bind="<?php echo esc_attr($bind_key); ?>"><?php echo esc_html($text); ?></span>
        <?php endif; ?>
    </p>
    <?php
    return ob_get_clean();
}

function csop_hf_front_css() {
    static $done = false;
    if ($done) return '';
    $done = true;

    return <<<'CSS'
<style id="csop-hf-front-css">
.csop-hf-wrap,.csop-hf-footer{font-family:"Open Sans",Arial,Helvetica,sans-serif;box-sizing:border-box}.csop-hf-wrap *,.csop-hf-wrap *:before,.csop-hf-wrap *:after,.csop-hf-footer *,.csop-hf-footer *:before,.csop-hf-footer *:after{box-sizing:border-box}.csop-hf-skip{position:absolute;left:-9999px}.csop-hf-header.main-navigation{width:100%;background:var(--csop-hf-nav-bg);color:var(--csop-hf-nav-text);position:sticky;top:0;z-index:9998;box-shadow:0 2px 2px -2px rgba(0,0,0,.2)}.csop-hf-header .inside-navigation{max-width:var(--csop-hf-width);min-height:var(--csop-hf-nav-height);margin:0 auto;padding:0 20px;display:flex;align-items:center;justify-content:flex-end}.csop-hf-header .navigation-branding{display:flex;align-items:center;margin-right:auto;min-width:0}.csop-hf-header .site-logo{line-height:0;flex:0 0 auto}.csop-hf-header .site-logo a{display:block}.csop-hf-header .header-image{display:block;height:var(--csop-hf-nav-height);width:auto;max-width:none}.csop-hf-header .csop-hf-text-logo{display:grid;place-items:center;height:calc(var(--csop-hf-nav-height) - 18px);min-width:calc(var(--csop-hf-nav-height) - 18px);padding:0 8px;border:2px solid currentColor;border-radius:6px;color:var(--csop-hf-nav-text);font-size:16px;font-weight:800;line-height:1;text-transform:lowercase}.csop-hf-header .main-title{font-size:25px;line-height:var(--csop-hf-nav-height);margin:0 20px 0 10px;font-weight:400;white-space:nowrap}.csop-hf-header .main-title a,.csop-hf-header a{color:var(--csop-hf-nav-text);text-decoration:none}.csop-hf-header .main-nav{display:flex;align-self:stretch}.csop-hf-header .main-nav ul{list-style:none;margin:0;padding:0}.csop-hf-header .main-nav>ul{display:flex;align-items:stretch}html[lang=en] .csop-hf-header .main-nav>ul{flex-wrap:wrap;justify-content:flex-end;align-items:center}html[lang=en] .csop-hf-header .main-nav .menu>li>a{min-height:calc(var(--csop-hf-nav-height)*.5);line-height:calc(var(--csop-hf-nav-height)*.5)}.csop-hf-header .menu>li{position:relative;margin:0;flex-shrink:0}.csop-hf-header .main-nav .menu>li>a,.csop-hf-header .menu-bar-item button,.csop-hf-header .menu-toggle{min-height:var(--csop-hf-nav-height);line-height:var(--csop-hf-nav-height);display:flex;align-items:center;color:var(--csop-hf-nav-text);background:transparent;border:0;padding:0 20px;font-size:17px;cursor:pointer;white-space:nowrap}.csop-hf-header .main-nav .menu>li.current-menu-item>a,.csop-hf-header .main-nav .menu>li:hover>a,.csop-hf-header .main-nav .menu>li:focus-within>a,.csop-hf-header .menu-bar-item button:hover{background:var(--csop-hf-nav-hover);color:var(--csop-hf-nav-text)}.csop-hf-header .dropdown-menu-toggle{display:inline-flex;margin-left:8px}.csop-hf-header svg{width:1em;height:1em;fill:currentColor;display:block}.csop-hf-header .sub-menu{display:none;position:absolute;left:0;top:100%;min-width:190px;background:var(--csop-hf-nav-bg);z-index:9999;box-shadow:0 2px 2px rgba(0,0,0,.15)}.csop-hf-header li:hover>.sub-menu,.csop-hf-header li:focus-within>.sub-menu{display:block}.csop-hf-header .sub-menu li a{display:flex;align-items:center;line-height:45px;min-height:45px;padding:0 20px;white-space:nowrap;font-size:16px;color:var(--csop-hf-nav-text)}.csop-hf-header .sub-menu li a:hover{background:var(--csop-hf-nav-hover)}.csop-hf-header .trp-flag-image{margin-right:8px;vertical-align:middle}.csop-hf-header .menu-bar-items{display:flex;align-self:stretch}.csop-hf-header .menu-bar-item{display:flex}.csop-hf-header .menu-bar-item button{width:60px;justify-content:center;padding:0}.csop-hf-header .menu-toggle{display:none;color:var(--csop-hf-nav-text)}.csop-hf-header .menu-toggle .icon-menu-bars{display:inline-grid;place-items:center;margin-right:8px}.csop-hf-header .menu-toggle .icon-menu-bars svg+svg{display:none}.csop-hf-header.csop-mobile-open .menu-toggle .icon-menu-bars svg:first-child{display:none}.csop-hf-header.csop-mobile-open .menu-toggle .icon-menu-bars svg+svg{display:block}.csop-hf-search-panel{background:var(--csop-hf-nav-hover);padding:14px 20px}.csop-hf-search-panel form{max-width:var(--csop-hf-width);margin:0 auto;display:flex;gap:10px}.csop-hf-search-panel input{flex:1;min-width:0;height:44px;border:1px solid rgba(255,255,255,.25);background:#fff;color:#222;padding:0 14px;font-size:16px}.csop-hf-search-panel button{height:44px;border:0;background:#1b78e2;color:#fff;padding:0 20px;font-size:15px;cursor:pointer}.csop-hf-footer{--gb-container-width:1200px;--base-2:#f7f8f9;--contrast-2:#2f4468;--contrast-3:#878787;--accent-2:#1b78e2;color:#212121}.csop-hf-footer a{color:#1b78e2;text-decoration:none}.csop-hf-footer a:hover{color:#35343a}.csop-hf-footer p,.csop-hf-footer h3{margin-top:0}.csop-hf-footer .gb-element-80d35441{background-color:var(--base-2);border-top:1px solid var(--csop-hf-footer-border)}.csop-hf-footer .gb-element-e3cf7d4a{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:40px 20px}.csop-hf-footer .gb-element-084a3b6e{column-gap:2em;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));row-gap:1em}.csop-hf-footer .gb-element-3ff89166,.csop-hf-footer .gb-element-b12f2b12{text-align:center}.csop-hf-footer .gb-element-13da7ed5{padding-left:60px;text-align:left}.csop-hf-footer .gb-element-d10d2533{padding-left:20px}.csop-hf-footer .gb-element-76365dcf{display:flex;justify-content:space-between;margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:20px}.csop-hf-footer .gb-element-6dacc793{column-gap:15px;display:flex}.csop-hf-footer .gb-element-7de49033{background-color:var(--base-2)}.csop-hf-footer .gb-element-d4b812ad{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:20px}.csop-hf-footer .gb-element-511ef82e{column-gap:1em;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));row-gap:1em}.csop-hf-footer .gb-media-dab60b27,.csop-hf-footer .gb-media-1aec2793{height:auto;max-width:100%;object-fit:cover;width:100%}.csop-hf-footer .wp-block-navigation__container{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;align-items:flex-start;gap:.5em}.csop-hf-footer .wp-block-navigation-item__content{display:inline-block;color:#1b78e2}.csop-hf-footer .gb-text-09cfc29e,.csop-hf-footer .gb-text-0897f7de,.csop-hf-footer .gb-text-b5d58ce0{margin-bottom:10px}.csop-hf-footer .gb-text-1a21da79{margin-bottom:0}.csop-hf-footer .gb-text-c3994867{font-size:15px;margin-bottom:0}.csop-hf-footer .gb-text-7720e281{display:block;font-size:15px;margin-bottom:0;text-align:right}.csop-hf-footer .gb-text-7720e281 a{color:var(--contrast-2)}.csop-hf-footer .gb-text-7720e281 a:hover{color:var(--contrast-3);font-size:15px}@media(max-width:1024px){.csop-hf-footer .gb-element-76365dcf{align-items:center;flex-direction:column;justify-content:center;row-gap:20px}.csop-hf-footer .gb-element-6dacc793{order:-1}}@media(max-width:900px){.csop-hf-header .inside-navigation{padding:0}.csop-hf-header .navigation-branding{margin-left:10px;margin-right:auto}.csop-hf-header .main-title{margin-right:10px}.csop-hf-header .menu-toggle{display:flex;align-items:center;padding:0 15px}.csop-hf-header .main-nav{display:none;width:100%;order:5;background:var(--csop-hf-nav-bg)}.csop-hf-header.csop-mobile-open .main-nav{display:block}.csop-hf-header .inside-navigation{flex-wrap:wrap;justify-content:space-between}.csop-hf-header .main-nav>ul{display:block;width:100%}.csop-hf-header .main-nav .menu>li>a{line-height:52px;min-height:52px}.csop-hf-header .sub-menu{position:static;box-shadow:none;background:rgba(0,0,0,.16);display:none}.csop-hf-header .menu-item-has-children.csop-sub-open>.sub-menu{display:block}.csop-hf-header .menu-bar-items{margin-left:0}.csop-hf-footer .gb-element-084a3b6e{grid-template-columns:1fr 1fr}.csop-hf-footer .gb-element-13da7ed5,.csop-hf-footer .gb-element-d10d2533{padding-left:0}}@media(max-width:767px){.csop-hf-footer .gb-element-084a3b6e{grid-template-columns:1fr}.csop-hf-footer .gb-element-511ef82e{grid-template-columns:1fr}.csop-hf-footer .gb-text-c3994867{text-align:center}.csop-hf-footer .gb-text-7720e281{text-align:center}}
</style>
CSS;
}

function csop_hf_front_js() {
    static $done = false;
    if ($done) return '';
    $done = true;

    return <<<'JS'
<script id="csop-hf-front-js">
(function(){
  function closest(el, selector){while(el&&el.nodeType===1){if(el.matches(selector))return el;el=el.parentElement;}return null;}
  document.addEventListener('click', function(event){
    var toggle = closest(event.target, '[data-csop-menu-toggle]');
    if (toggle) {
      var nav = closest(toggle, '.csop-hf-header');
      if (nav) {
        var open = !nav.classList.contains('csop-mobile-open');
        nav.classList.toggle('csop-mobile-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      }
      return;
    }
    var searchToggle = closest(event.target, '[data-csop-search-toggle]');
    if (searchToggle) {
      var root = closest(searchToggle, '.csop-hf-header');
      var panel = root ? root.querySelector('[data-csop-search-panel]') : null;
      if (panel) {
        panel.hidden = !panel.hidden;
        if (!panel.hidden) {
          var input = panel.querySelector('input[type="search"]');
          if (input) input.focus();
        }
      }
      return;
    }
    var parent = closest(event.target, '.csop-hf-header .menu-item-has-children > a');
    if (parent && window.matchMedia('(max-width: 900px)').matches) {
      event.preventDefault();
      parent.parentElement.classList.toggle('csop-sub-open');
    }
  });
})();
</script>
JS;
}

function csop_hf_admin_menu() {
    if (!function_exists('cc_site_visual_dashboard_page')) {
        function cc_site_visual_dashboard_page() {
            echo '<div class="wrap"><h1>网站可视化编辑</h1><p>在左侧子菜单中管理站点模块。</p></div>';
        }
        add_menu_page(
            '网站可视化编辑',
            '网站可视化编辑',
            'manage_options',
            'cc-site-visual',
            'cc_site_visual_dashboard_page',
            'dashicons-admin-customizer',
            3
        );
    }

    add_submenu_page(
        'cc-site-visual',
        'csvosupport 眉页脚页',
        '眉页脚页',
        'manage_options',
        'csop-header-footer',
        'csop_hf_settings_page'
    );
}

function csop_hf_admin_assets($hook) {
    if (strpos((string) $hook, 'csop-header-footer') === false) return;

    wp_register_style('csop-hf-admin-style', false, array(), '1.0.0');
    wp_enqueue_style('csop-hf-admin-style');
    wp_add_inline_style('csop-hf-admin-style', csop_hf_admin_css());

    wp_register_script('csop-hf-admin-script', false, array('jquery'), '1.0.0', true);
    wp_enqueue_script('csop-hf-admin-script');
    wp_add_inline_script('csop-hf-admin-script', csop_hf_admin_js());
}

function csop_hf_settings_page() {
    if (!current_user_can('manage_options')) return;

    if (isset($_POST['csop_hf_save'])) {
        check_admin_referer('csop_hf_save_action');
        $raw = isset($_POST['csop_hf']) && is_array($_POST['csop_hf']) ? $_POST['csop_hf'] : array();
        update_option(csop_hf_option_name(), csop_hf_sanitize_options($raw));
        echo '<div class="notice notice-success is-dismissible"><p>已保存 csvosupport 眉页脚页设置。</p></div>';
    }

    if (isset($_POST['csop_hf_reset'])) {
        check_admin_referer('csop_hf_save_action');
        update_option(csop_hf_option_name(), csop_hf_defaults());
        echo '<div class="notice notice-success is-dismissible"><p>已恢复默认 demo 数据。</p></div>';
    }

    $settings = csop_hf_get_options();
    ?>
    <div class="wrap csop-hf-admin-wrap">
        <h1>csvosupport 眉页脚页可视化编辑</h1>
        <p>前台会自动注入全站眉页和脚页，也可单独使用短代码：<code>[csop_header]</code>、<code>[csop_footer]</code></p>

        <form method="post" novalidate>
            <?php wp_nonce_field('csop_hf_save_action'); ?>

            <div class="csop-hf-admin-layout">
                <div class="csop-hf-admin-form">
                    <div class="csop-hf-jumpbar">
                        <button type="button" data-csop-hf-jump="#csop-hf-panel-basic">基础</button>
                        <button type="button" data-csop-hf-jump="#csop-hf-panel-header">眉页</button>
                        <button type="button" data-csop-hf-jump="#csop-hf-panel-footer">脚页</button>
                        <button type="button" data-csop-hf-jump="#csop-hf-panel-contact">联系</button>
                    </div>

                    <section class="csop-hf-panel" id="csop-hf-panel-basic">
                        <h2>基础与样式</h2>
                        <div class="csop-hf-check-grid">
                            <?php csop_hf_checkbox('enabled_header', '启用全站眉页', $settings['enabled_header']); ?>
                            <?php csop_hf_checkbox('enabled_footer', '启用全站脚页', $settings['enabled_footer']); ?>
                        </div>
                        <div class="csop-hf-row-2">
                            <?php csop_hf_field('container_width', '内容最大宽度 px', $settings['container_width'], 'number', array('css_var' => '--csop-hf-width', 'suffix' => 'px')); ?>
                            <?php csop_hf_field('nav_height', '导航高度 px', $settings['nav_height'], 'number', array('css_var' => '--csop-hf-nav-height', 'suffix' => 'px')); ?>
                        </div>
                        <div class="csop-hf-row-3">
                            <?php csop_hf_color('nav_bg', '导航背景', $settings['nav_bg'], '--csop-hf-nav-bg'); ?>
                            <?php csop_hf_color('nav_hover_bg', '导航悬浮/当前', $settings['nav_hover_bg'], '--csop-hf-nav-hover'); ?>
                            <?php csop_hf_color('nav_text', '导航文字', $settings['nav_text'], '--csop-hf-nav-text'); ?>
                        </div>
                        <div class="csop-hf-row-2">
                            <?php csop_hf_color('footer_bg', '脚页背景', $settings['footer_bg'], '--base-2'); ?>
                            <?php csop_hf_field('footer_border', '脚页上边框', $settings['footer_border']); ?>
                        </div>
                    </section>

                    <section class="csop-hf-panel" id="csop-hf-panel-header">
                        <h2>眉页内容</h2>
                        <div class="csop-hf-row-2">
                            <?php csop_hf_field('site_name', '站点名称', $settings['site_name']); ?>
                            <?php csop_hf_field('home_url', 'Logo/站点链接', $settings['home_url']); ?>
                        </div>
                        <?php csop_hf_field('logo_image', 'Logo 图片 URL', $settings['logo_image'], 'url'); ?>
                        <div class="csop-hf-row-2">
                            <?php csop_hf_field('mobile_label', '手机菜单文字', $settings['mobile_label']); ?>
                            <?php csop_hf_field('search_placeholder', '搜索框占位文字', $settings['search_placeholder']); ?>
                        </div>
                        <?php csop_hf_textarea('menu_items', '顶部菜单，一行一个：文字|链接', $settings['menu_items']); ?>
                        <div class="csop-hf-row-2">
                            <?php csop_hf_field('language_label', '语言菜单标题', $settings['language_label']); ?>
                            <?php csop_hf_field('search_label', '搜索按钮 aria 标签', $settings['search_label']); ?>
                        </div>
                        <?php csop_hf_textarea('language_items', '语言下拉，一行一个：文字|链接|旗帜图片URL', $settings['language_items']); ?>
                    </section>

                    <section class="csop-hf-panel" id="csop-hf-panel-footer">
                        <h2>脚页内容</h2>
                        <div class="csop-hf-row-2">
                            <?php csop_hf_field('qr_wechat_image', '微信二维码图片', $settings['qr_wechat_image'], 'url'); ?>
                            <?php csop_hf_field('qr_wechat_label', '微信二维码标题', $settings['qr_wechat_label']); ?>
                        </div>
                        <div class="csop-hf-row-2">
                            <?php csop_hf_field('qr_whatsapp_image', 'WhatsApp 二维码图片', $settings['qr_whatsapp_image'], 'url'); ?>
                            <?php csop_hf_field('qr_whatsapp_label', 'WhatsApp 二维码标题', $settings['qr_whatsapp_label']); ?>
                        </div>
                        <?php csop_hf_field('quick_title', '快速链接标题', $settings['quick_title']); ?>
                        <?php csop_hf_textarea('quick_links', '快速链接，一行一个：文字|链接', $settings['quick_links']); ?>
                    </section>

                    <section class="csop-hf-panel" id="csop-hf-panel-contact">
                        <h2>联系信息与版权</h2>
                        <?php csop_hf_field('contact_title', '联系区标题', $settings['contact_title']); ?>
                        <div class="csop-hf-row-2">
                            <?php csop_hf_field('telegram_text', 'Telegram 显示文字', $settings['telegram_text']); ?>
                            <?php csop_hf_field('telegram_url', 'Telegram 链接', $settings['telegram_url'], 'url'); ?>
                        </div>
                        <div class="csop-hf-row-2">
                            <?php csop_hf_field('whatsapp_text', 'Whatsapp 显示文字', $settings['whatsapp_text']); ?>
                            <?php csop_hf_field('whatsapp_url', 'Whatsapp 链接', $settings['whatsapp_url'], 'url'); ?>
                        </div>
                        <div class="csop-hf-row-2">
                            <?php csop_hf_field('email_text', 'Email 显示文字', $settings['email_text']); ?>
                            <?php csop_hf_field('email_url', 'Email 链接', $settings['email_url']); ?>
                        </div>
                        <div class="csop-hf-row-2">
                            <?php csop_hf_field('wechat_text', 'Wechat 显示文字', $settings['wechat_text']); ?>
                            <?php csop_hf_field('copyright_text', '版权文字', $settings['copyright_text']); ?>
                        </div>
                        <div class="csop-hf-row-2">
                            <?php csop_hf_field('footer_contact_text', '底部链接文字', $settings['footer_contact_text']); ?>
                            <?php csop_hf_field('footer_contact_url', '底部链接地址', $settings['footer_contact_url']); ?>
                        </div>
                    </section>

                    <div class="csop-hf-save-bar">
                        <button type="submit" name="csop_hf_save" class="button button-primary button-hero">保存设置</button>
                        <button type="submit" name="csop_hf_reset" class="button button-secondary button-hero" onclick="return confirm('确定恢复 csvosupport demo 默认头尾？')">恢复默认</button>
                    </div>
                </div>

                <div class="csop-hf-admin-preview">
                    <div class="csop-hf-preview-head">
                        <div>
                            <strong>实时可视化预览</strong>
                            <span>按 demo 的 GeneratePress 眉页和 GenerateBlocks 脚页结构输出</span>
                        </div>
                    </div>
                    <div class="csop-hf-preview-tools">
                        <label>预览设备
                            <select data-csop-hf-preview-width>
                                <option value="1440" selected>桌面 1440px</option>
                                <option value="1200">桌面 1200px</option>
                                <option value="768">平板 768px</option>
                                <option value="390">手机 390px</option>
                            </select>
                        </label>
                        <label>缩放
                            <input type="range" min="35" max="100" value="75" data-csop-hf-preview-zoom>
                            <span data-csop-hf-preview-zoom-text>75%</span>
                        </label>
                    </div>
                    <div class="csop-hf-preview-scroll" data-csop-hf-preview-stage>
                        <div class="csop-hf-preview-scaler" data-csop-hf-preview-scaler>
                            <div class="csop-hf-preview-viewport" data-csop-hf-preview-viewport>
                                <?php echo csop_hf_render_header($settings, true); ?>
                                <div class="csop-hf-preview-body">
                                    <h2>csvosupport 内容区域预览</h2>
                                    <p>产品系统、普通页面和博客列表会出现在眉页与脚页之间。</p>
                                </div>
                                <?php echo csop_hf_render_footer($settings, true); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php
}

function csop_hf_field($key, $label, $value, $type = 'text', $args = array()) {
    $css_var = isset($args['css_var']) ? $args['css_var'] : '';
    $suffix = isset($args['suffix']) ? $args['suffix'] : '';
    ?>
    <label class="csop-hf-field">
        <span><?php echo esc_html($label); ?></span>
        <input
            type="<?php echo esc_attr($type); ?>"
            name="csop_hf[<?php echo esc_attr($key); ?>]"
            value="<?php echo esc_attr($value); ?>"
            data-csop-field="<?php echo esc_attr($key); ?>"
            <?php if ($css_var): ?>data-csop-css-var="<?php echo esc_attr($css_var); ?>"<?php endif; ?>
            <?php if ($suffix): ?>data-csop-css-suffix="<?php echo esc_attr($suffix); ?>"<?php endif; ?>
        >
    </label>
    <?php
}

function csop_hf_textarea($key, $label, $value) {
    ?>
    <label class="csop-hf-field">
        <span><?php echo esc_html($label); ?></span>
        <textarea name="csop_hf[<?php echo esc_attr($key); ?>]" data-csop-field="<?php echo esc_attr($key); ?>" rows="6"><?php echo esc_textarea($value); ?></textarea>
    </label>
    <?php
}

function csop_hf_color($key, $label, $value, $css_var) {
    ?>
    <label class="csop-hf-field">
        <span><?php echo esc_html($label); ?></span>
        <input type="color" name="csop_hf[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($value); ?>" data-csop-field="<?php echo esc_attr($key); ?>" data-csop-css-var="<?php echo esc_attr($css_var); ?>">
    </label>
    <?php
}

function csop_hf_checkbox($key, $label, $value) {
    ?>
    <label class="csop-hf-check">
        <input type="checkbox" name="csop_hf[<?php echo esc_attr($key); ?>]" value="1" data-csop-field="<?php echo esc_attr($key); ?>" <?php checked($value, '1'); ?>>
        <span><?php echo esc_html($label); ?></span>
    </label>
    <?php
}

function csop_hf_admin_css() {
    return <<<'CSS'
.csop-hf-admin-wrap{--csop-admin-border:#dcdcde;--csop-admin-muted:#667085;--csop-admin-bg:#f6f7fb}.csop-hf-admin-layout{display:grid;grid-template-columns:minmax(420px,560px) 1fr;gap:24px;align-items:start}.csop-hf-admin-form,.csop-hf-admin-preview{background:#fff;border:1px solid var(--csop-admin-border);border-radius:8px;box-shadow:0 8px 24px rgba(15,23,42,.06)}.csop-hf-admin-form{padding:18px}.csop-hf-jumpbar{position:sticky;top:32px;z-index:4;background:#fff;display:flex;gap:8px;flex-wrap:wrap;padding-bottom:14px;border-bottom:1px solid #eef0f4;margin-bottom:16px}.csop-hf-jumpbar button{border:1px solid #cfd6e4;background:#fff;border-radius:6px;padding:7px 11px;cursor:pointer}.csop-hf-panel{padding:16px 0;border-bottom:1px solid #eef0f4}.csop-hf-panel h2{margin:0 0 14px;font-size:18px}.csop-hf-row-2,.csop-hf-row-3{display:grid;gap:14px}.csop-hf-row-2{grid-template-columns:repeat(2,minmax(0,1fr))}.csop-hf-row-3{grid-template-columns:repeat(3,minmax(0,1fr))}.csop-hf-field{display:grid;gap:7px;margin-bottom:14px}.csop-hf-field>span,.csop-hf-check span{font-weight:600;color:#1d2327}.csop-hf-field input,.csop-hf-field textarea{width:100%;max-width:100%;border:1px solid #cfd6e4;border-radius:6px;padding:8px 10px;background:#fff}.csop-hf-field input[type=color]{height:40px;padding:3px}.csop-hf-field textarea{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;line-height:1.5}.csop-hf-check-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-bottom:14px}.csop-hf-check{border:1px solid #d7dce8;border-radius:8px;padding:10px 12px;display:flex;align-items:center;gap:8px;background:#fbfcff}.csop-hf-save-bar{position:sticky;bottom:0;background:#fff;border-top:1px solid #eef0f4;margin:0 -18px -18px;padding:14px 18px;display:flex;gap:10px;z-index:5}.csop-hf-preview-head{padding:16px 18px;border-bottom:1px solid #eef0f4;display:flex;justify-content:space-between;align-items:center}.csop-hf-preview-head strong{display:block;font-size:16px}.csop-hf-preview-head span{display:block;color:var(--csop-admin-muted);font-size:12px;margin-top:3px}.csop-hf-preview-tools{padding:12px 18px;border-bottom:1px solid #eef0f4;display:flex;gap:18px;align-items:center;flex-wrap:wrap}.csop-hf-preview-tools label{display:flex;align-items:center;gap:8px;color:#344054;font-weight:600}.csop-hf-preview-scroll{height:760px;overflow:auto;background:repeating-linear-gradient(45deg,#f3f4f8,#f3f4f8 12px,#edf0f6 12px,#edf0f6 24px);padding:24px}.csop-hf-preview-scaler{transform-origin:top left}.csop-hf-preview-viewport{width:1440px;background:#fafafa;box-shadow:0 16px 42px rgba(15,23,42,.22);min-height:900px}.csop-hf-preview-body{margin:30px auto;padding:50px 20px;max-width:1200px;background:#fff;border-radius:6px;box-shadow:rgba(60,64,67,.3) 0 1px 2px 0,rgba(60,64,67,.15) 0 2px 6px 2px}.csop-hf-preview-body h2{color:#2f4468;margin-top:0}.csop-hf-preview .csop-hf-header{position:relative!important}.csop-hf-preview .csop-hf-footer{margin-top:30px}@media(max-width:1200px){.csop-hf-admin-layout{grid-template-columns:1fr}.csop-hf-admin-preview{position:relative}}@media(max-width:782px){.csop-hf-row-2,.csop-hf-row-3,.csop-hf-check-grid{grid-template-columns:1fr}}
CSS;
}

function csop_hf_admin_js() {
    return <<<'JS'
(function($){
  function applyScale(){
    var width = $('[data-csop-hf-preview-width]').val() || '1440';
    var zoom = $('[data-csop-hf-preview-zoom]').val() || '75';
    $('[data-csop-hf-preview-viewport]').css('width', width + 'px');
    $('[data-csop-hf-preview-scaler]').css({transform:'scale(' + (zoom / 100) + ')', width: width + 'px'});
    $('[data-csop-hf-preview-zoom-text]').text(zoom + '%');
  }
  function resolveUrl(value){
    if(!value) return '#';
    if(value === '#') return '#';
    if(/^https?:\/\//i.test(value) || /^mailto:/i.test(value) || /^tel:/i.test(value)) return value;
    if(value.charAt(0) === '/') return value;
    return '/' + value.replace(/^\/+/, '');
  }
  function syncField(input){
    var $input = $(input);
    var key = $input.data('csop-field');
    var value = $input.val();
    var cssVar = $input.data('csop-css-var');
    var suffix = $input.data('csop-css-suffix') || '';
    if(cssVar){
      $('[data-csop-hf-root], .csop-hf-footer').each(function(){ this.style.setProperty(cssVar, value + suffix); });
    }
    $('[data-csop-bind="'+key+'"]').text(value);
    $('[data-csop-href="'+key+'"]').attr('href', resolveUrl(value));
    $('[data-csop-src="'+key+'"]').attr('src', value);
    $('[data-csop-placeholder="'+key+'"]').attr('placeholder', value);
  }
  $(document).on('input change', '[data-csop-field]', function(){ syncField(this); });
  $(document).on('click', '[data-csop-hf-jump]', function(){
    var target = $($(this).data('csop-hf-jump'));
    if(target.length){ target[0].scrollIntoView({behavior:'smooth', block:'start'}); }
  });
  $(document).on('input change', '[data-csop-hf-preview-width], [data-csop-hf-preview-zoom]', applyScale);
  $(applyScale);
})(jQuery);
JS;
}

/**
 * csvosupport homepage renderer copied from the csofferprep demo.
 * Shortcode: [csop_home]
 */
defined('ABSPATH') || exit;

add_action('init', 'csop_home_bootstrap_options');
add_action('admin_menu', 'csop_home_admin_menu', 35);
add_action('admin_enqueue_scripts', 'csop_home_admin_assets');
add_shortcode('csop_home', 'csop_home_shortcode');

function csop_home_option_name() {
    return 'csop_home_options_v1';
}

function csop_home_default_options() {
    return array('content_html' => '');
}

function csop_home_bootstrap_options() {
    if (get_option(csop_home_option_name()) === false) {
        add_option(csop_home_option_name(), csop_home_default_options());
    }
}

function csop_home_get_options() {
    $saved = get_option(csop_home_option_name(), array());
    if (!is_array($saved)) $saved = array();
    return array_merge(csop_home_default_options(), $saved);
}

function csop_home_sanitize_options($raw) {
    $raw = is_array($raw) ? $raw : array();
    return array(
        'content_html' => isset($raw['content_html']) ? wp_unslash($raw['content_html']) : '',
    );
}

function csop_home_current_html() {
    $options = csop_home_get_options();
    $html = isset($options['content_html']) ? trim((string) $options['content_html']) : '';
    return $html !== '' ? $html : csop_home_demo_article();
}

function csop_home_shortcode() {
    return csop_home_render(false);
}

function csop_home_admin_menu() {
    if (!function_exists('cc_site_visual_dashboard_page')) {
        function cc_site_visual_dashboard_page() {
            echo '<div class="wrap"><h1>网站可视化编辑</h1><p>在左侧子菜单中管理站点模块。</p></div>';
        }
        add_menu_page('网站可视化编辑', '网站可视化编辑', 'manage_options', 'cc-site-visual', 'cc_site_visual_dashboard_page', 'dashicons-admin-customizer', 3);
    }
    add_submenu_page('cc-site-visual', 'csvosupport 首页', '首页设置', 'manage_options', 'csop-homepage', 'csop_home_settings_page');
}

function csop_home_admin_assets($hook) {
    if (strpos((string) $hook, 'csop-homepage') === false) return;
    wp_enqueue_media();
    wp_register_style('csop-home-admin-style', false, array(), '1.0.0');
    wp_enqueue_style('csop-home-admin-style');
    wp_add_inline_style('csop-home-admin-style', csop_home_admin_css());
    wp_register_script('csop-home-admin-script', false, array('jquery'), '1.0.0', true);
    wp_enqueue_script('csop-home-admin-script');
    wp_add_inline_script('csop-home-admin-script', csop_home_admin_js());
}

function csop_home_settings_page() {
    if (!current_user_can('manage_options')) return;
    if (isset($_POST['csop_home_save'])) {
        check_admin_referer('csop_home_save_action');
        $raw = isset($_POST['csop_home']) && is_array($_POST['csop_home']) ? $_POST['csop_home'] : array();
        update_option(csop_home_option_name(), csop_home_sanitize_options($raw));
        echo '<div class="notice notice-success is-dismissible"><p>首页可视化内容已保存。</p></div>';
    }
    if (isset($_POST['csop_home_reset'])) {
        check_admin_referer('csop_home_save_action');
        update_option(csop_home_option_name(), csop_home_default_options());
        echo '<div class="notice notice-success is-dismissible"><p>首页已恢复 demo 默认内容。</p></div>';
    }
    $content_html = csop_home_current_html();
    ?>
    <div class="wrap csop-home-admin-wrap">
        <h1>csvosupport 首页</h1>
        <p>前台短代码：<code>[csop_home]</code>。编辑左侧内容后，右侧会实时刷新预览；保存后前台生效。</p>
        <form method="post" novalidate>
            <?php wp_nonce_field('csop_home_save_action'); ?>
            <div class="csop-home-admin-layout">
                <div class="csop-home-admin-card csop-home-editor-card">
                    <div class="csop-home-admin-head">
                        <div>
                            <h2>首页内容编辑</h2>
                            <p>保留 demo 的 GenerateBlocks 结构、图片、Tab 和按钮样式；客户可直接改文字、图片 URL、按钮链接和模块 HTML。</p>
                        </div>
                        <a class="button" href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener">前台预览</a>
                    </div>
                    <label class="csop-home-field">
                        <span>首页主体 HTML</span>
                        <textarea name="csop_home[content_html]" data-csop-home-html><?php echo esc_textarea($content_html); ?></textarea>
                    </label>
                    <div class="csop-home-save-bar">
                        <button type="submit" name="csop_home_save" class="button button-primary button-hero">保存首页内容</button>
                        <button type="submit" name="csop_home_reset" class="button button-secondary button-hero" onclick="return confirm('确定恢复首页 demo 默认内容？')">恢复默认</button>
                    </div>
                </div>
                <div class="csop-home-admin-card csop-home-admin-preview">
                    <div class="csop-home-preview-head">
                        <div>
                            <strong>实时可视化预览</strong>
                            <span>左侧内容变更后会同步到右侧预览区域</span>
                        </div>
                        <button type="button" class="button" data-csop-home-fit>自动适配</button>
                    </div>
                    <div class="csop-home-preview-frame">
                        <div class="csop-home-preview-scale" data-csop-home-preview><?php echo csop_home_render(true); ?></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php
}

function csop_home_render($preview = false) {
    ob_start();
    echo csop_home_front_css();
    ?>
    <main class="csop-home-demo <?php echo $preview ? 'csop-home-preview' : ''; ?>">
        <?php echo csop_home_current_html(); ?>
    </main>
    <?php
    echo csop_home_front_js();
    return ob_get_clean();
}

function csop_home_front_css() {
    static $done = false;
    if ($done) return '';
    $done = true;
    return <<<'CSS'
<style id="csop-home-front-css">

.csop-home-demo,.csop-home-demo *,.csop-home-demo *:before,.csop-home-demo *:after{box-sizing:border-box}
.csop-home-demo{--contrast:#212121;--contrast-2:#2f4468;--contrast-3:#878787;--base:#fafafa;--base-2:#f7f8f9;--base-3:#ffffff;--accent:#242226;--accent-2:#1b78e2;--accent-hover:#35343a;--highlight:#83b0de;background:var(--base);color:var(--contrast);font-family:"Open Sans",Arial,sans-serif;font-size:17px;line-height:1.5;width:100%;overflow:hidden}
.csop-home-demo a{color:#1b78e2;text-decoration:none}.csop-home-demo a:hover{color:var(--accent-hover)}
.csop-home-demo h1{font-weight:600;font-size:40px;color:var(--contrast-2)}.csop-home-demo h2{font-weight:600;font-size:30px;color:var(--contrast-2)}.csop-home-demo h3{font-size:20px;color:var(--contrast-2)}
.csop-home-demo .inside-article{background:transparent;border-radius:0;box-shadow:none;padding:0;margin:0;max-width:none;overflow:hidden}
.csop-home-demo .entry-content>*:first-child{margin-top:0}.csop-home-demo .entry-content>*:last-child{margin-bottom:0}
.csop-home-demo .wp-block-buttons{display:flex;flex-wrap:wrap;gap:.5em;margin:1.5em 0}.csop-home-demo .wp-block-button{display:inline-block}.csop-home-demo .wp-block-button__link{display:inline-block;padding:10px 24px;background:var(--accent);color:#fff;border-radius:999px;font-size:15px;text-align:center}.csop-home-demo .has-base-3-color{color:var(--base-3)!important}.csop-home-demo .has-accent-2-background-color{background-color:var(--accent-2)!important}.csop-home-demo .has-contrast-color{color:var(--contrast)!important}.csop-home-demo .has-accent-color{color:var(--accent)!important}
.csop-home-demo .wp-block-list{margin-top:0}.csop-home-demo figure{margin:0 0 1.5em}.csop-home-demo .wp-block-image img{max-width:100%;height:auto;display:block}.csop-home-demo .wp-element-caption{font-size:13px;color:var(--contrast-3);text-align:center;margin-top:.5em}
.csop-home-demo .wp-block-gallery{display:flex;flex-wrap:wrap;gap:.5em;margin:0 0 1.5em;padding:0}.csop-home-demo .wp-block-gallery.has-nested-images figure.wp-block-image{box-sizing:border-box;display:flex;flex-direction:column;flex-grow:1;justify-content:center;max-width:100%;position:relative;width:calc(25% - .5em)}.csop-home-demo .wp-block-gallery.has-nested-images figure.wp-block-image img{width:100%;height:100%;object-fit:cover;border-radius:2px}.csop-home-demo .wp-block-gallery.columns-4 figure.wp-block-image{width:calc(25% - .5em)}
.csop-home-demo .gb-tabs__item{display:none}.csop-home-demo .gb-tabs__item.gb-tabs__item-open{display:block}.csop-home-demo .gb-tabs__menu-item{cursor:pointer;user-select:none}.csop-home-demo .gb-tabs__menu-item:focus{outline:2px solid var(--accent-2);outline-offset:2px}
.csop-home-demo .csop-reveal{opacity:0;transform:translateY(22px);transition:opacity .72s ease,transform .72s ease;will-change:opacity,transform}.csop-home-demo .csop-reveal.csop-in{opacity:1;transform:none}.csop-home-demo .csop-reveal-delay-1{transition-delay:.08s}.csop-home-demo .csop-reveal-delay-2{transition-delay:.16s}.csop-home-demo .csop-reveal-delay-3{transition-delay:.24s}@media (prefers-reduced-motion:reduce){.csop-home-demo .csop-reveal{opacity:1;transform:none;transition:none}}
.csop-home-demo img{max-width:100%}@media(max-width:767px){.csop-home-demo .inside-article{margin:0;padding:0}.csop-home-demo h1{font-size:32px}.csop-home-demo .wp-block-gallery.has-nested-images figure.wp-block-image,.csop-home-demo .wp-block-gallery.columns-4 figure.wp-block-image{width:calc(50% - .5em)}}

:root{--gb-container-width:1200px;}.gb-container .wp-block-image img{vertical-align:middle;}.gb-grid-wrapper .wp-block-image{margin-bottom:0;}.gb-highlight{background:none;}.gb-shape{line-height:0;}.gb-container-link{position:absolute;top:0;right:0;bottom:0;left:0;z-index:99;}.gb-element-0639ac5f{background-blend-mode:normal,normal;background:linear-gradient(to left,rgba(0,0,0,0.7) 0%,rgba(0,0,0,0.7) 100%),var(--inline-bg-image) center /cover no-repeat}.gb-element-9eda365a{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:100px 20px}.gb-element-77cfd298{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:100px 20px 40px 20px}.gb-element-ba61be1d{margin-bottom:30px;margin-left:auto;margin-right:auto;width:10%;border-top:3px solid var(--accent-2)}.gb-element-8420f9a6{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:60px 20px 80px 20px}@media (max-width:767px){.gb-element-8420f9a6{padding-bottom:60px;padding-top:60px}}.gb-element-44aeb402{align-items:center;border-bottom-color:var(--global-color-7);margin-bottom:10px;text-align:center}.gb-element-8526df89{margin-bottom:30px;margin-left:auto;margin-right:auto;width:10%;border-top:3px solid var(--accent-2)}.gb-element-644ba81e{column-gap:2em;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));row-gap:2em}@media (max-width:767px){.gb-element-644ba81e{grid-template-columns:1fr;row-gap:1.5em}}.gb-element-04df542a{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 15px 10px}.gb-element-04df542a:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-c0b03233{text-align:center}.gb-element-1aa32bcf{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 15px 10px}.gb-element-1aa32bcf:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-d54e97d1{text-align:center}.gb-element-3ee3a811{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 15px 10px}.gb-element-3ee3a811:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-4347e508{text-align:center}.gb-element-3c706259{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 15px 10px}.gb-element-3c706259:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-6288e754{text-align:center}.gb-element-4c34f39e{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 15px 10px}.gb-element-4c34f39e:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-3276f866{text-align:center}.gb-element-335115d4{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;text-align:center;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 15px 10px}.gb-element-335115d4:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-e33fac9c{text-align:center}.gb-element-0fe52152{background-attachment:fixed;background-blend-mode:normal;background-image:var(--inline-bg-image);background-position:center;background-repeat:no-repeat;background-size:cover;padding-bottom:100px;padding-top:100px}.gb-element-93badf17{background-color:rgba(27,120,227,0.72);color:var(--base-3);margin-left:auto;margin-right:auto;max-width:1160px;text-align:center;border-radius:5px;padding:60px 20px}.gb-element-bf9c793f{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:80px 20px 40px 20px}@media (max-width:767px){.gb-element-bf9c793f{padding-bottom:60px;padding-top:60px}}.gb-element-03bb26a5{align-items:center;border-bottom-color:var(--global-color-7)}.gb-element-817d0990{margin-bottom:30px;margin-left:auto;margin-right:auto;width:10%;border-top:3px solid var(--accent-2)}.gb-element-7fbf0c12{column-gap:1.5em;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));row-gap:1.5em}@media (max-width:767px){.gb-element-7fbf0c12{grid-template-columns:1fr;row-gap:1.5em}}.gb-element-dcfdb50b{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 10px 8px}.gb-element-dcfdb50b:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-080f0e69{padding-left:30px;padding-right:8px;text-align:center}.gb-element-f952711d{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 10px 8px}.gb-element-f952711d:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-9b25b2c5{padding-left:30px;padding-right:8px;text-align:center}.gb-element-556bbf6d{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 10px 8px}.gb-element-556bbf6d:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-f01effe1{padding-left:30px;padding-right:8px;text-align:center}.gb-element-bbe44f83{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 10px 8px}.gb-element-bbe44f83:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-0945459f{padding-left:30px;padding-right:8px;text-align:center}.gb-element-cc34185d{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:40px 20px 80px 20px}@media (max-width:767px){.gb-element-cc34185d{padding-bottom:60px;padding-top:60px}}.gb-element-e49b6a68{align-items:center;border-bottom-color:var(--global-color-7)}.gb-element-9704f49b{margin-bottom:30px;margin-left:auto;margin-right:auto;width:10%;border-top:3px solid var(--accent-2)}.gb-element-cc72d6c3{column-gap:1em;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));row-gap:1em}@media (max-width:767px){.gb-element-cc72d6c3{grid-template-columns:1fr;row-gap:1.5em}}.gb-element-0d2725ff{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:20px}.gb-element-0d2725ff:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-0ccb957e{column-gap:1em;display:grid;grid-template-columns:1fr 3fr;row-gap:1em}@media (max-width:767px){.gb-element-0ccb957e{grid-template-columns:1fr}}.gb-element-5e4c89d8{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:20px}.gb-element-5e4c89d8:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-ab84b3b5{column-gap:1em;display:grid;grid-template-columns:1fr 3fr;row-gap:1em}@media (max-width:767px){.gb-element-ab84b3b5{grid-template-columns:1fr}}.gb-element-83eea067{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:20px}.gb-element-83eea067:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-656e9daf{column-gap:1em;display:grid;grid-template-columns:1fr 3fr;row-gap:1em}@media (max-width:767px){.gb-element-656e9daf{grid-template-columns:1fr}}.gb-element-1cf72096{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:20px}.gb-element-1cf72096:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-a4f0c7fe{column-gap:1em;display:grid;grid-template-columns:1fr 3fr;row-gap:1em}@media (max-width:767px){.gb-element-a4f0c7fe{grid-template-columns:1fr}}.gb-element-38c69647{box-shadow:0px 0px 4px 2px rgba(0,0,0,0.1);margin-bottom:60px;margin-left:auto;margin-right:auto;max-width:1160px;border:1px solid rgba(135,135,135,0.29);border-radius:10px;padding:30px 20px}.gb-element-ca749091{box-shadow:0px 0px 5px 3px rgba(0,0,0,0.1);margin-bottom:30px;border:1px solid rgba(135,135,135,0.34);border-radius:6px;padding:20px}.gb-element-6f418cf9{box-shadow:0px 0px 5px 3px rgba(0,0,0,0.1);margin-bottom:30px;border:1px solid rgba(135,135,135,0.34);border-radius:6px;padding:20px 20px 10px 20px}.gb-element-80d35441{background-color:#f6f6f6;border-top:1px solid rgba(135,135,135,0.52)}.gb-element-e3cf7d4a{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:40px 20px}.gb-element-084a3b6e{column-gap:2em;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));row-gap:1em}@media (max-width:767px){.gb-element-084a3b6e{grid-template-columns:1fr}}.gb-element-3ff89166{text-align:center}.gb-element-b12f2b12{text-align:center}.gb-element-13da7ed5{padding-left:60px;text-align:left}@media (max-width:767px){.gb-element-13da7ed5{padding-left:0px}}.gb-element-d10d2533{padding-left:20px}@media (max-width:767px){.gb-element-d10d2533{padding-left:0px}}.gb-element-76365dcf{display:flex;justify-content:space-between;margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:20px}@media (max-width:1024px){.gb-element-76365dcf{align-items:center;flex-direction:column;justify-content:center;row-gap:20px}}.gb-element-6dacc793{column-gap:15px;display:flex}@media (max-width:1024px){.gb-element-6dacc793{order:-1}}.gb-element-7de49033{background-color:var(--base-2)}.gb-element-d4b812ad{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:20px}.gb-element-511ef82e{column-gap:1em;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));row-gap:1em}@media (max-width:767px){.gb-element-511ef82e{grid-template-columns:1fr}}.gb-text-193bb6ca{color:var(--base-3);margin-bottom:40px}.gb-text-e24a13ba{color:var(--accent-2);font-size:55px;letter-spacing:5px;line-height:1.5;margin-bottom:40px}.gb-text-169e8bed{color:var(--base-3)}.gb-text-f2f3f905{color:var(--contrast);font-size:50px;font-weight:400;margin-bottom:30px;text-align:center}.gb-text-0e0453c1{font-size:40px;font-weight:500;margin-bottom:30px;text-align:center}@media (max-width:767px){.gb-text-0e0453c1{font-size:25px;font-weight:600}}.gb-text-a0a8ba26{column-gap:0.5em;display:block;font-weight:600;text-align:center}.gb-text-a0a8ba26 .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-a0a8ba26{font-size:24px}}.gb-text-495f982c{column-gap:0.5em;display:block;font-weight:600;text-align:center}.gb-text-495f982c .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-495f982c{font-size:24px}}.gb-text-0dc40ddd{column-gap:0.5em;display:block;font-weight:600;text-align:center}.gb-text-0dc40ddd .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-0dc40ddd{font-size:24px}}.gb-text-afe09214{column-gap:0.5em;display:block;font-weight:600;text-align:center}.gb-text-afe09214 .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-afe09214{font-size:24px}}.gb-text-967f6727{column-gap:0.5em;display:block;font-weight:600;text-align:center}.gb-text-967f6727 .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-967f6727{font-size:24px}}.gb-text-0f3d2feb{column-gap:0.5em;display:block;font-weight:600;text-align:center}.gb-text-0f3d2feb .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-0f3d2feb{font-size:24px}}.gb-text-87ab5dab{color:var(--base-3);text-align:center}.gb-text-ee938c30{margin-bottom:0px}.gb-text-5d3337d5{color:var(--contrast);font-size:40px;font-weight:500;margin-bottom:30px;text-align:center}@media (max-width:767px){.gb-text-5d3337d5{font-size:25px;font-weight:600}}.gb-text-5fe57c83{column-gap:0.5em;display:block;font-weight:600;padding-left:20px;text-align:center}.gb-text-5fe57c83 .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-5fe57c83{font-size:24px}}.gb-text-aae11c36{column-gap:0.5em;display:block;font-weight:600;padding-left:20px;text-align:center}.gb-text-aae11c36 .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-aae11c36{font-size:24px}}.gb-text-82f291f1{column-gap:0.5em;display:block;font-weight:600;padding-left:20px;text-align:center}.gb-text-82f291f1 .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-82f291f1{font-size:24px}}.gb-text-dea3d70e{column-gap:0.5em;display:block;font-weight:600;padding-left:20px;text-align:center}.gb-text-dea3d70e .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-dea3d70e{font-size:24px}}.gb-text-d42e626f{color:var(--contrast);font-size:40px;font-weight:500;margin-bottom:30px;text-align:center}@media (max-width:767px){.gb-text-d42e626f{font-size:25px;font-weight:600}}.gb-text-75fb0c21{color:var(--accent-hover);font-size:12px;min-height:200px}.gb-text-aa90639b{color:var(--accent-hover);font-size:12px;min-height:200px}.gb-text-c48aea3b{color:var(--accent-hover);font-size:12px;min-height:200px}.gb-text-5434fef7{color:var(--accent-hover);font-size:12px;min-height:200px}.gb-text-20281697{color:var(--contrast);font-size:38px;font-weight:400;padding-left:10px;text-align:left;width:350px;border-left:4px solid var(--accent-2)}@media (max-width:767px){.gb-text-20281697{font-size:25px;font-weight:600}}.gb-text-e90ae470{font-size:15px;font-weight:600;margin-bottom:0px}.gb-text-286dab72{font-size:15px;font-weight:600;margin-bottom:0px}.gb-text-a47aa7db{font-size:15px;font-weight:600;margin-bottom:0px}.gb-text-74b2cf67{font-size:15px;font-weight:600;margin-bottom:0px}.gb-text-33432665{font-weight:700;text-align:left}.gb-text-09cfc29e{margin-bottom:10px}.gb-text-0897f7de{margin-bottom:10px}.gb-text-b5d58ce0{margin-bottom:10px}.gb-text-1a21da79{margin-bottom:0px}.gb-text-c3994867{font-size:15px;margin-bottom:0px}@media (max-width:767px){.gb-text-c3994867{text-align:center}}.gb-text-7720e281{display:block;font-size:15px;margin-bottom:0px;text-align:right}.gb-text-7720e281 a{color:var(--contrast-2)}.gb-text-7720e281 a:hover{color:var(--contrast-3);font-size:15px}.gb-shape-67aa0073{align-items:center;background-color:var(--accent-2);border-bottom-style:solid;border-bottom-width:0px;border-left-style:solid;border-left-width:0px;border-right-style:solid;border-right-width:0px;border-top-style:solid;border-top-width:0px;display:inline-flex;font-size:25px;font-weight:700;justify-content:center;margin-bottom:20px;object-fit:fill;border-radius:100px;padding:15px}.gb-shape-67aa0073 svg{fill:currentColor;height:40px;width:40px;color:var(--base-3);font-size:30px}.gb-shape-26a52e65{align-items:center;background-color:var(--accent-2);border-bottom-style:solid;border-bottom-width:0px;border-left-style:solid;border-left-width:0px;border-right-style:solid;border-right-width:0px;border-top-style:solid;border-top-width:0px;display:inline-flex;font-size:25px;font-weight:700;justify-content:center;margin-bottom:20px;object-fit:fill;border-radius:100px;padding:15px}.gb-shape-26a52e65 svg{fill:currentColor;height:40px;width:40px;color:var(--base-3);font-size:30px}.gb-shape-a5802c71{align-items:center;background-color:var(--accent-2);border-bottom-style:solid;border-bottom-width:0px;border-left-style:solid;border-left-width:0px;border-right-style:solid;border-right-width:0px;border-top-style:solid;border-top-width:0px;display:inline-flex;font-size:25px;font-weight:700;justify-content:center;margin-bottom:20px;object-fit:fill;border-radius:100px;padding:15px}.gb-shape-a5802c71 svg{fill:currentColor;height:40px;width:40px;color:var(--base-3);font-size:30px}.gb-shape-0475d889{align-items:center;background-color:var(--accent-2);border-bottom-style:solid;border-bottom-width:0px;border-left-style:solid;border-left-width:0px;border-right-style:solid;border-right-width:0px;border-top-style:solid;border-top-width:0px;display:inline-flex;font-size:25px;font-weight:700;justify-content:center;margin-bottom:20px;object-fit:fill;border-radius:100px;padding:15px}.gb-shape-0475d889 svg{fill:currentColor;height:40px;width:40px;color:var(--base-3);font-size:30px}.gb-shape-91579803{align-items:center;background-color:var(--accent-2);border-bottom-style:solid;border-bottom-width:0px;border-left-style:solid;border-left-width:0px;border-right-style:solid;border-right-width:0px;border-top-style:solid;border-top-width:0px;display:inline-flex;font-size:25px;font-weight:700;justify-content:center;margin-bottom:20px;object-fit:fill;border-radius:100px;padding:15px}.gb-shape-91579803 svg{fill:currentColor;height:40px;width:40px;color:var(--base-3);font-size:30px}.gb-shape-c6ccb341{align-items:center;background-color:var(--accent-2);border-bottom-style:solid;border-bottom-width:0px;border-left-style:solid;border-left-width:0px;border-right-style:solid;border-right-width:0px;border-top-style:solid;border-top-width:0px;display:inline-flex;font-size:25px;font-weight:700;justify-content:center;margin-bottom:20px;object-fit:fill;border-radius:100px;padding:15px}.gb-shape-c6ccb341 svg{fill:currentColor;height:40px;width:40px;color:var(--base-3);font-size:30px}.gb-shape-280325fc{align-items:center;display:inline-flex;font-size:25px;font-weight:700;height:40px;justify-content:center;margin-bottom:20px;width:40px;border-radius:100px}.gb-shape-280325fc svg{fill:currentColor;height:auto;width:60px;color:var(--accent-2);font-size:30px}.gb-shape-41fb84cb{align-items:center;display:inline-flex;font-size:25px;font-weight:700;height:40px;justify-content:center;margin-bottom:20px;margin-left:auto;width:40px;border-radius:100px}.gb-shape-41fb84cb svg{fill:currentColor;height:auto;width:60px;color:var(--accent-2);font-size:30px}.gb-shape-3ee27093{align-items:center;display:inline-flex;font-size:25px;font-weight:700;height:40px;justify-content:center;margin-bottom:20px;margin-left:auto;width:40px;border-radius:100px}.gb-shape-3ee27093 svg{fill:currentColor;height:auto;width:60px;color:var(--accent-2);font-size:30px}.gb-shape-5ef9faca{align-items:center;display:inline-flex;font-size:25px;font-weight:700;height:40px;justify-content:center;margin-bottom:20px;margin-left:auto;width:40px;border-radius:100px}.gb-shape-5ef9faca svg{fill:currentColor;height:auto;width:60px;color:var(--accent-2);font-size:30px}.gb-media-176e053f{background-color:var(--contrast-3);height:auto;max-width:100%;object-fit:cover;width:auto;border-radius:100px}.gb-media-c8bb6e14{background-color:var(--contrast-3);height:auto;max-width:100%;object-fit:cover;width:auto;border-radius:100px}.gb-media-25b77a9a{background-color:var(--contrast-3);height:auto;max-width:100%;object-fit:cover;width:auto;border-radius:100px}.gb-media-6e39e858{background-color:var(--contrast-3);height:auto;max-width:100%;object-fit:cover;width:auto;border-radius:100px}.gb-media-dab60b27{height:auto;max-width:100%;object-fit:cover;width:100%}.gb-media-1aec2793{height:auto;max-width:100%;object-fit:cover;width:100%}.gb-tabs-513a33df{column-gap:20px;display:flex;flex-direction:column;row-gap:20px}.gb-tabs__menu-6aae225b{align-items:center;column-gap:15px;display:flex;justify-content:center}@media (max-width:767px){.gb-tabs__menu-6aae225b{max-width:100%;overflow-x:auto}}.gb-tabs__menu-item-4a18984e{background-color:var(--base);box-shadow:0px 0px 10px 1px rgba(0,0,0,0.1);color:#000000;margin-bottom:0px;transition:all 0.2s ease 0s;border:2px solid rgba(135,135,135,0.26);border-radius:6px;padding:8px 20px}.gb-tabs__menu-item-4a18984e:is(.gb-block-is-current,.gb-block-is-current:hover,.gb-block-is-current:focus){background-color:var(--accent-2);color:var(--base)}.gb-tabs__menu-item-4a18984e:is(:hover,:focus){color:#000000;background-color:#000000;border:2px solid var(--accent-2)}@media (max-width:767px){.gb-tabs__menu-item-4a18984e{flex-grow:1;flex-shrink:0}}.gb-tabs__menu-item-87819c3d{background-color:var(--base);box-shadow:0px 0px 10px 1px rgba(0,0,0,0.1);color:#000000;margin-bottom:0px;transition:all 0.2s ease 0s;border:2px solid rgba(135,135,135,0.26);border-radius:6px;padding:8px 20px}.gb-tabs__menu-item-87819c3d:is(.gb-block-is-current,.gb-block-is-current:hover,.gb-block-is-current:focus){background-color:var(--accent-2);color:var(--base)}.gb-tabs__menu-item-87819c3d:is(:hover,:focus){background-color:#fafafa;color:#000000;border:2px solid var(--accent-2)}@media (max-width:767px){.gb-tabs__menu-item-87819c3d{flex-grow:1;flex-shrink:0}}.gb-tabs__menu-item-13e7b89e{background-color:var(--base);box-shadow:0px 0px 10px 1px rgba(0,0,0,0.1);color:#000000;margin-bottom:0px;transition:all 0.2s ease 0s;border:2px solid rgba(135,135,135,0.26);border-radius:6px;padding:8px 20px}.gb-tabs__menu-item-13e7b89e:is(.gb-block-is-current,.gb-block-is-current:hover,.gb-block-is-current:focus){background-color:var(--accent-2);color:var(--base)}.gb-tabs__menu-item-13e7b89e:is(:hover,:focus){background-color:#fafafa;color:#000000;border:2px solid var(--accent-2)}@media (max-width:767px){.gb-tabs__menu-item-13e7b89e{flex-grow:1;flex-shrink:0}}.gb-tabs__menu-item-85742302{background-color:var(--base);box-shadow:0px 0px 10px 1px rgba(0,0,0,0.1);color:#000000;margin-bottom:0px;transition:all 0.2s ease 0s;border:2px solid rgba(135,135,135,0.26);border-radius:6px;padding:8px 20px}.gb-tabs__menu-item-85742302:is(.gb-block-is-current,.gb-block-is-current:hover,.gb-block-is-current:focus){background-color:var(--accent-2);color:var(--base)}.gb-tabs__menu-item-85742302:is(:hover,:focus){background-color:#fafafa;color:#000000;border:2px solid var(--accent-2)}@media (max-width:767px){.gb-tabs__menu-item-85742302{flex-grow:1;flex-shrink:0}}
</style>
CSS;
}

function csop_home_admin_css() {
    return <<<'CSS'

.csop-home-admin-wrap .csop-home-admin-layout{display:grid;grid-template-columns:minmax(420px,560px) 1fr;gap:24px;align-items:start}.csop-home-admin-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.06)}.csop-home-admin-card code{font-size:13px}.csop-home-admin-head,.csop-home-preview-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:14px}.csop-home-admin-head h2{margin:0 0 8px}.csop-home-admin-head p,.csop-home-preview-head span{color:#667085;margin:0;line-height:1.55}.csop-home-field{display:grid;gap:8px}.csop-home-field span{font-weight:700}.csop-home-field textarea{width:100%;min-height:620px;font-family:Consolas,Monaco,monospace;font-size:12px;line-height:1.55;border:1px solid #cfd6e4;border-radius:8px;padding:12px;resize:vertical}.csop-home-save-bar{position:sticky;bottom:0;display:flex;gap:10px;flex-wrap:wrap;background:#fff;border-top:1px solid #eef0f4;margin:16px -18px -18px;padding:14px 18px}.csop-home-admin-preview{position:sticky;top:32px}.csop-home-preview-frame{height:760px;overflow:auto;background:#eef0f5;padding:22px;border-radius:8px}.csop-home-preview-scale{width:1440px;transform:scale(.58);transform-origin:top left;background:#fafafa;box-shadow:0 14px 42px rgba(15,23,42,.2);min-height:900px}.csop-home-demo.csop-preview-highlight{outline:3px solid #1b78e2;outline-offset:4px}@media(max-width:1200px){.csop-home-admin-wrap .csop-home-admin-layout{grid-template-columns:1fr}.csop-home-admin-preview{position:static}.csop-home-preview-frame{height:640px}}
CSS;
}

function csop_home_admin_js() {
    return <<<'JS'
jQuery(function($){
  var textarea = $('[data-csop-home-html]');
  var preview = $('[data-csop-home-preview] .csop-home-demo');
  var timer = null;

  function refreshPreview(){
    if (!textarea.length || !preview.length) return;
    preview.html(textarea.val());
    preview.addClass('csop-preview-highlight');
    window.setTimeout(function(){ preview.removeClass('csop-preview-highlight'); }, 520);
  }

  textarea.on('input', function(){
    window.clearTimeout(timer);
    timer = window.setTimeout(refreshPreview, 240);
  });

  $('[data-csop-home-fit]').on('click', function(){
    var frame = $('.csop-home-preview-frame');
    var scale = $('.csop-home-preview-scale');
    if (!frame.length || !scale.length) return;
    var next = frame.width() < 980 ? 0.44 : 0.58;
    scale.css('transform', 'scale(' + next + ')');
  });
});
JS;
}

function csop_home_front_js() {
    static $done = false;
    if ($done) return '';
    $done = true;
    return <<<'JS'
<script id="csop-home-front-js">
(function(){
  function initReveal(){
    var root = document.querySelector('.csop-home-demo');
    if (!root) return;
    var targets = root.querySelectorAll('.entry-content > div, .gb-element-644ba81e > *, .gb-element-7fbf0c12 > *, .gb-element-cc72d6c3 > *, .gb-element-511ef82e > *');
    if (!targets.length) return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      targets.forEach(function(el){ el.classList.add('csop-in'); });
      return;
    }
    targets.forEach(function(el, index){
      el.classList.add('csop-reveal', 'csop-reveal-delay-' + (index % 4));
    });
    if (!('IntersectionObserver' in window)) {
      targets.forEach(function(el){ el.classList.add('csop-in'); });
      return;
    }
    var observer = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if (!entry.isIntersecting) return;
        entry.target.classList.add('csop-in');
        observer.unobserve(entry.target);
      });
    }, {threshold: 0.12, rootMargin: '0px 0px -8% 0px'});
    targets.forEach(function(el){ observer.observe(el); });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initReveal); else initReveal();
  document.addEventListener('click', function(event){
    var menuItem = event.target.closest('.csop-home-demo .gb-tabs__menu-item');
    if (!menuItem) return;
    var tabs = menuItem.closest('.gb-tabs');
    if (!tabs) return;
    var menuItems = Array.prototype.slice.call(tabs.querySelectorAll('.gb-tabs__menu-item'));
    var index = menuItems.indexOf(menuItem);
    if (index < 0) return;
    menuItems.forEach(function(item){ item.classList.remove('gb-block-is-current'); });
    menuItem.classList.add('gb-block-is-current');
    var panels = tabs.querySelectorAll('.gb-tabs__item');
    panels.forEach(function(panel, i){ panel.classList.toggle('gb-tabs__item-open', i === index); });
    tabs.setAttribute('data-opened-tab', String(index + 1));
  });
})();
</script>
JS;
}

function csop_home_demo_article() {
    $html = <<<'HTML'
<article id="post-2756" class="post-2756 page type-page status-publish" itemtype="https://schema.org/CreativeWork" itemscope>
	<div class="inside-article">
		
		<div class="entry-content" itemprop="text">
			
<div class="gb-element-0639ac5f" style="--inline-bg-image: url(https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-012-4685.webp)">
<div class="gb-element-9eda365a">
<h3 class="gb-text gb-text-193bb6ca">csvosupport 工作室｜OA 代写｜VO 代面｜面试辅导</h3>



<h2 class="gb-text gb-text-e24a13ba">来自硅谷的靠谱面试辅助服务<br>30分钟免费语音咨询<br>给你最优质的海外求职辅助</h2>



<p class="gb-text gb-text-169e8bed">csvosupport 成立于2017年，团队成员包括来自大厂科技公司的工程师、研究人员，以及有ACM算法竞赛背景的导师，致力于提供最优质的面试辅导、OA代做、VO辅助和代面试服务。<br><br>我们专注服务科技行业的求职全过程。自成立以来，我们坚持以高质量辅导和透明服务为核心，坚持公开透明每一位导师的学术和工业界背景，立志成为VO辅助和代面领域的领头羊。</p>



<div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex">
<div class="wp-block-button has-custom-width wp-block-button__width-25 is-style-fill"><a class="wp-block-button__link has-base-3-color has-accent-2-background-color has-text-color has-background has-link-color wp-element-button" href="/contact/" style="border-top-left-radius:30px;border-top-right-radius:30px;border-bottom-left-radius:30px;border-bottom-right-radius:30px">联系我们</a></div>
</div>
</div>
</div>



<div>
<div class="gb-element-77cfd298">
<h2 class="gb-text gb-text-f2f3f905">北美最强的面试辅助团队</h2>



<div class="gb-element-ba61be1d"></div>



<p class="gb-text">我们凭借多年北美及海外求职实战经验，打造了业内极具口碑的 「OA代写」、「模拟面试」、「VO代面」、「面试辅助」一体化方案。从笔试到视频面试，从技术细节到表达策略，我们深知大厂招聘的每一道关卡，能够为你量身定制最优解法。<br><br><br>过去几年，我们已帮助数百位客户成功拿下 Amazon、Bloomberg、Pinterest、Meta、Stripe、Coinbase、DoorDash、Optiver、Citadel 等顶级公司的 Offer，不仅稳稳进入目标公司，更收获了远超行业平均水平的高额薪资。我们的客户中，不乏年薪double的工程师，也有实现职业跨越、直接跃升为senior岗位的案例。<br><br><br>我们不是流水线服务，而是你的定制化求职伙伴：每一位客户都会配备专属顾问与技术导师，全程一对一指导，确保你的每一次OA与VO表现都能精准击中招聘方的需求。</p>



<div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex">
<div class="wp-block-button has-custom-width wp-block-button__width-25 is-style-fill"><a class="wp-block-button__link has-base-3-color has-accent-2-background-color has-text-color has-background has-link-color wp-element-button" href="/price/" style="border-top-left-radius:30px;border-top-right-radius:30px;border-bottom-left-radius:30px;border-bottom-right-radius:30px">了解更多服务细节</a></div>
</div>
</div>
</div>



<div>
<div class="gb-element-8420f9a6">
<div class="gb-element-44aeb402">
<h2 class="gb-text gb-text-0e0453c1">服务范围</h2>



<div class="gb-element-8526df89"></div>
</div>



<div class="gb-element-644ba81e">
<a class="gb-element-04df542a" href="#">
<div class="gb-element-c0b03233">
<span class="gb-shape gb-shape-67aa0073"><svg viewBox="0 0 640 512" xmlns="http://www.w3.org/2000/svg"><path d="M64 96c0-35.3 28.7-64 64-64l384 0c35.3 0 64 28.7 64 64l0 240-64 0 0-240-384 0 0 240-64 0 0-240zM0 403.2C0 392.6 8.6 384 19.2 384l601.6 0c10.6 0 19.2 8.6 19.2 19.2 0 42.4-34.4 76.8-76.8 76.8L76.8 480C34.4 480 0 445.6 0 403.2zM281 209l-31 31 31 31c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-48-48c-9.4-9.4-9.4-24.6 0-33.9l48-48c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9zM393 175l48 48c9.4 9.4 9.4 24.6 0 33.9l-48 48c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l31-31-31-31c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-a0a8ba26">OA代做</h3>



<ul style="font-size:16px;font-style:normal;font-weight:400" class="wp-block-list has-contrast-color has-text-color has-link-color wp-elements-b58a482e960967c15000248d162a6ca0">
<li>OA 代做保过，竞赛大神带你满分通过</li>



<li>秒杀所有edge case，确保满分提交</li>



<li>精通大厂当年最新题库，全覆盖！</li>



<li>最优解 + 高可读性，品质双重保证</li>



<li>199 USD起</li>
</ul>
</a>



<a class="gb-element-1aa32bcf" href="#">
<div class="gb-element-d54e97d1">
<span class="gb-shape gb-shape-26a52e65"><svg viewBox="0 0 640 512" xmlns="http://www.w3.org/2000/svg"><path d="M192 384c53 0 96 43 96 96 0 17.7-14.3 32-32 32L32 512c-17.7 0-32-14.3-32-32 0-53 43-96 96-96l96 0zM544 32c35.3 0 64 28.7 64 64l0 288c0 33.1-25.2 60.4-57.5 63.7l-6.5 .3-211.1 0c-5.1-24.2-16.3-46.1-32.1-64l51.2 0 0-32c0-17.7 14.3-32 32-32l96 0c17.7 0 32 14.3 32 32l0 32 32 0 0-288-352 0 0 57.3c-14.8-6-31-9.3-48-9.3-5.4 0-10.8 .3-16 1l0-49c0-35.3 28.7-64 64-64l352 0zM144 352a80 80 0 1 1 0-160 80 80 0 1 1 0 160z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-495f982c">VO辅助</h3>



<ul style="font-size:16px" class="wp-block-list has-contrast-color has-text-color has-link-color wp-elements-a159239b3c38d4716db1a045e41022ba">
<li>实时传输高质答案，0破绽，满分体验</li>



<li>双导师协同辅助，保障x2，稳定输出</li>



<li>海量辅助案例，Alpha品质，眼见为实</li>



<li>多通道传输，语音/文字同步推送答案</li>



<li>299 USD起</li>
</ul>
</a>



<a class="gb-element-3ee3a811" href="#">
<div class="gb-element-4347e508">
<span class="gb-shape gb-shape-a5802c71"><svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM165.4 321.9c20.4 28 53.4 46.1 90.6 46.1s70.2-18.1 90.6-46.1c7.8-10.7 22.8-13.1 33.5-5.3s13.1 22.8 5.3 33.5C356.3 390 309.2 416 256 416s-100.3-26-129.4-65.9c-7.8-10.7-5.4-25.7 5.3-33.5s25.7-5.4 33.5 5.3zM144 208a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm192-32a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-0dc40ddd">VO代面</h3>



<ul style="font-size:16px" class="wp-block-list has-contrast-color has-text-color has-link-color wp-elements-45d7b5e3bfde10f432a7febaa95e4415">
<li>真人 VO代面，客户无需出镜</li>



<li>免费语音沟通，免费Mock展示</li>



<li>代面导师人均大厂senior+在职</li>



<li>稳OFFER，不只是说说而已</li>



<li>499 USD起</li>
</ul>
</a>



<a class="gb-element-3c706259" href="#">
<div class="gb-element-6288e754">
<span class="gb-shape gb-shape-0475d889"><svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 141.3l0 309.3 .5-.2C311.1 427.7 369.7 416 428.8 416l19.2 0 0-320-19.2 0c-42.2 0-84.1 8.4-123.1 24.6-16.8 7-33.4 13.9-49.7 20.7zM230.9 61.5L256 72 281.1 61.5C327.9 42 378.1 32 428.8 32L464 32c26.5 0 48 21.5 48 48l0 352c0 26.5-21.5 48-48 48l-35.2 0c-50.7 0-100.9 10-147.7 29.5l-12.8 5.3c-7.9 3.3-16.7 3.3-24.6 0l-12.8-5.3C184.1 490 133.9 480 83.2 480L48 480c-26.5 0-48-21.5-48-48L0 80C0 53.5 21.5 32 48 32l35.2 0c50.7 0 100.9 10 147.7 29.5z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-afe09214">简历润色</h3>



<ul style="font-size:16px" class="wp-block-list has-contrast-color has-text-color has-link-color wp-elements-c13d8f6b6f498c4224036a11e991f5fc">
<li>直击工业技术栈，拒绝toy project</li>



<li>100%原创，围绕客户经历量身定做</li>



<li>技术深度挖掘，与招聘JD无缝匹配</li>



<li>大厂视角审阅，HR + HM 二次优化</li>
</ul>
</a>



<a class="gb-element-4c34f39e" href="#">
<div class="gb-element-3276f866">
<span class="gb-shape gb-shape-91579803"><svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M36.4 353.2c4.1-14.6 11.8-27.9 22.6-38.7l181.2-181.2 33.9-33.9c16.6 16.6 51.3 51.3 104 104l33.9 33.9-33.9 33.9-181.2 181.2c-10.7 10.7-24.1 18.5-38.7 22.6L30.4 510.6c-8.3 2.3-17.3 0-23.4-6.2S-1.4 489.3 .9 481L36.4 353.2zm55.6-3.7c-4.4 4.7-7.6 10.4-9.3 16.6l-24.1 86.9 86.9-24.1c6.4-1.8 12.2-5.1 17-9.7L91.9 349.5zm354-146.1c-16.6-16.6-51.3-51.3-104-104L308 65.5C334.5 39 349.4 24.1 352.9 20.6 366.4 7 384.8-.6 404-.6S441.6 7 455.1 20.6l35.7 35.7C504.4 69.9 512 88.3 512 107.4s-7.6 37.6-21.2 51.1c-3.5 3.5-18.4 18.4-44.9 44.9z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-967f6727">面试辅导</h3>



<ul style="font-size:16px" class="wp-block-list has-contrast-color has-text-color has-link-color wp-elements-f04fb69b3910c6fea31582a52d65c14a">
<li>大厂面试官一对一辅导，全链路覆盖</li>



<li>真题实战演练，模拟当下最热考点</li>



<li>个性化刷题路线，传授独家技巧</li>



<li>面试官思维建模，教你识别提问意图</li>
</ul>
</a>



<a class="gb-element-335115d4" href="#">
<div class="gb-element-e33fac9c">
<span class="gb-shape gb-shape-c6ccb341"><svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M241 87.1l15 20.7 15-20.7C296 52.5 336.2 32 378.9 32 452.4 32 512 91.6 512 165.1l0 2.6c0 112.2-139.9 242.5-212.9 298.2-12.4 9.4-27.6 14.1-43.1 14.1s-30.8-4.6-43.1-14.1C139.9 410.2 0 279.9 0 167.7l0-2.6C0 91.6 59.6 32 133.1 32 175.8 32 216 52.5 241 87.1z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-0f3d2feb">我们有哪些优势？</h3>



<ul style="font-size:16px" class="wp-block-list has-contrast-color has-text-color has-link-color wp-elements-83281534e5b98ffd525cac7a37cbc3b3">
<li>我们提供不限时长的免费语音咨询</li>



<li>代面现场录音供您参考，所见即所得</li>



<li>唯一支持&nbsp;senior/staff 代面的机构</li>



<li>我们始终将真诚与责任视为立业之本</li>
</ul>
</a>
</div>
</div>
</div>



<div class="gb-element-0fe52152" style="--inline-bg-image: url(https://csvosupport.com/wp-content/uploads/2026/06/local-ext-04-main-mevia-site-microsoft-2026-sde-online-assessment-cleanup-1.webp)">
<div class="gb-element-93badf17">
<h2 class="gb-text gb-text-87ab5dab">服务范围 &#8211; 专注海外求职面试</h2>



<p class="gb-text">（服务范围不包含中国地区的面试订单）</p>



<p class="gb-text gb-text-ee938c30"><br>OA代写&nbsp; &nbsp;OA代做&nbsp; &nbsp;VO代做&nbsp; &nbsp;代面试&nbsp; &nbsp;面试辅助&nbsp; &nbsp;SDE代面&nbsp; &nbsp;MLE代面试&nbsp; &nbsp;系统设计代面<br>简历润色&nbsp; CV修改&nbsp; 面试Mock&nbsp; 面经分享&nbsp; VO助攻&nbsp; HackerRank代写&nbsp; CodeSignal代做<br><br>Amazon代面&nbsp; &nbsp;亚麻辅助&nbsp; Meta代面试&nbsp; Pinterest代面&nbsp; Bloomberg代面试&nbsp; &nbsp;Uber代面试<br>Citadel代做OA&nbsp; Optiver代面&nbsp; Stripe代面试&nbsp; SnowFlake代做面试&nbsp; Atlassian面试辅助<br>北美大厂代面&nbsp; Coderpad代面 技术面试辅助 北美求职辅导 远程面试辅助&nbsp; 硅谷代面试<br>美国面试辅导 mock面试 模拟面试 BQ辅导 算法辅导 系统设计辅导 SDE培训 MLE培训&nbsp;</p>
</div>
</div>



<div>
<div class="gb-element-bf9c793f">
<div class="gb-element-03bb26a5">
<h2 class="gb-text gb-text-5d3337d5">为什么选择我们</h2>



<div class="gb-element-817d0990"></div>
</div>



<div class="gb-element-7fbf0c12">
<a class="gb-element-dcfdb50b" href="#">
<div class="gb-element-080f0e69">
<span class="gb-shape gb-shape-280325fc"><svg aria-hidden="true" height="1em" width="1em" viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-5fe57c83">「公认的行业领跑者」</h3>



<ul class="wp-block-list has-accent-color has-text-color has-link-color wp-elements-677b633c3679d15aa40aad6bfd2af7a7">
<li>随时可约语音沟通，感受导师硬核实力</li>



<li>提供工业级交付标准的面试方案</li>



<li>海量成功案例，免费观看面试实战演示</li>
</ul>
</a>



<a class="gb-element-f952711d" href="#">
<div class="gb-element-9b25b2c5">
<span class="gb-shape gb-shape-41fb84cb"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512" width="1em" height="1em" aria-hidden="true"><path d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm0 96c48.6 0 88 39.4 88 88s-39.4 88-88 88-88-39.4-88-88 39.4-88 88-88zm0 344c-58.7 0-111.3-26.6-146.5-68.2 18.8-35.4 55.6-59.8 98.5-59.8 2.4 0 4.8.4 7.1 1.1 13 4.2 26.6 6.9 40.9 6.9 14.3 0 28-2.7 40.9-6.9 2.3-.7 4.7-1.1 7.1-1.1 42.9 0 79.7 24.4 98.5 59.8C359.3 421.4 306.7 448 248 448z" fill="currentColor"></path></svg></span>
</div>



<h3 class="gb-text gb-text-aae11c36">「严格筛选的导师团队」</h3>



<ul class="wp-block-list has-accent-color has-text-color has-link-color wp-elements-007f9b3b796afd4e478558cb6d7c3bd2">
<li>汇聚100%浓度的专业方向PhD导师团队</li>



<li>拥有1000+导师库，覆盖各大细分领域</li>



<li>导师「学术+职业」背景信息透明公开</li>
</ul>
</a>



<a class="gb-element-556bbf6d" href="#">
<div class="gb-element-f01effe1">
<span class="gb-shape gb-shape-3ee27093"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="1em" height="1em" aria-hidden="true"><path d="M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zM227.314 387.314l184-184c6.248-6.248 6.248-16.379 0-22.627l-22.627-22.627c-6.248-6.249-16.379-6.249-22.628 0L216 308.118l-70.059-70.059c-6.248-6.248-16.379-6.248-22.628 0l-22.627 22.627c-6.248 6.248-6.248 16.379 0 22.627l104 104c6.249 6.249 16.379 6.249 22.628.001z" fill="currentColor"></path></svg></span>
</div>



<h3 class="gb-text gb-text-82f291f1">「公认的行业领跑者」</h3>



<ul class="wp-block-list has-accent-color has-text-color has-link-color wp-elements-2be89e43f16f27b6f0833b659c51c04f">
<li>严格保障客户隐私，加密存储所有资料</li>



<li>坚持诚信第一，用心完成每一份交付</li>



<li>无限期的售后服务，杜绝一切后顾之忧</li>
</ul>
</a>



<a class="gb-element-bbe44f83" href="#">
<div class="gb-element-0945459f">
<span class="gb-shape gb-shape-5ef9faca"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="1em" height="1em" aria-hidden="true"><path d="M466.5 83.7l-192-80a48.15 48.15 0 0 0-36.9 0l-192 80C27.7 91.1 16 108.6 16 128c0 198.5 114.5 335.7 221.5 380.3 11.8 4.9 25.1 4.9 36.9 0C360.1 472.6 496 349.3 496 128c0-19.4-11.7-36.9-29.5-44.3zM256.1 446.3l-.1-381 175.9 73.3c-3.3 151.4-82.1 261.1-175.8 307.7z" fill="currentColor"></path></svg></span>
</div>



<h3 class="gb-text gb-text-dea3d70e">「公认的行业领跑者」</h3>



<ul class="wp-block-list has-accent-color has-text-color has-link-color wp-elements-2be89e43f16f27b6f0833b659c51c04f">
<li>严格保障客户隐私，加密存储所有资料</li>



<li>坚持诚信第一，用心完成每一份交付</li>



<li>无限期的售后服务，杜绝一切后顾之忧</li>
</ul>
</a>
</div>
</div>
</div>



<div>
<div class="gb-element-cc34185d">
<div class="gb-element-e49b6a68">
<h2 class="gb-text gb-text-d42e626f">管理团队</h2>



<div class="gb-element-9704f49b"></div>
</div>



<div class="gb-element-cc72d6c3">
<a class="gb-element-0d2725ff" href="#">
<p class="gb-text gb-text-75fb0c21">目前就职于Google，10余年开发经验，目前担任Senior Solution Architect职位，北大计算机本硕，擅长各种算法、Java、C++等编程语言。在学校期间多次参加ACM、天池大数据等多项比赛，拥有多项顶级paper、专利等，辅导帮助的1000+学生入职Google、Meta、阿里、Amazon等多个大厂。</p>



<div class="gb-element-0ccb957e">
<div>
<img fetchpriority="high" decoding="async" width="512" height="512" class="gb-media-176e053f" alt="Solid black square image (likely a placeholder or blackout)." title="6840541" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-016-6840541.webp" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-016-6840541.webp 512w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-015-6840541-300x300-1.webp 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-014-6840541-150x150-1.webp 150w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-013-6840541-12x12-1.webp 12w" sizes="(max-width: 512px) 100vw, 512px" />
</div>



<div>
<h4 class="gb-text">学长</h4>
</div>
</div>
</a>



<a class="gb-element-5e4c89d8" href="#">
<p class="gb-text gb-text-aa90639b">目前于University of Oxford读硕士.本科某计算机强势985，在大数据领域拥有丰富的实战经验，熟悉擅长HDFS、MapReduce、Yarn、Zookeeper、Hive、Flume、Kafka、HBase、Spark、Flink等，熟悉擅长MATLAB Simulink数学模型设计,具有多年信号时域、频域、调制域分析，掌握信道、调制、编码等常用功能的M语言实现经验。</p>



<div class="gb-element-ab84b3b5">
<div>
<img decoding="async" class="gb-media-c8bb6e14" alt="" title="WechatIMG9866" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-007-wechatimg9866.png"/>
</div>



<div>
<h4 class="gb-text">Roger</h4>
</div>
</div>
</a>



<a class="gb-element-83eea067" href="#">
<p class="gb-text gb-text-c48aea3b">Princeton University博士，人在海外，曾在谷歌、苹果等多家大厂工作。深度学习NLP方向拥有多篇SCI，机器学习方向拥有Github千星⭐️项目，Leetcode全国排名百名内，编程能力一流，专业辅导多年，精通TensorFlow、Keras，pytorch,QA问题，NER问题，文本分类，情感分析；对贝叶斯、随机森林、SVM、神经网络、聚类、PCA等有深入应用和研究。再计算机视觉上，图像分类，图像目标检测，图像分割，生成对抗网络等具备丰富的经验。</p>



<div class="gb-element-656e9daf">
<div>
<img decoding="async" width="404" height="296" class="gb-media-25b77a9a" alt="" title="WechatIMG7956" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-006-wechatimg7956.png" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-006-wechatimg7956.png 404w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-005-wechatimg7956-300x220-1.png 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-004-wechatimg7956-16x12-1.png 16w" sizes="(max-width: 404px) 100vw, 404px" />
</div>



<div>
<h4 class="gb-text">James</h4>
</div>
</div>
</a>



<a class="gb-element-1cf72096" href="#">
<p class="gb-text gb-text-5434fef7">北大硕博连读，学长多年好基友，CPA、CFA证书持有者，在商业分析、管理会计、金融工程有着丰富的辅导经验，和学长一起努力奋斗8年，服务学员数量1000+。</p>



<div class="gb-element-a4f0c7fe">
<div>
<img decoding="async" width="404" height="426" class="gb-media-6e39e858" alt="" title="WechatIMG7954" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-003-wechatimg7954.png" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-003-wechatimg7954.png 404w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-002-wechatimg7954-285x300-1.png 285w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-001-wechatimg7954-11x12-1.png 11w" sizes="(max-width: 404px) 100vw, 404px" />
</div>



<div>
<h4 class="gb-text">Isaac</h4>
</div>
</div>
</a>
</div>
</div>
</div>



<div>
<div class="gb-element-38c69647">
<h2 class="gb-text gb-text-20281697">客户评价</h2>



<p class="gb-text">1000+ 成功案例，只想带你了解真实的我们！</p>



<div class="gb-tabs gb-tabs-513a33df" data-opened-tab="1">
<div class="gb-tabs__menu gb-tabs__menu-6aae225b" role="tablist">
<div tabindex="0" class="gb-tabs__menu-item gb-tabs__menu-item-4a18984e gb-block-is-current" role="tab" id="gb-tab-menu-item-4a18984e">
<span class="gb-text gb-text-e90ae470">用户评价</span>
</div>



<div tabindex="0" class="gb-tabs__menu-item gb-tabs__menu-item-87819c3d" role="tab" id="gb-tab-menu-item-87819c3d">
<span class="gb-text gb-text-286dab72">offer 案例</span>
</div>



<div tabindex="0" class="gb-tabs__menu-item gb-tabs__menu-item-13e7b89e" role="tab" id="gb-tab-menu-item-13e7b89e">
<span class="gb-text gb-text-a47aa7db">OA 案例</span>
</div>



<div tabindex="0" class="gb-tabs__menu-item gb-tabs__menu-item-85742302" role="tab" id="gb-tab-menu-item-85742302">
<span class="gb-text gb-text-74b2cf67">VO 案例</span>
</div>
</div>



<div class="gb-tabs__items">
<div class="gb-tabs__item gb-tabs__item-open" role="tabpanel" id="gb-tab-item-254099d5">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-1 is-layout-flex wp-block-gallery-is-layout-flex">
<figure class="wp-block-image size-large"><img title="review1 - csvosupport" loading="eager" decoding="async" width="456" height="1024" data-id="3156" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-071-review1-456x1024-1.jpg" alt="Chinese chat screenshot discussing follow-up and sending a transfer, with green and white message bubbles and an orange transfer card in the middle." class="wp-image-3156" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-071-review1-456x1024-1.jpg 456w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-070-review1-134x300-1.jpg 134w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-074-review1-768x1726-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-073-review1-684x1536-1.jpg 684w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-075-review1-912x2048-1.jpg 912w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-072-review1-5x12-1.jpg 5w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-076-review1.jpg 940w" sizes="auto, (max-width: 456px) 100vw, 456px" data-mwl-img-id="3156" /><figcaption class="wp-element-caption">review1</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review2 - csvosupport" loading="eager" decoding="async" width="636" height="1024" data-id="3157" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-078-review2-636x1024-1.jpg" alt="Screenshots of a chat in Chinese: green message says &#039;谷歌 match 上了吗&#039;, emojis, white message &#039;上周已经发 offer 啦&#039;, an attached Google document image, and another green message &#039;不错啊，总包多少&#039; with emoji avatars." class="wp-image-3157" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-078-review2-636x1024-1.jpg 636w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-077-review2-186x300-1.jpg 186w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-079-review2-768x1236-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-080-review2-7x12-1.jpg 7w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-081-review2.jpg 886w" sizes="auto, (max-width: 636px) 100vw, 636px" data-mwl-img-id="3157" /><figcaption class="wp-element-caption">review2</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review3 - csvosupport" loading="eager" decoding="async" width="830" height="1024" data-id="3158" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-085-review3-830x1024-1.jpg" alt="Chinese chat screenshot: green and white message bubbles discussing being in team match and waiting, with emojis and a profile icon on the right." class="wp-image-3158" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-085-review3-830x1024-1.jpg 830w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-083-review3-243x300-1.jpg 243w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-084-review3-768x947-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-082-review3-10x12-1.jpg 10w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-086-review3.jpg 1080w" sizes="auto, (max-width: 830px) 100vw, 830px" data-mwl-img-id="3158" /><figcaption class="wp-element-caption">review3</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review4 - csvosupport" loading="eager" decoding="async" width="762" height="1024" data-id="3159" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-088-review4-762x1024-1.jpg" alt="WeChat-style chat screenshot in Chinese: at 9:13 AM a distorted image preview; at 9:47 AM a green message saying &#039;怎麼樣&#039; and a small sad emoji chat avatar on the side, followed by a white message thanking the leaders for active communication and great support, then another green message &#039;看見&#039;" class="wp-image-3159" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-088-review4-762x1024-1.jpg 762w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-087-review4-223x300-1.jpg 223w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-089-review4-768x1032-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-090-review4-9x12-1.jpg 9w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-091-review4.jpg 886w" sizes="auto, (max-width: 762px) 100vw, 762px" data-mwl-img-id="3159" /><figcaption class="wp-element-caption">review4</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review5 - csvosupport" loading="eager" decoding="async" width="886" height="974" data-id="3160" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-095-review5.jpg" alt="OA proxy 聊天截图：讨论在浏览器中试试及腾讯会议/Zoom 的特殊设置，结束后离开房间和会议，并表示会拿到 offer，屏幕底部有天使表情。" class="wp-image-3160" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-095-review5.jpg 886w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-093-review5-273x300-1.jpg 273w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-094-review5-768x844-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-092-review5-11x12-1.jpg 11w" sizes="auto, (max-width: 886px) 100vw, 886px" data-mwl-img-id="3160" /><figcaption class="wp-element-caption">review5</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review6 - csvosupport" loading="eager" decoding="async" width="453" height="1024" data-id="3161" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-097-review6-453x1024-1.jpg" alt="Screenshot of a Chinese chat about preparing for a coding interview; green bubbles show plan to &#039;release SD&#039; and approval responses like &#039;ok&#039;." class="wp-image-3161" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-097-review6-453x1024-1.jpg 453w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-096-review6-133x300-1.jpg 133w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-100-review6-768x1737-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-099-review6-679x1536-1.jpg 679w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-101-review6-905x2048-1.jpg 905w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-098-review6-5x12-1.jpg 5w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-102-review6.jpg 930w" sizes="auto, (max-width: 453px) 100vw, 453px" data-mwl-img-id="3161" /><figcaption class="wp-element-caption">review6</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review7 - csvosupport" loading="eager" decoding="async" width="517" height="1024" data-id="3162" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-104-review7-517x1024-1.jpg" alt="Screenshots of a Chinese chat conversation in a messaging app; green bubbles on the right with Chick-fil-A avatars, a long white bubble on the left with a message about finishing a loop, and timestamps 昨天 上午11:01." class="wp-image-3162" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-104-review7-517x1024-1.jpg 517w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-103-review7-151x300-1.jpg 151w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-106-review7-768x1522-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-107-review7-775x1536-1.jpg 775w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-105-review7-6x12-1.jpg 6w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-108-review7.jpg 910w" sizes="auto, (max-width: 517px) 100vw, 517px" data-mwl-img-id="3162" /><figcaption class="wp-element-caption">review7</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review8 - csvosupport" loading="eager" decoding="async" width="450" height="1024" data-id="3163" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-110-review8-450x1024-1.jpg" alt="Dark messaging thread in Chinese: congratulatory exchange after a prize transfer, with green chat bubbles saying “哈哈 恭喜！” and “OK/哈哈好” and a PDF/file transfer preview showing a WeChat/desktop UI element nearby." class="wp-image-3163" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-110-review8-450x1024-1.jpg 450w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-109-review8-132x300-1.jpg 132w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-113-review8-768x1747-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-112-review8-675x1536-1.jpg 675w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-114-review8-901x2048-1.jpg 901w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-111-review8-5x12-1.jpg 5w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-115-review8.jpg 948w" sizes="auto, (max-width: 450px) 100vw, 450px" data-mwl-img-id="3163" /><figcaption class="wp-element-caption">review8</figcaption></figure>
</figure>
</div>



<div class="gb-tabs__item" role="tabpanel" id="gb-tab-item-830667c1">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-2 is-layout-flex wp-block-gallery-is-layout-flex">
<figure class="wp-block-image size-large"><img title="amazon-offer - csvosupport" loading="eager" decoding="async" width="798" height="1024" data-id="3164" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-033-amazon-offer-798x1024-1.jpg" alt="Amazon letter on company letterhead offering a Software Dev Engineer position in Jersey City, NJ, with start date June 30, 2025 and salary details (annualized pay and sign-on)." class="wp-image-3164" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-033-amazon-offer-798x1024-1.jpg 798w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-031-amazon-offer-234x300-1.jpg 234w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-032-amazon-offer-768x986-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-034-amazon-offer-9x12-1.jpg 9w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-035-amazon-offer.jpg 916w" sizes="auto, (max-width: 798px) 100vw, 798px" data-mwl-img-id="3164" /><figcaption class="wp-element-caption">amazon offer</figcaption></figure>



<figure class="wp-block-image size-large"><img title="amazon-sde2 - csvosupport" loading="eager" decoding="async" width="1024" height="619" data-id="3165" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-042-amazon-sde2-1024x619-1.jpg" alt="Screenshot of an email header with the Amazon logo, followed by body text congratulating the recipient on an AWS offer and mentioning a formal offer letter and benefits details." class="wp-image-3165" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-042-amazon-sde2-1024x619-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-045-amazon-sde2-300x181-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-046-amazon-sde2-768x464-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-043-amazon-sde2-1536x928-1.jpg 1536w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-044-amazon-sde2-18x12-1.jpg 18w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-047-amazon-sde2.jpg 1628w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3165" /><figcaption class="wp-element-caption">amazon sde2</figcaption></figure>



<figure class="wp-block-image size-large"><img title="google-offer - csvosupport" loading="eager" decoding="async" width="1024" height="953" data-id="3166" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-048-google-offer-1024x953-1.jpg" alt="Screenshot of an email from Google Offer Letters Team via DocuSign showing a blue banner with &#039;Review Document&#039; and a Google logo, about reviewing employment documents." class="wp-image-3166" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-048-google-offer-1024x953-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-050-google-offer-300x279-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-051-google-offer-768x715-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-049-google-offer-13x12-1.jpg 13w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-052-google-offer.jpg 1160w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3166" /><figcaption class="wp-element-caption">google offer</figcaption></figure>



<figure class="wp-block-image size-large"><img title="microsoft-offer - csvosupport" loading="eager" decoding="async" width="473" height="1024" data-id="3167" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-059-microsoft-offer-473x1024-1.jpg" alt="Mobile email screenshot announcing a Microsoft internship offer, with a celebratory banner reading &#039;Congratulations!&#039; and &#039;On your offer to be a Microsoft Intern&#039;" class="wp-image-3167" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-059-microsoft-offer-473x1024-1.jpg 473w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-058-microsoft-offer-139x300-1.jpg 139w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-062-microsoft-offer-768x1662-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-061-microsoft-offer-710x1536-1.jpg 710w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-063-microsoft-offer-946x2048-1.jpg 946w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-060-microsoft-offer-6x12-1.jpg 6w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-064-microsoft-offer.jpg 1080w" sizes="auto, (max-width: 473px) 100vw, 473px" data-mwl-img-id="3167" /><figcaption class="wp-element-caption">microsoft offer</figcaption></figure>
</figure>
</div>



<div class="gb-tabs__item" role="tabpanel" id="gb-tab-item-40de34e5">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-3 is-layout-flex wp-block-gallery-is-layout-flex">
<figure class="wp-block-image size-large"><img title="amazon-oa - csvosupport" loading="eager" decoding="async" width="1024" height="819" data-id="3168" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-017-amazon-oa-1024x819-1.jpg" alt="Screenshot of a UI titled &#039;Code Question 2&#039; showing a warehouse-inspection scenario with bulleted rules, and a right panel listing test results and test cases." class="wp-image-3168" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-017-amazon-oa-1024x819-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-019-amazon-oa-300x240-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-020-amazon-oa-768x614-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-018-amazon-oa-15x12-1.jpg 15w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-021-amazon-oa.jpg 1350w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3168" /><figcaption class="wp-element-caption">amazon oa</figcaption></figure>



<figure class="wp-block-image size-large"><img title="amazon-oa2 - csvosupport" loading="eager" decoding="async" width="1024" height="764" data-id="3169" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-022-amazon-oa2-1024x764-1.jpg" alt="Split-screen screenshot: left panel shows a written problem about inventory quality; right panel shows Python code in a dark editor, with colored blocks and line numbers. watermark reads &#039;interviewAid&#039;." class="wp-image-3169" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-022-amazon-oa2-1024x764-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-024-amazon-oa2-300x224-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-025-amazon-oa2-768x573-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-023-amazon-oa2-16x12-1.jpg 16w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-026-amazon-oa2.jpg 1248w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3169" /><figcaption class="wp-element-caption">amazon oa2</figcaption></figure>



<figure class="wp-block-image size-large"><img title="amazon-oa3 - csvosupport" loading="eager" decoding="async" width="768" height="1024" data-id="3170" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-028-amazon-oa3-768x1024-1.jpg" alt="Split screen: left side shows a dark IDE window with a Test Cases list and green &#039;6 passed&#039; status; right side shows a Chinese chat conversation about AI assistant usage with green and white speech bubbles." class="wp-image-3170" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-028-amazon-oa3-768x1024-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-027-amazon-oa3-225x300-1.jpg 225w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-029-amazon-oa3-9x12-1.jpg 9w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-030-amazon-oa3.jpg 1080w" sizes="auto, (max-width: 768px) 100vw, 768px" data-mwl-img-id="3170" /><figcaption class="wp-element-caption">amazon oa3</figcaption></figure>



<figure class="wp-block-image size-large"><img title="amazon-sde-oa - csvosupport" loading="eager" decoding="async" width="1024" height="659" data-id="3171" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-036-amazon-sde-oa-1024x659-1.jpg" alt="Java Spring controller code: updateComment method with @PutMapping(&#039;/{id}&#039;), handling request and errors in a try-catch block (Info visible: ResponseEntity, NOT_FOUND, UNAUTHORIZED)." class="wp-image-3171" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-036-amazon-sde-oa-1024x659-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-039-amazon-sde-oa-300x193-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-040-amazon-sde-oa-768x494-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-037-amazon-sde-oa-1536x989-1.jpg 1536w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-038-amazon-sde-oa-18x12-1.jpg 18w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-041-amazon-sde-oa.jpg 1678w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3171" /><figcaption class="wp-element-caption">amazon sde oa</figcaption></figure>



<figure class="wp-block-image size-large"><img title="intuit-oa - csvosupport" loading="eager" decoding="async" width="1024" height="930" data-id="3176" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-053-intuit-oa-1024x930-1.jpg" alt="Screenshot of a dark UI displaying an SQL: Stock Market Software Capitalization Report with bullet points about sectors, total capitalization, and notes; includes a schema table section labeled &#039;companies&#039; at the bottom." class="wp-image-3176" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-053-intuit-oa-1024x930-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-055-intuit-oa-300x273-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-056-intuit-oa-768x698-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-054-intuit-oa-13x12-1.jpg 13w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-057-intuit-oa.jpg 1080w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3176" /><figcaption class="wp-element-caption">intuit oa</figcaption></figure>



<figure class="wp-block-image size-large"><img title="stripe-oa1 - csvosupport" loading="eager" decoding="async" width="783" height="1024" data-id="3177" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-118-stripe-oa1-783x1024-1.jpg" alt="Screenshot of a dark command-guide discussing SHUTDOWN handling, target routing, and example CONNECT/SHUTDOWN commands with a right-side test results panel." class="wp-image-3177" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-118-stripe-oa1-783x1024-1.jpg 783w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-116-stripe-oa1-229x300-1.jpg 229w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-117-stripe-oa1-768x1005-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-119-stripe-oa1-9x12-1.jpg 9w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-120-stripe-oa1.jpg 1080w" sizes="auto, (max-width: 783px) 100vw, 783px" data-mwl-img-id="3177" /><figcaption class="wp-element-caption">stripe oa1</figcaption></figure>



<figure class="wp-block-image size-large"><img title="tiktok-oa - csvosupport" loading="eager" decoding="async" width="1024" height="760" data-id="3178" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-121-tiktok-oa-1024x760-1.jpg" alt="Split-screen: left side shows a UI with a &#039;Create post&#039; form and &#039;Publish post&#039; button, plus a &#039;Recent posts&#039; list; right side displays a code editor with a project file tree and open files, overlaid by a large watermark." class="wp-image-3178" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-121-tiktok-oa-1024x760-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-123-tiktok-oa-300x223-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-124-tiktok-oa-768x570-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-122-tiktok-oa-16x12-1.jpg 16w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-125-tiktok-oa.jpg 1455w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3178" /><figcaption class="wp-element-caption">tiktok oa</figcaption></figure>



<figure class="wp-block-image size-large"><img title="two-sigma-oa - csvosupport" loading="eager" decoding="async" width="1024" height="883" data-id="3179" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-130-two-sigma-oa-1024x883-1.jpg" alt="Screenshot of a dark coding notebook UI titled &#039;Daily Temperature By Town&#039; with long descriptive text on the left (Part One and Part Two tasks) and a code editor/results panel on the right, including a &#039;Run Code&#039; button and a &#039;Compiler Message&#039; section." class="wp-image-3179" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-130-two-sigma-oa-1024x883-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-132-two-sigma-oa-300x259-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-133-two-sigma-oa-768x662-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-131-two-sigma-oa-14x12-1.jpg 14w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-134-two-sigma-oa.jpg 1252w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3179" /><figcaption class="wp-element-caption">two sigma oa</figcaption></figure>
</figure>
</div>



<div class="gb-tabs__item" role="tabpanel" id="gb-tab-item-366108b8">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-4 is-layout-flex wp-block-gallery-is-layout-flex">
<figure class="wp-block-image size-large"><img title="vo1 - csvosupport" loading="eager" decoding="async" width="563" height="1024" data-id="3172" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-136-vo1-563x1024-1.jpg" alt="Dark-mode email screenshot from Talent Acquisition asking Chen to schedule a 15–30 minute call to discuss the offer timeline and next steps after a successful interview." class="wp-image-3172" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-136-vo1-563x1024-1.jpg 563w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-135-vo1-165x300-1.jpg 165w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-137-vo1-7x12-1.jpg 7w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-138-vo1.jpg 650w" sizes="auto, (max-width: 563px) 100vw, 563px" data-mwl-img-id="3172" /><figcaption class="wp-element-caption">vo1</figcaption></figure>



<figure class="wp-block-image size-large"><img title="vo2 - csvosupport" loading="eager" decoding="async" width="1024" height="273" data-id="3173" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-139-vo2-1024x273-1.jpg" alt="Email notifying about Stripe internship virtual onsite interviews, outlining two exercises ( Programming and ML Integration ) and scheduling details." class="wp-image-3173" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-139-vo2-1024x273-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-141-vo2-300x80-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-142-vo2-768x205-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-140-vo2-18x5-1.jpg 18w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-143-vo2.jpg 1505w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3173" /><figcaption class="wp-element-caption">vo2</figcaption></figure>



<figure class="wp-block-image size-full"><img title="vo3 - csvosupport" loading="eager" decoding="async" width="666" height="567" data-id="3174" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-146-vo3.jpg" alt="Stripe job offer letter screenshot for Software Engineer, Intern; includes salary (,000 bi-weekly) and visa/benefits details." class="wp-image-3174" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-146-vo3.jpg 666w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-145-vo3-300x255-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-144-vo3-14x12-1.jpg 14w" sizes="auto, (max-width: 666px) 100vw, 666px" data-mwl-img-id="3174" /><figcaption class="wp-element-caption">vo3</figcaption></figure>



<figure class="wp-block-image size-large"><img title="vo4 - csvosupport" loading="eager" decoding="async" width="636" height="1024" data-id="3175" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-148-vo4-636x1024-1.jpg" alt="Email inviting to a Data Scientist II interview with a 60-minute Jam Session and scheduling details" class="wp-image-3175" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-148-vo4-636x1024-1.jpg 636w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-147-vo4-186x300-1.jpg 186w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-149-vo4-7x12-1.jpg 7w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-150-vo4.jpg 734w" sizes="auto, (max-width: 636px) 100vw, 636px" data-mwl-img-id="3175" /><figcaption class="wp-element-caption">vo4</figcaption></figure>



<figure class="wp-block-image size-large"><img title="vo5 - csvosupport" loading="eager" decoding="async" width="1024" height="375" data-id="3180" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-151-vo5-1024x375-1.jpg" alt="Email confirming two 1-hour technical interviews and next steps for a remote interview process (scheduling details)." class="wp-image-3180" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-151-vo5-1024x375-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-155-vo5-300x110-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-156-vo5-768x281-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-152-vo5-1536x562-1.jpg 1536w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-154-vo5-2048x750-1.jpg 2048w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-153-vo5-18x7-1.jpg 18w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3180" /><figcaption class="wp-element-caption">vo5</figcaption></figure>



<figure class="wp-block-image size-large"><img title="vo6 - csvosupport" loading="eager" decoding="async" width="1024" height="838" data-id="3181" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-157-vo6-1024x838-1.jpg" alt="Text outlining a remote interview process: virtual final round, 2-hour technical interview, 45-minute HR interview, three-round four-hour interview, lunch break, and Teams." class="wp-image-3181" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-157-vo6-1024x838-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-159-vo6-300x245-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-160-vo6-768x628-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-158-vo6-15x12-1.jpg 15w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-161-vo6.jpg 1274w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3181" /><figcaption class="wp-element-caption">vo6</figcaption></figure>



<figure class="wp-block-image size-large"><img title="openai-offer - csvosupport" loading="eager" decoding="async" width="958" height="1024" data-id="3182" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-068-openai-offer-958x1024-1.jpg" alt="Offer to join OpenAI—signature requested by [name obscured], shown on a mobile email screen with a profile avatar and a star icon nearby (names obscured by blue scribbles)." class="wp-image-3182" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-068-openai-offer-958x1024-1.jpg 958w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-066-openai-offer-281x300-1.jpg 281w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-067-openai-offer-768x821-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-065-openai-offer-11x12-1.jpg 11w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-069-openai-offer.jpg 1080w" sizes="auto, (max-width: 958px) 100vw, 958px" data-mwl-img-id="3182" /><figcaption class="wp-element-caption">openai offer</figcaption></figure>



<figure class="wp-block-image size-large"><img title="tiktok-offer - csvosupport" loading="eager" decoding="async" width="637" height="1024" data-id="3183" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-127-tiktok-offer-637x1024-1.jpg" alt="Email from TikTok inviting you to join ByteDance as a Data Engineer Intern for Summer 2026, with offer letter and office address link" class="wp-image-3183" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-127-tiktok-offer-637x1024-1.jpg 637w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-126-tiktok-offer-187x300-1.jpg 187w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-128-tiktok-offer-7x12-1.jpg 7w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-129-tiktok-offer.jpg 750w" sizes="auto, (max-width: 637px) 100vw, 637px" data-mwl-img-id="3183" /><figcaption class="wp-element-caption">tiktok offer</figcaption></figure>
</figure>
</div>



<div class="gb-tabs__item" role="tabpanel" id="gb-tab-item-e9c90b20">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-5 is-layout-flex wp-block-gallery-is-layout-flex">
<figure class="wp-block-image size-large"><img title="2026 Google NG Interview Review - csvosupport" loading="eager" decoding="async" width="1000" height="671" data-id="2685" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-008-2026-google-ng-interview-review.webp" alt="Google logo on a dark tech background with a digital brain, text reads'2026 Google NG Interview Review' and 'Interview Experience &amp; Tips' (thumbnail for article)" class="wp-image-2685" style="aspect-ratio:4/3" data-mwl-img-id="2685"/><figcaption class="wp-element-caption">2026 Google NG Interview Review</figcaption></figure>



<figure class="wp-block-image size-large"><img title="How to Pass Coinbase OA - csvosupport" loading="eager" decoding="async" width="1168" height="784" data-id="2683" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-009-how-to-pass-coinbase-oa.jpg" alt="Banner: &quot;How to Pass Coinbase OA&quot; with a laptop showing code, blue neon theme for a coding tutorial." class="wp-image-2683" style="aspect-ratio:4/3" data-mwl-img-id="2683"/><figcaption class="wp-element-caption">How to Pass Coinbase OA</figcaption></figure>



<figure class="wp-block-image size-large"><img title="image - csvosupport" loading="eager" decoding="async" width="1080" height="1672" data-id="2681" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-011-image-18.png" alt="Diagram of a circular ring of drone hubs labeled 1,2,3,m-1,m around a circle; hub 1 is highlighted in green." class="wp-image-2681" style="aspect-ratio:4/3" data-mwl-img-id="2681"/><figcaption class="wp-element-caption">image</figcaption></figure>



<figure class="wp-block-image size-large"><img title="image - csvosupport" loading="eager" decoding="async" width="728" height="972" data-id="2680" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-010-image-17.png" alt="Dark-mode IDE screen showing Python code on the left and a Test Cases panel on the right; six tests ran in 1.10s with all tests passing (6 passed, 0 failed). UI shows Run/Run Tests and a Save &amp; Proceed button at the top." class="wp-image-2680" style="aspect-ratio:4/3" data-mwl-img-id="2680"/><figcaption class="wp-element-caption">image</figcaption></figure>
</figure>
</div>



<div class="gb-tabs__item" role="tabpanel" id="gb-tab-item-36de4168">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-6 is-layout-flex wp-block-gallery-is-layout-flex">
<figure class="wp-block-image size-large"><img title="2026 Google NG Interview Review - csvosupport" loading="eager" decoding="async" width="1000" height="671" data-id="2685" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-008-2026-google-ng-interview-review.webp" alt="Google logo on a dark tech background with a digital brain, text reads'2026 Google NG Interview Review' and 'Interview Experience &amp; Tips' (thumbnail for article)" class="wp-image-2685" style="aspect-ratio:4/3" data-mwl-img-id="2685"/><figcaption class="wp-element-caption">2026 Google NG Interview Review</figcaption></figure>



<figure class="wp-block-image size-large"><img title="How to Pass Coinbase OA - csvosupport" loading="eager" decoding="async" width="1168" height="784" data-id="2683" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-009-how-to-pass-coinbase-oa.jpg" alt="Banner: &quot;How to Pass Coinbase OA&quot; with a laptop showing code, blue neon theme for a coding tutorial." class="wp-image-2683" style="aspect-ratio:4/3" data-mwl-img-id="2683"/><figcaption class="wp-element-caption">How to Pass Coinbase OA</figcaption></figure>



<figure class="wp-block-image size-large"><img title="image - csvosupport" loading="eager" decoding="async" width="1080" height="1672" data-id="2681" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-011-image-18.png" alt="Diagram of a circular ring of drone hubs labeled 1,2,3,m-1,m around a circle; hub 1 is highlighted in green." class="wp-image-2681" style="aspect-ratio:4/3" data-mwl-img-id="2681"/><figcaption class="wp-element-caption">image</figcaption></figure>



<figure class="wp-block-image size-large"><img title="image - csvosupport" loading="eager" decoding="async" width="728" height="972" data-id="2680" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-010-image-17.png" alt="Dark-mode IDE screen showing Python code on the left and a Test Cases panel on the right; six tests ran in 1.10s with all tests passing (6 passed, 0 failed). UI shows Run/Run Tests and a Save &amp; Proceed button at the top." class="wp-image-2680" style="aspect-ratio:4/3" data-mwl-img-id="2680"/><figcaption class="wp-element-caption">image</figcaption></figure>
</figure>
</div>
</div>
</div>
</div>
</div>
		</div>

			</div>
</article>
HTML;
    return csop_home_inject_oavo_testimonials($html);
}

function csop_home_inject_oavo_testimonials($html) {
    $start_marker = '<div class="gb-element-38c69647">' . "\n" . '<h2 class="gb-text gb-text-20281697">客户评价</h2>';
    $end_marker = "\n\t\t</div>\n\n\t\t\t</div>\n</article>";
    $start = strpos($html, $start_marker);
    $end = strrpos($html, $end_marker);
    if ($start === false || $end === false || $end <= $start) return $html;
    return substr($html, 0, $start) . csop_home_oavo_testimonials_html() . substr($html, $end);
}

function csop_home_oavo_testimonials_data() {
    return array(
        array(
            'label' => '真实案例',
            'class' => 'gb-tabs__menu-item-4a18984e',
            'id' => 'oavo-real-case',
            'images' => array(
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-real-case-01.jpg', 'alt' => '真实案例 1'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-real-case-02.jpg', 'alt' => '真实案例 2'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-real-case-03.jpg', 'alt' => '真实案例 3'),
            ),
        ),
        array(
            'label' => 'OA通过案例',
            'class' => 'gb-tabs__menu-item-87819c3d',
            'id' => 'oavo-oa-case',
            'images' => array(
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-oa-case-04.png', 'alt' => 'OA案例 1'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-oa-case-05.png', 'alt' => 'OA案例 2'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-oa-case-06.png', 'alt' => 'OA案例 3'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-oa-case-07.png', 'alt' => 'OA案例 4'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-oa-case-08.png', 'alt' => 'OA案例 5'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-oa-case-09.png', 'alt' => 'OA案例 6'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-oa-case-10-scaled.png', 'alt' => 'OA案例 7'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-oa-case-11.png', 'alt' => 'OA案例 8'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-oa-case-12.png', 'alt' => 'OA案例 9'),
            ),
        ),
        array(
            'label' => 'VO通过案例',
            'class' => 'gb-tabs__menu-item-13e7b89e',
            'id' => 'oavo-vo-case',
            'images' => array(
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-vo-case-13.png', 'alt' => 'VO案例 1'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-vo-case-14.png', 'alt' => 'VO案例 2'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-vo-case-15.png', 'alt' => 'VO案例 3'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-vo-case-16.png', 'alt' => 'VO案例 4'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-vo-case-17.png', 'alt' => 'VO案例 5'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-vo-case-18.png', 'alt' => 'VO案例 6'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-vo-case-19.png', 'alt' => 'VO案例 7'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-vo-case-20.png', 'alt' => 'VO案例 8'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-vo-case-21.png', 'alt' => 'VO案例 9'),
            ),
        ),
        array(
            'label' => '客户评价',
            'class' => 'gb-tabs__menu-item-85742302',
            'id' => 'oavo-review-case',
            'images' => array(
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-customer-review-22.png', 'alt' => '客户评价 1'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-customer-review-23.png', 'alt' => '客户评价 2'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-customer-review-24.png', 'alt' => '客户评价 3'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-customer-review-25.png', 'alt' => '客户评价 4'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-customer-review-26.png', 'alt' => '客户评价 5'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-customer-review-27.png', 'alt' => '客户评价 6'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-customer-review-28.png', 'alt' => '客户评价 7'),
                array('url' => 'https://csvosupport.com/wp-content/uploads/2026/06/oavo-customer-review-29.png', 'alt' => '客户评价 8'),
            ),
        ),
    );
}

function csop_home_oavo_testimonials_html() {
    $tabs = csop_home_oavo_testimonials_data();
    ob_start();
    ?>
<div class="gb-element-38c69647">
<h2 class="gb-text gb-text-20281697">客户评价</h2>

<p class="gb-text">100+ 成功案例，真实反馈见证我们的专业实力</p>

<div class="gb-tabs gb-tabs-513a33df" data-opened-tab="1">
<div class="gb-tabs__menu gb-tabs__menu-6aae225b" role="tablist">
<?php foreach ($tabs as $index => $tab): ?>
<div tabindex="0" class="gb-tabs__menu-item <?php echo esc_attr($tab['class']); ?> <?php echo $index === 0 ? 'gb-block-is-current' : ''; ?>" role="tab" id="gb-tab-menu-item-<?php echo esc_attr($tab['id']); ?>">
<span class="gb-text gb-text-e90ae470"><?php echo esc_html($tab['label']); ?></span>
</div>

<?php endforeach; ?>
</div>

<div class="gb-tabs__items">
<?php foreach ($tabs as $index => $tab): ?>
<div class="gb-tabs__item <?php echo $index === 0 ? 'gb-tabs__item-open' : ''; ?>" role="tabpanel" id="gb-tab-item-<?php echo esc_attr($tab['id']); ?>">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-oavo-<?php echo esc_attr((string) ($index + 1)); ?> is-layout-flex wp-block-gallery-is-layout-flex">
<?php foreach ($tab['images'] as $image): ?>
<figure class="wp-block-image size-large"><img title="<?php echo esc_attr($image['alt'] . ' - csvosupport'); ?>" loading="lazy" decoding="async" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" class="wp-image-oavo-case" style="aspect-ratio:4/3" /><figcaption class="wp-element-caption"><?php echo esc_html($image['alt']); ?></figcaption></figure>

<?php endforeach; ?>
</figure>
</div>

<?php endforeach; ?>
</div>
</div>
</div>
    <?php
    return ob_get_clean();
}

/**
 * csvosupport menu page renderers copied from the csofferprep demo.
 * Shortcodes: [csop_page_blog], [csop_page_about], [csop_page_price], [csop_page_contact]
 */
defined('ABSPATH') || exit;

add_action('init', 'csop_pages_bootstrap_options');
add_action('admin_menu', 'csop_pages_admin_menu', 36);
add_action('admin_enqueue_scripts', 'csop_pages_admin_assets');
add_shortcode('csop_page_blog', 'csop_page_blog_shortcode');
add_shortcode('csop_page_about', 'csop_page_about_shortcode');
add_shortcode('csop_page_price', 'csop_page_price_shortcode');
add_shortcode('csop_page_contact', 'csop_page_contact_shortcode');
add_shortcode('csop_page_en', 'csop_page_en_shortcode');
add_shortcode('csop_page_zh_tw', 'csop_page_zh_tw_shortcode');

function csop_pages_definitions() {
    return array(
        'blog' => array('label' => '面试真题', 'slug' => 'blog', 'shortcode' => 'csop_page_blog'),
        'about' => array('label' => '关于我们', 'slug' => 'about_us', 'shortcode' => 'csop_page_about'),
        'price' => array('label' => '服务&价格', 'slug' => 'price', 'shortcode' => 'csop_page_price'),
        'contact' => array('label' => '联系学长', 'slug' => 'contact', 'shortcode' => 'csop_page_contact'),
    );
}

function csop_pages_option_name() {
    return 'csop_menu_pages_options_v1';
}

function csop_pages_default_options() {
    return array('content_html' => array());
}

function csop_pages_bootstrap_options() {
    if (get_option(csop_pages_option_name()) === false) {
        add_option(csop_pages_option_name(), csop_pages_default_options());
    }
}

function csop_pages_get_options() {
    $saved = get_option(csop_pages_option_name(), array());
    if (!is_array($saved)) $saved = array();
    $saved = array_merge(csop_pages_default_options(), $saved);
    if (!isset($saved['content_html']) || !is_array($saved['content_html'])) {
        $saved['content_html'] = array();
    }
    return $saved;
}

function csop_pages_custom_html($page) {
    $options = csop_pages_get_options();
    return isset($options['content_html'][$page]) ? (string) $options['content_html'][$page] : '';
}

function csop_pages_save_custom_html($page, $html) {
    $pages = csop_pages_definitions();
    if (!isset($pages[$page])) return;
    $options = csop_pages_get_options();
    $options['content_html'][$page] = (string) $html;
    update_option(csop_pages_option_name(), $options);
}

function csop_pages_reset_custom_html($page) {
    $options = csop_pages_get_options();
    if (isset($options['content_html'][$page])) {
        unset($options['content_html'][$page]);
        update_option(csop_pages_option_name(), $options);
    }
}

function csop_page_blog_shortcode() { return csop_pages_render('blog', false); }
function csop_page_about_shortcode() { return csop_pages_render('about', false); }
function csop_page_price_shortcode() { return csop_pages_render('price', false); }
function csop_page_contact_shortcode() { return csop_pages_render('contact', false); }
function csop_page_en_shortcode() { return function_exists('csop_home_render') ? csop_home_render(false) : csop_pages_render('en', false); }
function csop_page_zh_tw_shortcode() { return function_exists('csop_home_render') ? csop_home_render(false) : csop_pages_render('zh_tw', false); }

function csop_pages_admin_menu() {
    if (!function_exists('cc_site_visual_dashboard_page')) {
        function cc_site_visual_dashboard_page() {
            echo '<div class="wrap"><h1>网站可视化编辑</h1><p>在左侧子菜单中管理站点模块。</p></div>';
        }
        add_menu_page('网站可视化编辑', '网站可视化编辑', 'manage_options', 'cc-site-visual', 'cc_site_visual_dashboard_page', 'dashicons-admin-customizer', 3);
    }
    add_submenu_page('cc-site-visual', 'csvosupport 普通界面', '普通界面设置', 'manage_options', 'csop-menu-pages', 'csop_pages_settings_page');
}

function csop_pages_admin_assets($hook) {
    if (strpos((string) $hook, 'csop-menu-pages') === false) return;
    wp_enqueue_media();
    wp_register_style('csop-pages-admin-style', false, array(), '1.0.0');
    wp_enqueue_style('csop-pages-admin-style');
    wp_add_inline_style('csop-pages-admin-style', csop_pages_admin_css());
    wp_register_script('csop-pages-admin-script', false, array('jquery'), '1.0.0', true);
    wp_enqueue_script('csop-pages-admin-script');
    wp_add_inline_script('csop-pages-admin-script', csop_pages_admin_js());
}

function csop_pages_settings_page() {
    if (!current_user_can('manage_options')) return;
    $pages = csop_pages_definitions();
    $active = isset($_GET['csop_page']) ? sanitize_key(wp_unslash($_GET['csop_page'])) : 'blog';
    if (!isset($pages[$active])) $active = 'blog';
    if (isset($_POST['csop_pages_save'])) {
        check_admin_referer('csop_pages_save_action');
        $raw = isset($_POST['csop_pages']) && is_array($_POST['csop_pages']) ? $_POST['csop_pages'] : array();
        $content_html = isset($raw['content_html']) ? wp_unslash($raw['content_html']) : '';
        csop_pages_save_custom_html($active, $content_html);
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($pages[$active]['label']) . ' 内容已保存。</p></div>';
    }
    if (isset($_POST['csop_pages_reset'])) {
        check_admin_referer('csop_pages_save_action');
        csop_pages_reset_custom_html($active);
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($pages[$active]['label']) . ' 已恢复 demo 默认内容。</p></div>';
    }
    $content_html = csop_pages_html($active);
    ?>
    <div class="wrap csop-pages-admin-wrap">
        <h1>csvosupport 普通界面</h1>
        <p>这些界面以中文页面为唯一基准，保留 GeneratePress / GenerateBlocks 的页面结构、侧栏、图片和按钮样式。英文和繁中不做单独界面，只在同一页面结构上翻译文字。</p>
        <div class="csop-pages-admin-tabs">
            <?php foreach ($pages as $key => $item) : ?>
                <a class="button <?php echo $key === $active ? 'button-primary' : ''; ?>" href="<?php echo esc_url(add_query_arg('csop_page', $key)); ?>"><?php echo esc_html($item['label']); ?></a>
            <?php endforeach; ?>
        </div>
        <form method="post" novalidate>
            <?php wp_nonce_field('csop_pages_save_action'); ?>
            <div class="csop-pages-admin-layout">
                <div class="csop-pages-admin-card csop-pages-editor-card">
                    <div class="csop-pages-admin-head">
                        <div>
                            <h2><?php echo esc_html($pages[$active]['label']); ?> 内容编辑</h2>
                            <p>当前界面短代码：<code>[<?php echo esc_html($pages[$active]['shortcode']); ?>]</code></p>
                        </div>
                        <a class="button" href="<?php echo esc_url(home_url('/' . trim($pages[$active]['slug'], '/') . '/')); ?>" target="_blank" rel="noopener">前台预览</a>
                    </div>
                    <div class="csop-pages-meta">
                        <span>页面路径：<code>/<?php echo esc_html($pages[$active]['slug']); ?>/</code></span>
                        <span>页面块顺序：<code>[csop_header]</code> → <code>[<?php echo esc_html($pages[$active]['shortcode']); ?>]</code> → <code>[csop_footer]</code></span>
                    </div>
                    <div class="csop-pages-tool-row">
                        <button type="button" class="button" data-csop-pages-media>插入图片</button>
                        <button type="button" class="button" data-csop-pages-button>插入按钮</button>
                        <button type="button" class="button" data-csop-pages-refresh>刷新右侧预览</button>
                    </div>
                    <label class="csop-pages-field">
                        <span>界面主体 HTML</span>
                        <textarea name="csop_pages[content_html]" data-csop-pages-html><?php echo esc_textarea($content_html); ?></textarea>
                    </label>
                    <div class="csop-pages-save-bar">
                        <button type="submit" name="csop_pages_save" class="button button-primary button-hero">保存当前界面</button>
                        <button type="submit" name="csop_pages_reset" class="button button-secondary button-hero" onclick="return confirm('确定恢复当前界面的 demo 默认内容？')">恢复默认</button>
                    </div>
                </div>
                <div class="csop-pages-admin-card csop-pages-admin-preview-card">
                    <div class="csop-pages-preview-head">
                        <div>
                            <strong>实时可视化预览</strong>
                            <span>按 demo 样式渲染当前界面，便于客户在后台确认页面效果。</span>
                        </div>
                        <button type="button" class="button" data-csop-pages-fit>自动适配</button>
                    </div>
                    <div class="csop-pages-preview-frame"><div class="csop-pages-preview-scale" data-csop-pages-preview><?php echo csop_pages_render($active, true); ?></div></div>
                </div>
            </div>
        </form>
    </div>
    <?php
}

function csop_pages_render($page, $preview = false) {
    ob_start();
    echo csop_pages_front_css();
    ?>
    <main class="csop-menu-page csop-menu-page-<?php echo esc_attr($page); ?> <?php echo $preview ? 'csop-menu-page-preview' : ''; ?>" data-csop-menu-page="<?php echo esc_attr($page); ?>">
        <?php echo csop_pages_html($page); ?>
    </main>
    <?php
    echo csop_pages_front_js();
    return ob_get_clean();
}

function csop_pages_html($page) {
    if ($page === 'blog') return csop_pages_blog_html();
    $custom = csop_pages_custom_html($page);
    if (trim($custom) !== '') return $custom;
    $html = csop_pages_default_html($page);
    if (in_array($page, array('en', 'zh_tw'), true) && function_exists('csop_home_inject_oavo_testimonials')) {
        $html = csop_home_inject_oavo_testimonials($html);
    }
    return $html;
}

function csop_pages_default_html($page) {
    switch ($page) {
        case 'blog': return csop_pages_blog_html();
        case 'about': return csop_pages_about_html();
        case 'price': return csop_pages_price_html();
        case 'contact': return csop_pages_contact_html();
        case 'en': return csop_pages_en_html();
        case 'zh_tw': return csop_pages_zh_tw_html();
        default: return csop_pages_blog_html();
    }
}

function csop_pages_front_css() {
    static $done = false;
    if ($done) return '';
    $done = true;
    return <<<'CSS'
<style id="csop-menu-pages-front-css">
.csop-menu-page-en .site.grid-container,.csop-menu-page-zh_tw .site.grid-container{max-width:none!important}.csop-menu-page-en .site-content,.csop-menu-page-zh_tw .site-content{display:block!important;padding:0!important}.csop-menu-page-en .inside-article,.csop-menu-page-zh_tw .inside-article{background:transparent!important;border-radius:0!important;box-shadow:none!important;padding:0!important;margin:0!important;max-width:none!important}
.csop-menu-page .csop-reveal{opacity:0;transform:translateY(20px);transition:opacity .68s ease,transform .68s ease;will-change:opacity,transform}.csop-menu-page .csop-reveal.csop-in{opacity:1;transform:none}.csop-menu-page .csop-reveal-delay-1{transition-delay:.07s}.csop-menu-page .csop-reveal-delay-2{transition-delay:.14s}.csop-menu-page .csop-reveal-delay-3{transition-delay:.21s}@media (prefers-reduced-motion:reduce){.csop-menu-page .csop-reveal{opacity:1;transform:none;transition:none}}
.csop-menu-page,.csop-menu-page *,.csop-menu-page *:before,.csop-menu-page *:after{box-sizing:border-box}.csop-menu-page{--contrast:#212121;--contrast-2:#2f4468;--contrast-3:#878787;--base:#fafafa;--base-2:#f7f8f9;--base-3:#fff;--accent:#242226;--accent-2:#1b78e2;--accent-hover:#35343a;--highlight:#83b0de;background:var(--base);color:var(--contrast);font-family:"Open Sans",Arial,sans-serif;font-size:17px;line-height:1.5;width:100%;overflow:hidden}.csop-menu-page a{color:#1b78e2;text-decoration:none}.csop-menu-page a:hover{color:var(--accent-hover)}.csop-menu-page p{margin:0 0 1.5em}.csop-menu-page h1,.csop-menu-page h2,.csop-menu-page h3,.csop-menu-page h4{font-weight:600;color:var(--contrast-2);line-height:1.25;margin:0 0 20px}.csop-menu-page h1{font-size:40px}.csop-menu-page h2{font-size:30px}.csop-menu-page h3{font-size:20px}.csop-menu-page ul,.csop-menu-page ol{margin:0 0 1.5em 1.6em}.csop-menu-page img{max-width:100%;height:auto}.csop-menu-page figure{margin:0 0 1.5em}.csop-menu-page .screen-reader-text{position:absolute!important;width:1px!important;height:1px!important;padding:0!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important}.csop-menu-page .site.grid-container{max-width:1200px;margin-left:auto;margin-right:auto;background:transparent}.csop-menu-page .site-content{display:flex;gap:30px;align-items:flex-start;padding:30px 20px}.csop-menu-page .content-area{flex:1 1 auto;min-width:0}.csop-menu-page .site-main{width:100%}.csop-menu-page .inside-article{background:var(--base-3);border-radius:6px;box-shadow:rgba(60,64,67,.3) 0 1px 2px 0,rgba(60,64,67,.15) 0 2px 6px 2px;padding:50px 20px;overflow:hidden}.csop-menu-page .entry-header{margin-bottom:2em}.csop-menu-page .entry-title{margin-bottom:0}.csop-menu-page .entry-content>*:first-child{margin-top:0}.csop-menu-page .entry-content>*:last-child{margin-bottom:0}.csop-menu-page .widget-area{flex:0 0 30%;max-width:360px}.csop-menu-page .inside-right-sidebar{display:flex;flex-direction:column;gap:30px}.csop-menu-page .wp-block-search{display:flex;gap:8px;align-items:center}.csop-menu-page .wp-block-search__label{display:block;width:100%;font-weight:600;margin-bottom:8px}.csop-menu-page .wp-block-search__input{min-height:44px;border:1px solid #d8d8d8;border-radius:4px;padding:8px 12px;flex:1;min-width:0}.csop-menu-page .wp-block-search__button{min-height:44px;border:0;border-radius:4px;background:var(--accent);color:#fff;padding:0 14px;cursor:pointer}.csop-menu-page .wp-block-search__inside-wrapper{display:flex;gap:8px;width:100%}.csop-menu-page .gb-element-ca749091,.csop-menu-page .gb-element-6f418cf9{background:#fff}.csop-menu-page .wp-block-media-text{display:grid;grid-template-columns:50% 1fr;gap:2em;align-items:center;margin:0 0 1.5em}.csop-menu-page .wp-block-media-text__media img{display:block;width:100%}.csop-menu-page .wp-block-media-text__content{padding:0 8%}.csop-menu-page .wp-block-gallery{display:flex;flex-wrap:wrap;gap:.5em;margin:0 0 1.5em;padding:0}.csop-menu-page .wp-block-gallery.has-nested-images figure.wp-block-image{box-sizing:border-box;display:flex;flex-direction:column;flex-grow:1;justify-content:center;max-width:100%;position:relative;width:calc(25% - .5em)}.csop-menu-page .wp-block-gallery.has-nested-images figure.wp-block-image img{width:100%;height:100%;object-fit:cover;border-radius:2px}.csop-menu-page .wp-block-buttons{display:flex;flex-wrap:wrap;gap:.5em;margin:1.5em 0}.csop-menu-page .wp-block-button__link{display:inline-block;padding:10px 24px;background:var(--accent);color:#fff;border-radius:999px;font-size:15px;text-align:center}.csop-menu-page .wp-element-caption{font-size:13px;color:var(--contrast-3);text-align:center;margin-top:.5em}.csop-menu-page .has-base-3-color{color:var(--base-3)!important}.csop-menu-page .has-accent-2-color{color:var(--accent-2)!important}.csop-menu-page .has-accent-2-background-color{background-color:var(--accent-2)!important}.csop-menu-page .has-accent-color{color:var(--accent)!important}.csop-menu-page .has-contrast-color{color:var(--contrast)!important}.csop-menu-page .gb-tabs__item{display:none}.csop-menu-page .gb-tabs__item.gb-tabs__item-open{display:block}.csop-menu-page .gb-tabs__menu-item{cursor:pointer;user-select:none}.csop-menu-page .post-image img{display:block;width:100%;border-radius:6px}.csop-menu-page .paging-navigation{margin:30px 0}.csop-menu-page .nav-links{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}.csop-menu-page .page-numbers{display:inline-flex;min-width:36px;height:36px;align-items:center;justify-content:center;border-radius:4px;background:#fff;border:1px solid #ddd;color:#2f4468}.csop-menu-page .page-numbers.current{background:#242226;color:#fff}.csop-menu-page-preview{font-size:16px}.csop-menu-page-preview .site-content{padding:20px}.csop-menu-page-preview .inside-article{padding:34px 18px}@media(max-width:900px){.csop-menu-page .site-content{display:block;padding:0}.csop-menu-page .inside-article{margin:0;border-radius:0;padding:30px}.csop-menu-page .widget-area{max-width:none;margin:24px 20px}.csop-menu-page h1{font-size:32px}.csop-menu-page h2{font-size:25px}.csop-menu-page .wp-block-media-text{grid-template-columns:1fr}.csop-menu-page .wp-block-media-text__content{padding:0}.csop-menu-page .wp-block-gallery.has-nested-images figure.wp-block-image{width:calc(50% - .5em)}}

:root{--gb-container-width:1200px;}.gb-container .wp-block-image img{vertical-align:middle;}.gb-grid-wrapper .wp-block-image{margin-bottom:0;}.gb-highlight{background:none;}.gb-shape{line-height:0;}.gb-container-link{position:absolute;top:0;right:0;bottom:0;left:0;z-index:99;}.gb-element-0639ac5f{background-blend-mode:normal,normal;background:linear-gradient(to left,rgba(0,0,0,0.7) 0%,rgba(0,0,0,0.7) 100%),var(--inline-bg-image) center /cover no-repeat}.gb-element-9eda365a{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:100px 20px}.gb-element-77cfd298{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:100px 20px 40px 20px}.gb-element-ba61be1d{margin-bottom:30px;margin-left:auto;margin-right:auto;width:10%;border-top:3px solid var(--accent-2)}.gb-element-8420f9a6{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:60px 20px 80px 20px}@media (max-width:767px){.gb-element-8420f9a6{padding-bottom:60px;padding-top:60px}}.gb-element-44aeb402{align-items:center;border-bottom-color:var(--global-color-7);margin-bottom:10px;text-align:center}.gb-element-8526df89{margin-bottom:30px;margin-left:auto;margin-right:auto;width:10%;border-top:3px solid var(--accent-2)}.gb-element-644ba81e{column-gap:2em;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));row-gap:2em}@media (max-width:767px){.gb-element-644ba81e{grid-template-columns:1fr;row-gap:1.5em}}.gb-element-04df542a{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 15px 10px}.gb-element-04df542a:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-c0b03233{text-align:center}.gb-element-1aa32bcf{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 15px 10px}.gb-element-1aa32bcf:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-d54e97d1{text-align:center}.gb-element-3ee3a811{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 15px 10px}.gb-element-3ee3a811:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-4347e508{text-align:center}.gb-element-3c706259{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 15px 10px}.gb-element-3c706259:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-6288e754{text-align:center}.gb-element-4c34f39e{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 15px 10px}.gb-element-4c34f39e:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-3276f866{text-align:center}.gb-element-335115d4{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;text-align:center;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 15px 10px}.gb-element-335115d4:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-e33fac9c{text-align:center}.gb-element-0fe52152{background-attachment:fixed;background-blend-mode:normal;background-image:var(--inline-bg-image);background-position:center;background-repeat:no-repeat;background-size:cover;padding-bottom:100px;padding-top:100px}.gb-element-93badf17{background-color:rgba(27,120,227,0.72);color:var(--base-3);margin-left:auto;margin-right:auto;max-width:1160px;text-align:center;border-radius:5px;padding:60px 20px}.gb-element-bf9c793f{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:80px 20px 40px 20px}@media (max-width:767px){.gb-element-bf9c793f{padding-bottom:60px;padding-top:60px}}.gb-element-03bb26a5{align-items:center;border-bottom-color:var(--global-color-7)}.gb-element-817d0990{margin-bottom:30px;margin-left:auto;margin-right:auto;width:10%;border-top:3px solid var(--accent-2)}.gb-element-7fbf0c12{column-gap:1.5em;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));row-gap:1.5em}@media (max-width:767px){.gb-element-7fbf0c12{grid-template-columns:1fr;row-gap:1.5em}}.gb-element-dcfdb50b{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 10px 8px}.gb-element-dcfdb50b:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-080f0e69{padding-left:30px;padding-right:8px;text-align:center}.gb-element-f952711d{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 10px 8px}.gb-element-f952711d:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-9b25b2c5{padding-left:30px;padding-right:8px;text-align:center}.gb-element-556bbf6d{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 10px 8px}.gb-element-556bbf6d:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-f01effe1{padding-left:30px;padding-right:8px;text-align:center}.gb-element-bbe44f83{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:30px 30px 10px 8px}.gb-element-bbe44f83:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-0945459f{padding-left:30px;padding-right:8px;text-align:center}.gb-element-cc34185d{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:40px 20px 80px 20px}@media (max-width:767px){.gb-element-cc34185d{padding-bottom:60px;padding-top:60px}}.gb-element-e49b6a68{align-items:center;border-bottom-color:var(--global-color-7)}.gb-element-9704f49b{margin-bottom:30px;margin-left:auto;margin-right:auto;width:10%;border-top:3px solid var(--accent-2)}.gb-element-cc72d6c3{column-gap:1em;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));row-gap:1em}@media (max-width:767px){.gb-element-cc72d6c3{grid-template-columns:1fr;row-gap:1.5em}}.gb-element-0d2725ff{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:20px}.gb-element-0d2725ff:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-0ccb957e{column-gap:1em;display:grid;grid-template-columns:1fr 3fr;row-gap:1em}@media (max-width:767px){.gb-element-0ccb957e{grid-template-columns:1fr}}.gb-element-5e4c89d8{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:20px}.gb-element-5e4c89d8:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-ab84b3b5{column-gap:1em;display:grid;grid-template-columns:1fr 3fr;row-gap:1em}@media (max-width:767px){.gb-element-ab84b3b5{grid-template-columns:1fr}}.gb-element-83eea067{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:20px}.gb-element-83eea067:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-656e9daf{column-gap:1em;display:grid;grid-template-columns:1fr 3fr;row-gap:1em}@media (max-width:767px){.gb-element-656e9daf{grid-template-columns:1fr}}.gb-element-1cf72096{background-color:var(--base-2);box-shadow:0px 0px 5px 1px rgba(0,0,0,0.1);display:block;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.2);border-radius:10px;padding:20px}.gb-element-1cf72096:is(:hover,:focus){transform:translate3d(0px,-5px,0px);border:1px solid rgba(27,120,227,0.53)}.gb-element-a4f0c7fe{column-gap:1em;display:grid;grid-template-columns:1fr 3fr;row-gap:1em}@media (max-width:767px){.gb-element-a4f0c7fe{grid-template-columns:1fr}}.gb-element-38c69647{box-shadow:0px 0px 4px 2px rgba(0,0,0,0.1);margin-bottom:60px;margin-left:auto;margin-right:auto;max-width:1160px;border:1px solid rgba(135,135,135,0.29);border-radius:10px;padding:30px 20px}.gb-element-ca749091{box-shadow:0px 0px 5px 3px rgba(0,0,0,0.1);margin-bottom:30px;border:1px solid rgba(135,135,135,0.34);border-radius:6px;padding:20px}.gb-element-6f418cf9{box-shadow:0px 0px 5px 3px rgba(0,0,0,0.1);margin-bottom:30px;border:1px solid rgba(135,135,135,0.34);border-radius:6px;padding:20px 20px 10px 20px}.gb-element-80d35441{background-color:#f6f6f6;border-top:1px solid rgba(135,135,135,0.52)}.gb-element-e3cf7d4a{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:40px 20px}.gb-element-084a3b6e{column-gap:2em;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));row-gap:1em}@media (max-width:767px){.gb-element-084a3b6e{grid-template-columns:1fr}}.gb-element-3ff89166{text-align:center}.gb-element-b12f2b12{text-align:center}.gb-element-13da7ed5{padding-left:60px;text-align:left}@media (max-width:767px){.gb-element-13da7ed5{padding-left:0px}}.gb-element-d10d2533{padding-left:20px}@media (max-width:767px){.gb-element-d10d2533{padding-left:0px}}.gb-element-76365dcf{display:flex;justify-content:space-between;margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:20px}@media (max-width:1024px){.gb-element-76365dcf{align-items:center;flex-direction:column;justify-content:center;row-gap:20px}}.gb-element-6dacc793{column-gap:15px;display:flex}@media (max-width:1024px){.gb-element-6dacc793{order:-1}}.gb-element-7de49033{background-color:var(--base-2)}.gb-element-d4b812ad{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:20px}.gb-element-511ef82e{column-gap:1em;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));row-gap:1em}@media (max-width:767px){.gb-element-511ef82e{grid-template-columns:1fr}}.gb-text-193bb6ca{color:var(--base-3);margin-bottom:40px}.gb-text-e24a13ba{color:var(--accent-2);font-size:55px;letter-spacing:5px;line-height:1.5;margin-bottom:40px}.gb-text-169e8bed{color:var(--base-3)}.gb-text-f2f3f905{color:var(--contrast);font-size:50px;font-weight:400;margin-bottom:30px;text-align:center}.gb-text-0e0453c1{font-size:40px;font-weight:500;margin-bottom:30px;text-align:center}@media (max-width:767px){.gb-text-0e0453c1{font-size:25px;font-weight:600}}.gb-text-a0a8ba26{column-gap:0.5em;display:block;font-weight:600;text-align:center}.gb-text-a0a8ba26 .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-a0a8ba26{font-size:24px}}.gb-text-495f982c{column-gap:0.5em;display:block;font-weight:600;text-align:center}.gb-text-495f982c .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-495f982c{font-size:24px}}.gb-text-0dc40ddd{column-gap:0.5em;display:block;font-weight:600;text-align:center}.gb-text-0dc40ddd .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-0dc40ddd{font-size:24px}}.gb-text-afe09214{column-gap:0.5em;display:block;font-weight:600;text-align:center}.gb-text-afe09214 .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-afe09214{font-size:24px}}.gb-text-967f6727{column-gap:0.5em;display:block;font-weight:600;text-align:center}.gb-text-967f6727 .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-967f6727{font-size:24px}}.gb-text-0f3d2feb{column-gap:0.5em;display:block;font-weight:600;text-align:center}.gb-text-0f3d2feb .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-0f3d2feb{font-size:24px}}.gb-text-87ab5dab{color:var(--base-3);text-align:center}.gb-text-ee938c30{margin-bottom:0px}.gb-text-5d3337d5{color:var(--contrast);font-size:40px;font-weight:500;margin-bottom:30px;text-align:center}@media (max-width:767px){.gb-text-5d3337d5{font-size:25px;font-weight:600}}.gb-text-5fe57c83{column-gap:0.5em;display:block;font-weight:600;padding-left:20px;text-align:center}.gb-text-5fe57c83 .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-5fe57c83{font-size:24px}}.gb-text-aae11c36{column-gap:0.5em;display:block;font-weight:600;padding-left:20px;text-align:center}.gb-text-aae11c36 .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-aae11c36{font-size:24px}}.gb-text-82f291f1{column-gap:0.5em;display:block;font-weight:600;padding-left:20px;text-align:center}.gb-text-82f291f1 .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-82f291f1{font-size:24px}}.gb-text-dea3d70e{column-gap:0.5em;display:block;font-weight:600;padding-left:20px;text-align:center}.gb-text-dea3d70e .gb-shape svg{width:1em;height:1em;fill:currentColor}@media (max-width:767px){.gb-text-dea3d70e{font-size:24px}}.gb-text-d42e626f{color:var(--contrast);font-size:40px;font-weight:500;margin-bottom:30px;text-align:center}@media (max-width:767px){.gb-text-d42e626f{font-size:25px;font-weight:600}}.gb-text-75fb0c21{color:var(--accent-hover);font-size:12px;min-height:200px}.gb-text-aa90639b{color:var(--accent-hover);font-size:12px;min-height:200px}.gb-text-c48aea3b{color:var(--accent-hover);font-size:12px;min-height:200px}.gb-text-5434fef7{color:var(--accent-hover);font-size:12px;min-height:200px}.gb-text-20281697{color:var(--contrast);font-size:38px;font-weight:400;padding-left:10px;text-align:left;width:350px;border-left:4px solid var(--accent-2)}@media (max-width:767px){.gb-text-20281697{font-size:25px;font-weight:600}}.gb-text-e90ae470{font-size:15px;font-weight:600;margin-bottom:0px}.gb-text-286dab72{font-size:15px;font-weight:600;margin-bottom:0px}.gb-text-a47aa7db{font-size:15px;font-weight:600;margin-bottom:0px}.gb-text-74b2cf67{font-size:15px;font-weight:600;margin-bottom:0px}.gb-text-33432665{font-weight:700;text-align:left}.gb-text-09cfc29e{margin-bottom:10px}.gb-text-0897f7de{margin-bottom:10px}.gb-text-b5d58ce0{margin-bottom:10px}.gb-text-1a21da79{margin-bottom:0px}.gb-text-c3994867{font-size:15px;margin-bottom:0px}@media (max-width:767px){.gb-text-c3994867{text-align:center}}.gb-text-7720e281{display:block;font-size:15px;margin-bottom:0px;text-align:right}.gb-text-7720e281 a{color:var(--contrast-2)}.gb-text-7720e281 a:hover{color:var(--contrast-3);font-size:15px}.gb-shape-67aa0073{align-items:center;background-color:var(--accent-2);border-bottom-style:solid;border-bottom-width:0px;border-left-style:solid;border-left-width:0px;border-right-style:solid;border-right-width:0px;border-top-style:solid;border-top-width:0px;display:inline-flex;font-size:25px;font-weight:700;justify-content:center;margin-bottom:20px;object-fit:fill;border-radius:100px;padding:15px}.gb-shape-67aa0073 svg{fill:currentColor;height:40px;width:40px;color:var(--base-3);font-size:30px}.gb-shape-26a52e65{align-items:center;background-color:var(--accent-2);border-bottom-style:solid;border-bottom-width:0px;border-left-style:solid;border-left-width:0px;border-right-style:solid;border-right-width:0px;border-top-style:solid;border-top-width:0px;display:inline-flex;font-size:25px;font-weight:700;justify-content:center;margin-bottom:20px;object-fit:fill;border-radius:100px;padding:15px}.gb-shape-26a52e65 svg{fill:currentColor;height:40px;width:40px;color:var(--base-3);font-size:30px}.gb-shape-a5802c71{align-items:center;background-color:var(--accent-2);border-bottom-style:solid;border-bottom-width:0px;border-left-style:solid;border-left-width:0px;border-right-style:solid;border-right-width:0px;border-top-style:solid;border-top-width:0px;display:inline-flex;font-size:25px;font-weight:700;justify-content:center;margin-bottom:20px;object-fit:fill;border-radius:100px;padding:15px}.gb-shape-a5802c71 svg{fill:currentColor;height:40px;width:40px;color:var(--base-3);font-size:30px}.gb-shape-0475d889{align-items:center;background-color:var(--accent-2);border-bottom-style:solid;border-bottom-width:0px;border-left-style:solid;border-left-width:0px;border-right-style:solid;border-right-width:0px;border-top-style:solid;border-top-width:0px;display:inline-flex;font-size:25px;font-weight:700;justify-content:center;margin-bottom:20px;object-fit:fill;border-radius:100px;padding:15px}.gb-shape-0475d889 svg{fill:currentColor;height:40px;width:40px;color:var(--base-3);font-size:30px}.gb-shape-91579803{align-items:center;background-color:var(--accent-2);border-bottom-style:solid;border-bottom-width:0px;border-left-style:solid;border-left-width:0px;border-right-style:solid;border-right-width:0px;border-top-style:solid;border-top-width:0px;display:inline-flex;font-size:25px;font-weight:700;justify-content:center;margin-bottom:20px;object-fit:fill;border-radius:100px;padding:15px}.gb-shape-91579803 svg{fill:currentColor;height:40px;width:40px;color:var(--base-3);font-size:30px}.gb-shape-c6ccb341{align-items:center;background-color:var(--accent-2);border-bottom-style:solid;border-bottom-width:0px;border-left-style:solid;border-left-width:0px;border-right-style:solid;border-right-width:0px;border-top-style:solid;border-top-width:0px;display:inline-flex;font-size:25px;font-weight:700;justify-content:center;margin-bottom:20px;object-fit:fill;border-radius:100px;padding:15px}.gb-shape-c6ccb341 svg{fill:currentColor;height:40px;width:40px;color:var(--base-3);font-size:30px}.gb-shape-280325fc{align-items:center;display:inline-flex;font-size:25px;font-weight:700;height:40px;justify-content:center;margin-bottom:20px;width:40px;border-radius:100px}.gb-shape-280325fc svg{fill:currentColor;height:auto;width:60px;color:var(--accent-2);font-size:30px}.gb-shape-41fb84cb{align-items:center;display:inline-flex;font-size:25px;font-weight:700;height:40px;justify-content:center;margin-bottom:20px;margin-left:auto;width:40px;border-radius:100px}.gb-shape-41fb84cb svg{fill:currentColor;height:auto;width:60px;color:var(--accent-2);font-size:30px}.gb-shape-3ee27093{align-items:center;display:inline-flex;font-size:25px;font-weight:700;height:40px;justify-content:center;margin-bottom:20px;margin-left:auto;width:40px;border-radius:100px}.gb-shape-3ee27093 svg{fill:currentColor;height:auto;width:60px;color:var(--accent-2);font-size:30px}.gb-shape-5ef9faca{align-items:center;display:inline-flex;font-size:25px;font-weight:700;height:40px;justify-content:center;margin-bottom:20px;margin-left:auto;width:40px;border-radius:100px}.gb-shape-5ef9faca svg{fill:currentColor;height:auto;width:60px;color:var(--accent-2);font-size:30px}.gb-media-176e053f{background-color:var(--contrast-3);height:auto;max-width:100%;object-fit:cover;width:auto;border-radius:100px}.gb-media-c8bb6e14{background-color:var(--contrast-3);height:auto;max-width:100%;object-fit:cover;width:auto;border-radius:100px}.gb-media-25b77a9a{background-color:var(--contrast-3);height:auto;max-width:100%;object-fit:cover;width:auto;border-radius:100px}.gb-media-6e39e858{background-color:var(--contrast-3);height:auto;max-width:100%;object-fit:cover;width:auto;border-radius:100px}.gb-media-dab60b27{height:auto;max-width:100%;object-fit:cover;width:100%}.gb-media-1aec2793{height:auto;max-width:100%;object-fit:cover;width:100%}.gb-tabs-513a33df{column-gap:20px;display:flex;flex-direction:column;row-gap:20px}.gb-tabs__menu-6aae225b{align-items:center;column-gap:15px;display:flex;justify-content:center}@media (max-width:767px){.gb-tabs__menu-6aae225b{max-width:100%;overflow-x:auto}}.gb-tabs__menu-item-4a18984e{background-color:var(--base);box-shadow:0px 0px 10px 1px rgba(0,0,0,0.1);color:#000000;margin-bottom:0px;transition:all 0.2s ease 0s;border:2px solid rgba(135,135,135,0.26);border-radius:6px;padding:8px 20px}.gb-tabs__menu-item-4a18984e:is(.gb-block-is-current,.gb-block-is-current:hover,.gb-block-is-current:focus){background-color:var(--accent-2);color:var(--base)}.gb-tabs__menu-item-4a18984e:is(:hover,:focus){color:#000000;background-color:#000000;border:2px solid var(--accent-2)}@media (max-width:767px){.gb-tabs__menu-item-4a18984e{flex-grow:1;flex-shrink:0}}.gb-tabs__menu-item-87819c3d{background-color:var(--base);box-shadow:0px 0px 10px 1px rgba(0,0,0,0.1);color:#000000;margin-bottom:0px;transition:all 0.2s ease 0s;border:2px solid rgba(135,135,135,0.26);border-radius:6px;padding:8px 20px}.gb-tabs__menu-item-87819c3d:is(.gb-block-is-current,.gb-block-is-current:hover,.gb-block-is-current:focus){background-color:var(--accent-2);color:var(--base)}.gb-tabs__menu-item-87819c3d:is(:hover,:focus){background-color:#fafafa;color:#000000;border:2px solid var(--accent-2)}@media (max-width:767px){.gb-tabs__menu-item-87819c3d{flex-grow:1;flex-shrink:0}}.gb-tabs__menu-item-13e7b89e{background-color:var(--base);box-shadow:0px 0px 10px 1px rgba(0,0,0,0.1);color:#000000;margin-bottom:0px;transition:all 0.2s ease 0s;border:2px solid rgba(135,135,135,0.26);border-radius:6px;padding:8px 20px}.gb-tabs__menu-item-13e7b89e:is(.gb-block-is-current,.gb-block-is-current:hover,.gb-block-is-current:focus){background-color:var(--accent-2);color:var(--base)}.gb-tabs__menu-item-13e7b89e:is(:hover,:focus){background-color:#fafafa;color:#000000;border:2px solid var(--accent-2)}@media (max-width:767px){.gb-tabs__menu-item-13e7b89e{flex-grow:1;flex-shrink:0}}.gb-tabs__menu-item-85742302{background-color:var(--base);box-shadow:0px 0px 10px 1px rgba(0,0,0,0.1);color:#000000;margin-bottom:0px;transition:all 0.2s ease 0s;border:2px solid rgba(135,135,135,0.26);border-radius:6px;padding:8px 20px}.gb-tabs__menu-item-85742302:is(.gb-block-is-current,.gb-block-is-current:hover,.gb-block-is-current:focus){background-color:var(--accent-2);color:var(--base)}.gb-tabs__menu-item-85742302:is(:hover,:focus){background-color:#fafafa;color:#000000;border:2px solid var(--accent-2)}@media (max-width:767px){.gb-tabs__menu-item-85742302{flex-grow:1;flex-shrink:0}}

:root{--gb-container-width:1200px;}.gb-container .wp-block-image img{vertical-align:middle;}.gb-grid-wrapper .wp-block-image{margin-bottom:0;}.gb-highlight{background:none;}.gb-shape{line-height:0;}.gb-container-link{position:absolute;top:0;right:0;bottom:0;left:0;z-index:99;}.gb-element-45d59580{background-color:var(--base-3)}.gb-element-733c2acb{background-color:var(--base-3);margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:80px 20px}@media (max-width:767px){.gb-element-733c2acb{padding-bottom:60px;padding-top:60px}}.gb-element-8ba48021{align-items:center;display:flex;flex-direction:column;padding-bottom:20px;padding-left:20px;padding-right:20px;text-align:left}.gb-element-b384802b{align-items:center;column-gap:10px;display:flex;justify-content:center;margin-top:20px;row-gap:10px}.gb-element-ca749091{box-shadow:0px 0px 5px 3px rgba(0,0,0,0.1);margin-bottom:30px;border:1px solid rgba(135,135,135,0.34);border-radius:6px;padding:20px}.gb-element-6f418cf9{box-shadow:0px 0px 5px 3px rgba(0,0,0,0.1);margin-bottom:30px;border:1px solid rgba(135,135,135,0.34);border-radius:6px;padding:20px 20px 10px 20px}.gb-element-80d35441{background-color:#f6f6f6;border-top:1px solid rgba(135,135,135,0.52)}.gb-element-e3cf7d4a{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:40px 20px}.gb-element-084a3b6e{column-gap:2em;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));row-gap:1em}@media (max-width:767px){.gb-element-084a3b6e{grid-template-columns:1fr}}.gb-element-3ff89166{text-align:center}.gb-element-b12f2b12{text-align:center}.gb-element-13da7ed5{padding-left:60px;text-align:left}@media (max-width:767px){.gb-element-13da7ed5{padding-left:0px}}.gb-element-d10d2533{padding-left:20px}@media (max-width:767px){.gb-element-d10d2533{padding-left:0px}}.gb-element-76365dcf{display:flex;justify-content:space-between;margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:20px}@media (max-width:1024px){.gb-element-76365dcf{align-items:center;flex-direction:column;justify-content:center;row-gap:20px}}.gb-element-6dacc793{column-gap:15px;display:flex}@media (max-width:1024px){.gb-element-6dacc793{order:-1}}.gb-element-7de49033{background-color:var(--base-2)}.gb-element-d4b812ad{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:20px}.gb-element-511ef82e{column-gap:1em;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));row-gap:1em}@media (max-width:767px){.gb-element-511ef82e{grid-template-columns:1fr}}.gb-looper-e75fb7a3{column-gap:25px;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));row-gap:30px}@media (max-width:767px){.gb-looper-e75fb7a3{grid-template-columns:1fr}}.gb-loop-item-78be825f{background-color:var(--base-2);box-shadow:0px 0px 5px 0px rgba(0,0,0,0.1);display:block;position:relative;text-align:center;transition:all 0.3s ease 0s;border:1px solid rgba(180,180,191,0.21);border-radius:10px}.gb-loop-item-78be825f:is(:hover,:focus){transform:translate3d(0px,-3px,0px)}.gb-media-46f33b3d{border-top-left-radius:10px;border-top-right-radius:10px;display:block;height:180px;margin-bottom:20px;max-width:100%;position:relative;width:100%}.gb-media-dab60b27{height:auto;max-width:100%;object-fit:cover;width:100%}.gb-media-1aec2793{height:auto;max-width:100%;object-fit:cover;width:100%}.gb-text-52b92314{display:inline-flex;font-size:14px;margin-bottom:10px;text-align:center}.gb-text-b6d6dcf9{font-size:18px;font-weight:500;line-height:1.3;margin-bottom:20px;text-align:center}.gb-text-b6d6dcf9 a:hover{color:var(--global-color-7)}.gb-text-a9fddb18{font-size:14px;line-height:1.5;margin-bottom:20px;text-align:center}.gb-text-945867ce{align-items:center;background-color:#215bc2;color:#ffffff;display:inline-flex;text-decoration:none;border-radius:30px;padding:0.7rem 3rem}.gb-text-945867ce:is(:hover,:focus){background-color:#1a4a9b;color:#ffffff}.gb-text-313a478a{background-color:#ffffff;color:#000000;display:inline-flex;font-size:14px;line-height:1;text-decoration:none;border:1px solid #000;border-radius:30px;padding:1rem}.gb-text-6c88e5ee{background-color:#ffffff;color:#000000;display:inline-flex;font-size:14px;line-height:1;text-decoration:none;border:1px solid #000;border-radius:30px;padding:1rem}.gb-text-33432665{font-weight:700;text-align:left}.gb-text-09cfc29e{margin-bottom:10px}.gb-text-0897f7de{margin-bottom:10px}.gb-text-b5d58ce0{margin-bottom:10px}.gb-text-1a21da79{margin-bottom:0px}.gb-text-c3994867{font-size:15px;margin-bottom:0px}@media (max-width:767px){.gb-text-c3994867{text-align:center}}.gb-text-7720e281{display:block;font-size:15px;margin-bottom:0px;text-align:right}.gb-text-7720e281 a{color:var(--contrast-2)}.gb-text-7720e281 a:hover{color:var(--contrast-3);font-size:15px}.gb-query-page-numbers-0b936250{align-items:center;column-gap:5px;display:flex;justify-content:center;row-gap:5px}.gb-query-page-numbers-0b936250 .page-numbers{background-color:#ffffff;color:#000000;display:inline-flex;text-decoration:none;line-height:1;font-size:14px;border:1px solid #000;padding:1rem}.gb-query-page-numbers-0b936250 .page-numbers.current{border-top-width:0;border-right-width:0;border-bottom-width:0;border-left-width:0}.gb-query-page-numbers-0b936250 .page-numbers.dots{border-top-width:0;border-right-width:0;border-bottom-width:0;border-left-width:0}
</style>
CSS;
}

function csop_pages_front_js() {
    static $done = false;
    if ($done) return '';
    $done = true;
    return <<<'JS'
<script id="csop-menu-pages-front-js">
(function(){
  function initReveal(){
    var roots = document.querySelectorAll('.csop-menu-page');
    if (!roots.length) return;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var observer = !reduce && 'IntersectionObserver' in window ? new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if (!entry.isIntersecting) return;
        entry.target.classList.add('csop-in');
        observer.unobserve(entry.target);
      });
    }, {threshold: 0.1, rootMargin: '0px 0px -8% 0px'}) : null;
    roots.forEach(function(root){
      var targets = root.querySelectorAll('.inside-article, .widget-area, .gb-loop-item-78be825f, .wp-block-media-text, .entry-content > .gb-element-45d59580');
      targets.forEach(function(el, index){
        el.classList.add('csop-reveal', 'csop-reveal-delay-' + (index % 4));
        if (reduce || !observer) el.classList.add('csop-in'); else observer.observe(el);
      });
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initReveal); else initReveal();
  document.addEventListener('click', function(event){
    var menuItem = event.target.closest('.csop-menu-page .gb-tabs__menu-item');
    if (!menuItem) return;
    var tabs = menuItem.closest('.gb-tabs');
    if (!tabs) return;
    var items = Array.prototype.slice.call(tabs.querySelectorAll('.gb-tabs__menu-item'));
    var index = items.indexOf(menuItem);
    if (index < 0) return;
    items.forEach(function(item){ item.classList.remove('gb-block-is-current'); });
    menuItem.classList.add('gb-block-is-current');
    tabs.querySelectorAll('.gb-tabs__item').forEach(function(panel, i){ panel.classList.toggle('gb-tabs__item-open', i === index); });
    tabs.setAttribute('data-opened-tab', String(index + 1));
  });
})();
</script>
JS;
}

function csop_pages_admin_css() {
    return <<<'CSS'
.csop-pages-admin-wrap .csop-pages-admin-tabs{display:flex;flex-wrap:wrap;gap:8px;margin:16px 0 18px}.csop-pages-admin-wrap .csop-pages-admin-layout{display:grid;grid-template-columns:minmax(420px,560px) 1fr;gap:24px;align-items:start}.csop-pages-admin-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.06)}.csop-pages-admin-card code{font-size:13px}.csop-pages-admin-head,.csop-pages-preview-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:14px}.csop-pages-admin-head h2{margin:0 0 8px}.csop-pages-admin-head p,.csop-pages-preview-head span{color:#667085;margin:0;line-height:1.55}.csop-pages-meta{display:grid;gap:8px;background:#f7f9fc;border:1px solid #e5eaf2;border-radius:8px;color:#455468;margin:0 0 14px;padding:12px}.csop-pages-tool-row{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 12px}.csop-pages-field{display:grid;gap:8px}.csop-pages-field span{font-weight:700}.csop-pages-field textarea{width:100%;min-height:620px;font-family:Consolas,Monaco,monospace;font-size:12px;line-height:1.55;border:1px solid #cfd6e4;border-radius:8px;padding:12px;resize:vertical}.csop-pages-save-bar{position:sticky;bottom:0;display:flex;gap:10px;flex-wrap:wrap;background:#fff;border-top:1px solid #eef0f4;margin:16px -18px -18px;padding:14px 18px}.csop-pages-admin-preview-card{min-width:0;position:sticky;top:32px}.csop-pages-preview-frame{height:760px;overflow:auto;background:#eef0f5;padding:22px;border-radius:8px}.csop-pages-preview-scale{width:1440px;transform:scale(.58);transform-origin:top left;background:#fafafa;box-shadow:0 14px 42px rgba(15,23,42,.2);min-height:900px}.csop-menu-page.csop-preview-highlight{outline:3px solid #1b78e2;outline-offset:4px}@media(max-width:1200px){.csop-pages-admin-wrap .csop-pages-admin-layout{grid-template-columns:1fr}.csop-pages-admin-preview-card{position:static}.csop-pages-preview-frame{height:640px}}
CSS;
}

function csop_pages_admin_js() {
    return <<<'JS'
jQuery(function($){
  var textarea = $('[data-csop-pages-html]');
  var preview = $('[data-csop-pages-preview] .csop-menu-page');
  var timer = null;

  function refreshPreview(){
    if (!textarea.length || !preview.length) return;
    preview.html(textarea.val());
    preview.addClass('csop-preview-highlight');
    window.setTimeout(function(){ preview.removeClass('csop-preview-highlight'); }, 520);
  }

  function insertAtCursor(markup){
    if (!textarea.length) return;
    var el = textarea.get(0);
    var value = textarea.val();
    var start = el.selectionStart || 0;
    var end = el.selectionEnd || 0;
    textarea.val(value.slice(0, start) + markup + value.slice(end));
    el.focus();
    el.selectionStart = el.selectionEnd = start + markup.length;
    refreshPreview();
  }

  textarea.on('input', function(){
    window.clearTimeout(timer);
    timer = window.setTimeout(refreshPreview, 240);
  });

  $('[data-csop-pages-refresh]').on('click', refreshPreview);

  $('[data-csop-pages-fit]').on('click', function(){
    var frame = $('.csop-pages-preview-frame');
    var scale = $('.csop-pages-preview-scale');
    if (!frame.length || !scale.length) return;
    var next = frame.width() < 980 ? 0.44 : 0.58;
    scale.css('transform', 'scale(' + next + ')');
  });

  $('[data-csop-pages-button]').on('click', function(){
    insertAtCursor('<div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="/contact/">立即咨询</a></div></div>');
  });

  $('[data-csop-pages-media]').on('click', function(){
    if (!window.wp || !wp.media) return;
    var frame = wp.media({title: '选择或上传图片', button: {text: '插入图片'}, multiple: false});
    frame.on('select', function(){
      var item = frame.state().get('selection').first().toJSON();
      var alt = item.alt || item.title || '';
      insertAtCursor('<figure class="wp-block-image"><img src="' + item.url + '" alt="' + alt.replace(/"/g, '&quot;') + '"></figure>');
    });
    frame.open();
  });

  document.addEventListener('click', function(event){
    var frameLink = event.target.closest('.csop-pages-preview-frame a');
    if (frameLink) event.preventDefault();
  });
});
JS;
}

function csop_pages_blog_thumb($pid) {
    $t = get_the_post_thumbnail_url($pid, 'large');
    if ($t) return $t;
    $content = get_post_field('post_content', $pid);
    if ($content && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $m)) return $m[1];
    return '';
}

/**
 * Estimate reading time (in minutes) from rendered HTML content.
 * Counts CJK characters individually plus whitespace-delimited words.
 */
function csop_article_reading_minutes($html) {
    $text = wp_strip_all_tags((string) $html);
    $text = trim(preg_replace('/\s+/u', ' ', $text));
    if ($text === '') return 1;
    $cjk = preg_match_all('/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}]/u', $text);
    $stripped = preg_replace('/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}]/u', ' ', $text);
    $words = preg_match_all('/[A-Za-z0-9]+/u', $stripped);
    // ~300 CJK chars/min, ~220 English words/min.
    $minutes = ($cjk / 300) + ($words / 220);
    return max(1, (int) ceil($minutes));
}

/**
 * Category label + slug for the current post (falls back to the pretty section).
 */
function csop_article_primary_category($post_id) {
    $cats = get_the_category($post_id);
    if (is_array($cats)) {
        foreach ($cats as $cat) {
            if (!empty($cat->slug) && $cat->slug !== 'uncategorized') {
                return array('name' => $cat->name, 'url' => get_category_link($cat->term_id));
            }
        }
    }
    $blog = home_url('/blog/');
    if (function_exists('csop_ml_current_lang') && function_exists('csop_ml_lang_url')) {
        $blog = csop_ml_lang_url($blog, csop_ml_current_lang());
    }
    return array('name' => '面试真题', 'url' => $blog);
}

/**
 * Render the right-hand contact / related sidebar for single posts.
 * Reuses the header/footer contact options so nothing needs re-entering.
 */
function csop_article_sidebar_html($post_id) {
    $s = function_exists('csop_hf_get_options') ? csop_hf_get_options() : array();
    $get = function ($key, $default = '') use ($s) {
        return isset($s[$key]) && $s[$key] !== '' ? $s[$key] : $default;
    };

    $wechat = $get('wechat_text', 'Coding0201');
    $email = $get('email_text', '');
    $email_url = $get('email_url', $email ? 'mailto:' . $email : '');
    $whatsapp = $get('whatsapp_text', '');
    $whatsapp_url = $get('whatsapp_url', '');
    $telegram = $get('telegram_text', '');
    $telegram_url = $get('telegram_url', '');
    $qr = $get('qr_wechat_image', '');
    $qr_label = $get('qr_wechat_label', $wechat ? '微信 ' . $wechat : '');

    // Related posts (same category if possible), excluding the current one.
    $cats = wp_get_post_categories($post_id);
    $related_args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 4,
        'post__not_in' => array($post_id),
        'orderby' => 'date',
        'order' => 'DESC',
        'ignore_sticky_posts' => true,
    );
    if (!empty($cats)) $related_args['category__in'] = $cats;
    $related = get_posts($related_args);
    if (count($related) < 3) {
        $related = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 4,
            'post__not_in' => array($post_id),
            'orderby' => 'date',
            'order' => 'DESC',
            'ignore_sticky_posts' => true,
        ));
    }

    ob_start();
    ?>
    <aside class="csop-article-sidebar">
        <div class="csop-article-contact">
            <p class="csop-article-kicker">Get In Touch</p>
            <h3>联系学长</h3>
            <p class="csop-article-contact-lead">有 OA / VO / Mock 需求？扫码或直接联系，通常几分钟内回复。</p>
            <?php if ($qr): ?>
                <div class="csop-article-qr">
                    <img src="<?php echo esc_url($qr); ?>" alt="<?php echo esc_attr($qr_label); ?>" loading="lazy" />
                    <?php if ($qr_label): ?><span><?php echo esc_html($qr_label); ?></span><?php endif; ?>
                </div>
            <?php endif; ?>
            <ul class="csop-article-contact-list">
                <?php if ($wechat): ?><li><strong>微信</strong><span><?php echo esc_html($wechat); ?></span></li><?php endif; ?>
                <?php if ($email): ?><li><strong>Email</strong><a href="<?php echo esc_url($email_url); ?>"><?php echo esc_html($email); ?></a></li><?php endif; ?>
                <?php if ($whatsapp): ?><li><strong>WhatsApp</strong><a href="<?php echo esc_url($whatsapp_url); ?>"><?php echo esc_html($whatsapp); ?></a></li><?php endif; ?>
                <?php if ($telegram): ?><li><strong>Telegram</strong><a href="<?php echo esc_url($telegram_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($telegram); ?></a></li><?php endif; ?>
            </ul>
        </div>
        <?php if ($related): ?>
            <div class="csop-article-related">
                <p class="csop-article-kicker">Related</p>
                <h3>相关文章</h3>
                <ul>
                    <?php foreach ($related as $rp):
                        $rlink = get_permalink($rp);
                        if (function_exists('csop_pretty_article_url')) {
                            $lang = function_exists('csop_ml_current_lang') ? csop_ml_current_lang() : 'zh';
                            $pretty = csop_pretty_article_url($rp->ID, $lang);
                            if ($pretty !== '') $rlink = $pretty;
                        }
                        $rtitle = get_the_title($rp);
                        if (function_exists('csop_ml_get_post_value')) {
                            $rtitle = csop_ml_get_post_value($rp->ID, 'title', $rtitle);
                        }
                        $rthumb = csop_pages_blog_thumb($rp->ID);
                    ?>
                        <li>
                            <a href="<?php echo esc_url($rlink); ?>">
                                <?php if ($rthumb): ?>
                                    <span class="csop-article-related-thumb" style="background-image:url('<?php echo esc_url($rthumb); ?>')"></span>
                                <?php else: ?>
                                    <span class="csop-article-related-thumb csop-article-related-fallback"></span>
                                <?php endif; ?>
                                <span class="csop-article-related-meta">
                                    <span class="csop-article-related-title"><?php echo esc_html($rtitle); ?></span>
                                    <span class="csop-article-related-date"><?php echo esc_html(get_the_date('Y-m-d', $rp)); ?></span>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </aside>
    <?php
    return ob_get_clean();
}

/**
 * Repair common markup breakages inside post body HTML that can cascade and
 * destroy the surrounding page layout. Specifically:
 *  - <a href> / src attributes closed with a curly quote (" " ' ') instead of a
 *    straight quote, which makes the browser swallow following markup into the
 *    attribute value until the next straight quote.
 *  - Inline <em>/<strong> tags injected into the middle of a URL (from underscores
 *    in the URL being treated as markdown emphasis).
 */
function csop_article_repair_html($html) {
    if (!is_string($html) || $html === '') return $html;

    // Curly-quote closer as raw chars OR HTML entities (decimal/hex/named).
    $curly = '(?:[\x{201C}\x{201D}\x{2018}\x{2019}]|&#8216;|&#8217;|&#8220;|&#8221;|&#x201[6789ABCD];|&[lr][sd]quo;)';

    // 1. Fix double-quoted href/src attributes closed by a curly quote instead
    //    of a straight one. The value part must not contain a straight " (so a
    //    well-formed attribute is left untouched); it stops at the first curly
    //    closer, which we drop and replace with a proper straight quote.
    $html = preg_replace_callback(
        '/\b(href|src)\s*=\s*"((?:(?!")[\s\S])*?)' . $curly . '/u',
        function ($m) {
            // Strip any inline tags that leaked into the URL (e.g. <em>_</em>).
            $val = preg_replace('/<\/?(?:em|strong|b|i|code)>/i', '', $m[2]);
            return $m[1] . '="' . $val . '"';
        },
        $html
    );

    // 2. Same for single-quoted attributes.
    $html = preg_replace_callback(
        "/\\b(href|src)\\s*=\\s*'((?:(?!')[\\s\\S])*?)" . $curly . "/u",
        function ($m) {
            $val = preg_replace('/<\/?(?:em|strong|b|i|code)>/i', '', $m[2]);
            return $m[1] . "='" . $val . "'";
        },
        $html
    );

    // 3. Demote any <h1> inside the body to <h2> so the page keeps exactly one
    //    <h1> (the article title we render in the header). Prevents the
    //    "multiple H1" SEO warning when content authors paste an H1.
    $html = preg_replace('/<(\/?)h1(\b[^>]*)>/i', '<$1h2$2>', $html);

    return $html;
}

/**
 * Wrap single-post content in the full article layout (breadcrumb, header meta,
 * featured image, body + contact sidebar). Runs after language filtering and
 * exported-shortcode rendering so $content is final HTML.
 */
function csop_article_layout_wrap($content) {
    if (is_admin() || !is_singular('post') || !in_the_loop() || !is_main_query()) return $content;
    global $post;
    if (!$post || $post->post_type !== 'post') return $content;

    // Guard against double-wrapping.
    if (strpos($content, 'csop-article-page') !== false) return $content;

    // Repair malformed markup in the body so a single broken link can't
    // swallow the sidebar/footer and collapse the whole page layout.
    $content = csop_article_repair_html($content);

    $post_id = $post->ID;
    $title = get_the_title($post_id);
    if (function_exists('csop_ml_get_post_value')) {
        $title = csop_ml_get_post_value($post_id, 'title', $title);
    }
    $cat = csop_article_primary_category($post_id);
    $date = get_the_date('Y年n月j日', $post_id);
    $author = get_the_author_meta('display_name', $post->post_author);
    if ($author === '') $author = 'csvosupport 团队';
    $minutes = csop_article_reading_minutes($content);
    $thumb = has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, 'large') : '';
    $home = home_url('/');
    if (function_exists('csop_ml_current_lang') && function_exists('csop_ml_lang_url')) {
        $home = csop_ml_lang_url($home, csop_ml_current_lang());
    }

    $sidebar = csop_article_sidebar_html($post_id);

    ob_start();
    ?>
    <div class="csop-article-page">
        <div class="csop-article-shell">
            <div class="csop-article-main">
                <nav class="csop-article-crumbs" aria-label="面包屑">
                    <a href="<?php echo esc_url($home); ?>">首页</a>
                    <span aria-hidden="true">›</span>
                    <a href="<?php echo esc_url($cat['url']); ?>"><?php echo esc_html($cat['name']); ?></a>
                    <span aria-hidden="true">›</span>
                    <span class="csop-article-crumbs-current"><?php echo esc_html($title); ?></span>
                </nav>
                <header class="csop-article-header">
                    <p class="csop-article-eyebrow"><?php echo esc_html($cat['name']); ?></p>
                    <h1 class="csop-article-title"><?php echo esc_html($title); ?></h1>
                    <div class="csop-article-meta">
                        <span class="csop-article-author"><?php echo esc_html($author); ?></span>
                        <span class="csop-article-dot" aria-hidden="true">·</span>
                        <span><?php echo esc_html($date); ?></span>
                        <span class="csop-article-dot" aria-hidden="true">·</span>
                        <span><?php echo esc_html($minutes); ?> 分钟阅读</span>
                    </div>
                </header>
                <?php if ($thumb): ?>
                    <figure class="csop-article-hero">
                        <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($title); ?>" />
                    </figure>
                <?php endif; ?>
                <div class="csop-article-body"><?php echo $content; ?></div>
            </div>
            <?php echo $sidebar; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function csop_article_layout_css() {
    if (is_admin() || !is_singular('post')) return;
    ?>
<style id="csop-article-layout-css">
.csop-article-page{--csa-ink:#212121;--csa-blue:#2f4468;--csa-accent:#1b78e2;--csa-muted:#687382;--csa-border:#e3e7eb;--csa-soft:#f7f8f9;font-family:"Open Sans",Arial,"PingFang SC","Microsoft YaHei",sans-serif;color:var(--csa-ink);background:var(--csa-soft);margin:0;padding:0}
.csop-article-page *{box-sizing:border-box}
.csop-article-shell{width:min(1180px,calc(100% - 32px));margin:0 auto;display:flex;gap:32px;align-items:flex-start;padding:34px 0 60px}
.csop-article-main{flex:1 1 auto;min-width:0;background:#fff;border:1px solid var(--csa-border);border-radius:12px;box-shadow:0 1px 2px rgba(60,64,67,.14),0 2px 10px rgba(60,64,67,.08);padding:40px clamp(20px,4vw,56px)}
.csop-article-crumbs{font-size:13px;color:var(--csa-muted);margin-bottom:22px;display:flex;flex-wrap:wrap;gap:8px;align-items:center}
.csop-article-crumbs a{color:var(--csa-accent);text-decoration:none}
.csop-article-crumbs a:hover{text-decoration:underline}
.csop-article-crumbs-current{color:var(--csa-muted);max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.csop-article-header{border-bottom:1px solid var(--csa-border);padding-bottom:22px;margin-bottom:26px}
.csop-article-eyebrow{margin:0 0 10px;color:var(--csa-accent);font-size:12px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
.csop-article-title{margin:0 0 16px;color:var(--csa-blue);font-size:clamp(28px,4vw,42px);line-height:1.18;font-weight:800}
.csop-article-meta{display:flex;flex-wrap:wrap;gap:10px;align-items:center;color:var(--csa-muted);font-size:14px}
.csop-article-author{font-weight:700;color:var(--csa-ink)}
.csop-article-dot{color:#c4ccd4}
.csop-article-hero{margin:0 0 30px;border-radius:10px;overflow:hidden}
.csop-article-hero img{display:block;width:100%;height:auto;max-height:460px;object-fit:cover}
.csop-article-body{font-size:17px;line-height:1.8;color:#2b2f36}
.csop-article-body>*:first-child{margin-top:0}
.csop-article-body>*:last-child{margin-bottom:0}
.csop-article-body h2{color:var(--csa-blue);font-size:clamp(22px,2.6vw,30px);line-height:1.28;font-weight:700;margin:1.8em 0 .7em}
.csop-article-body h3{color:var(--csa-blue);font-size:20px;font-weight:700;margin:1.5em 0 .6em}
.csop-article-body p{margin:0 0 1.4em}
.csop-article-body ul,.csop-article-body ol{margin:0 0 1.4em 1.5em;padding:0}
.csop-article-body li{margin:.4em 0}
.csop-article-body img{max-width:100%;height:auto;border-radius:8px;margin:1em 0}
.csop-article-body a{color:var(--csa-accent);text-decoration:underline}
.csop-article-body pre{background:#1b1f27;color:#eaeef5;border-radius:8px;padding:16px 18px;overflow:auto;font-size:14px;line-height:1.6;margin:0 0 1.4em}
.csop-article-body code{font-family:"SFMono-Regular",Consolas,Menlo,monospace;font-size:.92em}
.csop-article-body :not(pre)>code{background:var(--csa-soft);border:1px solid var(--csa-border);border-radius:4px;padding:2px 6px}
.csop-article-body blockquote{margin:0 0 1.4em;padding:12px 20px;border-left:4px solid var(--csa-accent);background:var(--csa-soft);color:#40484f;border-radius:0 8px 8px 0}
.csop-article-body table{width:100%;border-collapse:collapse;margin:0 0 1.4em;font-size:15px}
.csop-article-body th,.csop-article-body td{border:1px solid var(--csa-border);padding:10px 12px;text-align:left}
.csop-article-body th{background:var(--csa-soft);color:var(--csa-blue);font-weight:700}
.csop-article-sidebar{flex:0 0 320px;max-width:320px;position:sticky;top:24px;display:flex;flex-direction:column;gap:20px}
.csop-article-kicker{margin:0 0 6px;color:var(--csa-accent);font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
.csop-article-contact{background:var(--csa-blue);color:#fff;border-radius:12px;padding:24px}
.csop-article-contact h3{margin:0 0 10px;color:#fff;font-size:22px;font-weight:800}
.csop-article-contact .csop-article-kicker{color:#9fc2ea}
.csop-article-contact-lead{margin:0 0 16px;color:rgba(255,255,255,.82);font-size:14px;line-height:1.7}
.csop-article-qr{background:#fff;border-radius:10px;padding:14px;text-align:center;margin-bottom:16px}
.csop-article-qr img{display:block;width:100%;max-width:180px;height:auto;margin:0 auto 8px;border-radius:6px}
.csop-article-qr span{color:var(--csa-blue);font-size:13px;font-weight:700}
.csop-article-contact-list{list-style:none;margin:0;padding:0}
.csop-article-contact-list li{display:flex;justify-content:space-between;gap:12px;padding:11px 0;border-top:1px solid rgba(255,255,255,.16);font-size:14px}
.csop-article-contact-list strong{color:rgba(255,255,255,.7);font-weight:600}
.csop-article-contact-list a,.csop-article-contact-list span{color:#fff;word-break:break-all;text-align:right}
.csop-article-contact-list a{text-decoration:none}
.csop-article-contact-list a:hover{text-decoration:underline}
.csop-article-related{background:#fff;border:1px solid var(--csa-border);border-radius:12px;padding:22px}
.csop-article-related h3{margin:0 0 14px;color:var(--csa-blue);font-size:19px;font-weight:800}
.csop-article-related ul{list-style:none;margin:0;padding:0}
.csop-article-related li{border-top:1px solid var(--csa-border)}
.csop-article-related li:first-child{border-top:0}
.csop-article-related a{display:flex;gap:12px;align-items:center;padding:12px 0;text-decoration:none}
.csop-article-related-thumb{flex:0 0 64px;width:64px;height:48px;border-radius:6px;background-size:cover;background-position:center;background-color:#dbe4ee}
.csop-article-related-fallback{background:linear-gradient(135deg,#1b78e2,#2f4468)}
.csop-article-related-meta{display:flex;flex-direction:column;gap:4px;min-width:0}
.csop-article-related-title{color:var(--csa-ink);font-size:14px;font-weight:600;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.csop-article-related a:hover .csop-article-related-title{color:var(--csa-accent)}
.csop-article-related-date{color:var(--csa-muted);font-size:12px}
@media(max-width:900px){
.csop-article-shell{flex-direction:column;padding:0 0 40px}
.csop-article-main{border-radius:0;border-left:0;border-right:0;padding:30px 18px}
.csop-article-sidebar{position:static;flex:1 1 auto;max-width:none;width:100%;padding:0 16px}
}
</style>
    <?php
}

function csop_pages_blog_html() {
    $paged = isset($_GET['blog_page']) ? max(1, intval($_GET['blog_page'])) : 1;
    $per_page = 9;
    $q = new WP_Query(array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'paged' => $paged,
        'ignore_sticky_posts' => true,
    ));

    ob_start();
    ?>
    <style id="csop-blog-dynamic-css">
    .csop-menu-page-blog .csop-blog-thumb-fallback{display:block;height:180px;border-top-left-radius:10px;border-top-right-radius:10px;background:linear-gradient(135deg,#1b78e2,#2f4468)}
    .csop-menu-page-blog .csop-blog-empty{background:var(--base-3);border:1px solid rgba(135,135,135,.2);border-radius:10px;padding:60px 20px;text-align:center}
    .csop-menu-page-blog .csop-blog-empty h2{margin-bottom:14px}
    .csop-menu-page-blog .gb-element-b384802b{margin-top:40px}
    </style>
    <div class="site grid-container container hfeed" id="page">
        <div class="site-content" id="content">
            <div class="content-area" id="primary">
                <main class="site-main" id="main">
                    <article class="post type-page status-publish" itemtype="https://schema.org/CreativeWork" itemscope>
                        <div class="inside-article">
                            <div class="entry-content" itemprop="text">
                                <div class="gb-element-45d59580" id="latest-article">
                                    <div class="gb-element-733c2acb">
                                        <div>
                                        <?php if ($q->have_posts()): ?>
                                            <div class="gb-looper-e75fb7a3">
                                            <?php while ($q->have_posts()): $q->the_post();
                                                $pid = get_the_ID();
                                                $link = get_permalink($pid);
                                                if (function_exists('csop_ml_current_lang') && function_exists('csop_ml_lang_url')) {
                                                    $current_lang = csop_ml_current_lang();
                                                    $link = csop_ml_lang_url($link, $current_lang);
                                                }
                                                $thumb = csop_pages_blog_thumb($pid);
                                                $excerpt = wp_trim_words(get_the_excerpt(), 48, '…');
                                            ?>
                                                <div class="gb-loop-item gb-loop-item-78be825f post-<?php echo esc_attr($pid); ?>">
                                                    <a href="<?php echo esc_url($link); ?>">
                                                        <?php if ($thumb): ?>
                                                            <img decoding="async" width="1200" height="630" class="gb-media-46f33b3d" src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
                                                        <?php else: ?>
                                                            <span class="gb-media-46f33b3d csop-blog-thumb-fallback" aria-hidden="true"></span>
                                                        <?php endif; ?>
                                                    </a>
                                                    <div class="gb-element-8ba48021">
                                                        <p class="gb-text gb-text-52b92314">发布日期: <?php echo esc_html(get_the_date('j F, Y')); ?></p>
                                                        <h3 class="gb-text gb-text-b6d6dcf9 length-limit"><a href="<?php echo esc_url($link); ?>"><?php echo esc_html(get_the_title()); ?></a></h3>
                                                        <p class="gb-text gb-text-a9fddb18 expert"><?php echo esc_html($excerpt); ?></p>
                                                        <a class="gb-text gb-text-945867ce" href="<?php echo esc_url($link); ?>">阅读更多</a>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                            </div>
                                            <?php
                                            $total = (int) $q->max_num_pages;
                                            if ($total > 1):
                                                $blog_base = home_url('/blog/');
                                                if (function_exists('csop_ml_current_lang') && function_exists('csop_ml_lang_url')) {
                                                    $blog_base = csop_ml_lang_url($blog_base, csop_ml_current_lang());
                                                }
                                                $links = paginate_links(array(
                                                    'base' => add_query_arg(array(
                                                        'blog_page' => '%#%',
                                                    ), $blog_base),
                                                    'format' => '',
                                                    'current' => $paged,
                                                    'total' => $total,
                                                    'prev_text' => '«',
                                                    'next_text' => '»',
                                                    'type' => 'plain',
                                                ));
                                                if ($links):
                                            ?>
                                                <div class="gb-element-b384802b">
                                                    <div class="gb-query-page-numbers-0b936250"><?php echo $links; ?></div>
                                                </div>
                                            <?php endif; endif; ?>
                                        <?php else: ?>
                                            <div class="csop-blog-empty">
                                                <h2 class="gb-text">暂无文章</h2>
                                                <p class="gb-text">请到后台「写新文章」发布第一篇面试真题 / 博客，发布后会自动显示在这里。</p>
                                            </div>
                                        <?php endif; wp_reset_postdata(); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </main>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function csop_pages_about_html() {
    return <<<'CSOP_ABOUT_HTML'
<div class="site grid-container container hfeed" id="page">
				<div class="site-content" id="content">
			
	<div class="content-area" id="primary">
		<main class="site-main" id="main">
			
<article id="post-20" class="post-20 page type-page status-publish" itemtype="https://schema.org/CreativeWork" itemscope>
	<div class="inside-article">
		
			<header class="entry-header">
				<h1 class="entry-title" itemprop="headline">ProgramHelp代写的故事(10余年资深代写)</h1>			</header>

			
		<div class="entry-content" itemprop="text">
			
<style>
  :root {
    --notice-bg: #fff8e1;
    --notice-border: #f59e0b;
    --notice-link: #2563eb;
    --notice-link-hover: #1e40af;
  }

  .notice-box {
    background: linear-gradient(90deg, var(--notice-bg) 0%, #fff3c4 100%);
    border-left: 6px solid var(--notice-border);
    padding: 18px 24px;
    border-radius: 12px;
    font-size: 1.1rem;
    line-height: 1.8;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .notice-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.15);
  }

  .notice-box a {
    color: var(--notice-link);
    font-weight: bold;
    text-decoration: underline;
    text-decoration-color: rgba(37, 99, 235, 0.4);
    transition: color 0.2s ease, text-decoration-color 0.2s ease;
  }
  .notice-box a:hover {
    color: var(--notice-link-hover);
    text-decoration-color: var(--notice-link-hover);
  }

  @media (max-width: 600px) {
    .notice-box {
      font-size: 1rem;
      padding: 14px 16px;
      line-height: 1.6;
    }
  }
</style>

<aside class="notice-box" role="note" aria-label="招聘信息">
  招人招人，团队发展需要，欢迎加入，每个月几K美金问题不大，详细
  <a href="https://csofferprep.com/join_us/" target="_blank" rel="noopener noreferrer" title="请查看">请查看</a>。
</aside>



<h2 class="wp-block-heading">全球服务</h2>



<p class="wp-block-paragraph">&nbsp;我从2012年，大学本科开始就和朋友做编程代写服务，起初我们只有三人，赚的都是辛苦钱。后面凭借我门过硬的质量保证，良好的售后服务，我们的代写服务日益获得广大学生同学的认可。期间我们边从事代写服务边完成学业，一直到我研究生到国外交换时，我深知很多学生找各种高价完成自己的作业，或者高价找中介，所以我正式决定组建我们的代写团队，并将团队扩充到5人。</p>



<h2 class="wp-block-heading">覆盖用户1000+</h2>



<p class="wp-block-paragraph">截止2025年12月，<a href="/contact/" target="_blank" rel="noopener" title="ProgramHelp">ProgramHelp</a>已经完成订单数(国内+海外)<strong>8000+</strong>单，覆盖用户数目达到<strong>1000+</strong>人，这些数据的背后是艰辛的付出和广大客户的信赖与支持。 </p>



<p class="wp-block-paragraph">团队内部是我熟知且深交多年的伙伴好友，每人负责各自的订单，做事情都一定亲力亲为，也保证没有中介，中间商赚取差价，凭借我自己完成一一项程序代做，作业代写，面试代面相关事情，给与用户最直接的沟通和最满意的回馈！代码代写，答疑，咨询，作业，代写，全站式服务，面试咨询辅导 ，面试在线帮助，面试代面等都是亲力亲为 。</p>



<p class="wp-block-paragraph">客户的真实好评是我们坚持的唯一动力，Programhelp团队通过大量的面试、OA提升团队实战水平，在此期间收获大量客户满意！</p>



<pre id="hljs-item-0" class="wp-block-preformatted"><code>因为淋过雨，所以想给别人撑伞，请相信我，一定能帮到你！</code></pre>



<h2 class="wp-block-heading">团队简介</h2>



<div class="wp-block-media-text has-media-on-the-right is-stacked-on-mobile" style="grid-template-columns:auto 26%"><div class="wp-block-media-text__content">
<p class="wp-block-paragraph"><strong>学长</strong><br>目前就职于Google，10余年开发经验，目前担任Senior Solution Architect职位，北大计算机本硕，擅长各种算法、Java、C++等编程语言。在学校期间多次参加ACM、天池大数据等多项比赛，拥有多项顶级paper、专利等，辅导帮助的<strong>1000+</strong>学生入职Google、Meta、阿里、Amazon等多个大厂。</p>
</div><figure class="wp-block-media-text__media"><img title="cropped-logo.png - csvosupport" fetchpriority="high" decoding="async" width="512" height="512" src="https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-011-cropped-logo.png" alt="" class="wp-image-24 size-full" srcset="https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-011-cropped-logo.png 512w, https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-009-cropped-logo-300x300-1.png 300w, https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-005-cropped-logo-150x150-1.png 150w, https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-004-cropped-logo-12x12-1.png 12w, https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-008-cropped-logo-270x270-1.png 270w, https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-007-cropped-logo-192x192-1.png 192w, https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-006-cropped-logo-180x180-1.png 180w, https://csvosupport.com/wp-content/uploads/2026/06/extra-ext-010-cropped-logo-32x32-1.png 32w" sizes="(max-width: 512px) 100vw, 512px" /></figure></div>



<div class="wp-block-media-text is-stacked-on-mobile" style="grid-template-columns:26% auto"><figure class="wp-block-media-text__media"><img title="WechatIMG9866 - csvosupport" decoding="async" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-007-wechatimg9866.png" alt="" class="wp-image-1266 size-full"/></figure><div class="wp-block-media-text__content">
<p class="wp-block-paragraph"><strong>Roger</strong><br>目前于<strong>University of Oxford</strong>读硕士.本科某计算机强势985，在大数据领域拥有丰富的实战经验，熟悉擅长HDFS、MapReduce、Yarn、Zookeeper、Hive、Flume、Kafka、HBase、Spark、Flink等，熟悉擅长MATLAB Simulink数学模型设计,具有多年信号时域、频域、调制域分析，掌握信道、调制、编码等常用功能的M语言实现经验。</p>
</div></div>



<div class="wp-block-media-text is-stacked-on-mobile" style="grid-template-columns:27% auto"><figure class="wp-block-media-text__media"><img title="WechatIMG7956 - csvosupport" decoding="async" width="404" height="296" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-006-wechatimg7956.png" alt="" class="wp-image-90 size-full" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-006-wechatimg7956.png 404w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-005-wechatimg7956-300x220-1.png 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-004-wechatimg7956-16x12-1.png 16w" sizes="(max-width: 404px) 100vw, 404px" /></figure><div class="wp-block-media-text__content">
<p class="wp-block-paragraph"><strong>James</strong><br><strong>Princeton University</strong>博士，人在海外，曾在谷歌、苹果等多家大厂工作。深度学习NLP方向拥有多篇SCI，机器学习方向拥有Github千星⭐️项目，Leetcode全国排名百名内，编程能力一流，专业辅导多年，精通TensorFlow、Keras，pytorch,QA问题，NER问题，文本分类，情感分析；对贝叶斯、随机森林、SVM、神经网络、聚类、PCA等有深入应用和研究。再计算机视觉上，图像分类，图像目标检测，图像分割，生成对抗网络等具备丰富的经验。</p>
</div></div>



<div class="wp-block-media-text has-media-on-the-right is-stacked-on-mobile" style="grid-template-columns:auto 28%"><div class="wp-block-media-text__content">
<p class="wp-block-paragraph"><strong>Isaac</strong></p>



<p class="wp-block-paragraph">北大硕博连读，学长多年好基友，CPA、CFA证书持有者，在商业分析、管理会计、金融工程有着丰富的辅导经验，和学长一起努力奋斗8年，服务学员数量<strong>1000+</strong>。</p>
</div><figure class="wp-block-media-text__media"><img title="WechatIMG7954 - csvosupport" decoding="async" width="404" height="426" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-003-wechatimg7954.png" alt="" class="wp-image-91 size-full" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-003-wechatimg7954.png 404w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-002-wechatimg7954-285x300-1.png 285w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-001-wechatimg7954-11x12-1.png 11w" sizes="(max-width: 404px) 100vw, 404px" /></figure></div>
		</div>

			</div>
</article>
		</main>
	</div>

	<div class="widget-area sidebar is-right-sidebar" id="right-sidebar">
	<div class="inside-right-sidebar">
		
<div class="gb-element-ca749091"><form role="search" method="get" action="/" class="wp-block-search__button-outside wp-block-search__icon-button wp-block-search"    ><label class="wp-block-search__label screen-reader-text" for="wp-block-search__input-1" >Search</label><div class="wp-block-search__inside-wrapper" ><input class="wp-block-search__input" id="wp-block-search__input-1" placeholder="Search...." value="" type="search" name="s" required /><button aria-label="Search" class="wp-block-search__button has-background has-accent-2-background-color has-icon wp-element-button" type="submit" ><svg class="search-icon" viewBox="0 0 24 24" width="24" height="24">
					<path d="M13 5c-3.3 0-6 2.7-6 6 0 1.4.5 2.7 1.3 3.7l-3.8 3.8 1.1 1.1 3.8-3.8c1 .8 2.3 1.3 3.7 1.3 3.3 0 6-2.7 6-6S16.3 5 13 5zm0 10.5c-2.5 0-4.5-2-4.5-4.5s2-4.5 4.5-4.5 4.5 2 4.5 4.5-2 4.5-4.5 4.5z"></path>
				</svg></button></div></form></div>



<div class="gb-element-6f418cf9">
<h3 class="gb-text gb-text-33432665">联系我们</h3>



<p class="has-accent-color has-text-color has-link-color wp-elements-37adab168be67c00a946dd6bace7bea7 wp-block-paragraph"><strong>Telegram:</strong>&nbsp;<a href="https://t.me/OAVOProxy" target="_blank" rel="noreferrer noopener">@OAVOProxy</a></p>



<p class="has-accent-color has-text-color has-link-color wp-elements-0cfeab2d5c0fe298464a142139276e49 wp-block-paragraph"><strong>Phone:</strong>&nbsp;<a href="tel:+8617863968105">+86 17863968105</a></p>



<p class="has-accent-color has-text-color has-link-color wp-elements-df2044f41f1eed4a6d8f57ef5420eac9 wp-block-paragraph"><strong>Email:</strong> catcstech@gmail.com</p>



<p class="has-accent-color has-text-color has-link-color wp-elements-5c349ff6265ec25715b3ab102d2b045b wp-block-paragraph"><strong>Wechat:</strong>&nbsp;Coding0201</p>



<figure class="wp-block-image"><img decoding="async" src="https://csvosupport.com/wp-content/uploads/2026/06/oavo-wechat-qr-30.jpg" alt="wechat"/></figure>



<p class="has-accent-color has-text-color has-link-color wp-elements-652e195e6f66f821e63dc58704043197 wp-block-paragraph">-------微信二维码↑-----</p>



<p class="has-contrast-color has-text-color has-link-color wp-elements-dd2cd1556c285aa49fb8be37a807e869 wp-block-paragraph">为了保证我尽快联系和评估您的面试，作业, 请注明您的面试，作业具体要求</p>



<p class="has-accent-2-color has-text-color has-link-color wp-elements-a3e318d009a320132ae810381ebcd215 wp-block-paragraph"><strong>100% Plagiarism Free 代码保证唯一</strong></p>



<p class="has-accent-2-color has-text-color has-link-color wp-elements-5d9e5bae960f44b3274a5ad6c5cb64de wp-block-paragraph"><strong>100% Confidentiality 完全保密</strong></p>



<p class="has-accent-2-color has-text-color has-link-color wp-elements-d2d615c413565f67796839b6d0625afb wp-block-paragraph"><strong>100% Quality Assurance 保证质量</strong></p>
</div>



<p class="wp-block-paragraph"></p>
	</div>
</div>

	</div>
</div>
CSOP_ABOUT_HTML;;
}

function csop_pages_price_html() {
    return <<<'CSOP_PRICE_HTML'
<div class="site grid-container container hfeed" id="page">
				<div class="site-content" id="content">
			
	<div class="content-area" id="primary">
		<main class="site-main" id="main">
			
<article id="post-22" class="post-22 page type-page status-publish" itemtype="https://schema.org/CreativeWork" itemscope>
	<div class="inside-article">
		
			<header class="entry-header">
				<h1 class="entry-title" itemprop="headline">服务&#038;价格(Service &#038; Price)</h1>			</header>

			
		<div class="entry-content" itemprop="text">
			
<div style="background-color: transparent; border: 1px solid #ddd; border-radius: 8px; 
    padding: 20px; margin: 10px auto; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
    width: 100%; max-width: max-width: 100%;"></p>
<p>    <!-- OA辅助服务 --></p>
<h2 style="font-size: 22px; margin-bottom: 10px; font-weight: bold;">OA辅助服务</h2>
<p style="font-size: 16px; line-height: 1.6;">专业提供 <strong>在线评测（OA）代写服务</strong>，确保所有测试用例 100% 通过，不通过所有测试用例不收费。</p>
<p style="font-size: 16px; line-height: 1.6;">我们通过远程控制软件 <strong>ToDesk</strong> 进行，确保无痕且不会被检测到。</p>
<p style="font-size: 16px; line-height: 1.6;">适用于 <strong>HackerRank</strong> 和 <strong>Codesignal</strong> 等平台，通过远程控制实现无痕操作，确保安全。</p>
<p style="font-size: 18px; font-weight: bold;">价格：<span style="color: #d9534f;">$300</span></p>
</div>
<p><!-- 面试辅助服务 --></p>
<div style="background-color: transparent; border: 1px solid #ddd; border-radius: 8px; 
    padding: 20px; margin: 10px auto; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
    width: 100%; max-width: max-width: 100%;"></p>
<h2 style="font-size: 22px; margin-bottom: 10px; font-weight: bold;">面试辅助（VO辅助）</h2>
<p style="font-size: 16px; line-height: 1.6;">北美CS专家人工为您提供<strong>实时提示和思路</strong>，帮助您效果远超AI，在面试中脱颖而出！<a href="https://csofferprep.com/shop/">详细介绍</a></p>
<h3 style="font-size: 18px; margin-top: 15px;">面试辅助流程</h3>
<ul style="font-size: 16px; line-height: 1.6; padding-left: 20px;">
<li>确认面试时间，设备调试成功后支付定金。</li>
<li>包含 <strong>BQ问题、Code、Follow up question、项目简历DIVE、技术八股、System Design</strong> 等内容。</li>
<li>面试进行时，通过无痕文档(自主研发，保证无痕辅助)实时提示。</li>
</ul>
<p style="font-size: 18px; font-weight: bold;">价格：<span style="color: #d9534f;">$300起</span></p>
</div>
<p><!-- 代面试服务 --></p>
<div style="background-color: transparent; border: 1px solid #ddd; border-radius: 8px; 
    padding: 20px; margin: 10px auto; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
    width: 100%; max-width: max-width: 100%;"></p>
<h2 style="font-size: 22px; margin-bottom: 10px; font-weight: bold;">代面试服务</h2>
<p style="font-size: 16px; line-height: 1.6;">通过<strong>转接摄像头与变声技术</strong>，我们的专业团队帮助您完成面试，直达 Offer！</p>
<h3 style="font-size: 18px; margin-top: 15px;">代面操作方式</h3>
<h4 style="font-size: 16px; margin-top: 10px; font-weight: bold;">第一种方式：对口型面试</h4>
<p style="font-size: 16px; line-height: 1.6;">我们采用对口型方式，提前模拟测试，配合默契。你的脸合成我们的声音，提前调试并说明注意事项。</p>
<h4 style="font-size: 16px; margin-top: 10px; font-weight: bold;">第二种方式：全替出镜代面试</h4>
<p style="font-size: 16px; line-height: 1.6;">我们全程代替你面试，你只需提供简历及个人信息。适用于面试官与岗位不在同组的情况，例如 Amazon SDE 面试。</p>
<p style="font-size: 18px; font-weight: bold;">价格：<span style="color: #d9534f;">$500起</span></p>
</div>
<p><!-- 全套包过服务 --></p>
<div style="background-color: transparent; border: 1px solid #ddd; border-radius: 8px; 
    padding: 20px; margin: 10px auto; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
    width: 100%; max-width: max-width: 100%;"></p>
<h2 style="font-size: 22px; margin-bottom: 10px; font-weight: bold;">全套包过套餐</h2>
<p style="font-size: 16px; line-height: 1.6;">从 <strong>OA</strong> 到 <strong>面试</strong> 再到 <strong>薪资谈判</strong>，一站式服务助您快速拿下满意 Offer！</p>
<p style="font-size: 16px; line-height: 1.6;"><strong>服务周期：</strong> Offer 为止，不拿 Offer 可持续服务。</p>
<p style="font-size: 18px; font-weight: bold;">价格：<span style="color: #d9534f;">详谈</span></p>
</div>
		</div>

			</div>
</article>
		</main>
	</div>

	<div class="widget-area sidebar is-right-sidebar" id="right-sidebar">
	<div class="inside-right-sidebar">
		
<div class="gb-element-ca749091"><form role="search" method="get" action="/" class="wp-block-search__button-outside wp-block-search__icon-button wp-block-search"    ><label class="wp-block-search__label screen-reader-text" for="wp-block-search__input-1" >Search</label><div class="wp-block-search__inside-wrapper" ><input class="wp-block-search__input" id="wp-block-search__input-1" placeholder="Search...." value="" type="search" name="s" required /><button aria-label="Search" class="wp-block-search__button has-background has-accent-2-background-color has-icon wp-element-button" type="submit" ><svg class="search-icon" viewBox="0 0 24 24" width="24" height="24">
					<path d="M13 5c-3.3 0-6 2.7-6 6 0 1.4.5 2.7 1.3 3.7l-3.8 3.8 1.1 1.1 3.8-3.8c1 .8 2.3 1.3 3.7 1.3 3.3 0 6-2.7 6-6S16.3 5 13 5zm0 10.5c-2.5 0-4.5-2-4.5-4.5s2-4.5 4.5-4.5 4.5 2 4.5 4.5-2 4.5-4.5 4.5z"></path>
				</svg></button></div></form></div>



<div class="gb-element-6f418cf9">
<h3 class="gb-text gb-text-33432665">联系我们</h3>



<p class="has-accent-color has-text-color has-link-color wp-elements-37adab168be67c00a946dd6bace7bea7 wp-block-paragraph"><strong>Telegram:</strong>&nbsp;<a href="https://t.me/OAVOProxy" target="_blank" rel="noreferrer noopener">@OAVOProxy</a></p>



<p class="has-accent-color has-text-color has-link-color wp-elements-0cfeab2d5c0fe298464a142139276e49 wp-block-paragraph"><strong>Phone:</strong>&nbsp;<a href="tel:+8617863968105">+86 17863968105</a></p>



<p class="has-accent-color has-text-color has-link-color wp-elements-df2044f41f1eed4a6d8f57ef5420eac9 wp-block-paragraph"><strong>Email:</strong> catcstech@gmail.com</p>



<p class="has-accent-color has-text-color has-link-color wp-elements-5c349ff6265ec25715b3ab102d2b045b wp-block-paragraph"><strong>Wechat:</strong>&nbsp;Coding0201</p>



<figure class="wp-block-image"><img decoding="async" src="https://csvosupport.com/wp-content/uploads/2026/06/oavo-wechat-qr-30.jpg" alt="wechat"/></figure>



<p class="has-accent-color has-text-color has-link-color wp-elements-652e195e6f66f821e63dc58704043197 wp-block-paragraph">-------微信二维码↑-----</p>



<p class="has-contrast-color has-text-color has-link-color wp-elements-dd2cd1556c285aa49fb8be37a807e869 wp-block-paragraph">为了保证我尽快联系和评估您的面试，作业, 请注明您的面试，作业具体要求</p>



<p class="has-accent-2-color has-text-color has-link-color wp-elements-a3e318d009a320132ae810381ebcd215 wp-block-paragraph"><strong>100% Plagiarism Free 代码保证唯一</strong></p>



<p class="has-accent-2-color has-text-color has-link-color wp-elements-5d9e5bae960f44b3274a5ad6c5cb64de wp-block-paragraph"><strong>100% Confidentiality 完全保密</strong></p>



<p class="has-accent-2-color has-text-color has-link-color wp-elements-d2d615c413565f67796839b6d0625afb wp-block-paragraph"><strong>100% Quality Assurance 保证质量</strong></p>
</div>



<p class="wp-block-paragraph"></p>
	</div>
</div>

	</div>
</div>
CSOP_PRICE_HTML;;
}

function csop_pages_contact_html() {
    return <<<'CSOP_CONTACT_HTML'
<div class="site grid-container container hfeed" id="page">
				<div class="site-content" id="content">
			
	<div class="content-area" id="primary">
		<main class="site-main" id="main">
			
<article id="post-19" class="post-19 page type-page status-publish" itemtype="https://schema.org/CreativeWork" itemscope>
	<div class="inside-article">
		
			<header class="entry-header">
				<h1 class="entry-title" itemprop="headline">联系学长 Contact Us</h1>			</header>

			
		<div class="entry-content" itemprop="text">
			
<p class="wp-block-paragraph">有需要随时联系我，面试代面，考试，作业，代写OA</p>



<ul class="wp-block-list">
<li><strong>Website :</strong><a href="/">https://csvosupport.com/</a></li>



<li><strong>Telegram:</strong> <a href="https://t.me/OAVOProxy" target="_blank" rel="noreferrer noopener">@OAVOProxy</a></li>



<li><strong>Email</strong>: catcstech@gmail.com</li>



<li><strong>Wechat:</strong> Coding0201</li>
</ul>



<p class="wp-block-paragraph">&nbsp;为了保证我们尽快联系和评估您的需求，添加时请注明您的面试、笔试具体要求</p>



<p class="wp-block-paragraph"><strong>&nbsp;注意:&nbsp;全年无休，24小时响应</strong></p>
		</div>

			</div>
</article>
		</main>
	</div>

	<div class="widget-area sidebar is-right-sidebar" id="right-sidebar">
	<div class="inside-right-sidebar">
		
<div class="gb-element-ca749091"><form role="search" method="get" action="/" class="wp-block-search__button-outside wp-block-search__icon-button wp-block-search"    ><label class="wp-block-search__label screen-reader-text" for="wp-block-search__input-1" >Search</label><div class="wp-block-search__inside-wrapper" ><input class="wp-block-search__input" id="wp-block-search__input-1" placeholder="Search...." value="" type="search" name="s" required /><button aria-label="Search" class="wp-block-search__button has-background has-accent-2-background-color has-icon wp-element-button" type="submit" ><svg class="search-icon" viewBox="0 0 24 24" width="24" height="24">
					<path d="M13 5c-3.3 0-6 2.7-6 6 0 1.4.5 2.7 1.3 3.7l-3.8 3.8 1.1 1.1 3.8-3.8c1 .8 2.3 1.3 3.7 1.3 3.3 0 6-2.7 6-6S16.3 5 13 5zm0 10.5c-2.5 0-4.5-2-4.5-4.5s2-4.5 4.5-4.5 4.5 2 4.5 4.5-2 4.5-4.5 4.5z"></path>
				</svg></button></div></form></div>



<div class="gb-element-6f418cf9">
<h3 class="gb-text gb-text-33432665">联系我们</h3>



<p class="has-accent-color has-text-color has-link-color wp-elements-37adab168be67c00a946dd6bace7bea7 wp-block-paragraph"><strong>Telegram:</strong>&nbsp;<a href="https://t.me/OAVOProxy" target="_blank" rel="noreferrer noopener">@OAVOProxy</a></p>



<p class="has-accent-color has-text-color has-link-color wp-elements-0cfeab2d5c0fe298464a142139276e49 wp-block-paragraph"><strong>Phone:</strong>&nbsp;<a href="tel:+8617863968105">+86 17863968105</a></p>



<p class="has-accent-color has-text-color has-link-color wp-elements-df2044f41f1eed4a6d8f57ef5420eac9 wp-block-paragraph"><strong>Email:</strong> catcstech@gmail.com</p>



<p class="has-accent-color has-text-color has-link-color wp-elements-5c349ff6265ec25715b3ab102d2b045b wp-block-paragraph"><strong>Wechat:</strong>&nbsp;Coding0201</p>



<figure class="wp-block-image"><img decoding="async" src="https://csvosupport.com/wp-content/uploads/2026/06/oavo-wechat-qr-30.jpg" alt="wechat"/></figure>



<p class="has-accent-color has-text-color has-link-color wp-elements-652e195e6f66f821e63dc58704043197 wp-block-paragraph">-------微信二维码↑-----</p>



<p class="has-contrast-color has-text-color has-link-color wp-elements-dd2cd1556c285aa49fb8be37a807e869 wp-block-paragraph">为了保证我尽快联系和评估您的面试，作业, 请注明您的面试，作业具体要求</p>



<p class="has-accent-2-color has-text-color has-link-color wp-elements-a3e318d009a320132ae810381ebcd215 wp-block-paragraph"><strong>100% Plagiarism Free 代码保证唯一</strong></p>



<p class="has-accent-2-color has-text-color has-link-color wp-elements-5d9e5bae960f44b3274a5ad6c5cb64de wp-block-paragraph"><strong>100% Confidentiality 完全保密</strong></p>



<p class="has-accent-2-color has-text-color has-link-color wp-elements-d2d615c413565f67796839b6d0625afb wp-block-paragraph"><strong>100% Quality Assurance 保证质量</strong></p>
</div>



<p class="wp-block-paragraph"></p>
	</div>
</div>

	</div>
</div>
CSOP_CONTACT_HTML;;
}

function csop_pages_en_html() {
    return <<<'CSOP_EN_HTML'
<div class="site grid-container container hfeed" id="page">
				<div class="site-content" id="content">
			
	<div class="content-area" id="primary">
		<main class="site-main" id="main">
			
<article id="post-2756" class="post-2756 page type-page status-publish" itemtype="https://schema.org/CreativeWork" itemscope>
	<div class="inside-article">
		
		<div class="entry-content" itemprop="text">
			
<div class="gb-element-0639ac5f" style="--inline-bg-image: url(https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-012-4685.webp)">
<div class="gb-element-9eda365a">
<h3 class="gb-text gb-text-193bb6ca">csvosupport Studio｜OA Proxy Writing｜VO Proxy Interview｜Interview Coaching</h3>



<h2 class="gb-text gb-text-e24a13ba">Reliable interview assistance service from Silicon Valley<br>30-minute free voice consultation<br>Premium overseas job-seeking assistance for you</h2>



<p class="gb-text gb-text-169e8bed">Founded in 2017, csvosupport's team includes engineers and researchers from top tech companies, as well as mentors with ACM algorithm competition backgrounds, dedicated to providing the highest quality interview coaching, OA proxy writing, VO assistance, and proxy interview services.<br><br>We focus on serving the entire job-seeking process in the tech industry. Since our founding, we have adhered to high-quality coaching and transparent service as our core principles, openly disclosing every mentor's academic and industry background, striving to become the leader in VO assistance and proxy interview services.</p>



<div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex">
<div class="wp-block-button has-custom-width wp-block-button__width-25 is-style-fill"><a class="wp-block-button__link has-base-3-color has-accent-2-background-color has-text-color has-background has-link-color wp-element-button" href="/contact/" style="border-top-left-radius:30px;border-top-right-radius:30px;border-bottom-left-radius:30px;border-bottom-right-radius:30px">Contact us</a></div>
</div>
</div>
</div>



<div>
<div class="gb-element-77cfd298">
<h2 class="gb-text gb-text-f2f3f905">North America's strongest interview assistance team</h2>



<div class="gb-element-ba61be1d"></div>



<p class="gb-text">Leveraging years of hands-on experience in North American and overseas job-seeking, we have built a highly acclaimed integrated solution of 「OA Proxy Writing」, 「Mock Interviews」, 「VO Proxy Interview」, and 「Interview Assistance」. From online assessments to video interviews, from technical details to communication strategies, we know every hurdle in big tech recruiting and can tailor the optimal solution for you.<br><br><br>Over the past few years, we have helped hundreds of clients secure offers from top companies like Amazon, Bloomberg, Pinterest, Meta, Stripe, Coinbase, DoorDash, Optiver, Citadel, and more — not only entering their target companies but also earning salaries far above the industry average. Among our clients are engineers who doubled their annual salary, as well as those who achieved career leaps directly to senior positions.<br><br><br>We are not an assembly-line service but your personalized job-seeking partner: each client is assigned a dedicated consultant and technical mentor for one-on-one guidance throughout the process, ensuring your every OA and VO performance precisely meets the recruiter's expectations.</p>



<div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex">
<div class="wp-block-button has-custom-width wp-block-button__width-25 is-style-fill"><a class="wp-block-button__link has-base-3-color has-accent-2-background-color has-text-color has-background has-link-color wp-element-button" href="/price/" style="border-top-left-radius:30px;border-top-right-radius:30px;border-bottom-left-radius:30px;border-bottom-right-radius:30px">Learn more about our services</a></div>
</div>
</div>
</div>



<div>
<div class="gb-element-8420f9a6">
<div class="gb-element-44aeb402">
<h2 class="gb-text gb-text-0e0453c1">Service Scope</h2>



<div class="gb-element-8526df89"></div>
</div>



<div class="gb-element-644ba81e">
<a class="gb-element-04df542a" href="#">
<div class="gb-element-c0b03233">
<span class="gb-shape gb-shape-67aa0073"><svg viewbox="0 0 640 512" xmlns="http://www.w3.org/2000/svg"><path d="M64 96c0-35.3 28.7-64 64-64l384 0c35.3 0 64 28.7 64 64l0 240-64 0 0-240-384 0 0 240-64 0 0-240zM0 403.2C0 392.6 8.6 384 19.2 384l601.6 0c10.6 0 19.2 8.6 19.2 19.2 0 42.4-34.4 76.8-76.8 76.8L76.8 480C34.4 480 0 445.6 0 403.2zM281 209l-31 31 31 31c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-48-48c-9.4-9.4-9.4-24.6 0-33.9l48-48c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9zM393 175l48 48c9.4 9.4 9.4 24.6 0 33.9l-48 48c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l31-31-31-31c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-a0a8ba26">OA Exam Assistance</h3>



<ul style="font-size:16px;font-style:normal;font-weight:400" class="wp-block-list has-contrast-color has-text-color has-link-color wp-elements-b58a482e960967c15000248d162a6ca0">
<li>OA Pass Guaranteed: Contest Pros Get You Full Score</li>



<li>Crush All Edge Cases, Guarantee Perfect-Score Submission</li>



<li>Master Top Companies' Latest Question Banks, Full Coverage!</li>



<li>Optimal Solutions + High Readability, Double Quality Guarantee</li>



<li>From 199 USD</li>
</ul>
</a>



<a class="gb-element-1aa32bcf" href="#">
<div class="gb-element-d54e97d1">
<span class="gb-shape gb-shape-26a52e65"><svg viewbox="0 0 640 512" xmlns="http://www.w3.org/2000/svg"><path d="M192 384c53 0 96 43 96 96 0 17.7-14.3 32-32 32L32 512c-17.7 0-32-14.3-32-32 0-53 43-96 96-96l96 0zM544 32c35.3 0 64 28.7 64 64l0 288c0 33.1-25.2 60.4-57.5 63.7l-6.5 .3-211.1 0c-5.1-24.2-16.3-46.1-32.1-64l51.2 0 0-32c0-17.7 14.3-32 32-32l96 0c17.7 0 32 14.3 32 32l0 32 32 0 0-288-352 0 0 57.3c-14.8-6-31-9.3-48-9.3-5.4 0-10.8 .3-16 1l0-49c0-35.3 28.7-64 64-64l352 0zM144 352a80 80 0 1 1 0-160 80 80 0 1 1 0 160z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-495f982c">VO assist</h3>



<ul style="font-size:16px" class="wp-block-list has-contrast-color has-text-color has-link-color wp-elements-a159239b3c38d4716db1a045e41022ba">
<li>Real-Time High-Quality Answers, Zero Detection, Full-Score Experience</li>



<li>Dual-Coach Support, 2x Assurance, Stable Output</li>



<li>Proven Track Record, Alpha Quality, Seeing Is Believing</li>



<li>Multi-Channel Delivery, Voice/Text Answers in Sync</li>



<li>From 299 USD</li>
</ul>
</a>



<a class="gb-element-3ee3a811" href="#">
<div class="gb-element-4347e508">
<span class="gb-shape gb-shape-a5802c71"><svg viewbox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM165.4 321.9c20.4 28 53.4 46.1 90.6 46.1s70.2-18.1 90.6-46.1c7.8-10.7 22.8-13.1 33.5-5.3s13.1 22.8 5.3 33.5C356.3 390 309.2 416 256 416s-100.3-26-129.4-65.9c-7.8-10.7-5.4-25.7 5.3-33.5s25.7-5.4 33.5 5.3zM144 208a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm192-32a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-0dc40ddd">VO Interview Assistance</h3>



<ul style="font-size:16px" class="wp-block-list has-contrast-color has-text-color has-link-color wp-elements-45d7b5e3bfde10f432a7febaa95e4415">
<li>Live VO mock interview, candidate stays off-camera</li>



<li>Free voice consultation, free mock demo</li>



<li>All mock tutors are senior+ at top tech firms</li>



<li>Securing OFFERs, not just talk</li>



<li>From 499 USD</li>
</ul>
</a>



<a class="gb-element-3c706259" href="#">
<div class="gb-element-6288e754">
<span class="gb-shape gb-shape-0475d889"><svg viewbox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 141.3l0 309.3 .5-.2C311.1 427.7 369.7 416 428.8 416l19.2 0 0-320-19.2 0c-42.2 0-84.1 8.4-123.1 24.6-16.8 7-33.4 13.9-49.7 20.7zM230.9 61.5L256 72 281.1 61.5C327.9 42 378.1 32 428.8 32L464 32c26.5 0 48 21.5 48 48l0 352c0 26.5-21.5 48-48 48l-35.2 0c-50.7 0-100.9 10-147.7 29.5l-12.8 5.3c-7.9 3.3-16.7 3.3-24.6 0l-12.8-5.3C184.1 490 133.9 480 83.2 480L48 480c-26.5 0-48-21.5-48-48L0 80C0 53.5 21.5 32 48 32l35.2 0c50.7 0 100.9 10 147.7 29.5z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-afe09214">Resume polishing</h3>



<ul style="font-size:16px" class="wp-block-list has-contrast-color has-text-color has-link-color wp-elements-c13d8f6b6f498c4224036a11e991f5fc">
<li>Real industry tech stacks, no toy projects</li>



<li>100% original, tailored to your experience</li>



<li>Deep technical deep-dives, seamless JD matching</li>



<li>Big-tech perspective review, HR + HM refinement</li>
</ul>
</a>



<a class="gb-element-4c34f39e" href="#">
<div class="gb-element-3276f866">
<span class="gb-shape gb-shape-91579803"><svg viewbox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M36.4 353.2c4.1-14.6 11.8-27.9 22.6-38.7l181.2-181.2 33.9-33.9c16.6 16.6 51.3 51.3 104 104l33.9 33.9-33.9 33.9-181.2 181.2c-10.7 10.7-24.1 18.5-38.7 22.6L30.4 510.6c-8.3 2.3-17.3 0-23.4-6.2S-1.4 489.3 .9 481L36.4 353.2zm55.6-3.7c-4.4 4.7-7.6 10.4-9.3 16.6l-24.1 86.9 86.9-24.1c6.4-1.8 12.2-5.1 17-9.7L91.9 349.5zm354-146.1c-16.6-16.6-51.3-51.3-104-104L308 65.5C334.5 39 349.4 24.1 352.9 20.6 366.4 7 384.8-.6 404-.6S441.6 7 455.1 20.6l35.7 35.7C504.4 69.9 512 88.3 512 107.4s-7.6 37.6-21.2 51.1c-3.5 3.5-18.4 18.4-44.9 44.9z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-967f6727">Interview Coaching</h3>



<ul style="font-size:16px" class="wp-block-list has-contrast-color has-text-color has-link-color wp-elements-f04fb69b3910c6fea31582a52d65c14a">
<li>1-on-1 coaching by big-tech interviewers, end-to-end</li>



<li>Real question practice on trending hot topics</li>



<li>Personalized coding roadmap with exclusive techniques</li>



<li>Interviewer mindset modeling to identify intent behind questions</li>
</ul>
</a>



<a class="gb-element-335115d4" href="#">
<div class="gb-element-e33fac9c">
<span class="gb-shape gb-shape-c6ccb341"><svg viewbox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M241 87.1l15 20.7 15-20.7C296 52.5 336.2 32 378.9 32 452.4 32 512 91.6 512 165.1l0 2.6c0 112.2-139.9 242.5-212.9 298.2-12.4 9.4-27.6 14.1-43.1 14.1s-30.8-4.6-43.1-14.1C139.9 410.2 0 279.9 0 167.7l0-2.6C0 91.6 59.6 32 133.1 32 175.8 32 216 52.5 241 87.1z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-0f3d2feb">Why choose us?</h3>



<ul style="font-size:16px" class="wp-block-list has-contrast-color has-text-color has-link-color wp-elements-83281534e5b98ffd525cac7a37cbc3b3">
<li>Unlimited free voice consultation</li>



<li>Recorded mock interviews for your reference — what you see is what you get</li>



<li>The only service supporting senior/staff-level interview proxy</li>



<li>We uphold sincerity and responsibility as our foundation</li>
</ul>
</a>
</div>
</div>
</div>



<div class="gb-element-0fe52152" style="--inline-bg-image: url(https://csvosupport.com/wp-content/uploads/2026/06/local-ext-04-main-mevia-site-microsoft-2026-sde-online-assessment-cleanup-1.webp)">
<div class="gb-element-93badf17">
<h2 class="gb-text gb-text-87ab5dab">Service Scope &#8211; Overseas interview preparation</h2>



<p class="gb-text">(Excluding interview orders from mainland China)</p>



<p class="gb-text gb-text-ee938c30"><br>OA solving&nbsp; &nbsp;OA completion&nbsp; &nbsp;VO proxy&nbsp; &nbsp;Interview proxy&nbsp; &nbsp;Interview coaching&nbsp; &nbsp;SDE proxy&nbsp; &nbsp;MLE proxy&nbsp; &nbsp;System design proxy<br>Resume polish&nbsp; CV editing&nbsp; Mock interviews&nbsp; Interview sharing&nbsp; VO coaching&nbsp; HackerRank solving&nbsp; CodeSignal solving<br><br>Amazon proxy&nbsp; &nbsp;Amazon assistance&nbsp; Meta proxy&nbsp; Pinterest proxy&nbsp; Bloomberg proxy&nbsp; &nbsp;Uber proxy<br>Citadel OA proxy&nbsp; Optiver interview proxy&nbsp; Stripe interview proxy&nbsp; SnowFlake interview proxy&nbsp; Atlassian interview coaching<br>North American big tech interview proxy&nbsp; Coderpad proxy tech interview coaching North America job hunting remote interview coaching&nbsp; Silicon Valley interview proxy<br>US interview coaching mock interviews simulated interviews BQ coaching algorithm coaching system design coaching SDE training MLE training&nbsp;</p>
</div>
</div>



<div>
<div class="gb-element-bf9c793f">
<div class="gb-element-03bb26a5">
<h2 class="gb-text gb-text-5d3337d5">Why Choose Us</h2>



<div class="gb-element-817d0990"></div>
</div>



<div class="gb-element-7fbf0c12">
<a class="gb-element-dcfdb50b" href="#">
<div class="gb-element-080f0e69">
<span class="gb-shape gb-shape-280325fc"><svg aria-hidden="true" height="1em" width="1em" viewbox="0 0 576 512" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-5fe57c83">「Recognized industry leader」</h3>



<ul class="wp-block-list has-accent-color has-text-color has-link-color wp-elements-677b633c3679d15aa40aad6bfd2af7a7">
<li>Voice chats available anytime, experience mentors' real expertise</li>



<li>Interview solutions built to production-grade standards</li>



<li>Countless success stories — watch free interview demos</li>
</ul>
</a>



<a class="gb-element-f952711d" href="#">
<div class="gb-element-9b25b2c5">
<span class="gb-shape gb-shape-41fb84cb"><svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 496 512" width="1em" height="1em" aria-hidden="true"><path d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm0 96c48.6 0 88 39.4 88 88s-39.4 88-88 88-88-39.4-88-88 39.4-88 88-88zm0 344c-58.7 0-111.3-26.6-146.5-68.2 18.8-35.4 55.6-59.8 98.5-59.8 2.4 0 4.8.4 7.1 1.1 13 4.2 26.6 6.9 40.9 6.9 14.3 0 28-2.7 40.9-6.9 2.3-.7 4.7-1.1 7.1-1.1 42.9 0 79.7 24.4 98.5 59.8C359.3 421.4 306.7 448 248 448z" fill="currentColor"></path></svg></span>
</div>



<h3 class="gb-text gb-text-aae11c36">「Rigorously selected mentor team」</h3>



<ul class="wp-block-list has-accent-color has-text-color has-link-color wp-elements-007f9b3b796afd4e478558cb6d7c3bd2">
<li>100% domain-specialized PhD mentor team</li>



<li>1000+ mentor pool covering all subfields</li>



<li>Mentors' academic &amp; career backgrounds fully transparent</li>
</ul>
</a>



<a class="gb-element-556bbf6d" href="#">
<div class="gb-element-f01effe1">
<span class="gb-shape gb-shape-3ee27093"><svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 512 512" width="1em" height="1em" aria-hidden="true"><path d="M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zM227.314 387.314l184-184c6.248-6.248 6.248-16.379 0-22.627l-22.627-22.627c-6.248-6.249-16.379-6.249-22.628 0L216 308.118l-70.059-70.059c-6.248-6.248-16.379-6.248-22.628 0l-22.627 22.627c-6.248 6.248-6.248 16.379 0 22.627l104 104c6.249 6.249 16.379 6.249 22.628.001z" fill="currentColor"></path></svg></span>
</div>



<h3 class="gb-text gb-text-82f291f1">「Recognized industry leader」</h3>



<ul class="wp-block-list has-accent-color has-text-color has-link-color wp-elements-2be89e43f16f27b6f0833b659c51c04f">
<li>Client privacy strictly protected, all data encrypted</li>



<li>Upholding integrity, delivering every task with dedication</li>



<li>Lifetime after-sales support, eliminating all worries</li>
</ul>
</a>



<a class="gb-element-bbe44f83" href="#">
<div class="gb-element-0945459f">
<span class="gb-shape gb-shape-5ef9faca"><svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 512 512" width="1em" height="1em" aria-hidden="true"><path d="M466.5 83.7l-192-80a48.15 48.15 0 0 0-36.9 0l-192 80C27.7 91.1 16 108.6 16 128c0 198.5 114.5 335.7 221.5 380.3 11.8 4.9 25.1 4.9 36.9 0C360.1 472.6 496 349.3 496 128c0-19.4-11.7-36.9-29.5-44.3zM256.1 446.3l-.1-381 175.9 73.3c-3.3 151.4-82.1 261.1-175.8 307.7z" fill="currentColor"></path></svg></span>
</div>



<h3 class="gb-text gb-text-dea3d70e">「Recognized industry leader」</h3>



<ul class="wp-block-list has-accent-color has-text-color has-link-color wp-elements-2be89e43f16f27b6f0833b659c51c04f">
<li>Client privacy strictly protected, all data encrypted</li>



<li>Upholding integrity, delivering every task with dedication</li>



<li>Lifetime after-sales support, eliminating all worries</li>
</ul>
</a>
</div>
</div>
</div>



<div>
<div class="gb-element-cc34185d">
<div class="gb-element-e49b6a68">
<h2 class="gb-text gb-text-d42e626f">Management Team</h2>



<div class="gb-element-9704f49b"></div>
</div>



<div class="gb-element-cc72d6c3">
<a class="gb-element-0d2725ff" href="#">
<p class="gb-text gb-text-75fb0c21">Currently at Google as Senior Solution Architect with 10+ years of experience, BS/MS in CS from Peking University. Proficient in algorithms, Java, C++. Competed in ACM, Tianchi Big Data, etc. Holds top papers and patents. Helped 1000+ students land jobs at Google, Meta, Alibaba, Amazon, and more.</p>



<div class="gb-element-0ccb957e">
<div>
<img fetchpriority="high" decoding="async" width="512" height="512" class="gb-media-176e053f" alt="Solid black square image (likely a placeholder or blackout)." title="6840541" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-016-6840541.webp" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-016-6840541.webp 512w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-015-6840541-300x300-1.webp 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-014-6840541-150x150-1.webp 150w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-013-6840541-12x12-1.webp 12w" sizes="(max-width: 512px) 100vw, 512px" />
</div>



<div>
<h4 class="gb-text">Senior</h4>
</div>
</div>
</a>



<a class="gb-element-5e4c89d8" href="#">
<p class="gb-text gb-text-aa90639b">Currently pursuing a Master's at University of Oxford. Bachelor's from a top 985 CS university. Extensive big data experience with HDFS, MapReduce, Yarn, Zookeeper, Hive, Flume, Kafka, HBase, Spark, Flink. Skilled in MATLAB Simulink modeling, time/frequency/modulation domain signal analysis, and M-language implementation of channel, modulation, and coding.</p>



<div class="gb-element-ab84b3b5">
<div>
<img decoding="async" class="gb-media-c8bb6e14" alt="" title="WechatIMG9866" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-007-wechatimg9866.png"/>
</div>



<div>
<h4 class="gb-text">Roger.</h4>
</div>
</div>
</a>



<a class="gb-element-83eea067" href="#">
<p class="gb-text gb-text-c48aea3b">PhD from Princeton University, based overseas. Worked at Google, Apple, and other major companies. Multiple SCI papers in deep learning NLP. GitHub thousand-star ⭐️ ML project. Top 100 on Leetcode nationally. Years of tutoring in TensorFlow, Keras, PyTorch, QA, NER, text classification, sentiment analysis. Deep expertise in Bayesian, Random Forest, SVM, neural networks, clustering, PCA. Extensive CV experience in image classification, object detection, segmentation, GANs.</p>



<div class="gb-element-656e9daf">
<div>
<img decoding="async" width="404" height="296" class="gb-media-25b77a9a" alt="" title="WechatIMG7956" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-006-wechatimg7956.png" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-006-wechatimg7956.png 404w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-005-wechatimg7956-300x220-1.png 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-004-wechatimg7956-16x12-1.png 16w" sizes="(max-width: 404px) 100vw, 404px" />
</div>



<div>
<h4 class="gb-text">James.</h4>
</div>
</div>
</a>



<a class="gb-element-1cf72096" href="#">
<p class="gb-text gb-text-5434fef7 translation-block">Pursuing a master’s and doctoral degree concurrently at Peking University, he is a close friend of the senior schoolmate for many years and a holder of CPA and CFA certifications. He has extensive tutoring experience in business analysis, management accounting, and financial engineering. Having worked hard together with the senior schoolmate for eight years, he has served more than 1,000 students.</p>



<div class="gb-element-a4f0c7fe">
<div>
<img decoding="async" width="404" height="426" class="gb-media-6e39e858" alt="" title="WechatIMG7954" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-003-wechatimg7954.png" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-003-wechatimg7954.png 404w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-002-wechatimg7954-285x300-1.png 285w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-001-wechatimg7954-11x12-1.png 11w" sizes="(max-width: 404px) 100vw, 404px" />
</div>



<div>
<h4 class="gb-text">Isaac</h4>
</div>
</div>
</a>
</div>
</div>
</div>



<div>
<div class="gb-element-38c69647">
<h2 class="gb-text gb-text-20281697">Client Reviews</h2>



<p class="gb-text">1000+ success stories — let us show you the real us!</p>



<div class="gb-tabs gb-tabs-513a33df" data-opened-tab="1">
<div class="gb-tabs__menu gb-tabs__menu-6aae225b" role="tablist">
<div tabindex="0" class="gb-tabs__menu-item gb-tabs__menu-item-4a18984e gb-block-is-current" role="tab" id="gb-tab-menu-item-4a18984e">
<span class="gb-text gb-text-e90ae470">User Reviews</span>
</div>



<div tabindex="0" class="gb-tabs__menu-item gb-tabs__menu-item-87819c3d" role="tab" id="gb-tab-menu-item-87819c3d">
<span class="gb-text gb-text-286dab72">Offer Cases</span>
</div>



<div tabindex="0" class="gb-tabs__menu-item gb-tabs__menu-item-13e7b89e" role="tab" id="gb-tab-menu-item-13e7b89e">
<span class="gb-text gb-text-a47aa7db">OA Cases</span>
</div>



<div tabindex="0" class="gb-tabs__menu-item gb-tabs__menu-item-85742302" role="tab" id="gb-tab-menu-item-85742302">
<span class="gb-text gb-text-74b2cf67">VO Cases</span>
</div>
</div>



<div class="gb-tabs__items">
<div class="gb-tabs__item gb-tabs__item-open" role="tabpanel" id="gb-tab-item-254099d5">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-1 is-layout-flex wp-block-gallery-is-layout-flex">
<figure class="wp-block-image size-large"><img title="review1 - csvosupport" loading="eager" decoding="async" width="456" height="1024" data-id="3156" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-071-review1-456x1024-1.jpg" alt="Chinese chat screenshot discussing follow-up and sending a transfer, with green and white message bubbles and an orange transfer card in the middle." class="wp-image-3156" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-071-review1-456x1024-1.jpg 456w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-070-review1-134x300-1.jpg 134w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-074-review1-768x1726-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-073-review1-684x1536-1.jpg 684w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-075-review1-912x2048-1.jpg 912w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-072-review1-5x12-1.jpg 5w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-076-review1.jpg 940w" sizes="auto, (max-width: 456px) 100vw, 456px" data-mwl-img-id="3156" /><figcaption class="wp-element-caption">review1</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review2 - csvosupport" loading="eager" decoding="async" width="636" height="1024" data-id="3157" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-078-review2-636x1024-1.jpg" alt="Screenshots of a chat in Chinese: green message says &#039;Did Google match you?&#039;, emojis, white message &#039;Already sent the offer last week&#039;, an attached Google document image, and another green message &#039;Nice, what&#039;s the total package?&#039; with emoji avatars." class="wp-image-3157" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-078-review2-636x1024-1.jpg 636w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-077-review2-186x300-1.jpg 186w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-079-review2-768x1236-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-080-review2-7x12-1.jpg 7w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-081-review2.jpg 886w" sizes="auto, (max-width: 636px) 100vw, 636px" data-mwl-img-id="3157" /><figcaption class="wp-element-caption">review2</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review3 - csvosupport" loading="eager" decoding="async" width="830" height="1024" data-id="3158" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-085-review3-830x1024-1.jpg" alt="Chinese chat screenshot: green and white message bubbles discussing being in team match and waiting, with emojis and a profile icon on the right." class="wp-image-3158" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-085-review3-830x1024-1.jpg 830w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-083-review3-243x300-1.jpg 243w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-084-review3-768x947-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-082-review3-10x12-1.jpg 10w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-086-review3.jpg 1080w" sizes="auto, (max-width: 830px) 100vw, 830px" data-mwl-img-id="3158" /><figcaption class="wp-element-caption">review3</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review4 - csvosupport" loading="eager" decoding="async" width="762" height="1024" data-id="3159" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-088-review4-762x1024-1.jpg" alt="WeChat-style chat screenshot in Chinese: at 9:13 AM a distorted image preview; at 9:47 AM a green message saying &#039;How is it?&#039; and a small sad emoji chat avatar on the side, followed by a white message thanking the leaders for active communication and great support, then another green message &#039;Got it&#039;" class="wp-image-3159" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-088-review4-762x1024-1.jpg 762w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-087-review4-223x300-1.jpg 223w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-089-review4-768x1032-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-090-review4-9x12-1.jpg 9w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-091-review4.jpg 886w" sizes="auto, (max-width: 762px) 100vw, 762px" data-mwl-img-id="3159" /><figcaption class="wp-element-caption">review4</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review5 - csvosupport" loading="eager" decoding="async" width="886" height="974" data-id="3160" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-095-review5.jpg" alt="OA proxy chat screenshot: discussing trying it in the browser and special settings for Tencent Meeting/Zoom, leaving the room and meeting after finishing, and saying they will get the offer, with an angel emoji at the bottom of the screen." class="wp-image-3160" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-095-review5.jpg 886w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-093-review5-273x300-1.jpg 273w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-094-review5-768x844-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-092-review5-11x12-1.jpg 11w" sizes="auto, (max-width: 886px) 100vw, 886px" data-mwl-img-id="3160" /><figcaption class="wp-element-caption">review5</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review6 - csvosupport" loading="eager" decoding="async" width="453" height="1024" data-id="3161" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-097-review6-453x1024-1.jpg" alt="Screenshot of a Chinese chat about preparing for a coding interview; green bubbles show plan to &#039;release SD&#039; and approval responses like &#039;ok&#039;." class="wp-image-3161" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-097-review6-453x1024-1.jpg 453w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-096-review6-133x300-1.jpg 133w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-100-review6-768x1737-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-099-review6-679x1536-1.jpg 679w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-101-review6-905x2048-1.jpg 905w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-098-review6-5x12-1.jpg 5w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-102-review6.jpg 930w" sizes="auto, (max-width: 453px) 100vw, 453px" data-mwl-img-id="3161" /><figcaption class="wp-element-caption">review6</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review7 - csvosupport" loading="eager" decoding="async" width="517" height="1024" data-id="3162" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-104-review7-517x1024-1.jpg" alt="Screenshots of a Chinese chat conversation in a messaging app; green bubbles on the right with Chick-fil-A avatars, a long white bubble on the left with a message about finishing a loop, and timestamps yesterday at 11:01 AM." class="wp-image-3162" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-104-review7-517x1024-1.jpg 517w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-103-review7-151x300-1.jpg 151w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-106-review7-768x1522-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-107-review7-775x1536-1.jpg 775w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-105-review7-6x12-1.jpg 6w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-108-review7.jpg 910w" sizes="auto, (max-width: 517px) 100vw, 517px" data-mwl-img-id="3162" /><figcaption class="wp-element-caption">review7</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review8 - csvosupport" loading="eager" decoding="async" width="450" height="1024" data-id="3163" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-110-review8-450x1024-1.jpg" alt="Dark messaging thread in Chinese: congratulatory exchange after a prize transfer, with green chat bubbles saying "Haha, congrats!" and "OK/Haha sure" and a PDF/file transfer preview showing a WeChat/desktop UI element nearby." class="wp-image-3163" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-110-review8-450x1024-1.jpg 450w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-109-review8-132x300-1.jpg 132w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-113-review8-768x1747-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-112-review8-675x1536-1.jpg 675w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-114-review8-901x2048-1.jpg 901w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-111-review8-5x12-1.jpg 5w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-115-review8.jpg 948w" sizes="auto, (max-width: 450px) 100vw, 450px" data-mwl-img-id="3163" /><figcaption class="wp-element-caption">review8</figcaption></figure>
</figure>
</div>



<div class="gb-tabs__item" role="tabpanel" id="gb-tab-item-830667c1">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-2 is-layout-flex wp-block-gallery-is-layout-flex">
<figure class="wp-block-image size-large"><img title="amazon-offer - csvosupport" loading="eager" decoding="async" width="798" height="1024" data-id="3164" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-033-amazon-offer-798x1024-1.jpg" alt="Amazon letter on company letterhead offering a Software Dev Engineer position in Jersey City, NJ, with start date June 30, 2025 and salary details (annualized pay and sign-on)." class="wp-image-3164" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-033-amazon-offer-798x1024-1.jpg 798w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-031-amazon-offer-234x300-1.jpg 234w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-032-amazon-offer-768x986-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-034-amazon-offer-9x12-1.jpg 9w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-035-amazon-offer.jpg 916w" sizes="auto, (max-width: 798px) 100vw, 798px" data-mwl-img-id="3164" /><figcaption class="wp-element-caption">amazon offer</figcaption></figure>



<figure class="wp-block-image size-large"><img title="amazon-sde2 - csvosupport" loading="eager" decoding="async" width="1024" height="619" data-id="3165" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-042-amazon-sde2-1024x619-1.jpg" alt="Screenshot of an email header with the Amazon logo, followed by body text congratulating the recipient on an AWS offer and mentioning a formal offer letter and benefits details." class="wp-image-3165" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-042-amazon-sde2-1024x619-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-045-amazon-sde2-300x181-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-046-amazon-sde2-768x464-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-043-amazon-sde2-1536x928-1.jpg 1536w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-044-amazon-sde2-18x12-1.jpg 18w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-047-amazon-sde2.jpg 1628w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3165" /><figcaption class="wp-element-caption">amazon sde2</figcaption></figure>



<figure class="wp-block-image size-large"><img title="google-offer - csvosupport" loading="eager" decoding="async" width="1024" height="953" data-id="3166" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-048-google-offer-1024x953-1.jpg" alt="Screenshot of an email from Google Offer Letters Team via DocuSign showing a blue banner with &#039;Review Document&#039; and a Google logo, about reviewing employment documents." class="wp-image-3166" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-048-google-offer-1024x953-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-050-google-offer-300x279-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-051-google-offer-768x715-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-049-google-offer-13x12-1.jpg 13w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-052-google-offer.jpg 1160w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3166" /><figcaption class="wp-element-caption">google offer</figcaption></figure>



<figure class="wp-block-image size-large"><img title="microsoft-offer - csvosupport" loading="eager" decoding="async" width="473" height="1024" data-id="3167" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-059-microsoft-offer-473x1024-1.jpg" alt="Mobile email screenshot announcing a Microsoft internship offer, with a celebratory banner reading &#039;Congratulations!&#039; and &#039;On your offer to be a Microsoft Intern&#039;" class="wp-image-3167" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-059-microsoft-offer-473x1024-1.jpg 473w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-058-microsoft-offer-139x300-1.jpg 139w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-062-microsoft-offer-768x1662-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-061-microsoft-offer-710x1536-1.jpg 710w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-063-microsoft-offer-946x2048-1.jpg 946w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-060-microsoft-offer-6x12-1.jpg 6w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-064-microsoft-offer.jpg 1080w" sizes="auto, (max-width: 473px) 100vw, 473px" data-mwl-img-id="3167" /><figcaption class="wp-element-caption">microsoft offer</figcaption></figure>
</figure>
</div>



<div class="gb-tabs__item" role="tabpanel" id="gb-tab-item-40de34e5">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-3 is-layout-flex wp-block-gallery-is-layout-flex">
<figure class="wp-block-image size-large"><img title="amazon-oa - csvosupport" loading="eager" decoding="async" width="1024" height="819" data-id="3168" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-017-amazon-oa-1024x819-1.jpg" alt="Screenshot of a UI titled &#039;Code Question 2&#039; showing a warehouse-inspection scenario with bulleted rules, and a right panel listing test results and test cases." class="wp-image-3168" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-017-amazon-oa-1024x819-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-019-amazon-oa-300x240-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-020-amazon-oa-768x614-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-018-amazon-oa-15x12-1.jpg 15w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-021-amazon-oa.jpg 1350w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3168" /><figcaption class="wp-element-caption">amazon oa</figcaption></figure>



<figure class="wp-block-image size-large"><img title="amazon-oa2 - csvosupport" loading="eager" decoding="async" width="1024" height="764" data-id="3169" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-022-amazon-oa2-1024x764-1.jpg" alt="Split-screen screenshot: left panel shows a written problem about inventory quality; right panel shows Python code in a dark editor, with colored blocks and line numbers. watermark reads &#039;interviewAid&#039;." class="wp-image-3169" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-022-amazon-oa2-1024x764-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-024-amazon-oa2-300x224-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-025-amazon-oa2-768x573-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-023-amazon-oa2-16x12-1.jpg 16w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-026-amazon-oa2.jpg 1248w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3169" /><figcaption class="wp-element-caption">amazon oa2</figcaption></figure>



<figure class="wp-block-image size-large"><img title="amazon-oa3 - csvosupport" loading="eager" decoding="async" width="768" height="1024" data-id="3170" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-028-amazon-oa3-768x1024-1.jpg" alt="Split screen: left side shows a dark IDE window with a Test Cases list and green &#039;6 passed&#039; status; right side shows a Chinese chat conversation about AI assistant usage with green and white speech bubbles." class="wp-image-3170" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-028-amazon-oa3-768x1024-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-027-amazon-oa3-225x300-1.jpg 225w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-029-amazon-oa3-9x12-1.jpg 9w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-030-amazon-oa3.jpg 1080w" sizes="auto, (max-width: 768px) 100vw, 768px" data-mwl-img-id="3170" /><figcaption class="wp-element-caption">amazon oa3</figcaption></figure>



<figure class="wp-block-image size-large"><img title="amazon-sde-oa - csvosupport" loading="eager" decoding="async" width="1024" height="659" data-id="3171" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-036-amazon-sde-oa-1024x659-1.jpg" alt="Java Spring controller code: updateComment method with @PutMapping(&#039;/{id}&#039;), handling request and errors in a try-catch block (Info visible: ResponseEntity, NOT_FOUND, UNAUTHORIZED)." class="wp-image-3171" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-036-amazon-sde-oa-1024x659-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-039-amazon-sde-oa-300x193-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-040-amazon-sde-oa-768x494-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-037-amazon-sde-oa-1536x989-1.jpg 1536w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-038-amazon-sde-oa-18x12-1.jpg 18w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-041-amazon-sde-oa.jpg 1678w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3171" /><figcaption class="wp-element-caption">amazon sde oa</figcaption></figure>



<figure class="wp-block-image size-large"><img title="intuit-oa - csvosupport" loading="eager" decoding="async" width="1024" height="930" data-id="3176" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-053-intuit-oa-1024x930-1.jpg" alt="Screenshot of a dark UI displaying an SQL: Stock Market Software Capitalization Report with bullet points about sectors, total capitalization, and notes; includes a schema table section labeled &#039;companies&#039; at the bottom." class="wp-image-3176" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-053-intuit-oa-1024x930-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-055-intuit-oa-300x273-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-056-intuit-oa-768x698-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-054-intuit-oa-13x12-1.jpg 13w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-057-intuit-oa.jpg 1080w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3176" /><figcaption class="wp-element-caption">intuit oa</figcaption></figure>



<figure class="wp-block-image size-large"><img title="stripe-oa1 - csvosupport" loading="eager" decoding="async" width="783" height="1024" data-id="3177" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-118-stripe-oa1-783x1024-1.jpg" alt="Screenshot of a dark command-guide discussing SHUTDOWN handling, target routing, and example CONNECT/SHUTDOWN commands with a right-side test results panel." class="wp-image-3177" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-118-stripe-oa1-783x1024-1.jpg 783w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-116-stripe-oa1-229x300-1.jpg 229w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-117-stripe-oa1-768x1005-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-119-stripe-oa1-9x12-1.jpg 9w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-120-stripe-oa1.jpg 1080w" sizes="auto, (max-width: 783px) 100vw, 783px" data-mwl-img-id="3177" /><figcaption class="wp-element-caption">stripe oa1</figcaption></figure>



<figure class="wp-block-image size-large"><img title="tiktok-oa - csvosupport" loading="eager" decoding="async" width="1024" height="760" data-id="3178" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-121-tiktok-oa-1024x760-1.jpg" alt="Split-screen: left side shows a UI with a &#039;Create post&#039; form and &#039;Publish post&#039; button, plus a &#039;Recent posts&#039; list; right side displays a code editor with a project file tree and open files, overlaid by a large watermark." class="wp-image-3178" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-121-tiktok-oa-1024x760-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-123-tiktok-oa-300x223-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-124-tiktok-oa-768x570-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-122-tiktok-oa-16x12-1.jpg 16w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-125-tiktok-oa.jpg 1455w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3178" /><figcaption class="wp-element-caption">tiktok oa</figcaption></figure>



<figure class="wp-block-image size-large"><img title="two-sigma-oa - csvosupport" loading="eager" decoding="async" width="1024" height="883" data-id="3179" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-130-two-sigma-oa-1024x883-1.jpg" alt="Screenshot of a dark coding notebook UI titled &#039;Daily Temperature By Town&#039; with long descriptive text on the left (Part One and Part Two tasks) and a code editor/results panel on the right, including a &#039;Run Code&#039; button and a &#039;Compiler Message&#039; section." class="wp-image-3179" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-130-two-sigma-oa-1024x883-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-132-two-sigma-oa-300x259-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-133-two-sigma-oa-768x662-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-131-two-sigma-oa-14x12-1.jpg 14w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-134-two-sigma-oa.jpg 1252w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3179" /><figcaption class="wp-element-caption">two sigma oa</figcaption></figure>
</figure>
</div>



<div class="gb-tabs__item" role="tabpanel" id="gb-tab-item-366108b8">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-4 is-layout-flex wp-block-gallery-is-layout-flex">
<figure class="wp-block-image size-large"><img title="vo1 - csvosupport" loading="eager" decoding="async" width="563" height="1024" data-id="3172" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-136-vo1-563x1024-1.jpg" alt="Dark-mode email screenshot from Talent Acquisition asking Chen to schedule a 15–30 minute call to discuss the offer timeline and next steps after a successful interview." class="wp-image-3172" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-136-vo1-563x1024-1.jpg 563w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-135-vo1-165x300-1.jpg 165w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-137-vo1-7x12-1.jpg 7w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-138-vo1.jpg 650w" sizes="auto, (max-width: 563px) 100vw, 563px" data-mwl-img-id="3172" /><figcaption class="wp-element-caption">vo1</figcaption></figure>



<figure class="wp-block-image size-large"><img title="vo2 - csvosupport" loading="eager" decoding="async" width="1024" height="273" data-id="3173" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-139-vo2-1024x273-1.jpg" alt="Email notifying about Stripe internship virtual onsite interviews, outlining two exercises ( Programming and ML Integration ) and scheduling details." class="wp-image-3173" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-139-vo2-1024x273-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-141-vo2-300x80-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-142-vo2-768x205-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-140-vo2-18x5-1.jpg 18w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-143-vo2.jpg 1505w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3173" /><figcaption class="wp-element-caption">vo2</figcaption></figure>



<figure class="wp-block-image size-full"><img title="vo3 - csvosupport" loading="eager" decoding="async" width="666" height="567" data-id="3174" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-146-vo3.jpg" alt="Stripe job offer letter screenshot for Software Engineer, Intern; includes salary (,000 bi-weekly) and visa/benefits details." class="wp-image-3174" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-146-vo3.jpg 666w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-145-vo3-300x255-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-144-vo3-14x12-1.jpg 14w" sizes="auto, (max-width: 666px) 100vw, 666px" data-mwl-img-id="3174" /><figcaption class="wp-element-caption">vo3</figcaption></figure>



<figure class="wp-block-image size-large"><img title="vo4 - csvosupport" loading="eager" decoding="async" width="636" height="1024" data-id="3175" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-148-vo4-636x1024-1.jpg" alt="Email inviting to a Data Scientist II interview with a 60-minute Jam Session and scheduling details" class="wp-image-3175" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-148-vo4-636x1024-1.jpg 636w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-147-vo4-186x300-1.jpg 186w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-149-vo4-7x12-1.jpg 7w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-150-vo4.jpg 734w" sizes="auto, (max-width: 636px) 100vw, 636px" data-mwl-img-id="3175" /><figcaption class="wp-element-caption">vo4</figcaption></figure>



<figure class="wp-block-image size-large"><img title="vo5 - csvosupport" loading="eager" decoding="async" width="1024" height="375" data-id="3180" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-151-vo5-1024x375-1.jpg" alt="Email confirming two 1-hour technical interviews and next steps for a remote interview process (scheduling details)." class="wp-image-3180" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-151-vo5-1024x375-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-155-vo5-300x110-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-156-vo5-768x281-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-152-vo5-1536x562-1.jpg 1536w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-154-vo5-2048x750-1.jpg 2048w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-153-vo5-18x7-1.jpg 18w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3180" /><figcaption class="wp-element-caption">vo5</figcaption></figure>



<figure class="wp-block-image size-large"><img title="vo6 - csvosupport" loading="eager" decoding="async" width="1024" height="838" data-id="3181" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-157-vo6-1024x838-1.jpg" alt="Text outlining a remote interview process: virtual final round, 2-hour technical interview, 45-minute HR interview, three-round four-hour interview, lunch break, and Teams." class="wp-image-3181" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-157-vo6-1024x838-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-159-vo6-300x245-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-160-vo6-768x628-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-158-vo6-15x12-1.jpg 15w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-161-vo6.jpg 1274w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3181" /><figcaption class="wp-element-caption">vo6</figcaption></figure>



<figure class="wp-block-image size-large"><img title="openai-offer - csvosupport" loading="eager" decoding="async" width="958" height="1024" data-id="3182" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-068-openai-offer-958x1024-1.jpg" alt="Offer to join OpenAI—signature requested by [name obscured], shown on a mobile email screen with a profile avatar and a star icon nearby (names obscured by blue scribbles)." class="wp-image-3182" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-068-openai-offer-958x1024-1.jpg 958w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-066-openai-offer-281x300-1.jpg 281w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-067-openai-offer-768x821-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-065-openai-offer-11x12-1.jpg 11w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-069-openai-offer.jpg 1080w" sizes="auto, (max-width: 958px) 100vw, 958px" data-mwl-img-id="3182" /><figcaption class="wp-element-caption">openai offer</figcaption></figure>



<figure class="wp-block-image size-large"><img title="tiktok-offer - csvosupport" loading="eager" decoding="async" width="637" height="1024" data-id="3183" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-127-tiktok-offer-637x1024-1.jpg" alt="Email from TikTok inviting you to join ByteDance as a Data Engineer Intern for Summer 2026, with offer letter and office address link" class="wp-image-3183" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-127-tiktok-offer-637x1024-1.jpg 637w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-126-tiktok-offer-187x300-1.jpg 187w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-128-tiktok-offer-7x12-1.jpg 7w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-129-tiktok-offer.jpg 750w" sizes="auto, (max-width: 637px) 100vw, 637px" data-mwl-img-id="3183" /><figcaption class="wp-element-caption">tiktok offer</figcaption></figure>
</figure>
</div>



<div class="gb-tabs__item" role="tabpanel" id="gb-tab-item-e9c90b20">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-5 is-layout-flex wp-block-gallery-is-layout-flex">
<figure class="wp-block-image size-large"><img title="2026 Google NG Interview Review - csvosupport" loading="eager" decoding="async" width="1000" height="671" data-id="2685" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-008-2026-google-ng-interview-review.webp" alt="Google logo on a dark tech background with a digital brain, text reads'2026 Google NG Interview Review' and 'Interview Experience &amp; Tips' (thumbnail for article)" class="wp-image-2685" style="aspect-ratio:4/3" data-mwl-img-id="2685"/><figcaption class="wp-element-caption">2026 Google NG Interview Review</figcaption></figure>



<figure class="wp-block-image size-large"><img title="How to Pass Coinbase OA - csvosupport" loading="eager" decoding="async" width="1168" height="784" data-id="2683" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-009-how-to-pass-coinbase-oa.jpg" alt="Banner: &quot;How to Pass Coinbase OA&quot; with a laptop showing code, blue neon theme for a coding tutorial." class="wp-image-2683" style="aspect-ratio:4/3" data-mwl-img-id="2683"/><figcaption class="wp-element-caption">How to Pass Coinbase OA</figcaption></figure>



<figure class="wp-block-image size-large"><img title="image - csvosupport" loading="eager" decoding="async" width="1080" height="1672" data-id="2681" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-011-image-18.png" alt="Diagram of a circular ring of drone hubs labeled 1,2,3,m-1,m around a circle; hub 1 is highlighted in green." class="wp-image-2681" style="aspect-ratio:4/3" data-mwl-img-id="2681"/><figcaption class="wp-element-caption">image</figcaption></figure>



<figure class="wp-block-image size-large"><img title="image - csvosupport" loading="eager" decoding="async" width="728" height="972" data-id="2680" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-010-image-17.png" alt="Dark-mode IDE screen showing Python code on the left and a Test Cases panel on the right; six tests ran in 1.10s with all tests passing (6 passed, 0 failed). UI shows Run/Run Tests and a Save &amp; Proceed button at the top." class="wp-image-2680" style="aspect-ratio:4/3" data-mwl-img-id="2680"/><figcaption class="wp-element-caption">image</figcaption></figure>
</figure>
</div>



<div class="gb-tabs__item" role="tabpanel" id="gb-tab-item-36de4168">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-6 is-layout-flex wp-block-gallery-is-layout-flex">
<figure class="wp-block-image size-large"><img title="2026 Google NG Interview Review - csvosupport" loading="eager" decoding="async" width="1000" height="671" data-id="2685" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-008-2026-google-ng-interview-review.webp" alt="Google logo on a dark tech background with a digital brain, text reads'2026 Google NG Interview Review' and 'Interview Experience &amp; Tips' (thumbnail for article)" class="wp-image-2685" style="aspect-ratio:4/3" data-mwl-img-id="2685"/><figcaption class="wp-element-caption">2026 Google NG Interview Review</figcaption></figure>



<figure class="wp-block-image size-large"><img title="How to Pass Coinbase OA - csvosupport" loading="eager" decoding="async" width="1168" height="784" data-id="2683" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-009-how-to-pass-coinbase-oa.jpg" alt="Banner: &quot;How to Pass Coinbase OA&quot; with a laptop showing code, blue neon theme for a coding tutorial." class="wp-image-2683" style="aspect-ratio:4/3" data-mwl-img-id="2683"/><figcaption class="wp-element-caption">How to Pass Coinbase OA</figcaption></figure>



<figure class="wp-block-image size-large"><img title="image - csvosupport" loading="eager" decoding="async" width="1080" height="1672" data-id="2681" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-011-image-18.png" alt="Diagram of a circular ring of drone hubs labeled 1,2,3,m-1,m around a circle; hub 1 is highlighted in green." class="wp-image-2681" style="aspect-ratio:4/3" data-mwl-img-id="2681"/><figcaption class="wp-element-caption">image</figcaption></figure>



<figure class="wp-block-image size-large"><img title="image - csvosupport" loading="eager" decoding="async" width="728" height="972" data-id="2680" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-010-image-17.png" alt="Dark-mode IDE screen showing Python code on the left and a Test Cases panel on the right; six tests ran in 1.10s with all tests passing (6 passed, 0 failed). UI shows Run/Run Tests and a Save &amp; Proceed button at the top." class="wp-image-2680" style="aspect-ratio:4/3" data-mwl-img-id="2680"/><figcaption class="wp-element-caption">image</figcaption></figure>
</figure>
</div>
</div>
</div>
</div>
</div>
		</div>

			</div>
</article>
		</main>
	</div>

	
	</div>
</div>
CSOP_EN_HTML;;
}

function csop_pages_zh_tw_html() {
    return <<<'CSOP_ZH_TW_HTML'
<div class="site grid-container container hfeed" id="page">
				<div class="site-content" id="content">
			
	<div class="content-area" id="primary">
		<main class="site-main" id="main">
			
<article id="post-2756" class="post-2756 page type-page status-publish" itemtype="https://schema.org/CreativeWork" itemscope>
	<div class="inside-article">
		
		<div class="entry-content" itemprop="text">
			
<div class="gb-element-0639ac5f" style="--inline-bg-image: url(https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-012-4685.webp)">
<div class="gb-element-9eda365a">
<h3 class="gb-text gb-text-193bb6ca">csvosupport 工作室｜OA 代寫｜VO 代面｜面試輔導</h3>



<h2 class="gb-text gb-text-e24a13ba">來自矽谷的靠譜面試輔助服務<br>30分鐘免費語音諮詢<br>給你最優質的海外求職輔助</h2>



<p class="gb-text gb-text-169e8bed">csvosupport 成立於2017年，團隊成員包括來自大廠科技公司的工程師、研究人員，以及有ACM算法競賽背景的導師，致力於提供最優質的面試輔導、OA代做、VO輔助和代面試服務。<br><br>我們專注服務科技行業的求職全過程。自成立以來，我們堅持以高質量輔導和透明服務為核心，堅持公開透明每一位導師的學術和工業界背景，立志成為VO輔助和代面領域的領頭羊。</p>



<div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex">
<div class="wp-block-button has-custom-width wp-block-button__width-25 is-style-fill"><a class="wp-block-button__link has-base-3-color has-accent-2-background-color has-text-color has-background has-link-color wp-element-button" href="/contact/" style="border-top-left-radius:30px;border-top-right-radius:30px;border-bottom-left-radius:30px;border-bottom-right-radius:30px">聯絡我們</a></div>
</div>
</div>
</div>



<div>
<div class="gb-element-77cfd298">
<h2 class="gb-text gb-text-f2f3f905">北美最強的面試輔助團隊</h2>



<div class="gb-element-ba61be1d"></div>



<p class="gb-text">我們憑藉多年北美及海外求職實戰經驗，打造了業內極具口碑的 「OA代寫」、「模擬面試」、「VO代面」、「面試輔助」一體化方案。從筆試到視頻面試，從技術細節到表達策略，我們深知大廠招聘的每一道關卡，能夠為你量身定製最優解法。<br><br><br>過去幾年，我們已幫助數百位客戶成功拿下 Amazon、Bloomberg、Pinterest、Meta、Stripe、Coinbase、DoorDash、Optiver、Citadel 等頂級公司的 Offer，不僅穩穩進入目標公司，更收穫了遠超行業平均水平的高額薪資。我們的客戶中，不乏年薪double的工程師，也有實現職業跨越、直接躍升為senior崗位的案例。<br><br><br>我們不是流水線服務，而是你的定製化求職夥伴：每一位客戶都會配備專屬顧問與技術導師，全程一對一指導，確保你的每一次OA與VO表現都能精準擊中招聘方的需求。</p>



<div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex">
<div class="wp-block-button has-custom-width wp-block-button__width-25 is-style-fill"><a class="wp-block-button__link has-base-3-color has-accent-2-background-color has-text-color has-background has-link-color wp-element-button" href="/price/" style="border-top-left-radius:30px;border-top-right-radius:30px;border-bottom-left-radius:30px;border-bottom-right-radius:30px">了解更多服務細節</a></div>
</div>
</div>
</div>



<div>
<div class="gb-element-8420f9a6">
<div class="gb-element-44aeb402">
<h2 class="gb-text gb-text-0e0453c1">服務範圍</h2>



<div class="gb-element-8526df89"></div>
</div>



<div class="gb-element-644ba81e">
<a class="gb-element-04df542a" href="#">
<div class="gb-element-c0b03233">
<span class="gb-shape gb-shape-67aa0073"><svg viewbox="0 0 640 512" xmlns="http://www.w3.org/2000/svg"><path d="M64 96c0-35.3 28.7-64 64-64l384 0c35.3 0 64 28.7 64 64l0 240-64 0 0-240-384 0 0 240-64 0 0-240zM0 403.2C0 392.6 8.6 384 19.2 384l601.6 0c10.6 0 19.2 8.6 19.2 19.2 0 42.4-34.4 76.8-76.8 76.8L76.8 480C34.4 480 0 445.6 0 403.2zM281 209l-31 31 31 31c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-48-48c-9.4-9.4-9.4-24.6 0-33.9l48-48c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9zM393 175l48 48c9.4 9.4 9.4 24.6 0 33.9l-48 48c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l31-31-31-31c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-a0a8ba26">OA代做</h3>



<ul style="font-size:16px;font-style:normal;font-weight:400" class="wp-block-list has-contrast-color has-text-color has-link-color wp-elements-b58a482e960967c15000248d162a6ca0">
<li>OA 代做保過，競賽大神帶你滿分通過</li>



<li>秒殺所有edge case，確保滿分提交</li>



<li>精通大廠當年最新題庫，全覆蓋！</li>



<li>最優解 + 高可讀性，品質雙重保證</li>



<li>199 USD起</li>
</ul>
</a>



<a class="gb-element-1aa32bcf" href="#">
<div class="gb-element-d54e97d1">
<span class="gb-shape gb-shape-26a52e65"><svg viewbox="0 0 640 512" xmlns="http://www.w3.org/2000/svg"><path d="M192 384c53 0 96 43 96 96 0 17.7-14.3 32-32 32L32 512c-17.7 0-32-14.3-32-32 0-53 43-96 96-96l96 0zM544 32c35.3 0 64 28.7 64 64l0 288c0 33.1-25.2 60.4-57.5 63.7l-6.5 .3-211.1 0c-5.1-24.2-16.3-46.1-32.1-64l51.2 0 0-32c0-17.7 14.3-32 32-32l96 0c17.7 0 32 14.3 32 32l0 32 32 0 0-288-352 0 0 57.3c-14.8-6-31-9.3-48-9.3-5.4 0-10.8 .3-16 1l0-49c0-35.3 28.7-64 64-64l352 0zM144 352a80 80 0 1 1 0-160 80 80 0 1 1 0 160z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-495f982c">VO輔助</h3>



<ul style="font-size:16px" class="wp-block-list has-contrast-color has-text-color has-link-color wp-elements-a159239b3c38d4716db1a045e41022ba">
<li>實時傳輸高質答案，0破綻，滿分體驗</li>



<li>雙導師協同輔助，保障x2，穩定輸出</li>



<li>海量輔助案例，Alpha品質，眼見為實</li>



<li>多通道傳輸，語音/文字同步推送答案</li>



<li>299 USD起</li>
</ul>
</a>



<a class="gb-element-3ee3a811" href="#">
<div class="gb-element-4347e508">
<span class="gb-shape gb-shape-a5802c71"><svg viewbox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM165.4 321.9c20.4 28 53.4 46.1 90.6 46.1s70.2-18.1 90.6-46.1c7.8-10.7 22.8-13.1 33.5-5.3s13.1 22.8 5.3 33.5C356.3 390 309.2 416 256 416s-100.3-26-129.4-65.9c-7.8-10.7-5.4-25.7 5.3-33.5s25.7-5.4 33.5 5.3zM144 208a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm192-32a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-0dc40ddd">VO代面</h3>



<ul style="font-size:16px" class="wp-block-list has-contrast-color has-text-color has-link-color wp-elements-45d7b5e3bfde10f432a7febaa95e4415">
<li>真人 VO代面，客戶無需出鏡</li>



<li>免費語音溝通，免費Mock展示</li>



<li>代面導師人均大廠senior+在職</li>



<li>穩OFFER，不只是說說而已</li>



<li>499 USD起</li>
</ul>
</a>



<a class="gb-element-3c706259" href="#">
<div class="gb-element-6288e754">
<span class="gb-shape gb-shape-0475d889"><svg viewbox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 141.3l0 309.3 .5-.2C311.1 427.7 369.7 416 428.8 416l19.2 0 0-320-19.2 0c-42.2 0-84.1 8.4-123.1 24.6-16.8 7-33.4 13.9-49.7 20.7zM230.9 61.5L256 72 281.1 61.5C327.9 42 378.1 32 428.8 32L464 32c26.5 0 48 21.5 48 48l0 352c0 26.5-21.5 48-48 48l-35.2 0c-50.7 0-100.9 10-147.7 29.5l-12.8 5.3c-7.9 3.3-16.7 3.3-24.6 0l-12.8-5.3C184.1 490 133.9 480 83.2 480L48 480c-26.5 0-48-21.5-48-48L0 80C0 53.5 21.5 32 48 32l35.2 0c50.7 0 100.9 10 147.7 29.5z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-afe09214">簡歷潤色</h3>



<ul style="font-size:16px" class="wp-block-list has-contrast-color has-text-color has-link-color wp-elements-c13d8f6b6f498c4224036a11e991f5fc">
<li>直擊工業技術棧，拒絕toy project</li>



<li>100%原創，圍繞客戶經歷量身定做</li>



<li>技術深度挖掘，與招聘JD無縫匹配</li>



<li>大廠視角審閱，HR + HM 二次優化</li>
</ul>
</a>



<a class="gb-element-4c34f39e" href="#">
<div class="gb-element-3276f866">
<span class="gb-shape gb-shape-91579803"><svg viewbox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M36.4 353.2c4.1-14.6 11.8-27.9 22.6-38.7l181.2-181.2 33.9-33.9c16.6 16.6 51.3 51.3 104 104l33.9 33.9-33.9 33.9-181.2 181.2c-10.7 10.7-24.1 18.5-38.7 22.6L30.4 510.6c-8.3 2.3-17.3 0-23.4-6.2S-1.4 489.3 .9 481L36.4 353.2zm55.6-3.7c-4.4 4.7-7.6 10.4-9.3 16.6l-24.1 86.9 86.9-24.1c6.4-1.8 12.2-5.1 17-9.7L91.9 349.5zm354-146.1c-16.6-16.6-51.3-51.3-104-104L308 65.5C334.5 39 349.4 24.1 352.9 20.6 366.4 7 384.8-.6 404-.6S441.6 7 455.1 20.6l35.7 35.7C504.4 69.9 512 88.3 512 107.4s-7.6 37.6-21.2 51.1c-3.5 3.5-18.4 18.4-44.9 44.9z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-967f6727">面試輔導</h3>



<ul style="font-size:16px" class="wp-block-list has-contrast-color has-text-color has-link-color wp-elements-f04fb69b3910c6fea31582a52d65c14a">
<li>大廠面試官一對一輔導，全鏈路覆蓋</li>



<li>真題實戰演練，模擬當下最熱考點</li>



<li>個性化刷題路線，傳授獨家技巧</li>



<li>面試官思維建模，教你識別提問意圖</li>
</ul>
</a>



<a class="gb-element-335115d4" href="#">
<div class="gb-element-e33fac9c">
<span class="gb-shape gb-shape-c6ccb341"><svg viewbox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M241 87.1l15 20.7 15-20.7C296 52.5 336.2 32 378.9 32 452.4 32 512 91.6 512 165.1l0 2.6c0 112.2-139.9 242.5-212.9 298.2-12.4 9.4-27.6 14.1-43.1 14.1s-30.8-4.6-43.1-14.1C139.9 410.2 0 279.9 0 167.7l0-2.6C0 91.6 59.6 32 133.1 32 175.8 32 216 52.5 241 87.1z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-0f3d2feb">我們有哪些優勢？</h3>



<ul style="font-size:16px" class="wp-block-list has-contrast-color has-text-color has-link-color wp-elements-83281534e5b98ffd525cac7a37cbc3b3">
<li>我們提供不限時長的免費語音諮詢</li>



<li>代面現場錄音供您參考，所見即所得</li>



<li>唯一支持&nbsp;senior/staff 代面的機構</li>



<li>我們始終將真誠與責任視為立業之本</li>
</ul>
</a>
</div>
</div>
</div>



<div class="gb-element-0fe52152" style="--inline-bg-image: url(https://csvosupport.com/wp-content/uploads/2026/06/local-ext-04-main-mevia-site-microsoft-2026-sde-online-assessment-cleanup-1.webp)">
<div class="gb-element-93badf17">
<h2 class="gb-text gb-text-87ab5dab">服務範圍 &#8211; 專注海外求職面試</h2>



<p class="gb-text">（服務範圍不包含中國地區的面試訂單）</p>



<p class="gb-text gb-text-ee938c30"><br>OA代寫&nbsp; &nbsp;OA代做&nbsp; &nbsp;VO代做&nbsp; &nbsp;代面試&nbsp; &nbsp;面試輔助&nbsp; &nbsp;SDE代面&nbsp; &nbsp;MLE代面試&nbsp; &nbsp;系統設計代面<br>簡歷潤色&nbsp; CV修改&nbsp; 面試Mock&nbsp; 面經分享&nbsp; VO助攻&nbsp; HackerRank代寫&nbsp; CodeSignal代做<br><br>Amazon代面&nbsp; &nbsp;亞麻輔助&nbsp; Meta代面試&nbsp; Pinterest代面&nbsp; Bloomberg代面試&nbsp; &nbsp;Uber代面試<br>Citadel代做OA&nbsp; Optiver代面&nbsp; Stripe代面試&nbsp; SnowFlake代做面試&nbsp; Atlassian面試輔助<br>北美大廠代面&nbsp; Coderpad代面 技術面試輔助 北美求職輔導 遠程面試輔助&nbsp; 矽谷代面試<br>美國面試輔導 mock面試 模擬面試 BQ輔導 演算法輔導 系統設計輔導 SDE培訓 MLE培訓&nbsp;</p>
</div>
</div>



<div>
<div class="gb-element-bf9c793f">
<div class="gb-element-03bb26a5">
<h2 class="gb-text gb-text-5d3337d5">為什麼選擇我們</h2>



<div class="gb-element-817d0990"></div>
</div>



<div class="gb-element-7fbf0c12">
<a class="gb-element-dcfdb50b" href="#">
<div class="gb-element-080f0e69">
<span class="gb-shape gb-shape-280325fc"><svg aria-hidden="true" height="1em" width="1em" viewbox="0 0 576 512" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z"></path></svg></span>
</div>



<h3 class="gb-text gb-text-5fe57c83">「公認的行業領跑者」</h3>



<ul class="wp-block-list has-accent-color has-text-color has-link-color wp-elements-677b633c3679d15aa40aad6bfd2af7a7">
<li>隨時可約語音溝通，感受導師硬核實力</li>



<li>提供工業級交付標準的面試方案</li>



<li>海量成功案例，免費觀看面試實戰演示</li>
</ul>
</a>



<a class="gb-element-f952711d" href="#">
<div class="gb-element-9b25b2c5">
<span class="gb-shape gb-shape-41fb84cb"><svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 496 512" width="1em" height="1em" aria-hidden="true"><path d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm0 96c48.6 0 88 39.4 88 88s-39.4 88-88 88-88-39.4-88-88 39.4-88 88-88zm0 344c-58.7 0-111.3-26.6-146.5-68.2 18.8-35.4 55.6-59.8 98.5-59.8 2.4 0 4.8.4 7.1 1.1 13 4.2 26.6 6.9 40.9 6.9 14.3 0 28-2.7 40.9-6.9 2.3-.7 4.7-1.1 7.1-1.1 42.9 0 79.7 24.4 98.5 59.8C359.3 421.4 306.7 448 248 448z" fill="currentColor"></path></svg></span>
</div>



<h3 class="gb-text gb-text-aae11c36">「嚴格篩選的導師團隊」</h3>



<ul class="wp-block-list has-accent-color has-text-color has-link-color wp-elements-007f9b3b796afd4e478558cb6d7c3bd2">
<li>匯聚100%濃度的專業方向PhD導師團隊</li>



<li>擁有1000+導師庫，覆蓋各大細分領域</li>



<li>導師「學術+職業」背景信息透明公開</li>
</ul>
</a>



<a class="gb-element-556bbf6d" href="#">
<div class="gb-element-f01effe1">
<span class="gb-shape gb-shape-3ee27093"><svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 512 512" width="1em" height="1em" aria-hidden="true"><path d="M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zM227.314 387.314l184-184c6.248-6.248 6.248-16.379 0-22.627l-22.627-22.627c-6.248-6.249-16.379-6.249-22.628 0L216 308.118l-70.059-70.059c-6.248-6.248-16.379-6.248-22.628 0l-22.627 22.627c-6.248 6.248-6.248 16.379 0 22.627l104 104c6.249 6.249 16.379 6.249 22.628.001z" fill="currentColor"></path></svg></span>
</div>



<h3 class="gb-text gb-text-82f291f1">「公認的行業領跑者」</h3>



<ul class="wp-block-list has-accent-color has-text-color has-link-color wp-elements-2be89e43f16f27b6f0833b659c51c04f">
<li>嚴格保障客戶隱私，加密存儲所有資料</li>



<li>堅持誠信第一，用心完成每一份交付</li>



<li>無限期的售後服務，杜絕一切後顧之憂</li>
</ul>
</a>



<a class="gb-element-bbe44f83" href="#">
<div class="gb-element-0945459f">
<span class="gb-shape gb-shape-5ef9faca"><svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 512 512" width="1em" height="1em" aria-hidden="true"><path d="M466.5 83.7l-192-80a48.15 48.15 0 0 0-36.9 0l-192 80C27.7 91.1 16 108.6 16 128c0 198.5 114.5 335.7 221.5 380.3 11.8 4.9 25.1 4.9 36.9 0C360.1 472.6 496 349.3 496 128c0-19.4-11.7-36.9-29.5-44.3zM256.1 446.3l-.1-381 175.9 73.3c-3.3 151.4-82.1 261.1-175.8 307.7z" fill="currentColor"></path></svg></span>
</div>



<h3 class="gb-text gb-text-dea3d70e">「公認的行業領跑者」</h3>



<ul class="wp-block-list has-accent-color has-text-color has-link-color wp-elements-2be89e43f16f27b6f0833b659c51c04f">
<li>嚴格保障客戶隱私，加密存儲所有資料</li>



<li>堅持誠信第一，用心完成每一份交付</li>



<li>無限期的售後服務，杜絕一切後顧之憂</li>
</ul>
</a>
</div>
</div>
</div>



<div>
<div class="gb-element-cc34185d">
<div class="gb-element-e49b6a68">
<h2 class="gb-text gb-text-d42e626f">管理團隊</h2>



<div class="gb-element-9704f49b"></div>
</div>



<div class="gb-element-cc72d6c3">
<a class="gb-element-0d2725ff" href="#">
<p class="gb-text gb-text-75fb0c21">目前就職於Google，10餘年開發經驗，目前擔任Senior Solution Architect職位，北大計算機本碩，擅長各種算法、Java、C++等編程語言。在學校期間多次參加ACM、天池大數據等多項比賽，擁有多項頂級paper、專利等，輔導幫助的1000+學生入職Google、Meta、阿里、Amazon等多個大廠。</p>



<div class="gb-element-0ccb957e">
<div>
<img fetchpriority="high" decoding="async" width="512" height="512" class="gb-media-176e053f" alt="Solid black square image (likely a placeholder or blackout)." title="6840541" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-016-6840541.webp" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-016-6840541.webp 512w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-015-6840541-300x300-1.webp 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-014-6840541-150x150-1.webp 150w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-013-6840541-12x12-1.webp 12w" sizes="(max-width: 512px) 100vw, 512px" />
</div>



<div>
<h4 class="gb-text">學長</h4>
</div>
</div>
</a>



<a class="gb-element-5e4c89d8" href="#">
<p class="gb-text gb-text-aa90639b">目前於University of Oxford讀碩士.本科某計算機強勢985，在大數據領域擁有豐富的實戰經驗，熟悉擅長HDFS、MapReduce、Yarn、Zookeeper、Hive、Flume、Kafka、HBase、Spark、Flink等，熟悉擅長MATLAB Simulink數學模型設計,具有多年信號時域、頻域、調製域分析，掌握信道、調製、編碼等常用功能的M語言實現經驗。</p>



<div class="gb-element-ab84b3b5">
<div>
<img decoding="async" class="gb-media-c8bb6e14" alt="" title="WechatIMG9866" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-007-wechatimg9866.png"/>
</div>



<div>
<h4 class="gb-text">Roger</h4>
</div>
</div>
</a>



<a class="gb-element-83eea067" href="#">
<p class="gb-text gb-text-c48aea3b">Princeton University博士，人在海外，曾在谷歌、蘋果等多家大廠工作。深度學習NLP方向擁有多篇SCI，機器學習方向擁有Github千星⭐️項目，Leetcode全國排名百名內，編程能力一流，專業輔導多年，精通TensorFlow、Keras，pytorch,QA問題，NER問題，文本分類，情感分析；對貝葉斯、隨機森林、SVM、神經網絡、聚類、PCA等有深入應用和研究。再計算機視覺上，圖像分類，圖像目標檢測，圖像分割，生成對抗網絡等具備豐富的經驗。</p>



<div class="gb-element-656e9daf">
<div>
<img decoding="async" width="404" height="296" class="gb-media-25b77a9a" alt="" title="WechatIMG7956" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-006-wechatimg7956.png" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-006-wechatimg7956.png 404w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-005-wechatimg7956-300x220-1.png 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-004-wechatimg7956-16x12-1.png 16w" sizes="(max-width: 404px) 100vw, 404px" />
</div>



<div>
<h4 class="gb-text">James</h4>
</div>
</div>
</a>



<a class="gb-element-1cf72096" href="#">
<p class="gb-text gb-text-5434fef7 translation-block">北大碩博連讀，學長多年好基友，CPA、CFA證書持有者，在商業分析、管理會計、金融工程有著豐富的輔導經驗，和學長一起努力奮鬥8年，服務學員數量<strong>1000+</strong>。</p>



<div class="gb-element-a4f0c7fe">
<div>
<img decoding="async" width="404" height="426" class="gb-media-6e39e858" alt="" title="WechatIMG7954" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-003-wechatimg7954.png" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-003-wechatimg7954.png 404w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-002-wechatimg7954-285x300-1.png 285w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-001-wechatimg7954-11x12-1.png 11w" sizes="(max-width: 404px) 100vw, 404px" />
</div>



<div>
<h4 class="gb-text">Isaac</h4>
</div>
</div>
</a>
</div>
</div>
</div>



<div>
<div class="gb-element-38c69647">
<h2 class="gb-text gb-text-20281697">客戶評價</h2>



<p class="gb-text">1000+ 成功案例，只想帶你了解真實的我們！</p>



<div class="gb-tabs gb-tabs-513a33df" data-opened-tab="1">
<div class="gb-tabs__menu gb-tabs__menu-6aae225b" role="tablist">
<div tabindex="0" class="gb-tabs__menu-item gb-tabs__menu-item-4a18984e gb-block-is-current" role="tab" id="gb-tab-menu-item-4a18984e">
<span class="gb-text gb-text-e90ae470">用戶評價</span>
</div>



<div tabindex="0" class="gb-tabs__menu-item gb-tabs__menu-item-87819c3d" role="tab" id="gb-tab-menu-item-87819c3d">
<span class="gb-text gb-text-286dab72">offer 案例</span>
</div>



<div tabindex="0" class="gb-tabs__menu-item gb-tabs__menu-item-13e7b89e" role="tab" id="gb-tab-menu-item-13e7b89e">
<span class="gb-text gb-text-a47aa7db">OA 案例</span>
</div>



<div tabindex="0" class="gb-tabs__menu-item gb-tabs__menu-item-85742302" role="tab" id="gb-tab-menu-item-85742302">
<span class="gb-text gb-text-74b2cf67">VO 案例</span>
</div>
</div>



<div class="gb-tabs__items">
<div class="gb-tabs__item gb-tabs__item-open" role="tabpanel" id="gb-tab-item-254099d5">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-1 is-layout-flex wp-block-gallery-is-layout-flex">
<figure class="wp-block-image size-large"><img title="review1 - csvosupport" loading="eager" decoding="async" width="456" height="1024" data-id="3156" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-071-review1-456x1024-1.jpg" alt="Chinese chat screenshot discussing follow-up and sending a transfer, with green and white message bubbles and an orange transfer card in the middle." class="wp-image-3156" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-071-review1-456x1024-1.jpg 456w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-070-review1-134x300-1.jpg 134w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-074-review1-768x1726-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-073-review1-684x1536-1.jpg 684w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-075-review1-912x2048-1.jpg 912w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-072-review1-5x12-1.jpg 5w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-076-review1.jpg 940w" sizes="auto, (max-width: 456px) 100vw, 456px" data-mwl-img-id="3156" /><figcaption class="wp-element-caption">review1</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review2 - csvosupport" loading="eager" decoding="async" width="636" height="1024" data-id="3157" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-078-review2-636x1024-1.jpg" alt="Screenshots of a chat in Chinese: green message says &#039;谷歌 match 上了嗎&#039;, emojis, white message &#039;上週已經發 offer 啦&#039;, an attached Google document image, and another green message &#039;不錯啊，總包多少&#039; with emoji avatars." class="wp-image-3157" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-078-review2-636x1024-1.jpg 636w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-077-review2-186x300-1.jpg 186w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-079-review2-768x1236-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-080-review2-7x12-1.jpg 7w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-081-review2.jpg 886w" sizes="auto, (max-width: 636px) 100vw, 636px" data-mwl-img-id="3157" /><figcaption class="wp-element-caption">review2</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review3 - csvosupport" loading="eager" decoding="async" width="830" height="1024" data-id="3158" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-085-review3-830x1024-1.jpg" alt="Chinese chat screenshot: green and white message bubbles discussing being in team match and waiting, with emojis and a profile icon on the right." class="wp-image-3158" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-085-review3-830x1024-1.jpg 830w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-083-review3-243x300-1.jpg 243w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-084-review3-768x947-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-082-review3-10x12-1.jpg 10w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-086-review3.jpg 1080w" sizes="auto, (max-width: 830px) 100vw, 830px" data-mwl-img-id="3158" /><figcaption class="wp-element-caption">review3</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review4 - csvosupport" loading="eager" decoding="async" width="762" height="1024" data-id="3159" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-088-review4-762x1024-1.jpg" alt="WeChat-style chat screenshot in Chinese: at 9:13 AM a distorted image preview; at 9:47 AM a green message saying &#039;怎麼樣&#039; and a small sad emoji chat avatar on the side, followed by a white message thanking the leaders for active communication and great support, then another green message &#039;看見&#039;" class="wp-image-3159" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-088-review4-762x1024-1.jpg 762w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-087-review4-223x300-1.jpg 223w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-089-review4-768x1032-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-090-review4-9x12-1.jpg 9w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-091-review4.jpg 886w" sizes="auto, (max-width: 762px) 100vw, 762px" data-mwl-img-id="3159" /><figcaption class="wp-element-caption">review4</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review5 - csvosupport" loading="eager" decoding="async" width="886" height="974" data-id="3160" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-095-review5.jpg" alt="OA proxy 聊天截圖：討論在瀏覽器中試試及騰訊會議/Zoom 的特殊設置，結束後離開房間和會議，並表示會拿到 offer，屏幕底部有天使表情。" class="wp-image-3160" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-095-review5.jpg 886w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-093-review5-273x300-1.jpg 273w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-094-review5-768x844-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-092-review5-11x12-1.jpg 11w" sizes="auto, (max-width: 886px) 100vw, 886px" data-mwl-img-id="3160" /><figcaption class="wp-element-caption">review5</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review6 - csvosupport" loading="eager" decoding="async" width="453" height="1024" data-id="3161" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-097-review6-453x1024-1.jpg" alt="Screenshot of a Chinese chat about preparing for a coding interview; green bubbles show plan to &#039;release SD&#039; and approval responses like &#039;ok&#039;." class="wp-image-3161" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-097-review6-453x1024-1.jpg 453w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-096-review6-133x300-1.jpg 133w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-100-review6-768x1737-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-099-review6-679x1536-1.jpg 679w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-101-review6-905x2048-1.jpg 905w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-098-review6-5x12-1.jpg 5w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-102-review6.jpg 930w" sizes="auto, (max-width: 453px) 100vw, 453px" data-mwl-img-id="3161" /><figcaption class="wp-element-caption">review6</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review7 - csvosupport" loading="eager" decoding="async" width="517" height="1024" data-id="3162" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-104-review7-517x1024-1.jpg" alt="Screenshots of a Chinese chat conversation in a messaging app; green bubbles on the right with Chick-fil-A avatars, a long white bubble on the left with a message about finishing a loop, and timestamps 昨天 上午11:01." class="wp-image-3162" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-104-review7-517x1024-1.jpg 517w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-103-review7-151x300-1.jpg 151w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-106-review7-768x1522-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-107-review7-775x1536-1.jpg 775w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-105-review7-6x12-1.jpg 6w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-108-review7.jpg 910w" sizes="auto, (max-width: 517px) 100vw, 517px" data-mwl-img-id="3162" /><figcaption class="wp-element-caption">review7</figcaption></figure>



<figure class="wp-block-image size-large"><img title="review8 - csvosupport" loading="eager" decoding="async" width="450" height="1024" data-id="3163" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-110-review8-450x1024-1.jpg" alt="Dark messaging thread in Chinese: congratulatory exchange after a prize transfer, with green chat bubbles saying “哈哈 恭喜！” and “OK/哈哈好” and a PDF/file transfer preview showing a WeChat/desktop UI element nearby." class="wp-image-3163" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-110-review8-450x1024-1.jpg 450w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-109-review8-132x300-1.jpg 132w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-113-review8-768x1747-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-112-review8-675x1536-1.jpg 675w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-114-review8-901x2048-1.jpg 901w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-111-review8-5x12-1.jpg 5w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-115-review8.jpg 948w" sizes="auto, (max-width: 450px) 100vw, 450px" data-mwl-img-id="3163" /><figcaption class="wp-element-caption">review8</figcaption></figure>
</figure>
</div>



<div class="gb-tabs__item" role="tabpanel" id="gb-tab-item-830667c1">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-2 is-layout-flex wp-block-gallery-is-layout-flex">
<figure class="wp-block-image size-large"><img title="amazon-offer - csvosupport" loading="eager" decoding="async" width="798" height="1024" data-id="3164" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-033-amazon-offer-798x1024-1.jpg" alt="Amazon letter on company letterhead offering a Software Dev Engineer position in Jersey City, NJ, with start date June 30, 2025 and salary details (annualized pay and sign-on)." class="wp-image-3164" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-033-amazon-offer-798x1024-1.jpg 798w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-031-amazon-offer-234x300-1.jpg 234w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-032-amazon-offer-768x986-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-034-amazon-offer-9x12-1.jpg 9w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-035-amazon-offer.jpg 916w" sizes="auto, (max-width: 798px) 100vw, 798px" data-mwl-img-id="3164" /><figcaption class="wp-element-caption">amazon offer</figcaption></figure>



<figure class="wp-block-image size-large"><img title="amazon-sde2 - csvosupport" loading="eager" decoding="async" width="1024" height="619" data-id="3165" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-042-amazon-sde2-1024x619-1.jpg" alt="Screenshot of an email header with the Amazon logo, followed by body text congratulating the recipient on an AWS offer and mentioning a formal offer letter and benefits details." class="wp-image-3165" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-042-amazon-sde2-1024x619-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-045-amazon-sde2-300x181-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-046-amazon-sde2-768x464-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-043-amazon-sde2-1536x928-1.jpg 1536w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-044-amazon-sde2-18x12-1.jpg 18w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-047-amazon-sde2.jpg 1628w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3165" /><figcaption class="wp-element-caption">amazon sde2</figcaption></figure>



<figure class="wp-block-image size-large"><img title="google-offer - csvosupport" loading="eager" decoding="async" width="1024" height="953" data-id="3166" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-048-google-offer-1024x953-1.jpg" alt="Screenshot of an email from Google Offer Letters Team via DocuSign showing a blue banner with &#039;Review Document&#039; and a Google logo, about reviewing employment documents." class="wp-image-3166" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-048-google-offer-1024x953-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-050-google-offer-300x279-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-051-google-offer-768x715-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-049-google-offer-13x12-1.jpg 13w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-052-google-offer.jpg 1160w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3166" /><figcaption class="wp-element-caption">google offer</figcaption></figure>



<figure class="wp-block-image size-large"><img title="microsoft-offer - csvosupport" loading="eager" decoding="async" width="473" height="1024" data-id="3167" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-059-microsoft-offer-473x1024-1.jpg" alt="Mobile email screenshot announcing a Microsoft internship offer, with a celebratory banner reading &#039;Congratulations!&#039; and &#039;On your offer to be a Microsoft Intern&#039;" class="wp-image-3167" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-059-microsoft-offer-473x1024-1.jpg 473w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-058-microsoft-offer-139x300-1.jpg 139w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-062-microsoft-offer-768x1662-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-061-microsoft-offer-710x1536-1.jpg 710w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-063-microsoft-offer-946x2048-1.jpg 946w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-060-microsoft-offer-6x12-1.jpg 6w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-064-microsoft-offer.jpg 1080w" sizes="auto, (max-width: 473px) 100vw, 473px" data-mwl-img-id="3167" /><figcaption class="wp-element-caption">microsoft offer</figcaption></figure>
</figure>
</div>



<div class="gb-tabs__item" role="tabpanel" id="gb-tab-item-40de34e5">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-3 is-layout-flex wp-block-gallery-is-layout-flex">
<figure class="wp-block-image size-large"><img title="amazon-oa - csvosupport" loading="eager" decoding="async" width="1024" height="819" data-id="3168" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-017-amazon-oa-1024x819-1.jpg" alt="Screenshot of a UI titled &#039;Code Question 2&#039; showing a warehouse-inspection scenario with bulleted rules, and a right panel listing test results and test cases." class="wp-image-3168" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-017-amazon-oa-1024x819-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-019-amazon-oa-300x240-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-020-amazon-oa-768x614-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-018-amazon-oa-15x12-1.jpg 15w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-021-amazon-oa.jpg 1350w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3168" /><figcaption class="wp-element-caption">amazon oa</figcaption></figure>



<figure class="wp-block-image size-large"><img title="amazon-oa2 - csvosupport" loading="eager" decoding="async" width="1024" height="764" data-id="3169" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-022-amazon-oa2-1024x764-1.jpg" alt="Split-screen screenshot: left panel shows a written problem about inventory quality; right panel shows Python code in a dark editor, with colored blocks and line numbers. watermark reads &#039;interviewAid&#039;." class="wp-image-3169" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-022-amazon-oa2-1024x764-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-024-amazon-oa2-300x224-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-025-amazon-oa2-768x573-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-023-amazon-oa2-16x12-1.jpg 16w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-026-amazon-oa2.jpg 1248w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3169" /><figcaption class="wp-element-caption">amazon oa2</figcaption></figure>



<figure class="wp-block-image size-large"><img title="amazon-oa3 - csvosupport" loading="eager" decoding="async" width="768" height="1024" data-id="3170" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-028-amazon-oa3-768x1024-1.jpg" alt="Split screen: left side shows a dark IDE window with a Test Cases list and green &#039;6 passed&#039; status; right side shows a Chinese chat conversation about AI assistant usage with green and white speech bubbles." class="wp-image-3170" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-028-amazon-oa3-768x1024-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-027-amazon-oa3-225x300-1.jpg 225w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-029-amazon-oa3-9x12-1.jpg 9w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-030-amazon-oa3.jpg 1080w" sizes="auto, (max-width: 768px) 100vw, 768px" data-mwl-img-id="3170" /><figcaption class="wp-element-caption">amazon oa3</figcaption></figure>



<figure class="wp-block-image size-large"><img title="amazon-sde-oa - csvosupport" loading="eager" decoding="async" width="1024" height="659" data-id="3171" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-036-amazon-sde-oa-1024x659-1.jpg" alt="Java Spring controller code: updateComment method with @PutMapping(&#039;/{id}&#039;), handling request and errors in a try-catch block (Info visible: ResponseEntity, NOT_FOUND, UNAUTHORIZED)." class="wp-image-3171" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-036-amazon-sde-oa-1024x659-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-039-amazon-sde-oa-300x193-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-040-amazon-sde-oa-768x494-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-037-amazon-sde-oa-1536x989-1.jpg 1536w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-038-amazon-sde-oa-18x12-1.jpg 18w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-041-amazon-sde-oa.jpg 1678w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3171" /><figcaption class="wp-element-caption">amazon sde oa</figcaption></figure>



<figure class="wp-block-image size-large"><img title="intuit-oa - csvosupport" loading="eager" decoding="async" width="1024" height="930" data-id="3176" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-053-intuit-oa-1024x930-1.jpg" alt="Screenshot of a dark UI displaying an SQL: Stock Market Software Capitalization Report with bullet points about sectors, total capitalization, and notes; includes a schema table section labeled &#039;companies&#039; at the bottom." class="wp-image-3176" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-053-intuit-oa-1024x930-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-055-intuit-oa-300x273-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-056-intuit-oa-768x698-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-054-intuit-oa-13x12-1.jpg 13w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-057-intuit-oa.jpg 1080w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3176" /><figcaption class="wp-element-caption">intuit oa</figcaption></figure>



<figure class="wp-block-image size-large"><img title="stripe-oa1 - csvosupport" loading="eager" decoding="async" width="783" height="1024" data-id="3177" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-118-stripe-oa1-783x1024-1.jpg" alt="Screenshot of a dark command-guide discussing SHUTDOWN handling, target routing, and example CONNECT/SHUTDOWN commands with a right-side test results panel." class="wp-image-3177" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-118-stripe-oa1-783x1024-1.jpg 783w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-116-stripe-oa1-229x300-1.jpg 229w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-117-stripe-oa1-768x1005-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-119-stripe-oa1-9x12-1.jpg 9w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-120-stripe-oa1.jpg 1080w" sizes="auto, (max-width: 783px) 100vw, 783px" data-mwl-img-id="3177" /><figcaption class="wp-element-caption">stripe oa1</figcaption></figure>



<figure class="wp-block-image size-large"><img title="tiktok-oa - csvosupport" loading="eager" decoding="async" width="1024" height="760" data-id="3178" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-121-tiktok-oa-1024x760-1.jpg" alt="Split-screen: left side shows a UI with a &#039;Create post&#039; form and &#039;Publish post&#039; button, plus a &#039;Recent posts&#039; list; right side displays a code editor with a project file tree and open files, overlaid by a large watermark." class="wp-image-3178" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-121-tiktok-oa-1024x760-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-123-tiktok-oa-300x223-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-124-tiktok-oa-768x570-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-122-tiktok-oa-16x12-1.jpg 16w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-125-tiktok-oa.jpg 1455w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3178" /><figcaption class="wp-element-caption">tiktok oa</figcaption></figure>



<figure class="wp-block-image size-large"><img title="two-sigma-oa - csvosupport" loading="eager" decoding="async" width="1024" height="883" data-id="3179" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-130-two-sigma-oa-1024x883-1.jpg" alt="Screenshot of a dark coding notebook UI titled &#039;Daily Temperature By Town&#039; with long descriptive text on the left (Part One and Part Two tasks) and a code editor/results panel on the right, including a &#039;Run Code&#039; button and a &#039;Compiler Message&#039; section." class="wp-image-3179" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-130-two-sigma-oa-1024x883-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-132-two-sigma-oa-300x259-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-133-two-sigma-oa-768x662-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-131-two-sigma-oa-14x12-1.jpg 14w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-134-two-sigma-oa.jpg 1252w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3179" /><figcaption class="wp-element-caption">two sigma oa</figcaption></figure>
</figure>
</div>



<div class="gb-tabs__item" role="tabpanel" id="gb-tab-item-366108b8">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-4 is-layout-flex wp-block-gallery-is-layout-flex">
<figure class="wp-block-image size-large"><img title="vo1 - csvosupport" loading="eager" decoding="async" width="563" height="1024" data-id="3172" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-136-vo1-563x1024-1.jpg" alt="Dark-mode email screenshot from Talent Acquisition asking Chen to schedule a 15–30 minute call to discuss the offer timeline and next steps after a successful interview." class="wp-image-3172" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-136-vo1-563x1024-1.jpg 563w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-135-vo1-165x300-1.jpg 165w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-137-vo1-7x12-1.jpg 7w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-138-vo1.jpg 650w" sizes="auto, (max-width: 563px) 100vw, 563px" data-mwl-img-id="3172" /><figcaption class="wp-element-caption">vo1</figcaption></figure>



<figure class="wp-block-image size-large"><img title="vo2 - csvosupport" loading="eager" decoding="async" width="1024" height="273" data-id="3173" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-139-vo2-1024x273-1.jpg" alt="Email notifying about Stripe internship virtual onsite interviews, outlining two exercises ( Programming and ML Integration ) and scheduling details." class="wp-image-3173" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-139-vo2-1024x273-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-141-vo2-300x80-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-142-vo2-768x205-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-140-vo2-18x5-1.jpg 18w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-143-vo2.jpg 1505w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3173" /><figcaption class="wp-element-caption">vo2</figcaption></figure>



<figure class="wp-block-image size-full"><img title="vo3 - csvosupport" loading="eager" decoding="async" width="666" height="567" data-id="3174" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-146-vo3.jpg" alt="Stripe job offer letter screenshot for Software Engineer, Intern; includes salary (,000 bi-weekly) and visa/benefits details." class="wp-image-3174" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-146-vo3.jpg 666w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-145-vo3-300x255-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-144-vo3-14x12-1.jpg 14w" sizes="auto, (max-width: 666px) 100vw, 666px" data-mwl-img-id="3174" /><figcaption class="wp-element-caption">vo3</figcaption></figure>



<figure class="wp-block-image size-large"><img title="vo4 - csvosupport" loading="eager" decoding="async" width="636" height="1024" data-id="3175" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-148-vo4-636x1024-1.jpg" alt="Email inviting to a Data Scientist II interview with a 60-minute Jam Session and scheduling details" class="wp-image-3175" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-148-vo4-636x1024-1.jpg 636w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-147-vo4-186x300-1.jpg 186w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-149-vo4-7x12-1.jpg 7w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-150-vo4.jpg 734w" sizes="auto, (max-width: 636px) 100vw, 636px" data-mwl-img-id="3175" /><figcaption class="wp-element-caption">vo4</figcaption></figure>



<figure class="wp-block-image size-large"><img title="vo5 - csvosupport" loading="eager" decoding="async" width="1024" height="375" data-id="3180" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-151-vo5-1024x375-1.jpg" alt="Email confirming two 1-hour technical interviews and next steps for a remote interview process (scheduling details)." class="wp-image-3180" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-151-vo5-1024x375-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-155-vo5-300x110-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-156-vo5-768x281-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-152-vo5-1536x562-1.jpg 1536w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-154-vo5-2048x750-1.jpg 2048w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-153-vo5-18x7-1.jpg 18w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3180" /><figcaption class="wp-element-caption">vo5</figcaption></figure>



<figure class="wp-block-image size-large"><img title="vo6 - csvosupport" loading="eager" decoding="async" width="1024" height="838" data-id="3181" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-157-vo6-1024x838-1.jpg" alt="Text outlining a remote interview process: virtual final round, 2-hour technical interview, 45-minute HR interview, three-round four-hour interview, lunch break, and Teams." class="wp-image-3181" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-157-vo6-1024x838-1.jpg 1024w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-159-vo6-300x245-1.jpg 300w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-160-vo6-768x628-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-158-vo6-15x12-1.jpg 15w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-161-vo6.jpg 1274w" sizes="auto, (max-width: 1024px) 100vw, 1024px" data-mwl-img-id="3181" /><figcaption class="wp-element-caption">vo6</figcaption></figure>



<figure class="wp-block-image size-large"><img title="openai-offer - csvosupport" loading="eager" decoding="async" width="958" height="1024" data-id="3182" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-068-openai-offer-958x1024-1.jpg" alt="Offer to join OpenAI—signature requested by [name obscured], shown on a mobile email screen with a profile avatar and a star icon nearby (names obscured by blue scribbles)." class="wp-image-3182" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-068-openai-offer-958x1024-1.jpg 958w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-066-openai-offer-281x300-1.jpg 281w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-067-openai-offer-768x821-1.jpg 768w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-065-openai-offer-11x12-1.jpg 11w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-069-openai-offer.jpg 1080w" sizes="auto, (max-width: 958px) 100vw, 958px" data-mwl-img-id="3182" /><figcaption class="wp-element-caption">openai offer</figcaption></figure>



<figure class="wp-block-image size-large"><img title="tiktok-offer - csvosupport" loading="eager" decoding="async" width="637" height="1024" data-id="3183" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-127-tiktok-offer-637x1024-1.jpg" alt="Email from TikTok inviting you to join ByteDance as a Data Engineer Intern for Summer 2026, with offer letter and office address link" class="wp-image-3183" style="aspect-ratio:4/3" srcset="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-127-tiktok-offer-637x1024-1.jpg 637w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-126-tiktok-offer-187x300-1.jpg 187w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-128-tiktok-offer-7x12-1.jpg 7w, https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-129-tiktok-offer.jpg 750w" sizes="auto, (max-width: 637px) 100vw, 637px" data-mwl-img-id="3183" /><figcaption class="wp-element-caption">tiktok offer</figcaption></figure>
</figure>
</div>



<div class="gb-tabs__item" role="tabpanel" id="gb-tab-item-e9c90b20">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-5 is-layout-flex wp-block-gallery-is-layout-flex">
<figure class="wp-block-image size-large"><img title="2026 Google NG Interview Review - csvosupport" loading="eager" decoding="async" width="1000" height="671" data-id="2685" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-008-2026-google-ng-interview-review.webp" alt="Google logo on a dark tech background with a digital brain, text reads'2026 Google NG Interview Review' and 'Interview Experience &amp; Tips' (thumbnail for article)" class="wp-image-2685" style="aspect-ratio:4/3" data-mwl-img-id="2685"/><figcaption class="wp-element-caption">2026 Google NG Interview Review</figcaption></figure>



<figure class="wp-block-image size-large"><img title="How to Pass Coinbase OA - csvosupport" loading="eager" decoding="async" width="1168" height="784" data-id="2683" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-009-how-to-pass-coinbase-oa.jpg" alt="Banner: &quot;How to Pass Coinbase OA&quot; with a laptop showing code, blue neon theme for a coding tutorial." class="wp-image-2683" style="aspect-ratio:4/3" data-mwl-img-id="2683"/><figcaption class="wp-element-caption">How to Pass Coinbase OA</figcaption></figure>



<figure class="wp-block-image size-large"><img title="image - csvosupport" loading="eager" decoding="async" width="1080" height="1672" data-id="2681" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-011-image-18.png" alt="Diagram of a circular ring of drone hubs labeled 1,2,3,m-1,m around a circle; hub 1 is highlighted in green." class="wp-image-2681" style="aspect-ratio:4/3" data-mwl-img-id="2681"/><figcaption class="wp-element-caption">image</figcaption></figure>



<figure class="wp-block-image size-large"><img title="image - csvosupport" loading="eager" decoding="async" width="728" height="972" data-id="2680" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-010-image-17.png" alt="Dark-mode IDE screen showing Python code on the left and a Test Cases panel on the right; six tests ran in 1.10s with all tests passing (6 passed, 0 failed). UI shows Run/Run Tests and a Save &amp; Proceed button at the top." class="wp-image-2680" style="aspect-ratio:4/3" data-mwl-img-id="2680"/><figcaption class="wp-element-caption">image</figcaption></figure>
</figure>
</div>



<div class="gb-tabs__item" role="tabpanel" id="gb-tab-item-36de4168">
<figure class="wp-block-gallery has-nested-images columns-4 is-cropped wp-block-gallery-6 is-layout-flex wp-block-gallery-is-layout-flex">
<figure class="wp-block-image size-large"><img title="2026 Google NG Interview Review - csvosupport" loading="eager" decoding="async" width="1000" height="671" data-id="2685" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-008-2026-google-ng-interview-review.webp" alt="Google logo on a dark tech background with a digital brain, text reads'2026 Google NG Interview Review' and 'Interview Experience &amp; Tips' (thumbnail for article)" class="wp-image-2685" style="aspect-ratio:4/3" data-mwl-img-id="2685"/><figcaption class="wp-element-caption">2026 Google NG Interview Review</figcaption></figure>



<figure class="wp-block-image size-large"><img title="How to Pass Coinbase OA - csvosupport" loading="eager" decoding="async" width="1168" height="784" data-id="2683" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-009-how-to-pass-coinbase-oa.jpg" alt="Banner: &quot;How to Pass Coinbase OA&quot; with a laptop showing code, blue neon theme for a coding tutorial." class="wp-image-2683" style="aspect-ratio:4/3" data-mwl-img-id="2683"/><figcaption class="wp-element-caption">How to Pass Coinbase OA</figcaption></figure>



<figure class="wp-block-image size-large"><img title="image - csvosupport" loading="eager" decoding="async" width="1080" height="1672" data-id="2681" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-011-image-18.png" alt="Diagram of a circular ring of drone hubs labeled 1,2,3,m-1,m around a circle; hub 1 is highlighted in green." class="wp-image-2681" style="aspect-ratio:4/3" data-mwl-img-id="2681"/><figcaption class="wp-element-caption">image</figcaption></figure>



<figure class="wp-block-image size-large"><img title="image - csvosupport" loading="eager" decoding="async" width="728" height="972" data-id="2680" src="https://csvosupport.com/wp-content/uploads/2026/06/demo-ext-010-image-17.png" alt="Dark-mode IDE screen showing Python code on the left and a Test Cases panel on the right; six tests ran in 1.10s with all tests passing (6 passed, 0 failed). UI shows Run/Run Tests and a Save &amp; Proceed button at the top." class="wp-image-2680" style="aspect-ratio:4/3" data-mwl-img-id="2680"/><figcaption class="wp-element-caption">image</figcaption></figure>
</figure>
</div>
</div>
</div>
</div>
</div>
		</div>

			</div>
</article>
		</main>
	</div>

	
	</div>
</div>
CSOP_ZH_TW_HTML;;
}
