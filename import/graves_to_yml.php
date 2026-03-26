<?php
require 'vendor/autoload.php';
use League\HTMLToMarkdown\HtmlConverter;
$html_md_converter = new HtmlConverter();

$all_graves = json_decode(file_get_contents('Graves/all_graves.json'), TRUE);
echo count($all_graves) . " graves\n";

$output_path = "../_graves/";
$output_ext = '.md';

$nid_prefix = 'G-';
$psm_prefix = 'M-';
$person_prefix = 'P-';

$grave_types = [
    "1"    => "Ledger / Flat Stone",
    "2"    => "Headstone",
    "3"    => "Wall Monument",
    "4"    => "Obilisk",
    "5"    => "Free Standing Cross",
    "6"    => "Chest Tomb",
    "7"    => "Sculpture",
    "11"   => "Vase",
    "12"   => "Vault",
    "3163" => "War Grave",
    "3170" => "Cremation",
    "3171" => "Curb / Surround",
];

foreach($all_graves as $g) {

    $filename = $output_path . $nid_prefix . $g['nid'] . $output_ext;

    $fp = fopen($filename, "w");

    echo $filename . "\n";

    fwrite($fp, "---\n");
    fwrite($fp, "nid: " . $nid_prefix . $g['nid'] . "\n");
    fwrite($fp, "title: " . $g['title'] . "\n");
    fwrite($fp, "created: " . date('Y-m-d H:i:s', $g['created']) . "\n");
    fwrite($fp, "changed: " . date('Y-m-d H:i:s', $g['changed']) . "\n");

    // Multiline
    if(!empty($g['body']['und'])) {
        fwrite($fp, "inscription: " . json_encode($g['body']['und'][0]['safe_value']) . "\n");  // use JSON to maintain the \n in the string
    }

    if(!empty($g['field_graveyard_section']['und'])) {
        fwrite($fp, "graveyard_section: \"" . $g['field_graveyard_section']['und'][0]['safe_value'] . "\"\n"); // quoted string, as could be just a number
    }

    if(!empty($g['field_grave_plan_reference']['und'])) {
        fwrite($fp, "grave_plan_reference: \"" . $g['field_grave_plan_reference']['und'][0]['safe_value'] . "\"\n");  // quoted string, as could be just a number
    }

    if(!empty($g['field_grave_century']['und'])) {
        fwrite($fp, "grave_century: " . $g['field_grave_century']['und'][0]['value'] . "\n");  // should be just a integer number
    }

    if(!empty($g['field_grid_square']['und'])) {
        fwrite($fp, "grid_square: \"" . $g['field_grid_square']['und'][0]['safe_value'] . "\"\n"); // quoted string, as could be just a number
    }

    if(!empty($g['field_location']['und'])) {
        fwrite($fp, "location: " . "{ latitude: " . $g['field_location']['und'][0]['lat'] .", longitude: " . $g['field_location']['und'][0]['lon'] . "}\n");
    }

    if(!empty($g['field_grave_external_image']['und'])) {
        $images = $g['field_grave_external_image']['und'];
        fwrite($fp, "images: \n");
        foreach($images as $image) {
            fwrite($fp, "    - " . $image['url'] . "\n");
        }
    }

    if(!empty($g['field_grave_type']['und'])) {
        if(array_key_exists($g['field_grave_type']['und'][0]['tid'], $grave_types)) {
            fwrite($fp, "grave_type: " . $grave_types[$g['field_grave_type']['und'][0]['tid']] . "\n");
        }
        else {
            echo("Unknown grave type: " . $g['field_grave_type']['und'][0]['tid'] . "for nid: " . $g['nid'] . "\n");
        }
    }

    if(!empty($g['field_photo_sphere_marker']['und'])) {
        $psms = $g['field_photo_sphere_marker']['und'];
        fwrite($fp, "photo_sphere_marker: \n");
        foreach($psms as $psm) {
            fwrite($fp, "    - " . $psm_prefix . $psm['target_id'] . "\n");
        }
    }

    if(!empty($g['field_people']['und'])) {
        $people = $g['field_people']['und'];
        fwrite($fp, "people: \n");
        foreach($people as $person) {
            fwrite($fp, "    - " . $person_prefix . $person['target_id'] . "\n");
        }
    }

    fwrite($fp, "---");

    if(!empty($g['field_grave_notes']['und'])) {
        $unescaped = nl2br(html_entity_decode($g['field_grave_notes']['und'][0]['safe_value']));
        fwrite($fp, "\n" . $html_md_converter->convert($unescaped)  . "\n");
    }

    fclose($fp);
}


