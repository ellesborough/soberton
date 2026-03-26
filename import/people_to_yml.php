<?php
require 'vendor/autoload.php';
use League\HTMLToMarkdown\HtmlConverter;
$html_md_converter = new HtmlConverter();

$output_path = "../_people/";
$output_ext = '.md';

$nid_prefix = 'P-';
$district_prefix = 'D-';
$grave_prefix = 'G-';

$all_people = json_decode(file_get_contents('People/all_people.json'), TRUE);
echo count($all_people) . " people\n";

$d7_field_data_field_registration_year = json_decode(file_get_contents('d7_field_data_field_registration_year.json'), TRUE);
$registration_years = array_reduce($d7_field_data_field_registration_year[2]['data'], function ($c, $v) {
    $c[$v['entity_id']] = $v['field_registration_year_value'];
    return $c;
}, []);

$d7_field_data_field_registration_quarter = json_decode(file_get_contents('d7_field_data_field_registration_quarter.json'), TRUE);
$registration_quarters = array_reduce($d7_field_data_field_registration_quarter[2]['data'], function ($c, $v) {

    $qtr_str = '';
    switch ($v['field_registration_quarter_target_id']) {
        case '3152': $qtr_str = 'January-March';    break;
        case '3153': $qtr_str = 'April-June';       break;
        case '3154': $qtr_str = 'July-September';   break;
        case '3155': $qtr_str = 'October-December'; break;
        default:     $qtr_str = 'Unknown';          break;
    }
    $c[$v['entity_id']] = $qtr_str;
    return $c;
}, []);

$d7_field_data_field_registration_districts = json_decode(file_get_contents('d7_field_data_field_registration_districts.json'), TRUE);
$registration_districts = array_reduce($d7_field_data_field_registration_districts[2]['data'], function ($c, $v) {
    $c[$v['entity_id']] = $v['field_registration_districts_target_id'];
    return $c;
}, []);

function registration_record_str($record_id) {
    global $registration_years;
    global $registration_quarters;
    global $registration_districts;
    global $district_prefix;

    $ret = [];
    if(array_key_exists($record_id, $registration_years)) {
        $ret[] = "year: " . $registration_years[$record_id];
    }
    if(array_key_exists($record_id, $registration_quarters)) {
        $ret[] = "quarter: " . $registration_quarters[$record_id];
    }
    if(array_key_exists($record_id, $registration_districts)) {
        $ret[] = "district: " . $district_prefix . $registration_districts[$record_id];
    }
    return '{' . join(', ', $ret) . '}';
}

foreach($all_people as $p) {

    $filename = $output_path . $nid_prefix . $p['nid'] . $output_ext;

    $fp = fopen($filename, "w");

    echo $filename . "\n";

    fwrite($fp, "---\n");
    fwrite($fp, "nid: " . $nid_prefix . $p['nid'] . "\n");
    fwrite($fp, "title: " . $p['title'] . "\n");
    fwrite($fp, "created: " . date('Y-m-d H:i:s', $p['created']) . "\n");
    fwrite($fp, "changed: " . date('Y-m-d H:i:s', $p['changed']) . "\n");

    fwrite($fp, "promote: " . ($p['promote'] ? 'true' : 'false') . "\n");

    if(!empty($p['field_forenames']['und'])) {
        fwrite($fp, "forenames: " . $p['field_forenames']['und'][0]['safe_value'] . "\n");
    }

    if(!empty($p['field_family_name']['und'])) {
        fwrite($fp, "family_name: " . $p['field_family_name']['und'][0]['safe_value'] . "\n");
    }

    if(!empty($p['field_maiden_name']['und'])) {
        fwrite($fp, "maiden_name: " . $p['field_maiden_name']['und'][0]['safe_value'] . "\n");
    }

    if(!empty($p['field_gender']['und'])) {
        fwrite($fp, "gender: " . ($p['field_gender']['und'][0]['tid'] == '8' ? 'male' : 'female') . "\n");       #  '8' Male, '9' Female,
    }

    if(!empty($p['field_father']['und'])) {
        fwrite($fp, "father: " . $nid_prefix . $p['field_father']['und'][0]['target_id'] . "\n");
    }
    if(!empty($p['field_mother']['und'])) {
        fwrite($fp, "mother: " . $nid_prefix . $p['field_mother']['und'][0]['target_id'] . "\n");
    }

    if(!empty($p['field_spouse']['und'])) {
        $spouses = $p['field_spouse']['und'];
        fwrite($fp, "spouse: \n");
        foreach($spouses as $spouse) {
            fwrite($fp, "    - " . $nid_prefix . $spouse['target_id'] . "\n");
        }
    }

    if(!empty($p['field_children']['und'])) {
        $children = $p['field_children']['und'];
        fwrite($fp, "children: \n");
        foreach($children as $child) {
            fwrite($fp, "    - " . $nid_prefix . $child['target_id'] . "\n");
        }
    }

    if(!empty($p['field_grave']['und'])) {
        fwrite($fp, "grave: " . $grave_prefix . $p['field_grave']['und'][0]['target_id'] . "\n");
    }

    # Partial date can be YYYY, YYYY-MM or YYYY-MM-DD
    if(!empty($p['field_date_of_birth']['und'])) {
        $dob = $p['field_date_of_birth']['und'][0]['from'];
        fwrite($fp, 'date_of_birth: "' . $dob['year']);
        if(!empty($dob['month'])) {
             fwrite($fp, '-' . str_pad($dob['month'], 2, '0', STR_PAD_LEFT));
        }
        if(!empty($dob['day'])) {
            fwrite($fp, '-' . str_pad($dob['day'], 2, '0', STR_PAD_LEFT));
        }
        fwrite($fp, '"' . "\n");
    }

    # Partial date can be YYYY, YYYY-MM or YYYY-MM-DD
    if(!empty($p['field_date_of_death']['und'])) {
        $dod = $p['field_date_of_death']['und'][0]['from'];
        fwrite($fp, 'date_of_death: "' . $dod['year']);
        if(!empty($dod['month'])) {
             fwrite($fp, '-' . str_pad($dod['month'], 2, '0', STR_PAD_LEFT));
        }
        if(!empty($dod['day'])) {
            fwrite($fp, '-' . str_pad($dod['day'], 2, '0', STR_PAD_LEFT));
        }
        fwrite($fp, '"' . "\n");
    }

    if(!empty($p['field_birth_record']['und'])) {
        $record_id = $p['field_birth_record']['und'][0]['value'];
        fwrite($fp, "birth_record: " . registration_record_str($record_id) . "\n");
    }

    if(!empty($p['field_marriage_records']['und'])) {
        $records = $p['field_marriage_records']['und'];
        fwrite($fp, "marriage_records: \n");
        foreach($records as $record) {
            fwrite($fp, "    - " . registration_record_str($record['value']) . "\n");
        }
    }

    if(!empty($p['field_death_record']['und'])) {
        $record_id = $p['field_death_record']['und'][0]['value'];
        fwrite($fp, "death_record: " . registration_record_str($record_id) . "\n");
    }

    if(!empty($p['field_grave_external_image']['und'])) {
        $images = $p['field_grave_external_image']['und'];
        fwrite($fp, "images: \n");
        foreach($images as $image) {
            fwrite($fp, "    - " . $image['url'] . "\n");
        }
    }

    fwrite($fp, "---");

    if(!empty($p['field_notes']['und'])) {
        $unescaped = nl2br(html_entity_decode($p['field_notes']['und'][0]['safe_value']));
        fwrite($fp, "\n" . $html_md_converter->convert($unescaped)  . "\n");
    }

    fclose($fp);
}


