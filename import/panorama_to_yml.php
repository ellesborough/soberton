<?php

$output_path = "../_panorama/";
$output_ext = '.md';

$ps_prefix = 'S-';

$all_panoramas = json_decode(file_get_contents('Panorama/all_panorama.json'), TRUE);
echo count($all_panoramas) . " panoramas\n";


foreach($all_panoramas as $s) {

    $filename = $output_path . $ps_prefix . $s['nid'] . $output_ext;

    $fp = fopen($filename, "w");

    echo $filename . "\n";

    fwrite($fp, "---\n");
    fwrite($fp, "nid: " . $ps_prefix . $s['nid'] . "\n");
    fwrite($fp, "title: " . $s['title'] . "\n");
    fwrite($fp, "created: " . date('Y-m-d H:i:s', $s['created']) . "\n");
    fwrite($fp, "changed: " . date('Y-m-d H:i:s', $s['changed']) . "\n");

    if(!empty($s['field_grave_external_image']['und'])) {

        $path = parse_url($s['field_grave_external_image']['und'][0]['url'], PHP_URL_PATH);

        fwrite($fp, "image_filename: " . pathinfo($path, PATHINFO_BASENAME) . "\n");
    }

    if(!empty($s['field_location']['und'])) {
        fwrite($fp, "location: " . "{ latitude: " . $s['field_location']['und'][0]['lat'] .", longitude: " . $s['field_location']['und'][0]['lon'] . "}\n");
    }

    fwrite($fp, "---\n");


    fclose($fp);
}


