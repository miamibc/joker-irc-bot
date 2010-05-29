<?php

/**
 * @package    Joker
 * @author     Miami <miami@blackcrystal.net>
 * @version    1.1 on 26/05/2010
 * @license    Released under the MIT License
 * @link       www.blackcrystal.net
 *
 * Joker initialization
 * 
 * What we have here:
 *   - Initialize bot
 *   - Change some parameters
 *   - Activate required plugins
 *   - Activate exsample plugins
 *   - Start the bot
 */


// Initialize bot

include 'joker.class.php';

$joker = new Joker();

// Change some parameters

$joker->me       = 'BC^joker';
$joker->altnicks = array( 'BC^j0k3r', 'BC^jo' ); // used by Startup plugin to set nick
$joker->autojoin = array( '#blackcrystal' );     // used by Startup plugin to join channels
$joker->admins   = array( 'BC^miami' );          // used by admin plugin
$joker->loglevel = true;                         // true level :D see all messages

// Activate required plugins

$joker->load('startup');                         // Startup sequence
$joker->load('admin');                           // Simple administration

// Activate exsample plugins

// $joker->load('HelloWorld');                   // Hello world application
// $joker->load('PrivateTalk');                  // Private talk
// $joker->load('TimerExample');                 // Timer usage example
// $joker->load('Temp');                         // Google weather

// Start the bot

$joker->connect( 'irc.quakenet.org', 6667 );