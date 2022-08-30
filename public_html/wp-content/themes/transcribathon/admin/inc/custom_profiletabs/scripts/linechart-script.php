<?php include(str_repeat("../",(sizeof(explode("/",substr((string)getcwd(),strrpos((string)getcwd(),"/wp-content"),strlen((string)getcwd()))))-2))."../wp-load.php");
global $wpdb,$mysql_con;
$theme_sets = get_theme_mods();
// $theme_link_color = "".$theme_sets['vantage_general_link_color'];
$theme_link_color = "#b33e3e";

if(isset($_POST['q']) && $_POST['q'] === "get-ln-chart"):
			$content = "";
			$hoursallowed = 0;
			if(is_user_logged_in()){	
				if(isset($_POST['uid']) && $_POST['uid'] == get_current_user_id()){
					$hoursallowed ++;
				}else{
					um_fetch_user(get_current_user_id());
					$role = um_user('role');
					if($role === 'administrator' || $role === 'mentor'){
						$hoursallowed ++;
					}
				}
			}

			if(!isset($_POST['kind']) || trim($_POST['kind']) == ""){
				$c_kind = 'days';
				$c_min = date('Y-m').'-01';
				$c_max = date('Y-m-t');
			}else{
				$c_kind = $_POST['kind'];
				$c_min = $_POST['start'];
				$c_max = $_POST['ende'];
			}

			
			// Steps ermitteln
			$c_steps = array();
			$db_min = "";
			$db_max = "";
			switch($c_kind){
				case 'years':
					for($i=0; $i<10; $i++){
						if(strtotime("+".$i." year", strtotime($c_min))<=strtotime($c_max)){
							if ($db_min == "") {
								$db_min = date("Y-m-d", strtotime("+".$i." year", strtotime($c_min)));
							}
							array_push($c_steps,date("Y", strtotime("+".$i." year", strtotime($c_min))));
							$d = new DateTime( date("Y-m-d", strtotime("+".$i." year", strtotime($c_min))));
							$db_max =  $d->format( 'Y-m-t' );
						}
					}
				break;
				case 'months':
					for($i=0; $i<12; $i++){
						if(strtotime("+".$i." month", strtotime($c_min))<=strtotime($c_max)){
							if ($db_min == "") {
								$db_min = date("Y-m-d", strtotime("+".$i." month", strtotime($c_min)));
							}
							array_push($c_steps,date("Ym", strtotime("+".$i." month", strtotime($c_min))));
							$d = new DateTime( date("Y-m-d", strtotime("+".$i." month", strtotime($c_min))));
							$db_max =  $d->format( 'Y-m-t' );
						}
					}
				break;
				case 'days':
					//$d = new DateTime( date("Y-m-d", strtotime($c_min)));
					//$max =  (int)$d->format( 't' );
					for($i=0; $i<60; $i++){
						if(strtotime("+".$i." day", strtotime($c_min))<=strtotime($c_max)){
							if ($db_min == "") {
								$db_min = date("Y-m-d", strtotime("+".$i." day", strtotime($c_min)));
							}
							array_push($c_steps,date("Ymd", strtotime("+".$i." day", strtotime($c_min))));
							$db_max =  date("Y-m-d", strtotime("+".$i." day", strtotime($c_min)));
						}
					}
				break;
				case 'hours':
					for($i=0; $i<25; $i++){
						if(strtotime("+".$i." hour", strtotime($c_min))<=strtotime($c_max)){
							if ($db_min == "") {
								$db_min = date("Y-m-d H:i:s", strtotime("+".$i." hour", strtotime($c_min)));
							}
							array_push($c_steps,date("YmdH", strtotime("+".$i." hour", strtotime($c_min))));
							$db_max =  date("Y-m-d  H:i:s", strtotime("+".$i." hour", strtotime($c_min)));
						}
					}
				break;
			}
			
			
			// Werte ermitteln
			//$query = "SELECT * FROM ".$wpdb->prefix."user_transcriptionprogress WHERE userid='".$_POST['uid']."' AND datum >= '".$db_min."' AND datum <= '".$db_max."' ORDER BY datum ASC";
			//$chars = $wpdb->get_results($query);
			//$content .=  $query;

			// Set request parameters
			$url = TP_API_HOST."/tp-api/scores?UserId=".$_POST['uid']."&ScoreType=Transcription";
			$requestType = "GET";

			// Execude http request
			include dirname(__FILE__)."/../../custom_scripts/send_api_request.php";

			// Display data
			$scores = json_decode($result, true);
			$hours = array();
			$days = array();
			$months = array();
			$years = array();
			$h = array();
			$d = array();
			$m = array();
            $y = array();
			foreach($scores as $score){
				$years[date('Y',strtotime($score['Timestamp']))] += $score['Amount'];
				$months[date('Ym',strtotime($score['Timestamp']))] += $score['Amount'];
				$days[date('Ymd',strtotime($score['Timestamp']))] += $score['Amount'];
				$hours[date('YmdH',strtotime($score['Timestamp']))] += $score['Amount'];
				
            }
			
			// highest ermitteln
			$highest = 0;
			for($i=0; $i<sizeof($c_steps); $i++){
				if (isset(${$c_kind}[$c_steps[$i]]) && (int)${$c_kind}[$c_steps[$i]] > $highest) {
					$highest=${$c_kind}[$c_steps[$i]]; 
				}
			}
			
			// Einteilungen ermitteln und hoehe ggf anpassen
			$sk = 10;
			$fk = 0;
			$stps = array(5,10);
			$k = 0;
			$f = 1;
			$stepsize = 5;
			$x=0;
			while($x<100):
				if(($highest/3) <= ($stps[$k]*$f)){
					break;
				}else{
					$stepsize = ($stps[$k]*$f);
				}
				if($k<1){ $k=1; }else{ $k=0; $f = ($f*10); }
				$x++;
			endwhile;
			
			$s_anzahl = (intval($highest/$stepsize)+1);
			
			$highest = ($s_anzahl*$stepsize);
			
			// Einstellungen fuer die Darstellung
			$xoffset1 = 55;
			$xoffset2 = 55;
			$yoffset1 = 45;
			if($c_kind != "days"){
				$yoffset2 = 75;
			}else{
				$yoffset2 = 75;
			}

			$charouterwidth = 1110;
			$charinnerwidth = ($charouterwidth-$xoffset1-$xoffset2);
			$charouterheight = 440;
			$charinnerheight = ($charouterheight-$yoffset1-$yoffset2);
			
			$einpro = $highest/100;
			$f = $charinnerheight/$highest;
			
			
		
			
			$content .= "<div id='".$theme_sets['vantage_general_link_color']."' class=\"tct_linechart_area\" style=\"min-height:".$charouterheight."px;\">\n";
				$content .= "<div class=\"tct_linechart_holder\">\n";
				
				
				
				switch($c_kind){
					
					case 'years':
						$xstep =  $charinnerwidth/(sizeof($c_steps)-1);	
						$content .= "<svg height=\"100%\" data=\"".$charinnerwidth."\" width=\"100%\" viewBox=\"0 0 ".$charouterwidth." ".$charouterheight."\">\n";
							
							$poly = "";
							$polyflaeche = "";
							$polydots = "";
							$vraster = "";
							$x=0;
							
							
							$polyflaeche .= $xoffset1.",".($charinnerheight-$bh+$yoffset1)." ";
							for($i=0; $i<sizeof($c_steps); $i++){
								if ($i>0) {
									$t=" "; }else{ $t = ""; 
								}
								if(isset(${$c_kind}[$c_steps[$i]]) && (int)${$c_kind}[$c_steps[$i]] > 0){ 
									$bh = (${$c_kind}[$c_steps[$i]]*$f);
								}else{
									$bh = 0;
								}
								$poly .= $t.($x+$xoffset1).",".((int)($charinnerheight-$bh+$yoffset1));
								$polyflaeche .= $t.($x+$xoffset1).",".($charinnerheight-$bh+$yoffset1);
								$vraster .= "<line x1=\"".($x+$xoffset1)."\" y1=\"".$yoffset1."\" x2=\"".($x+$xoffset1)."\" y2=\"".($charinnerheight+$yoffset1)."\" style=\"stroke:#ddd;stroke-width:1\" />";
								$text = date('Y',strtotime(substr($c_steps[$i],0,4)."-01-01"));
								$vraster .= "<text x=\"".($x+$xoffset1)."\" y=\"".(($charinnerheight+$yoffset1)+20)."\" style=\"fill:#999; font-size:11px\" text-anchor=\"middle\">".$text."</text>";
								//$polydots .= "<circle cx=\"".($x+$xoffset1)."\" cy=\"".($charinnerheight-$bh+$yoffset1)."\" title=\"moinsen\" class=\"but\" onclick=\"getTCTlineChart('wholeyear','".$c_steps[$i]."','');\" r=\"4\" stroke=\"".$theme_link_color."\" stroke-width=\"2\" fill=".$theme_link_color." fill-opacity=0.6 />";
								$polydots .= "<circle cx=\"".($x+$xoffset1)."\" cy=\"".($charinnerheight-$bh+$yoffset1)."\" title=\"moinsen\" r=\"4\" stroke=\"".$theme_link_color."\" stroke-width=\"2\" fill=".$theme_link_color." fill-opacity=0.6 />";
								$polydots .= "<g class=\"tooltip css\" transform=\"translate(".($x+$xoffset1).",".($charinnerheight-$bh+$yoffset1).")\"><rect x=\"-3em\" y=\"-30\" width=\"6em\" height=\"15px\"/><text y=\"-30\" dy=\"1em\" text-anchor=\"middle\" style=\"fill:#fff; font-size:11px\">".number_format_i18n((int)${$c_kind}[$c_steps[$i]])."</text></g>";
								$x += $xstep;
							}
						
							$polyflaeche = $polyflaeche." ".($xoffset1+$charinnerwidth).",".($charinnerheight+$yoffset1);
							// Horizontales raster
							$hraster = "";
							$hraster .= "<line x1=\"".$xoffset1."\" y1=\"".$yoffset1."\" x2=\"".($xoffset1+$charinnerwidth)."\" y2=\"".$yoffset1."\" style=\"stroke:#ddd;stroke-width:1\" />";
							$h=$s_anzahl;
							for($i=1; $i<=$s_anzahl; $i++){
								$hraster .= "<line x1=\"".$xoffset1."\" y1=\"".((($i*$stepsize)*$f)+$yoffset1)."\" x2=\"".($xoffset1+$charinnerwidth)."\" y2=\"".((($i*$stepsize)*$f)+$yoffset1)."\" style=\"stroke:#ddd;stroke-width:1\" />";
								$hraster .= "<text x=\"".($xoffset1-4)."\" y=\"".(((($i-1)*$stepsize)*$f)+5+$yoffset1)."\" style=\"fill:#999; font-size:11px\" text-anchor=\"end\">".($h*$stepsize)."</text>";
								$h--;
							}	
							
							// Ausgabe
							$content .= $hraster."\n";
							$content .= $vraster."\n";
							$content .= " <polygon points=\"".$polyflaeche."\" style=\"fill:".$theme_link_color.";opacity:0.2;stroke:#daccd9;stroke-width:1\" />\n";
							$content .= " <polyline points=\"".$poly."\" style=\"fill:none;stroke:".$theme_link_color.";stroke-width:;stroke-opacity:0.6\" />\n";
							$content .= $polydots;
						$content .= "</svg>\n"; 
					break;	
					
					case 'months':
						$xstep =  $charinnerwidth/(sizeof($c_steps)-1);	
						$content .= "<svg height=\"100%\" data=\"".$charinnerwidth."\" width=\"100%\" viewBox=\"0 0 ".$charouterwidth." ".$charouterheight."\">\n";
							
							$poly = "";
							$polyflaeche = "";
							$polydots = "";
							$vraster = "";
							$x=0;
							
							$content .= "<text x=\"".($x+$xoffset1)."\" y=\"".($yoffset1 -12)."\" style=\"fill:#444; font-weight:700; font-size:13px; \" text-anchor=\"start\" letter-spacing=\"1\">"._x('Transcribed characters in','used in line-chart','transcribathon')."  ".date('Y',strtotime($c_min))." </text>";
							$polyflaeche .= $xoffset1.",".($charinnerheight-$bh+$yoffset1)." ";
							for($i=0; $i<sizeof($c_steps); $i++){
								if($i>0){ $t=" "; }else{ $t = ""; }
								if(isset(${$c_kind}[$c_steps[$i]]) && (int)${$c_kind}[$c_steps[$i]] > 0){ 
									$bh = (${$c_kind}[$c_steps[$i]]*$f);
								}else{
									$bh = 0;
								}
								$poly .= $t.($x+$xoffset1).",".((int)($charinnerheight-$bh+$yoffset1));
								$polyflaeche .= $t.($x+$xoffset1).",".($charinnerheight-$bh+$yoffset1);
								$vraster .= "<line x1=\"".($x+$xoffset1)."\" y1=\"".$yoffset1."\" x2=\"".($x+$xoffset1)."\" y2=\"".($charinnerheight+$yoffset1)."\" style=\"stroke:#ddd;stroke-width:1\" />";
								$text = date('M Y',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-01"));
								$vraster .= "<text x=\"".($x+$xoffset1)."\" y=\"".(($charinnerheight+$yoffset1)+20)."\" style=\"fill:#999; font-size:11px\" text-anchor=\"middle\">".$text."</text>";
								//$vraster .= "<text x=\"".($x+$xoffset1)."\" y=\"".(($charinnerheight+$yoffset1)+32)."\" style=\"fill:#999; font-size:11px\" text-anchor=\"middle\">".(int)${$c_kind}[$c_steps[$i]]."</text>";
								if(date('Ym',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-01"))<= date('Ym')){
									$polydots .= "<circle cx=\"".($x+$xoffset1)."\" cy=\"".($charinnerheight-$bh+$yoffset1)."\" r=\"4\" stroke=\"".$theme_link_color."\" class=\"but\" onclick=\"getTCTlinePersonalChart('days','".date('Y-m-d',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-01"))."','".date('Y-m-t',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-01"))."','".$_POST['holder']."','".$_POST['uid']."');\" stroke-width=\"2\" fill=".$theme_link_color." fill-opacity=0.6 />";
									$polydots .= "<g class=\"tooltip css\" transform=\"translate(".($x+$xoffset1).",".($charinnerheight-$bh+$yoffset1).")\"><rect x=\"-3em\" y=\"-30\" width=\"6em\" height=\"15px\"/><text y=\"-30\" dy=\"1em\" text-anchor=\"middle\" style=\"fill:#fff; font-size:11px\">".number_format_i18n((int)${$c_kind}[$c_steps[$i]])."</text></g>";
									$lastx = ($x+$xoffset1);
								}
								$vraster .= "<text x=\"".($xoffset1-10)."\" y=\"".(($charinnerheight+$yoffset1)+50)."\" class=\"textbut\" onclick=\"getTCTlinePersonalChart('months','".date('Y-m-d',strtotime("- 1 year",strtotime($c_min)))."','".date('Y-m-t',strtotime("- 1 year",strtotime($c_max)))."','".$_POST['holder']."','".$_POST['uid']."');\" style=\"fill:".$theme_link_color."; font-size:11px;\" text-anchor=\"start\">« "._x('show year before','used in line-chart','transcribathon')."</text>";
								$vraster .= "<text x=\"".($xoffset1+$charinnerwidth+10)."\" y=\"".(($charinnerheight+$yoffset1)+50)."\" class=\"textbut\" onclick=\"getTCTlinePersonalChart('months','".date('Y-m-d',strtotime("+ 1 year",strtotime($c_min)))."','".date('Y-m-t',strtotime("+ 1 year",strtotime($c_max)))."','".$_POST['holder']."','".$_POST['uid']."');\" style=\"fill:".$theme_link_color."; font-size:11px;\" text-anchor=\"end\">"._x('show next year','used in line-chart','transcribathon')." »</text>";
								$x += $xstep;
							}
						
							$polyflaeche = $polyflaeche." ".($lastx).",".($charinnerheight+$yoffset1);
							// Horizontales raster
							$hraster = "";
							$hraster .= "<line x1=\"".$xoffset1."\" y1=\"".$yoffset1."\" x2=\"".($xoffset1+$charinnerwidth)."\" y2=\"".$yoffset1."\" style=\"stroke:#ddd;stroke-width:1\" />";
							$h=$s_anzahl;
							for($i=1; $i<=$s_anzahl; $i++){
								$hraster .= "<line x1=\"".$xoffset1."\" y1=\"".((($i*$stepsize)*$f)+$yoffset1)."\" x2=\"".($xoffset1+$charinnerwidth)."\" y2=\"".((($i*$stepsize)*$f)+$yoffset1)."\" style=\"stroke:#ddd;stroke-width:1\" />";
								$hraster .= "<text x=\"".($xoffset1-4)."\" y=\"".(((($i-1)*$stepsize)*$f)+5+$yoffset1)."\" style=\"fill:#999; font-size:11px\" text-anchor=\"end\">".($h*$stepsize)."</text>";
								$h--;
							}	
							
							// Ausgabe
							$content .= $hraster."\n";
							$content .= $vraster."\n";
							$content .= " <polygon points=\"".$polyflaeche."\" style=\"fill:".$theme_link_color.";opacity:0.2;stroke:#daccd9;stroke-width:1\" />\n";
							$content .= " <polyline points=\"".$poly."\" style=\"fill:none;stroke:".$theme_link_color.";stroke-width:;stroke-opacity:0.6\" />\n";
							$content .= $polydots;
						$content .= "</svg>\n"; 
					break;
						
					case 'days':
						$xstep =  $charinnerwidth/(sizeof($c_steps)-1);	
						$content .= "<svg height=\"100%\" data=\"".$charinnerwidth."\" width=\"100%\" viewBox=\"0 0 ".$charouterwidth." ".$charouterheight."\">\n";
							
							$poly = "";
							$polyflaeche = "";
							$polydots = "";
							$vraster = "";
							$x=0;
							
							$content .= "<text x=\"".($x+$xoffset1)."\" y=\"".($yoffset1 -12)."\" style=\"fill:#444; font-weight:700; font-size:13px; \" text-anchor=\"start\" letter-spacing=\"1\">"._x('Transcribed characters','used in line-chart','transcribathon')."  ".date_i18n(get_option('date_format'),strtotime($c_min))." &ndash; ".date_i18n(get_option('date_format'),strtotime($c_max))."</text>";
							$polyflaeche .= $xoffset1.",".($charinnerheight-$bh+$yoffset1)." ";
							$lastx = 0;
							for($i=0; $i<sizeof($c_steps); $i++){
								if($i>0){ $t=" "; }else{ $t = ""; }
								if(isset(${$c_kind}[$c_steps[$i]]) && (int)${$c_kind}[$c_steps[$i]] > 0){ 
									$bh = (${$c_kind}[$c_steps[$i]]*$f);
								}else{
									$bh = 0;
								}
								if(date('Ymd',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-".substr($c_steps[$i],6,2)))<= date('Ymd')){
									$poly .= $t.($x+$xoffset1).",".((int)($charinnerheight-$bh+$yoffset1));
									$polyflaeche .= $t.($x+$xoffset1).",".($charinnerheight-$bh+$yoffset1);
								}
								$vraster .= "<line x1=\"".($x+$xoffset1)."\" y1=\"".$yoffset1."\" x2=\"".($x+$xoffset1)."\" y2=\"".($charinnerheight+$yoffset1)."\" style=\"stroke:#ddd;stroke-width:1\" />";
								$text1 = date('j',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-".substr($c_steps[$i],6,2)));
								$text2 = date('D',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-".substr($c_steps[$i],6,2)));
								$text3 = date('M',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-".substr($c_steps[$i],6,2)));
								$mydate = substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-".substr($c_steps[$i],6,2);
								$vraster .= "<text x=\"".($x+$xoffset1)."\" y=\"".(($charinnerheight+$yoffset1)+20)."\" style=\"fill:#999; font-size:5px !important; \" text-anchor=\"middle\">".$text2."</text>";
								$vraster .= "<text x=\"".($x+$xoffset1)."\" y=\"".(($charinnerheight+$yoffset1)+32)."\" style=\"fill:#777; font-size:11px;\" text-anchor=\"middle\">".$text1."</text>";
								//$vraster .= "<text x=\"".($x+$xoffset1)."\" y=\"".(($charinnerheight+$yoffset1)+45)."\" style=\"fill:#999; font-size:5px !important; \" text-anchor=\"middle\">".$text3."</text>";
								if(date('Ymd',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-".substr($c_steps[$i],6,2)))<= date('Ymd')){
									if($hoursallowed > 0){
										$polydots .= "<circle cx=\"".($x+$xoffset1)."\" cy=\"".($charinnerheight-$bh+$yoffset1)."\" r=\"4\" stroke=\"".$theme_link_color."\" class=\"but\" onclick=\"getTCTlinePersonalChart('hours','".$mydate." 00:00:00','".date('Y-m-d',strtotime("+1 day",strtotime($mydate)))." 00:00:00','".$_POST['holder']."','".$_POST['uid']."');\" stroke-width=\"2\" fill=".$theme_link_color." fill-opacity=0.6 />";
									}else{
										$polydots .= "<circle cx=\"".($x+$xoffset1)."\" cy=\"".($charinnerheight-$bh+$yoffset1)."\" r=\"4\" stroke=\"".$theme_link_color."\" stroke-width=\"2\" fill=".$theme_link_color." fill-opacity=0.6 />";
									}
									$polydots .= "<g class=\"tooltip css\" transform=\"translate(".($x+$xoffset1).",".($charinnerheight-$bh+$yoffset1).")\"><rect x=\"-3em\" y=\"-30\" width=\"6em\" height=\"15px\"/><text y=\"-30\" dy=\"1em\" text-anchor=\"middle\" style=\"fill:#fff; font-size:11px;\">".number_format_i18n((int)${$c_kind}[$c_steps[$i]])."</text></g>";
									$lastx = ($x+$xoffset1);
								}
								$vraster .= "<text x=\"".($xoffset1-10)."\" y=\"".(($charinnerheight+$yoffset1)+50)."\" class=\"textbut\" onclick=\"getTCTlinePersonalChart('days','".date('Y-m-d',strtotime("- 1 month",strtotime($c_min)))."','".date('Y-m-t',strtotime("- 1 month",strtotime($c_min)))."','".$_POST['holder']."','".$_POST['uid']."');\" style=\"fill:".$theme_link_color."; font-size:11px;\" text-anchor=\"start\">« "._x('show month before','used in line-chart','transcribathon')."</text>";
								$vraster .= "<text x=\"".($xoffset1+($charinnerwidth/2))."\" y=\"".(($charinnerheight+$yoffset1)+50)."\" class=\"textbut\" onclick=\"getTCTlinePersonalChart('months','".date('Y')."-01-01','".date('Y-m-t',strtotime(date('Y').'-12-01'))."','".$_POST['holder']."','".$_POST['uid']."');\" style=\"fill:".$theme_link_color."; font-size:11px\" text-anchor=\"middle\">"._x('show whole year','used in line-chart','transcribathon')."</text>";
								$vraster .= "<text x=\"".($xoffset1+$charinnerwidth+10)."\" y=\"".(($charinnerheight+$yoffset1)+50)."\" class=\"textbut\" onclick=\"getTCTlinePersonalChart('days','".date('Y-m-d',strtotime("+ 1 month",strtotime($c_min)))."','".date('Y-m-t',strtotime("+ 1 month",strtotime($c_min)))."','".$_POST['holder']."','".$_POST['uid']."');\" style=\"fill:".$theme_link_color."; font-size:11px;\" text-anchor=\"end\">"._x('show next month','used in line-chart','transcribathon')." »</text>";
								$x += $xstep;
							}
						
							$polyflaeche = $polyflaeche." ".($lastx).",".($charinnerheight+$yoffset1);
							// Horizontales raster
							$hraster = "";
							$hraster .= "<line x1=\"".$xoffset1."\" y1=\"".$yoffset1."\" x2=\"".($xoffset1+$charinnerwidth)."\" y2=\"".$yoffset1."\" style=\"stroke:#ddd;stroke-width:1\" />";
							$h=$s_anzahl;
							for($i=1; $i<=$s_anzahl; $i++){
								$hraster .= "<line x1=\"".$xoffset1."\" y1=\"".((($i*$stepsize)*$f)+$yoffset1)."\" x2=\"".($xoffset1+$charinnerwidth)."\" y2=\"".((($i*$stepsize)*$f)+$yoffset1)."\" style=\"stroke:#ddd;stroke-width:1\" />";
								$hraster .= "<text x=\"".($xoffset1-4)."\" y=\"".(((($i-1)*$stepsize)*$f)+5+$yoffset1)."\" style=\"fill:#999; font-size:11px\" text-anchor=\"end\">".($h*$stepsize)."</text>";
								$h--;
							}	
							
							// Ausgabe
							$content .= $hraster."\n";
							$content .= $vraster."\n";
							$content .= " <polygon points=\"".$polyflaeche."\" style=\"fill:".$theme_link_color.";opacity:0.2;stroke:#daccd9;stroke-width:1\" />\n";
							$content .= " <polyline points=\"".$poly."\" style=\"fill:none;stroke:".$theme_link_color.";stroke-width:;stroke-opacity:0.6\" />\n";
							$content .= $polydots;
						$content .= "</svg>\n"; 
					break;	
						
					case 'hours':
						$xstep =  $charinnerwidth/(sizeof($c_steps)-1);	
						$content .= "<svg height=\"100%\" data=\"".$charinnerwidth."\" width=\"100%\" viewBox=\"0 0 ".$charouterwidth." ".$charouterheight."\">\n";
							
							$poly = "";
							$polyflaeche = "";
							$polydots = "";
							$vraster = "";
							$x=0;
							$lastx = 0;
							$content .= "<text x=\"".($x+$xoffset1)."\" y=\"".($yoffset1 -12)."\" style=\"fill:#444; font-weight:700; font-size:13px; \" text-anchor=\"start\" letter-spacing=\"1\">"._x('Transcribed characters on','used in line-chart','transcribathon')."  ".date_i18n(get_option('date_format'),strtotime($c_min))."</text>";
							$polyflaeche .= $xoffset1.",".($charinnerheight-$bh+$yoffset1)." ";
							for($i=0; $i<sizeof($c_steps); $i++){
								if($i>0){ $t=" "; }else{ $t = ""; }
								if(isset(${$c_kind}[$c_steps[$i]]) && (int)${$c_kind}[$c_steps[$i]] > 0){ 
									$bh = (${$c_kind}[$c_steps[$i]]*$f);
								}else{
									$bh = 0;
								}
								if(date('Ymd H:i:s',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-".substr($c_steps[$i],6,2)." ".substr($c_steps[$i],8,2).":00:00"))<= date('Ymd H:i:s')){
									$poly .= $t.($x+$xoffset1).",".((int)($charinnerheight-$bh+$yoffset1));
									$polyflaeche .= $t.($x+$xoffset1).",".($charinnerheight-$bh+$yoffset1);
								}
								$vraster .= "<line x1=\"".($x+$xoffset1)."\" y1=\"".$yoffset1."\" x2=\"".($x+$xoffset1)."\" y2=\"".($charinnerheight+$yoffset1)."\" style=\"stroke:#ddd;stroke-width:1\" />";
								$text = date('ga',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-".substr($c_steps[$i],6,2)." ".substr($c_steps[$i],8,2).":00:00"));
								$vraster .= "<text x=\"".($x+$xoffset1)."\" y=\"".(($charinnerheight+$yoffset1)+20)."\" style=\"fill:#999; font-size:11px\" text-anchor=\"middle\">".$text."</text>";
								//$vraster .= "<text x=\"".($x+$xoffset1)."\" y=\"".(($charinnerheight+$yoffset1)+35)."\" style=\"fill:#999; font-size:11px\" text-anchor=\"middle\">".(int)${$c_kind}[$c_steps[$i]]."</text>";
								if(date('Ymd H:i:s',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-".substr($c_steps[$i],6,2)." ".substr($c_steps[$i],8,2).":00:00"))<= date('Ymd H:i:s')){
									$polydots .= "<circle cx=\"".($x+$xoffset1)."\" cy=\"".($charinnerheight-$bh+$yoffset1)."\" r=\"4\" stroke=\"".$theme_link_color."\" stroke-width=\"2\" fill=".$theme_link_color." fill-opacity=0.6 />";
									$polydots .= "<g class=\"tooltip css\" transform=\"translate(".($x+$xoffset1).",".($charinnerheight-$bh+$yoffset1).")\"><rect x=\"-3em\" y=\"-30\" width=\"6em\" height=\"15px\"/><text y=\"-30\" dy=\"1em\" text-anchor=\"middle\" style=\"fill:#fff; font-size:11px\">".number_format_i18n((int)${$c_kind}[$c_steps[$i]])."</text></g>";
									$lastx = ($x+$xoffset1);
								}
								$x += $xstep;
							}
							$vraster .= "<text x=\"".($xoffset1-10)."\" y=\"".(($charinnerheight+$yoffset1)+40)."\" class=\"textbut\" onclick=\"getTCTlinePersonalChart('hours','".date('Y-m-d H:i:s',strtotime("- 1 day",strtotime($c_min)))."','".date('Y-m-d H:i:s',strtotime("- 1 day",strtotime($c_max)))."','".$_POST['holder']."','".$_POST['uid']."');\" style=\"fill:".$theme_link_color."; font-size:11px\" text-anchor=\"start\">« "._x('show day before','used in line-chart','transcribathon')."</text>";
							$vraster .= "<text x=\"".($xoffset1+($charinnerwidth/2))."\" y=\"".(($charinnerheight+$yoffset1)+40)."\" class=\"textbut\" onclick=\"getTCTlinePersonalChart('days','".date('Y-m',strtotime($c_min))."-01','".date('Y-m-t',strtotime($c_min))."','".$_POST['holder']."','".$_POST['uid']."');\" style=\"fill:".$theme_link_color."; font-size:11px\" text-anchor=\"middle\">"._x('show whole month','used in line-chart','transcribathon')."</text>";
							$vraster .= "<text x=\"".($xoffset1+$charinnerwidth+10)."\" y=\"".(($charinnerheight+$yoffset1)+40)."\" class=\"textbut\" onclick=\"getTCTlinePersonalChart('hours','".date('Y-m-d H:i:s',strtotime("+ 1 day",strtotime($c_min)))."','".date('Y-m-d H:i:s',strtotime("+ 1 day",strtotime($c_max)))."','".$_POST['holder']."','".$_POST['uid']."');\" style=\"fill:".$theme_link_color."; font-size:11px\" text-anchor=\"end\">"._x('show next day','used in line-chart','transcribathon')." »</text>";
								
							$polyflaeche = $polyflaeche." ".$lastx.",".($charinnerheight+$yoffset1);
							// Horizontales raster
							$hraster = "";
							$hraster .= "<line x1=\"".$xoffset1."\" y1=\"".$yoffset1."\" x2=\"".($xoffset1+$charinnerwidth)."\" y2=\"".$yoffset1."\" style=\"stroke:#ddd;stroke-width:1\" />";
							$h=$s_anzahl;
							for($i=1; $i<=$s_anzahl; $i++){
								$hraster .= "<line x1=\"".$xoffset1."\" y1=\"".((($i*$stepsize)*$f)+$yoffset1)."\" x2=\"".($xoffset1+$charinnerwidth)."\" y2=\"".((($i*$stepsize)*$f)+$yoffset1)."\" style=\"stroke:#ddd;stroke-width:1\" />";
								$hraster .= "<text x=\"".($xoffset1-4)."\" y=\"".(((($i-1)*$stepsize)*$f)+5+$yoffset1)."\" style=\"fill:#999; font-size:11px\" text-anchor=\"end\">".($h*$stepsize)."</text>";
								$h--;
							}	
							
							// Ausgabe
							$content .= $hraster."\n";
							$content .= $vraster."\n";
							$content .= " <polygon points=\"".$polyflaeche."\" style=\"fill:".$theme_link_color.";opacity:0.2;stroke:#daccd9;stroke-width:1\" />\n";
							$content .= " <polyline points=\"".$poly."\" style=\"fill:none;stroke:".$theme_link_color.";stroke-width:;stroke-opacity:0.6\" />\n";
							$content .= $polydots;
						$content .= "</svg>\n"; 
					break;	
						// rgba(127,57,120,0.3) zart: #c5a6c2 , voll: ".$theme_link_color."
				}
				$content .= "</div>\n"; // .tct_linechart_holder
			$content .= "</div>\n"; // .tct_linechart_area
			$content .= "<div class=\"tct_linechart_operator\">\n";
			$content .= "</div>\n"; // .tct_linechart_operator
			
	//Rueckgabe 
	$res = array();
	$res['status'] = 'ok';
	$res['content'] = $content;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;	


// TEAM

if(isset($_POST['q']) && $_POST['q'] === "get-ln-team-chart"):
			//{'q':'get-ln-chart','kind':what,'start':start,'ende':ende}
			$content = "";
			$hoursallowed = 1;
		
			if(!isset($_POST['kind']) || trim($_POST['kind']) == ""){
				$c_kind = 'days';
				$c_min = date('Y-m').'-01';
				$c_max = date('Y-m-t');
			}else{
				$c_kind = $_POST['kind'];
				$c_min = $_POST['start'];
				$c_max = $_POST['ende'];
			}

			
			// Steps ermitteln
			$c_steps = array();
			$db_min = "";
			$db_max = "";
			switch($c_kind){
				case 'years':
					for($i=0; $i<10; $i++){
						if(strtotime("+".$i." year", strtotime($c_min))<=strtotime($c_max)){
							if($db_min == ""){$db_min = date("Y-m-d", strtotime("+".$i." year", strtotime($c_min)));}
							array_push($c_steps,date("Y", strtotime("+".$i." year", strtotime($c_min))));
							$d = new DateTime( date("Y-m-d", strtotime("+".$i." year", strtotime($c_min))));
							$db_max =  $d->format( 'Y-m-t' );
						}
					}
				break;
				case 'months':
					for($i=0; $i<12; $i++){
						if(strtotime("+".$i." month", strtotime($c_min))<=strtotime($c_max)){
							if($db_min == ""){$db_min = date("Y-m-d", strtotime("+".$i." month", strtotime($c_min)));}
							array_push($c_steps,date("Ym", strtotime("+".$i." month", strtotime($c_min))));
							$d = new DateTime( date("Y-m-d", strtotime("+".$i." month", strtotime($c_min))));
							$db_max =  $d->format( 'Y-m-t' );
						}
					}
				break;
				case 'days':
					//$d = new DateTime( date("Y-m-d", strtotime($c_min)));
					//$max =  (int)$d->format( 't' );
					for($i=0; $i<60; $i++){
						if(strtotime("+".$i." day", strtotime($c_min))<=strtotime($c_max)){
							if($db_min == ""){$db_min = date("Y-m-d", strtotime("+".$i." day", strtotime($c_min)));}
							array_push($c_steps,date("Ymd", strtotime("+".$i." day", strtotime($c_min))));
							$db_max =  date("Y-m-d", strtotime("+".$i." day", strtotime($c_min)));
						}
					}
				break;
				case 'hours':
					for($i=0; $i<25; $i++){
						if(strtotime("+".$i." hour", strtotime($c_min))<=strtotime($c_max)){
							if($db_min == ""){$db_min = date("Y-m-d H:i:s", strtotime("+".$i." hour", strtotime($c_min)));}
							array_push($c_steps,date("YmdH", strtotime("+".$i." hour", strtotime($c_min))));
							$db_max =  date("Y-m-d  H:i:s", strtotime("+".$i." hour", strtotime($c_min)));
						}
					}
				break;
			}
			
			
			// Werte ermitteln
			//$query = "SELECT * FROM ".$wpdb->prefix."team_transcriptionprogress WHERE teamid='".$_POST['tid']."' AND datum >= '".$db_min."' AND datum <= '".$db_max."' ORDER BY datum ASC";
			//$chars = $wpdb->get_results($query);
			//$content .=  $query;
			$hours = array();
			$days = array();
			$months = array();
			$years = array();
			$h = array();
			$d = array();
			$m = array();
            $y = array();
            /*
			foreach($chars as $char){
				$years[date('Y',strtotime($char->datum))] += (int)$char->amount;
				$months[date('Ym',strtotime($char->datum))] += (int)$char->amount;
				$days[date('Ymd',strtotime($char->datum))] += (int)$char->amount;
				$hours[date('YmdH',strtotime($char->datum))] += (int)$char->amount;
				
            }
            */
			
			// highest ermitteln
			$highest = 0;
			for($i=0; $i<sizeof($c_steps); $i++){
				if(isset(${$c_kind}[$c_steps[$i]]) && (int)${$c_kind}[$c_steps[$i]] > $highest){ $highest=${$c_kind}[$c_steps[$i]]; }
			}
			
			// Einteilungen ermitteln und hoehe ggf anpassen
			$sk = 10;
			$fk = 0;
			$stps = array(5,10);
			$k = 0;
			$f = 1;
			$stepsize = 5;
			$x=0;
			while($x<100):
				if(($highest/3) <= ($stps[$k]*$f)){
					break;
				}else{
					$stepsize = ($stps[$k]*$f);
				}
				if($k<1){ $k=1; }else{ $k=0; $f = ($f*10); }
				$x++;
			endwhile;
			
			$s_anzahl = (intval($highest/$stepsize)+1);
			
			$highest = ($s_anzahl*$stepsize);
			
			// Einstellungen fuer die Darstellung
			$xoffset1 = 55;
			$xoffset2 = 55;
			$yoffset1 = 45;
			if($c_kind != "days"){
				$yoffset2 = 75;
			}else{
				$yoffset2 = 75;
			}

			$charouterwidth = 1110;
			$charinnerwidth = ($charouterwidth-$xoffset1-$xoffset2);
			$charouterheight = 440;
			$charinnerheight = ($charouterheight-$yoffset1-$yoffset2);
			
			$einpro = $highest/100;
			$f = $charinnerheight/$highest;
			
			
		
			
			$content .= "<div class=\"tct_linechart_area\" style=\"min-height:".$charouterheight."px;\">\n";
				$content .= "<div class=\"tct_linechart_holder\">\n";
				
				
				
				switch($c_kind){
					
					case 'years':
						$xstep =  $charinnerwidth/(sizeof($c_steps)-1);	
						$content .= "<svg height=\"100%\" data=\"".$charinnerwidth."\" width=\"100%\" viewBox=\"0 0 ".$charouterwidth." ".$charouterheight."\">\n";
							
							$poly = "";
							$polyflaeche = "";
							$polydots = "";
							$vraster = "";
							$x=0;
							
							
							$polyflaeche .= $xoffset1.",".($charinnerheight-$bh+$yoffset1)." ";
							for($i=0; $i<sizeof($c_steps); $i++){
								if($i>0){ $t=" "; }else{ $t = ""; }
								if(isset(${$c_kind}[$c_steps[$i]]) && (int)${$c_kind}[$c_steps[$i]] > 0){ 
									$bh = (${$c_kind}[$c_steps[$i]]*$f);
								}else{
									$bh = 0;
								}
								$poly .= $t.($x+$xoffset1).",".((int)($charinnerheight-$bh+$yoffset1));
								$polyflaeche .= $t.($x+$xoffset1).",".($charinnerheight-$bh+$yoffset1);
								$vraster .= "<line x1=\"".($x+$xoffset1)."\" y1=\"".$yoffset1."\" x2=\"".($x+$xoffset1)."\" y2=\"".($charinnerheight+$yoffset1)."\" style=\"stroke:#ddd;stroke-width:1\" />";
								$text = date('Y',strtotime(substr($c_steps[$i],0,4)."-01-01"));
								$vraster .= "<text x=\"".($x+$xoffset1)."\" y=\"".(($charinnerheight+$yoffset1)+20)."\" style=\"fill:#999; font-size:11px\" text-anchor=\"middle\">".$text."</text>";
								//$polydots .= "<circle cx=\"".($x+$xoffset1)."\" cy=\"".($charinnerheight-$bh+$yoffset1)."\" title=\"moinsen\" class=\"but\" onclick=\"getTCTlineChart('wholeyear','".$c_steps[$i]."','');\" r=\"4\" stroke=\"".$theme_link_color."\" stroke-width=\"2\" fill=".$theme_link_color." fill-opacity=0.6 />";
								$polydots .= "<circle cx=\"".($x+$xoffset1)."\" cy=\"".($charinnerheight-$bh+$yoffset1)."\" title=\"moinsen\" r=\"4\" stroke=\"".$theme_link_color."\" stroke-width=\"2\" fill=".$theme_link_color." fill-opacity=0.6 />";
								$polydots .= "<g class=\"tooltip css\" transform=\"translate(".($x+$xoffset1).",".($charinnerheight-$bh+$yoffset1).")\"><rect x=\"-3em\" y=\"-30\" width=\"6em\" height=\"15px\"/><text y=\"-30\" dy=\"1em\" text-anchor=\"middle\" style=\"fill:#fff; font-size:11px\">".number_format_i18n((int)${$c_kind}[$c_steps[$i]])."</text></g>";
								$x += $xstep;
							}
						
							$polyflaeche = $polyflaeche." ".($xoffset1+$charinnerwidth).",".($charinnerheight+$yoffset1);
							// Horizontales raster
							$hraster = "";
							$hraster .= "<line x1=\"".$xoffset1."\" y1=\"".$yoffset1."\" x2=\"".($xoffset1+$charinnerwidth)."\" y2=\"".$yoffset1."\" style=\"stroke:#ddd;stroke-width:1\" />";
							$h=$s_anzahl;
							for($i=1; $i<=$s_anzahl; $i++){
								$hraster .= "<line x1=\"".$xoffset1."\" y1=\"".((($i*$stepsize)*$f)+$yoffset1)."\" x2=\"".($xoffset1+$charinnerwidth)."\" y2=\"".((($i*$stepsize)*$f)+$yoffset1)."\" style=\"stroke:#ddd;stroke-width:1\" />";
								$hraster .= "<text x=\"".($xoffset1-4)."\" y=\"".(((($i-1)*$stepsize)*$f)+5+$yoffset1)."\" style=\"fill:#999; font-size:11px\" text-anchor=\"end\">".($h*$stepsize)."</text>";
								$h--;
							}	
							
							// Ausgabe
							$content .= $hraster."\n";
							$content .= $vraster."\n";
							$content .= " <polygon points=\"".$polyflaeche."\" style=\"fill:".$theme_link_color.";opacity:0.2;stroke:#daccd9;stroke-width:1\" />\n";
							$content .= " <polyline points=\"".$poly."\" style=\"fill:none;stroke:".$theme_link_color.";stroke-width:;stroke-opacity:0.6\" />\n";
							$content .= $polydots;
						$content .= "</svg>\n"; 
					break;	
					
					case 'months':
						$xstep =  $charinnerwidth/(sizeof($c_steps)-1);	
						$content .= "<svg height=\"100%\" data=\"".$charinnerwidth."\" width=\"100%\" viewBox=\"0 0 ".$charouterwidth." ".$charouterheight."\">\n";
							
							$poly = "";
							$polyflaeche = "";
							$polydots = "";
							$vraster = "";
							$x=0;
							
							$content .= "<text x=\"".($x+$xoffset1)."\" y=\"".($yoffset1 -12)."\" style=\"fill:#444; font-weight:700; font-size:13px; \" text-anchor=\"start\" letter-spacing=\"1\">"._x('Transcribed characters in','used in line-chart','transcribathon')."  ".date('Y',strtotime($c_min))." </text>";
							$polyflaeche .= $xoffset1.",".($charinnerheight-$bh+$yoffset1)." ";
							for($i=0; $i<sizeof($c_steps); $i++){
								if($i>0){ $t=" "; }else{ $t = ""; }
								if(isset(${$c_kind}[$c_steps[$i]]) && (int)${$c_kind}[$c_steps[$i]] > 0){ 
									$bh = (${$c_kind}[$c_steps[$i]]*$f);
								}else{
									$bh = 0;
								}
								$poly .= $t.($x+$xoffset1).",".((int)($charinnerheight-$bh+$yoffset1));
								$polyflaeche .= $t.($x+$xoffset1).",".($charinnerheight-$bh+$yoffset1);
								$vraster .= "<line x1=\"".($x+$xoffset1)."\" y1=\"".$yoffset1."\" x2=\"".($x+$xoffset1)."\" y2=\"".($charinnerheight+$yoffset1)."\" style=\"stroke:#ddd;stroke-width:1\" />";
								$text = date('M Y',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-01"));
								$vraster .= "<text x=\"".($x+$xoffset1)."\" y=\"".(($charinnerheight+$yoffset1)+20)."\" style=\"fill:#999; font-size:11px\" text-anchor=\"middle\">".$text."</text>";
								//$vraster .= "<text x=\"".($x+$xoffset1)."\" y=\"".(($charinnerheight+$yoffset1)+32)."\" style=\"fill:#999; font-size:11px\" text-anchor=\"middle\">".(int)${$c_kind}[$c_steps[$i]]."</text>";
								if(date('Ym',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-01"))<= date('Ym')){
									$polydots .= "<circle cx=\"".($x+$xoffset1)."\" cy=\"".($charinnerheight-$bh+$yoffset1)."\" r=\"4\" stroke=\"".$theme_link_color."\" class=\"but\" onclick=\"getTCTlineTeamChart('days','".date('Y-m-d',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-01"))."','".date('Y-m-t',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-01"))."','".$_POST['holder']."','".$_POST['tid']."');\" stroke-width=\"2\" fill=".$theme_link_color." fill-opacity=0.6 />";
									$polydots .= "<g class=\"tooltip css\" transform=\"translate(".($x+$xoffset1).",".($charinnerheight-$bh+$yoffset1).")\"><rect x=\"-3em\" y=\"-30\" width=\"6em\" height=\"15px\"/><text y=\"-30\" dy=\"1em\" text-anchor=\"middle\" style=\"fill:#fff; font-size:11px\">".number_format_i18n((int)${$c_kind}[$c_steps[$i]])."</text></g>";
									$lastx = ($x+$xoffset1);
								}
								$vraster .= "<text x=\"".($xoffset1-10)."\" y=\"".(($charinnerheight+$yoffset1)+50)."\" class=\"textbut\" onclick=\"getTCTlineTeamChart('months','".date('Y-m-d',strtotime("- 1 year",strtotime($c_min)))."','".date('Y-m-t',strtotime("- 1 year",strtotime($c_max)))."','".$_POST['holder']."','".$_POST['tid']."');\" style=\"fill:".$theme_link_color."; font-size:11px;\" text-anchor=\"start\">« "._x('show year before','used in line-chart','transcribathon')."</text>";
								$vraster .= "<text x=\"".($xoffset1+$charinnerwidth+10)."\" y=\"".(($charinnerheight+$yoffset1)+50)."\" class=\"textbut\" onclick=\"getTCTlineTeamChart('months','".date('Y-m-d',strtotime("+ 1 year",strtotime($c_min)))."','".date('Y-m-t',strtotime("+ 1 year",strtotime($c_max)))."','".$_POST['holder']."','".$_POST['tid']."');\" style=\"fill:".$theme_link_color."; font-size:11px;\" text-anchor=\"end\">"._x('show next year','used in line-chart','transcribathon')." »</text>";
								$x += $xstep;
							}
						
							$polyflaeche = $polyflaeche." ".($lastx).",".($charinnerheight+$yoffset1);
							// Horizontales raster
							$hraster = "";
							$hraster .= "<line x1=\"".$xoffset1."\" y1=\"".$yoffset1."\" x2=\"".($xoffset1+$charinnerwidth)."\" y2=\"".$yoffset1."\" style=\"stroke:#ddd;stroke-width:1\" />";
							$h=$s_anzahl;
							for($i=1; $i<=$s_anzahl; $i++){
								$hraster .= "<line x1=\"".$xoffset1."\" y1=\"".((($i*$stepsize)*$f)+$yoffset1)."\" x2=\"".($xoffset1+$charinnerwidth)."\" y2=\"".((($i*$stepsize)*$f)+$yoffset1)."\" style=\"stroke:#ddd;stroke-width:1\" />";
								$hraster .= "<text x=\"".($xoffset1-4)."\" y=\"".(((($i-1)*$stepsize)*$f)+5+$yoffset1)."\" style=\"fill:#999; font-size:11px\" text-anchor=\"end\">".($h*$stepsize)."</text>";
								$h--;
							}	
							
							// Ausgabe
							$content .= $hraster."\n";
							$content .= $vraster."\n";
							$content .= " <polygon points=\"".$polyflaeche."\" style=\"fill:".$theme_link_color.";opacity:0.2;stroke:#daccd9;stroke-width:1\" />\n";
							$content .= " <polyline points=\"".$poly."\" style=\"fill:none;stroke:".$theme_link_color.";stroke-width:;stroke-opacity:0.6\" />\n";
							$content .= $polydots;
						$content .= "</svg>\n"; 
					break;
						
					case 'days':
						$xstep =  $charinnerwidth/(sizeof($c_steps)-1);	
						$content .= "<svg height=\"100%\" data=\"".$charinnerwidth."\" width=\"100%\" viewBox=\"0 0 ".$charouterwidth." ".$charouterheight."\">\n";
							
							$poly = "";
							$polyflaeche = "";
							$polydots = "";
							$vraster = "";
							$x=0;
							
							$content .= "<text x=\"".($x+$xoffset1)."\" y=\"".($yoffset1 -12)."\" style=\"fill:#444; font-weight:700; font-size:13px; \" text-anchor=\"start\" letter-spacing=\"1\">"._x('Transcribed characters','used in line-chart','transcribathon')."  ".date_i18n(get_option('date_format'),strtotime($c_min))." &ndash; ".date_i18n(get_option('date_format'),strtotime($c_max))."</text>";
							$polyflaeche .= $xoffset1.",".($charinnerheight-$bh+$yoffset1)." ";
							$lastx = 0;
							for($i=0; $i<sizeof($c_steps); $i++){
								if($i>0){ $t=" "; }else{ $t = ""; }
								if(isset(${$c_kind}[$c_steps[$i]]) && (int)${$c_kind}[$c_steps[$i]] > 0){ 
									$bh = (${$c_kind}[$c_steps[$i]]*$f);
								}else{
									$bh = 0;
								}
								if(date('Ymd',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-".substr($c_steps[$i],6,2)))<= date('Ymd')){
									$poly .= $t.($x+$xoffset1).",".((int)($charinnerheight-$bh+$yoffset1));
									$polyflaeche .= $t.($x+$xoffset1).",".($charinnerheight-$bh+$yoffset1);
								}
								$vraster .= "<line x1=\"".($x+$xoffset1)."\" y1=\"".$yoffset1."\" x2=\"".($x+$xoffset1)."\" y2=\"".($charinnerheight+$yoffset1)."\" style=\"stroke:#ddd;stroke-width:1\" />";
								$text1 = date('j',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-".substr($c_steps[$i],6,2)));
								$text2 = date('D',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-".substr($c_steps[$i],6,2)));
								$text3 = date('M',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-".substr($c_steps[$i],6,2)));
								$mydate = substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-".substr($c_steps[$i],6,2);
								$vraster .= "<text x=\"".($x+$xoffset1)."\" y=\"".(($charinnerheight+$yoffset1)+20)."\" style=\"fill:#999; font-size:5px !important; \" text-anchor=\"middle\">".$text2."</text>";
								$vraster .= "<text x=\"".($x+$xoffset1)."\" y=\"".(($charinnerheight+$yoffset1)+32)."\" style=\"fill:#777; font-size:11px;\" text-anchor=\"middle\">".$text1."</text>";
								//$vraster .= "<text x=\"".($x+$xoffset1)."\" y=\"".(($charinnerheight+$yoffset1)+45)."\" style=\"fill:#999; font-size:5px !important; \" text-anchor=\"middle\">".$text3."</text>";
								if(date('Ymd',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-".substr($c_steps[$i],6,2)))<= date('Ymd')){
									if($hoursallowed > 0){
										$polydots .= "<circle cx=\"".($x+$xoffset1)."\" cy=\"".($charinnerheight-$bh+$yoffset1)."\" r=\"4\" stroke=\"".$theme_link_color."\" class=\"but\" onclick=\"getTCTlineTeamChart('hours','".$mydate." 00:00:00','".date('Y-m-d',strtotime("+1 day",strtotime($mydate)))." 00:00:00','".$_POST['holder']."','".$_POST['tid']."');\" stroke-width=\"2\" fill=".$theme_link_color." fill-opacity=0.6 />";
									}else{
										$polydots .= "<circle cx=\"".($x+$xoffset1)."\" cy=\"".($charinnerheight-$bh+$yoffset1)."\" r=\"4\" stroke=\"".$theme_link_color."\" stroke-width=\"2\" fill=".$theme_link_color." fill-opacity=0.6 />";
									}
									$polydots .= "<g class=\"tooltip css\" transform=\"translate(".($x+$xoffset1).",".($charinnerheight-$bh+$yoffset1).")\"><rect x=\"-3em\" y=\"-30\" width=\"6em\" height=\"15px\"/><text y=\"-30\" dy=\"1em\" text-anchor=\"middle\" style=\"fill:#fff; font-size:11px;\">".number_format_i18n((int)${$c_kind}[$c_steps[$i]])."</text></g>";
									$lastx = ($x+$xoffset1);
								}
								$vraster .= "<text x=\"".($xoffset1-10)."\" y=\"".(($charinnerheight+$yoffset1)+50)."\" class=\"textbut\" onclick=\"getTCTlineTeamChart('days','".date('Y-m-d',strtotime("- 1 month",strtotime($c_min)))."','".date('Y-m-t',strtotime("- 1 month",strtotime($c_min)))."','".$_POST['holder']."','".$_POST['tid']."');\" style=\"fill:".$theme_link_color."; font-size:11px;\" text-anchor=\"start\">« "._x('show month before','used in line-chart','transcribathon')."</text>";
								$vraster .= "<text x=\"".($xoffset1+($charinnerwidth/2))."\" y=\"".(($charinnerheight+$yoffset1)+50)."\" class=\"textbut\" onclick=\"getTCTlineTeamChart('months','".date('Y')."-01-01','".date('Y-m-t',strtotime(date('Y').'-12-01'))."','".$_POST['holder']."','".$_POST['tid']."');\" style=\"fill:".$theme_link_color."; font-size:11px\" text-anchor=\"middle\">"._x('show whole year','used in line-chart','transcribathon')."</text>";
								$vraster .= "<text x=\"".($xoffset1+$charinnerwidth+10)."\" y=\"".(($charinnerheight+$yoffset1)+50)."\" class=\"textbut\" onclick=\"getTCTlineTeamChart('days','".date('Y-m-d',strtotime("+ 1 month",strtotime($c_min)))."','".date('Y-m-t',strtotime("+ 1 month",strtotime($c_min)))."','".$_POST['holder']."','".$_POST['tid']."');\" style=\"fill:".$theme_link_color."; font-size:11px;\" text-anchor=\"end\">"._x('show next month','used in line-chart','transcribathon')." »</text>";
								$x += $xstep;
							}
						
							$polyflaeche = $polyflaeche." ".($lastx).",".($charinnerheight+$yoffset1);
							// Horizontales raster
							$hraster = "";
							$hraster .= "<line x1=\"".$xoffset1."\" y1=\"".$yoffset1."\" x2=\"".($xoffset1+$charinnerwidth)."\" y2=\"".$yoffset1."\" style=\"stroke:#ddd;stroke-width:1\" />";
							$h=$s_anzahl;
							for($i=1; $i<=$s_anzahl; $i++){
								$hraster .= "<line x1=\"".$xoffset1."\" y1=\"".((($i*$stepsize)*$f)+$yoffset1)."\" x2=\"".($xoffset1+$charinnerwidth)."\" y2=\"".((($i*$stepsize)*$f)+$yoffset1)."\" style=\"stroke:#ddd;stroke-width:1\" />";
								$hraster .= "<text x=\"".($xoffset1-4)."\" y=\"".(((($i-1)*$stepsize)*$f)+5+$yoffset1)."\" style=\"fill:#999; font-size:11px\" text-anchor=\"end\">".($h*$stepsize)."</text>";
								$h--;
							}	
							
							// Ausgabe
							$content .= $hraster."\n";
							$content .= $vraster."\n";
							$content .= " <polygon points=\"".$polyflaeche."\" style=\"fill:".$theme_link_color.";opacity:0.2;stroke:#daccd9;stroke-width:1\" />\n";
							$content .= " <polyline points=\"".$poly."\" style=\"fill:none;stroke:".$theme_link_color.";stroke-width:;stroke-opacity:0.6\" />\n";
							$content .= $polydots;
						$content .= "</svg>\n"; 
					break;	
						
					case 'hours':
						$xstep =  $charinnerwidth/(sizeof($c_steps)-1);	
						$content .= "<svg height=\"100%\" data=\"".$charinnerwidth."\" width=\"100%\" viewBox=\"0 0 ".$charouterwidth." ".$charouterheight."\">\n";
							
							$poly = "";
							$polyflaeche = "";
							$polydots = "";
							$vraster = "";
							$x=0;
							$lastx = 0;
							$content .= "<text x=\"".($x+$xoffset1)."\" y=\"".($yoffset1 -12)."\" style=\"fill:#444; font-weight:700; font-size:13px; \" text-anchor=\"start\" letter-spacing=\"1\">"._x('Transcribed characters on','used in line-chart','transcribathon')."  ".date_i18n(get_option('date_format'),strtotime($c_min))."</text>";
							$polyflaeche .= $xoffset1.",".($charinnerheight-$bh+$yoffset1)." ";
							for($i=0; $i<sizeof($c_steps); $i++){
								if($i>0){ $t=" "; }else{ $t = ""; }
								if(isset(${$c_kind}[$c_steps[$i]]) && (int)${$c_kind}[$c_steps[$i]] > 0){ 
									$bh = (${$c_kind}[$c_steps[$i]]*$f);
								}else{
									$bh = 0;
								}
								if(date('Ymd H:i:s',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-".substr($c_steps[$i],6,2)." ".substr($c_steps[$i],8,2).":00:00"))<= date('Ymd H:i:s')){
									$poly .= $t.($x+$xoffset1).",".((int)($charinnerheight-$bh+$yoffset1));
									$polyflaeche .= $t.($x+$xoffset1).",".($charinnerheight-$bh+$yoffset1);
								}
								$vraster .= "<line x1=\"".($x+$xoffset1)."\" y1=\"".$yoffset1."\" x2=\"".($x+$xoffset1)."\" y2=\"".($charinnerheight+$yoffset1)."\" style=\"stroke:#ddd;stroke-width:1\" />";
								$text = date('ga',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-".substr($c_steps[$i],6,2)." ".substr($c_steps[$i],8,2).":00:00"));
								$vraster .= "<text x=\"".($x+$xoffset1)."\" y=\"".(($charinnerheight+$yoffset1)+20)."\" style=\"fill:#999; font-size:11px\" text-anchor=\"middle\">".$text."</text>";
								//$vraster .= "<text x=\"".($x+$xoffset1)."\" y=\"".(($charinnerheight+$yoffset1)+35)."\" style=\"fill:#999; font-size:11px\" text-anchor=\"middle\">".(int)${$c_kind}[$c_steps[$i]]."</text>";
								if(date('Ymd H:i:s',strtotime(substr($c_steps[$i],0,4)."-".substr($c_steps[$i],4,2)."-".substr($c_steps[$i],6,2)." ".substr($c_steps[$i],8,2).":00:00"))<= date('Ymd H:i:s')){
									$polydots .= "<circle cx=\"".($x+$xoffset1)."\" cy=\"".($charinnerheight-$bh+$yoffset1)."\" r=\"4\" stroke=\"".$theme_link_color."\" stroke-width=\"2\" fill=".$theme_link_color." fill-opacity=0.6 />";
									$polydots .= "<g class=\"tooltip css\" transform=\"translate(".($x+$xoffset1).",".($charinnerheight-$bh+$yoffset1).")\"><rect x=\"-3em\" y=\"-30\" width=\"6em\" height=\"15px\"/><text y=\"-30\" dy=\"1em\" text-anchor=\"middle\" style=\"fill:#fff; font-size:11px\">".number_format_i18n((int)${$c_kind}[$c_steps[$i]])."</text></g>";
									$lastx = ($x+$xoffset1);
								}
								$x += $xstep;
							}
							$vraster .= "<text x=\"".($xoffset1-10)."\" y=\"".(($charinnerheight+$yoffset1)+40)."\" class=\"textbut\" onclick=\"getTCTlineTeamChart('hours','".date('Y-m-d H:i:s',strtotime("- 1 day",strtotime($c_min)))."','".date('Y-m-d H:i:s',strtotime("- 1 day",strtotime($c_max)))."','".$_POST['holder']."','".$_POST['tid']."');\" style=\"fill:".$theme_link_color."; font-size:11px\" text-anchor=\"start\">« "._x('show day before','used in line-chart','transcribathon')."</text>";
							$vraster .= "<text x=\"".($xoffset1+($charinnerwidth/2))."\" y=\"".(($charinnerheight+$yoffset1)+40)."\" class=\"textbut\" onclick=\"getTCTlineTeamChart('days','".date('Y-m',strtotime($c_min))."-01','".date('Y-m-t',strtotime($c_min))."','".$_POST['holder']."','".$_POST['tid']."');\" style=\"fill:".$theme_link_color."; font-size:11px\" text-anchor=\"middle\">"._x('show whole month','used in line-chart','transcribathon')."</text>";
							$vraster .= "<text x=\"".($xoffset1+$charinnerwidth+10)."\" y=\"".(($charinnerheight+$yoffset1)+40)."\" class=\"textbut\" onclick=\"getTCTlineTeamChart('hours','".date('Y-m-d H:i:s',strtotime("+ 1 day",strtotime($c_min)))."','".date('Y-m-d H:i:s',strtotime("+ 1 day",strtotime($c_max)))."','".$_POST['holder']."','".$_POST['tid']."');\" style=\"fill:".$theme_link_color."; font-size:11px\" text-anchor=\"end\">"._x('show next day','used in line-chart','transcribathon')." »</text>";
								
							$polyflaeche = $polyflaeche." ".$lastx.",".($charinnerheight+$yoffset1);
							// Horizontales raster
							$hraster = "";
							$hraster .= "<line x1=\"".$xoffset1."\" y1=\"".$yoffset1."\" x2=\"".($xoffset1+$charinnerwidth)."\" y2=\"".$yoffset1."\" style=\"stroke:#ddd;stroke-width:1\" />";
							$h=$s_anzahl;
							for($i=1; $i<=$s_anzahl; $i++){
								$hraster .= "<line x1=\"".$xoffset1."\" y1=\"".((($i*$stepsize)*$f)+$yoffset1)."\" x2=\"".($xoffset1+$charinnerwidth)."\" y2=\"".((($i*$stepsize)*$f)+$yoffset1)."\" style=\"stroke:#ddd;stroke-width:1\" />";
								$hraster .= "<text x=\"".($xoffset1-4)."\" y=\"".(((($i-1)*$stepsize)*$f)+5+$yoffset1)."\" style=\"fill:#999; font-size:11px\" text-anchor=\"end\">".($h*$stepsize)."</text>";
								$h--;
							}	
							
							// Ausgabe
							$content .= $hraster."\n";
							$content .= $vraster."\n";
							$content .= " <polygon points=\"".$polyflaeche."\" style=\"fill:".$theme_link_color.";opacity:0.2;stroke:#daccd9;stroke-width:1\" />\n";
							$content .= " <polyline points=\"".$poly."\" style=\"fill:none;stroke:".$theme_link_color.";stroke-width:;stroke-opacity:0.6\" />\n";
							$content .= $polydots;
						$content .= "</svg>\n"; 
					break;	
						// rgba(127,57,120,0.3) zart: #c5a6c2 , voll: ".$theme_link_color."
				}
				$content .= "</div>\n"; // .tct_linechart_holder
			$content .= "</div>\n"; // .tct_linechart_area
			$content .= "<div class=\"tct_linechart_operator\">\n";
			$content .= "</div>\n"; // .tct_linechart_operator
			
	//Rueckgabe 
	$res = array();
	$res['status'] = 'ok';
	$res['content'] = $content;
	header("Content-Type: text/json; charset=utf-8");
	echo trim(json_encode($res));
endif;	




?>