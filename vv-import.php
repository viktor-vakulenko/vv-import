<?php
/*
Plugin Name: HRlink import plugin
Description: PLugin for import from xml to post 
Author: Viktor Vakulenko
URL:
Version: 1.0
*/
add_action('admin_menu', 'test_plugin_setup_menu');

function test_plugin_setup_menu(){
    add_menu_page( 'HRlink Import Plugin Page', '<strong style="color: #0BC20B;">HRlink Importer</strong>', 'manage_options', 'hrlink-import-plugin', 'test_init','dashicons-download', 40 );
}

function test_init(){
    test_handle_post();
    ?>
    <h2>New Job Oportunity Import</h2>
    <?php
    programmatically_create_post();
    ?>
    <!-- Form to handle the upload - The enctype value here is very important -->
    <form  method="post" enctype="multipart/form-data">
<!--        <input type='file' id='test_upload_hrlink' name='test_upload_hrlink' ></input>-->
        <input type='hidden' id='test_upload_hrlink' name='test_upload_hrlink' value="my_media_update"></input>
        <?php submit_button('RUN IMPORT') ?>
    </form>
    <?php
}

function test_handle_post(){
    // First check if the file appears on the _FILES array
    if(isset($_POST['test_upload_hrlink'])){
//        $url = 'https://www.just-stickers.com.ua/wp-includes/css/export.xml';
        $url = 'https://webservice.hrlink.pl/sync/api/prezentacja/export.php';
        $xml = simplexml_load_file($url,'SimpleXMLElement', LIBXML_NOCDATA);
        $content = $xml->AD;
        $content = $xml->AD->ZAKRES_OBOWIAZKOW;
        $slug = $xml->AD->STANOWISKO;
        $title = $xml->AD->JOB_TITLE;
//        $author_id = 10;
        foreach ($xml as $ad){
            $title = $ad->JOB_TITLE;
            if (!get_page_by_title($title, OBJECT, 'job_application')) {
                $post_id = wp_insert_post(
                    array(
                        'comment_status' => 'closed',
                        'ping_status' => 'closed',
//                        'post_author' => $author_id,
                        'post_name' => $slug,
                        'post_title' => $title,
                        'post_status' => 'publish',
                        'post_type' => 'job_application',
                        'meta_key'		=> 'field_55891a7185job',
                        'meta_value'	=> 'red',
                    )
                );


                if ( $post_id && ! is_wp_error( $post_id ) ) {
                        $post_ID = $post_id;
                    $url = 'https://webservice.hrlink.pl/sync/api/prezentacja/export.php';
                        $xml = simplexml_load_file($url,'SimpleXMLElement', LIBXML_NOCDATA);
                        $content1 = $ad->ZAKRES_OBOWIAZKOW;
                        $content2 = $ad->WYMAGANIA;
                        $content3 = $ad->OFERUJEMY;
                        $content = $content1. $content2. $content3;

                    $field_key = 'field_55c31ccc60job';
                    $value = array(
                        // nested for each row
//                    array(
//                        // field key => value pairs
//                        'field_55c31dd471job' => 'value for row 1'
//                    ),
                        array(
                            // field key => value pairs

                            'field_592e810b5455a' => $content,
                        ),
                    );

                    update_field($field_key, $value, $post_ID);
                }

                echo '  <div class="notice notice-success is-dismissible" style="height: 30px; padding-top: 10px;">New job <b>"' .$title.'"</b> published.<br></div><br>';
            }


        }


//        if (!get_page_by_title($title, OBJECT, 'job_application')) {
//            $post_id = wp_insert_post(
//                array(
//                    'comment_status' => 'closed',
//                    'ping_status' => 'closed',
//                    'post_author' => $author_id,
//                    'post_name' => $slug,
//                    'post_title' => $title,
//                    'post_status' => 'publish',
//                    'post_type' => 'job_application',
//                    'meta_key'		=> 'field_55891a7185job',
//                    'meta_value'	=> 'red',
//                )
//            );
//
//
//            if ( $post_id && ! is_wp_error( $post_id ) ) {
//                $post_ID = $post_id;
//                $url = 'https://webservice.hrlink.pl/sync/api/prezentacja/export.php';
//                $xml = simplexml_load_file($url,'SimpleXMLElement', LIBXML_NOCDATA);
//                $content1 = (string)$xml->AD->ZAKRES_OBOWIAZKOW;
//                $content2 = (string)$xml->AD->WYMAGANIA;
//                $content3 = (string)$xml->AD->OFERUJEMY;
//                $content = $content1. $content2. $content3;
//                $title = $xml->AD->JOB_TITLE;
//                $field_key = 'field_55c31ccc60job';
//                $value = array(
//                    // nested for each row
////                    array(
////                        // field key => value pairs
////                        'field_55c31dd471job' => 'value for row 1'
////                    ),
//                    array(
//                        // field key => value pairs
//
//                        'field_592e810b5455a' => $content,
//                    ),
//                );
//
//                update_field($field_key, $value, $post_ID);
//            }
//
//            echo '  <div style="
//    width: 50%;
//    height: 50px;
//    text-align: center;
//    border: 2px solid green;
//    display: flex;
//    justify-content: center;
//    font-size: 18px;
//    align-items: center;
//    background: #e7e06b;">New job st ' .$title.' published.<br></div>';
//        }
//        else{
//
//            echo '<div style="
//    width: 50%;
//    height: 50px;
//    text-align: center;
//    border: 2px solid green;
//    display: flex;
//    justify-content: center;
//    font-size: 18px;
//    align-items: center;
//    background: #e7e06b;">Nothing have for import.<br> You have actual Job aplications</div>';
//        }


    }
}

function programmatically_create_post()
{
//    $url = 'https://www.just-stickers.com.ua/wp-includes/css/export.xml';
    $url = 'https://webservice.hrlink.pl/sync/api/prezentacja/export.php';
    $xml = simplexml_load_file($url,'SimpleXMLElement', LIBXML_NOCDATA);
    $xml2 = simplexml_load_file($url);
//  if (is_array($xml2->ID)){
//      echo "arraaay!! yes";
//}
//else{
//    echo "not array ";
//}
    $content = $xml->AD->ZAKRES_OBOWIAZKOW;
    $slug = $xml->AD->STANOWISKO;
    $title = $xml->AD->JOB_TITLE;
    ?>
    <div class="notice-info notice"><h4>You have &nbsp;</h4><?PHP
        $number = 0;
    foreach ($xml as $ad){


        if (!get_page_by_title($title, OBJECT, 'job_application')) {
            $title = $ad->JOB_TITLE;
            if ($title) {
                echo '<p style="font-size: 14px;">'.++$number .'.&nbsp"'. $title . '" </p> &nbsp;';

            }

        }
        else{
            echo '<h4>nothing</h4>';
            break;
        }

    }

    ?>
        <h4> for import</h4></div>
    <?php






}



?>