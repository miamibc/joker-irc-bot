<?php

/**
 * @package    Joker
 * @subpackage Plugins
 * @author     Miami <miami@blackcrystal.net>
 * @version    1.0 on 26/05/2010
 * @license    Released under the MIT License
 * @link       www.blackcrystal.net
 * 
 * Simple administration plugin.
 *
 * What we have here:
 *   - Plugin accepts only private messages from admin(s)
 *   - You can control bot using standart IRC commands:
 *       'join' , 'part',  'nick',  'quit', 'topic', 'invite' ,'action', 'yo'
 *       'msg', 'notice' ,'op' , 'deop' ,'vo', 'devo', 'kick'
 *   - You can list, add or remove plugins using 'list', 'load', 'unload' commands
 */

class Admin {

  // Admin nicknames list
  // NB! This is not secure, but Simple and transparent
  public $admins = array('BC^miami'); 

  public function PRIVMSG(Joker $joker)
  {
    
    // do not process channel messages
    if ($joker->chan)
      return;

    // admins-only
    if (!in_array($joker->nick, $this->admins))
      return;

    $result = '';

    $hash = $joker->param;
    $par1 = array_shift($hash);

    switch (true) {

      // here comes one-parameter functions
      case in_array( $par1, array( 
          'join' , 'part',  'nick',  'quit', 'load', 'unload'
      )):
        $par2 = implode(' ', $hash);
        $result = $joker->$par1($par2);
        break;

      // two-parameter functions
      case in_array( $par1, array(
          'msg', 'notice' ,'op' , 'deop' ,'vo', 'devo', 'topic', 'invite' ,'action', 'yo'
      )):
        $par2 = array_shift($hash);
        $par3 = implode(' ', $hash);
        $result = $joker->$par1($par2, $par3);
        break;

      // three-parameter functions
      case $par1 == 'kick';
        $par2 = array_shift($hash);
        $par3 = array_shift($hash);
        $par4 = implode(' ', $hash);
        $result = $joker->$par1($par2, $par3, $par4);
        break;

      // three-parameter functions
      case $par1 == 'list';
        $joker->msg($joker->nick, implode(' ', array_keys($joker->plugins)));
        break;

    }

    if ($result) $joker->msg($joker->nick, $result);
    return Joker::STOP;

  }

}