<?php
namespace Lichou\Calendar;
require 'vendor/autoload.php'; 

//use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\BrowserKit\HttpBrowser;

use InvalidArgumentException;

class CalendarCrawler{
    private const BASE_URL = "https://edt.univ-evry.fr";
    
    private string $groupName;
    private string $calendarId;
    private string $icsLink;
    private array $modulesList;
    private array $coursesList;
    private string $currentYear;
    private string $currentWeek; 

    private HttpBrowser $browser;
    private CookieJar $cookieJar;

    public function __construct(){
        $this->cookieJar = new CookieJar();
        $this->browser = new HttpBrowser(HttpClient::create(), null, $this->cookieJar);
        $this->modulesList = array();
        $this->coursesList = array();
        $date = getdate();
        $this->currentYear = ($date["mon"] > 9)?strval($date["year"]):strval($date["year"] - 1);
        $this->currentWeek = 36;
    }

    public function retrieveData(string $groupName){
        $this->groupName = $groupName;
        $this->login();
        $this->modulesList = $this->retrieveModules();
        //$this->modulesList = ["M1MIAA-FA-UEC13_RECHOP"];
        foreach($this->modulesList as $module){
            $this->coursesList = array_merge($this->coursesList, $this->retriveCoursesFromModule($module));
        }
        return $this->coursesList;
    }

    private function login(){
        // Accéder à la page d'acueil du site.
        $reponse = $this->browser->request("GET", self::BASE_URL . "/index.php");

        // Récupérer et remplir le formulaire de connexion.
        $form = $reponse->filter('form')->form();
        $form['loginstudent'] = $this->groupName;
        $reponse = $this->browser->submit($form);

        //Récuéper les informations du calendrier.
        $result = $reponse->filter('a[href$=".ics"]');
        $this->icsLink = ($result->count() > 0 && $result->getNode(0)->hasAttribute('href'))?$result->getNode(0)->getAttribute('href'):"";
        $result = $reponse->filter('input[name="current_student"]');
        $this->calendarId = ($result->count() > 0 && $result->getNode(0)->hasAttribute('value'))?$result->getNode(0)->getAttribute('value'):"";
    }

    public function retrieveModules(){
        $reponse = $this->browser->request('GET', self::BASE_URL . "/module_etudiant.php?current_week=$this->currentWeek&current_year=$this->currentYear&current_student=$this->calendarId");
        return array_filter($reponse->filter('select[name="selec_module"] option')->each(function($node, $index){
            try {
                return $node->attr('value');
            } catch(InvalidArgumentException $e){
                return NULL;
            }
        }));
    }
    
    public function retriveCoursesFromModule(string $module){
        $reponse = $this->browser->request('GET',  self::BASE_URL . "/module_etudiant.php?annee_scolaire=1&selec_module=" . urlencode($module) . "&jour=0&current_week=$this->currentWeek&current_year=$this->currentYear&current_student=$this->calendarId");
        $table = $reponse->filter('body > div:nth-child(5) > table');
        
        $entetes = array();
        return array_filter($table->filter('tr')->each(function ($rangee, $i) use (&$entetes, &$module) {
            if($i === 0){
                $entetes = $rangee->filter('th')->each(function ($colonne, $i) {
                    return trim(strtolower(preg_replace("/[^\p{L} ]+/u", "", $colonne->text())));
                });
            } else {
                $cours = array();
                $rangee->filter('td')->each(function ($colonne, $i) use (&$entetes, &$cours) {
                    $cours[$entetes[$i]] = trim($colonne->text());
                }); 
                $cours["module"] =  $module;
                return $cours;
            }
            return NULL;
        }));
    }
}