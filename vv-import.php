<?php
/*
Plugin Name: HRlink import plugin
Description: Plugin for import from xml to post
Author: <a href="https://github.com/viktor-vakulenko/vv-import">viktor vakulenko</a>
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
    if (isset($_POST['test_upload_hrlink'])) {
//        $url = 'https://www.just-stickers.com.ua/wp-includes/css/export.xml';
        $url = 'https://webservice.hrlink.pl/sync/api/prezentacja/export.php';
        $xml = simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOCDATA);
        $content = $xml->AD;
        $content = $xml->AD->ZAKRES_OBOWIAZKOW;
        $slug = $xml->AD->STANOWISKO;
        $title = $xml->AD->JOB_TITLE;
//        $author_id = 10;
        foreach ($xml as $ad) {
            $title = $ad->JOB_TITLE;
            $link = $ad->APPLY_LINK;
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
                        'meta_key' => 'field_55891a7185job',
                        'meta_value' => 'red',
                    )
                );


                if ($post_id && !is_wp_error($post_id)) {
                    $post_ID = $post_id;
                    $url = 'https://webservice.hrlink.pl/sync/api/prezentacja/export.php';
                    $xml = simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOCDATA);
                    $content1 = $ad->ZAKRES_OBOWIAZKOW;
                    $content2 = $ad->WYMAGANIA;
                    $content3 = $ad->OFERUJEMY;
                    $content = $content1 . $content2 . $content3;
                    $link = (string)$ad->APPLY_LINK;


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
                            'field_5d53ef234713f' => 'apply',
                            'field_5d53ef234710f' => $link,

                        ),

                    );
                    update_field($field_key, $value, $post_ID);
                }

                echo '  <div class="notice notice-success is-dismissible" style="height: 30px; padding-top: 10px;">New job <b>"' . $title . '"</b> published.<br></div><br>';
            }


        }

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
    $link = $xml->AD->APPLY_LINK;
    ?>
    <div class="notice-info notice"><h4>You have &nbsp;</h4><?PHP
        $number = 0;
    foreach ($xml as $ad){

        $ad->APPLY_LINK;
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


//Cron job new shedule time
add_filter( 'cron_schedules', 'custom_cron_shedule');

function custom_cron_shedule( $shedule ) {
    // $raspisanie - это массив, состоящий из всех зарегистрированных интервалов
    // наша задача - добавить в него свой собственный интервал, к примеру пусть будет 3 минуты
    $shedule['every_one_hour'] = array(
        'interval' => 360, // в одной минуте 60 секунд, в трёх минутах - 180
        'display' => 'Every 1 hour' // отображаемое имя
    );
    return $shedule;
}
if( !wp_next_scheduled('new_import_from_hrlink' ) )
    wp_schedule_event( time(), 'every_one_hour', 'new_import_from_hrlink' );

add_action( 'new_import_from_hrlink', 'import_new_job_application', 10, 3 );
function import_new_job_application(){
    // First check if the file appears on the _FILES array

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
        $link = $ad->APPLY_LINK;
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
                $link =(string)$ad->APPLY_LINK;



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
                        'field_5d53ef234713f' => 'apply',
                        'field_5d53ef234710f' => $link,

                    ),

                );
                update_field($field_key, $value, $post_ID);
            }

            echo '  <div class="notice notice-success is-dismissible" style="height: 30px; padding-top: 10px;">New job <b>"' .$title.'"</b> published.<br></div><br>';
        }


    }


}
?>