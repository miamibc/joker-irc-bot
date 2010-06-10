<?php

/**
 * @package    Joker
 * @subpackage Plugins
 * @author     Miami <miami@blackcrystal.net>
 * @version    1.1 on 03/06/2010
 * @license    Released under the MIT License
 * @link       www.blackcrystal.net
 * 
 * Temp plugin
 *
 * Uses Google Weather API to get current temperature and weather
 * @see http://www.google.com/ig/api?hl=ru&weather=tallinn
 *
 * What we have here:
 *   - Plugin accepts channel and private messages
 *   - Caching of the results
 */
class Temp {

  public $cache = array('moon'=>"!temp(Moon): It's hot here, cheese is melted, whole... :p ");

  public function PRIVMSG(Joker $joker)
  {

    $hash = $joker->param;
    $cmd  = array_shift($hash);

    if ($cmd != '!temp') return;

    if (!count($hash)) $hash[] = 'Tallinn';

    $cityraw = urlencode( strtolower(@implode(' ',$hash)));

    if (isset ($this->cache[$cityraw]))
    {
      $text = $this->cache[$cityraw];
    }

    else
    {

    $url = 'http://www.google.com/ig/api?hl=en&weather=' . $cityraw ;
    $joker->log('p',"Getting $url");
    $file = file_get_contents($url);


    $cache     = preg_match('@<postal_code data="(.*)"/>@Ui', $file, $matches) ? $matches[1] : '';
    $city      = preg_match('@<city data="(.*)"/>@Ui', $file, $matches) ? $matches[1] : '';
    $condition = preg_match('@<condition data="(.*)"/>@Ui', $file, $matches) ? $matches[1] : '';
    $temp      = preg_match('@<temp_c data="(.*)"/>@Ui', $file, $matches) ? $matches[1] : '';
    $humidity  = preg_match('@<humidity data="(.*)"/>@Ui', $file, $matches) ? $matches[1] : '';
    $wind      = preg_match('@<wind_condition data="(.*)"/>@Ui', $file, $matches) ? $matches[1] : '';

    $text = $city ? "!temp({$city}): {$temp}'C, {$condition}, {$humidity}, {$wind}"
                  : '!temp: No such place :p';
    
    $this->cache[$cityraw] = $text;
    
    }

    $joker->answer( $text );

  }

}