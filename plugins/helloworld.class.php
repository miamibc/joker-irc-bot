<?php

/**
 * @package    Joker
 * @subpackage Plugins
 * @author     Miami <miami@blackcrystal.net>
 * @version    1.1 on 26/05/2010
 * @license    Released under the MIT License
 * @link       www.blackcrystal.net
 * 
 * HelloWorld plugin
 *
 * This example plugin says "Hello, #channelname" when bot
 * joins any channel. After the first Hello it removes himself from
 * plugins and don't recieve events anymore.
 *
 * What we have here:
 *   - Plugin is listens to JOIN event
 *   - Plugin send MSG if bot joins channel
 *   - Plugin removes himself from plugins
 */

class HelloWorld {

  public function JOIN(Joker $joker)
  {
    if ( $joker->nick == $joker->me )
    {
      $joker->msg($joker->chan,'Hello, '.$joker->chan);
      $joker->unload('HelloWorld');
    }
  }
  
}