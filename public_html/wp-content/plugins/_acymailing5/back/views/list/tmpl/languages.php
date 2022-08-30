<?php
/**
 * @package	AcyMailing for WordPress
 * @version	5.10.12
 * @author	acyba.com
 * @copyright	(C) 2009-2020 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('ABSPATH') or die('Restricted access');
?><div class="<?php echo acymailing_isAdmin() ? 'acyblockoptions' : 'onelineblockoptions'; ?>">
	<span class="acyblocktitle"><?php echo acymailing_translation('LANGUAGES'); ?></span>
	<?php echo $this->languages->display('languages', $this->list->languages); ?>
</div>
