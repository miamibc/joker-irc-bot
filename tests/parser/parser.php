<?php

/**
 * @package    Joker
 * @subpackage Tests
 * @author     Miami <miami@blackcrystal.net>
 * @version    1.0 on 02/04/2009
 * @license    Released under the MIT License
 * @link       www.blackcrystal.net
 * 
 * Parser for IRC-server RAW commands
 *
 * What we have here:
 *   - My task was to madke better parser for RAW commands
 *   - To made it shorter and clean to understand
 *   - I made it really short, but hard to understand
 */

$file = file("strings.txt");

$preg = '^(NOTICE AUTH|PING)?' .   //notice auth or ping command are some awful shit, 
                                   //dunno why they have another syntax then other commands 
                                   //anyways, if these commands fired, subpattern will not fire
        '(?:^:' .                  //start of subpattern. It starts if this is beginning of line and :
          '((.*)!(.*)@(.*)|.*) ' . //address, nick, user, host, or just address (if this is server message)
          '(\d* |\S* )' .          //event number or name
          '([^:]*)' .              //all until :
        ')?' .                     //end of subpattern
        '(?: :(.*?))?$';           //this fires for NOTICE AUTH and PING too. 
                                   //Getting all the text, removing ' :' from start.
                                   //06 may 2009 02:05am Sergei Miami <miami@blackcrystal.net>
                                   // deep night, but I made it in one line, hooray!

print $preg."\n\n";

foreach ($file as $string)
{
  print $string;
  if (preg_match('_'.$preg.'_Ui',$string,$matches)) {
    print_r($matches);
  }
}


/*
Here is the sample result of parsing RAW by this regexp:

[0] => full RAW string             :BC^joker!joker@85.14.235.80.sta.estpak.ee MODE #blackcrystal +l 100500
[1] => NOTICE AUTH or PING command       
[2] => full address or server      BC^joker!joker@85.14.235.80.sta.estpak.ee 
[3] => Nick                        BC^joker                    
[4] => Username                    joker
[5] => Hostname                    85.14.235.80.sta.estpak.ee
[6] => Event (number or string)    MODE 
[7] => Event params                #blackcrystal +l 100500
[8] => Text

 @see also tests/parser/strings.txt and tests/parser/results.txt
 */