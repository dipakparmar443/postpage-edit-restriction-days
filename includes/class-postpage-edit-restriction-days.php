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
			'number_of_minute' => ( 1440 * 3 ),
			'restrict_limit'   => 'minute',
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
			wp_enqueue_style( 'wp-jquery-ui-dialog' );

			$css_file = plugin_dir_path( __FILE__ ) . '../assets/css/admin.css';
			$js_file  = plugin_dir_path( __FILE__ ) . '../assets/js/post.js';

			wp_enqueue_style( 'ds-admin', plugin_dir_url( __FILE__ ) . '../assets/css/admin.css', array(), filemtime( $css_file ) );
			wp_enqueue_script( 'ds-admin', plugin_dir_url( __FILE__ ) . '../assets/js/post.js', array( 'jquery' ), filemtime( $js_file ), true );

			wp_localize_script(
			    'ds-admin',
			    'DS',
			    array(
			        'edit_message'    => __( "You can't edit this", 'postpage-edit-restriction-days' ),
			        'view_message'    => __( "You can't view this", 'postpage-edit-restriction-days' ),
			        'trash_message'   => __( "You can't trash this", 'postpage-edit-restriction-days' ),
			        'untrash_message' => __( "You can't restore this", 'postpage-edit-restriction-days' ),
			        'delete_message'  => __( "You can't delete this", 'postpage-edit-restriction-days' ),
			        'preview_message' => __( "You can't preview this", 'postpage-edit-restriction-days' ),
			        'is_page'         => __( 'page', 'postpage-edit-restriction-days' ),
			        'is_post'         => __( 'post', 'postpage-edit-restriction-days' ),
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
			add_menu_page(
				__( 'Restrict Data', 'postpage-edit-restriction-days' ),
				__( 'Restrict Data', 'postpage-edit-restriction-days' ),
				'manage_options',
				'postpage-edit-restriction-days',
				array( $this, 'ds_pro_render_menu_page' ),
				'dashicons-calendar-alt'
			);
		}

		/**
		 * Render admin menu page.
		 */
		public function ds_pro_render_menu_page() {
			$roles = get_editable_roles();
			?>
			<div class="wrap">
				<h2><?php esc_html_e( 'Restriction Panel', 'postpage-edit-restriction-days' ); ?></h2>
			</div>
			<div class="ds-pro-features">
				<form method="post" onsubmit="return false;">
					<table class="form-table">
						<tbody>
							<?php foreach ( $roles as $role_key => $all_role ) : ?>
								<tr>
									<td>
										<select disabled>
											<option><?php esc_html_e( 'Select Role', 'postpage-edit-restriction-days' ); ?></option>
											<?php foreach ( $roles as $key => $role ) : ?>
												<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $key, $role_key ); ?>><?php echo esc_html( $role['name'] ); ?></option>
											<?php endforeach; ?>
										</select>
										<input type="number" min="1" value="5" readonly>
										<select disabled>
											<option value="minute"><?php esc_html_e( 'Minute', 'postpage-edit-restriction-days' ); ?></option>
										</select>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php submit_button( esc_html__( 'Save', 'postpage-edit-restriction-days' ), 'primary', 'ds-submit', true ); ?>
					<?php wp_nonce_field( 'ds_restrict_save_action', 'ds_restrict_nonce' ); ?>
				</form>
				<div class="ds-pro-actions">
					<span class="dashicons dashicons-lock"></span>
					<a href="#" class="button"><?php esc_html_e( 'Buy Now Pro', 'postpage-edit-restriction-days' ); ?></a>
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
			if ( isset( $this->options['number_of_minute'] ) && in_array( $post_type, $this->post_types, true ) ) {
				$number_of_minute = $this->options['number_of_minute'];
				$publish_date     = strtotime( $post->post_date );
				$current_time     = current_time( 'timestamp' );
				$minutes          = abs( $publish_date - $current_time ) / 60;

				if ( $minutes >= $number_of_minute ) {

					if ( isset( $actions['edit'] ) ) {
						$actions['edit'] = '<a href="javascript:;" class="ds-invalid" aria-label="' . esc_attr__( 'Edit', 'postpage-edit-restriction-days' ) . '">' . esc_html__( 'Edit', 'postpage-edit-restriction-days' ) . '</a>';
					}

					if ( isset( $actions['edit_vc5'] ) ) {
						$actions['edit_vc5'] = '<a href="javascript:;" class="ds-invalid" aria-label="' . esc_attr__( 'Edit with Visual Composer', 'postpage-edit-restriction-days' ) . '">' . esc_html__( 'Edit with Visual Composer', 'postpage-edit-restriction-days' ) . '</a>';
					}

					if ( isset( $actions['inline hide-if-no-js'] ) ) {
						$actions['inline hide-if-no-js'] = '<button type="button" class="button-link ds-invalid" aria-label="' . esc_attr__( 'Quick Edit', 'postpage-edit-restriction-days' ) . '" aria-expanded="false">' . esc_html__( 'Quick Edit', 'postpage-edit-restriction-days' ) . '</button>';
					}

					if ( isset( $actions['view'] ) ) {
						$actions['view'] = '<a href="javascript:;" rel="bookmark" class="ds-invalid ds-invalid-view" aria-label="' . esc_attr__( 'View', 'postpage-edit-restriction-days' ) . '">' . esc_html__( 'View', 'postpage-edit-restriction-days' ) . '</a>';
						if ( isset( $_POST['ds_restrict_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ds_restrict_nonce'] ) ), 'ds_restrict_save_action' ) ) {
							if ( isset( $_REQUEST['post_status'] ) && 'draft' === sanitize_text_field( wp_unslash( $_REQUEST['post_status'] ) ) ) {
								$actions['view'] = '<a href="javascript:;" rel="bookmark" class="ds-invalid ds-invalid-preview" aria-label="' . esc_attr__( 'Preview', 'postpage-edit-restriction-days' ) . '">' . esc_html__( 'Preview', 'postpage-edit-restriction-days' ) . '</a>';
							}
						}
					}

					if ( isset( $actions['trash'] ) ) {
						$actions['trash'] = '<a href="javascript:;" class="ds-invalid ds-invalid-trash">' . esc_html__( 'Trash', 'postpage-edit-restriction-days' ) . '</a>';
					}

					if ( isset( $actions['untrash'] ) ) {
						$actions['untrash'] = '<a href="javascript:;" class="ds-invalid ds-invalid-untrash">' . esc_html__( 'Restore', 'postpage-edit-restriction-days' ) . '</a>';
					}

					if ( isset( $actions['delete'] ) ) {
						$actions['delete'] = '<a href="javascript:;" class="ds-invalid ds-invalid-delete">' . esc_html__( 'Delete Permanently', 'postpage-edit-restriction-days' ) . '</a>';
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
			$post_locked = doing_filter( 'manage_page_posts_columns' ) ? __( 'Page Locked', 'postpage-edit-restriction-days' ) : __( 'Post Locked', 'postpage-edit-restriction-days' );
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
			if ( 'post_locked' === $column ) {
				if ( isset( $this->options['number_of_minute'] ) ) {
					$number_of_minute = $this->options['number_of_minute'];
					$publish_date     = strtotime( get_the_time( 'Y-m-d H:i:s', $post_id ) );
					$current_time     = current_time( 'timestamp' );
					$minutes          = abs( $publish_date - $current_time ) / 60;

					if ( current_user_can( 'administrator' ) ) {
						echo '<span class="dashicons dashicons-unlock ds-post-unlocked"></span>';
					} elseif ( $minutes >= $number_of_minute ) {
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
		 * @param int    $post_id Post ID.
		 * @param string $context Display context.
		 * @return string
		 */
		public function ds_edit_post_link( $link, $post_id, $context ) {
			global $pagenow;

			if ( 'post.php' === $pagenow ) {
				return $link;
			}

			$post_type = get_post_type( $post_id );
			if ( isset( $this->options['number_of_minute'] ) && in_array( $post_type, $this->post_types, true ) ) {
				$number_of_minute = $this->options['number_of_minute'];
				$publish_date     = strtotime( get_the_time( 'Y-m-d H:i:s', $post_id ) );
				$current_time     = current_time( 'timestamp' );
				$minutes          = abs( $publish_date - $current_time ) / 60;

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
		    if ( isset( $_REQUEST['ds_restrict_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['ds_restrict_nonce'] ) ), 'ds_restrict_save_action' ) ) {
		        $post_id = isset( $_REQUEST['post'] ) ? absint( wp_unslash( $_REQUEST['post'] ) ) : 0;
		        $this->ds_disabled_post_edit( $post_id );
		    } else {
		        // Optional: handle failed nonce
		        wp_die( esc_html__( 'Security check failed', 'postpage-edit-restriction-days' ) );
		    }
		}

		/**
		 * Disable post edit by post ID.
		 *
		 * @param int $post_id Post ID.
		 */
		public function ds_disabled_post_edit( $post_id ) {
			$post_type = get_post_type( $post_id );
			if ( isset( $this->options['number_of_minute'] ) && in_array( $post_type, $this->post_types, true ) ) {
				$number_of_minute = $this->options['number_of_minute'];
				$publish_date     = strtotime( get_the_time( 'Y-m-d H:i:s', $post_id ) );
				$current_time     = current_time( 'timestamp' );
				$minutes          = abs( $publish_date - $current_time ) / 60;

				if ( $minutes >= $number_of_minute ) {
					// translators: %s is the post type (e.g., post, page)
					$message = sprintf(
					    /* translators: %s: post type (post, page, etc.) */
					    esc_html__( "You can't view this %s", 'postpage-edit-restriction-days' ),
					    esc_html( $post_type )
					);
					wp_die(
					    wp_kses_post( $message ),
					    esc_html__( 'Restrict Data', 'postpage-edit-restriction-days' ),
					    array(
					        'back_link' => true,
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
		    if ( isset( $_POST['ds_restrict_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ds_restrict_nonce'] ) ), 'ds_restrict_save_action' ) ) {
		        $this->ds_disabled_post_edit( $post_id );
		    }
		}


		/**
		 * Display admin help notice.
		 */
		public function ds_help_notices() {
		    ?>
		    <div class="notice notice-info is-dismissible ds-help-notice">
		        <?php
				/* translators: %1$s: URL to the WordPress.org review page */
				$message = wp_sprintf(
				    /* translators: %1$s: URL to the WordPress.org review page */
				    __( 'Dear user, our "Post/Page Edit Restriction Days" plugin has restricted editing your posts and pages. If you have a moment, please consider leaving a review on <a href="%1$s" target="_blank">WordPress.org</a>.', 'postpage-edit-restriction-days' ),
				    '#'
				);

				echo wp_kses_post( $message );
				?>
				<p><?php esc_html_e( '- Josef', 'postpage-edit-restriction-days' ); ?></p>
		    </div>
		    <?php
		}
	}
}
