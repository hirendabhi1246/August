<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Users_List extends WP_List_Table
{

    /** Class constructor */
    public function __construct()
    {

        parent::__construct([
            'singular' => __('User', 'referral'), //singular name of the listed records
            'plural'   => __('Users', 'referral'), //plural name of the listed records
            'ajax'     => false //does this table support ajax?
        ]);
    }

    /**
     * Retrieve users data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function get_users($per_page = 5, $page_number = 1)
    {

        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}users";

        if (!empty($_REQUEST['orderby'])) {
            $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
            $sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result;
    }


    /**
     * Delete a user record.
     *
     * @param int $id user ID
     */
    public static function delete_users($id)
    {
        global $wpdb;

        $wpdb->delete(
            "{$wpdb->prefix}users",
            ['ID' => $id],
            ['%d']
        );
    }


    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count()
    {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}users";

        return $wpdb->get_var($sql);
    }


    /** Text displayed when no user data is available */
    public function no_items()
    {
        _e('No users avaliable.', 'referral');
    }


    public function column_default($item, $column_name)
    {
        // switch ( $column_name ) {
        //     case 'email':
        //     case 'role':
        //         return $item[ $column_name ];
        //     default:
        //         return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        // }

        switch ($column_name) {
            case 'name':
                return $item['user_nicename'];
            case 'email':
                return $item['user_email'];
            case 'referral-user-name':
                return "--";
            case 'action':
                return "--";
            default:
                return print_r($item, true);
        }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />',
            $item['ID']
        );
    }


    /**
     * Method for name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_name($item)
    {

        $delete_nonce = wp_create_nonce('referral_delete_users');

        $title = '<strong>' . $item['user_nicename'] . '</strong>';

        $actions = [
            'delete' => sprintf('<a href="?page=%s&action=%s&users=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['ID']), $delete_nonce)
        ];

        return $title . $this->row_actions($actions);
    }


    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns()
    {
        $columns = [
            'cb'      => '<input type="checkbox" />',
            'name'    => __('Username', 'referral'),
            'email' => __('Email', 'referral'),
            'referral-user-name' => __('Referral User name', 'referral'),
            'action'    => __('Action', 'referral')
        ];

        return $columns;
    }


    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'name' => array('name', true),
            'email' => array('email', false)
        );

        return $sortable_columns;
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions()
    {
        $actions = [
            'bulk-delete' => 'Delete'
        ];

        return $actions;
    }


    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items()
    {

        $this->_column_headers = [
            $this->get_columns(),
            [], // hidden columns
            $this->get_sortable_columns(),
            $this->get_primary_column_name(),
        ];

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page('users_per_page', 5);
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args([
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page //WE have to determine how many items to show on a page
        ]);

        $this->items = self::get_users($per_page, $current_page);

        // $example_data = array(
        //     array('ID' => 1,'booktitle' => 'Quarter Share', 'author' => 'Rakesh','isbn' => '978-0982514542')
        //     ,array('ID' => 2,'booktitle' => 'Quarter Share', 'author' => 'Rakesh','isbn' => '978-0982514542')
        // );
        // $columns = $this->get_columns();
        // $hidden = array();
        // $sortable = $this->get_sortable_columns();
        // $this->_column_headers = array($columns, $hidden, $sortable);
        // $this->items = $example_data;



    }

    public function process_bulk_action()
    {

        //Detect when a bulk action is being triggered...
        if ('delete' === $this->current_action()) {

            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr($_REQUEST['_wpnonce']);

            if (!wp_verify_nonce($nonce, 'referral_delete_users')) {
                die('nonce error');
            } else {
                self::delete_users(absint($_GET['users']));

                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
                // add_query_arg() return the current url
                       echo '<div class="notice notice-success is-dismissible"><p>User Deleted..</p></div>';

            }
        }

        // If the delete bulk action is triggered
        if ((isset($_POST['action']) && $_POST['action'] == 'bulk-delete')
            || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete')
        ) {

            $delete_ids = esc_sql($_POST['bulk-delete']);

            // loop over the array of record IDs and delete them
            foreach ($delete_ids as $id) {
                self::delete_users($id);
            }

            // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
            // add_query_arg() return the current url
                   echo '<div class="notice notice-success is-dismissible"><p>Bulk Deleted..</p></div>';

        }
    }
}
