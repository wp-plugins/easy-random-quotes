<?php
/*
Plugin Name: Easy Random Quotes
Plugin URI: http://trepmal.com/plugins/easy-random-quotes/
Description: Insert quotes and pull them randomly into your pages and posts (via shortcodes) or your template (via template tags). 
Author: Kailey Lampert
Version: 1.1
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
		register_activation_hook( __FILE__, array( &$this, 'activate' ) );
		register_uninstall_hook( __FILE__, array( &$this, 'uninstall' ) );
		add_action( 'widgets_init', 'kl_easyrandomquotes_load_widget' ); /* Add our function to the widgets_init hook. */
	}

	function activate( ) {
		add_option( 'kl-easyrandomquotes',serialize( array( ) ) );
	}
	function uninstall( ) {
		//uninstall.php runs instead
		delete_option( 'kl-easyrandomquotes' );
	}
	function menu( ) {
		add_submenu_page( 'options-general.php', 'Easy Random Quotes', 'Easy Random Quotes', 'administrator', __FILE__, array( &$this, 'page' ) );
	}

	function page( ) {
		echo '<div class="wrap">';
		echo '<h2>' . __( 'Easy Random Quotes' ) . '</h2>';
		
		if ( isset( $_POST['erq_add'] ) ) {
			$newquote = $_POST['erq_newquote'];
			
			$theQuotes =  get_option( 'kl-easyrandomquotes' ) ; 	//get existing
			if( !empty( $newquote ) ) {

				$theQuotes[] = $newquote;									//add new
						
				check_admin_referer( 'easyrandomquotes-update_add' );
				if ( is_array( $theQuotes ) ) {
					update_option( 'kl-easyrandomquotes',$theQuotes );		//successfully updated
					echo '<p>New quote was added</p>';
				}
				else {														//uh oh...
					echo '<p>oops, something didn\'t work</p>';
				}
			}
			else { echo 'nothing added'; }
		
		}

		if ( isset( $_POST['erq_edit'] ) ) {

			$ids = $_POST['erq_quote'];
			$dels = $_POST['erq_del'];
			
			$theQuotes =  get_option( 'kl-easyrandomquotes' ) ; 	//get existing
			
			foreach( $ids as $id  => $quote ) {
				$theQuotes[$id] = $quote;									//update each part with new quote
			}

			if ( is_array( $dels ) )											//if checkmarks are selected...
			foreach( $dels as $id  => $quote ) {						
				unset( $theQuotes[$id] );										//delete selected...
			}

			check_admin_referer( 'easyrandomquotes-update_edit' );
			if ( get_option( 'kl-easyrandomquotes' ) ==  $theQuotes ) {			//if there were no changes, do this to prevent false positive
				echo '<p>' . __( 'Nothing changed' ) . '</p>';
			}
			else if ( update_option( 'kl-easyrandomquotes',$theQuotes ) ) {		//if option was successfully update with new values
				echo '<p>' . __( 'Quote was edited/deleted' ) . '</p>';
			}
			else {
				echo '<p>' . __( 'Oops, something didn\'t work' ) . '</p>';		// uh oh.....
			}
		}


		echo '<h3>' . __( 'Add Quote' ) . '</h3>';
			echo '<form method="post">';
			wp_nonce_field( 'easyrandomquotes-update_add' );
			echo '<table class="widefat page">';
			echo '<thead><tr><th class="manage-column" colspan = "2">' . __( 'Add New' ) . '</th></tr></thead>';
			echo '<tbody><tr>';
			echo '<td><textarea name="erq_newquote" rows="6" cols="60"></textarea></td>';
			echo '<td><p><input type="hidden" name="erq_add" /><input type="submit" value = "' . __( 'Add' ) . '" /></p></td>';
			echo '</tr></tbody>';
			echo '</table>';
			echo '</form>';

		echo '<h3>' . __( 'Edit Quotes' ) . '</h3>';
			echo '<form method="post">';
			wp_nonce_field( 'easyrandomquotes-update_edit' );
			echo '<table class="widefat page">';
			$tblrows = '<tr>
							<th class="manage-column column-cb check-column" id="cb"><input type="checkbox" /></th>
							<th class="manage-column">' . __( 'The quote' ) . '</th>
							<th class="manage-column">' . __( 'Short code (for posts/pages)' ) . '</th>
				</tr>';
			echo '<thead>' . $tblrows . '</thead>';
			echo '<tfoot>' . $tblrows . '</tfoot>';
			
			echo '<tbody>';
	
			$theQuotes =  get_option( 'kl-easyrandomquotes' ) ;
		
			if ( is_array( $theQuotes ) ) {
				foreach( $theQuotes as $id=>$quote ) {
					echo '<tr>';
					echo '<th class="check-column"><input type="checkbox" name="erq_del[' . $id . ']" /></th>';
					echo '<td><textarea name="erq_quote[' . $id . ']" rows="6" cols="60">' . stripslashes( $quote ) . '</textarea></td>';
					echo '<td>[erq id=' . $id . ']</td>';
					echo '</tr>';
				}
			}
		
			else { echo '<tr><th>' . __( 'No quotes' ) . '</th></tr>'; }

			echo '</tbody>';
			echo '</table>';
			echo '<p><input type="hidden" name="erq_edit" /><input type="submit" value = "' . __( 'Save' ) . '" /></p>';
			echo '</form>';
			echo '<p><strong>' . __( 'Short code: ' ) . '</strong><br />';
			echo 'Specific quote: <code>' . '[erq id=2]' . '</code><br />';
			echo 'Random: <code>' . '[erq]' . '</code></p>';
			echo '<p><strong>' . __( 'Template tag: ' ) . '</strong><br />';
			echo 'Specific quote: <code>' . htmlspecialchars( '<?php echo erq_shortcode(array(\'id\' => \'2\')); ?>' ) . '</code><br />';
			echo 'Random: <code>' . htmlspecialchars( '<?php echo erq_shortcode(); ?>' ) . '</code></p>';
			echo '<p>' . __( 'Quotes retained when plugin deactivated. Quotes deleted when plugin removed.' ) . '</p>';
			echo '</div>';
		
	}// end page( ) function
		
}

function erq_shortcode( $atts = array( 'id' => 'rand' ) ) {

	extract(shortcode_atts(array(
			'id' => 'rand'
			), $atts));
	$theQuotes = unserialize( get_option( 'kl-easyrandomquotes' ) ); 	//get exsisting
	$tot = count( $theQuotes )-1;
	$rand = rand( 0,$tot );
	$use = ($id == 'rand') ? $rand : $id;
	
	return stripslashes( $theQuotes["{$use}"] );
}
add_shortcode( 'erq', 'erq_shortcode' );


/* widget */
function kl_easyrandomquotes_load_widget() { register_widget( 'kl_easyrandomquotes_widget' ); } /* Function that registers our widget. */
class kl_easyrandomquotes_widget extends WP_Widget {

	function kl_easyrandomquotes_widget() {
		$widget_ops = array( 'classname' => 'kl-erq', 'description' => 'Displays random quotes' ); /* Widget settings. */
		$control_ops = array( 'id_base' => 'kl-erq' ); /* Widget control settings. */
		$this->WP_Widget( 'kl-erq', 'Easy Random Quotes', $widget_ops, $control_ops ); /* Create the widget. */
    }

	function widget( $args, $instance ) {
		extract( $args );
		echo $before_widget; /* Before widget (defined by themes). */
		if ( $instance[ 'title' ] ) echo $before_title . $instance[ 'title' ] . $after_title; /* Title of widget (before and after defined by themes). */
		echo erq_shortcode( );
		echo $after_widget; /* After widget (defined by themes). */
	}

	function form( $instance ) {
		echo '<p>Displays random quote.</p>';
	}
}
/* end widget */

?>