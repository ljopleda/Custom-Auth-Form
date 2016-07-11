<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class User_List extends WP_List_Table {

	public function __construct() {
		parent::__construct( [
			'singular' => __( 'User', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Users', 'sp' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );
	}


	/**
	 * Retrieve customers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_users( $per_page = 5, $page_number = 1 ) {

		global $wpdb;

		$sql = "SELECT u.display_name,u.ID,u.user_email,ud.fullname FROM {$wpdb->base_prefix}users AS u, {$wpdb->prefix}user_details AS ud";
	  $sql .= " WHERE u.ID = ud.user_id";

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}


	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_customer( $id ) {
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->base_prefix}users",
			[ 'ID' => $id ],
			[ '%d' ]
		);
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->base_prefix}users";

		return $wpdb->get_var( $sql );
	}


	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No Users Avaliable.', 'sp' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'display_name':
			case 'user_email':
			case 'fullname':
			case 'ID':
				return $item[ $column_name ];
			case 'action':
				return '<a href="#">Deactivate</a>';
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
		);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {

		$delete_nonce = wp_create_nonce( 'sp_delete_customer' );

		$title = '<strong>' . $item['name'] . '</strong>';

		$actions = [
			'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce )
		];

		return $title . $this->row_actions( $actions );
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'cb'      => '<input type="checkbox" />',
      'ID'    => __( 'ID', 'sp' ),
			'display_name'    => __( 'Username', 'sp' ),
			'fullname'    => __( 'Full Name', 'sp' ),
			'user_email' => __( 'Email', 'sp' ),
			'action' => 'Action'
		];

		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'display_name' => array( 'display_name', true ),
			'ID' => array( 'ID', false ),
			'fullname' => array( 'Full Name', false )
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete'
		];

		return $actions;
		return [];
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'users_per_page', 10 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = self::get_users( $per_page, $current_page );
	}

	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'sp_delete_customer' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_customer( absint( $_GET['customer'] ) );

		                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		                // add_query_arg() return the current url
		                wp_redirect( esc_url_raw(add_query_arg()) );
				exit;
			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_customer( $id );

			}

			// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		        // add_query_arg() return the current url
		        wp_redirect( esc_url_raw(add_query_arg()) );
			exit;
		}
	}

}
class SP_Plugin {

	static $instance;
	public $userlist;

	public function __construct() {
		add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
    add_action('admin_init', [$this, 'admin_init']);
		add_action( 'admin_menu', [ $this, 'plugin_menu' ] );
	}

	public function admin_init(){
		register_setting('caf-recaptcha-settings', 'caf_recaptcha_site_key');
		register_setting('caf-recaptcha-settings', 'caf_recaptcha_client_key');
	}

	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	public function plugin_menu() {

		$hook = add_options_page(
			'Custom Auth Form',
			'Custom Auth Form',
			'manage_options',
			'user-list',
			[ $this, 'plugin_settings_page' ]
		);

		add_action( "load-$hook", [ $this, 'screen_option' ] );

	}

	public function plugin_settings_page() {
		?>
		<div class="wrap">
			<h2>Custom Auth Form</h2>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->userlist->prepare_items();
								$this->userlist->display(); ?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
			<form method="post" action="options.php">
		    <?php settings_fields( 'caf-recaptcha-settings' ); ?>
		    <?php do_settings_sections( 'caf-recaptcha-settings' ); ?>
		    <table class="form-table">
		      <tr valign="top">
		        <th scope="row" colspan="2">Google Recaptcha Settings</th>
		      </tr>
		      <tr>
		        <td width="300px">
		          <label>Google Secret Key</label><br>
		          <small>Used by the server to interact with google.</small>
		        </td>
		        <td><input type="text" name="caf_recaptcha_site_key" value="<?php echo esc_attr( get_option('caf_recaptcha_site_key') ); ?>" / style="min-width:300px;"></td>
		      </tr>
		      <tr>
		        <td width="300px">
		          <label>Google Site Key</label><br>
		          <small>Used by the browser to interact with the users</small>
		        </td>
		        <td><input type="text" name="caf_recaptcha_client_key" value="<?php echo esc_attr( get_option('caf_recaptcha_client_key') ); ?>" / style="min-width:300px;"></td>
		      </tr>
		    </table>
		    <?php submit_button(); ?>
		  </form>
		</div>
	<?php
	}

	public function screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => 'User per page',
			'default' => 5,
			'option'  => 'users_per_page'
		];

		add_screen_option( $option, $args );

		$this->userlist = new User_List();
	}

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

New SP_Plugin();
