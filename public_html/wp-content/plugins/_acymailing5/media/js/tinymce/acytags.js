/**
 * @package    AcyMailing for WordPress
 * @version    5.10.12
 * @author     acyba.com
 * @copyright  (C) 2009-2020 ACYBA S.A.R.L. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

(function() {
	if(document.getElementById('toolbar-tag')){
		tinymce.PluginManager.add('acytags', function(editor, url){
			editor.addButton('acytags', {
				tooltip: 'Tags', icon: 'acytags', onclick: function(){
					if(document.getElementById('a_tag')){
						document.getElementById('a_tag').click();
					}else{
						document.getElementById('toolbar-tag').click();
					}
				}
			});
		});
	}
})();
