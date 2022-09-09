<?php

class Meow_MGCL_Core_Button_Meow_Gallery {

	public function __construct( $core ) {
    $this->core = $core;
    add_filter( 'mgcl_linkers', array( $this, 'linker' ), 100, 7 );
	}

	function linker( $handled, $element, $parent, $mediaId, $url, $rel, $target ) {
    // Let's look for the closest link tag enclosing the image

    $anotherParent = $parent->parent();

    if ( $handled || $anotherParent->class !== 'mgl-icon' ) {
      return $handled;
    }

    $potentialLinkNode = $parent;
    $css = 'position: absolute; height: auto; font-size: 15px; text-decoration: none;
      padding: 2px 10px; bottom: 5px; left: 5px; box-shadow: 0px 0px 1px 0px rgba(0, 0, 0, 0.5);
      border-radius: 10px; width: calc(100% - 10px); text-align: center; background: rgba(15, 115, 239, 0.80); color: white;';
    $css_hover = 'background: rgba(15, 115, 239, 0.9);';

    $id = uniqid();

    $style = "<style>
      #mgcl-${id} {
      }
      #mgcl-${id} a {
        position: absolute; 
        bottom: 8px; left: 8px;
        height: auto !important;
        width: calc(100% - 16px);
        flex: none !important;
      }
      #mgcl-${id} a {
        font-size: 15px;
        text-decoration: none;
        padding: 2px 10px;
        box-shadow: 0px 0px 1px 0px rgba(0, 0, 0, 0.5);
        border-radius: 10px;
        text-align: center;
        background: rgba(15, 115, 239, 0.80);
        color: white;
        z-index: 100;
      }
      #mgcl-${id} a:hover { 
        background: rgba(15, 115, 239, 0.9);
      }
    </style>";

    //$style = "<style>#mgcl-${id} a { ${css} } #mgcl-${id} a:hover { ${css_hover} }</style>";

    if ( $this->core->enableLogs ) {
      error_log( 'Linker: Will embed the IMG tag.' );
    }
    $label = $value = get_option( 'mgcl_button_label', "Click here" );
    if ( $this->core->parsingEngine === 'HtmlDomParser' ) {
      $element->outertext = $style . '<div id="mgcl-' . $id . '">' . $element . '<a href="' . $url . 
        '" class="custom-link-button no-lightbox" onclick="event.stopPropagation()" target="' . $target . '" rel="' . $rel . '">
        ' . $label . '</a></div>';
    }
    else {
      return false;
    }
    return true;
	}
}

?>