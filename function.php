add_shortcode('user_list','front_top_individual');
function front_top_individual($attr){
        ob_start();

        $load_more_btn = 'no';
        $per_page = '10';
        $deed_type = "Individual";

        if(isset($attr)){
            if($attr['per_page']){
                $per_page = $attr['per_page'];
            }
            if($attr['load_more_btn']){
                $load_more_btn = $attr['load_more_btn'];
            }
            if($attr['deed_type']){
                $deed_type = $attr['deed_type'];
            }
        }

    $deed_list = get_deed_list(1, $per_page, $deed_type);
    $next_data = $deed_list['next_data'];
    
    if($deed_list['html']){


        if($deed_type == 'Individual'){
        ?>
        <div class="beer-callout" id="deed_list_loadmore_ajax"> 
            <?php
            echo $deed_list['html'];
            ?>
        </div>
        <?php }else{?>
            <div class="beer-callout text_White" id="deed_list_loadmore_ajax"> 
                <?php
                echo $deed_list['html'];
                ?>
            </div>
       <?php } ?>
            
        <?php if($load_more_btn == 'yes'){ ?>
            <div class="show_more deed_main_load_more" style="display:<?php echo ($next_data > 0)? "block" : "none"; ?>">
                <span>
                    <button type="button" class="load_more deed_load_more">Load More <span class="load_spin_deed" style="display:none;"><i class="fas fa-circle-notch fa-spin"></i></span></button>
                    
                </span>
            </div>
        <?php } 
    }
    ?>
        <script>
            jQuery(document).ready(function($){
                
                        let currentPage = 1;
                        $('.deed_load_more').on('click', function(e) {
                            e.preventDefault();
                        currentPage++;
                     
                        $('.deed_load_more').find(".load_spin_deed").show();
                        
                        var a_url = '<?php echo admin_url('admin-ajax.php') ?>';
                        var data={
                            'action' : 'action_fornt_topers',
                            'pageno' : currentPage,
                            'no_of_records_per_page' : '<?php echo $per_page; ?>',
                            'deed_type' : '<?php echo $deed_type; ?>'
                        };
                      
                        $.post(a_url,data,function(response){
                            var result_data =  JSON.parse(response);
                        
                           
                            if(result_data.html){                                
                                $('#deed_list_loadmore_ajax').append(result_data.html);                                
                            }
                            $('.deed_load_more').find(".load_spin_deed").hide();
                            if(result_data.next_data == 0){
                                $('.deed_main_load_more').hide();
                            }
                        });
                          
                    });
                });
        </script>
    <?php
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}

function get_offset_deed($pageno = '', $no_of_records_per_page = 10)
{
    if (isset($pageno)) {
        $pageno = $pageno;
    } else {
        $pageno = 1;
    }
    $offset = ($pageno-1) * $no_of_records_per_page;
    return $offset;
}

function get_deed_list($pageno, $no_of_records_per_page, $deed_type)
{     
    $offsetval = get_offset_deed($pageno, $no_of_records_per_page);
    $offsetNew = get_offset_deed(($pageno+1), $no_of_records_per_page);
    
    global $wpdb ;
    $post_deed_count = $wpdb->prefix.'post_deed_type';
    
    $sql = "SELECT deeed.*, SUM(deed_count) as  count_total FROM $post_deed_count AS deeed WHERE com_or_ind = '$deed_type' GROUP BY user_id ORDER BY  id  DESC LIMIT $offsetval, $no_of_records_per_page";    
    $top_ind =  $wpdb->get_results($sql , ARRAY_A);


    $sql1 = "SELECT deeed.*, SUM(deed_count) as  count_total FROM $post_deed_count AS deeed WHERE com_or_ind = '$deed_type' GROUP BY user_id ORDER BY  id  DESC LIMIT $offsetNew, $no_of_records_per_page";    
    $top_next =  $wpdb->get_results($sql1 , ARRAY_A);

    $html = '';

    if($top_ind){
        foreach($top_ind as  $top_inds){

            
            $html .= '<div class="beer-clp">';
            $html .= '<span class="beer-icn">'; 
            if($deed_type != 'Individual'){ 
                $html .= '<img src="'.site_url().'/wp-content/uploads/2022/09/favourite.png" alt="ENGLISH STYLE BROWN ALE">';
            }else{
                $html .= '<img src="'.site_url().'/wp-content/uploads/2022/09/favourite-org.png" alt="ENGLISH STYLE BROWN ALE">';
            }
            
            $html .=    '</span>' ;
            $html .=          '<span class="beer-para">';

                                    $uid = $top_inds['user_id'];
                                    $user = get_user_by( 'id', $uid );

            $html .=                '<a href=""><h4 data-id="'.$uid.'">'.$user->display_name.'';
                            
            $html .=            '</h4></a>';
            $html .=                '<span class="sp-p1"><b>Points:</b>'.$top_inds['count_total'].'';

            $html .=         '</span>' ;
            $html .=            '</span>'; 
            $html .=        '</div>';
            
        }            
    }
    
    $data = array(
        'html' => $html,
        'next_data' => count($top_next)
    );
    return $data;
}
add_action('wp_ajax_action_fornt_topers','front_end_topers_listing');
add_action('wp_ajax_nopriv_action_fornt_topers','front_end_topers_listing');
function front_end_topers_listing(){
    
    $no_of_records_per_page = $_REQUEST['no_of_records_per_page'];
    $data = get_deed_list($_REQUEST['pageno'], $no_of_records_per_page);
    echo json_encode($data);
    die();
    
} 

