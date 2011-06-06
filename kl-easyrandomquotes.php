<?php
/*
Plugin Name: Easy Random Quotes
Plugin URI: http://trepmal.com/plugins/easy-random-quotes/
Description: Insert quotes and pull them randomly into your pages and posts (via shortcodes) or your template (via template tags). 
Author: Kailey Lampert
Version: 1.7
Author URI: http://kaileylampert.com/
*/
/*
    Copyright (C) 2010  Kailey Lampert

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

$kl_easyrandomquotes = new kl_easyrandomquotes( );

class kl_easyrandomquotes {

	function kl_easyrandomquotes( ) {
		add_action( 'admin_menu', array( &$this, 'menu' ) );
		add_action( 'widgets_init', 'kl_easyrandomquotes_load_widget' );
		add_shortcode( 'erq', 'erq_shortcode' );
	}

	function menu( ) {
		$role = get_option( 'kl-permissions', 'administrator' );

		$page = add_submenu_page( 'options-general.php', 'Easy Random Quotes', 'Easy Random Quotes', $role, __FILE__, array( &$this, 'page' ) );
		add_action( 'admin_head-' . $page, array( &$this, 'update' ) );

		$help = '<p><strong>' . 
		__( 'Short code' , 'easy-random-quotes' ) . ': </strong><br />' . 
		__( 'Specific quote' , 'easy-random-quotes' ) . ': <code>' . '[erq id=2]' . '</code><br />' .
		__( 'Random' , 'easy-random-quotes' ) . ': <code>' . '[erq]' . '</code></p><p><strong>' . 
		__( 'Template tag' , 'easy-random-quotes' ) . ': </strong><br />'.
		__( 'Specific quote' , 'easy-random-quotes' ) . ': <code>' . htmlspecialchars( '<?php echo erq_shortcode(array(\'id\' => \'2\')); ?>' ) . '</code><br />'.
		__( 'Random' , 'easy-random-quotes' ) . ': <code>' . htmlspecialchars( '<?php echo erq_shortcode(); ?>' ) . '</code></p><p>' . 
		__( 'Quotes retained when plugin deactivated. Quotes deleted when plugin removed.' , 'easy-random-quotes' ) . '</p>' . 
		'<form method="post"><input type="submit" name="clear" value = "' . __( 'Reset' , 'easy-random-quotes' ) . '" />' .
		'<label for="confirm_delete"><input type="checkbox" name="confirm" id="confirm_delete" value="true" />' . 
		__( 'Check to confirm' , 'easy-random-quotes' ) . '</label><br />'.
		__( 'Reset is just a delete-all option', 'easy-random-quotes' ) . '</form>';
		if ($page)
		add_contextual_help( $page, $help );
	}

	function update() {
	
		if ( isset( $_POST['erq_add'] ) ) {
			$newquote = $_POST['erq_newquote'];

			if ( is_array( get_option( 'kl-easyrandomquotes' ) ) ) {
				$theQuotes = get_option( 'kl-easyrandomquotes' ); //get existing
			} else {
				$theQuotes = array(); //else make sure it's at lease an array
			}

			if( !empty( $newquote ) ) {

				$theQuotes[] = $newquote; //add new

				check_admin_referer( 'easyrandomquotes-update_add' );
				if ( is_array( $theQuotes ) ) {
					update_option( 'kl-easyrandomquotes',$theQuotes ); //successfully updated
					add_action( 'admin_notices', create_function('$a', 'echo "<div class=\"updated\"><p>" . __( "New quote was added" , "easy-random-quotes" ) . "</p></div>";') );
				} else { //uh oh...
					add_action( 'admin_notices', create_function('$a', 'echo "<div class=\"updated\"><p>" . __( "Oops, something didn\'t work" , "easy-random-quotes" ) . "</p></div>";') );
				}
			} else { 
				add_action( 'admin_notices', create_function('$a', 'echo "<div class=\"updated\"><p>" . __( "Nothing added" , "easy-random-quotes" ) . "</p></div>";') );
			}

		}//end if add

		if ( isset( $_POST['erq_import'] ) ) {
			$newquotes = $_POST['erq_newquote'];
			$newquotes = explode( "\n", $newquotes );
			$newquotes = array_filter( $newquotes );
			
			global $erq_count;
			$erq_count = count( $newquotes );

			if ( is_array( get_option( 'kl-easyrandomquotes' ) ) ) {
				$theQuotes = get_option( 'kl-easyrandomquotes' ); //get existing
			} else {
				$theQuotes = array(); //else make sure it's at lease an array
			}
			
			//array_merge messes up the keys, and using the '+' method will skip certain items
			foreach ($newquotes as $newquote) {
				$theQuotes[] = $newquote;
			}
	
			check_admin_referer( 'easyrandomquotes-update_add' );
			if ( is_array( $theQuotes ) ) {
				update_option( 'kl-easyrandomquotes',$theQuotes ); //successfully updated
				add_action( 'admin_notices', 'erq_import_success' );
				function erq_import_success() { global $erq_count; echo "<div class=\"updated\"><p>$erq_count " . __( "new quotes were added" , "easy-random-quotes" ) . "</p></div>";}
			} else { //uh oh...
				add_action( 'admin_notices', create_function('$a', 'echo "<div class=\"updated\"><p>" . __( "Oops, something didn\'t work" , "easy-random-quotes" ) . "</p></div>";') );
			}


		}//end if import

		if ( isset( $_POST[ 'erq_edit' ] ) ) {

			$ids = $_POST[ 'erq_quote' ];
			$dels = isset( $_POST[ 'erq_del' ] ) ? $_POST[ 'erq_del' ] : array();
			$theQuotes =  get_option( 'kl-easyrandomquotes' ) ; //get existing

			foreach( $ids as $id  => $quote ) {
				$theQuotes[$id] = $quote; //update each part with new quote
			}

			if ( is_array( $dels ) ) //if checkmarks are selected...
				foreach( $dels as $id  => $quote ) {
					unset( $theQuotes[$id] ); //delete selected...
				}

			check_admin_referer( 'easyrandomquotes-update_edit' );
			if ( get_option( 'kl-easyrandomquotes' ) ==  $theQuotes ) { //if there were no changes, do this to prevent false positive
				add_action( 'admin_notices', create_function('$a', 'echo "<div class=\"updated\"><p>" . __( "Nothing changed" , "easy-random-quotes" ) . "</p></div>";') );
			} elseif ( update_option( 'kl-easyrandomquotes',$theQuotes ) ) { //if option was successfully update with new values
				add_action( 'admin_notices', create_function('$a', 'echo "<div class=\"updated\"><p>" . __( "Quote was edited/deleted" , "easy-random-quotes" ) . "</p></div>";') );
			} else {
				add_action( 'admin_notices', create_function('$a', 'echo "<div class=\"updated\"><p>" . __( "Oops, something didn\'t work" , "easy-random-quotes" ) . "</p></div>";') );
			}

		}//end if edit

		if ( isset( $_POST[ 'clear' ] ) ) {

			if ( !isset( $_POST[ 'confirm' ])) {
				add_action( 'admin_notices', create_function('$a', 'echo "<div class=\"updated\"><p>" . __( "You must confirm for a reset" , "easy-random-quotes" ) . "</p></div>";') );
			} elseif (delete_option( 'kl-easyrandomquotes' )) {
				add_action( 'admin_notices', create_function('$a', 'echo "<div class=\"updated\"><p>" . __( "All quotes deleted" , "easy-random-quotes" ) . "</p></div>";') );
			}

		}//end if clear

		if ( isset( $_POST[ 'kl-permissions' ] ) ) {

			$role = esc_attr( $_POST['kl-permissions'] );
			if ( update_option( 'kl-permissions', $role ) ) {
				add_action( 'admin_notices', create_function('$a', 'echo "<div class=\"updated\"><p>" . __( "Role changed" , "easy-random-quotes" ) . "</p></div>";') );
			} else {
				add_action( 'admin_notices', create_function('$a', 'echo "<div class=\"updated\"><p>" . __( "Role could not be changed" , "easy-random-quotes" ) . "</p></div>";') );
			}

		}//end if permissions

	}//end update()

	function page( ) {
		echo '<div class="wrap">';
		echo '<h2>' . __( 'Easy Random Quotes' , 'easy-random-quotes' ) . '</h2>';
		
		if(current_user_can('manage_options')) {
			echo '<form method="post" class="alignright">';
			$roles = array(	'manage_options' => 'Administrators',
							'edit_posts' => 'Editors',
							'publish_posts' => 'Authors',
							'edit_pages' => 'Contributors');
			$role = get_option( 'kl-permissions', 'administrator' );
			echo 'Allow <select name="kl-permissions">';
			foreach( $roles as $c=>$r ) {
				echo '<option value="'.$c.'" '.selected($role, $c, false).'>'.$r.'</option>';
			}
			echo '</select> to manage quotes.';
			echo '<input type="submit" class="button-primary" value="Change permissions" /></form>';
		}
		
		echo '<h3>' . __( 'Add Quote' , 'easy-random-quotes' ) . '</h3>';
			echo '<form method="post">';
			wp_nonce_field( 'easyrandomquotes-update_add' );
			echo '<table class="widefat page"><thead><tr><th class="manage-column" colspan = "2">' . __( 'Add New' , 'easy-random-quotes' ) . '</th></tr></thead><tbody><tr>';
			echo '<td><textarea name="erq_newquote" rows="6" cols="60"></textarea></td>';
			echo '<td><p><input name="erq_add" type="submit" value = "' . __( 'Add' , 'easy-random-quotes' ) . '" />
			<input name="erq_import" type="submit" value = "' . __( 'Import' , 'easy-random-quotes' ) . '" /></p><p>
			With Import, each new line will be treated as the start of a new quote</p></td>';
			echo '</tr></tbody></table>';
			echo '</form>';

		echo '<h3>' . __( 'Edit Quotes' ) . '</h3>';
			echo '<form method="post">';
			wp_nonce_field( 'easyrandomquotes-update_edit' );
			
			$tblrows = '<tr><th class="manage-column column-cb check-column" id="cb"><input type="checkbox" /></th>
							<th class="manage-column">' . __( 'The quote' , 'easy-random-quotes' ) . '</th>
							<th class="manage-column">' . __( 'Short code (for posts/pages)' , 'easy-random-quotes' ) . '</th></tr>';
			echo '<table class="widefat page"><thead>' . $tblrows . '</thead><tfoot>' . $tblrows . '</tfoot><tbody>';

			$theQuotes =  get_option( 'kl-easyrandomquotes' ) ;

			if ( is_array( $theQuotes ) ) {
				foreach( $theQuotes as $id=>$quote ) {
					echo '<tr>';
					echo '<th class="check-column"><input type="checkbox" name="erq_del[' . $id . ']" /></th>';
					echo '<td><textarea name="erq_quote[' . $id . ']" rows="6" cols="60">' . stripslashes( $quote ) . '</textarea></td>';
					echo '<td>[erq id=' . $id . ']</td>';
					echo '</tr>';
				}
			} else { echo '<tr><th colspan="3">' . __( 'No quotes' , 'easy-random-quotes' ) . '</th></tr>'; }

			echo '</tbody></table>';
			echo '<p>' . 
			__( 'Checked items will be deleted' , 'easy-random-quotes' ) .
			'<br /><input type="hidden" name="erq_edit" /><input type="submit" class="button-primary" value = "' . 
			__( 'Save Changes' , 'easy-random-quotes' ) . '" /></p>';
			echo '</form>';
			
		echo '</div>';

	}// end page()

}

//shortcode/template tag
//outside of class to make it more accessible
function erq_shortcode( $atts = array( 'id' => 'rand' ) ) {
	extract(shortcode_atts(array(
			'id' => 'rand'
			), $atts));
	$theQuotes = get_option( 'kl-easyrandomquotes' ); 	//get exsisting
	$use = ( 'rand' == $id ) ? array_rand( $theQuotes ) : $id;
	return stripslashes( $theQuotes[ $use ] );
}

/* widget */
function kl_easyrandomquotes_load_widget() {
	register_widget( 'kl_easyrandomquotes_widget' );
}
class kl_easyrandomquotes_widget extends WP_Widget {
	function kl_easyrandomquotes_widget() {
		$widget_ops = array( 'classname' => 'kl-erq', 'description' => 'Displays random quotes' );
		$control_ops = array( 'id_base' => 'kl-erq' );
		$this->WP_Widget( 'kl-erq', 'Easy Random Quotes', $widget_ops, $control_ops );
    }
	function widget( $args, $instance ) {
		extract( $args );
		echo $before_widget;
		if ( $instance[ 'title' ] ) echo $before_title . $instance[ 'title' ] . $after_title;
		echo '<p>' . erq_shortcode( ) . '</p>';
		echo $after_widget;
	}
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance[ 'title' ] = esc_attr($new_instance[ 'title' ]);
		return $instance;
	}
	function form( $instance ) {
		$defaults = array( 'title' => 'A Random Thought' );
		$instance = wp_parse_args( (array) $instance, $defaults );
		echo '<p><label for="' . $this->get_field_id( 'title' ) . '">' . 'Title' . '</label>
        	<input type="text" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" value="' . $instance[ 'title' ] . '" /></p>';
	}
}