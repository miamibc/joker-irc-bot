<?php

/**
 * @package    Joker
 * @subpackage Plugins
 * @author     Miami <miami@blackcrystal.net>
 * @version    1.0 on 26/05/2010
 * @license    Released under the MIT License
 * @link       www.blackcrystal.net
 *
 * TimerExample plugin
 *
 * This plugin demonstrates using of timer.
 *
 * What we have here:
 *   - Plugin waits to join channel
 *   - Plugin drops message every 10 seconds until you stop him
 */

class TimerExample {

  private $time = null; // time of next hit
  private $delay = 10;  // delay in seconds
  private $chan = null; // channel, where bot joins last

  public function JOIN(Joker $joker)
  {
    if ($joker->nick == $joker->me ) $this->chan = $joker->chan;
  }

  public function TIMER(Joker $joker)
  {

    if (!is_null($this->chan) && $this->time + $this->delay < time() )
    {
      $this->time = time() + $this->delay;
      $joker->msg($this->chan, "10 seconds timer. Time is ". date('H:i:s') );
    }

  }


}