<?php

    ini_set('display_errors', 0);

    $img_bg = 'img-bg.jpg';
    $img_cache = 'img-cache.jpg';
    $img_font = './freesansbold.ttf';
    $url = 'https://www.votolivre.org';

    function get_page ($url) {
        $data = @file_get_contents($url);
        return $data;
    }

    function parse_count ($data) {
        echo $data;
        preg_match ('/<h1>([0-9]*)<\/h1>/', $data, $m);
        return $m[1];
    }

    function generate_cache_image ($count) {
        global $img_bg, $img_cache, $img_font;
        $img = imagecreatefromjpeg ($img_bg);
        $white = imagecolorallocate ($img, 255, 255, 255);
        imagettftext($img, 38, 0, 50, 245, $white, $img_font, $count);
        imagejpeg ($img, $img_cache, 100);
        imagedestroy ($img);
    }

    function output_image($img_cache) {
        $img = imagecreatefromjpeg ($img_cache);
        header ('Content-Type: image/jpeg');
        imagejpeg ($img, False, 100);
        imagedestroy ($img);
        exit(0);
    }

    if (time() - filemtime ($img_cache) > 3600) {
        $data = get_page ($url);
        $count = parse_count ($data);
        generate_cache_image ($count);
    }

    output_image($img_cache);

?>
