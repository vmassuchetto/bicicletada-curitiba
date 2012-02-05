<?php

// Favicon

add_action('wp_head', 'custom_css');
function custom_css() {
    global $bc_url;
    ?>
    <link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon.ico" type="image/x-icon">
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


// Seção de código no final da página

add_action('wp_footer', 'custom_footer');
function custom_footer() {
    ?>
    <script type="text/javascript">

        jQuery(document).ready(function(){

            // Conserta o link para pedaladas na Admin Bar

            var html = jQuery('#wp-admin-bar').html();
            html = html.replace("members", "membros");
            jQuery('#wp-admin-bar').html(html);

            // Expande lista de links

            var links_obj = jQuery('.widget_links ul');
            links_obj.hide();
            links_obj.before('<a href="javascript:void(0);" class="show-links">Ver Links</a>');
            jQuery('.show-links').click(function(){
                jQuery(this).hide();
                jQuery(links_obj).slideDown(500);
            });

            // Botão de compartilhamento do Google Plus

            window.___gcfg = {lang: 'pt-BR'};
            (function() {
                var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                po.src = 'https://apis.google.com/js/plusone.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
            })();

        });
    </script>
    <?php
}

// Insere créditos no rodapé

add_action('bp_footer', 'custom_bp_footer');
function custom_bp_footer() {
    ?>
    <div class="credits"><p>Tema da <a target="_blank" href="http://github.com/viniciusmassuchetto/bicicletada-curitiba">Bicicletada Curitiba</a></p></div>
    <?php
}

// Mostra os botões de compartilhamento nos posts, abaixo da foto do perfil do autor

function share_box() {

    global $post;

    ?>
    <div class="share-box">

        <div class="box identica">
            <iframe height="61" width="61" scrolling="no" frameborder="0" src="http://www.tildehash.com/identishare.php?noscript&style2&title=<?php echo $post->post_title . ' ' . get_permalink($post->ID); ?>" border="0" marginheight="0" marginwidth="0" allowtransparency="true" style="width:55px; height:70px;"></iframe>
        </div>

        <div class="box facebook">
            <iframe src="http://www.facebook.com/plugins/like.php?href=<?php echo get_permalink($post->ID); ?>&amp;layout=box_count&amp;show_faces=false&amp;width=50&amp;action=curtir&amp;font=arial&amp;colorscheme=light&amp;height=60&amp;send=true" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:55px; height:65px;" allowTransparency="true"></iframe>
        </div>

        <div class="box twitter">
            <iframe frameborder="0" scrolling="no" allowtransparency="true" src="http://platform.twitter.com/widgets/tweet_button.html?count=vertical&amp;enableNewSizing=false&amp;id=twitter-widget-0&amp;lang=en&amp;original_referer=<?php echo get_permalink($post->ID); ?>&amp;size=m&amp;text=<?php echo $post->post_title; ?>&amp;url=<?php echo get_permalink($post->ID); ?>" class="twitter-share-button twitter-count-vertical" style="width: 55px; height: 62px;" title="Twitter Tweet Button"></iframe>
        </div>

        <div class="box googleplus">
            <g:plusone size="tall" href="<?php echo get_permalink($post->ID); ?>"></g:plusone>
        </div>

    </div>
    <?php

}

// Insere faixa do Twitter logo abaixo do cabeçalho

add_action('bp_after_header', 'twitter_stripe');
function twitter_stripe() {
    ?>
    <div class="twitter-stripe">
        <a href="javascript:void(0)" title="Carregando Último Tweet" class="loading">Carregando Último Tweet</a>
    </div>
    <script type="text/javascript">
        jQuery.post( location.href, {
            'action': 'twitter-stripe-ajaxload'
        }, function(data) {
            jQuery('.twitter-stripe').html(data);
        });
    </script>
    <?php
}

// Carrega o último Tweet através de uma requisição AJAX

add_action('init', 'twitter_stripe_ajaxload');
function twitter_stripe_ajaxload() {

    global $_POST;
    if ($_POST['action'] != 'twitter-stripe-ajaxload')
        return;

    require_once(WPINC . '/class-simplepie.php');
    $cache_dir = WP_CONTENT_DIR . '/cache/twitter';
    if (!is_dir($cache_dir))
        return;

    $feed = new SimplePie();
    $feed->set_feed_url('http://twitter.com/statuses/user_timeline/bicicletadactba.rss');
    $feed->set_cache_location($cache_dir);
    $feed->init();
    $feed->handle_content_type();
    $item = $feed->get_item();

    ?>
    <a href="<?php echo $item->get_link(); ?>" title="<?php echo $item->get_title(); ?>" target="_blank"><?php echo str_replace('bicicletadactba: ', '', $item->get_title()); ?></a>
    <?php

    exit(1);
}

// Faixa abaixo do título dos fóruns

add_action('bp_before_directory_groups_content', 'forum_stripe');
function forum_stripe() {
    ?>
    <div class="forum-stripe">
        <p>Atenção! Leia as <a href="<?php echo get_bloginfo('url'); ?>/forum/regras-do-forum/">regras do fórum</a> antes de postar.</p>
    </div>
    <?php
}

// Mostra as pedaladas nos tópicos do fórum abaixo das fotos dos usuários

add_action('bp_get_the_topic_post_poster_avatar', 'topic_poster_avatar');
function topic_poster_avatar($avatar) {
    preg_match_all('/user-([0-9]+)-avatar/', $avatar, $m);
    $id = $m[1][0];
    if ($count = get_user_meta($id, 'cpoints', 1))
        return $avatar . '<div class="cp-avatar-counter"><span class="number">' . $count . '</span> pedaladas</div>';
    return $avatar;
}

// Conserta alguns erros na importação do WordPress.com

function fix_content() {

    global $wpdb;

    $sql = "
        SELECT ID
        FROM $wpdb->posts
    ";
    foreach ($wpdb->get_results($sql) as $p) {

        $post = get_post($i = $p->ID);

        // Tira a marca [youtube=<url>] e transforma somente em <url>
        $post->post_content = preg_replace('/\[youtube=(.+)\]/', '\1', $post->post_content);

        // Conserta links
        $post->post_content = str_replace('http://bicicletadacuritiba.wordpress.com', 'http://bicicletadacuritiba.org', $post->post_content);

        wp_update_post($post);
    }

}
//fix_content();
//exit();

// Mostra informações para desenvolvedores, versões de plugins e tudo mais

add_action('wp', 'devinfo');
function devinfo() {

    if (!isset($_GET['devinfo']))
        return;

    require_once(ABSPATH.'/wp-admin/admin-functions.php');

    echo '<pre>';
    echo '*   [WordPress](http://wordpress.org)</a>: '. get_bloginfo('version') . "\n";

    foreach (get_plugins() as $p)
        echo '*   [' . $p['Name'] . '](' . $p['PluginURI'] . '): ' . $p['Version'] . "\n";

    echo '</pre>';

    exit();

}

?>
