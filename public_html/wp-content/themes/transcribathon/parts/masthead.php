<?php
/**
 * Part Name: Default Masthead
 */
?>
<header id="masthead" class="site-header" role="banner">
	<?php get_template_part( 'parts/menu', apply_filters( 'vantage_menu_type', siteorigin_setting( 'layout_menu' ) ) ); ?>
	

	<div id="MyPopup"> 
   
   <div class=" ">

		 <!-- Modal content -->
	   <span id="MyPopupp" class="cookie-confrm">OK</span>
     <P>This website uses cookies to ensure you get the best experience on our website. By clicking or navigating the site, you agree to allow our collection of information through cookies. <a href='<?php echo network_home_url(); ?>terms-of-use/' target="_blank">More info</a>
     </p>
   </div>
   
</div>
</header><!-- #masthead .site-header -->


<script>


function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + (exdays*24*60*60*1000));
  var expires = "expires="+d.toUTCString();
  document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
  var name = cname + "=";
  var ca = document.cookie.split(';');
  for(var i=0; i<ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0)==' ') c = c.substring(1);
    if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
  }
  return "";
}

var cookie = getCookie('shown');
if (!cookie) {
  showPopup();
}

function showPopup() {
  
  	document.getElementById("MyPopupp").onclick= function(){
  		
    setCookie('shown', 'true', 365);
     document.querySelector('#MyPopup').style.display = 'none';
  };	
  document.querySelector('#MyPopup').style.display = 'block';
}


</script>

<style>

#MyPopup {
  display:none;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 501;
  background: #B33E3E;
  padding: 23px 30px 20px 30px;
}

#MyPopup p {
  color: #ffffff;
  font-size: 17px;
  line-height: 44px;
  /* padding: 0; */
  margin: 0;
}

#MyPopup a {
  color: #ffffff;
  font-style: oblique;
}

.modaler {
  position: fixed; /* Stay in place */
  z-index: 1; /* Sit on top */
  left: 0;
  bottom: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  overflow: auto; /* Enable scroll if needed */
  background-color: rgb(0,0,0); /* Fallback color */
  background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* The cookie-confrm Button */
.cookie-confrm {
  color: #fff;
  float: right;
  font-size: 1.4em;
  font-weight: bold;
  border: 2px solid #ffffff;
  padding: 8px 8px;
  border-radius: 8px;
  margin-left: 20px;
  margin-right: 32px;
}

.cookie-confrm:hover,
.cookie-confrm:focus {
  color: white;
  text-decoration: none;
  cursor: pointer;
}

</style>