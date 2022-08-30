<?php
global $wpdb;
$myid = uniqid(rand()).date('YmdHis');
$base = 0;

// Translation for special keywords
$btrans = array("%ICL_LANGUAGE_NAME%"=>ICL_LANGUAGE_NAME,"\n"=>"<br />","%ICL_LANGUAGE_URLSLUG%"=>$url_slug,"%ICL_LANGUAGE_SHORT%"=>ICL_LANGUAGE_CODE);

if(isset($instance['tct-menulist-box'])  && is_array($instance['tct-menulist-box']) && sizeof($instance['tct-menulist-box'])>0){
	echo "<div class=\"tct-menulist\">\n";
	foreach($instance['tct-menulist-box'] as $listbox){
		echo "<div class=\"tct-menulist-item\">\n";	
			echo "<a class=\"tct-menulist-title\">".$listbox['tct-menulist-title']."</a>\n";
			if(isset($listbox['tct-menulist-linkitems'])  && is_array($listbox['tct-menulist-linkitems']) && sizeof($listbox['tct-menulist-linkitems'])>0){
				echo "<div class=\"tct-menulist-content\">\n";
					echo "<ul>\n";
					foreach($listbox['tct-menulist-linkitems'] as $link){
						$before = "";
						$after = "";
						if(isset($link['tct-menulist-link-url']) && trim($link['tct-menulist-link-url']) != ""){
							$burl = strtr($link['tct-menulist-link-url'],$btrans);
							if(preg_match('/post:/i',$burl)){ $url = "/?p=".str_replace('post:','',str_replace(' ','',$burl)); }else{ $url = $burl; }
							$before = "<a href=\"".$url."\" ";
							if(isset($link['tct-menulist-link-target']) && trim($link['tct-menulist-link-target']) != ""){
								$before .= "target=\"_blank\">";
							}else{
								$before .= ">";
							}
							$after .= "</a>";
						}
						echo "<li>".$before.$link['tct-menulist-link-title'].$after."</li>\n";
					}
					echo "</ul>\n";
				echo "</div>\n"; // .tct-menulist-content 
			}
		
		
		echo "</div>\n"; // .soua-accordion
	}
	echo "</div>\n"; // .tct-menulist
}

?>