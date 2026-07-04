<?php
/**
 * Site Workbench Snippet Export
 * Name: CSOfferPrep 眉页脚页系统
 * Enabled: no
 * Shortcodes: csop_header, csop_footer
 * Updated At: 2026-06-27 02:12:54
 * Original ID: snippet_5074b216-385b-423d-9cf6-9598b166b036
 */
/**
 * CSOfferPrep global header and footer system.
 * Shortcodes: [csop_header], [csop_footer]
 */
defined('ABSPATH') || exit;

add_action('admin_menu', 'csop_hf_admin_menu', 20);
add_action('admin_enqueue_scripts', 'csop_hf_admin_assets');
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
        'site_name' => 'CSOfferPrep',
        'home_url' => '/',
        'logo_image' => 'https://csofferprep.com/wp-content/uploads/2026/05/cropped-logo_prep-e1780507206758.png',
        'mobile_label' => '菜单',
        'search_label' => 'Open search',
        'search_placeholder' => 'Search...',
        'menu_items' => "🏚 网站首页|/\n📖 面试真题|/blog/\n⏺ 关于我们|/about_us/\n💰 服务&价格|/price/\n📬 联系学长|/contact/",
        'language_label' => 'Language',
        'language_items' => "简体中文|/|https://csofferprep.com/wp-content/plugins/translatepress-multilingual/assets/flags/4x3/zh_CN.svg\nEnglish|/en/|https://csofferprep.com/wp-content/plugins/translatepress-multilingual/assets/flags/4x3/en_US.svg\n繁體中文|/zh_tw/|https://csofferprep.com/wp-content/plugins/translatepress-multilingual/assets/flags/4x3/zh_TW.svg",
        'footer_bg' => '#f6f6f6',
        'footer_border' => 'rgba(135,135,135,0.52)',
        'qr_wechat_image' => 'https://csofferprep.com/wp-content/uploads/2023/03/WhatsApp-Image-2026-06-06-at-73252-PM-2.jpeg',
        'qr_wechat_label' => '微信（美国账号）',
        'qr_whatsapp_image' => 'https://csofferprep.com/wp-content/uploads/2023/03/WhatsApp-Image-2026-06-06-at-73252-PM-3.jpeg',
        'qr_whatsapp_label' => 'WhatsApp（推荐）',
        'quick_title' => 'Quick Links',
        'quick_links' => "🏚 网站首页|/\n📖 面试真题|/blog/\n⏺ 关于我们|/about_us/\n💰  服务&价格|/price/\n📬 联系学长|/contact/",
        'contact_title' => 'Get In Touch',
        'telegram_text' => '@codework520',
        'telegram_url' => 'https://t.me/codework520',
        'whatsapp_text' => '@csofferprep',
        'whatsapp_url' => 'https://wa.me/8617282592082',
        'email_text' => 'azn7u2@gmail.com',
        'email_url' => 'mailto:azn7u2@gmail.com',
        'wechat_text' => 'www521314net',
        'copyright_text' => '© 2026 CSOfferPrep • 版权所有',
        'footer_contact_text' => '联系我们',
        'footer_contact_url' => '/contact/',
    );
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

function csop_hf_start_buffer() {
    if (!csop_hf_can_render()) return;
    ob_start('csop_hf_inject_markup');
}

function csop_hf_inject_markup($html) {
    if (!is_string($html) || $html === '') return $html;
    if (stripos($html, '<html') === false && stripos($html, '<body') === false) return $html;

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

    return $html;
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
                            <img class="header-image is-logo-image" alt="<?php echo esc_attr($settings['site_name']); ?>" src="<?php echo esc_url($settings['logo_image']); ?>" title="<?php echo esc_attr($settings['site_name']); ?>" width="334" height="286" data-csop-src="logo_image">
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
                                        <a href="<?php echo esc_url(csop_hf_resolve_url($item['url'])); ?>">
                                            <span data-no-translation>
                                                <?php if (!empty($item['image'])): ?>
                                                    <img src="<?php echo esc_url($item['image']); ?>" class="trp-flag-image" alt="" role="presentation" loading="lazy" decoding="async" width="18" height="14">
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
                    <div class="gb-element-3ff89166">
                        <img loading="lazy" decoding="async" width="523" height="528" class="gb-media-dab60b27" alt="" title="<?php echo esc_attr($settings['qr_wechat_label']); ?>" src="<?php echo esc_url($settings['qr_wechat_image']); ?>" data-csop-src="qr_wechat_image">
                        <p class="gb-text" data-csop-bind="qr_wechat_label"><?php echo esc_html($settings['qr_wechat_label']); ?></p>
                    </div>

                    <div class="gb-element-b12f2b12">
                        <img loading="lazy" decoding="async" width="300" height="301" class="gb-media-1aec2793" alt="QR code" title="<?php echo esc_attr($settings['qr_whatsapp_label']); ?>" src="<?php echo esc_url($settings['qr_whatsapp_image']); ?>" data-csop-src="qr_whatsapp_image">
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
                        <?php echo csop_hf_contact_line('Whatsapp:', $settings['whatsapp_text'], $settings['whatsapp_url'], 'whatsapp_text'); ?>
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
.csop-hf-wrap,.csop-hf-footer{font-family:"Open Sans",Arial,Helvetica,sans-serif;box-sizing:border-box}.csop-hf-wrap *,.csop-hf-wrap *:before,.csop-hf-wrap *:after,.csop-hf-footer *,.csop-hf-footer *:before,.csop-hf-footer *:after{box-sizing:border-box}.csop-hf-skip{position:absolute;left:-9999px}.csop-hf-header.main-navigation{width:100%;background:var(--csop-hf-nav-bg);color:var(--csop-hf-nav-text);position:sticky;top:0;z-index:9998;box-shadow:0 2px 2px -2px rgba(0,0,0,.2)}.csop-hf-header .inside-navigation{max-width:var(--csop-hf-width);min-height:var(--csop-hf-nav-height);margin:0 auto;padding:0 20px;display:flex;align-items:center;justify-content:flex-end}.csop-hf-header .navigation-branding{display:flex;align-items:center;margin-right:auto;min-width:0}.csop-hf-header .site-logo{line-height:0;flex:0 0 auto}.csop-hf-header .site-logo a{display:block}.csop-hf-header .header-image{display:block;height:var(--csop-hf-nav-height);width:auto;max-width:none}.csop-hf-header .main-title{font-size:25px;line-height:var(--csop-hf-nav-height);margin:0 20px 0 10px;font-weight:400;white-space:nowrap}.csop-hf-header .main-title a,.csop-hf-header a{color:var(--csop-hf-nav-text);text-decoration:none}.csop-hf-header .main-nav{display:flex;align-self:stretch}.csop-hf-header .main-nav ul{list-style:none;margin:0;padding:0}.csop-hf-header .main-nav>ul{display:flex;align-items:stretch}.csop-hf-header .menu>li{position:relative;margin:0}.csop-hf-header .main-nav .menu>li>a,.csop-hf-header .menu-bar-item button,.csop-hf-header .menu-toggle{min-height:var(--csop-hf-nav-height);line-height:var(--csop-hf-nav-height);display:flex;align-items:center;color:var(--csop-hf-nav-text);background:transparent;border:0;padding:0 20px;font-size:17px;cursor:pointer}.csop-hf-header .main-nav .menu>li.current-menu-item>a,.csop-hf-header .main-nav .menu>li:hover>a,.csop-hf-header .main-nav .menu>li:focus-within>a,.csop-hf-header .menu-bar-item button:hover{background:var(--csop-hf-nav-hover);color:var(--csop-hf-nav-text)}.csop-hf-header .dropdown-menu-toggle{display:inline-flex;margin-left:8px}.csop-hf-header svg{width:1em;height:1em;fill:currentColor;display:block}.csop-hf-header .sub-menu{display:none;position:absolute;left:0;top:100%;min-width:190px;background:var(--csop-hf-nav-bg);z-index:9999;box-shadow:0 2px 2px rgba(0,0,0,.15)}.csop-hf-header li:hover>.sub-menu,.csop-hf-header li:focus-within>.sub-menu{display:block}.csop-hf-header .sub-menu li a{display:flex;align-items:center;line-height:45px;min-height:45px;padding:0 20px;white-space:nowrap;font-size:16px;color:var(--csop-hf-nav-text)}.csop-hf-header .sub-menu li a:hover{background:var(--csop-hf-nav-hover)}.csop-hf-header .trp-flag-image{margin-right:8px;vertical-align:middle}.csop-hf-header .menu-bar-items{display:flex;align-self:stretch}.csop-hf-header .menu-bar-item{display:flex}.csop-hf-header .menu-bar-item button{width:60px;justify-content:center;padding:0}.csop-hf-header .menu-toggle{display:none;color:var(--csop-hf-nav-text)}.csop-hf-header .menu-toggle .icon-menu-bars{display:inline-grid;place-items:center;margin-right:8px}.csop-hf-header .menu-toggle .icon-menu-bars svg+svg{display:none}.csop-hf-header.csop-mobile-open .menu-toggle .icon-menu-bars svg:first-child{display:none}.csop-hf-header.csop-mobile-open .menu-toggle .icon-menu-bars svg+svg{display:block}.csop-hf-search-panel{background:var(--csop-hf-nav-hover);padding:14px 20px}.csop-hf-search-panel form{max-width:var(--csop-hf-width);margin:0 auto;display:flex;gap:10px}.csop-hf-search-panel input{flex:1;min-width:0;height:44px;border:1px solid rgba(255,255,255,.25);background:#fff;color:#222;padding:0 14px;font-size:16px}.csop-hf-search-panel button{height:44px;border:0;background:#1b78e2;color:#fff;padding:0 20px;font-size:15px;cursor:pointer}.csop-hf-footer{--gb-container-width:1200px;--base-2:#f7f8f9;--contrast-2:#2f4468;--contrast-3:#878787;--accent-2:#1b78e2;color:#212121}.csop-hf-footer a{color:#1b78e2;text-decoration:none}.csop-hf-footer a:hover{color:#35343a}.csop-hf-footer p,.csop-hf-footer h3{margin-top:0}.csop-hf-footer .gb-element-80d35441{background-color:var(--base-2);border-top:1px solid var(--csop-hf-footer-border)}.csop-hf-footer .gb-element-e3cf7d4a{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:40px 20px}.csop-hf-footer .gb-element-084a3b6e{column-gap:2em;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));row-gap:1em}.csop-hf-footer .gb-element-3ff89166,.csop-hf-footer .gb-element-b12f2b12{text-align:center}.csop-hf-footer .gb-element-13da7ed5{padding-left:60px;text-align:left}.csop-hf-footer .gb-element-d10d2533{padding-left:20px}.csop-hf-footer .gb-element-76365dcf{display:flex;justify-content:space-between;margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:20px}.csop-hf-footer .gb-element-6dacc793{column-gap:15px;display:flex}.csop-hf-footer .gb-element-7de49033{background-color:var(--base-2)}.csop-hf-footer .gb-element-d4b812ad{margin-left:auto;margin-right:auto;max-width:var(--gb-container-width);padding:20px}.csop-hf-footer .gb-element-511ef82e{column-gap:1em;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));row-gap:1em}.csop-hf-footer .gb-media-dab60b27,.csop-hf-footer .gb-media-1aec2793{height:auto;max-width:100%;object-fit:cover;width:100%}.csop-hf-footer .wp-block-navigation__container{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;align-items:flex-start;gap:.5em}.csop-hf-footer .wp-block-navigation-item__content{display:inline-block;color:#1b78e2}.csop-hf-footer .gb-text-09cfc29e,.csop-hf-footer .gb-text-0897f7de,.csop-hf-footer .gb-text-b5d58ce0{margin-bottom:10px}.csop-hf-footer .gb-text-1a21da79{margin-bottom:0}.csop-hf-footer .gb-text-c3994867{font-size:15px;margin-bottom:0}.csop-hf-footer .gb-text-7720e281{display:block;font-size:15px;margin-bottom:0;text-align:right}.csop-hf-footer .gb-text-7720e281 a{color:var(--contrast-2)}.csop-hf-footer .gb-text-7720e281 a:hover{color:var(--contrast-3);font-size:15px}@media(max-width:1024px){.csop-hf-footer .gb-element-76365dcf{align-items:center;flex-direction:column;justify-content:center;row-gap:20px}.csop-hf-footer .gb-element-6dacc793{order:-1}}@media(max-width:900px){.csop-hf-header .inside-navigation{padding:0}.csop-hf-header .navigation-branding{margin-left:10px;margin-right:auto}.csop-hf-header .main-title{margin-right:10px}.csop-hf-header .menu-toggle{display:flex;align-items:center;padding:0 15px}.csop-hf-header .main-nav{display:none;width:100%;order:5;background:var(--csop-hf-nav-bg)}.csop-hf-header.csop-mobile-open .main-nav{display:block}.csop-hf-header .inside-navigation{flex-wrap:wrap;justify-content:space-between}.csop-hf-header .main-nav>ul{display:block;width:100%}.csop-hf-header .main-nav .menu>li>a{line-height:52px;min-height:52px}.csop-hf-header .sub-menu{position:static;box-shadow:none;background:rgba(0,0,0,.16);display:none}.csop-hf-header .menu-item-has-children.csop-sub-open>.sub-menu{display:block}.csop-hf-header .menu-bar-items{margin-left:0}.csop-hf-footer .gb-element-084a3b6e{grid-template-columns:1fr 1fr}.csop-hf-footer .gb-element-13da7ed5,.csop-hf-footer .gb-element-d10d2533{padding-left:0}}@media(max-width:767px){.csop-hf-footer .gb-element-084a3b6e{grid-template-columns:1fr}.csop-hf-footer .gb-element-511ef82e{grid-template-columns:1fr}.csop-hf-footer .gb-text-c3994867{text-align:center}.csop-hf-footer .gb-text-7720e281{text-align:center}}
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
        'CSOfferPrep 眉页脚页',
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
        echo '<div class="notice notice-success is-dismissible"><p>已保存 CSOfferPrep 眉页脚页设置。</p></div>';
    }

    if (isset($_POST['csop_hf_reset'])) {
        check_admin_referer('csop_hf_save_action');
        update_option(csop_hf_option_name(), csop_hf_defaults());
        echo '<div class="notice notice-success is-dismissible"><p>已恢复默认 demo 数据。</p></div>';
    }

    $settings = csop_hf_get_options();
    ?>
    <div class="wrap csop-hf-admin-wrap">
        <h1>CSOfferPrep 眉页脚页可视化编辑</h1>
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
                        <button type="submit" name="csop_hf_reset" class="button button-secondary button-hero" onclick="return confirm('确定恢复 CSOfferPrep demo 默认头尾？')">恢复默认</button>
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
                                    <h2>CSOfferPrep 内容区域预览</h2>
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
