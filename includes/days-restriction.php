<?php
/**
 * Days Restriction
 *
 * @package WordPress
 */

// If check class exists OR not.
if ( ! class_exists( 'Days_Restriction' ) ) {

	/**
	 * Declare class `Days_Restriction`
	 */
	class Days_Restriction {

		/**
		 * Default options 3 Day.
		 *
		 * @var $options
		 */
		public $options = array(
			'number_of_minute' => (1440 * 3),
			'restrict_limit' => 'minute',
		);

		/**
		 * Error message.
		 *
		 * @var $error_message
		 */
		public $error_message = '';

		/**
		 * Error class
		 *
		 * @var $error_class
		 */
		public $error_class = 'notice notice-error';

		/**
		 * Post type
		 *
		 * @var $post_types
		 */
		public $post_types = array(
			'post',
			'page',
		);

		/**
		 * Calling class `construct`
		 */
		public function __construct() {
			// Get options.
			add_action( 'admin_enqueue_scripts', array( $this, 'ds_admin_scripts' ) );
			add_action( 'admin_menu', array( $this, 'ds_register_menu' ) );
			add_action( 'admin_footer', array( $this, 'ds_dialog_box_popup' ) );
			//add_action( 'admin_notices', array( $this, 'ds_help_notices' ) );
			// Post list table.
			add_filter( 'manage_post_posts_columns', array( $this, 'ds_manage_post_posts_columns' ) );
			add_action( 'manage_post_posts_custom_column', array( $this, 'ds_manage_post_posts_columns_cb' ), 10, 2 );
			add_filter( 'manage_page_posts_columns', array( $this, 'ds_manage_post_posts_columns' ) );
			add_action( 'manage_page_posts_custom_column', array( $this, 'ds_manage_post_posts_columns_cb' ), 10, 2 );
			if ( ! current_user_can( 'administrator' ) ) {
				add_filter( 'post_row_actions', array( $this, 'ds_post_row_actions' ), 10, 2 );
				add_filter( 'page_row_actions', array( $this, 'ds_post_row_actions' ), 10, 2 );
				add_filter( 'get_edit_post_link', array( $this, 'ds_edit_post_link' ), 10, 3 );
				add_action( 'load-post.php', array( $this, 'ds_post_edit_page' ) );
				add_action( 'pre_post_update', array( $this, 'ds_pre_post_update' ) );
			}
		}

		/**
		 * Admin enqueue script.
		 */
		public function ds_admin_scripts() {
			// enqueue these scripts and styles before admin_head
			wp_enqueue_script( 'jquery-ui-dialog' );
			// jquery and jquery-ui should be dependencies, didn't check though.
			wp_enqueue_style( 'wp-jquery-ui-dialog' );
			// Post JS.
			wp_enqueue_style( 'ds-admin', plugin_dir_url( __FILE__ ) . '../assets/css/admin.css' );
			wp_enqueue_script( 'ds-admin', plugin_dir_url( __FILE__ ) . '../assets/js/post.js', array( 'jquery' ), '', true );
			wp_localize_script( 'ds-admin', 'DS',
				array(
					'edit_message' => __( 'You can\'t edit this', 'days-restriction' ),
					'view_message' => __( 'You can\'t view this', 'days-restriction' ),
					'trash_message' => __( 'You can\'t trash this' ),
					'untrash_message' => __( 'You can\'t restore this', 'days-restriction' ),
					'delete_message' => __( 'You can\'t delete this', 'days-restriction' ),
					'preview_message' => __( 'You can\'t preview this', 'days-restriction' ),
					'is_page' => __( 'page', 'days-restriction' ),
					'is_post' => __( 'post', 'days-restriction' ),
				)
			);
		}

		/**
		 * Post dialog box.
		 */
		public function ds_dialog_box_popup() { ?>
			<div id="ds-dialog-box">
				<h2></h2>
			</div>
			<?php
		}

		/**
		 * Register admin menu.
		 */
		public function ds_register_menu() {
			$hook = add_menu_page( __( 'Restrict Data', 'days-restriction' ), __( 'Restrict Data', 'days-restriction' ), 'manage_options', 'days-restriction', array( $this, 'ds_pro_render_menu_page' ), 'dashicons-calendar-alt' );
		}

		/**
		 * Render admin menu page.
		 */
		public function ds_pro_render_menu_page() {
			$roles = get_editable_roles();
		?>
			<div class="wrap">
				<h2><?php _e( 'Restriction Panel', 'days-restriction' ); ?></h2>
			</div>
			<div class="ds-pro-features">
				<form method="post" onsubmit="return false;">
					<table class="form-table">
						<tbody>
							<?php foreach ( $roles as $role_key => $all_role ) : ?>
								<tr>
									<td>
										<select disabled>
											<option><?php _e( 'Select Role', 'days-restriction' ); ?></option>
											<?php foreach ( $roles as $key => $role ) : ?>
												<option value="<?php echo $key; ?>"<?php selected( $key, $role_key ); ?>><?php echo $role['name']; ?></option>
											<?php endforeach; ?>
										</select>
										<input type="number" min="1" value="5" readonly>
										<select disabled>
											<option value="minute"><?php _e( 'Minute', 'days-restriction' ); ?></option>
										</select>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php submit_button( __( 'Save', 'days-restriction' ), 'primary', 'ds-submit', 'p' ); ?>
				</form>
				<div class="ds-pro-actions">
					<span class="dashicons dashicons-lock"></span>
					<a href="#" class="button"><?php _e( 'Buy Now Pro', 'days-restriction' ); ?></a>
				</div>
			</div>
			<?php
		}

		/**
		 * Post row actions.
		 *
		 * @param array  $actions Row Action.
		 * @param object $post Post object.
		 * @return array Row action.
		 */
		public function ds_post_row_actions( $actions, $post ) {
			$post_type = get_post_type( $post->ID );
			if ( isset( $this->options['number_of_minute'] ) && in_array( $post_type, $this->post_types ) ) {
				$number_of_minute = $this->options['number_of_minute'];
				$publish_date = $post->post_date;
				$publish_date = strtotime( $publish_date );
				$current_time = current_time( 'timestamp' );
				$minutes = abs( $publish_date - $current_time ) / 60;
				if ( $minutes >= $number_of_minute ) {
					// Edit.
					if ( isset( $actions['edit'] ) ) {
						$actions['edit'] = '<a href="javascript:;" class="ds-invalid" aria-label="' . __( 'Edit', 'days-restriction' ) . ' &#8220;12345&#8221;">' . __( 'Edit', 'days-restriction' ) .'</a>';
					}
					// VC Edit.
					if ( isset( $actions['edit_vc5'] ) ) {
						$actions['edit_vc5'] = '<a href="javascript:;" class="ds-invalid" aria-label="' . __( 'Edit with Visual Composer', 'days-restriction' ) . ' &#8220;12345&#8221;">' . __( 'Edit with Visual Composer', 'days-restriction' ) .'</a>';
					}
					// Quick edit.
					if ( isset( $actions['inline hide-if-no-js'] ) ) {
						$actions['inline hide-if-no-js'] = '<button type="button" class="button-link ds-invalid" aria-label="' . __( 'Quick Edit', 'days-restriction' ) . ' &#8220;12345&#8221; inline" aria-expanded="false">' . __( 'Quick Edit', 'days-restriction' ) . '</button>';
					}
					// View link.
					if ( isset( $actions['view'] ) ) {
						$actions['view'] = '<a href="javascript:;" rel="bookmark" class="ds-invalid ds-invalid-view" aria-label="' . __( 'View', 'days-restriction' ) . ' &#8220;12345&#8221;">' . __( 'View', 'days-restriction' ) . '</a>';
						if ( isset( $_REQUEST['post_status'] ) && 'draft' === sanitize_text_field( $_REQUEST['post_status'] ) ) {
							$actions['view'] = '<a href="javascript:;" rel="bookmark" class="ds-invalid ds-invalid-preview" aria-label="' . __( 'Preview', 'days-restriction' ) . ' &#8220;12345&#8221;">' . __( 'Preview', 'days-restriction' ) . '</a>';
						}
					}
					// Trace link.
					if ( isset( $actions['trash'] ) ) {
						$actions['trash'] = '<a href="javascript:;" class="ds-invalid ds-invalid-trash">' . __( 'Trash', 'days-restriction' ) . '</a>';
					}
					// Untrash post.
					if ( isset( $actions['untrash'] ) ) {
						$actions['untrash'] = '<a href="javascript:;" class="ds-invalid ds-invalid-untrash">' . __( 'Restore', 'days-restriction' ) . '</a>';
					}
					// Delete post.
					if ( isset( $actions['delete'] ) ) {
						$actions['delete'] = '<a href="javascript:;" class="ds-invalid ds-invalid-delete">' . __( 'Delete Permanently', 'days-restriction' ) . '</a>';
					}
				}
			}
			return $actions;
		}

		/**
		 * Manage post column.
		 *
		 * @param array $columns Columns
		 * @return array
		 */
		public function ds_manage_post_posts_columns( $columns ) {
			if ( doing_filter( 'manage_page_posts_columns' ) ) {
				$post_locked = __( 'Page Locked', 'days-restriction' );
			} else {
				$post_locked = __( 'Post Locked', 'days-restriction' );
			}
			$columns['post_locked'] = $post_locked;
			return $columns;
		}

		/**
		 * Display post locked icon.
		 *
		 * @param array $column Post table column.
		 * @param int   $post_id Post ID.
		 */
		public function ds_manage_post_posts_columns_cb( $column, $post_id ) {
			if ( 'post_locked' == $column ) {
				if ( isset( $this->options['number_of_minute'] ) ) {
					$number_of_minute = $this->options['number_of_minute'];
					$publish_date = get_the_time( 'Y-m-d H:i:s', $post_id );
					$publish_date = strtotime( $publish_date );
					$current_time = current_time( 'timestamp' );
					$minutes = abs( $publish_date - $current_time ) / 60;
					if ( current_user_can( 'administrator' ) ) {
						echo '<span class="dashicons dashicons-unlock ds-post-unlocked"></span>';
					} else if ( $minutes >= $number_of_minute ) {
						echo '<span class="dashicons dashicons-lock ds-post-locked"></span>';
					} else {
						echo '<span class="dashicons dashicons-unlock ds-post-unlocked"></span>';
					}
				}
			}
		}

		/**
		 * Post edit link.
		 *
		 * @param string $link Post Edit Link.
		 * @param int $post_id Post ID.
		 * @param string $context Display context.
		 * @return string
		 */
		public function ds_edit_post_link( $link, $post_id, $context ) {
			global $pagenow;
			// If check is post edit OR not.
			if ( 'post.php' === $pagenow ) {
				return $link;
			}
			$post_type = get_post_type( $post_id );
			if ( isset( $this->options['number_of_minute'] ) && in_array( $post_type, $this->post_types ) ) {
				$number_of_minute = $this->options['number_of_minute'];
				$publish_date = get_the_time( 'Y-m-d H:i:s', $post_id );
				$publish_date = strtotime( $publish_date );
				$current_time = current_time( 'timestamp' );
				$minutes = abs( $publish_date - $current_time ) / 60;
				if ( $minutes >= $number_of_minute ) {
					$link = 'javascript:;';
				}
			}
			return $link;
		}

		/**
		 * Edit post
		 */
		public function ds_post_edit_page() {
			$post_id = 0;
			if ( isset( $_REQUEST['post'] ) ) {
				$post_id = (int) $_REQUEST['post'];
			}
			$this->ds_disabled_post_edit( $post_id );
		}

		/**
		 * Disable post edit by post ID.
		 *
		 * @param int $post_id Post ID.
		 */
		public function ds_disabled_post_edit( $post_id ) {
			$post_type = get_post_type( $post_id );
			if ( isset( $this->options['number_of_minute'] ) && in_array( $post_type, $this->post_types ) ) {
				$number_of_minute = $this->options['number_of_minute'];
				$publish_date = get_the_time( 'Y-m-d H:i:s', $post_id );
				$publish_date = strtotime( $publish_date );
				$current_time = current_time( 'timestamp' );
				$minutes = abs( $publish_date - $current_time ) / 60;
				if ( $minutes >= $number_of_minute ) {
					$message = wp_sprintf( __( 'You can\'t edit this %1$s', 'days-restriction' ), get_post_type( $post_id ) );
					wp_die( $message, __( 'Restrict Data', 'days-restriction' ),
						array(
							'back_link' => true
						)
					);
				}
			}
		}

		/**
		 * Save post before.
		 *
		 * @param int $post_id Post ID.
		 */
		public function ds_pre_post_update( $post_id ) {
			$this->ds_disabled_post_edit( $post_id );
		}

		/**
		 * Display admin help notice.
		 */
		public function ds_help_notices() {
			?>
			<div class="notice notice-info is-dismissible ds-help-notice">
				<p><?php echo wp_sprintf( __( 'Dear user, our “Post/Page Edit Restriction Days” plugin has restricted editing your posts and pages to ensure your site’s contents integrity, awesome! If you have a moment, please consider leaving a review on <a href="%1$s" target="_blank">WordPress.org</a> to spread the good word. We really appreciate it! If you have any questions or feedback, leave us a message.', 'days-restriction' ), '#' ); ?></p>
				<p><?php _e( '- Josef', 'days-restriction' ); ?></p>
			</div>
			<?php
		}
	}
}
