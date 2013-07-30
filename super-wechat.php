<?php
/*
 * Plugin Name: Super Wechat
 * Plugin URI: http://angelawang.me/
 * Description: All You Need For Wechat
 * Version: 1.0
 * Author: Angela Wang
 * Author URI: http://angelawang.me/
 * License: GPL2
 *
 * Copyright 2013 Angela Wang (email : idu.angela@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/*
	TODO:
	1- wechat interface
		- Menu Module
	2- wordpress interface
		- auto-replier matrix
 */

class Super_Wechat {

	function __construct() {

		$menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
		$menu_options = array();
		foreach( $menus as $menu ) {
			array_push( $menu_options, array(
				"id"	=> $menu->name,
				"label"	=> $menu->name
			) );
		}
		unset($menus);

		$cats = get_categories( array(
			'type'		=> 'post',
			'order'		=> 'ASC',
			'orderby'	=> 'name',
			'taxonomy'	=> 'category',
			'hide_empty'=> true,
		) );
		$cat_options = array();
		foreach ($cats as $cat) {
			array_push( $cat_options, array(
				"id"	=> $cat->term_taxonomy_id,
				"label"	=> $cat->category_nicename
			) );
		}
		unset($cats);

		$authors = get_users( array(
			/*'role'		=> 'author',*/
			'order'		=> 'ASC',
			'orderby'	=> 'nicename',
		) );
		$author_options = array();
		foreach ($authors as $author) {
			array_push( $author_options, array(
				"id"	=> $author->ID,
				"label"	=> $author->user_nicename
			) );
		}
		unset($authors);


		$this->sections = array(
			"main_configuration"	=> __("Super Wechat Configuration", "super_wechat"),
			"menu_configuration"	=> __("Module - Menu Configuration", "super_wechat"),
			"repost_configuration"	=> __("Module - Repost By Link Configuration", "super_wechat"),
			"reply_configuration"	=> __("Module - Auto Reply Configuration", "super_wechat"),
		);

		$this->options 	= array(
			array(
				"id"		=> "token",
				"type"		=> "text",
				"label"		=> __("Token", "super_wechat"),
				"section"	=> "main_configuration",
				"default"	=> "",
			),
			array(
				"id"		=> "access_token",
				"type"		=> "text",
				"label"		=> __("Access Token", "super_wechat"),
				"section"	=> "main_configuration",
				"default"	=> "",
			),
			array(
				"id"		=> "feedback",
				"type"		=> "text",
				"label"		=> __("Success Message", "super_wechat"),
				"section"	=> "main_configuration",
				"default"	=> "",
			),
			array(
				"id"		=> "modules",
				"type"		=> "checkbox",
				"label"		=> __("Modules", "super_wechat"),
				"values"	=> array(
					array(
						"id"	=> "menu",
						"label"	=> __("Menu", "super_wechat")
					),
					array(
						"id"	=> "reply",
						"label"	=> __("Auto Reply", "super_wechat")
					),
					array(
						"id"	=> "repost",
						"label"	=> __("Repost By Link", "super_wechat")
					)
				),
				"section"	=> "main_configuration",
				"default"	=> array(),
			),
			array(
				"id"		=> "menu",
				"type"		=> "dropdown",
				"label"		=> __("Wechat Menu", "super_wechat"),
				"values"	=> $menu_options,
				"section"	=> "menu_configuration",
				"default"	=> "",
			),
			array(
				"id"		=> "reply",
				"type"		=> "matrix",
				"label"		=> __("Auto Reply Messages", "super_wechat"),
				"section"	=> "reply_configuration",
				"default"	=> array()
			),
			array(
				"id"		=> "repost_category",
				"type"		=> "dropdown",
				"label"		=> __("Default Posting Category", "super_wechat"),
				"values"	=> $cat_options,
				"section"	=> "repost_configuration",
				"default"	=> "",
			),
			array(
				"id"		=> "repost_status",
				"type"		=> "dropdown",
				"label"		=> __("Default Post Status", "super_wechat"),
				"values"	=> array(
					array(
						"id"	=> "publish",
						"label"	=> __("Publish", "super_wechat"),
					),
					array(
						"id"	=> "draft",
						"label"	=> __("Draft", "super_wechat"),
					),
					array(
						"id"	=> "pending",
						"label"	=> __("Pending", "super_wechat")
					)
				),
				"section"	=> "repost_configuration",
				"default"	=> "draft",
			),
			array(
				"id"		=> "repost_author",
				"type"		=> "dropdown",
				"label"		=> __("Default Author", "super_wechat"),
				"values"	=> $author_options,
				"section"	=> "repost_configuration",
				"default"	=> "",
			),
		);

		$this->default = array();

		foreach ($this->options as $option) {
			$this->default[$option["id"]] = $option["default"];
		}

		$this->settings_option_name 	= "super_wechat_settings";


		add_action( "admin_menu", array($this, "admin_menu_callback") );

		load_plugin_textdomain( "super_wechat", false, plugin_dir_path( __FILE__ ) . 'languages/' );


	}

	function admin_menu_callback() {

		add_options_page( __('Super Wechat', "super_wechat"), __('Super Wechat', "super_wechat"), 'manage_options', 'super-wechat', array($this, "options_page_callback") );

	}

	function options_page_callback() {

		//Let's make a copy instead so we can keep popping!
		$temp_options 	= $this->options;
		$temp_values 	= get_option( $this->settings_option_name );
		$temp_values	= !empty( $temp_values ) ? $temp_values : $this->default;

		if( !empty( $_POST["_wpnonce"] ) ) {

			//Make Update
			foreach($_POST as $key => $value) {
				//in_array( $key, array("_wpnonce", "_wp_http_referer") )
				if( preg_match( '/^_/', $key) ) continue;

				$temp_values[$key] = $value;
			}

			update_option( $this->settings_option_name, $temp_values );

		}

		?>
		<table class="form-table">
			<form method="POST">
			<?php
			wp_nonce_field();

			foreach( $this->sections as $id => $label ) {

				$section_name = split("_", $id);
				if( "main_configuration" == $id ||
					in_array( $section_name[0], $temp_values["modules"] ) ) {

					?>
					<tr>
						<th colspan="2"><h2><?php echo $label; ?></h2></th>
					</tr>

					<?php

					foreach( $temp_options as $index => $option ) {

						if( $id == $option["section"] ) {

							//Belongs to this section - Begin Processing
							?>
							<tr>
								<th><label for="<?php echo $option["id"]; ?>"><?php echo $option["label"]; ?></label></th>
								<td>
							<?php

							if( "text" == $option["type"] ) {
								?>
								<input type="text" name="<?php echo $option["id"]; ?>" id="<?php echo $option["id"]; ?>" value="<?php echo $temp_values[$option["id"]] ?>">
								<?php
							} else if( "checkbox" == $option["type"] ) {

								foreach( $option["values"] as $value ) {
									?>
									<input
										type="checkbox"
										name="<?php echo $option["id"]; ?>[]"
										value="<?php echo $value["id"]; ?>"
										<?php 
											if( in_array( $value["id"], $temp_values[$option["id"]] ) ) echo 'checked="checked"'; ?>
									> <?php echo $value["label"]; ?>
									<?php
								}

							} else if( "dropdown" == $option["type"] ) {

								?>
								<select name="<?php echo $option["id"]; ?>">
									<option value=""></option>
								<?php
								foreach( $option["values"] as $value ) {
									?>
									<option
										value="<?php echo $value["id"]; ?>"
										<?php selected( $value["id"], $temp_values[$option["id"]] ); ?>
									><?php echo $value["label"]; ?></option>

									<?php
								}
								?>
								</select>
								<?php

							} else if( "matrix" == $option["type"] ) {

								?>
								<button class="button matrix_btn"><?php _e("Add New Rule", "super_wechat"); ?></button>
								<table class="form-table matrix">
									<tbody>
									</tbody>
								</table>
								<?php

							}

							?>
								</td>
							</tr>
							<?php

							//Pop the option
							unset( $option[$index] );

						}

						//Let's move on
						continue;

					}

				}

			}

			?>
			<tr><td colspan="2"><?php submit_button(); ?></td></tr>
			</form>
		</table>
		<?php

	}

}

$wechat = new Super_Wechat();
?>