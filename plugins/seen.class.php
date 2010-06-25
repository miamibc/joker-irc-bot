<?php

/**
 * @package    Joker
 * @subpackage Plugins
 * @author     Miami <miami@blackcrystal.net>
 * @version    1.0 on 10/06/2010
 * @license    Released under the MIT License
 * @link       www.blackcrystal.net
 *
 * Seen plugin
 * This plugin tracks users activity on channels
 * and answers !seen command in private chat or channel.
 *
 * What we have here.
 * - Tracking of events PRIVMSG, ACTION, JOIN, QUIT, PART, NICK
 * - Sqlite database for users activity log
 * - Simple searching using LIKE sql operator
 */

class Seen {

  public function __construct(Joker $joker)
  {
    $filename =  dirname(__FILE__) . '/data/seen.db';    
    $this->db = new SQLite3($filename);

    // if no database initialized, do it
    $this->db->exec( 'CREATE TABLE IF NOT EXISTS seen (d integer , a text unique , m text) ');
    $joker->log('p', "Loaded seen database {$filename}");
  }

  public function PRIVMSG(Joker $joker)
  {
    // first we register activity
    if ($joker->chan) $this->add($joker->addr, 'talking on '. $joker->chan );

    // then, we process trigger
    if ($joker->param[0] == '!seen')
    {
      isset($joker->param[1])
           ? $joker->answer( $this->seen( $joker->param[1] ))
           : $joker->answer( '!seen Usage: !seen <nick>');
    }    
  }

  public function ACTION(Joker $joker)
  {
    if ($joker->chan) $this->add($joker->addr, 'talking on '. $joker->chan );
  }

  public function JOIN(Joker $joker)
  {
    if ($joker->nick != $joker->me) $this->add($joker->addr, 'joining '. $joker->chan );
  }

  public function MODE(Joker $joker)
  {
    if ($joker->nick != $joker->me) $this->add($joker->addr, 'changing mode on '. $joker->chan );
  }

  public function TOPIC(Joker $joker)
  {
    if ($joker->nick != $joker->me) $this->add($joker->addr, 'changing topic on '. $joker->chan );
  }

  public function QUIT(Joker $joker)
  {
    if ($joker->nick != $joker->me) $this->add($joker->addr, "leaving network with the words \"{$joker->text}\"" );
  }

  public function PART(Joker $joker)
  {
    if ($joker->nick != $joker->me) $this->add($joker->addr, "leaving {$joker->chan} with the words \"{$joker->text}\"" );
  }

  public function NICK(Joker $joker)
  {
    if ($joker->nick != $joker->me)
    {
      // renamed to
      $this->add($joker->addr, "renaming to {$joker->text}" );

      // renamed from
      $this->add($joker->text . '!' . $joker->user . '@' . $joker->host, "renaming from {$joker->nick}" );
    }
  }

  /**
   * Add activity
   * @param string $addr nick!user@host address
   * @param string $msg message to save in seen log
   */
  public function add($addr, $msg )
  {
    $msg  = $this->db->escapeString($msg);
    $addr = $this->db->escapeString($addr);
    $time = time();
    $this->db->exec( "REPLACE INTO seen (a,m,d) VALUES ('{$addr}','{$msg}', '{$time}')");
  }

  /**
   * Searching in seen database
   * @param string $addr nick, or part of nick!user@host address
   * @return string formatted answer
   */
  public function seen($addr)
  {
    $addr   = $this->db->escapeString($addr);
    $result = $this->db->query("SELECT COUNT(1) FROM seen WHERE a LIKE '%{$addr}%'");
    $count =  array_shift( $result->fetchArray() );
    $result = $this->db->query("SELECT * FROM seen WHERE a LIKE '%{$addr}%' ORDER BY d DESC LIMIT 10");

    // array for all found nicks
    $nicks = array();

    while ($row = $result->fetchArray()) {      
      if (!isset($lastnick)) {
        list($lastnick, $lastaddr) = split('!',$row['a'],2 );
        $lastmsg  = $row['m'];
        $lastdate = $this->ago($row['d']);
      }
      else {
        $nicks[] = array_shift( split('!',$row['a'],2 ) );
      }
    }

    switch ($count) {
      case 0:  // nothing found, nicks list is empty
        return "!seen: Hmmm,   nope, never seen him before..." ;
      case 1:  // one nick, good
        return "!seen: I've seen $lastnick ({$lastaddr}) $lastdate ago $lastmsg.";
      default: // many nicks
        return "!seen: {$count} matches found. Last seen $lastnick ({$lastaddr}) $lastdate ago $lastmsg. Also matched: ".implode(' ', array_unique( $nicks ));
    }

  }

  /**
   * Converts seconds to textual representation of time between current time and $param
   * @param int $tm
   * @param int $rcs
   * @return string
   * @see http://www.php.net/manual/en/function.time.php#91864
   */
  public function ago($tm,$rcs = 0) {
    $cur_tm = time(); $dif = $cur_tm-$tm;
    $pds = array('second','minute','hour','day','week','month','year','decade');
    $lngh = array(1,60,3600,86400,604800,2630880,31570560,315705600);
    for($v = sizeof($lngh)-1; ($v >= 0)&&(($no = $dif/$lngh[$v])<=1); $v--); if($v < 0) $v = 0; $_tm = $cur_tm-($dif%$lngh[$v]);
    $no = floor($no); if($no <> 1) $pds[$v] .='s'; $x=sprintf("%d %s ",$no,$pds[$v]);
    if(($rcs == 1)&&($v >= 1)&&(($cur_tm-$_tm) > 0)) $x .= time_ago($_tm);
    return trim( $x );
  }


}

