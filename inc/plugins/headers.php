<?php

// Plugin : Add Meta Tags
// Author : Harshit Shrivastava

// Disallow direct access to this file for security reasons

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}
$plugins->add_hook("showthread_start", "headers_showThread");
$plugins->add_hook("index_start", "headers_showIndex");
$plugins->add_hook("forumdisplay_start", "headers_showForum");

function headers_info()
{
	return array(
		"name"			=> "Add Meta Tags",
		"description"	=> "Add Meta Tags to your pages",
		"website"		=> "http://mybb.com",
		"author"		=> "Harshit Shrivastava",
		"authorsite"	=> "mailto:harshit_s21@rediffmail.com",
		"version"		=> "1.0",
		"compatibility" => "18*,16*"
	);
}

function headers_showIndex()
{
	global $mybb,$db, $lang, $thread, $headerinclude,$theme;
	if ($mybb->settings['headers_show'] == 1)
	{		
		$image = empty($mybb->settings['headers_image']) ? htmlspecialchars_uni($theme['logo']) : htmlspecialchars_uni($mybb->settings['headers_image']);
		$headerinclude .="\n";
		$headerinclude = '<meta name="description" content="'.htmlspecialchars_uni($mybb->settings['headers_index']).'" /><meta property="image" content="'.$image.'" />'.$headerinclude;
	}
}
function headers_showForum()
{
	global $mybb,$db, $lang, $thread, $headerinclude,$theme,$foruminfo,$mybb;
	if ($mybb->settings['headers_show'] == 1)
	{		
		$image = empty($mybb->settings['headers_image']) ? htmlspecialchars_uni($theme['logo']) : htmlspecialchars_uni($mybb->settings['headers_image']);
		
		/*if(empty(get_forum($mybb->input['fid'])['description']))
			$description =  $mybb->settings['headers_index'];
		else
			$description =  get_forum($mybb->input['fid'])['description'];
		*/
		$headerinclude .="\n";
		$headerinclude = '<meta name="description" content="'.htmlspecialchars_uni($description).'" /><meta property="image" content="'.$image.'" />'.$headerinclude;
	}
}
function headers_showThread()
{
	global $mybb,$db, $lang, $thread, $headerinclude,$theme;
	if ($mybb->settings['headers_show'] == 1)
	{
		
		$options = array(
			"limit" => 1
		);
		$query = $db->simple_select("posts", "message", "tid=".(int)$mybb->input['tid'], $options);
		$post = $db->fetch_array($query);
		
		require_once MYBB_ROOT."inc/class_parser.php";
		$parser = new postParser; 
		
		$parser_options = array(
    'allow_html' => 'no',
    'allow_mycode' => 'no',
    'allow_smilies' => 'no',
    'allow_imgcode' => 'no',
	'allow_videocode'=>'no',
    'filter_badwords' => 'no',
    'nl2br' => 'no'
);

		$message = strip_tags($parser->parse_message($post['message'], $parser_options)); 
		
		if(my_strlen($message) > (int)$mybb->settings['headers_charLimit'] && !empty($mybb->settings['headers_charLimit']))
			$message = my_substr($message, 0, (int)$mybb->settings['headers_charLimit']).'...';

		$image = empty($mybb->settings['headers_image']) ? htmlspecialchars_uni($theme['logo']) : htmlspecialchars_uni($mybb->settings['headers_image']);
		$headerinclude .="\n";
		$headerinclude = '<meta name="og:description" content="'.htmlspecialchars_uni($message).'" /><meta name="description" content="'.htmlspecialchars_uni($message).'" /><meta property="og:image" content="'.$image.'" /><meta property="image" content="'.$image.'" /><meta name="keywords" content="'.htmlspecialchars_uni($mybb->settings['bbname']).','.htmlspecialchars_uni($thread['subject']).'" />'.$headerinclude;
	}
}

function headers_activate()
{
global $db, $mybb;
$headers_group = array(
        'gid'    => 'NULL',
        'name'  => 'headers',
        'title'      => 'Add Meta Tags',
        'description'    => 'Add meta tags to your pages',
        'disporder'    => "1",
        'isdefault'  => "0",
    ); 
$db->insert_query('settinggroups', $headers_group);
$gid = $db->insert_id(); 
// Enable / Disable
$headers_setting1 = array(
        'sid'            => 'NULL',
        'name'        => 'headers_show',
        'title'            => 'Show on board',
        'description'    => 'If you set this option to yes, this plugin will add headers to your pages.',
        'optionscode'    => 'yesno',
        'value'        => '1',
        'disporder'        => 1,
        'gid'            => intval($gid),
    );
$headers_setting2 = array(
        'sid'            => 'NULL',
        'name'        => 'headers_image',
        'title'            => 'Enter Image path',
        'description'    => 'Enter image path for your meta tags',
        'optionscode'    => 'text',
        'value'        => '',
        'disporder'        => 2,
        'gid'            => intval($gid),
    );
$headers_setting3 = array(
        'sid'            => 'NULL',
        'name'        => 'headers_charLimit',
        'title'            => 'Character limit to show on headers for threads',
        'description'    => 'Set the character limit to be shown on the meta description. 0 to disable it.',
        'optionscode'    => 'text',
        'value'        => '0',
        'disporder'        => 3,
        'gid'            => intval($gid),
    );
	
$headers_setting4 = array(
        'sid'            => 'NULL',
        'name'        => 'headers_index',
        'title'            => 'Meta tags for Index page',
        'description'    => 'Meta tags for Index page.',
        'optionscode'    => 'textarea',
        'value'        => 'Welcome to '.$mybb->settings['bbname'],
        'disporder'        => 4,
        'gid'            => intval($gid),
    );
$db->insert_query('settings', $headers_setting1);
$db->insert_query('settings', $headers_setting2);
$db->insert_query('settings', $headers_setting3);
$db->insert_query('settings', $headers_setting4);
  rebuild_settings();
}
function headers_deactivate()
{
  global $db;
  $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name = 'headers_show'");
  $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name = 'headers_image'");
  $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name = 'headers_charLimit'");
  $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name = 'headers_index'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='headers'");
  rebuild_settings();
}
?>
