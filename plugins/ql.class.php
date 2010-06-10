<?php

/**
 * @package    Joker
 * @subpackage Plugins
 * @author     Miami <miami@blackcrystal.net>
 * @version    1.0 on 03/06/2010
 * @license    Released under the MIT License
 * @link       www.blackcrystal.net
 * 
 * Quake Live player stats plugin
 *
 * Retrieves player statistics from Quakelive server
 * @see http://www.quakelive.com/profile/summary/bc_miami
 *
 * What we have here:
 *   - Plugin accepts channel and private messages
 *   - Usage !ql nick [option]   - where option can be summary (default), or friends
 */
class Ql {

  public function PRIVMSG(Joker $joker)
  {

    $hash = $joker->param;
    
    @list($cmd,$nick,$opt)  = $joker->param; // three parameters accepted

    if ($cmd != '!ql') return; // first must be !ql

    // if no nickname, answer with help message
    if (!$nick) return $joker->answer('!ql: Usage !ql nick [option]. Allowed options: summary (default), friends.');
    // if no option
    if (!$opt) $opt = 'summary';

    $url = "http://www.quakelive.com/profile/$opt/$nick";
    $joker->log('p',"Getting $url");
    $file = file_get_contents($url);
    $file = preg_replace('@\s+@',' ', $file ); // remove spaces and newlines
    $stats = new stdClass(); // one object, to easy debug all parsed infos

    // name is always present here
    $stats->name      = preg_match('@id="prf_player_name">(.+)</div>@Ui', $file, $matches) ? $matches[1] : '';

    switch (strtolower($opt)):

    case 'summary':

      $stats->lastgame  = preg_match('@<b>Last Game:</b> (.+) <br />@Ui', $file, $matches) ? trim(strip_tags($matches[1])) : '';
      $stats->played    = preg_match('@<b>Time Played:</b> <span title=".*">(.+)</span>@Ui', $file, $matches) ? $matches[1] : '';
      $stats->wins      = preg_match('@<b>Wins:</b> ([\d,]+)<br />@Ui', $file, $matches) ? $matches[1] : '';
      list(,$stats->losses,$stats->quits) = preg_match('@<b>Losses / Quits:</b> ([\d,]+) / ([\d,]+)<br />@Ui', $file, $matches) ? $matches : array('','','');
      list(,$stats->frags,$stats->deaths) = preg_match('@<b>Frags / Deaths:</b> ([\d,]+) / ([\d,]+)<br />@Ui', $file, $matches) ? $matches : array('','','');
      $stats->accuracy  = preg_match('@<b>Accuracy:</b> (.+)<br />@Ui', $file, $matches) ? $matches[1] : 'n/a';
      // now we get only section with last match played...
      $file             = preg_match('@class="\w+ recent_match \w+"(.+)<span class="played">@Ui', $file, $matches) ? $matches[1] : '';
      $stats->lastmap   = preg_match('@/levelshots/lg/(.{1,20})_v2010@Ui', $file, $matches) ? ucfirst($matches[1] ) : '';
      $stats->finish    = preg_match('@<span class="finish">(.+)</span>@Ui', $file, $matches) ? strtolower(trim( $matches[1]) ) : '';
      $text = $stats->played ? "!ql({$stats->name}): ".
                              "Wins/losses/quits: {$stats->wins}/{$stats->losses}/{$stats->quits}, ".
                              "frags/deaths: {$stats->frags}/{$stats->deaths}, accuracy: {$stats->accuracy}. ".
                              "Total time played {$stats->played}. ".
                              "Last game was {$stats->lastgame}".
                              ( $stats->lastmap && $stats->finish ? " on {$stats->lastmap}, {$stats->finish}" : "" ) .
                              "."
                           : '!ql: No such player :p';
      break;
    
    case 'friends':

      $stats->list      = preg_match_all('@<div class="player_name"> .+/flags/([a-z]{2})_.+ /> <a .+>(.+)</a>.+</div>@Ui', $file, $matches, PREG_PATTERN_ORDER) ? array_combine($matches[2], $matches[1]) : array();
      $stats->result = array();
      foreach ($stats->list as $name=>$country)
      {
        $name = strip_tags ( strtr($name, array('</span>'=>' '))) ; // changing <span class="clan">just</span>x1t] => just xlt
        $stats->result[] = "$name ($country)";
      }
      $result = count($stats->result) ? 'Friends: '. implode(', ', $stats->result) .'.' : 'Has no friends.';
      $text = "!ql({$stats->name}) $result";
      break;

    default:

      $text = '!ql: This option is not implemented yet';
      break;
    
    endswitch;

    // print_r($stats);
    $joker->answer($text);

    return Joker::STOP; // stop processing other plugins

  }

}