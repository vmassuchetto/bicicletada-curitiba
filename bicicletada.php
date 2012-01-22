<?php

    /*
    * Olá.
    *
    * Este não é um tema de verdade de acordo com os padrões do WordPress,
    * trata-se somente de um arquivo com inserções para o tema padrão do
    * BuddyPress, e que foi colocado aqui porque é mais provável que futuros
    * desenvolvedores procurem nesta pasta primeiro.
    *
    * Qualquer dúvida, entre em contato: viniciusmassuchetto@gmail.com
    */

    // Caminho para esta pasta

    $bc_dir = WP_CONTENT_DIR . '/themes/bicicletada/';
    $bc_url = get_bloginfo('url') . '/wp-content/themes/bicicletada/';


    // Coloca o CSS personalizado em todas as páginas

    add_action('wp_head', 'css_custom');
    function css_custom() {
        global $bc_url;
        ?>
        <link rel="shortcut icon" href="<?php echo $bc_url; ?>/img/favicon.ico" type="image/x-icon">
        <link rel="stylesheet" href="<?php echo $bc_url; ?>/bicicletada.css" />
        <?php
    }

    // Transforma a página inicial dos fórums na listagem de tópicos

    add_action('bp_init', 'forum_redirect');
    function forum_redirect() {
        global $bp;

        $path = esc_url($_SERVER['REQUEST_URI']);
        $path = apply_filters('bp_uri', $path);

        if (bp_is_group_home() && strpos($path, $bp->bp_options_nav['groups']['home']['slug'] ) === false)
            bp_core_redirect($bp->bp_options_nav[$bp->current_item]['forum']['link']);
    }

    // Transforma o primeiro item dos grupos em listagem de tópicos do fórum

    add_action('bp_init', 'forum_menu');
    function forum_menu() {
        global $bp;

        if ($bp->current_component == $bp->groups->slug) {
            unset($bp->bp_options_nav[$bp->current_item]['home']);
            $bp->bp_options_nav[$bp->current_item]['forum']['name'] = 'Tópicos';
            $bp->bp_options_nav[$bp->current_item]['forum']['position'] = 1;
            $bp->bp_options_nav[$bp->current_item]['members']['position'] = 2;
            $bp->bp_options_nav[$bp->current_item]['admin']['position'] = 100;
        }
    }

?>
