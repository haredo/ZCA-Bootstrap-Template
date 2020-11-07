<?php
/**
 * Common Template
 *
 * BOOTSTRAP v3.0.0
 *
 * outputs the html header. i,e, everything that comes before the \</head\> tag <br />
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All 2020 May 12 Modified in v1.5.7 $
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$zco_notifier->notify('NOTIFY_HTML_HEAD_START', $current_page_base, $template_dir);

// Prevent clickjacking risks by setting X-Frame-Options:SAMEORIGIN
header('X-Frame-Options:SAMEORIGIN');

/**
 * load the module for generating page meta-tags
 */
require(DIR_WS_MODULES . zen_get_module_directory('meta_tags.php'));
/**
 * output main page HEAD tag and related headers/meta-tags, etc
 */
?>
<!DOCTYPE html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://code.jquery.com">

    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo META_TAG_TITLE; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
    <meta name="keywords" content="<?php echo META_TAG_KEYWORDS; ?>">
    <meta name="description" content="<?php echo META_TAG_DESCRIPTION; ?>">
    <meta name="author" content="<?php echo STORE_NAME ?>">
    <meta name="generator" content="shopping cart program by Zen Cart&reg;, https://www.zen-cart.com eCommerce">
    <?php if (defined('ROBOTS_PAGES_TO_SKIP') && in_array($current_page_base, explode(",", constant('ROBOTS_PAGES_TO_SKIP'))) || $current_page_base == 'down_for_maintenance' || $robotsNoIndex === true) { ?>
      <meta name="robots" content="noindex, nofollow">
    <?php } ?>
    <?php if (defined('FAVICON')) { ?>
      <link href="<?php echo FAVICON; ?>" type="image/x-icon" rel="icon">
      <link href="<?php echo FAVICON; ?>" type="image/x-icon" rel="shortcut icon">
    <?php } //endif FAVICON  ?>

    <base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_SERVER . DIR_WS_CATALOG ); ?>" />
    <?php if (isset($canonicalLink) && $canonicalLink != '') { ?>
      <link href="<?php echo $canonicalLink; ?>" rel="canonical">
    <?php } ?>
    <?php
    // BOF hreflang for multilingual sites
    if (!isset($lng) || (isset($lng) && !is_object($lng))) {
      $lng = new language;
    }

    if (count($lng->catalog_languages) > 1) {
      foreach ($lng->catalog_languages as $key => $value) {
        echo '<link href="' . ($this_is_home_page ? zen_href_link(FILENAME_DEFAULT, 'language=' . $key, $request_type, false) : $canonicalLink . (strpos($canonicalLink, '?') ? '&amp;' : '?') . 'language=' . $key) . '" hreflang="' . $key . '" rel="alternate">' . "\n";
      }
    }
    // EOF hreflang for multilingual sites
    // Important to load Bootstrap CSS First...
    ?>
<!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA==" crossorigin="anonymous" />

    <?php
    /**
     * load all template-specific stylesheets, named like "style*.css", alphabetically
     */
    $directory_array = $template->get_template_part($template->get_template_dir('.css', DIR_WS_TEMPLATE, $current_page_base, 'css'), '/^style/', '.css');
    foreach ($directory_array as $key => $value) {
      echo '<link href="' . $template->get_template_dir('.css', DIR_WS_TEMPLATE, $current_page_base, 'css') . '/' . $value . '" rel="stylesheet">' . "\n";
    }
    /**
     * load stylesheets on a per-page/per-language/per-product/per-manufacturer/per-category basis. Concept by Juxi Zoza.
     */
    $manufacturers_id = (isset($_GET['manufacturers_id'])) ? $_GET['manufacturers_id'] : '';
    $tmp_products_id = (isset($_GET['products_id'])) ? (int)$_GET['products_id'] : '';
    $tmp_pagename = ($this_is_home_page) ? 'index_home' : $current_page_base;
    if ($current_page_base == 'page' && isset($ezpage_id)) {
      $tmp_pagename = $current_page_base . (int)$ezpage_id;
    }
    $sheets_array = array('/' . $_SESSION['language'] . '_stylesheet',
      '/' . $tmp_pagename,
      '/' . $_SESSION['language'] . '_' . $tmp_pagename,
      '/c_' . $cPath,
      '/' . $_SESSION['language'] . '_c_' . $cPath,
      '/m_' . $manufacturers_id,
      '/' . $_SESSION['language'] . '_m_' . (int)$manufacturers_id,
      '/p_' . $tmp_products_id,
      '/' . $_SESSION['language'] . '_p_' . $tmp_products_id
    );
    foreach ($sheets_array as $key => $value) {
      //echo "<!--looking for: $value-->\n";
      $perpagefile = $template->get_template_dir('.css', DIR_WS_TEMPLATE, $current_page_base, 'css') . $value . '.css';
      if (file_exists($perpagefile)) {
        echo '<link href="' . $perpagefile . '" rel="stylesheet">' . "\n";
      }
    }

    /**
     *  custom category handling for a parent and all its children ... works for any c_XX_XX_children.css  where XX_XX is any parent category
     */
    $tmp_cats = explode('_', $cPath);
    $value = '';
    foreach ($tmp_cats as $val) {
      $value .= $val;
      $perpagefile = $template->get_template_dir('.css', DIR_WS_TEMPLATE, $current_page_base, 'css') . '/c_' . $value . '_children.css';
      if (file_exists($perpagefile)) {
        echo '<link href="' . $perpagefile . '" rel="stylesheet">' . "\n";
      }
      $perpagefile = $template->get_template_dir('.css', DIR_WS_TEMPLATE, $current_page_base, 'css') . '/' . $_SESSION['language'] . '_c_' . $value . '_children.css';
      if (file_exists($perpagefile)) {
        echo '<link href="' . $perpagefile . '" rel="stylesheet">' . "\n";
      }
      $value .= '_';
    }

    /**
     * load printer-friendly stylesheets -- named like "print*.css", alphabetically
     */
    $directory_array = $template->get_template_part($template->get_template_dir('.css', DIR_WS_TEMPLATE, $current_page_base, 'css'), '/^print/', '.css');
    sort($directory_array);
    foreach ($directory_array as $key => $value) {
      echo '<link href="' . $template->get_template_dir('.css', DIR_WS_TEMPLATE, $current_page_base, 'css') . '/' . $value . '" media="print" rel="stylesheet">' . "\n";
    }

    require($template->get_template_dir('stylesheet_zca_colors.php', DIR_WS_TEMPLATE, $current_page_base, 'css') . '/stylesheet_zca_colors.php');

    /** CDN for jQuery core * */
    ?>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<?php
/*
    <script>window.jQuery || document.write(unescape('%3Cscript src="<?php echo $template->get_template_dir('.js', DIR_WS_TEMPLATE, $current_page_base, 'jscript'); ?>/jquery.min.js"%3E%3C/script%3E'));</script>
*/
?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>

    <?php
    /**
     * load all site-wide jscript_*.js files from includes/templates/YOURTEMPLATE/jscript, alphabetically
     */
    $directory_array = $template->get_template_part($template->get_template_dir('.js', DIR_WS_TEMPLATE, $current_page_base, 'jscript'), '/^jscript_/', '.js');
    foreach ($directory_array as $key => $value) {
      echo '<script src="' . $template->get_template_dir('.js', DIR_WS_TEMPLATE, $current_page_base, 'jscript') . '/' . $value . '"></script>' . "\n";
    }

    /**
     * load all page-specific jscript_*.js files from includes/modules/pages/PAGENAME, alphabetically
     */
    $directory_array = $template->get_template_part($page_directory, '/^jscript_/', '.js');
    foreach ($directory_array as $key => $value) {
      echo '<script src="' . $page_directory . '/' . $value . '"></script>' . "\n";
    }

    /**
     * load all site-wide jscript_*.php files from includes/templates/YOURTEMPLATE/jscript, alphabetically
     */
    $directory_array = $template->get_template_part($template->get_template_dir('.php', DIR_WS_TEMPLATE, $current_page_base, 'jscript'), '/^jscript_/', '.php');
    foreach ($directory_array as $key => $value) {
      /**
       * include content from all site-wide jscript_*.php files from includes/templates/YOURTEMPLATE/jscript, alphabetically.
       * These .PHP files can be manipulated by PHP when they're called, and are copied in-full to the browser page
       */
      require($template->get_template_dir('.php', DIR_WS_TEMPLATE, $current_page_base, 'jscript') . '/' . $value);
      echo "\n";
    }
    /**
     * include content from all page-specific jscript_*.php files from includes/modules/pages/PAGENAME, alphabetically.
     */
    $directory_array = $template->get_template_part($page_directory, '/^jscript_/');
    foreach ($directory_array as $key => $value) {
      /**
       * include content from all page-specific jscript_*.php files from includes/modules/pages/PAGENAME, alphabetically.
       * These .PHP files can be manipulated by PHP when they're called, and are copied in-full to the browser page
       */
      require($page_directory . '/' . $value);
      echo "\n";
    }

// DEBUG: echo '<!-- I SEE cat: ' . $current_category_id . ' || vs cpath: ' . $cPath . ' || page: ' . $current_page . ' || template: ' . $current_template . ' || main = ' . ($this_is_home_page ? 'YES' : 'NO') . ' -->';
    ?>

  <?php
  $zco_notifier->notify('NOTIFY_HTML_HEAD_END', $current_page_base);
  ?>
  </head>

<?php // NOTE: Blank line following is intended:   ?>
