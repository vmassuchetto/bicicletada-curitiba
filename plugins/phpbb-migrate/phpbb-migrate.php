<?php

/*
 * Plugin Name: phpBB to BuddyPress Importer
 * Plugin URI: http://vinicius.soylocoporti.org/phpbb-to-buddypress-importer
 * Description: Import buddypress users, activity and forums do BuddyPress
 * Author: viniciusmassuchetto
 * Author URI: http://vinicius.soylocoporti.org.br/
 * Version: 0.1
 * License: GPLv2
 */

global $wpdb;

// phpBB server

$src = new $wpdb('root', 'root', 'bicicletada_forum', 'localhost');

$src->show_errors();
$wpdb->show_errors();

// Config

// phpBB admin IDs

$admins = array(14286);
$default_user = 7157;

// Avatars

$avatar_src_dir = '/home/vinicius/temp/avatars/upload';
$avatar_dst_dir = '/home/vinicius/web/bicicletada/wp-content/uploads/avatars';

// phpBB forums to merge
// e.g: 3 => 4 will put all posts from forum 3 in forum 4

$merge_forums = array(
    'default' => 3,
    // 3 = Cicloativismo
    8 => 3,
    9 => 3,
    12 => 3,
    13 => 3,
    15 => 3,
    23 => 3,
    24 => 3,
    29 => 3,
    30 => 3,
    32 => 3,
    33 => 3,
    34 => 3,
    35 => 3,
    36 => 3,
    37 => 3,
    // 6 = Classificados
    10 => 6,
    // 18 = Passeios e Viagens
    16 => 18,
    38 => 18,
    39 => 18,
    // 14 = Oficina da Bike
    19 => 14,
    20 => 14,
    21 => 14,
    22 => 14,
    25 => 14,
    // 27 = Campeonatos e Competições
    28 => 27,
    37 => 27
    // 40 = Ocorrências
);

// End of config

// Populate users

function phpbbmig_populate_users() {

    echo "<br/>importing users ... <br/>";

    global $src, $wpdb, $admins;

    $sql = array(
        "TRUNCATE TABLE {$wpdb->dbname} . {$wpdb->users}",
        "TRUNCATE TABLE {$wpdb->dbname} . {$wpdb->usermeta}"
    );
    foreach ($sql as $s) $wpdb->query($s);

    $sql = "
        SELECT
            user_id,
            username_clean,
            user_password,
            user_email,
            user_website,
            FROM_UNIXTIME (user_regdate) AS user_registered,
            username
        FROM {$src->dbname}.phpbb_users
        WHERE 1
            AND user_password != ''
            AND user_type != 1;
    ";

    foreach ($src->get_results($sql) as $s) {

        echo "importing {$s->username_clean} ... <br/>";

        $wpdb->insert($wpdb->users, array(
            'user_login' => str_replace('-', '', sanitize_title($s->username_clean)),
            'user_pass' => md5(rand()),
            'user_nicename' => $s->username,
            'user_email' => $s->user_email,
            'user_registered' => 1,
            'user_status' => 0,
            'display_name' => $s->username,
            'user_url' => $s->user_website,
            'user_registered' => $s->user_registered
        ));
        $user_id = $wpdb->insert_id;
        update_user_meta($user_id, '_old_id', $s->user_id);

        if (in_array($s->user_id, $admins)) {
            update_user_meta($user_id, 'wp_user_level', 10);
            update_user_meta($user_id, 'wp_capabilities', array('administrator' => 1));
        }
    }
}

// Populate forums

function phpbbmig_populate_forums() {

    echo "<br/>importing forums ... <br/>";

    global $src, $wpdb, $merge_forums, $admins;

    // Clean everything as new installation

    $sql = array(
        "TRUNCATE TABLE {$wpdb->dbname}.{$wpdb->prefix}bb_forums",
        "TRUNCATE TABLE {$wpdb->dbname}.{$wpdb->prefix}bp_groups",
        "TRUNCATE TABLE {$wpdb->dbname}.{$wpdb->prefix}bp_groups_groupmeta",
        "TRUNCATE TABLE {$wpdb->dbname}.{$wpdb->prefix}bp_groups_members",
        "INSERT INTO {$wpdb->dbname}.{$wpdb->prefix}bb_forums (
            forum_id, forum_name, forum_slug, forum_desc, forum_parent, forum_order, topics, posts
            ) VALUES ( NULL , 'Default Forum', 'default-forum', '', '0', '1', '0', '0')"
    );
    foreach ($sql as $s) $wpdb->query($s);

    // Get the default admin

    $admin = $wpdb->get_var("
        SELECT user_id
        FROM {$wpdb->usermeta}
        WHERE 1
            AND meta_key = '_old_id'
            AND meta_value = $admins[0]
        LIMIT 1
    ");

    $sql = "
        SELECT
            forum_id,
            forum_name,
            forum_desc
        FROM {$src->dbname}.phpbb_forums;
    ";
    foreach ($src->get_results($sql) as $s) {

        if (array_key_exists($s->forum_id, $merge_forums))
            continue;

        echo "importing {$s->forum_name} ... <br/>";

        $forum_slug = sanitize_title($s->forum_name);
        $wpdb->insert($wpdb->prefix . 'bb_forums', array(
            'forum_name' => $s->forum_name,
            'forum_slug' => $forum_slug,
            'forum_desc' => $s->forum_desc,
            'forum_parent' => 1,
            'forum_order' => 1
        ));
        $forum_id = $wpdb->insert_id;

        // Create group

        $wpdb->insert($wpdb->prefix . 'bp_groups', array(
            'creator_id' => $admin,
            'name' => $s->forum_name,
            'slug' => $forum_slug,
            'description' => $s->forum_desc,
            'status' => 'public',
            'enable_forum' => 1,
            'date_created' => '2011-01-01 00:00:00'
        ));
        $group_id = $wpdb->insert_id;

        // Associate the forum with the group

        $wpdb->insert($wpdb->prefix . 'bp_groups_groupmeta', array(
            'group_id' => $group_id,
            'meta_key' => 'forum_id',
            'meta_value' => $forum_id
        ));
         $wpdb->insert($wpdb->prefix . 'bp_groups_groupmeta', array(
            'group_id' => $group_id,
            'meta_key' => 'last_activity',
            'meta_value' => date('Y-m-d H:i:s')
        ));
         $wpdb->insert($wpdb->prefix . 'bp_groups_groupmeta', array(
            'group_id' => $group_id,
            'meta_key' => 'invite_status',
            'meta_value' => 'members'
        ));
        $wpdb->insert($wpdb->prefix . 'bp_groups_groupmeta', array(
            'group_id' => $group_id,
            'meta_key' => 'total_member_count',
            'meta_value' => 1
        ));
        $wpdb->insert($wpdb->prefix . 'bp_groups_groupmeta', array(
            'group_id' => $group_id,
            'meta_key' => '_old_forum_id',
            'meta_value' => $s->forum_id
        ));

        // Add the admin in the group and update the user role

        $wpdb->insert($wpdb->prefix . 'bp_groups_members', array(
            'group_id' => $group_id,
            'user_id' => $admin,
            'is_admin' => 1,
            'is_mod' => 0,
            'user_title' => 'Administrador',
            'date_modified' => date('Y-m-d H:i:s'),
            'is_confirmed' => 1
        ));

        // Add users to this group

        $sql = "
            SELECT DISTINCT poster_id
            FROM {$src->dbname}.phpbb_posts
            WHERE forum_id = $s->forum_id
        ";
        foreach ($wpdb->get_results($sql) as $u) {

            if ($u->poster_id == $admins[0])
                continue;

            $wpdb->insert($wpdb->prefix. 'bp_groups_members', array(
                'group_id' => $group_id,
                'user_id' => $u->poster_id,
                'is_admin' => 0,
                'is_mod' => 0,
                'user_title' => 'Membro',
                'date_modified' => date('Y-m-d H:i:s'),
                'is_confirmed' => 1
            ));
        }

    }
}

// Populate topics

function phpbbmig_populate_topics() {

    echo "<br/>importing topics ... <br/>";

    global $wpdb, $src, $merge_forums, $default_user;

    $sql = array(
        "TRUNCATE TABLE {$wpdb->dbname}.{$wpdb->prefix}bb_topics",
        "TRUNCATE TABLE {$wpdb->dbname}.{$wpdb->prefix}bb_posts"
    );
    foreach ($sql as $s) $wpdb->query($s);

    $default_new_user = $wpdb->get_var("
        SELECT user_id
        FROM $wpdb->usermeta
        WHERE 1
            AND meta_key = '_old_id'
            AND meta_value = $default_user
    ");

    $sql = "
        SELECT
            topic_id,
            topic_title,
            topic_poster,
            topic_last_poster_id,
            topic_type,
            FROM_UNIXTIME (topic_time) AS topic_start_time,
            FROM_UNIXTIME (topic_last_view_time) AS topic_time,
            forum_id
        FROM {$src->dbname}.phpbb_topics t;
    ";
    foreach ($src->get_results($sql) as $t) {

        echo "importing topic $t->topic_id ... <br/>";

        // Get topic poster

        $topic_poster_id = $wpdb->get_var("
            SELECT user_id
            FROM {$wpdb->usermeta}
            WHERE meta_value = $t->topic_poster
                AND meta_key = '_old_id'
        ");
        $topic_poster_user = get_userdata($topic_poster_id);
        if (!$topic_poster_user)
            $topic_poster_user = get_userdata($default_new_user);

        // Get topic last poster

        $topic_last_poster_id = $wpdb->get_var("
            SELECT user_id
            FROM {$wpdb->usermeta}
            WHERE meta_value = $t->topic_last_poster_id
                AND meta_key = '_old_id'
        ");
        $topic_last_poster_user = get_userdata($topic_last_poster_id);
        if (!$topic_last_poster_user)
            $topic_last_poster_user = get_userdata($default_new_user);

        // Get topic forum

        if (array_key_exists($t->forum_id, $merge_forums))
            $forum_old_id = $merge_forums[$t->forum_id];
        else
            $forum_old_id = $t->forum_id;

        $group_id = $wpdb->get_var("
            SELECT group_id
            FROM {$wpdb->prefix}bp_groups_groupmeta
            WHERE meta_key = '_old_forum_id'
                AND meta_value = $forum_old_id
        ");

        if (!$group_id)
            $forum_id = $merge_forums['default'];
        else
            $forum_id = $wpdb->get_var("
                SELECT meta_value
                FROM {$wpdb->prefix}bp_groups_groupmeta
                WHERE group_id = $group_id
                    AND meta_key = 'forum_id'
            ");

        // Is sticky?

        if ($t->topic_type == 1)
            $topic_sticky = 1;
        else
            $topic_sticky = 0;

        // Insert topic

        $wpdb->insert($wpdb->prefix . 'bb_topics', array(
            'topic_title' => $t->topic_title,
            'topic_slug' => sanitize_title($t->topic_title),
            'topic_poster' => $topic_poster_user->ID,
            'topic_poster_name' => $topic_poster_user->display_name,
            'topic_last_poster' => $topic_last_poster_user->ID,
            'topic_last_poster_name' => $topic_last_poster_user->display_name,
            'topic_start_time' => $t->topic_start_time,
            'topic_time' => $t->topic_time,
            'forum_id' => $forum_id,
            'topic_status' => 0,
            'topic_open' => 1,
            'topic_last_post_id' => 1,
            'topic_sticky' => 0, //$topic_sticky,
            'topic_posts' => 0,
            'tag_count' => 0
        ));
        $topic_id = $wpdb->insert_id;

        // Get posts for the topic

        $sql = "
            SELECT
                post_id,
                forum_id,
                poster_id,
                post_text,
                FROM_UNIXTIME(post_time) AS post_time,
                poster_ip
            FROM {$src->dbname}.phpbb_posts p
            WHERE p.topic_id = {$t->topic_id}
        ";

        foreach ($src->get_results($sql) as $p) {

            echo "import post $p->post_id <br/>";

            // Get the poster

            $poster_id = $wpdb->get_var("
                SELECT user_id
                FROM {$wpdb->usermeta}
                WHERE meta_value = $p->poster_id
                    AND meta_key = '_old_id'
            ");
            $poster_user = get_userdata($poster_id);
            if (!$poster_user)
                $poster_user = get_userdata($default_new_user);

            // Format text

            $post_text = $p->post_text;

            $post_text = preg_replace('/\[.+\]/', '', $post_text);
            $post_text = strip_tags($post_text);

            // Inser post

            $wpdb->insert($wpdb->prefix . 'bb_posts', array(
                'forum_id' => $forum_id,
                'topic_id' => $topic_id,
                'poster_id' => $poster_user->ID,
                'post_text' => $post_text,
                'post_time' => $p->post_time,
                'poster_ip' => $p->poster_ip,
                'post_status' => 0,
                'post_position' => 1
            ));
        }
    }
}

function phpbbmig_move_avatars() {

    global $src, $wpdb, $avatar_src_dir, $avatar_dst_dir;

    echo "<br/>moving avatars ...<br/>";

    $h = opendir($avatar_src_dir);
    while (false !== ($f = readdir($h))) {

        $id = intval(preg_replace('/.+_([0-9]+)\..*/', '\1', $f));
        if (!$id) continue;

        $id = $wpdb->get_var("
            SELECT user_id
            FROM {$wpdb->usermeta}
            WHERE 1
                AND meta_key = '_old_id'
                AND meta_value = $id
            LIMIT 1
        ");

        echo "moving avatar $id ... <br/>";

        system("mkdir $avatar_dst_dir/$id/");
        system("cp $avatar_src_dir/$f $avatar_dst_dir/$id/$id-bpthumb.jpg");
        system("cp $avatar_src_dir/$f $avatar_dst_dir/$id/$id-bpfull.jpg");

    }

}

function phpbbmig_fix_counters() {

    global $wpdb;

    echo "fixing counters ...<br/>";

    // Forums count

    $sql = "
        SELECT forum_id
        FROM {$wpdb->prefix}bb_forums
    ";
    foreach ($wpdb->get_results($sql) as $f) {

        $topic_count = $wpdb->get_var("
            SELECT COUNT(topic_id)
            FROM {$wpdb->prefix}bb_topics
            WHERE forum_id = $f->forum_id
        ");
        $post_count = $wpdb->get_var("
            SELECT COUNT(post_id)
            FROM {$wpdb->prefix}bb_posts
            WHERE forum_id = $f->forum_id
        ");
        $wpdb->query("
            UPDATE {$wpdb->prefix}bb_forums
            SET
                topics = '$topic_count',
                posts = '$post_count'
            WHERE forum_id = '$f->forum_id'
        ");

    }

    // Topics count

    $sql = "
        SELECT topic_id
        FROM {$wpdb->prefix}bb_topics
    ";
    foreach ($wpdb->get_results($sql) as $t) {
        $count = $wpdb->get_var("
            SELECT COUNT(post_id)
            FROM {$wpdb->prefix}bb_posts
            WHERE topic_id = $t->topic_id
        ");
        $wpdb->query("
            UPDATE {$wpdb->dbname}.{$wpdb->prefix}bb_topics
            SET topic_posts = $count
            WHERE topic_id = $t->topic_id
        ");
    }

}

function phpbbmig_fill_profiles() {

    global $wpdb;

    echo "filling user profiles ...<br/>";

    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}bp_xprofile_data");
    $wpdb->query("UPDATE {$wpdb->users} SET user_nicename = user_login");

    $sql = "
        SELECT ID, display_name
        FROM {$wpdb->users}
    ";
    foreach ($wpdb->get_results($sql) as $u) {
        $wpdb->insert($wpdb->prefix . 'bp_xprofile_data', array(
            'field_id' => 1,
            'user_id' => $u->ID,
            'value' => $u->display_name
        ));
    }

}

// Uncomment to start importing

//add_action('wp', 'phpbbmig_run');
function phpbbmig_run() {
    //phpbbmig_populate_users();
    //phpbbmig_populate_forums();
    //phpbbmig_populate_topics();
    //phpbbmig_move_avatars();
    //phpbbmig_fix_counters();
    phpbbmig_fill_profiles();
    echo "done!";
    exit();
}

?>
