<?php
/*
Plugin Name: eFiles Backup
Plugin URI: http://quirm.net/2008/10/08/efiles-backup/
Description: Creates on demand backups of wp-content directories - not suitable for use on WPMU without alteration.
Version: 1.0.0
Author: Rich Pedley 
Author URI: http://elfden.co.uk/

    Copyright 2007  R PEDLEY  (email : rich@quirm.net)

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
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
session_start();
//setup constants 
if ( !defined('WP_CONTENT_DIR') )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( !defined('WP_PLUGIN_DIR') )
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' ); // full path, no trailing slash

$role = get_role('administrator');
$role->add_cap('do_ebu');
if (!function_exists('ebu_admin')) {
    /**
     * used by the admin panel hook
     */
    function ebu_admin() {    
        if (function_exists('add_menu_page')) {
			add_management_page(__('eFiles Backup','ebu'), __('eFiles Backup','ebu'),'administrator', basename('efiles-backup.php'),'ebu_files');
		}
	}
}

add_action('admin_menu', 'ebu_admin');
add_action('init','ebudata');
function ebudata(){
	if ( current_user_can('do_ebu') ){
		if(isset($_GET['ebu']) && is_numeric($_GET['ebu'])){
			
			switch($_GET['ebu']){
				case '1': //uploads only
					//get the upload directory
					$dirs=wp_upload_dir();
					$upload_dir= substr_replace($dirs['path'], '', -(strlen($dirs['subdir'])));
					ebudownload($upload_dir,'upload');
					break;
				case '2': //themes only
					ebudownload(get_theme_root(),'themes');
					break;
				case '3': //plugins only
					ebudownload(WP_PLUGIN_DIR,'plugins');
					break;
				case '9'://all the wp-content
					ebudownload(WP_CONTENT_DIR,'wp-content');
					break;
				default:
					return;
			}
		}
	}
}


function ebu_files(){
	?>
	<div class="wrap">
	<h2><?php _e('Files Backup on Demand','ebu'); ?></h2>
	<p><strong><?php _e('Warning','ebu'); ?></strong> <?php _e('this is only suitable for smaller sites, for larger sites please use FTP.','ebu'); ?></p>
	<ul>
	<li><a href="edit.php?page=efiles-backup.php&amp;ebu=1"><?php _e('Uploads Only','ebu'); ?></a></li>
	<li><a href="edit.php?page=efiles-backup.php&amp;ebu=2"><?php _e('Themes Only','ebu'); ?></a></li>
	<li><a href="edit.php?page=efiles-backup.php&amp;ebu=3"><?php _e('Plugins Only','ebu'); ?></a></li>
	<li><a href="edit.php?page=efiles-backup.php&amp;ebu=9"><?php _e('wp-content directory','ebu'); ?></a> <?php _e('includes all of the above - unless they have been moved from the default.','ebu'); ?></li>
	</ul>
	</div>
	<?php
}
function ebudownload($dir,$prepend){
	include_once("archive.class.php");
	//get the date
	$ebudate=date("Y-m-d");
	$ebusitename=str_replace(" ","-",get_bloginfo('name'));
	$backupfilename=$ebusitename.'-'.$prepend.'-'.$ebudate.'.zip';
	//initiate the class with the file name
	$ebackup = new zip_file($backupfilename);
	//setup some basics
	$ebackup->set_options(array('basedir' => $dir,'inmemory' => 1, 'recurse' => 1, 'storepaths' => 1,'prepend'=>$prepend));
	//add the files
	$ebackup->add_files(array("*.*"));
	// Create archive in memory
	$ebackup->create_archive();
	//and send to browser for auto download
	$ebackup->download_file();
}
load_plugin_textdomain('ebu', WP_PLUGIN_DIR.'/efiles-backup');
?>