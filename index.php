<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Joker the bot / BlackCrystal Labs</title>
<meta name="Description" content="Joker the IRC bot on PHP">
<meta name="Keywords" content="Joker BC^joker IRC bot php class classes">
<meta name="Author" content="BlackCrystal Developers Team">
<link rel="stylesheet" type="text/css" href="/labs/main.css" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>

<div id="bclabs"><a href="/labs/" title="Labs home"><img src="/labs/bclabs<?php echo rand(0,1)?>.jpg" alt="BlackCrystal Labs | Show what You can. Learn what You don't."></a></div>

<div id="contents">

<h1>Joker the IRC bot</h1>

<h3>Description / Описание</h3>

<p>Людям, знакомым с тусовкой в сети QuakeNet на канале #blackcrystal и нескольких других 
русскоговорящих каналах, известен такой бот как BC^joker. Почти 10 лет назад я написал его 
на скриптовом языке mIRCScript и в течении этого времени усовершенствовал и дополнял новыми 
возможностями. Бот умеет "шутить" цитатами с каналов, операторы имеют возможность пополнять 
базу шуток, бот умеет играть в забавные игры, время от времени проявляет свой характер, 
в общем существует на канале как полноценная личность.</p>

<p>Сейчас его умения уже не новинка и возникла идея "пересадить" его с неповоротливого mIRCScript 
на нечто более совершенное. Прежде всего для меня был интересен язык PHP, объектно-ориентированные 
его возможности. Мне удалось создать систему оповещения объектов, подключенных к основному - 
по сути транспортному. Вся логика и поведение бота благодаря этому находится отдельно, в классах 
которые осуществляют "прослушивание" и выполняют свой код только при наличии определённых сигналов, 
наподобие как это было сделано в mIRC. Набор классов бота и несколько примеров написания 
плагинов я выложил на сайте phpclasses.org, буду рад если кому-нибудь это окажется полезным.</p>

<h3>Todo / Планируется</h3>
<p>В будущем планируется сделать более совершенный парсер RAW-команд древнего протокола IRC, 
который на сегодняшний день представляет достаточно большой набор Regexp'ов. Уверен что можно 
будет сократить его как минимум вдвое. Также будет решена основная проблема PHP - внедрение 
нового кода без рестарта бота. Плагины можно будет переподключать. Также будет создан пример 
плагина, работающий с базой данных для того чтобы набор примеров имел более законченный вид.</p>

<h3>Links / Ссылки</h3>
<p>
<a href="http://www.phpclasses.org/browse/package/5369.html">Joker the IRC bot on phpclasses.org</a><br>
<a href="http://blackcrystal.net/svn/joker/">SVN repository (private access)</a><br>
</p>

<h3>License information</h3>

<p>PHP version of Joker has been released under the MIT, BSD, and GPL Licenses.<br>
mIRCScript version of Joker has been released under mIRC license.<br>
Some rights reserved.<br>
Author: Sergei Miami &lt;miami at blackcrystal dot net&gt;<br>
<a href="http://www.blackcrystal.net">http://www.blackcrystal.net</a></p>

</div>

</body>
</html>