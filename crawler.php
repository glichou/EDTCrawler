<?php
require 'vendor/autoload.php';
use Lichou\Calendar\CalendarCrawler;
use Lichou\Calendar\EventParser;

$crawler = new CalendarCrawler();
$liste = $crawler->retrieveData("M1MIAA");

$events = array();
foreach($liste as $element){
    $parser = new EventParser($element);
    $events[] = $parser->retrieveEvent();
}
echo(json_encode($events));