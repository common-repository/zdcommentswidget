<?php
/*
Plugin Name: ZdCommentsWidget
Plugin URI: http://www.zen-dreams.com/fr/
Description: ZdCommentsWidget allows you to add Recent Comments display using gravatar Widget
Version: 1.0.1
Author: Anthony PETITBOIS
Author URI: http://www.zen-dreams.com/fr/

Copyright 2008  Anthony PETITBOIS  (email : anthony@zen-dreams.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class zd_CommentsWidget {

	function zd_CommentsWidget() {
		load_plugin_textdomain('zd_CommentsWidget',PLUGINDIR . '/' . dirname(plugin_basename (__FILE__)) . '/lang');
		add_action('widgets_init', array(& $this, 'init_widget'));
	}
	
	function init_widget() {
		if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
			return;
		register_sidebar_widget(array('Comments Widget','widgets'),array(& $this, 'widget'));
		register_widget_control(array('Comments Widget', 'widgets'), array(& $this, 'widget_options'));	
	}
	
	function widget($args) {
		global $wpdb;
		
		$Options=get_option('zdcomments_options');
		$OptionList=explode('|',$Options);
		$WidgetTitle=$OptionList[0];
		$WidgetDisplay=$OptionList[1];
		$WidgetViewCount=($OptionList[2]!="") ? $OptionList[2] : "5";
		$DisplayLinks=$OptionList[3];
		$NoFollow=$OptionList[4];
		$DefaultAvatar=$OptionList[5];
		$AvatarSize=($OptionList[6]) ? $OptionList[6] : 32;
		$Display_CommenterName=$OptionList[7];
		$Display_CommentLink=$OptionList[8];
		extract($args);
		
		if (!$WidgetTitle) {
			if ($WidgetDisplay=="top") $WidgetTitle=__('Top Commenters','zd_CommentsWidget');
			else $WidgetTitle=__('Last Commenters','zd_CommentsWidget');
		}
		
		echo $before_widget.$before_title.$WidgetTitle.$after_title;

		if ($WidgetDisplay=="top") $query="SELECT count(comment_author_email) Compte,comment_ID, comment_author, comment_author_email, comment_author_url, comment_post_ID FROM $wpdb->comments WHERE comment_approved=1 and comment_type='' GROUP BY comment_author_email order by Compte DESC LIMIT 0, $WidgetViewCount";
		else $query="SELECT comment_author, comment_ID, comment_author_email, comment_author_url, comment_post_ID FROM $wpdb->comments WHERE comment_approved=1 and user_id=0 and comment_type='' order by comment_date desc LIMIT 0, $WidgetViewCount";
		$results=$wpdb->get_results($query);
		if ($results) {
			echo '<ul>';
			foreach ($results as $key => $Comment) {
				$url=$Comment->comment_author_url;
				if (($Display_CommentLink==1)&&($WidgetDisplay!="top")) $url=get_permalink($Comment->comment_post_ID)."#comment-".$Comment->comment_ID;
				echo '<li>';
				if (($url)&&($DisplayLinks)) {
					echo '<a href="'.$url.'"';
					if ($NoFollow) echo ' rel="nofollow"';
					echo '>';
				}
				$image=md5(strtolower($Comment->comment_author_email));
				$defavatar=urlencode($DefaultAvatar);
				echo '<img src="http://www.gravatar.com/avatar.php?gravatar_id='.$image.'&amp;size='.$AvatarSize.'&amp;default='.$defavatar.'" alt ="'.$Comment->comment_author.'" title="'.$Comment->comment_author.'" border="0"/>';
				if ($Display_CommenterName) echo $Comment->comment_author;
				if (($Comment->comment_author_url)&&($DisplayLinks)) echo '</a>';
				echo '</li>';
			}
			echo '</ul>';
			echo '<p style="clear: both;"></p>';
		}
		echo $after_widget;
	}
	
	function widget_options() {
		if ($_POST['zdcomments_display']) {
			$option=$_POST['zdcomments_title'].'|'.$_POST['zdcomments_display'].'|'.$_POST['zdcomments_widget_count'];
			($_POST['zdcomments_widget_links']) ? $option.='|1' : $option.='|0';
			($_POST['zdcomments_widget_nofollow']) ? $option.='|1' : $option.='|0';
			$option.="|".$_POST['zdcomments_defgrav'];
			$option.="|".$_POST['zdcomments_grav_size'];
			($_POST['zdcomments_widget_cname']) ? $option.='|1' : $option.='|0';
			($_POST['zdcomments_comment_link']) ? $option.='|1' : $option.='|0';
			update_option('zdcomments_options',$option);
		}
		$Options=get_option('zdcomments_options');
		$OptionList=explode('|',$Options);
		$WidgetTitle=$OptionList[0];
		$WidgetOption=$OptionList[1];
		$WidgetViewCount=($OptionList[2]!="") ? $OptionList[2] : "5";
		$DisplayLinks=($OptionList[3]=="1") ? ' checked="on"' : '';
		$NoFollow=($OptionList[4]=="1") ? ' checked="on"' : '';
		$DefaultAvatar=$OptionList[5];
		$AvatarSize=$OptionList[6];
		$Display_CommenterName=($OptionList[7]=="1") ? ' checked="on"' : '';
		$Display_CommentLink=($OptionList[8]=="1") ? ' checked="on"' : '';
		echo '<p><label for="zdcomments_title">'.__('Title','zd_CommentsWidget').':<br /><input type="text" name="zdcomments_title" id="zdcomments_title" value="'.$WidgetTitle.'" /></label></p>';
		echo '<p><label for="zdcomments_display">'.__('Display','zd_CommentsWidget').':<br /><select name="zdcomments_display" id="zdcomments_display">';
		echo '<option value="top"'; if ($WidgetOption=="top") echo ' selected="selected"'; echo '>'.__('Top Commenters','zd_CommentsWidget').'</option>';
		echo '<option value="last"'; if ($WidgetOption=="last") echo ' selected="selected"'; echo '>'.__('Last Commenters','zd_CommentsWidget').'</option>';
		echo '</select></label></p>';
		echo '<p><label for="zdcomments_defgrav">'.__('Default Gravatar','zd_CommentsWidget').':<br /><input type="text" name="zdcomments_defgrav" id="zdcomments_defgrav" value="'.$DefaultAvatar.'"/></label></p>';
		echo '<p><label for="zdcomments_grav_size">'.__('Gravatar Size','zd_CommentsWidget').':<br /><input type="text" name="zdcomments_grav_size" id="zdcomments_grav_size" value="'.$AvatarSize.'"/></label></p>';
		echo '<p><label for="zdcomments_widget_count">'.__('Display count','zd_CommentsWidget').':<br /><input type="text" name="zdcomments_widget_count" id="zdcomments_widget_count" value="'.$WidgetViewCount.'" /></label></p>';
		echo '<p><label for="zdcomments_widget_cname"><input type="checkbox" name="zdcomments_widget_cname" id="zdcomments_widget_cname" '.$Display_CommenterName.' /> '.__('Display Commenter name','zd_CommentsWidget').'</label></p>';
		echo '<p><label for="zdcomments_widget_links"><input type="checkbox" name="zdcomments_widget_links" id="zdcomments_widget_links" '.$DisplayLinks.' /> '.__('Display Links','zd_CommentsWidget').'</label></p>';
		echo '<p><label for="zdcomments_comment_link"><input type="checkbox" name="zdcomments_comment_link" id="zdcomments_comment_link" '.$Display_CommentLink.' /> '.__('Display Comment, not website','zd_CommentsWidget').'<br /><small>'.__('This option will use comment link instead of user website. Only working when displaying with last commenters','zd_CommentsWidget').'</small></label></p>';
		echo '<p><label for="zdcomments_widget_nofollow"><input type="checkbox" name="zdcomments_widget_nofollow" id="zdcomments_widget_nofollow" '.$NoFollow.' /> '.__('NoFollow Links','zd_CommentsWidget').'<br /><small>'.__('Activating this option will prevent SearchEngines to crawl the links','zd_CommentsWidget').'</small></label></p>';
	}
}
$ZdComWidget= new zd_CommentsWidget();

?>