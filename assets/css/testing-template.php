<?php

/**
 * Template Name: testing
 * 
 */
?>


<?php
$args = array(
    'limit' => -1,
    'status'  => 'publish',
    'tax_query' => array( array(
        'taxonomy' => 'product_cat',
        'field' => 'id',
        'terms' => array( 314 ), // HERE the product category to exclude
        'operator' => 'NOT IN',
    ) ),
);
$products = wc_get_products($args);

    // echo "<pre>";
    // var_dump($products);
    // exit;

function custom_echo($x, $length)
{
  if(strlen($x)<=$length)
  {
    return $x;
  }
  else
  {
    $y=substr($x,0,$length) . '...';
    return $y;
  }
}

function my_fix_content( $content ) {
  $content = preg_replace("~(?:\[/?)[^/\]]+/?\]~s", '', $content);
  $content = preg_replace('~(?:\[/?).*?"]~s', '', $content);
  $content = preg_replace('(\\[([^[]|)*])', '', $content );
  $content = preg_replace('/\[(.*?)\]/', '', $content );
  return $content;
}

function periodAfterLimit($short_Desc){
  $shortest_Desc = '';
  foreach(explode(".", $short_Desc) as $lines){
    if(strlen($shortest_Desc) < 200){
      $shortest_Desc = $shortest_Desc . ' ' . $lines . ".";
    }else{
      break;
    }
  }
  return $shortest_Desc;
}



$counter = 1;
$array = [];
$array[0] = array('ean', 'locale', 'category', 'title', 'short_description', 'description', 'picture', 'manufacturer', 'content_volume', 'cosmetics_ingredients', 'weight');


foreach($products as $product){

    // ean
    $ean = '';
    // locale
    $locale = 'de-DE';
    // category
    $category = '';
    $category_ids = $product->category_ids; // array
    if(!empty($category_ids)){
        foreach($category_ids as $cat_id){
            $category =  get_term_by( 'id', $cat_id, 'product_cat' )->name. "+" .$category;
        }
    }
    $category = substr_replace($category ,"",-1);
    // title
    $title = $product->name;
    // short_description
    $short_Desc = $product->short_description;
    $short_Desc = $short_Desc === "" ? periodAfterLimit(strip_tags(my_fix_content($product->description))) : $short_Desc;

    
    // description
    $desc = my_fix_content($product->description);
    // picture
    $image_id = $product->image_id;
    $imgurldesktop = wp_get_attachment_image_url( $image_id, '' );
    // manufacturer
    $manfacturer = "Nutracosmetic GmbH";
    // content_volume
    $content_volume = "";
    // cosmetics_ingredients
    $cosmetic_ingredients = "";
    // weight
    $weight = "";

    // EAn for each variation
    if ( $product->is_type( 'variable' ) ) {
      $variations = $product->get_available_variations();
      foreach($variations as $variation){
        $array[$counter][] = get_post_meta( $variation['variation_id'], '_ts_gtin', true );
        $array[$counter][] = $locale;
        $array[$counter][] = $category;
        $array[$counter][] = $title;
        $array[$counter][] = $short_Desc;
        $array[$counter][] = $desc;
        $array[$counter][] = $imgurldesktop;
        $array[$counter][] = $manfacturer;
        $array[$counter][] = array_shift(woocommerce_get_product_terms($product->id, 'pa_gre', 'names'));
        $array[$counter][] = $cosmetic_ingredients;
        $array[$counter][] = $weight;
        $counter++;
      }      
    }elseif( $product->is_type( 'simple' ) ){
      $array[$counter][] = get_post_meta( $product->get_id(), '_ts_gtin', true );
      $array[$counter][] = $locale;
      $array[$counter][] = $category;
      $array[$counter][] = $title;
      $array[$counter][] = $short_Desc;
      $array[$counter][] = $desc;
      $array[$counter][] = $imgurldesktop;
      $array[$counter][] = $manfacturer;
      if (str_contains($title, ' g ')) {
        $p1 = strpos($title, ' g ');
        $p2 = $p1 - 4;
        $int = (int) filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT);
        $unit = 'g';
        $value = $int . $unit;
        $array[$counter][] = $value;
      }
      if (str_contains($title, ' g) ')) {
        $p1 = strpos($title, ' g) ');
        $p2 = $p1 - 4;
        $int = (int) filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT);
        $unit = 'g';
        $value = $int . $unit;
        $array[$counter][] = $value;
      }
      if (str_contains($title, ' ml ')) {
        $p1 = strpos($title, ' ml ');
        $p2 = $p1 - 4;
        $int = (int) filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT);
        $unit = 'ml';
        $value = $int . $unit;
        $array[$counter][] = $value;
      }
      $array[$counter][] = $cosmetic_ingredients;
      // Weight
      if (str_contains($title, ' g ')) {
        $p1 = strpos($title, ' g ');
        $p2 = $p1 - 4;
        $int = (int) filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT);
        $unit = 'kg';
        $value = $int/1000;
        $value = $value + 0.100;
        $array[$counter][] = $value . $unit;
      }
      if (str_contains($title, ' g) ')) {
        $p1 = strpos($title, ' g) ');
        $p2 = $p1 - 4;
        $int = (int) filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT);
        $unit = 'kg';
        $value = $int/1000;
        $value = $value + 0.100;
        $array[$counter][] = $value . $unit;
      }
      if (str_contains($title, ' ml ')) {
        $p1 = strpos($title, ' ml ');
        $p2 = $p1 - 4;
        $int = (int) filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT);
        $unit = 'kg';
        $value = $int/1000;
        $value = $value + 0.100;
        $array[$counter][] = $value . $unit;
      }
    }
    
          
    $counter++;
}


// foreach($products as $product){

//   $ean = '';
//   $condition = 100;
//   $price = str_replace(".", '', $product->price);
//   $price_cs = $product->price;
//   $currency = 'EUR';
//   $handling_time = 2;
//   $count = 30;
//   // EAn for each variation
//   if ( $product->is_type( 'variable' ) ) {
//     $variations = $product->get_available_variations();
//     foreach($variations as $variation){
//       $array[$counter][] = get_post_meta( $variation['variation_id'], '_ts_gtin', true );
//       $array[$counter][] = $condition;
//       $array[$counter][] = $price;
//       $array[$counter][] = $price_cs;
//       $array[$counter][] = $currency;
//       $array[$counter][] = $handling_time;
//       $array[$counter][] = $count;
//       $counter++;
//     }      
//   }elseif( $product->is_type( 'simple' ) ){
//     $array[$counter][] = get_post_meta( $product->get_id(), '_ts_gtin', true );
//     $array[$counter][] = $condition;
//     $array[$counter][] = $price;
//     $array[$counter][] = $price_cs;
//     $array[$counter][] = $currency;
//     $array[$counter][] = $handling_time;
//     $array[$counter][] = $count;
//   }
   
//   $counter++;
// }



function array2csv(array &$array)
{
   if (count($array) == 0) {
     return null;
   }
   ob_start();
   $df = fopen("php://output", 'w');
   fputcsv($df, array_keys(reset($array)));
   foreach ($array as $row) {
      fputcsv($df, $row);
   }
   fclose($df);
   return ob_get_clean();
}

function download_send_headers($filename) {
  // disable caching
  $now = gmdate("D, d M Y H:i:s");
  header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
  header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
  header("Last-Modified: {$now} GMT");

  // force download  
  header("Content-Type: application/force-download");
  header("Content-Type: application/octet-stream");
  header("Content-Type: application/download");

  // disposition / encoding on response body
  header("Content-Disposition: attachment;filename={$filename}");
  header("Content-Transfer-Encoding: binary");
}

// download_send_headers("data_export_" . date("Y-m-d") . ".csv");
// echo array2csv($array);
// die();