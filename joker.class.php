<?php

/**
 * @package    Joker
 * @subpackage Classes
 * @author     Miami <miami@blackcrystal.net>
 * @version    1.1 on 26/05/2010
 * @license    Released under the MIT License
 * @link       www.blackcrystal.net
 *
 * Joker the IRC bot
 *
 * This is main class - a base for bot. It contains methods, that can be executed
 * from your plugins. Bot has built-in flood protection, timers and powerful
 * plugin system, that allows you to reload plugin classes on-fly without restart.
 */

class Joker {

  // Log level: false - no output, true - full output, array(..) - selected types:
  // 'e' - events, 'p' - parser, 'i' - incoming,
  // 'o' - outgoing, '+' - information, 'p' - plugin
  public $loglevel = array('i','o');

  public $plugins = array();       // hold plugins
  public $socket  = null;          // hold socket
  public $buffer  = array();       // hold outgoing buffer
  public $flood   = null;          // hold antiflood timer

  public $me       = null;
  public $altnicks = array();
  public $autojoin = array();
  public $adminss  = array();


  public $server,$port,
         $event,$addr,$nick,$user, // event  information
         $host,$chan,$raw,$param;  // event  information

  const STOP = 'stop';             // send this signal to stop plugins loop

  /**
   * Add and process event, by calling active plugins
   * that have desired method.
   * @param string $event
   */
  public function event($event=null)
  {
    if (!is_null($event)) $this->event = $event;

    // do nog log timers, cuz it's too much messages
    if ($this->event != 'TIMER')
      @$this->log('e', "M:$this->me E:$this->event N:$this->nick C:$this->chan T:$this->text") ;

    //now we run method on all plugins that has is
    foreach ($this->plugins as $pluginName => $instance)
    {
      // skip, if plugin is removed by another plugin
      if (!isset($this->plugins[$pluginName])) continue;

      // run method on plugin
      $eventName = $this->event;
      if (method_exists($instance,$this->event))
      {
        $result = $instance->$eventName($this);
        // stop processing on special signal
        if ($result === self::STOP) break;
      }
    }
    $this->clearEvent();
  }

  /**
   * Clear current event and all related info
   */
  public function clearEvent()
  {
  	$this->addr  = $this->nick  = $this->user  = $this->host  =
  	$this->chan  = $this->event = $this->text = $this->raw  = '';
  	$this->param = array();
  }

  /**
   * Load plugin
   * @param string $name
   */
  public function load($name)
  {

    $name = strtolower($name);
    $filename = dirname(__FILE__) . "/plugins/$name.class.php";
    if (!file_exists($filename))
    {
      $this->log('p', "$filename is not exists");
      return "$filename is not exists";
    }
    $error = trim(`php -l $filename`);
    if (stripos($error, 'No syntax errors detected') === FALSE )
    {
      $this->log('p', $error);
      return $error;
    }
    $file = file_get_contents($filename);
    $rev  = 'plugin'.uniqid();
    //set fake classname (one replace only)
    $file = preg_replace("@class\s+(\w+)@i", "class {$rev}", $file,1);
    $this->log('p', "Loading $name as $rev from $filename");
    eval('?>'.$file );
    $this->plugins[$name] = new $rev($this);
    return "$name loaded from $filename as $rev";
  }

  /**
   * Unload plugin
   * @param string $name
   */
  public function unload($name)
  {
    $name = strtolower($name);
    unset($this->plugins[$name]);
    $this->log('p', "$name unloaded");
    return "$name unloaded";

  }

  /**
   * Main loop
   */
  private function main()
  {
    // this is infinitive loop, that reads incoming
    // messages, sends outgoing and runs timers
    while (!feof($this->socket))
    {
      $this->clearEvent(); //clear event
      $this->raw = trim(fgets($this->socket, 2048));             //read incoming raw
      if ($this->raw != '') {
        $this->log('i', $this->raw);
  	    $this->parse(); //parse raw
      }
      if ($this->event) $this->event();                          //run event if exisis
      if (count($this->buffer)>0 && time() > $this->flood) // check flood protection and message in buffer
              $this->send(array_shift($this->buffer));           //send from buffer
      $this->event('TIMER');                                     //process timer
      usleep(100);

    }
  }

  /**
   * Log messages to console
   * @param string $type 'e' - events, 'p' - parser, 'i' - incoming, 'o' - outgoing, '+' - information, 'p' - plugin
   * @param string $text
   */
  public function log($type='+', $text='')
  {
    if ($this->loglevel === false) return;
    if ( ($this->loglevel === true) ||
            ( is_array( $this->loglevel ) && in_array( $type, $this->loglevel) ) )
      echo "\n[".date('H:i:s')."] $type $text";
  }

  /**
   * this method you can use for sending commands
   * it will be sent to server using antiflood system
   * @param string $raw
   */
  public function queue($raw)
  {
    $this->buffer[] = $raw;
  }

  /**
   * Send command to server
   * please avoid sending something by this method directly, cuz this will not prevent
   * excess flood by your bot. You can use this directly ONLY for PING reply
   * otherwise, use $this->queue
   * @param string $raw
   */
  public function send($raw)
  {
    $this->log('o', $raw);
    fwrite($this->socket, "$raw\r\n"); //send command
    $this->flood = time()+1;     //set antiflood timer to current time+ 1
  }

  /**
   * Incoming IRC commands parser
   */
  private function parse()
  {

    $matches = array();

  	// :nick!user@host PRIVMSG #chan :text
  	if (    preg_match('/^:(\S*) (\S*) (#\S*) :(.*)$/Ui',$this->raw,$matches))
  	list(,$this->addr,$this->event,$this->chan, $this->text) = $matches;

  	// :nick!user@host PRIVMSG #chan :\001ACTION text\001
  	if (    preg_match('/^:(\S*) PRIVMSG (#\S*) :\001(ACTION) (.*)\001$/Ui',$this->raw,$matches))
  	list(,$this->addr,$this->chan, $this->event, $this->text) = $matches;

  	// :nick!user@host JOIN #chan
  	elseif (preg_match('/^:(\S*) (\S*) (#\S*)$/Ui',$this->raw,$matches))
  	list(,$this->addr,$this->event,$this->chan) = $matches;

  	// :server 376 BC^j0k3r :End of /MOTD command.
  	elseif (preg_match('/^:(\S*) (\S*) \S* :(.*)$/Ui',$this->raw,$matches))
  	list(,$this->addr,$this->event,$this->text) = $matches;

  	// :server 254 me 88735 :channels formed
  	elseif (preg_match('/^:(\S*) (\S*) \S* (\S* :.*)$/Ui',$this->raw,$matches))
  	list(,$this->addr,$this->event,$this->text) = $matches;

  	// :wserver 433 * newnick :Nickname is already in use.
  	elseif (preg_match('/^:(\S*) (\S*) \* \S* :(.*)$/Ui',$this->raw,$matches))
  	list(,$this->addr,$this->event,$this->text) = $matches;

  	// :server 366 me #bctest :End of /NAMES list.
  	elseif (preg_match('/^:(\S*) (\S*) \S* (#\S*) :(.*)$/Ui',$this->raw,$matches))
  	list(,$this->addr,$this->event,$this->chan, $this->text) = $matches;

  	// :nick!user@host NICK :newnick
  	elseif (preg_match('/^:(\S*) (\S*) :(.*)$/Ui',$this->raw,$matches))
  	list(,$this->addr,$this->event, $this->text) = $matches;

  	// PING :text
  	elseif (preg_match('/^([^:]*) :(.*)$/Ui',$this->raw,$matches))
  	list(,$this->event,$this->text) = $matches;

  	// :server 005 me WHOX WALLCH....
  	elseif (preg_match('/^:(\S*) (\S*) \S* (.*)$/Ui',$this->raw,$matches))
  	list(,$this->addr,$this->event,$this->text) = $matches;

  	else {
  	  //else output a message and stop processing
  	  $this->log('e',"No matches for '$this->raw'");
  	  return;
  	}

  	$this->event = $this->eventName($this->event); //convert numeric event to string
  	@list($this->nick,$this->user,$this->host) = @explode('@',str_replace('!','@',$this->addr)); //get nick|user|host from addr
  	$this->text  = trim($this->text); //trim text
  	$this->param  = preg_split('|\s+|',$this->text); //make param array
  }

  /**
   * Convert numeric commands to readable analog.
   * If no translation for event found, convert to NUMBER_xxx
   * @param string $numeric
   * @return string
   */
  private function eventName($numeric)
  {
    // if event already named, replace spaces with _ and return
    // here we check only first char, cuz method names must start from letter
    if (preg_match('|[A-Z]|',$numeric{0})) return str_replace(' ','_',$numeric);

    // thanks to yapircl for this great RFC-1459 names list
    // @see http://projects.gtk.mine.nu
    // @see http://rfc.sunsite.dk/rfc/rfc1459.html
    static $events = array(
      '001' => 'RPL_WELCOME', '002' => 'RPL_YOURHOST', '003' => 'RPL_CREATED', '004' => 'RPL_MYINFO',
      '005' => 'RPL_ISUPPORT', 221 => 'RPL_UMODEIS', 250 => 'RPL_STATSDLINE', 251 => 'RPL_LUSERCLIENT',
      252 => 'RPL_LUSEROP', 253 => 'RPL_LUSERUNKNOWN', 254 => 'RPL_LUSERCHANNELS', 255 => 'RPL_LUSERME',
      301 => 'RPL_AWAY', 302 => 'RPL_USERHOST', 303 => 'RPL_ISON', 305 => 'RPL_UNAWAY', 306 => 'RPL_NOWAWAY',
      311 => 'RPL_WHOISUSER', 312 => 'RPL_WHOISSERVER', 313 => 'RPL_WHOISOPERATOR', 315 => 'RPL_ENDOFWHO',
      317 => 'RPL_WHOISIDLE', 318 => 'RPL_ENDOFWHOIS', 319 => 'RPL_WHOISCHANNELS', 321 => 'RPL_LISTSTART',
      322 => 'RPL_LIST', 323 => 'RPL_LISTEND', 324 => 'RPL_CHANNELMODEIS', 329 => 'RPL_CREATIONTIME',
      331 => 'RPL_NOTOPIC', 332 => 'RPL_TOPIC', 333 => 'RPL_TOPICWHOTIME', 341 => 'RPL_INVITING',
      351 => 'RPL_VERSION', 352 => 'RPL_WHOREPLY', 353 => 'RPL_NAMREPLY', 366 => 'RPL_ENDOFNAMES',
      367 => 'RPL_BANLIST', 368 => 'RPL_ENDOFBANLIST', 371 => 'RPL_INFO', 372 => 'RPL_MOTD',
      375 => 'RPL_MOTDSTART', 376 => 'RPL_ENDOFMOTD', 381 => 'RPL_YOUREOPER', 391 => 'RPL_TIME',
      412 => 'ERR_NOTEXTTOSEND', 422 => 'ERR_NOMOTD', 433 => 'ERR_NICKNAMEINUSE', 441 => 'ERR_USERNOTINCHANNEL',
      462 => 'ERR_ALREADYREGISTRED', 462 => 'ERR_NOPERMFORHOST', 464 => 'ERR_PASSWDMISMATCH',
      465 => 'ERR_YOUREBANNEDCREEP', 467 => 'ERR_KEYSET', 471 => 'ERR_CHANNELISFULL', 472 => 'ERR_UNKNOWNMODE',
      473 => 'ERR_INVITEONLYCHAN', 474 => 'ERR_BANNEDFROMCHAN', 475 => 'ERR_BADCHANNELKEY',
      481 => 'ERR_NOPRIVILEGES', 482 => 'ERR_CHANOPRIVSNEEDED', 491 => 'ERR_NOOPERHOST'
    );

    if (isset($events[$numeric])) return $events[$numeric];
    $this->log('e',"No name fo event $numeric, converted to NUMBER_$numeric");
    return 'NUMBER_'.$numeric;
  }

 /**
   * Connect command
   * @param string $server
   * @param string $port
   */
  public function connect($server=null, $port=null)
  {
    //disconnect if connected
    if ($this->socket) $this->disconnect();
    $this->log('+','Connecting '.$this->server.'...');

    //change server|port variables, if given in parameters, otherwise use old
    if (!is_null($server)) $this->server = $server;
    if (!is_null($port)) $this->$port = $port;

    //connect
    $erno = $errstr = 0;
    $this->socket = fsockopen($server, $port, $erno, $errstr, 30);
    if(!$this->socket) die("Could not connect $erno $errstr");

    //this option allows our bot to process timers and other
    //stuff while waiting for commands
    stream_set_blocking($this->socket,0);

    //run event CONNECTED
    $this->event('CONNECTED');

    //start main loop
    $this->main();

    /*
    I made this system to be maximum interesting for developers, like I am,
    that's why all processes from this point are controlled by plugins.
    Now your bot is connected to IRC, it sends CONNECTED event
    to all the plugins, that accepts it, and Startup plugin doing this.
    @see plugins/startup.class.php if you really interested in startup sequence
    @see plugins/helloworld.class.php if you want something easy
    */

  }

  /**
   * Disconnect command
   * @return string
   */
  public function disconnect()
  {
    if (!$this->socket) return;
    fclose($this->socket);
    $this->event('DISCONNECTED');
    $this->log('+','Disconnected from '.$this->server);
    return 'Disconnected from '.$this->server;
  }

  /**
   * Nick command
   * @param string $nick
   */
  public function nick($nick=null)
  {
    if (is_null($nick)) $nick = $this->me;
    $this->queue("NICK $nick");
    // @see startup plugin, where nick changing catches
  }

  /**
   * User command
   * @param string $username
   * @param string $hostname
   * @param string $servername
   * @param string $realname
   */
  public function user($username='joker', $hostname='joker', $servername='blackcrystal.net', $realname='BC^joker the IRC bot') { $this->queue("USER $username $hostname $servername :$realname"); }

  /**
   * PASS command
   * @param string $password
   */
  public function pass($password='NOPASS') { $this->queue("PASS $password"); }

  /**
   * JOIN command
   * @param string $chan
   */
  public function join($chan) { $this->queue("JOIN $chan") ; }

  /**
   * PART command
   * @param string $chan
   */
  public function part($chan) { $this->queue("PART $chan") ; }

  /**
   * MSG command
   * @param string $target
   * @param string $msg
   */
  public function msg($target,$msg)
  {
    $msg = implode(' ',array_slice(func_get_args(), 1));
    $msg = wordwrap($msg, 430, "\n", true);
    foreach (explode("\n", $msg) as $item)
    {
      $this->queue("PRIVMSG $target :$item");
    }
  }

  /**
   * Shortcut to give quick answer to somebody (channel or nick)
   * @param string $msg
   */
  public function answer($msg)
  {
    $msg = implode(' ',func_get_args());
    $target = $this->chan ? $this->chan : $this->nick;
    $this->msg($target, $msg);
  }


  /**
   * NOTICE command
   * @param string $target
   * @param string $msg
   */
  public function notice($target,$msg)
  {
    $msg = implode(' ',array_slice(func_get_args(), 1));
    $msg = wordwrap($msg, 430, "\n", true);
    foreach (explode("\n", $msg) as $item)
    {
      $this->queue("NOTICE $target :$item");
    }
  }

  /**
   * CHANLIST command
   * @param string $target
   */
  public function chanlist($target='') { $this->queue("LIST $target"); }

  /**
   * QUIT command
   * @param string $message
   */
  public function quit($msg='buj :p')
  {
    $msg = implode(' ', func_get_args() );
    $this->queue("QUIT :$msg");
  }

  /**
   * WHO command
   * @param string $params
   */
  public function who($params) { $this->queue("WHO $params"); }

  /**
   * MODE command
   * @param string $params
   */
  public function mode($params) { $this->queue("MODE ". implode(' ',func_get_args())); }

  /**
   * OP is alias for MODE command
   * @param string $chan
   * @param string $nick
   */
  public function op($chan, $nick) { $this->queue("MODE $chan +o $nick"); }

  /**
   * DEOP is alias for MODE command
   * @param string $chan
   * @param string $nick
   */
  public function deop($chan, $nick) { $this->queue("MODE $chan -o $nick"); }

  /**
   * VO is alias for MODE command
   * @param string $chan
   * @param string $nick
   */
  public function vo($chan, $nick) { $this->queue("MODE $chan +v $nick"); }

  /**
   * DEVO is alias for MODE command
   * @param string $chan
   * @param string $nick
   */
  public function devo($chan, $nick) { $this->queue("MODE $chan -v $nick"); }

  /**
   * TOPIC command
   * @param string $channel
   * @param string $topic
   */
  public function topic($channel, $topic)
  {
    $topic = implode(' ',array_slice(func_get_args(), 1));
    $this->queue("TOPIC $channel :$topic");
  }

  /**
   * INVITE command
   * @param string $nick
   * @param string $channel
   */
  public function invite($nick, $channel) { $this->queue("INVITE $nick $channel"); }

  /**
   * KICK command
   * @param string $channel
   * @param string $nick
   * @param string $comment
   */
  public function kick($channel, $nick, $comment = 'Sorry d0g :p')
  {
    $comment = implode(' ',array_slice(func_get_args(), 3));
    $this->queue("KICK $channel $nick :$comment");
  }

  /**
   * CTCP command
   * @param string $target
   * @param string $msg
   */
  public function ctcp($target, $msg)
  {
    $msg = implode(' ',array_slice(func_get_args(), 1));
    $msg = wordwrap($msg, 430, "\n", true);
    foreach (explode("\n", $msg) as $item)
    {
      $this->msg($target, "\001$item\001");
    }
  }

  /**
   * ACTION command
   * @param string $target
   * @param string $msg
   */
  public function action($target, $msg)
  {
    $msg = implode(' ',array_slice(func_get_args(), 1));
    $this->ctcp($target,'ACTION '.$msg);
  }

  /**
   * YO command, an easter egg
   * @param string $chan
   * @param string $nick
   */
  public function yo($chan,$nick) { $this->action($chan, 'sets mode: +yo '.$nick); }

}
