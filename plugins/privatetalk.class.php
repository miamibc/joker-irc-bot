<?php

/**
 * @package    Joker
 * @subpackage Plugins
 * @author     Miami <miami@blackcrystal.net>
 * @version    1.1 on 26/05/2010
 * @license    Released under the MIT License
 * @link       www.blackcrystal.net
 * 
 * Private Talk
 *
 * This is example plugin that uses internal variable to hold some
 * user information. This plugin accepts only private chat and stores
 * last time and a message, then output this back to user on next requests.
 *
 * Warning! Avoid collecting of lots information inside the memory,
 * cuz, normally PHP limits it. If you want more data stored
 * use database or external files. This example is only the example... ;)
 *
 * What we have here:
 *   - On first private MSG if shows "Hi, nick" and stores its time/message in array
 *   - If info exists, reply with last time and a message
 */

class PrivateTalk {
  
  private $infos = array(); //here we hold assoc.array of last chats
  
  public function PRIVMSG(Joker $joker) {

    //accept only private messages from users
    if ($joker->chan) return;
    
    if (!isset($this->infos[$joker->nick])) 
    {
      //first time you talk to Joker, it replies Hi, nickname...
      $joker->answer("Hi, $joker->nick. Nice to meet you! Type something again...");
      $this->infos[$joker->nick] = array('time'=>time(),'text'=>$joker->text);
    }
    else 
    {
      //if info exists, msg him about last chat
      $info = $this->infos[$joker->nick];
      $seconds = time() - $info['time'];
      $joker->answer("$seconds seconds ago you told me: $info[text]");
      
      //remember text, that was sayed
      $this->infos[$joker->nick] = array('time'=>time(),'text'=>$joker->text);        
    }
    
  }
}