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

  // allowed comands
  public $commands = array(
      'join', 'part', 'nick', 'quit', 'yo', 'kick',
      'msg', 'notice', 'action', 'topic', 'invite',
      'op', 'deop', 'vo', 'devo', 'say', 'to',
      'plugins', 'load', 'unload', 'help'
  );

  public function PRIVMSG(Joker $joker)
  {
    // do not process channel messages
    if ($joker->chan)
      return;

    // admins-only @see joker.php to set up admins
    if (!in_array($joker->nick, $joker->admins))
      return;

    $hash = $joker->param;
    $cmd = array_shift($hash);
    
    // if not allowed command
    if (!in_array($cmd, $this->commands))
      return;

    // call joker->method or this->method
    $result = method_exists($joker, $cmd) 
            ? call_user_func_array(array($joker, $cmd), $hash)
            : call_user_func_array(array($this, $cmd), array_merge(array($joker), $hash));

    // if result is available, answer with it
    if ($result)
      $joker->answer( $result );

    // stop processing rest plugins
    return Joker::STOP;
  }

  /**
   * Change target, or display current
   * @param Joker $joker
   * @param <type> $target
   * @return <type>
   */
  private function to(Joker $joker, $target = null)
  {
    if (is_null($target)) return 'Target is: ' . $this->to[$joker->nick];
    $this->to[$joker->nick] = $target;
    return 'Target changed to '. $target;
  }

  /**
   * For quick test
   * @param Joker $joker
   * @return <type>
   */
  private function say(Joker $joker,$msg = '')
  {
    if (!$msg) return 'Roflmao! Say what? o_0';
    if (!isset($this->to[$joker->nick] ) ) $this->to($joker, $joker->nick);
    $joker->msg($this->to[$joker->nick], implode(' ', array_slice( func_get_args(), 1)));
  }

  private function plugins(Joker $joker)
  {
    return implode(' ', array_keys($joker->plugins));
  }

  private function help(Joker $joker, $cmd='' )
  {
    switch (strtolower($cmd)):
    case '':
      return "Available commands: " .
      implode('|',$this->commands) ." ".
      "To get more help type: help [command]";
    case 'load':
      return  "load [plugin] - this command loads or reloads plugin from plugins folder. ".
      "It performs syntax check of file, before loading it, to avoid fatal ".
      "errors. Yes, this check does not protect your bot from fatal errors ".
      "to 100%, but some insurance really helps.";
    case 'unload':
      return "unload [plugin] - Unloads plugin. Actually, this is not unloading, just removing ".
      "from plugins array. Removed plugin stops recieving signals and (maybe) ".
      "PHP garbage collector will destroy it's instance in some time.";
    case 'plugins':
      return "plugins - shows list of loaded plugins.";
    case 'to':
      return "to [target] - change current target, whom to say. It can be #channel or nickname. Without parameter, bot shows you current target. You can say from bot using 'say' command";
    case 'say':
      return "say [something] - say somethig from your bot. To change whom to say use 'to' command. Default, is your nick, so you can quickly test by typing 'say something'.";
    default:
      return "No help for '{$cmd}' available. Type 'help' to see list of available commands";
    endswitch;
  }

}

