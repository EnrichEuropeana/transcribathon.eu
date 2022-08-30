<?php

class JFormFieldHelp extends JFormField
{
    var $type = 'help';

    public function getInput()
    {

        $config = acym_config();
        $level = $config->get('level');
        $link = ACYM_HELPURL.$this->value.'&level='.$level;

        return '<a class="btn" target="_blank" href="'.$link.'">'.acym_translation('ACYM_HELP').'</a>';
    }
}
