<?php

/**
 * @package    Joker
 * @subpackage Plugins
 * @author     Miami <miami@blackcrystal.net>
 * @version    1.1 on 26/05/2010
 * @license    Released under the MIT License
 * @link       www.blackcrystal.net
 * 
 * Startup plugin
 *
 * This plugin performs startup sequence to enter QuakeNet (maybe other NET's too;)
 * so, without it your bot will only stay connected to server for some time.
 *
 * What we have here:
 *   - Accept CONNECTED signal, set first nick from $joker->menicks, send USER and PASS commands
 *   - If nick is already used, set another nick from $joker->menicks
 *   - Skip MOTD and joins channels
 *   - Reply to server PING requests
 */

class Startup {

  /**
   * This event will fire when your bot connected to IRC
   * @param Joker $joker
   */
  public function CONNECTED(Joker $joker) 
  {
    // get nick and rotate nick list    
    $joker->nick();
    $joker->user();      //send default username @see joker.class.php -> method user
    $joker->pass();      //send default password @see joker.class.php -> method pass
  }

  /**
   * Catch bot's nick change
   * @param Joker $joker
   */
  public function NICK(Joker $joker)
  {
    if ($joker->nick == $joker->me)
            $joker->me = $joker->text;
  }

 public function ERR_NICKNAMEINUSE(Joker $joker) 
  {
    // nick is already in use, try another one
    // @see joker.php to setup available nicks
    $joker->altnicks[] = $nick = array_shift($joker->altnicks);
    $joker->nick($nick); 
  }
  
  public function RPL_MOTDSTART(Joker $joker)
  {
    // skip motd
    $this->loglevel = $joker->loglevel;
    $joker->loglevel = false;
  }
  
  public function RPL_ENDOFMOTD(Joker $joker) 
  {
    // set back $level parameter
    $joker->loglevel = $this->loglevel;
     
    // Join channels after MOTD ends
    foreach ($joker->autojoin as $chan)
    {
      $joker->join($chan);
    }
  }
    
  public function PING(Joker $joker) 
  { 
    // Reply to PING requests with PONG command and same digit
    // Using Joker's ->send() method to reply immediately
    if (preg_match('|^\d+$|',$joker->text))
    {
      $joker->send('PONG '.$joker->text) ;
    }
    else
    {
      $joker->send('PONG') ;
    }
       
  }
  
}