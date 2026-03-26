<?php

$output_path = "../_markers/";
$output_ext = '.md';

$psm_prefix = 'M-';
$ps_prefix = 'S-';
$grave_prefix = 'G-';

$all_markers = json_decode(file_get_contents('PanoramaMarkers/all_markers.json'), TRUE);
echo count($all_markers) . " markers\n";


foreach($all_markers as $m) {

    $filename = $output_path . $psm_prefix . $m['nid'] . $output_ext;

    $fp = fopen($filename, "w");

    echo $filename . "\n";

    fwrite($fp, "---\n");
    fwrite($fp, "nid: " . $psm_prefix . $m['nid'] . "\n");
    fwrite($fp, "title: " . $m['title'] . "\n");
    fwrite($fp, "created: " . date('Y-m-d H:i:s') . "\n");
    fwrite($fp, "changed: " . date('Y-m-d H:i:s') . "\n");

    if(!empty($m['field_photo_sphere']['und'])) {
        fwrite($fp, "photo_sphere: " . $ps_prefix . $m['field_photo_sphere']['und'][0]['target_id'] . "\n");
    }

    if(!empty($m['field_grave']['und'])) {
        fwrite($fp, "grave: " . $grave_prefix . $m['field_grave']['und'][0]['target_id'] . "\n");
    }

    if(!empty($m['field_geo']['und'])) {
        fwrite($fp, "location: " . "{ latitude: " . $m['field_geo']['und'][0]['lat'] .", longitude: " . $m['field_geo']['und'][0]['lon'] . "}\n");
    }

    fwrite($fp, "---\n");


    fclose($fp);
}


