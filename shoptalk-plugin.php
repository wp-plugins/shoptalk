<?php
/*
Plugin Name: Shop Talk Latest Shopping News
Plugin URI: http://www.shopping.aol.com/
Description: A customizable widget which displays the latest Shopping Talks around the world.
Version: 1.0
Author: AOL Shopping
Author URI: http://www.shopping.aol.com/
License: GPL3
Developer: Dhanu Gupta
*/
require_once("XMLParser.php");
if (!defined("ch"))
{
function setupch()
{
$ch = curl_init();
$c = curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
return($ch);
}

define("ch", setupch());

function curl_get_contents($url)
{
$c = curl_setopt(ch, CURLOPT_URL, $url);
return(curl_exec(ch));
}
}

function shoptalk()
{
  $options = get_option("widget_shoptalk");
  if (!is_array($options)){
    $options = array(
      'title' => 'Shop Talk News',
      'news' => '3',
      'chars' => '30'
    );
  }
  
  global $post; //Define the global post object to access the post tags and categories
  
  if($taxonomy == '') { $taxonomy = 'post_tag'; }
	
	$tags = wp_get_post_terms($post->ID, $taxonomy);
	$category = wp_get_post_terms($post->ID, 'category');
	if($category){$first_category = $category[0]->slug;}
	if($tags) { $first_tag 	= $tags[0]->slug; }

    $query_term = "";
    if(strlen($first_tag) >0) { $query_term = $first_tag;} else { $query_term =$first_category;}
  
  //Feed URL
 	$url = "http://shopping.aol.com/articles/tag/".$query_term."/rss.xml";
	$xml = curl_get_contents($url);
	//Set up the parser object
	$parser = new xmlToArrayParser($xml);
	$domArr = $parser->array; 
     
   
    //Stylesheet
    echo "<style>
    .gotd li { 
    clear: left;
    height: 110px;
    overflow: hidden;
    padding: 0;
    width: 300px;}
.gotd { margin-top:5px;}
.gotd a{outline: 0 none;text-decoration: none;float:left;}
.gotd img {  border: 1px solid #DADADA;box-shadow: 0 0 5px #CCCCCC;margin: 0 14px 4px 0;padding: 10px;width:75px;height:75px; float:left;}
.gotd h1 {color: #1D5287;font-size: 18px; }
.gotd .link { color:#DD490B;}</style>";
	
	if(count($domArr['rss']['channel']['item']) == 0) {
    $url = "http://shopping.aol.com/articles/rss.xml";
	$xml = curl_get_contents($url);
	//Set up the parser object
	$parser = new xmlToArrayParser($xml);
	$domArr = $parser->array;
    }
    if(count($domArr) >0) {
 		echo '<ul>';
		$itemarr = $domArr['rss']['channel']['item'];
		
			for ($i=0;$i<3;$i++){
				$titl= $domArr['rss']['channel']['item'][$i]['title'];
				$link= $domArr['rss']['channel']['item'][$i]['link'];
				$img= $domArr['rss']['channel']['item'][$i]['image1'];
				$titl = substr($titl,0,41)." ...";
				if(is_array($link)) {$link=$link[0];}
				echo '<div class="gotd">';
				echo '<li><a style="line-height:18px;" href="'.$link.'?wp=shoptalk" title="'.$titl.'"><img src="'.$img.'" style="width:75px;height:75px;"/>'.$titl.'</a>';
				echo '<div style=" margin-left: 45px;margin-top: -80px;float:left;">';
echo '<iframe src="http://www.facebook.com/plugins/like.php?&amp;href='.$link.'&amp;send=false&amp;layout=box_count&amp;width=178&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=66" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:56px; height:62px;margin-top:15px;margin-left:92px;" allowTransparency="true"></iframe>';
echo '<a style="margin-left:90px;" href="https://twitter.com/share" class="twitter-share-button" data-url="'.$link.'" data-count="vertical" data-via="aolshopping" data-text="OMG! I love this talk.." data-lang="en">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>';
echo '</div></div></li>';
			}
			echo '</ul>';
		}
	}

function widget_shoptalk($args)
{
  extract($args);
  
  $options = get_option("widget_shoptalk");
  if (!is_array($options)){
    $options = array(
      'title' => 'Shop Talk News',
      'news' => '3',
      'chars' => '30'
    );
  }
  
  echo $before_widget;
  echo $before_title;
  echo $options['title'];
  echo $after_title;
  shoptalk();
  echo $after_widget;
}

function shoptalk_control()
{
  $options = get_option("widget_shoptalk");
  if (!is_array($options)){
    $options = array(
      'title' => 'Shop Talk News',
      'news' => '5',
      'chars' => '30'
    );
  }
  
  if($_POST['shoptalk-Submit'])
  {
    $options['title'] = htmlspecialchars($_POST['shoptalk-WidgetTitle']);
    $options['news'] = htmlspecialchars($_POST['shoptalk-NewsCount']);
    $options['chars'] = htmlspecialchars($_POST['shoptalk-CharCount']);
    update_option("widget_shoptalk", $options);
  }
?> 
  <p>
    <label for="shoptalk-WidgetTitle">Widget Title: </label>
    <input type="text" id="shoptalk-WidgetTitle" name="shoptalk-WidgetTitle" value="<?php echo $options['title'];?>" />
    <br /><br />
    <label for="shoptalk-NewsCount">Max. News: </label>
    <input type="text" id="shoptalk-NewsCount" name="shoptalk-NewsCount" value="<?php echo $options['news'];?>" />
    <br /><br />
    <label for="shoptalk-CharCount">Max. Characters: </label>
    <input type="text" id="shoptalk-CharCount" name="shoptalk-CharCount" value="<?php echo $options['chars'];?>" />
    <br /><br />
    <input type="hidden" id="shoptalk-Submit"  name="shoptalk-Submit" value="1" />
  </p>
  
<?php
}

function shoptalk_init()
{
  register_sidebar_widget(__('Shop Talk News'), 'widget_shoptalk');    
  register_widget_control('Shop Talk News', 'shoptalk_control', 300, 200);
}
//plugin loaded
add_action("plugins_loaded", "shoptalk_init");
?>