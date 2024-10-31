<?php
/*
Plugin Name: Piclyf Widget
Plugin URI: http://www.alleba.com/blog/
Description: Displays user's picture stream utilizing Piclyf.com's API.
Author: Andrew dela Serna
Author URI: http://www.alleba.com/blog/
Version: 0.1
License: GPL

  Copyright 2011 Andrew dela Serna (email andrew@alleba.com)

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

INSTALLATION
------------
1. Unzip the archive 'piclyf.zip' to a local folder on your computer.
2. Upload the folder 'piclyf' and its contents to your blog's plugin folder (root/wp-content/plugins) using FTP.
3. Login to your Wordpress admin panel and browse to the Plugins section.
4. Activate the Piclyf plugin.

*/

function wp_piclyf_widget_init() {
	$url = get_bloginfo('wpurl')."/wp-content/plugins/piclyf";
	?>
	<link rel="stylesheet" href="<?php echo $url; ?>/style.css" type="text/css" media="all" />
	<?php
}

add_action('wp_head', 'wp_piclyf_widget_init');

function piclyf_widget () {

$piclyf_user = get_option('piclyf_user');
$piclyf_displayname = get_option('piclyf_displayname');
$piclyf_user = $piclyf_user ? $piclyf_user : 'piclyf';
$piclyf_thumbs = get_option('piclyf_thumbs');
$piclyf_thumbs = (($piclyf_thumbs > 0) && ($piclyf_thumbs < 31) && (is_numeric($piclyf_thumbs))) ? $piclyf_thumbs : 10;
$piclyf_thumbs = $piclyf_thumbs - 1;

$api = "http://api.piclyf.com/v1/".$piclyf_user."/pics";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $api);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    
$data = curl_exec($ch); // execute curl session
curl_close($ch); // close curl session

$object = json_decode($data, true);

$max = count($object) - 1;

$url = get_bloginfo('wpurl')."/wp-content/plugins/piclyf";

echo "<div id='piclyf-widget'>";

for ($i = 0; $i < 1; $i++) {

  $id = $object[$i][id];
  $screenname = $object[$i][owner][screen_name];
  $displayname = $piclyf_displayname ? $piclyf_displayname : $object[$i][owner][screen_name];
  $name = $object[$i][owner][name];
  $user_pic = $object[$i][owner][image_url_small];
  $website = $object[$i][owner][website];
  $website_disp = str_replace("http://", "", $website);
  $location = $object[$i][owner][location];
  $pics_count = $object[$i][owner][pics_count];
  $followers_count = $object[$i][owner][followers_count];
  $friends_count = $object[$i][owner][friends_count];
 
  
  echo "<div id='piclyf-user'>
<div id='piclyf-thumb'><a href='http://www.piclyf.com/$screenname/'><img src='$user_pic' alt='$name' /></a></div><div id='piclyf-info'>
  <a href='http://www.piclyf.com/$screenname/'>$displayname</a><br />$pics_count Pics | $followers_count followers </div>
  <div id='piclyf-clear'></div>
</div>";

}

echo "<div id='piclyf-thumbs'>";

for ($i = 0; $i <= $piclyf_thumbs; $i++) {

  $src = $object[$i][image_url_big];
  $src = str_replace('_b.', '_t.', $src);
  $alt = $object[$i][display_text];
  $id = $object[$i][id];
  $screenname = $object[$i][owner][screen_name];
  echo "<a href='http://www.piclyf.com/$screenname/pics/$id' title='$alt' rel='nofollow' target='_blank'><img src='$src' alt='$alt' /></a>";
}
echo "</div><div id='piclyf-logo'><a href='http://www.piclyf.com/' title='Piclyf'><img src='".$url."/piclyf-logov3.png' border='0' alt=Piclyf Logo' /></a></div></div>";
}


function widget_piclyf_init() {
        if(!function_exists('register_sidebar_widget')) { return; }
        function widget_piclyf($args) {
            extract($args);
            echo $before_widget . $before_title . "Piclyf Stream" . $after_title;
            piclyf_widget();
            echo $after_widget;
        }
        register_sidebar_widget('Piclyf','widget_piclyf');
    }
   
add_action('plugins_loaded', 'widget_piclyf_init');

add_action( 'admin_menu', 'piclyf_add_menu' );

function piclyf_add_menu()
{
    add_options_page('Setup Piclyf Widget', 'Piclyf Widget', 8, 'piclyf.php', 'piclyf_options');
}

function piclyf_options()
{
    ?>
    <div class="wrap">
    <h2>Piclyf Widget Options</h2>

    <form method="post" action="options.php">
    <?php wp_nonce_field('update-options'); ?>

    <table class="form-table">
    <tr valign="top">
        <th scope="row">Piclyf Username</th>
        <td><input type="text" name="piclyf_user" value="<?php echo get_option('piclyf_user'); ?>" size="20" /></td>
    </tr>
    <tr valign="top">
        <th scope="row">Display Name (if Username is too long)</th>
        <td><input type="text" name="piclyf_displayname" value="<?php echo get_option('piclyf_displayname'); ?>" size="20" /></td>
    </tr>
    <tr valign="top">
        <th scope="row">Number of Thumbnails to Display (30 max)</th>
        <td><input type="text" name="piclyf_thumbs" value="<?php $tpl = get_option('piclyf_thumbs'); echo $tpl ? $tpl : '10' ?>"  size="20" /></td>
    </tr>
    </table>

    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="page_options" value="piclyf_user,piclyf_displayname,piclyf_thumbs" />

    <p class="submit">
    <input type="submit" class="button-primary" value="Save" />
    </p>

    </form>
    </div>
    <?php
}
?>