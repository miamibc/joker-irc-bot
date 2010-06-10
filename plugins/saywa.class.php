<?php

/**
 * @package    Joker
 * @subpackage Plugins
 * @author     Miami <miami@blackcrystal.net>
 * @version    1.0 on 26/05/2010
 * @license    Released under the MIT License
 * @link       www.blackcrystal.net
 *
 * Say Wa plugin
 *
 * No description available (yet)
 */
class SayWa {

  public function PRIVMSG(Joker $joker)
  {

    if (strtolower($joker->chan) == '#blackcrystal' && stripos($joker->text, 'joker') !== FALSE)
      $joker->answer($joker->nick . ', a?');
  }

}

