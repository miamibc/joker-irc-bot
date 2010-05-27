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
 *   - Accept CONNECTED signal, set first NICK from nicklist, send USER and PASS commands
 *   - If nick is already used, set another NICK from nicklist
 *   - Skip MOTD and joins channels
 *   - Reply to server PING requests
 */

class Startup {

  public  $nicks    = array('BC^joker','BC^j0k3r','BC^jo'); //place here bot's nicknames
  public  $channels = array('#blackcrystal');                     //place here channels to join
  

  public function CONNECTED(Joker $joker) 
  {
    // This event will come after bot has made connection
    // @see joker.class.php -> connect() method
    $this->nicks[] = $nick = array_shift($this->nicks); //get first nick and rotate nick list
    $joker->nick($nick); //send selected nickname
    $joker->user();      //send default username @see joker.class.php -> method user
    $joker->pass();      //send default password @see joker.class.php -> method pass
  }
  
  public function ERR_NICKNAMEINUSE(Joker $joker) 
  {
    // nick is already in use, try another one
    $this->nicks[] = $newnick = array_shift($this->nicks); //get nick and rotate list
    $joker->nick($newnick);  //send selected nickname
  }
  
  public function RPL_MOTDSTART(Joker $joker)
  {
    // skip motd displaying
    $this->loglevel = $joker->loglevel;
    $joker->loglevel = false;
  }
  
  public function RPL_ENDOFMOTD(Joker $joker) 
  {
    // set back $level parameter
    $joker->loglevel = $this->loglevel;
     
    // Join channels after MOTD ends
    foreach ($this->channels as $chan) 
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