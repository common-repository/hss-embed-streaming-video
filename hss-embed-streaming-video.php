<?php
/*
Plugin Name: HSS Embed Streaming Video
Plugin URI: https://www.hoststreamsell.com
Description: Provide access to Streaming Video in your WordPress Website
Author: Gavin Byrne
Author URI: https://www.hoststreamsell.com
Contributors:
Version: 3.23

HSS Embed Streaming Video is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

HSS Embed Streaming Video is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with HSS Embed Streaming Video. If not, see <http://www.gnu.org/licenses/>.
*/


// create shortcode with parameters so that the user can define what's queried - default is to list all blog posts



register_activation_hook(__FILE__, 'hss_embed_add_defaults');
register_uninstall_hook(__FILE__, 'hss_embed_delete_plugin_options');
add_action('admin_init', 'hss_embed_init' );

function hss_embed_add_defaults() {
        $tmp = get_option('hss_embed_options');
    if(($tmp['chk_default_options_db']=='1')||(!is_array($tmp))) {
                delete_option('hss_embed_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
                $arr = array(   "api_key" => "", "jwplayer_version" => "videojs7", "jwplayer_logo_file" => "", "jwplayer_logo_link" => "", "jwplayer_logo_hide" => "false", "player_responsive_max_width" => "true", "log_player_events" => "true"
                );
                update_option('hss_embed_options', $arr);
        }
}

function hss_embed_delete_plugin_options() {
        delete_option('hss_embed_options');
}

function hss_embed_init(){
        register_setting( 'hss_embed_plugin_options', 'hss_embed_options', 'hss_embed_validate_options' );
	$options = get_option('hss_embed_options');
	if(is_array($options)){
		if (array_key_exists('responsive_player', $options)==false) {
		        $options['responsive_player'] = "0";
		        update_option('hss_embed_options', $options);
		}
                if (array_key_exists('jwplayer_version', $options)==false) {
                        $options['jwplayer_version'] = "videojs7";
                        update_option('hss_embed_options', $options);
                }
                if (array_key_exists('jwplayer_logo_file', $options)==false) {
                        $options['jwplayer_logo_file'] = "";
                        update_option('hss_embed_options', $options);
                }
                if (array_key_exists('jwplayer_logo_link', $options)==false) {
                        $options['jwplayer_logo_link'] = "";
                        update_option('hss_embed_options', $options);
                }
                if (array_key_exists('jwplayer_logo_hide', $options)==false) {
                        $options['jwplayer_logo_hide'] = "false";
                        update_option('hss_embed_options', $options);
                }
		if (array_key_exists('log_player_events', $options)==false) {
                        $options['log_player_events'] = "true";
                        update_option('hss_embed_options', $options);
                }

	}
}


function hss_embed_validate_options($input) {
        // strip html from textboxes
        if(!isset( $input['responsive_player'] ) )
                $input['responsive_player'] = 0;
        if (!is_numeric($input['database_id'])) {
                $input['database_id'] = "0";
        }else{
                $input['database_id'] =  trim(wp_filter_nohtml_kses($input['database_id']));
        }

        $input['api_key'] =  wp_filter_nohtml_kses($input['api_key']); // Sanitize textarea input (strip html tags, and escape characters)
        return $input;
}



add_action('wp_ajax_get_download_links_EMBED', 'get_download_links_callback_EMBED');
add_action('wp_ajax_nopriv_get_download_links_EMBED', 'get_download_links_callback_EMBED');
function get_download_links_callback_EMBED() {
 _log("get_download_links_callback");
$purchase_id = $_POST['purchase_id'];
 $videolink = $_POST['videolink'];
 #$video_id = get_post_meta($purchase_id, '_woo_video_id', true);
 $video_id = $purchase_id;
 echo get_video_download_links_EMBED($video_id,$videolink);

 die(); // this is required to return a proper result
}

add_action('wp_print_footer_scripts', 'get_download_links_javascript_EMBED');

function get_download_links_javascript_EMBED() {
?>
<script type="text/javascript" >
jQuery(document).ready(function($) {
    $('.myajaxdownloadlinks_EMBED').attr("disabled", false);
    $('.myajaxdownloadlinks_EMBED').click(function(event){
        $('#'+event.target.id).attr("disabled", true);
        var data = {
            action: 'get_download_links_EMBED',
            purchase_id: event.target.id,
<?php   if(isset($_GET['videolink'])){ ?>
            videolink: '<?php echo $_GET['videolink']; ?>'
<?php   }else{ ?>
            videolink: ''
<?php   } ?>
        };

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        $.post(ajaxurl, data, function(response) {
            //$('#'+event.target.id).css("visibility", "hidden");
            $("#download_links_"+event.target.id).html(response);
            setTimeout(function() {
                    $('#download_links_'+event.target.id).html("");
                    $('#'+event.target.id).attr("disabled", false);
                    //$('#'+event.target.id).css("visibility", "visible");
            }, 240000);
        });
    });
});
</script>
<?php
}


add_action('wp_head','hss_EMBED_ajaxurl');
function hss_EMBED_ajaxurl() {
?>
<script type="text/javascript">
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>
<?php
}



add_action('wp_ajax_hss_embed_store_user_setting', 'hss_embed_store_user_setting_callback');
add_action('wp_ajax_nopriv_hss_embed_store_user_setting', 'hss_embed_store_user_setting_callback');
function hss_embed_store_user_setting_callback() {
 #_hss_embed_log("hss_embed_store_user_setting_callback");
global $user_ID;

 $setting_name = $_POST['setting_name'];
 $setting_value = $_POST['setting_value'];
        #_hss_woo_log("hss_embed_store_user_setting_callback ".$user_ID." ".$setting_name." = ".$setting_value);

 if($user_ID!="" and $setting_name!="" and $setting_value!=""){
    #$user_ID = (int)$user_ID;
     if($user_ID>0){
        #_hss_woo_log("hss_embed_store_user_setting_callback user_id=".$user_ID." setting..");
        $options = get_option('hss_embed_options');
        if(!isset( $options['user_settings'] ) )
                $options['user_settings'] = [];
        if(!isset( $options['user_settings'][$user_ID] ) )
                $options['user_settings'][$user_ID] = [];
        $options['user_settings'][$user_ID][$setting_name] = $setting_value;
        update_option('hss_embed_options', $options);
        #_hss_woo_log("hss_embed_store_user_setting_callback ".$user_ID." ".$setting_name." = ".$options['user_settings'][$user_ID][$setting_name]);
     }
 }



 die(); // this is required to return a proper result
}



// Register style sheet.
add_action( 'wp_enqueue_scripts', 'register_hss_embed_plugin_styles' );

/**
 * Register style sheet.
 */
function register_hss_embed_plugin_styles() {
        wp_register_style( 'hss-embed-streaming-video', plugins_url( 'hss-embed-streaming-video/css/hss-woo.css' ) );
        wp_enqueue_style( 'hss-embed-streaming-video' );
}

function hss_embed_options_page () {
?>
        <div class="wrap">

                <!-- Display Plugin Icon, Header, and Description -->
                <div class="icon32" id="icon-options-general"><br></div>
                <h2>HostStreamSell Video Embed Plugin Settings</h2>
                <p>Please enter the settings below...</p>

                <!-- Beginning of the Plugin Options Form -->
                <form method="post" action="options.php">
                        <?php settings_fields('hss_embed_plugin_options'); ?>
                        <?php $options = get_option('hss_embed_options'); ?>

                        <!-- Table Structure Containing Form Controls -->
                        <!-- Each Plugin Option Defined on a New Table Row -->
                        <table class="form-table">

                                <!-- Textbox Control -->
                                <tr>
                                        <th scope="row">HostStreamSell API Key<BR><i>(available from your account on www.hoststreamsell.com)</i></th>
                                        <td>
                                                <input type="text" size="40" name="hss_embed_options[api_key]" value="<?php echo $options['api_key']; ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">Video Player Size<BR><i>(leave blank to use defaults)</i></th>
                                        <td>
                                                Width <input type="text" size="10" name="hss_embed_options[player_width_default]" value="<?php echo $options['player_width_default']; ?>" /> Height  <input type="text" size="10" name="hss_embed_options[player_height_default]" value="<?php echo $options['player_height_default']; ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">Make Player Width and Height Responsive</th>
                                        <td>
                                                <input type="checkbox" name="hss_embed_options[responsive_player]" value="1"<?php checked( 1 == $options['responsive_player']); ?> />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">Reponsive Player Max Width<BR><i>(default is 640 if left blank, only used when Reponsive Player checkbox is checked)</i></th>
                                        <td>
                                                Width <input type="text" size="10" name="hss_embed_options[player_responsive_max_width]" value="<?php echo $options['player_responsive_max_width']; ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">Mobile Device Video Player Size<BR><i>(leave blank to use defaults)</i></th>
                                        <td>
                                                Width <input type="text" size="10" name="hss_embed_options[player_width_mobile]" value="<?php echo $options['player_width_mobile']; ?>" /> Height  <input type="text" size="10" name="hss_embed_options[player_height_mobile]" value="<?php echo $options['player_height_mobile']; ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">Video Player Version<BR><i>Note: JW Player 7 requires a license file but they have changed their PRO plan to be free - see www.jwplayer.com for more info</i></th>
                                        <td>
                                                <select name="hss_embed_options[jwplayer_version]">
                                                <?php
                                                if ($options['jwplayer_version']=="6"){
                                                        ?><option value="6" SELECTED>JW 6 Free</option><?php
                                                        ?><option value="7">JW 7 Free</option><?php
                                                        ?><option value="7Prem">JW 7 Premium</option><?php
                                                        ?><option value="8">JW 8 (all versions)</option><?php
                                                        ?><option value="videojs7">Videojs 7</option><?php
                                                        ?><option value="videojs5">Videojs 5</option><?php
                                                }
                                                elseif ($options['jwplayer_version']=="7"){
                                                        ?><option value="7" SELECTED>JW 7 Free</option><?php
                                                        ?><option value="7Prem">JW 7 Premium</option><?php
                                                        ?><option value="6">JW 6 Free</option><?php
                                                        ?><option value="8">JW 8 (all versions)</option><?php
                                                        ?><option value="videojs7">Videojs 7</option><?php
                                                        ?><option value="videojs5">Videojs 5</option><?php
                                                }
                                                elseif ($options['jwplayer_version']=="7Prem"){
                                                        ?><option value="7Prem" SELECTED>JW 7 Premium</option><?php
                                                        ?><option value="7">JW 7 Free</option><?php
                                                        ?><option value="6">JW 6 Free</option><?php
                                                        ?><option value="8">JW 8 (all versions)</option><?php
                                                        ?><option value="videojs7">Videojs 7</option><?php
                                                        ?><option value="videojs5">Videojs 5</option><?php
                                                }
						elseif ($options['jwplayer_version']=="8"){
                                                        ?><option value="8" SELECTED>JW 8 (all versions)</option><?php
                                                        ?><option value="7Prem">JW 7 Premium</option><?php
                                                        ?><option value="7">JW 7 Free</option><?php
                                                        ?><option value="6">JW 6 Free</option><?php
                                                        ?><option value="videojs7">Videojs 7</option><?php
                                                        ?><option value="videojs5">Videojs 5</option><?php
                                                }
                                                elseif ($options['jwplayer_version']=="videojs5"){
                                                        ?><option value="videojs5" SELECTED>Videojs 5</option><?php
                                                        ?><option value="videojs7">Videojs 7</option><?php
                                                        ?><option value="7Prem">JW 7 Premium</option><?php
                                                        ?><option value="7">JW 7 Free</option><?php
                                                        ?><option value="8">JW 8 (all versions)</option><?php
                                                        ?><option value="6">JW 6 Free</option><?php
                                                }
						elseif ($options['jwplayer_version']=="videojs7"){
                                                        ?><option value="videojs7" SELECTED>Videojs 7</option><?php
                                                        ?><option value="videojs5">Videojs 5</option><?php
                                                        ?><option value="7Prem">JW 7 Premium</option><?php
                                                        ?><option value="7">JW 7 Free</option><?php
                                                        ?><option value="8">JW 8 (all versions)</option><?php
                                                        ?><option value="6">JW 6 Free</option><?php
                                                } ?>

                                                </select>
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">JW Player License Key<BR><i>(available from www.longtailvideo.com)</i></th>
                                        <td>
                                                <input type="text" size="50" name="hss_embed_options[jwplayer_license]" value="<?php echo $options['jwplayer_license']; ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">JW Player Watermark File URL<BR><i>(JW Player 7/8 only - read more <a href='http://support.jwplayer.com/customer/portal/articles/1406865-branding-your-player' target='_blank'>here</a>)</i></th>
                                        <td>
                                                <input type="text" size="50" name="hss_embed_options[jwplayer_logo_file]" value="<?php echo $options['jwplayer_logo_file']; ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">JW Player Watermark Link<BR><i>(JW Player 7/8 only)</i></th>
                                        <td>
                                                <input type="text" size="50" name="hss_embed_options[jwplayer_logo_link]" value="<?php echo $options['jwplayer_logo_link']; ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">JW Player Watermark Hide<BR><i>(JW Player 7/8 only)</i></th>
                                        <td>
                                                <select name="hss_embed_options[jwplayer_logo_hide]">
                                                <?php
                                                if ($options['jwplayer_logo_hide']=="false" or $options['jwplayer_logo_hide']==""){
                                                        ?><option value="false" SELECTED>false</option><?php
                                                        ?><option value="true">true</option><?php
                                                }
                                                elseif ($options['jwplayer_logo_hide']=="true"){
                                                        ?><option value="true" SELECTED>true</option><?php
                                                        ?><option value="false">false</option><?php
                                                } ?>
                                                </select>
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">Website Reference ID<BR><i>(set if you want a user to see the full video if they have purchased using one of the other HSS plugins)</i></th>
                                        <td>
                                                <input type="text" size="40" name="hss_embed_options[database_id]" value="<?php echo $options['database_id']; ?>" />
                                        </td>
                                </tr>
                                <tr>
                                        <th scope="row">Log Video Player Events</th>
                                        <td>
                                                <select name="hss_embed_options[log_player_events]">
                                                <?php
                                                if ($options['log_player_events']=="false" or $options['log_player_events']==""){
                                                        ?><option value="false" SELECTED>false</option><?php
                                                        ?><option value="true">true</option><?php
                                                }
                                                elseif ($options['log_player_events']=="true"){
                                                        ?><option value="true" SELECTED>true</option><?php
                                                        ?><option value="false">false</option><?php
                                                } ?>
                                                </select>
                                        </td>
                                </tr>

                                <tr>
                        </table>
                        <p class="submit">
                        <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                        </p>
                </form>
        </div>
<?php
}

function hss_embed_menu () {
        add_options_page('HostStreamSell Embed Video Admin','HSS Embed Admin','manage_options','hss_embed_admin', 'hss_embed_options_page');
}

add_action('admin_menu','hss_embed_menu');

add_shortcode( 'hss-embed-video', 'hss_embed_video_shortcode' );
function hss_embed_video_shortcode( $atts ) {
global $is_iphone;
        global $user_ID;
    ob_start();
 
    // define attributes and their defaults
    extract( shortcode_atts( array (
      'videoid' => '-1',
      'version' => 'trailer',
      'download' => 'false',
    ), $atts ) );


	if($videoid<0){
		echo "ERROR: you need to set the videoid attribute!";	
	      	$myvariable = ob_get_clean();
        	return $myvariable;
	}

	$video = "
                                <SCRIPT type=\"text/javascript\">


                                var agent=navigator.userAgent.toLowerCase();
                                var is_iphone = (agent.indexOf('iphone')!=-1);
                                var is_ipad = (agent.indexOf('ipad')!=-1);
                                var is_playstation = (agent.indexOf('playstation')!=-1);
                                var is_safari = (agent.indexOf('safari')!=-1);
                                var is_iemobile = (agent.indexOf('iemobile')!=-1);
				var is_silk = (agent.indexOf('silk')!=-1);
                                var is_blackberry = (agent.indexOf('BlackBerry')!=-1);
                                var is_android = (agent.indexOf('android')!=-1);
				</SCRIPT>
	";

	
	$videoids = explode(",",$videoid);
	foreach($videoids as $videoidinner)
	{
			$rand = substr(md5(microtime()),rand(0,26),5);
                                $options = get_option('hss_embed_options');
                                $userId = $user_ID;
				if($userId==0){
					$userId = mt_rand(100000,999999);
				}
				#echo $userId;

                                $hss_video_id = $videoidinner;
				if($version=="full")
					$force_allow = "yes";
				else
					$force_allow = "no";
				if (isset($options['database_id'])){
					$database_id = $options['database_id'];
					if($database_id=="")
						$database_id=0;
						
					$response = wp_remote_post( "https://www.hoststreamsell.com/api/1/xml/videos?api_key=".$options['api_key']."&video_id=$hss_video_id&private_user_id=$userId&database_id=".$database_id."&expands=playback_details&force_allow=$force_allow&limit=1000&offset=0&clientip=".get_the_user_ip_EMBED(), array(
                	                        'method' => 'GET',
        	                                'timeout' => 15,
	                                        'redirection' => 5,
	                                        'httpversion' => '1.0',
                                        	'blocking' => true,
                                	        'headers' => array(),
                        	                //'body' => $params,
                	                        'cookies' => array()
        	                            )
	                                );
				}else{
					$userId = mt_rand(100000,999999);
					$response = wp_remote_post( "https://www.hoststreamsell.com/api/1/xml/videos?api_key=".$options['api_key']."&video_id=$hss_video_id&expands=playback_details&private_user_id=$userId&database_id=0&force_allow=$force_allow&limit=1000&offset=0&clientip=".get_the_user_ip_EMBED(), array(
                                                'method' => 'GET',
                                                'timeout' => 15,
                                                'redirection' => 5,
                                                'httpversion' => '1.0',
                                                'blocking' => true,
                                                'headers' => array(),
                                                //'body' => $params,
                                                'cookies' => array()
                                            )
                                        );
				}
                                $res = "";
                                if( is_wp_error( $response ) ) {
                                   $return_string .= 'Error occured retieving video information, please try refresh the page';
                                } else {
                                   $res = $response['body'];
                                }

                                $xml = new SimpleXMLElement($res);
                                _log($xml);
				$title = htmlspecialchars($xml->result->title, ENT_QUOTES);
                                $hss_video_title = $title;
                                $user_has_access = $xml->result->user_has_access;
				$user_can_download = $xml->result->user_can_download;
                                

                                $description = $xml->result->description;
                                $feature_duration = $xml->result->feature_duration;
                                $trailer_duration = $xml->result->trailer_duration;
                                $video_width = $xml->result->width;
                                $video_height = $xml->result->height;
				$aspect_ratio = $xml->result->aspect_ratio;
                                if($video_width>640){
                                        $video_width = "640";
                                        $video_height = "390";
                                }
                                $referrer = site_url();
				$playersessionid = substr( md5(rand()), 0, 10);

                                $hss_video_user_token = $xml->result->user_token;

				$suid = $xml->result->suid;

                                $hss_video_mediaserver_ip = $xml->result->wowza_ip;
				$hss_video_mediaserver_url = $xml->result->wowza_url;

                                $hss_video_smil_token = "?privatetoken=".$hss_video_user_token;
                                $hss_video_mediaserver_ip = $xml->result->wowza_ip;

				$hss_video_m3u8 = $xml->result->m3u8;
                                $hss_video_smil = $xml->result->smil;
                                $hss_video_big_thumb_url = $xml->result->big_thumb_url;
                                $hss_rtsp_url = $xml->result->rtsp_url;
                                $referrer = site_url();

                                $content_width = $video_width;
                                $content_height = $video_height;

                                if($is_iphone){
                                        if($content_width<320){
                                                $content_width=320;
                                        }
                                }

                                if($video_width>$content_width){
                                        $mod = $content_width%40;
                                        $video_width = $content_width-$mod;
                                        $multiple = $video_width/40;
                                        $video_height = $multiple*30;
                                }

                                if($is_iphone){
                                        if($options['player_width_mobile']!="")
                                                $video_width=$options['player_width_mobile'];
                                        if($options['player_height_mobile']!="")
                                                $video_height=$options['player_height_mobile'];
                                }else{
                                        if($options['player_width_default']!="")
                                                $video_width=$options['player_width_default'];
                                        if($options['player_height_default']!="")
                                                $video_height=$options['player_height_default'];
                                }
                                $httpString = "http";
                                if (is_ssl()) {
					$httpString = "https";
					$hss_video_big_thumb_url = str_replace("http","https",$hss_video_big_thumb_url);
                                }

                                $stream_url = "$httpString://$hss_video_mediaserver_url/hss/smil:".$hss_video_smil."/playlist.m3u8".$hss_video_smil_token."&referer=".urlencode($referrer.":playersessionid:".$playersessionid);

				if($hss_video_m3u8!=""){
                                        $stream_url = $hss_video_m3u8."?referer=".urlencode($referrer.":playersessionid:".$playersessionid);
				}  

                                $subtitle_count = $xml->result->subtitle_count;
                                $subtitle_index = 1;
                                $subtitle_text = "";
                                $captions = "";
                                if($subtitle_count>0){
                                        $subtitle_text = ",
                                                tracks: [{";
                                        while($subtitle_index <= $subtitle_count)
                                        {
                                                $subtitle_label = (string)$xml->result[0]->subtitles->{'subtitle_label_'.$subtitle_index}[0];
                                                $subtitle_file = (string)$xml->result[0]->subtitles->{'subtitle_file_'.$subtitle_index}[0];
                                                $subtitle_text .= "
                                                    file: \"http://www.hoststreamsell.com/mod/secure_videos/subtitles/$subtitle_file?rand=".randomString_EMBED()."\",
                                                    label: \"$subtitle_label\",
                                                    kind: \"captions\",
                                                    \"default\": true";
                                                $subtitle_index += 1;
                                                if($subtitle_index <= $subtitle_count){
                                                        $subtitle_text .= "
                                                },{";
                                                }
                                        }
                                        $subtitle_text .= "
                                                }]";

                                        $fontSize = "";
                                        if($options["subtitle_font_size"]!=""){
                                                $fontSize = "
                                                        fontSize: ".$options["subtitle_font_size"].",";
                                        }
                                        $captions = "
                                                captions: {
                                                        color: '#FFFFFF',".$fontSize."
                                                        backgroundOpacity: 0
                                                },";
                                }

                        if ($options['jwplayer_version']=="6" or $options['jwplayer_version']=="7"){

                                $video = $video."
                                <script type=\"text/javascript\" src=\"";
                                if ($options['jwplayer_version']=="6"){
                                        $video.= $httpString."://www.hoststreamsell.com/mod/secure_videos/jwplayer-6.12/jwplayer/jwplayer.js";
                                }elseif($options['jwplayer_version']=="7"){
                                        $video.= $httpString."://www.hoststreamsell.com/mod/secure_videos/jwplayer7/jwplayer-7.0.2/jwplayer.js";
                                }else{
                                        $video.= $httpString."://www.hoststreamsell.com/mod/secure_videos/jwplayer-6.12/jwplayer/jwplayer.js";
                                }
                                $video.="\"></script>
                                <script type=\"text/javascript\">jwplayer.key=\"".$options['jwplayer_license']."\";</script>";
                                if($options["responsive_player"]==1){
                                        $responsive_width="640";
                                        if($options["player_responsive_max_width"]!="")
                                                $responsive_width=$options["player_responsive_max_width"];
                                        $video.="<div class='hss_video_player' style='max-width:".$responsive_width."px;'>";
                                }else{
                                        $video.="<div class='hss_video_player'>";
                                }
                                $video.="<div id='videoframe$videoidinner$rand'>An error occurred setting up the video player</div>
                                <SCRIPT type=\"text/javascript\">



                                if (is_iphone) { html5Player$videoidinner$rand();}
                                else if (is_ipad) { html5Player$videoidinner$rand(); }
				else if (is_silk) { newJWPlayer$videoidinner$rand(); }
                                else if (is_android) { rtspPlayer$videoidinner$rand(); }
                                else if (is_blackberry) { rtspPlayer$videoidinner$rand(); }
                                else if (is_playstation) { newJWPlayer$videoidinner$rand(); }
                                else { newJWPlayer$videoidinner$rand(); }

                                function newJWPlayer$videoidinner$rand()
                                {
                                        jwplayer('videoframe$videoidinner$rand').setup({
                                            playlist: [{
                                                image: '$hss_video_big_thumb_url',
                                                sources: [{
                                                    file: '".$stream_url."'
						}]$subtitle_text
                                            }],$captions
 					    rtmp: {
                                                bufferlength: 3,
                                                proxytype: 'best'
                                            },
                ";
                if($options['jwplayer_version']=="7" and $options['jwplayer_logo_file']!=""){
        $video.="                       logo: {
                                file: '".$options['jwplayer_logo_file']."',";
                        if($options['jwplayer_logo_link']!=""){
        $video.="
                                link: '".$options['jwplayer_logo_link']."',";
                        }
                        if($options['jwplayer_logo_hide']=="true"){
        $video.="
                                hide: '".$options['jwplayer_logo_hide']."',";
                        }
        $video.="
        },
                ";
                }
        $video.="
                                            primary: 'flash',   ";
                                if($options["responsive_player"]==1){
                                        $video.="                  width: '100%',
                                            aspectratio: '".$aspect_ratio."'";
                                }else{
                                        $video.="                 height: $video_height,
                                          width: $video_width";
                                }
        $video.="                       });
                                }

                                function rtspPlayer$videoidinner$rand()
                                {
                                        var player=document.getElementById(\"videoframe$videoidinner$rand\");
                                        player.innerHTML='<A HREF=\"rtsp://".$hss_video_mediaserver_ip."/hss/mp4:".$hss_rtsp_url."".$hss_video_smil_token."&referer=".urlencode($referrer)."\">'+
                                        '<IMG SRC=\"".$hss_video_big_thumb_url."\" '+
                                        'ALT=\"Start Mobile Video\" '+
                                        'BORDER=\"0\" '+
                                        'HEIGHT=\"$video_height\"'+
                                        'WIDTH=\"$video_width\">'+
                                        '</A>';
                                }

                                function html5Player$videoidinner$rand()
                                {
                                        var player=document.getElementById(\"videoframe$videoidinner$rand\");
                                        player.innerHTML='<video controls '+
                                        'src=\"".$stream_url."\" '+
                                        'HEIGHT=\"".$video_height."\" '+
                                        'WIDTH=\"".$video_width."\" '+
                                        'poster=\"".$hss_video_big_thumb_url."\" '+
                                        'title=\"".$hss_video_title."\">'+
                                        '</video>';
                                }
 </script>
        </div>
";

			}elseif ($options['jwplayer_version']=="7Prem" or $options['jwplayer_version']=="8"){
				if($options['jwplayer_version']=="7Prem"){
	                                $video .= "
        	                        <script type=\"text/javascript\" src=\"https://www.hoststreamsell.com/mod/secure_videos/jwplayer-7.10.7/jwplayer.js\"></script>
                	                <script type=\"text/javascript\">jwplayer.key=\"".$options['jwplayer_license']."\";</script>";
				}elseif($options['jwplayer_version']=="8"){
        	                        $video .= "
                	                <script type=\"text/javascript\" src=\"https://www.hoststreamsell.com/mod/secure_videos/jwplayer-8.1.8/jwplayer.js\"></script>
                        	        <script type=\"text/javascript\">jwplayer.key=\"".$options['jwplayer_license']."\";</script>";
				}

                                if($options["responsive_player"]==1){
                                        $responsive_width="640";
                                        if($options["player_responsive_max_width"]!="")
                                                $responsive_width=$options["player_responsive_max_width"];
                                        $video.="<div class='hss_video_player' style='max-width:".$responsive_width."px; width:100%;'>";
                                }else{
                                        $video.="<div class='hss_video_player'>";
                                }

                                $video.="<div id='videoframe$videoidinner$rand'>An error occurred setting up the video player</div>
                                <SCRIPT type=\"text/javascript\">

                                newJWPlayer$videoidinner$rand();

                                function newJWPlayer$videoidinner$rand()
                                {
                                        jwplayer('videoframe$videoidinner$rand').setup({
                                            playlist: [{
                                                image: '$hss_video_big_thumb_url',";
                if ($options['jwplayer_version']=="7Prem"){
        $video.="                              sources: [{
                                                    file: '".$stream_url."'
                                                }]";
                }else{
        $video.="                              sources: [{
                                                    file: '".$stream_url."'
                                                }]";
                }
                                                $video.="$subtitle_text
                                        }],$captions";
                if ($options['jwplayer_version']=="7Prem"){
        $video.="                                            dash: 'shaka',";
                }

if( ($options['jwplayer_version']=="7" or $options['jwplayer_version']=="7Prem" or $options['jwplayer_version']=="8") and $options['jwplayer_logo_file']!=""){
        $video.="                       logo: {
                                file: '".$options['jwplayer_logo_file']."',";
                        if($options['jwplayer_logo_link']!=""){
        $video.="
                                link: '".$options['jwplayer_logo_link']."',";
                        }
                        if($options['jwplayer_logo_hide']=="true"){
        $video.="
                                hide: '".$options['jwplayer_logo_hide']."',";
                        }
        $video.="
        } 
                ";
                }
	$video.="
        cast:{},
                ";
                                if($options["responsive_player"]==1){
                                        $video.="                  width: '100%',
                                            aspectratio: '".$aspect_ratio."'";
                                }else{
                                        $video.="                 height: $video_height,
                                          width: $video_width";
                                }
                                $video.="
                                         } );
                                }

 </script>
        </div>

                                ";


                }elseif ($options['jwplayer_version']=="videojs5"){

                                $responsiveText="";

                                if($options["responsive_player"]==1){
                                        $responsive_width="640";
                                        if($options["player_responsive_max_width"]!="")
                                                $responsive_width=$options["player_responsive_max_width"];
                                        $video.="<div class='hss_video_player' style='max-width:".$responsive_width."px; width:100%;'>";
                                        //$responsiveText="vjs-16-9";
                                        $responsiveText="vjs-fluid";
                                        //if($aspect_ratio=="4:3")
                                        //      $responsiveText="vjs-4-3";
                                }else{
                                        $video.="<div class='hss_video_player'>";
                                }

                                $video .= "


 <link href=\"$httpString://hoststreamsell.com/mod/secure_videos/videojs/video-js.css\" rel=\"stylesheet\">
  <script src=\"$httpString://hoststreamsell.com/mod/secure_videos/videojs/video.v5.19.2.js\"></script>
    <script src=\"$httpString://hoststreamsell.com/mod/secure_videos/videojs/videojs-contrib-hls.min.v5.5.0.js\"></script>
    <script src=\"$httpString://hoststreamsell.com/mod/secure_videos/videojs/videojs-contrib-quality-levels.2.0.3.js\"></script>

  <video poster=\"".$hss_video_big_thumb_url."\" id=\"my_video_$videoidinner$rand\" class=\"video-js vjs-default-skin ".$responsiveText." vjs-big-play-centered\" controls preload=\"auto\" width=\"".$video_width."\" height=\"".$video_height."\"
  data-setup='{}' crossorigin=\"anonymous\">
  <source src=\"".$stream_url."\" type='application/x-mpegURL'>";

                               $subtitle_count = $xml->result->subtitle_count;
                               $subtitle_index = 1;
                               $subtitle_text = "";
                               $captions = "";
                               if($subtitle_count>0){
                                    while($subtitle_index <= $subtitle_count)
                                    {
                                            $subtitle_label = (string)$xml->result[0]->subtitles->{'subtitle_label_'.$subtitle_index}[0];
                                            $subtitle_file = (string)$xml->result[0]->subtitles->{'subtitle_file_'.$subtitle_index}[0];
                                            $subtitle_index += 1;
                                            if(endsWith_EMBED($subtitle_file,"vtt"))
                                            {
$video .= "
<track kind=\"captions\" src=\"$httpString://www.hoststreamsell.com/mod/secure_videos/subtitles/".$subtitle_file."?rand=".randomString_EMBED()."\" srclang=\"en\" label=\"".$subtitle_label."\">";
                                            }

                                    }
                               }
$video .= "
</video>
                                <script>
                                function isIE()
                                {
                                            var isIE11 = navigator.userAgent.indexOf(\".NET CLR\") > -1;
                                                var isIE11orLess = isIE11 || navigator.appVersion.indexOf(\"MSIE\") != -1;
                                                return isIE11orLess;
                                }

                                container = document.getElementById('my_video_1');
                                if(isIE()){
                                        player = videojs('my_video_$videoidinner$rand', {techOrder: ['flash']});
                                }else{
                                        player = videojs('my_video_$videoidinner$rand');

				}
				player.qualityPickerPlugin();

 </script>
        </div>
";


                }elseif ($options['jwplayer_version']=="videojs7"){

                                $responsiveText="";

                                if($options["responsive_player"]==1){
                                        $responsive_width="640";
                                        if($options["player_responsive_max_width"]!="")
                                                $responsive_width=$options["player_responsive_max_width"];
                                        $video.="<div class='hss_video_player' style='max-width:".$responsive_width."px; width:100%;'>";
                                        //$responsiveText="vjs-16-9";
                                        $responsiveText="vjs-fluid";
                                        //if($aspect_ratio=="4:3")
                                        //      $responsiveText="vjs-4-3";
                                }else{
                                        $video.="<div class='hss_video_player'>";
                                }

                                $video .= "

 <link href=\"$httpString://hoststreamsell.com/mod/secure_videos/videojs/7/video-js.css\" rel=\"stylesheet\">
  <script src=\"$httpString://hoststreamsell.com/mod/secure_videos/videojs/7/video.js\"></script>
<script src=\"$httpString://hoststreamsell.com/mod/secure_videos/videojs/new/videojs-flash.2.1.1.min.js\"></script>
    <script src=\"$httpString://hoststreamsell.com/mod/secure_videos/videojs/videojs-contrib-quality-levels.2.0.3.js\"></script>
    <script src=\"$httpString://hoststreamsell.com/mod/secure_videos/videojs/7/videojs-hls-quality-selector.min.js\"></script>
 <script src=\"$httpString://hoststreamsell.com/mod/secure_videos/videojs/7/silvermine-videojs-chromecast.min.js\"></script>
<link href=\"$httpString://hoststreamsell.com/mod/secure_videos/videojs/7/silvermine-videojs-chromecast.css\" rel=\"stylesheet\">
<script type=\"text/javascript\" src=\"https://www.gstatic.com/cv/js/sender/v1/cast_sender.js?loadCastFramework=1\"></script>

  <video poster=\"".$hss_video_big_thumb_url."\" id=\"my_video_$videoidinner$rand\" class=\"video-js vjs-default-skin ".$responsiveText." vjs-big-play-centered\" controls preload=\"auto\" width=\"".$video_width."\" height=\"".$video_height."\"
  data-setup='{}' crossorigin=\"anonymous\">
";

                               $subtitle_count = $xml->result->subtitle_count;
                               $subtitle_index = 1;
                               $subtitle_text = "";
                               $captions = "";
                               if($subtitle_count>0){
                                    while($subtitle_index <= $subtitle_count)
                                    {
                                            $subtitle_label = (string)$xml->result[0]->subtitles->{'subtitle_label_'.$subtitle_index}[0];
                                            $subtitle_file = (string)$xml->result[0]->subtitles->{'subtitle_file_'.$subtitle_index}[0];
                                            $subtitle_index += 1;
                                            if(endsWith_EMBED($subtitle_file,"vtt"))
                                            {
$video .= "
<track kind=\"captions\" src=\"$httpString://www.hoststreamsell.com/mod/secure_videos/subtitles/".$subtitle_file."?rand=".randomString_EMBED()."\" srclang=\"en\" label=\"".$subtitle_label."\">";
                                            }

                                    }
                               }
$video .= "
</video>
                                <script>
                                function isIE()
                                {
                                            var isIE11 = navigator.userAgent.indexOf(\".NET CLR\") > -1;
                                                var isIE11orLess = isIE11 || navigator.appVersion.indexOf(\"MSIE\") != -1;
                                                return isIE11orLess;
                                }

                                if(isIE()){
                                        player = videojs('my_video_$videoidinner$rand', {techOrder: ['flash']});
                                }else{
                                        player = videojs('my_video_$videoidinner$rand', { controls: true, techOrder: ['chromecast', 'html5'],  plugins: { chromecast: {preloadWebComponents: true, addButtonToControlBar: true}  },
                                        html5: {
                                                hls: {
                                                  overrideNative: !videojs.browser.IS_ANY_SAFARI
                                                }
                                              }
                                        }
                                        );

                                }
                                player.src({
                                        src: \"".$stream_url."\",
                                        type: 'application/x-mpegURL',
                                });
                                player.hlsQualitySelector();
				player.chromecast();
 </script>
        </div>
";



                        }




if($options['log_player_events']=="true"){

  if($options['jwplayer_version']=="7" or $options['jwplayer_version']=="7Prem" or $options['jwplayer_version']=="8"){

    $video.="

                                <SCRIPT type=\"text/javascript\">
                                var agent=navigator.userAgent.toLowerCase();
             var videoreferrer=encodeURI(window.location.href);
jwplayer().on('ready',function(e) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=ready&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&setupTime='+e.setupTime+'&userid=".$userId."&suid=".$suid."&file='+jwplayer().getPlaylistItem(0).file,
                        dataType: 'jsonp',
                });
});
jwplayer().on('setupError',function(e) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=setupError&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&message='+e.message+'&userid=".$userId."&suid=".$suid."',
                        dataType: 'jsonp',
                });
});
jwplayer().on('error',function(e) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=error&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&message='+e.message,
                        dataType: 'jsonp',
                });
        jwplayer().load(jwplayer().getPlaylist());
});
jwplayer().on('buffer',function(e) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=buffer&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&oldstate='+e.oldstate+'&newstate='+e.newstate+'&reason='+e.reason,
                        dataType: 'jsonp',
                });
});
jwplayer().on('play',function(e) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=play&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&oldstate='+e.oldstate,
                        dataType: 'jsonp',
                });
});
jwplayer().on('pause',function(e) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=pause&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&oldstate='+e.oldstate,
                        dataType: 'jsonp',
                });
});
jwplayer().on('seek',function(e) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=seek&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&position='+e.position+'&offset='+e.offset,
                        dataType: 'jsonp',
                });
});
jwplayer().on('idle',function(e) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=idle&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&oldstate='+e.oldstate,
                        dataType: 'jsonp',
                });
});
jwplayer().on('complete',function(e) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=complete&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."',
                        dataType: 'jsonp',
                });
});
jwplayer().on('firstFrame',function(e) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=firstFrame&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&loadTime='+e.loadTime,
                        dataType: 'jsonp',
                });
});
jwplayer().on('levelsChanged',function(e) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=levelsChanged&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&currentQuality='+e.currentQuality,
                        dataType: 'jsonp',
                });
});
jwplayer().on('fullscreen',function(e) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=fullscreen&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&fullscreen='+e.fullscreen,
                        dataType: 'jsonp',
                });
});
jwplayer().on('resize',function(e) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=resize&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&width='+e.width+'&height='+e.height,
                        dataType: 'jsonp',
                });
});
  </script>
";


}elseif($options['jwplayer_version']=="6"){

$video.="

                                <SCRIPT type=\"text/javascript\">
                                var agent=navigator.userAgent.toLowerCase();

jwplayer().onReady(function(event) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw6&videoid=".$hss_video_id."&event=ready&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&file='+jwplayer().getPlaylistItem(0).file,
                        dataType: 'jsonp',
                });
});
jwplayer().onSetupError(function(event) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw6&videoid=".$hss_video_id."&event=setupError&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&message='+event.message,
                        dataType: 'jsonp',
                });
});
jwplayer().onError(function(event) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw6&videoid=".$hss_video_id."&event=error&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&message='+event.message,
                        dataType: 'jsonp',
                });
});
jwplayer().onBuffer(function(event) {
        jQuery.ajax({
                        url: $httpString'://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw6&videoid=".$hss_video_id."&event=buffer&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&oldstate='+event.oldstate,
                        dataType: 'jsonp',
                });
});
jwplayer().onPlay(function(event) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw6&videoid=".$hss_video_id."&event=play&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&oldstate='+event.oldstate,
                        dataType: 'jsonp',
                });
});
jwplayer().onPause(function(event) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw6&videoid=".$hss_video_id."&event=pause&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&oldstate='+event.oldstate,
                        dataType: 'jsonp',
                });
});
jwplayer().onSeek(function(event) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw6&videoid=".$hss_video_id."&event=seek&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&position='+event.position+'&offset='+event.offset,
                        dataType: 'jsonp',
                });
});
jwplayer().onIdle(function(event) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw6&videoid=".$hss_video_id."&event=idle&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&oldstate='+event.oldstate,
                        dataType: 'jsonp',
                });
});
jwplayer().onComplete(function(event) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw6&videoid=".$hss_video_id."&event=complete&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."',
                        dataType: 'jsonp',
                });
});
jwplayer().onFullscreen(function(event) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw6&videoid=".$hss_video_id."&event=fullscreen&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&fullscreen='+event.fullscreen,
                        dataType: 'jsonp',
                });
});
jwplayer().onResize(function(event) {
        jQuery.ajax({
                        url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=jw6&videoid=".$hss_video_id."&event=resize&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&width='+event.width+'&height='+event.height,
                        dataType: 'jsonp',
                });
});
  </script>


    ";
  }elseif($options['jwplayer_version']=="videojs5" or $options['jwplayer_version']=="videojs7"){

        $subtitle_language = "";
        if(isset( $options['user_settings'] ) )
                if(isset( $options['user_settings'][$user_ID] ) )
                        $subtitle_language = $options['user_settings'][$user_ID]["subtitle_language"];

        $audio_language = "";
        if(isset( $options['user_settings'] ) )
                if(isset( $options['user_settings'][$user_ID] ) )
                        $audio_language = $options['user_settings'][$user_ID]["audio_language"];

    $video.="


                                <SCRIPT type=\"text/javascript\">

                                var agent=navigator.userAgent.toLowerCase();
             var videoreferrer=encodeURI(window.location.href);
                var video = videojs('my_video_$videoidinner$rand').ready(function(){
                var player = this;


                player.textTracks().on('change', function action(event) {

let tracks = player.textTracks();

for (let i = 0; i < tracks.length; i++) {
  let track = tracks[i];

  if (track.kind === 'captions' && track.mode === 'showing') {
    console.log('subtitle changed '+track.label);

        var data = {
            action: 'hss_embed_store_user_setting',
            setting_name: 'subtitle_language',
            setting_value: track.label,
        };

        jQuery.post(ajaxurl, data);

  }
}
                });

                player.audioTracks().on('change', function action(event) {

let audioTracks = player.audioTracks();

for (let i = 0; i < audioTracks.length; i++) {
  let track = audioTracks[i];

  if (track.enabled) {
    console.log('audio changed '+track.label);

        var data = {
            action: 'hss_embed_store_user_setting',
            setting_name: 'audio_language',
            setting_value: track.label,
        };

        jQuery.post(ajaxurl, data);

  }
}



                });


                 player.on('loadedmetadata', function() {
                        jQuery.ajax({
                                dataType: 'jsonp',
                                url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=loadedmetadata&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&file='+player.currentSrc(),
                        });
        ";

        if($subtitle_language!=""){
        $video.="

let tracks = player.textTracks();

for (let i = 0; i < tracks.length; i++) {
  let track = tracks[i];
  if (track.kind === 'captions' && track.label === '".$subtitle_language."') {
    track.mode = 'showing';
        console.log('auto setting subtitles to '+track.label);
  }
}
        ";
        }

        if($audio_language!=""){
        $video.="

let audioTracks = player.audioTracks();

for (let i = 0; i < audioTracks.length; i++) {
  let track = audioTracks[i];
  if (track.label === '".$audio_language."') {
    track.enabled = true;
        console.log('auto setting audio to '+track.label);
  }
}
        ";
        }


        $video.="


                  });
                 player.on('play', function() {
                        jQuery.ajax({
                                dataType: 'jsonp',
                                url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=play&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&file='+player.currentSrc(),
                        });
                  });
                 player.on('ended', function() {
                        jQuery.ajax({
                                dataType: 'jsonp',
                                url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=ended&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&file='+player.currentSrc(),
                        });
                  });
                 player.on('pause', function() {
                        jQuery.ajax({
                                dataType: 'jsonp',
                                url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=pause&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&file='+player.currentSrc(),
                        });
                  });
                 player.on('seeking', function() {
                        console.log('Video seeking: ' + player.currentTime());
                        jQuery.ajax({
                                dataType: 'jsonp',
                                url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=seeking&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&file='+player.currentSrc()+'&offset='+player.currentTime().toFixed(2),
                        });
                 });
                 player.on('seeked', function() {
                        console.log('Video seek ended: ' + player.currentTime());
                  });
                 player.on('error', function(event) {
                        console.log(event);
                        event.stopImmediatePropagation();
                        var error = this.player().error();
                        console.log('error!', error.code, error.type , error.message);
                        jQuery.ajax({
                                dataType: 'jsonp',
                                url: '$httpString://eventsapi.hoststreamsell.com/v1/logevent/ping.gif?player=".$options['jwplayer_version']."&videoid=".$hss_video_id."&event=error&referrer='+videoreferrer+'&playersessionid=".$playersessionid."&browser='+agent+'&userid=".$userId."&suid=".$suid."&file='+player.currentSrc()+'&message='+error.message+'&code='+error.code+'&offset='+player.currentTime().toFixed(2),
                        });
                  });



                });

</script>


";
                        }



}


//$user_can_download="false";

if($user_can_download=="true" and $download=="true" and $version=="full"){
                                        $video .= "<div class='hss_download_button'><input type='button' id='$hss_video_id' class='myajaxdownloadlinks_EMBED' value='Get Download Links'></div>
                                        <div class='hss_download_links' id='download_links_$hss_video_id'></div>";
                                }






//			}

		}
		echo $video;

      $myvariable = ob_get_clean();
        return $myvariable;

}



function get_video_download_links_EMBED($hss_video_id,$videolink) {

        global $user_ID;
        $options = get_option('hss_embed_options');
        $userId = $user_ID;

        //$encode_id = 162;
        _log("get_video_download_links ".$hss_video_id." ".$videolink);

	$dbid=0;
	if($options['database_id']!="")
		$dbid=$options['database_id'];

                $params = array(
                   'method' => 'secure_videos.get_all_video_download_links',
                   'api_key' => $options['api_key'],
                   'video_id' => $hss_video_id,
                   'private_user_id' => $userId,
                   'database_id' => $dbid,
		   'force_allow' => "yes"
                );
                _log($params);
                $response = wp_remote_post( "https://www.hoststreamsell.com/services/api/rest/xml/", array(
                        'method' => 'POST',
                        'timeout' => 15,
                        'redirection' => 5,
                        'httpversion' => '1.0',
                        'blocking' => true,
                        'headers' => array(),
                        'body' => $params,
                        'cookies' => array()
                    )
                );
                $res = "";
                if( is_wp_error( $response ) ) {
                   $return_string .= 'Error occured retieving video information, please try refresh the page';
                } else {
                   $res = $response['body'];
                }

                $xml = new SimpleXMLElement($res);
                _log($xml);

                $purchase_option_count = (int)$xml->result[0]->download_option_count;
                $option_index = 1;
                $return_string = "";
                if($purchase_option_count > 0)
                {
                        $return_string = "<div>Video file downloads:</div>";
                        while($option_index <= $purchase_option_count)
                        {
                                $url = $xml->result[0]->{'download_option'.$option_index}[0]->url;
                                $name = $xml->result[0]->{'download_option'.$option_index}[0]->name;
                                #$return_string = $return_string.'<LI><a href="'.$url.'">'.$name.'</a></LI>';
                                $return_string = $return_string.'<div class="hss_download_file"><a href="'.$url.'">'.$name.'</a></div>';
                                $option_index+=1;
                        }
                        //$return_string = $return_string."</UL>";
                }else{
                        $return_string = "<div>No Video file downloads..</div>";
                }


                return $return_string;
}

function randomString_EMBED($length = 12) {
       $str = "";
       $characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
       $max = count($characters) - 1;
       for ($i = 0; $i < $length; $i++) {
               $rand = mt_rand(0, $max);
               $str .= $characters[$rand];
       }
       return $str;
}

function endsWith_EMBED($haystack, $needle)
{
	$length = strlen($needle);
	if ($length == 0) {
	       return true;
	}

        return (substr($haystack, -$length) === $needle);
}

function get_the_user_ip_EMBED() {
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
	         $ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
	         $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
	         $ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

if(!function_exists('_log')){
  function _log( $message ) {
    if( WP_DEBUG === true ){
      if( is_array( $message ) || is_object( $message ) ){
        error_log( print_r( $message, true ) );
      } else {
        error_log( $message );
      }
    }
  }
}

?>
