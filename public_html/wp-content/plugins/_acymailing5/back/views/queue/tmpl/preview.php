<?php
/**
 * @package	AcyMailing for WordPress
 * @version	5.10.12
 * @author	acyba.com
 * @copyright	(C) 2009-2020 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('ABSPATH') or die('Restricted access');
?><div class="newsletter_body" id="newsletter_preview_area">
	<?php echo $this->mail->sendHTML ? $this->mail->body : nl2br($this->mail->altbody); ?>
</div>
