<?php
/*
Plugin Name: Kento Vote
Plugin URI: http://kentothemes.com
Description: Kento Vote Plugin is count your vote and display voter thumbnail under vote button who voted on your post.
Version: 1.0
Author: KentoThemes
Author URI: http://kentothemes.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

function kento_vote_latest_jquery() {
	wp_enqueue_script('jquery');
}
add_action('init', 'kento_vote_latest_jquery');
wp_enqueue_script('inkthemes', plugins_url( '/js/kento-vote.js' , __FILE__ ) , array( 'jquery' ));
wp_localize_script( 'inkthemes', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php')));
define('KENTO_VOTE_PLUGIN_PATH', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) . '/' );
wp_enqueue_style('kento-vote-style', KENTO_VOTE_PLUGIN_PATH.'css/style.css');
register_activation_hook(__FILE__, kento_vote_install());
Register_uninstall_hook(__FILE__, kento_vote_drop());
function kento_vote_install() {
    global $wpdb;
        $sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "kento_vote"
                 ."( UNIQUE KEY id (id),
					id int(100) NOT NULL AUTO_INCREMENT,
					postid  int(10) NOT NULL,
					upvote  int(10) NOT NULL,
					downvote  int(10) NOT NULL)";
		$wpdb->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "kento_vote_info"
                 ."( UNIQUE KEY id (id),
					id int(100) NOT NULL AUTO_INCREMENT,
					postid  int(10) NOT NULL,
					userid  int(10) NOT NULL,				
					votetype int(2) NOT NULL)";
		$wpdb->query($sql);
		}

function kento_vote_drop() {
	if ( get_option('kento_vote_deletion') == 1 ) {
		
		global $wpdb;
		$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'kento_vote');
		$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'kento_vote_info');
	}
}

function is_user_logged($is_logged_in)

	{
		if ( is_user_logged_in() )
		return "looged";
		else
		return "notlooged";

	}
function voted($post_id)

	{

	if ( is_user_logged_in() ) {
		$postid = get_the_ID();
		global $wpdb;
		$userid = get_current_user_id();
    	$table = $wpdb->prefix . "kento_vote_info";
		
		$wpdb->get_results("SELECT * FROM $table WHERE userid = '$userid' AND postid = '$postid'", ARRAY_A);
		
		if($wpdb->num_rows > 0 )
			{
				return "voted";
			}
			
		else
			{
			return "notvoted";
			}
		}

	}
function voted_status($postid)
	{
	if ( is_user_logged_in() ) {
		$postid = get_the_ID();
		global $wpdb;
		$userid = get_current_user_id();
    	$table = $wpdb->prefix . "kento_vote_info";
		
		$result = $wpdb->get_results("SELECT votetype FROM $table WHERE userid = '$userid' AND postid = '$postid' ", ARRAY_A);
		$voted_status = $result[0]['votetype'];
		
		if($voted_status==1){
			return "upvoted";
			}
		elseif($voted_status==2){
			return "downvoted";
			}
		}
	}
function who_voted($postid)
	{
		global $wpdb;
    	$table = $wpdb->prefix . "kento_vote_info";
		$result = $wpdb->get_results("SELECT userid FROM $table WHERE postid = '$postid' LIMIT 10", ARRAY_A);
		$total_vote = $wpdb->num_rows;
		for($i=0; $i<$total_vote; $i++)
			{	
				$userid.= get_avatar($result[$i]['userid'],100);
			}
		return $userid;
	}
function kento_vote_insert()
	{
	$postid = $_POST['postid'];
	$votetype = $_POST['votetype'];
    global $wpdb;
    $table = $wpdb->prefix . "kento_vote";
	
	$wpdb->get_results( 'SELECT * FROM '.$table.' WHERE postid = "'.$postid.'"' );
	
	if($wpdb->num_rows > 0 )
		{
			if(voted($post_id)=="notvoted"){
			if($votetype=="upvote")
				{
					$wpdb->query("UPDATE $table SET upvote = upvote+1 WHERE postid = '".$postid."'");
					
					kento_vote_info_insert($postid,$votetype);
				}
			elseif($votetype=="downvote")
				{
					$wpdb->query("UPDATE $table SET downvote = downvote+1 WHERE postid = '".$postid."'");
					kento_vote_info_insert($postid,$votetype);
				}
				}
		}
	else 
		{
			if($votetype=="upvote")
				{
					$wpdb->query("INSERT INTO $table VALUES('',$postid,1,0)");
					
					global $wpdb;
					$table = $wpdb->prefix . "kento_vote_info";
					$postid = $postid;
					$userid = get_current_user_id();
					$votetype = "1";
					$wpdb->query("INSERT INTO $table VALUES('',$postid,$userid,$votetype)");
				}
			elseif($votetype=="downvote")
				{
					$wpdb->query("INSERT INTO $table VALUES('',$postid,0,1)");
					
					global $wpdb;
					$table = $wpdb->prefix . "kento_vote_info";
					$postid = $postid;
					$userid = get_current_user_id();
					$votetype = "2";
					$wpdb->query("INSERT INTO $table VALUES('',$postid,$userid,$votetype)");
				}
		}
	die();
	return true;
	}
function kento_vote_info_insert($postid,$votetype)
	{
		global $wpdb;
		$table = $wpdb->prefix . "kento_vote_info";
		$postid = $postid;
		$votetype = $votetype;
		$userid = get_current_user_id();
		$wpdb->get_results("SELECT * FROM $table WHERE userid = '$userid' AND postid = '$postid'", ARRAY_A);
		if($wpdb->num_rows > 0 )
			{
			}
		else
			{	if($votetype == "upvote")
					{
						$votetype = "1";
						$wpdb->query("INSERT INTO $table VALUES('',$postid,$userid,$votetype)");
					}
				elseif($votetype == "downvote")
					{
						$votetype = "2";
						$wpdb->query("INSERT INTO $table VALUES('',$postid,$userid,$votetype)");
					}
			}
	}
add_action('wp_ajax_kento_vote_insert', 'kento_vote_insert');
add_action('wp_ajax_nopriv_kento_vote_insert', 'kento_vote_insert');
function up_count($post_id)
	{	
		$postid = $post_id;
		global $wpdb;
    	$table = $wpdb->prefix . "kento_vote";
		$result = $wpdb->get_results("SELECT upvote FROM $table WHERE postid = '$postid'", ARRAY_A);
		$up_count = $result[0]['upvote'];
		if($up_count==""){
			return "0";
			}
		else{
			return $up_count;
			}
	}
function down_count($post_id)
	{	
		$postid = $post_id;
		global $wpdb;
    	$table = $wpdb->prefix . "kento_vote";
		$result = $wpdb->get_results("SELECT downvote FROM $table WHERE postid = '$postid'", ARRAY_A);
		$down_count = $result[0]['downvote'];
		if($down_count==""){
			return "0";
			}
		else{
			return $down_count;
			}
	}




//Login form Arguments
function kento_vote_login_box()
	{

	$login_box .= "<div id='kento-vote-login' >";
	$login_box .= "<p><strong>Please Login To Like this Post</strong></p>";
	$login_box  .= "<form action='".get_option('home')."/wp-login.php' method='post'>";
	$login_box .= "<p class='login-username'><label for='user_login'>Username</label><input type='text' name='log' id='log' value='".wp_specialchars(stripslashes($user_login), 1)."' size='20' /></p>";
	
	$login_box .= "<p class='login-password'><label for='user_pass'>Password</label><input type='password' name='pwd' id='pwd' size='20' /></p>";
	$login_box .= "<p class='login-remember'><input name='rememberme' id='rememberme' type='checkbox' checked='checked' value='forever' /><label for='rememberme'>Remember me</label></p>";
	$login_box .= "<p class='login-submit'><input type='submit' name='submit' value='Send' class='button' /></p>";
	$login_box .= "<p>";

 	$login_box .= "<input type='hidden' name='redirect_to' value='".$_SERVER['REQUEST_URI']."' />";   
	$login_box .= "</p>";
	$login_box .= "</form>";
	$login_box .= "Or";
	$login_box .= "<div class='register-box'><a href='".site_url('/wp-login.php?action=register')."'>Register</a></div>";
	$login_box .= "</div>";
	return $login_box ;
	}
















function show_form($cont){
$cont.= "<div id='kento-vote' class='".voted($post_id)."' logged='".is_user_logged($is_logged_in)."' >";
$cont.=  "<strong>Vote on This!</strong><br /><br />";
$cont.=   "<ul class='".voted_status($postid)."'><li id='kento-vote-up' votetype='upvote' postid='".get_the_ID()."' class='kento-vote-up vote-button' ><div class='kento-vote-info'><span class='up-vote-value' upvotevalue='".up_count(get_the_ID())."'>".up_count(get_the_ID())."</span> <span class='up-vote-text'>Up Vote</span></div></li>";
$cont.=  "<li id='kento-vote-down' votetype='downvote' postid='".get_the_ID()."' class='kento-vote-down vote-button'  ><div class='kento-vote-info'><span class='down-vote-value' downvotevalue='".down_count(get_the_ID())."'>".down_count(get_the_ID())."</span><span class='down-vote-text'> Down Vote</span></div></li></ul>";
$cont.= "<div class='who-voted'>".who_voted(get_the_ID())."</div>";
$cont.= kento_vote_login_box();
$cont.= "<div class='login-bg'></div>";
$cont.=  "</div>";
if(is_single()){
return $cont;
}
}
add_filter('the_content', 'show_form');
?>