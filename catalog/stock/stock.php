<?php

require('../includes/configure.php');
ini_set('include_path', DIR_FS_CATALOG . PATH_SEPARATOR . ini_get('include_path'));
chdir(DIR_FS_CATALOG);
require_once('includes/application_top.php');

$connection_type = zen_db_prepare_input($_GET['type']);
$products_table = TABLE_PRODUCTS;

$current_inventory = $db->Execute("SELECT * FROM " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p WHERE pd.products_id = p.products_id");
while (!$current_inventory->EOF) {
    $category = '';
    $cpath = zen_get_product_path((int)$current_inventory->fields['products_id']);
    $cpath_array = explode('_', $cpath);
    foreach($cpath_array as $cat_id){
       $category .= '|'.zen_get_category_name((int)$cat_id,1);
    }
    $category .= '|';
    $products[] = array(
        'products_name' => $current_inventory->fields['products_name'],
        'products_model' => $current_inventory->fields['products_model'],
        'quantity_available' => $current_inventory->fields['products_quantity'],
        'weight' => $current_inventory->fields['products_weight'],
        'manufacturer' => zen_get_products_manufacturers_name((int)$current_inventory->fields['products_id']),
        'wholesale_price' => number_format($current_inventory->fields['products_price'],2),
        'products_description' => htmlspecialchars($current_inventory->fields['products_description']),
        'products_category' => $category,
      //  'msrp' => number_format($current_inventory->fields['products_msrp'],2),
     //   'products_height' => $current_inventory->fields['products_height'],
     //   'products_width' => $current_inventory->fields['products_width'],
     //   'products_length' => $current_inventory->fields['products_length'],
      //  'scale' => $current_inventory->fields['products_scale'],
    );
    // echo $products['manufacturer'].'<br/>';
    $current_inventory->MoveNext();
}
switch ($connection_type) {
    case 'json':
        header('Content-Type: application/json; charset=utf-8');
        $json_output_array = array('products' => $products);
        $json_out = json_encode($json_output_array);
        echo $json_out;
        break;
    case 'xml':
        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<products>' . "\n";
        foreach ($products as $product) {
            echo '<product>' . "\n";
            foreach ($product as $field => $value) {
                echo '<' . $field . '>' . '<![CDATA[' . $value . ']]>' . '</' . $field . '>' . "\n";
            }
            echo '</product>' . "\n";
        }
        echo '</products>' . "\n";
        break;
    case 'csv':
    default:
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=stock.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, array_keys($products['0']));
        foreach ($products as $product) {
            fputcsv($out, $product);
        }
        fclose($out);
}
