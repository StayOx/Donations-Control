<!--Written by NineteenEleven for Kablowsion Inc.-->
<?php
define('NineteenEleven', TRUE);
include_once 'includes/config.php';
include_once 'includes/class_lib.php';
  $found_user = false;
  $timestamp = date('U');
  $language = new language;
if (isset($_POST['langSelect'])) {
    $lang = $language->getLang($_POST['langSelect']);
}else{
    $lang = $language->getLang(DEFAULT_LANGUAGE);
}

  $cacheExpire = cache_time * 86400;
if (PLAYER_TRACKER) {

    $mysqliD = new mysqli(DB_HOST,DB_USER,DB_PASS,DONATIONS_DB);
    //$SteamQuery = new SteamQuery;
    $ConvertID = new SteamIDConvert;
    $userip = $_SERVER['REMOTE_ADDR'];

    $result = $mysqliD->query("SELECT * FROM `player_tracker` WHERE playerip='". $userip . "';")or die("Failed to connect to donations database");

    function getXML($steam_link_xml, $steamid,$timestamp){
      $mysqliC = new mysqli(DB_HOST,DB_USER,DB_PASS,DONATIONS_DB);
      global $avatarmedium, $personaname;

      $xml = @simplexml_load_file($steam_link_xml);
        if(!empty($xml)) {
            $avatar = $xml->players->player->avatar;
            $avatarmedium = $xml->players->player->avatarmedium;
            $avatarfull = $xml->players->player->avatarfull;
            $personaname =$xml->players->player->personaname;
            $steamid64 = $xml->players->player->steamid;
            $steam_link = $xml->players->player->profileurl;
            //update cache database
            $mysqliC->query("INSERT INTO `cache` (steamid,
                                                    avatar,
                                                    avatarmedium,
                                                    avatarfull,
                                                    personaname,
                                                    timestamp,
                                                    steamid64,
                                                    steam_link) 
                                            VALUES ('{$steamid}',
                                              '{$avatar}',
                                              '{$avatarmedium}',
                                              '{$avatarfull}',
                                              '{$personaname}',
                                              '{$timestamp}',
                                              '{$steamid64}',
                                              '{$steam_link}' 
                                              );")or die("Failed to update cache");


        }

      $mysqliC->close();
      }

      if($result->num_rows > 0){

          $row = $result->fetch_array(MYSQLI_ASSOC);

              $playername = $row['playername'];
              $steamid = $row['steamid'];

              $found_user = true;

              $steamid64 = $ConvertID->IDto64($steamid);
              $steam_link_xml = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . API_KEY . "&format=xml&steamids=" . $steamid64;

            $cacheReturn = $mysqliD->query("SELECT * FROM `cache` WHERE steamid ='" . $steamid ."';");
              //$cacheReturn = mysql_query($chkCacheSQL);
                if($cacheReturn->num_rows > 0) {

                  $cacheResult = $cacheReturn->fetch_array(MYSQLI_ASSOC);

                  if($cacheResult['timestamp'] > $cacheExpire){

                    //cache still valid

                    $avatarmedium = $cacheResult['avatarmedium'];

                  }else{
                    //cache expired, updating

                    $mysqliD->query("DELETE FROM `cache` WHERE steamid = '".$cacheResult['steamid'] ."';");

                    getXML($steam_link_xml, $steamid,$timestamp);
                  }
                  
                }else{
                  //nothing in cache, getting stuff
                  getXML($steam_link_xml,$steamid,$timestamp);
                }
            
        }

$mysqliD->close();
    print("<!DOCTYPE html>");
    print("<html>");
    print("<head>");

	
echo'
<html>
<meta http-equiv="Content-Type"content="text/html;charset=UTF8">
<head>
    <script type="text/javascript" src="scripts/jscolor/jscolor.js"></script>
    <script>
    function change(){
        document.getElementById("langSelect").submit();
    }
    </script>
    <style type="text/css">
        body{background-color: gray;}
        .content{
			width: 60%;
            margin-left: auto;
            margin-right: auto;
            border:3px solid black;
            border-radius: 5px;
            padding: 20px;
            background-color: white;}
        #langSelect{
            position:relative;
			margin-top:-10px;
			padding: -10px; 
            z-index:99;
            float:right;}
		#infobox{
		font-size: 14px;}
		
    </style>
</head>
    <body>';
	
    echo "<title>". $lang->main[0]->title ."</title>";
    echo '<div class="content"><center>';
    echo '<form id="langSelect" method="post">Change Language:
    <select name = "langSelect" onchange="change()">';
        $langList = $language->listLang();
        foreach ($langList as $list) {
            if ($list == $lang->language) {
               printf('<option value="%s" selected>%s</option>',$list,$availableLanguages[$list]);
            }else{
                printf('<option value="%s">%s</option>',$list,$availableLanguages[$list]);
            }
           
        }
    
	echo"</select>";
	echo"</form>";
	echo"</br>";  
	
	//Javascript to allow gifting
    print("<script type=\"text/javascript\">");
    print("function gift() {document.getElementById('steamid-box').style.display = 'block';
      document.getElementById('id-field').value = '';
      document.getElementById('id-field').placeholder = '". $lang->main[0]->steamidgift ."';
      document.getElementById('userid').style.display = 'none';
      document.getElementById('infobox').style.display = 'block';
    }");

    print("</script>");
	print("<body id='content'>");
    print("<center>");
	print("<a href=\"index.php\"><img border=\"0\" src=\"images/logo.png\" alt=\"Community Logo\" width=\"220\" height=\"200\"></a>");
	print("</br>");
    print("<input type=\"image\" src=\"images/btn_donateCC_LG.gif\" form=\"donate_form\" />");
	print("<form action=\"donate.php\" target=\"_self\" id=\"donate_form\" method=\"post\">");
	
	print("<p>". $lang->main[0]->amount ."<input type=\"text\" name=\"amount\" size=\"5\" onmouseover=\"this.style.backgroundColor='whitesmoke'\" onmouseout=\"this.style.backgroundColor='whitesmoke'\" class=\"inputbox\" value=\"5\" required=\"true\"></p>");
    if(TIERED_DONOR){
          print("<input type=\"radio\" name=\"tier\" value=\"1\" checked =\"1\" id=\"tier1\">".$group1['name']." <input type=\"radio\" name=\"tier\" value=\"2\" id=\"tier2\">".$group2['name']."<br />");
		print("<p>");
	}	
      if($found_user){
        print("<div id='steamid-box' style=\"display:none;\" ><label for='steamid_user'>Steam ID:<br /></label>");
        print("<input type=\"text\" name=\"steamid_user\" required=\"true\" id=\"id-field\"  value=\"{$steamid}\" ></div>");
        print("<div id=\"userid\">". $lang->main[0]->welcome ." {$playername} <br /><br />");
        print("<img src='{$avatarmedium}' style=\"border:1px solid black;border-radius:5px;\" /><br />");
        print("<a href='#' onclick=\"gift();\">". $lang->main[0]->gift ."</a></div>");
        print("<div id='infobox' style=\"display:none;\">");
        print("<p>". $lang->main[0]->formats ." <a href=\"http://steamidfinder.com/\" target=\"_blank\">(?)</a><br />STEAM_0:0:0000000<br />steamcommunity.com/profiles/1234567891011<br />steamcommunity.com/id/". $lang->main[0]->steamlink ."<br /></p>");
        print("</div>");
      }else{
        print("<label for=\"paypaloption1\">Steam ID:<br /></label><input type=\"text\" id=\"paypaloption1\" name=\"steamid_user\" required=\"true\" id=\"id-box\" onmouseover=\"this.style.backgroundColor='whitesmoke'\" onmouseout=\"this.style.backgroundColor='whitesmoke'\" placeholder=\"". $lang->main[0]->steamid ."\" required=\"true\" size=\"30\"></p>");
        print("<div id='infobox'>");
        print("<p>". $lang->main[0]->formats ." <a href=\"http://steamidfinder.com/\" target=\"_blank\">(?)</a><br />STEAM_0:0:0000000<br />steamcommunity.com/profiles/1234567891011<br />steamcommunity.com/id/". $lang->main[0]->steamlink ."<br /></p>");
        print("</div>");
	}

	print("</form>");
	print("</div>");
    print("</center>");
	print("</body>");
    print("</html>");
	
}else{
	print("<body id='content'>");
    print("<center>");
	print("<a href=\"index.php\"><img border=\"0\" src=\"images/logo.png\" alt=\"Community Logo\" width=\"220\" height=\"200\"></a>");
	print("</br>");
    print("<input type=\"image\" src=\"images/btn_donateCC_LG.gif\" form=\"donate_form\" />");
	print("<form action=\"donate.php\" target=\"_self\" id=\"donate_form\" method=\"post\">");
	
	print("<p>". $lang->main[0]->amount ."<input type=\"text\" name=\"amount\" size=\"5\" onmouseover=\"this.style.backgroundColor='whitesmoke'\" onmouseout=\"this.style.backgroundColor='whitesmoke'\" class=\"inputbox\" value=\"5\" required=\"true\"></p>");
    if(TIERED_DONOR){
          print("<input type=\"radio\" name=\"tier\" value=\"1\" checked =\"1\" id=\"tier1\">".$group1['name']." <input type=\"radio\" name=\"tier\" value=\"2\" id=\"tier2\">".$group2['name']."<br />");
    	print("<p>");
	}
      if($found_user){
        print("<div id='steamid-box' style=\"display:none;\" ><label for='steamid_user'>Steam ID:<br /></label>");
        print("<input type=\"text\" name=\"steamid_user\" required=\"true\" id=\"id-field\"  value=\"{$steamid}\" ></div>");
        print("<div id=\"userid\">". $lang->main[0]->welcome ." {$playername} <br />");
        print("<img src='{$avatarmedium}' style=\"border:1px solid black;border-radius:5px;\" /><br />");
        print("<a href='#' onclick=\"gift();\"> ". $lang->main[0]->gift ." </a></div>");
        print("<div id='infobox' style=\"display:none;\">");
        print("<p>". $lang->main[0]->formats ." <a href=\"http://steamidfinder.com/\" target=\"_blank\">(?)</a><br />STEAM_0:0:0000000<br />steamcommunity.com/profiles/1234567891011<br />steamcommunity.com/id/". $lang->main[0]->steamlink ."<br /></p>");
        print("</div>");
      }else{
        print("<label for=\"paypaloption1\">Steam ID:<br /></label><input type=\"text\" id=\"paypaloption1\" name=\"steamid_user\" required=\"true\" id=\"id-box\" onmouseover=\"this.style.backgroundColor='whitesmoke'\" onmouseout=\"this.style.backgroundColor='whitesmoke'\" placeholder=\"". $lang->main[0]->steamid ."\" required=\"true\" size=\"30\"></p>");
        print("<div id='infobox'>");
        print("<p>". $lang->main[0]->formats ." <a href=\"http://steamidfinder.com/\" target=\"_blank\">(?)</a><br />STEAM_0:0:0000000<br />steamcommunity.com/profiles/1234567891011<br />steamcommunity.com/id/". $lang->main[0]->steamlink ."<br /></p>");
        print("</div>");
    }
	
    print("</form>");
	print("</div>");
    print("</center>");
	print("</body>");
    print("</html>");
}
echo $footer

?>