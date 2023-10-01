<?php
if (!function_exists('whizzChat_globalVal')) {

    function whizzChat_globalVal($key = '', $else = '') {
        if ($key != "") {
            if (isset($GLOBALS["whizzChat_options"]["$key"]) && $GLOBALS["whizzChat_options"]["$key"] != "") {
                return $GLOBALS["whizzChat_options"]["$key"];
            } else if (isset($else) && $else != "") {
                return $else;
            } else {
                return '';
            }
        } else {
            return $GLOBALS["whizzChat_options"];
        }
    }
}
if (!function_exists('whizzChat_upload_info')) {

    function whizzChat_upload_info($type = '') {
        $data = array();
        if ($type == 'image') {
            $img_size_kb = whizzChat_globalVal('whizzChat-image-size', '1111') * 1000;
            $formates = whizzChat_globalVal('whizzChat-image-format', 'jpeg,jpg,png,gif,bmp');
            $formates = explode(',', $formates);
            $data["format"] = $formates;
            $data["is_allow"] = whizzChat_globalVal('whizzChat-allow-image', 1);
            $data["size"] = $img_size_kb;
            $data["type"] = 'image';
        }

        if ($type == 'file') {
            $file_size_kb = whizzChat_globalVal('whizzChat-file-size', '1111') * 1000;
            $data["is_allow"] = whizzChat_globalVal('whizzChat-allow-file', 1);
            $data["size"] = $file_size_kb;
            $formates = whizzChat_globalVal('whizzChat-file-format', 'zip,docx');
            $formates = explode(',', $formates);
            $data["format"] = $formates;
            $data["type"] = 'file';
        }
        return $data;
    }

}

if (!function_exists('whizzChat_image_upload_info')) {

    function whizzChat_image_upload_info() {
        $img = array();
        $formates = whizzChat_globalVal('whizzChat-image-format', 'jpeg,jpg,png,gif,bmp');
        $formates = explode(',', $formates);
        $img["format"] = $formates;
        $img["is_allow"] = whizzChat_globalVal('whizzChat-allow-image', 1);
        $img["size"] = whizzChat_globalVal('whizzChat-image-size', '1111');
        return $img;
    }

}

if (!function_exists('whizzChat_file_upload_info')) {

    function whizzChat_file_upload_info() {
        $file = array();
        $file["is_allow"] = whizzChat_globalVal('whizzChat-allow-file', 1);
        $file["size"] = whizzChat_globalVal('whizzChat-file-size', '1111');
        $formates = whizzChat_globalVal('whizzChat-file-format', 'zip,docx');
        $formates = explode(',', $formates);
        $file["format"] = $formates;
        return $file;
    }

}

add_action('init', 'whizzChat_start_session', 1);

if (!function_exists('whizzChat_start_session')) {

    function whizzChat_start_session() {
        if (!headers_sent() && '' == session_id()) {
          //  session_start();
        }
    }

}

if (!function_exists('whizzChat_set_session')) {

    function whizzChat_set_session() {
        global $session;
        $_SESSION = array();
        session_write_close();
        session_start();
        session_regenerate_id();
    }

}

if (!function_exists('whizzChat_showChatBox_on')) {

    function whizzChat_showChatBox_on($chats_data = array()) {
        $default_posts = array(
            'post' => 'Post',
            'page' => 'Page',
        );
        $args = array(
            'public' => true,
            '_builtin' => false,
        );
        $custom_post_types = get_post_types(
                array(
            'public' => true,
            '_builtin' => false,
                ), 'objects');

        $cusPostType = array();
        if (isset($custom_post_types) && !empty($custom_post_types) && is_array($custom_post_types)) {
            foreach ($custom_post_types as $post_slug => $post_data) {
                $cusPostType[$post_slug] = $post_data->label;
            }
        }
        $final_array = array_merge($default_posts, $cusPostType);
        return $final_array;
    }

}

if (!function_exists('whizzChat_allowed_pages')) {

    function whizzChat_allowed_pages($page_id = "") {
        $allowed_types = whizzChat_globalVal('whizzChat-boxShow-on', array());
        if (isset($allowed_types) && count($allowed_types) > 0) {
            if (is_singular($allowed_types)) {
                return true;
            }
            if (is_author() && in_array('author', $allowed_types)) {
                return true;
            }
        }
        if($page_id != ""){          
            return true;
        }
        return false;
    }

}

if (!function_exists('whizzChat_get_blocked_user_id')) {

    function whizzChat_get_blocked_user_id($chat_id = '', $current_session = '') {
        global $wpdb;
        global $whizz_tbl_sessions;
        $query = "SELECT * FROM $whizz_tbl_sessions WHERE `id` = %s LIMIT 1";
        $results = $wpdb->get_results($wpdb->prepare($query, $chat_id));
        $blocked_id = '';
        $current_session = ($current_session != "" ) ? $current_session : whizzChat::session_id();
        if (isset($results[0]->session) && $results[0]->session != $current_session) {
            $blocked_id = $results[0]->session;
        } else if (isset($results[0]->rel) && $results[0]->rel != $current_session) {
            $blocked_id = $results[0]->rel;
        }
        return $blocked_id;
    }

}

if (!function_exists('whizzChat_is_user_blocked')) {

    function whizzChat_is_user_blocked($chat_id = 0, $double = false, $current_session = '') {
        global $wpdb;
        global $whizz_tbl_user_preferences;
        global $whizz_tbl_sessions;
        $query = "SELECT * FROM $whizz_tbl_sessions WHERE `id` = %s LIMIT 1";
        $results = $wpdb->get_results($wpdb->prepare($query, $chat_id));
        $blocked_id = '';
        $current_session = ($current_session != "" ) ? $current_session : whizzChat::session_id();
        if (isset($results[0]->session) && $results[0]->session != $current_session) {
            $blocked_id = $results[0]->session;
        } else if (isset($results[0]->rel) && $results[0]->rel != $current_session) {
            $blocked_id = $results[0]->rel;
        }
        $is_blocked = array();
        $is_blocked['id'] = 0;
        $is_blocked['current_session'] = $current_session;
        $is_blocked['blocked_id'] = $blocked_id;
        $is_blocked['blocker_id'] = $current_session;
        $is_blocked['post_id'] = (isset($results[0]->chat_box_id)) ? $results[0]->chat_box_id : '';
        $is_blocked['chat_session'] = (isset($results[0]->session)) ? $results[0]->session : '';
        $user_block = false;
        if ($blocked_id != "") {
            if ($double == true) {
                $query = "SELECT * FROM $whizz_tbl_user_preferences WHERE (`blocker_id` = %s AND `blocked_id` = %s) OR (`blocker_id` = %s AND `blocked_id` = %s) LIMIT 1";
                $blocked = $wpdb->get_results($wpdb->prepare($query, $current_session, $blocked_id, $blocked_id, $current_session));
            } else {
                $query = "SELECT * FROM $whizz_tbl_user_preferences WHERE `blocker_id` = %s AND `blocked_id` = %s LIMIT 1";
                $blocked = $wpdb->get_results($wpdb->prepare($query, $current_session, $blocked_id));
            }
            if ($blocked) {
                $user_block = true;
                $is_blocked['id'] = $blocked[0]->id;
                $is_blocked['post_id'] = $blocked[0]->post_id;
                $is_blocked['blocked_id'] = $blocked[0]->blocked_id;
                $is_blocked['blocker_id'] = $blocked[0]->blocker_id;
            }
        }
        $is_blocked['is_blocked'] = $user_block;
        return $is_blocked;
    }

}

/* Get Chat List */
if (!function_exists('whizzChat_load_all_chats')) {
    function whizzChat_load_all_chats(){
        global $wpdb;
        global $whizz_tbl_sessions;
        global $whizz_tblname_chat_message;
        $value = array();
        
        $query = "SELECT * FROM $whizz_tbl_sessions;";
        $chats = $wpdb->get_results($query,OBJECT);

        if (isset($chats) && count($chats) > 0) {
    
            foreach ($chats as $chat) {
    
                $qry_msgs = "SELECT * FROM $whizz_tblname_chat_message WHERE `session_id` = %s";
                $chats_msgs = $wpdb->get_results($wpdb->prepare($qry_msgs, $chat->id));
                 if (isset($chats_msgs) && count($chats_msgs) <= 0) {
                     continue;
                 }
    
                if (isset($chat->id)) {
                    $value[] = array(
                        "id" => $chat->id,
                        "name" => $chat->name,
                        "email" => $chat->email,
                        "chat_status" => $chat->status,
                        "post_id" => $chat->chat_box_id,
                        "post_title" => get_the_title($chat->chat_box_id),
                        "post_author_id" => $chat->rel,
                        "session_id" => $chat->session,
                        "start_time" => $chat->timestamp,
                        "last_active_time" => $chat->last_active_timestamp,
                        "chat_box_status" => $chat->chat_box_status,
                        "message_for" => $chat->message_for,
                        "message_count" => $chat->message_count
                    );
                    //$post_ids[] = $chat->chat_box_id;
                }
            }
        }
        
        return $value;
    }
}
if (!function_exists('whizzChat_chat_list')) {
    function whizzChat_chat_list($parms = array()) {
        global $wpdb, $whizz_tbl_sessions, $whizzChat_options,$whizz_tblname_chat_message;

        $whizzChat_options = get_option('whizz-chat-options');
        $whizzChat_chat_between = $whizzChat_options['whizzChat-chat-between'];
        $whizzchat_admin_page = $whizzChat_options["whizzChat-admin-page"];
        $whizzchat_admin_page = isset($whizzchat_admin_page) && $whizzchat_admin_page != '' ? $whizzchat_admin_page : 0;

        $whizzchat_admin_val = $whizzChat_options["whizzChat-admin-value"];

        $filter = '';
        $session_id = whizzChat::session_id();
        $user_id = get_current_user_id();
        $current_post_id = get_the_ID();
        $value = array();
        $post_ids = array();
        $inner = false;
        $status = (isset($parms['status'])) ? $parms['status'] : 1;
        $chat_box_status = (isset($parms['chat_box_status'])) ? $parms['chat_box_status'] : 0;
        $exclude_values = (isset($parms['exclude'])) ? implode(",", $parms['exclude']) : array();



        $exel = '';
        if (!empty($exclude_values)) {
            $exel = " AND `id` NOT IN (" . $exclude_values . ") ";
        }
        $qry = "SELECT * FROM $whizz_tbl_sessions WHERE (`session` = %s OR `rel` = %s)  AND `status` = %s $exel ORDER BY last_active_timestamp DESC";
        $chats = $wpdb->get_results($wpdb->prepare($qry, $session_id, $user_id, $status));


            

        if (isset($chats) && count($chats) > 0) {

            foreach ($chats as $chat) {

                $qry_msgs = "SELECT * FROM $whizz_tblname_chat_message WHERE `session_id` = %s";
                $chats_msgs = $wpdb->get_results($wpdb->prepare($qry_msgs, $chat->id));
                 if (isset($chats_msgs) && count($chats_msgs) <= 0) {
                     continue;
                 }

                if (isset($chat->id)) {
                    $value[] = array(
                        "id" => $chat->id,
                        "name" => $chat->name,
                        "email" => $chat->email,
                        "chat_status" => $chat->status,
                        "post_id" => $chat->chat_box_id,
                        "post_title" => get_the_title($chat->chat_box_id),
                        "post_author_id" => $chat->rel,
                        "session_id" => $chat->session,
                        "start_time" => $chat->timestamp,
                        "last_active_time" => $chat->last_active_timestamp,
                        "chat_box_status" => $chat->chat_box_status,
                        "message_for" => $chat->message_for,
                        "message_count" => $chat->message_count
                    );
                    $post_ids[] = $chat->chat_box_id;
                }
            }
        }

        $allowed_pages = whizzChat_allowed_pages();
        if (!isset($chats_data['id']) && $allowed_pages) {
            $current_post_id = get_the_ID();
            $author_id = get_post_field('post_author', $current_post_id);
            if (isset($post_ids) && !empty($post_ids) && !in_array($current_post_id, $post_ids)) {
                $value[] = array(
                    "id" => 0,
                    "name" => '',
                    "email" => '',
                    "chat_status" => 0,
                    "post_id" => $current_post_id,
                    "post_title" => get_the_title($current_post_id),
                    "post_author_id" => $author_id,
                    "session_id" => $session_id,
                    "start_time" => '',
                    "last_active_time" => '',
                    "message_for" => 0,
                    "message_count" => 0
                );
            }
        }
        return $value;
    }

}

if (!function_exists('whizzChat_chat_boxes')) {

    function whizzChat_chat_boxes($chats_data = array(), $extra_data = array() ,$page_id  = "" ,$author_page_id   =  "") {
        
        global $wpdb, $whizz_tbl_sessions, $whizzChat_options;
        $whizzChat_options = get_option('whizz-chat-options');
        $user_id = get_current_user_id();



        $whizzChat_chat_between  = $whizzChat_options["whizzChat-chat-between"];
        $whizzchat_admin_value   = $whizzChat_options["whizzChat-admin-value"];
        $whizzchat_admin_page    = $whizzChat_options["whizzChat-admin-page"];

        $whizzchat_admin_page    = isset($whizzchat_admin_page) && $whizzchat_admin_page != '' ? $whizzchat_admin_page : 0;

        $post_ids = array();
        $session_id = whizzChat::session_id();

        $value = $first_chat_id = array();
        $inner = false;
        $chats = array();
        $direct_load = ( isset($chats_data['direct_load']) && $chats_data['direct_load'] == true ) ? false : true;
        
        if (isset($chats_data) && $chats_data && $direct_load) {

            if (isset($chats_data['id']) && $chats_data['id'] != "") {

                $qry = "SELECT * FROM $whizz_tbl_sessions WHERE `id` = %s ORDER BY id DESC LIMIT 1";
                $chats = $wpdb->get_results($wpdb->prepare($qry, $chats_data['id']));
            } else {

                $ids = array();
                $postIds = array();
                if (isset($chats_data)) {
                    foreach ($chats_data as $val) {
                        $ids[] = ($val['chat_id']);
                        $postIds[] = $val['post_id'];
                    }
                }
                if (count($ids) > 0) {
                    $ids = implode(',', $ids);
                    $postIds = implode(',', $postIds);
                    $qry = "SELECT * FROM $whizz_tbl_sessions WHERE (`session` = %s OR `rel` = %s ) AND `id` IN ($ids) AND `chat_box_id` IN ($postIds) ORDER BY ID DESC";
                    $chats = $wpdb->get_results($wpdb->prepare($qry, $session_id, $user_id, 1));
                }
            }
      
        } else {
            $qry = "SELECT * FROM $whizz_tbl_sessions WHERE ( (`session` = %s AND `chatbox_sender_open` = %s) OR (`rel` = %s AND `chatbox_receiver_open` = %s) ) AND `status` = %s  ORDER BY ID DESC";
            $chats = $wpdb->get_results($wpdb->prepare($qry, $session_id ,1, $user_id, 1, 1));
            if($page_id != ""  && $author_page_id == ""){       
           $qry = "SELECT * FROM $whizz_tbl_sessions WHERE ( (`session` = %s AND `chat_box_id` = %s ) OR   (`rel` = %s AND `chat_box_id` = %s ))  ORDER BY ID DESC";
            $chats = $wpdb->get_results($wpdb->prepare($qry, $session_id,$page_id ,$session_id ,$page_id));
            }
        }
        $chat_box_open = 1;
        if (isset($chats_data['chat_box_open']) && $chats_data['chat_box_open'] != true) {
            $chat_box_open = 0;
        }

        $last_chat_id = '';
        if (isset($chats_data['last_chat_id']) && $chats_data['last_chat_id'] != "") {
            $last_chat_id = $chats_data['last_chat_id'];
        }
        $send_message = '';
        if (isset($chats_data['send_message']) && $chats_data['send_message'] != "") {
            $send_message = $chats_data['send_message'];
        }
        $max_chat_box = $whizzChat_options["whizzChat-max-chatbox"];
        $max_chat_box_window = isset($max_chat_box) && $max_chat_box != '' && $max_chat_box != 0 ? $max_chat_box : 3;
        if (isset($chats) && is_array($chats) && !(count($chats) <= $max_chat_box_window)) {
            if (isset($extra_data['load_more']) && $extra_data['load_more']) {
                $chats = array_slice($chats, $max_chat_box_window);  // restrict to show only required num of chat box
            } else {
                $chats = array_slice($chats, 0, $max_chat_box_window);  // restrict to show only required num of chat box
            }
        }
        if (isset($chats) && is_array($chats) && count($chats) > 0) {
            foreach ($chats as $chat) {
                if (isset($chat->id)) {
                    $first_message_id = '';
                    if (isset($extra_data['message_ids'])) {
                        foreach ($extra_data['message_ids'] as $val) {
                            $get_message_id = (isset($val['get_message_id'])) ? $val['get_message_id'] : '';
                            if (isset($val['chat_id']) && $val['chat_id'] == $chat->id) {
                                $first_message_id = $get_message_id;
                            }
                        }
                    }                                                                      
                    if($author_page_id != ""   &&  $author_page_id != $session_id){                
                        $value[] = array(
                        "id" => (isset($chat->id)) ? $chat->id : $chat->id,
                        "name" => $chat->name,
                        "email" => $chat->email,
                        "chat_status" => $chat->status,
                        "post_id" => $chat->chat_box_id,
                        "post_author_id" => $chat->rel,
                        "session_id" => $chat->session,
                        "start_time" => $chat->timestamp,
                        "last_active_time" => $chat->last_active_timestamp,
                        "chat_box_status" => $chat->chat_box_status,
                        "receiver_open" => $chat->chatbox_receiver_open,
                        "sender_open" => $chat->chatbox_sender_open,
                        "last_chat_id" => $last_chat_id,
                        "first_message_id" => $first_message_id,
                        "send_message" => $send_message,
                        "message_for" => $chat->message_for,
                        "message_count" => $chat->message_count,
                        "author_id" => $author_page_id
                    );
                    }
                    else{
                          $value[] = array(
                        "id" => (isset($chat->id)) ? $chat->id : $chat->id,
                        "name" => $chat->name,
                        "email" => $chat->email,
                        "chat_status" => $chat->status,
                        "post_id" => $chat->chat_box_id,
                        "post_author_id" => $chat->rel,
                        "session_id" => $chat->session,
                        "start_time" => $chat->timestamp,
                        "last_active_time" => $chat->last_active_timestamp,
                        "chat_box_status" => $chat->chat_box_status,
                        "receiver_open" => $chat->chatbox_receiver_open,
                        "sender_open" => $chat->chatbox_sender_open,
                        "last_chat_id" => $last_chat_id,
                        "first_message_id" => $first_message_id,
                        "send_message" => $send_message,
                        "message_for" => $chat->message_for,
                        "message_count" => $chat->message_count,
                        
                    );
                    }                              
                    $post_ids[] = $chat->chat_box_id;
                }
            }
        }
        /* Show hide chat here */
        $allowed_pages = whizzChat_allowed_pages($page_id);

        if($author_page_id  != ""){

           $allowed_pages   =   true; 
        }
        if (!isset($chats_data['id']) && $allowed_pages == true) {
            global $wp_query;
            if (isset($whizzchat_between) && $whizzchat_between == '1') {
                return; // return null if admin.
            } else {
                $current_post_id = isset($wp_query->post->ID)  ? $wp_query->post->ID : "";
            }            
            if($page_id != ""){              
                $current_post_id   = $page_id;
            }           
            $author_id = get_post_field('post_author', $current_post_id);
            if($author_page_id != ""){
                $author_id   =  $author_page_id;
            }
            $current_user = get_current_user_id();                      
            if (!in_array($current_post_id, $post_ids) && $author_id != $current_user) {
                $prepared_statement = $wpdb->prepare("SELECT `chat_box_id` FROM {$whizz_tbl_sessions} WHERE `session` = %s OR `rel` = %s;",$session_id,$user_id);
                $db_ids = $wpdb->get_col($prepared_statement);

                /*if new chat and request from author page */
                if($author_page_id != ""   &&  $author_page_id != $session_id){
                    
                    $value[] = array(
                    "id" => "0",
                    "name" => '',
                    "email" => '',
                    "chat_status" => 0,
                    "post_id" => $current_post_id,
                    "post_author_id" => $author_page_id,
                    "session_id" => $session_id,
                    "start_time" => '',
                    "last_active_time" => '',
                    "message_for" => 0,
                    "message_count" => 0,
                    "author_id" => $author_page_id,
                    'user_to_user' => $author_page_id,
                );  
                } 
                else{               
                    $value[] = array(
                    "id" => "0",
                    "name" => '',
                    "email" => '',
                    "chat_status" => 0,
                    "post_id" => $current_post_id,
                    "post_author_id" => $author_id,
                    "session_id" => $session_id,
                    "start_time" => '',
                    "last_active_time" => '',
                    "message_for" => 0,
                    "message_count" => 0
                );
                    
                }            
            }
        }
        return $value;
    }

}


if (!function_exists('whizzChat_chat_boxes_admin')) {

    function whizzChat_chat_boxes_admin($chats_data = array(), $extra_data = array()) {
        global $wpdb, $whizz_tbl_sessions, $whizzChat_options;

        $filter = '';
        $user_id = get_current_user_id();

        $post_ids = array();
        $session_id = whizzChat::session_id();
        $value = $first_chat_id = array();
        $inner = false;
        $chats = array();
        $direct_load = ( isset($chats_data['direct_load']) && $chats_data['direct_load'] == true ) ? false : true;

        if (isset($chats_data) && $chats_data && $direct_load) {

            if (isset($chats_data['id']) && $chats_data['id'] != "") {

                $qry = "SELECT * FROM $whizz_tbl_sessions WHERE `id` = %s ORDER BY id DESC LIMIT 1";
                $chats = $wpdb->get_results($wpdb->prepare($qry, $chats_data['id']));
            } else {
                $ids = array();
                $postIds = array();
                if (isset($chats_data)) {
                    foreach ($chats_data as $val) {
                        $ids[] = ($val['chat_id']);
                        $postIds[] = $val['post_id'];
                    }
                }

                if (count($ids) > 0) {
                    $ids = implode(',', $ids);
                    $postIds = implode(',', $postIds);
                    $qry = "SELECT * FROM $whizz_tbl_sessions WHERE (`session` = %s OR `rel` = %s ) AND `id` IN ($ids) AND `chat_box_id` IN ($postIds) AND `chatbox_sender_open` = %s ORDER BY ID DESC";
                    $chats = $wpdb->get_results($wpdb->prepare($qry, $session_id, $user_id, 1));
                }
            }
        } else {
            $qry = "SELECT * FROM $whizz_tbl_sessions WHERE ( (`chatbox_sender_open` = %s) OR (`rel` = %s AND `chatbox_receiver_open` = %s) ) AND `status` = %s  ORDER BY ID DESC";
            $chats = $wpdb->get_results($wpdb->prepare($qry, 1, $user_id, 1, 1));
        }


        $chat_box_open = 1;
        if (isset($chats_data['chat_box_open']) && $chats_data['chat_box_open'] != true) {
            $chat_box_open = 0;
        }

        $last_chat_id = '';
        if (isset($chats_data['last_chat_id']) && $chats_data['last_chat_id'] != "") {
            $last_chat_id = $chats_data['last_chat_id'];
        }
        $send_message = '';
        if (isset($chats_data['send_message']) && $chats_data['send_message'] != "") {
            $send_message = $chats_data['send_message'];
        }
        $max_chat_box = $whizzChat_options["whizzChat-max-chatbox"];
        $max_chat_box_window = isset($max_chat_box) && $max_chat_box != '' && $max_chat_box != 0 ? $max_chat_box : 3;
        if (!(count($chats) <= $max_chat_box_window)) {
            $chats = array_slice($chats, 0, $max_chat_box_window);  // restrict to show only required num of chat box
        }
        if (isset($chats) && count($chats) > 0) {
            foreach ($chats as $chat) {
                if (isset($chat->id)) {
                    $first_message_id = '';
                    if (isset($extra_data['message_ids'])) {
                        foreach ($extra_data['message_ids'] as $val) {
                            $get_message_id = (isset($val['get_message_id'])) ? $val['get_message_id'] : '';
                            if ($val['chat_id'] == $chat->id) {
                                $first_message_id = $get_message_id;
                            }
                        }
                    }

                    $value[] = array(
                        "id" => (isset($chat->id)) ? $chat->id : $chat->id,
                        "name" => $chat->name,
                        "email" => $chat->email,
                        "chat_status" => $chat->status,
                        "post_id" => $chat->chat_box_id,
                        "post_author_id" => $chat->rel,
                        "session_id" => $chat->session,
                        "start_time" => $chat->timestamp,
                        "last_active_time" => $chat->last_active_timestamp,
                        "chat_box_status" => $chat->chat_box_status,
                        "receiver_open" => $chat->chatbox_receiver_open,
                        "sender_open" => $chat->chatbox_sender_open,
                        "last_chat_id" => $last_chat_id,
                        "first_message_id" => $first_message_id,
                        "send_message" => $send_message,
                        "message_for" => $chat->message_for,
                        "message_count" => $chat->message_count
                    );
                    $post_ids[] = $chat->chat_box_id;
                }
            }
        }

        /* Show hide chat here */
        $allowed_pages = whizzChat_allowed_pages();
        if (!isset($chats_data['id']) && $allowed_pages == true) {
            $current_post_id = get_the_ID();
            $author_id = get_post_field('post_author', $current_post_id);
            $current_user = get_current_user_id();

            if (!in_array($current_post_id, $post_ids) && $author_id != $current_user) {
                $value[] = array(
                    "id" => 0,
                    "name" => '',
                    "email" => '',
                    "chat_status" => 0,
                    "post_id" => $current_post_id,
                    "post_author_id" => $author_id,
                    "session_id" => $session_id,
                    "start_time" => '',
                    "last_active_time" => '',
                    "message_for" => 0,
                    "message_count" => 0
                );
            }
        }
        return $value;
    }

}

if (!function_exists('whizzChat_return')) {

    function whizzChat_return($data = '') {

        return $data;
    }

}

if (!function_exists('whizzChat_get_image_size_links')) {

    function whizzChat_get_image_size_links($attachment_id = '') {
        $links = array();
        /* Get the intermediate image sizes and add the full size to the array. */
        $sizes = array(); //get_intermediate_image_sizes();
        $sizes[] = 'thumbnail';
        $sizes[] = 'full';
        foreach ($sizes as $size) {
            if ('thumbnail' == $size || 'full' == $size) {
                $image = wp_get_attachment_image_src($attachment_id, $size);
                if (!empty($image)) {
                    $links[$size] = $image[0];
                }
            }
        }
        return $links;
    }

}

add_filter('whizz_chat_author_rel_id', 'whizz_chat_author_rel_id_callback', 10, 2);

function whizz_chat_author_rel_id_callback($author_rel_id = 0, $type = '') {
    global $whizzChat_options;

    $whizzChat_options = get_option('whizz-chat-options');
    $whizzChat_between = $whizzChat_options['whizzChat-chat-between'];
    if (isset($whizzChat_between) && $whizzChat_between == 2) {
        return $author_rel_id;
    }
    $whizzChat_admin_val = $whizzChat_options['whizzChat-admin-value'];
    if ($whizzChat_between == 1 && $whizzChat_admin_val != '') { // for admin only
        $author_rel_id = $whizzChat_admin_val;
    }
    return $author_rel_id;
}

add_action('wp_footer', 'whizz_chat_realtime_comm_enabled');
add_action('admin_footer', 'whizz_chat_realtime_comm_enabled');

function whizz_chat_realtime_comm_enabled() {
    global $whizzChat_options;

    $whizzChat_options = get_option('whizz-chat-options');
    $whizzChat_comm_between = $whizzChat_options['whizzChat-comm-type'];
    $whizzChat_chat_between = $whizzChat_options['whizzChat-chat-between'];
    $whizzChat_record_limit = isset($whizzChat_options['whizzChat-record-limit']) && $whizzChat_options['whizzChat-record-limit'] != '' ? $whizzChat_options['whizzChat-record-limit'] : 10;


    $pop_up_disabled   =    isset($whizzChat_options['whizzChat-shortcode-allow'])  ?  $whizzChat_options['whizzChat-shortcode-allow']  :  false;

    if($pop_up_disabled){
         echo do_shortcode('[whizchat_shortcode]');
    }

    echo '<input type="hidden" id="whizz-chat-live" value="' . $whizzChat_comm_between . '"/>';
    echo '<input type="hidden" id="whizz-chat-between" value="' . $whizzChat_chat_between . '"/>';
    if (isset($whizzChat_chat_between) && ($whizzChat_chat_between == '1' || $whizzChat_chat_between == '2')) {
        echo '<input type="hidden" id="whizz-chat-admin" value="' . is_admin() . '"/>';
    } else {
        echo '<input type="hidden" id="whizz-chat-admin" value=""/>';
    }
    if (is_admin()) {
        $scren_val = 'admin';
    } else {
        $scren_val = 'user';
    }
    $whizz_sound_switch = 'on';
    if (isset($_COOKIE['whizz_sound_enable']) && $_COOKIE['whizz_sound_enable'] != '') {
        $whizz_sound_switch = $_COOKIE['whizz_sound_enable'];
    }

    $whizzchat_dashboard = 'disable';
    if (is_page_template('template-whizzchat.php')) {
        $whizzchat_dashboard = 'active';
    }

    echo '<input type="hidden" id="whizzchat-dashboard" value="' . $whizzchat_dashboard . '"/>';
    echo '<input type="hidden" id="whizz_sound_enable" value="' . $whizz_sound_switch . '"/>';
    echo '<input type="hidden" id="whizz_sound_time" value="' . $whizzChat_record_limit . '"/>';
    echo '<input type="hidden" id="whizzchat-screen" value="' . $scren_val . '"/>';
    $whizzchat_sound = plugins_url('whizz-chat') . '/assets/images/short-marimba-notification-ding';
    ?>
    <a id="whizzchat-notify" onclick="whizzchat_playSound('<?php echo whizzChat_return($whizzchat_sound); ?>');" style="display:none;">Play</a><div style="display:none;" id="sound"></div>
    <?php
    $u_id = whizzChat::session_id();
    echo '<input type="hidden" id="whizzchat-current-userid" value="' . $u_id . '"/>';
}

if (!function_exists('whizzchat_words_count')) {

    function whizzchat_words_count($contect = '', $limit = 180) {
        $string = '';
        $contents = strip_tags(strip_shortcodes($contect));
        $contents = whizzchat_removeURL($contents);
        $removeSpaces = str_replace(" ", "", $contents);
        $contents = preg_replace("~(?:\[/?)[^/\]]+/?\]~s", '', html_entity_decode($contents, ENT_QUOTES));
        if (strlen($removeSpaces) > $limit) {
            return mb_substr(str_replace("&nbsp;", "", $contents), 0, $limit) . '...';
        } else {
            return str_replace("&nbsp;", "", $contents);
        }
    }

}
if (!function_exists('whizzchat_removeURL')) {

    function whizzchat_removeURL($string) {
        return preg_replace("/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i", '', $string);
    }

}    