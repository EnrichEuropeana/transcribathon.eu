<?php
/**
 * @package	AcyMailing for WordPress
 * @version	5.10.12
 * @author	acyba.com
 * @copyright	(C) 2009-2020 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('ABSPATH') or die('Restricted access');
?><?php

class acyeditorHelper{

    var $height = '600';
    var $rows = 30;
    var $name = '';
    var $content = '';
    var $tempid = 0;

    function __construct(){
    }

    function setTemplate($id){
        $this->tempid = $id;
    }

    function prepareDisplay(){
    }

    function setDescription(){
        $this->height = 200;
        $this->rows = 10;
    }

    function setContent($var){
        if($var != 'newhtml') $this->content = $var;
        return 'tinymce.activeEditor.setContent('.$var.');';
    }

    function setEditorStylesheet($tempid){
        $function = 'try{
                        var iframe = document.getElementById("'.$this->name.'_ifr");
                        if(typeof iframe != undefined && iframe){
                            var css = iframe.contentDocument.querySelector(\'link[href*="'.ACYMAILING_MEDIA_FOLDER.'/templates/css/template_"]\');
                            var type = "direct";
                            if(typeof css == undefined || !css){
                                css = iframe.contentDocument.querySelector(\'link[href*="com_acymailing&ctrl=template&task=load&tempid="]\');
                                var type = "url";
                            }
                        
                            if('.$tempid.' !== 0){
                                if(typeof css != undefined && css){
                                    if(type == "direct"){
                                        css.href = css.href.replace(/template_\d{1,10}.css/, "template_"+'.$tempid.'+".css");
                                    }else{
                                        css.href = css.href.replace(/&tempid=\d{1,10}&time/, "&tempid="+'.$tempid.'+"&time");
                                    }
                                }else{
                                    var csselem = iframe.contentDocument.createElement("link");
                                    csselem.rel = "stylesheet";
                                    csselem.type = "text/css";
                                    csselem.href = "'.acymailing_rootURI().ACYMAILING_MEDIA_FOLDER.'/templates/css/template_"+'.$tempid.'+".css";
                                    
                                    iframe.contentDocument.head.appendChild(csselem);
                                }
                            }else{
                                if(typeof css != undefined && css) css.parentElement.removeChild(css);
                            }
                        }
                    }catch(err){
                    }';

        return $function;
    }

    function getContent(){
        return $this->content;
    }

    function addPlugins($plugins){
        $plugins['table'] = ACYMAILING_JS.'tinymce/table.min.js';
        $plugins['acytags'] = ACYMAILING_JS.'tinymce/acytags.js';
        return $plugins;
    }

    function addButtons($buttons){
        $position = array_search('wp_more', $buttons);
        if($position !== false) $buttons[$position] = '';

        array_unshift($buttons, 'separator', 'fontsizeselect');
        array_unshift($buttons, 'separator', 'fontselect');
        array_push($buttons, 'separator', 'table');
        array_push($buttons, 'separator', 'acytags');

        $this->addButtonAtPosition($buttons, 'alignjustify', 'alignright');
        $this->addButtonAtPosition($buttons, 'underline', 'italic');
        $this->addButtonAtPosition($buttons, 'strikethrough', 'underline');

        return $buttons;
    }

    function addButtonsToolbar($buttons){
        $position = array_search('strikethrough', $buttons);
        if($position !== false) $buttons[$position] = '';
        $this->addButtonAtPosition($buttons, 'backcolor', 'forecolor');

        return $buttons;
    }

    function addButtonAtPosition(&$buttons, $newButton, $after){
        $position = array_search($after, $buttons);

        if($position === false){
            array_push($buttons, 'separator', $newButton);
        }else{
            array_splice($buttons, $position+1, 0, $newButton);
        }
    }

    function display(){
        add_filter('mce_external_plugins', array($this, 'addPlugins'));
        add_filter('mce_buttons', array($this, 'addButtons'));
        add_filter('mce_buttons_2', array($this, 'addButtonsToolbar'));
        
        $css = '';
        if(!empty($this->tempid)) $css = acymailing_rootURI().ACYMAILING_MEDIA_FOLDER.'/templates/css/template_'.$this->tempid.'.css';

        $options = array(
            'editor_css' => '<style type="text/css">
                                .alignleft{float:left;margin:0.5em 1em 0.5em 0;}
                                .aligncenter{display: block;margin-left: auto;margin-right: auto;}
                                .alignright{float: right;margin: 0.5em 0 0.5em 1em;}
                             </style>',
            'editor_height' => $this->height,
            'textarea_rows' => $this->rows,
            "wpautop" => false,
            'tinymce' => array(
                'content_css' => $css,
                'content_style' => '.alignleft{float:left;margin:0.5em 1em 0.5em 0;} .aligncenter{display: block;margin-left: auto;margin-right: auto;} .alignright{float: right;margin: 0.5em 0 0.5em 1em;}'
            )
        );
        wp_editor($this->content, $this->name, $options);
    }

    function jsCode(){
        return '';
    }

    function jsMethods(){
        return '
        function jInsertEditorText(tag, editor, previousSelection){
            tinymce.activeEditor.execCommand("mceInsertContent", false, tag);
        }';
    }

}//endclass
