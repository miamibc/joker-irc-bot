<?php

/**
 * @package    Joker
 * @subpackage Plugins
 * @author     Miami <miami@blackcrystal.net>
 * @version    1.0 on 02.04.2009
 * @license    Released under the MIT License
 * @link       www.blackcrystal.net
 * 
 * Text processing plugin
 *
 * This plugin will flip IRC color codes to BB-style.
 * It must stay before plugins that processing PRIVMSG 
 * 
 * What we have here:
 *   - Any incoming PRIVMSG (channel, or private) will be converted
 *   - Internal format of codes is [c],[b],[u],[r]
 * 
 * @todo: converting codes on recieve and then convert back on send
 * @todo: possibly, will go from Plugins to Classes 
 */

class TextProcessing {

  public function PRIVMSG(Joker $joker) 
  {
    $joker->text = $this->flipColorCodes($joker->text);
  }

  private function flipColorCodes($text) 
  {
    static $colors = array(
    	'[c]'	=>	"\x03",
    	'[n]'	=>	"\x0f",
    	'[b]'	=>	"\x02",
    	'[u]'	=>	"\x1f",
    	'[r]'	=>	"\x16",
        "\x03"  => '[c]',
    	"\x0f"  => '[n]',
    	"\x02"  => '[b]',
    	"\x1f"  => '[u]',
    	"\x16"  => '[r]'
    );    	 
    return strtr($text,$colors);    		
  }
  
  private function encodeColorCodes($text) 
  {
    static $colors = array(
    	'[c]'	=>	"\x03",
    	'[n]'	=>	"\x0f",
    	'[b]'	=>	"\x02",
    	'[u]'	=>	"\x1f",
    	'[r]'	=>	"\x16",
    );    	 
    return strtr($text,$colors);    		
  }
  
  private function decodeColorCodes($text) 
  {
    static $colors = array(
        "\x03"  => '[c]',
    	"\x0f"  => '[n]',
    	"\x02"  => '[b]',
    	"\x1f"  => '[u]',
    	"\x16"  => '[r]'
    );    	 
    return strtr($text,$colors);    		
  }
   
}