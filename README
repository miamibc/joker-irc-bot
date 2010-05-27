Joker the IRC bot 
=================

Joker the IRC bot - is a class, designed for quick and easy IRC bot constructing.
Base class contains methods, that can be executed from your plugins. It has
built-in flood protection, timers and powerful plugin system, that allows you to
reload plugin classes on-fly, without restarting PHP console or disconnecting
from network.

Installation
------------

* Extract archive into your home folder

	  $ tar xzvf joker.tar.gz
	
* Edit joker.php. Uncomment plugins you want to be activated on start

    $joker->load('HelloWorld');   // Hello world application
    $joker->load('PrivateTalk');  // Private talk
    $joker->load('TimerExample'); // Timer usage example
    $joker->load('Temp');         // Google weather

* Change server address where to connect
    
    $joker->connect('irc.quakenet.org',6667);
    
* Start the bot in console

    $ php -f joker.php
        

Customizing
-----------

I made this class to be maximum interesting for developers, like I am, that's why
all processes are controlled by plugins. Now your bot is connected to IRC, it sends
CONNECTED event to all the plugins that accepts it, and Startup plugin continues
process. It performs all routines, needed to be properly connected to Quakenet,
maybe other networks too. It joins channels and responds every server PING message.
If you want to connect some other network, firs look in this plugin and debuging
information from console. 

Plugins
-------

I'm including some sample code, plugins that you may use as tutorial to deal with bot api. Write your own plugins and send them to me <miami at blackcrystal dot net> to wonder IRC world by your knowledge, to share good ideas and interesting php code.

At the moment we have plugin examples:

 * Startup plugin
   
  This plugin performs startup sequence to enter QuakeNet (maybe other NET's too;) so, without it your bot will only stay connected to server for some time.

  * Plugin accepts CONNECTED signal, set first NICK from nicklist, send USER and PASS commands
  * If nick is already used, set another NICK from nicklist
  * Skip MOTD and joins channels
  * Reply to server PING requests
   
* Simple administration plugin.

  * Plugin accepts only private messages from admin(s)
  * You can control bot using standart IRC commands: 'join', 'part', 'nick', 'quit', 'topic', 'invite', 'action', 'yo', 'msg', 'notice', 'op', 'deop', 'vo', 'devo', 'kick'
  * You can list, add or remove plugins using 'list', 'load', 'unload' commands
  
* HelloWorld plugin

  This example plugin says "Hello, #channelname" when bot joins any channel. After the first Hello it removes himself from plugins and don't recieve events anymore.

  * Plugin listens to JOIN event
  * Plugin send MSG if bot joins channel
  * Plugin removes himself from plugins

* Private Talk

  This is example plugin that uses internal variable to hold some
  user information. This plugin accepts only private chat and stores
  last time and a message, then output this back to user on next requests.

  Warning! Avoid collecting of lots information inside the memory,
  cuz, normally PHP limits it. If you want more data stored
  use database or external files. This example is only the example... ;)

  * On first private MSG if shows "Hi, nick" and stores its time/message in array
  * If info exists, reply with last time and a message

* Temp plugin

  Uses Google Weather API to get current temperature and weather as XML, example
  http://www.google.com/ig/api?hl=ru&weather=tallinn

  * Plugin accepts channel and private messages
  * Caching of the results


* TimerExample plugin

  This plugin demonstrates using of timer.

  * Plugin waits to join channel
  * Plugin drops message every 10 seconds until you stop him ('unload Temp'
    in private chat to do this)

Changelog
---------

* 1.0 Initial release
* 1.1 Added timers, plugin loading and unloading, added more example plugins



About the author
----------------

Sergei Miami - php developer with ~10 years of development experience.
Using Symfony, Codeigniter, jQuery and jQueryUI frameworks in my projects.
Sometimes playing with crystal-clear PHP.

I'm working on a web-based systems creation - custom CRM development, intranet
services, search engines (incl. bots, parsers, and data processing),
integrating smart technologies in production processes of any kind. There is
a lab on my company website, where im tryng to create simple usable inventions,
such as project you see right now. Go there, to find something else for your needs.

@see http://www.blackcrystal.net/lab for more lab projects
@see http://www.blackcrystal.net for more about BlackCrystal Ltd.

Copyright (c) 2010 Sergei Miami «Show what you can. Learn what you don't.»

