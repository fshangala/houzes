<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Chats_List_Table extends WP_List_Table {

    function get_columns() {
        $columns = array(
            'name'=>'Name',
            'last_active_timestamp'=>'Last Active',
            'message_count'=>'Messages'
        );
        return $columns;
    }
    function get_sortable_columns() {
        $sortable_columns = array(
            'name'=>array('name',false),
            'last_active_timestamp'=>array('last_active_timestamp',false)
        );
        return $sortable_columns;
    }
    function prepare_items() {
        global $wpdb;
        global $whizz_tbl_sessions;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns,$hidden,$sortable);

        $orderby = (isset($_GET['orderby'])) ? esc_sql( $_GET['orderby'] ) : 'last_active_timestamp';
        $order = ( isset( $_GET['order'] ) ) ? esc_sql( $_GET['order'] ) : 'asc';

        $query = "SELECT * FROM $whizz_tbl_sessions ORDER BY $orderby $order;";
        $this->items = $wpdb->get_results( $query, ARRAY_A  );
    }

    function column_default($item,$column_name) {
        return $item[$column_name];
    }
    function column_name($item) {
        $query_args = array(
            'page'=>$_REQUEST['page'],
            'view_session'=>$item['id']
        );
        if (isset($_GET['orderby'])) {
            $query_args['orderby']=$_GET['orderby'];
        }
        if (isset($_GET['order'])) {
            $query_args['order']=$_GET['order'];
        }
        $view_session_link = esc_url( add_query_arg( $query_args, "" ) );
        $actions = array(
            'view' => '<a href="'.$view_session_link.'">View Messages</a>'
        );
        return sprintf('%1$s %2$s',$item['name'],$this->row_actions($actions));
    }
}

class Messages_List_Table extends WP_List_Table {
    function get_columns() {
        return array(
            'fromname'=>'From',
            'message'=>'Message',
            'timestamp'=>'TimeStamp'
        );
    }
    function get_sortable_columns() {
        return array();
    }
    function prepare_items(){
        global $wpdb;
        global $whizz_tblname_chat_message;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns,$hidden,$sortable);
        
        $session_id = (isset($_GET['view_session'])) ? $_GET['view_session'] : 0;

        $query = "SELECT * FROM $whizz_tblname_chat_message WHERE session_id = $session_id ORDER BY timestamp asc;";
        try {
            $this->items = $wpdb->get_results( $query, ARRAY_A  );
        } catch (\Throwable $th) {
            $this->items = [];
        }
        
    }

    function column_default($item,$column_name){
        return $item[$column_name];
    }
}

class Houzez_Menu {

    public $slug = 'houzez-real-estate';
    public $capability = 'edit_posts';
    public static $instance;

    public function __construct() {

        add_action( 'admin_menu', array( $this, 'setup_menu' ) );
    }

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setup_menu() {

        add_menu_page( 'Chats', 'Chats', 'manage_options', 'chats', function(){
            echo '<h2>Chat Sessions</h2>';
            $chatList = new Chats_List_Table();
            $chatList->prepare_items();
            echo $chatList->display();
            
            echo '<h2>Chat Session Messages</h2>';
            $chatMessages = new Messages_List_Table();
            $chatMessages->prepare_items();
            echo $chatMessages->display();
        }, 'dashicons-format-chat', 2 );

        $submenus = array();

        $menu_name = apply_filters('houzez_realestate_menu_label', esc_html__( 'Real Estate', 'houzez-theme-functionality' ));
        add_menu_page(
            $menu_name,
            $menu_name,
            $this->capability,
            $this->slug,
            '',
            HOUZEZ_PLUGIN_IMAGES_URL. 'houzez-icon.svg',
            '6'
        );

        $submenus['addnew'] = array(
            $this->slug,
            esc_html__( 'Add New Property', 'houzez-theme-functionality' ),
            esc_html__( 'New Property', 'houzez-theme-functionality' ),
            $this->capability,
            'post-new.php?post_type=property',
        );

        // Property post type taxonomies
        $taxonomies = get_object_taxonomies( 'property', 'objects' );
        foreach ( $taxonomies as $single_tax ) {
            $submenus[ $single_tax->name ] = array(
                $this->slug,
                $single_tax->labels->add_new_item,
                $single_tax->labels->name,
                $this->capability,
                'edit-tags.php?taxonomy=' . $single_tax->name . '&post_type=property',
            );
        }

        if(houzez_check_post_types_plugin('houzez_agencies_post')) {
            $submenus['houzez_agencies'] = array(
                $this->slug,
                esc_html__( 'Agencies', 'houzez-theme-functionality' ),
                esc_html__( 'Agencies', 'houzez-theme-functionality' ),
                $this->capability,
                'edit.php?post_type=houzez_agency',
            );
        }

        if(houzez_check_post_types_plugin('houzez_agents_post')) {
            $submenus['houzez_agents'] = array(
                $this->slug,
                esc_html__( 'Agents', 'houzez-theme-functionality' ),
                esc_html__( 'Agents', 'houzez-theme-functionality' ),
                $this->capability,
                'edit.php?post_type=houzez_agent',
            );
        }

        if(houzez_check_post_types_plugin('houzez_partners_post')) {
            $submenus['houzez_partners'] = array(
                $this->slug,
                esc_html__( 'Partners', 'houzez-theme-functionality' ),
                esc_html__( 'Partners', 'houzez-theme-functionality' ),
                $this->capability,
                'edit.php?post_type=houzez_partner',
            );
        }

        $submenus['houzez_reviews'] = array(
            $this->slug,
            esc_html__( 'Reviews', 'houzez-theme-functionality' ),
            esc_html__( 'Reviews', 'houzez-theme-functionality' ),
            $this->capability,
            'edit.php?post_type=houzez_reviews',
        );

        if(houzez_check_post_types_plugin('houzez_packages_post')) {
            $submenus['houzez_packages'] = array(
                $this->slug,
                esc_html__( 'Packages', 'houzez-theme-functionality' ),
                esc_html__( 'Packages', 'houzez-theme-functionality' ),
                $this->capability,
                'edit.php?post_type=houzez_packages',
            );
        }

        if(houzez_check_post_types_plugin('houzez_invoices_post')) {
            $submenus['houzez_invoice'] = array(
                $this->slug,
                esc_html__( 'Invoices', 'houzez-theme-functionality' ),
                esc_html__( 'Invoices', 'houzez-theme-functionality' ),
                $this->capability,
                'edit.php?post_type=houzez_invoice',
            );
        }


        if(houzez_check_post_types_plugin('houzez_packages_info_post')) {
            $submenus['user_packages'] = array(
                $this->slug,
                esc_html__( 'Packages Info', 'houzez-theme-functionality' ),
                esc_html__( 'Packages Info', 'houzez-theme-functionality' ),
                $this->capability,
                'edit.php?post_type=user_packages',
            );
        }

        // Add filter for third party scripts
        $submenus = apply_filters( 'houzez_admin_realestate_menu', $submenus );

        if ( $submenus ) {
            foreach ( $submenus as $sub_menu ) {
                call_user_func_array( 'add_submenu_page', $sub_menu );
            }
        } // end $submenus
    }

}