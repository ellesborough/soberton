<?php

$output_path = "../_districts/";
$output_ext = '.md';

$district_prefix = 'D-';

$d7_taxonomy_term_data = json_decode(file_get_contents('d7_taxonomy_term_data.json'), TRUE);

$registration_districts = array_filter($d7_taxonomy_term_data[2]['data'], function ($v) { return $v['vid'] == "6";  });

echo count($registration_districts) . " districts\n";

$d7_field_data_field_district_centre = json_decode(file_get_contents('d7_field_data_field_district_centre.json'), TRUE);

$district_centres = array_reduce($d7_field_data_field_district_centre[2]['data'], function ($c, $v) {
    $c[$v['entity_id']] = "{ latitude: " . floatval($v['field_district_centre_lat']) . ", longitude: " . floatval($v['field_district_centre_lon']) . "}";
    return $c;
}, []);

echo count($district_centres) . " districts with centre location\n";

foreach($registration_districts as $rd) {

    $filename = $output_path . $district_prefix . $rd['tid'] . $output_ext;

    $fp = fopen($filename, "w");

    echo $filename . "\n";

    fwrite($fp, "---\n");
    fwrite($fp, "nid: " . $district_prefix . $rd['tid'] . "\n");
    fwrite($fp, "title: " . $rd['name'] . "\n");
    fwrite($fp, "created: " . date('Y-m-d H:i:s') . "\n");
    fwrite($fp, "changed: " . date('Y-m-d H:i:s') . "\n");

    if(array_key_exists($rd['tid'], $district_centres)) {
        fwrite($fp, "centre: " . $district_centres[$rd['tid']]. "\n");
    }

    fwrite($fp, "---\n");

    if(!empty($rd['description'])) {
        fwrite($fp, $rd['description'] . "\n");
    }

    fclose($fp);
}


