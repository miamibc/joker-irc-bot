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

include 'joker.class.php';

// Initialize bot

$joker = new Joker();

// Change some parameters

$joker->loglevel = true; //true level :D to see all types of messages

// Activate required plugins

$joker->load('Startup');         // Startup sequence
$joker->load('Admin');           // Simple administration

// Activate exsample plugins

// $joker->load('HelloWorld');   // Hello world application
// $joker->load('PrivateTalk');  // Private talk
// $joker->load('TimerExample'); // Timer usage example
// $joker->load('Temp');         // Google weather

// Start the bot

$joker->connect('irc.quakenet.org',6667);