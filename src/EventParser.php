<?php
namespace Lichou\Calendar;

use DateInterval;
use DateTime;
use ErrorException;
use DateTimeImmutable;
use DateTimeZone;

require 'vendor/autoload.php';

class EventParser{
    private const REGEX_DATE = "/(Lundi|Mardi|Mercredi|Jeudi|Vendredi|Samedi|Dimanche) /u";
    private const REGEX_UEC = "/-(?<ec>(?:EC|(?:UE(?:C|T|D)))[0-9]{2,3})_/";
    private const REGEX_SALLE = "/ (?=RFC|IBGBI|PEL|AX|BX|CX|1CY|IDF|MAU|IUT)/";
    private const REGEX_GROUPE = "/ (?=(?:DSP|LAM|SFA|SHS|ST|ISTY|ISM|OVSQ)_)/";

    private array $eventData;
    private DateTime $debut;
    private DateTime $fin;
    private DateInterval $heuresCumules;
    private string $type;
    private string $enseignement;
    private array $enseignants;
    private array $groupes;
    private string $commentaire;
    private array $salles;
    private string $uec;
    private bool $effectue;
    private bool $presentiel;

    public function __construct($eventData){
        if(gettype($eventData) === "array"){
            $this->eventData = array_filter($eventData);
            $this->debut = $this->extractStartDate();
            $this->fin = $this->extractEndDate();
            $this->type = $this->extractType();
            $this->enseignement = $this->eventData["enseignement"];
            $this->enseignants = $this->extractEnseignants();
            $this->groupes = $this->extractGroupes();
            $this->commentaire = $this->eventData["commentaire"] ?? "";
            $this->salles = $this->extractSalle();
            $this->presentiel = $this->extractPresentiel();
            $this->effectue = $this->extractEffectue();
            $this->uec = $this->extractUEC();
            $this->heuresCumules = $this->extractHeuresCumules();
        } else {
            throw new ErrorException("Type de données non valide");
        }
    }

    private function extractEnseignants(): array{
        if(array_key_exists("profs", $this->eventData)){
            $tab = explode('/', $this->eventData["profs"]); 
            return array_map('trim', $tab);
        }
        return array();
    }

    public function retrieveEvent(){
        return new CalendarEvent(
            $this->debut,
            $this->fin,
            $this->type,
            $this->enseignement,
            $this->enseignants,
            $this->salles,
            $this->commentaire,
            $this->presentiel,
            $this->effectue,
            $this->uec,
            $this->heuresCumules,
            $this->groupes
        );
    }

    //private function extractEnseignants(): array{
    //    if(array_key_exists("profs", $this->eventData)){
    //        return explode(" - ", $this->eventData["profs"]);
    //    }
    //    return array();
    //}

    private function extractGroupes(): array{
        // Le découpage des groupes se fait avec le début de nom, SFA, SHS (comme pour les salles)
        // le decoupage par tiret ne fonctionne pas. Pour voir la liste des domaine de formation
        // voir : https://www.universite-paris-saclay.fr/formation/formation-en-alternance/les-formations-en-apprentissage#
        // et https://www.univ-evry.fr/fileadmin/mediatheque/ueve-institutionnel/02_Formation/Etudes_et_Scolarite/pdf/annuaire_secretariats_pedagogiques_UFR_L1-L2.pdf
        if(array_key_exists("groupes", $this->eventData)){
            return array_filter(preg_split(self::REGEX_GROUPE, $this->eventData["groupes"], 0));   

        }
        return array();
    }

    private function extractPresentiel(): bool{
        if(array_key_exists("présentiel", $this->eventData)){
            return ($this->eventData["présentiel"] === "Présentiel" || $this->eventData["présentiel"] === "Non défini");
        }
        return false;
    }

    private function extractHeuresCumules(): DateInterval{
        if(array_key_exists("cumul", $this->eventData)){
            sscanf($this->eventData["cumul"], "%dh%d", $hour, $minute);
            return new DateInterval(sprintf("PT%dH%dM", $hour, $minute));
        }
        return NULL;
    }

    private function extractEffectue(): bool{
        if(array_key_exists("effectuée", $this->eventData)){
            return ($this->eventData["effectuée"] === "✓");
        }
        return false;
    }

    private function extractStartDate(): DateTime{
        if(array_key_exists("date", $this->eventData) && array_key_exists("heure de début", $this->eventData)){
            $date = preg_replace(self::REGEX_DATE, "", $this->eventData["date"]) . " " . $this->eventData["heure de début"];
            
            return DateTime::createFromFormat('d-m-Y G\hs', $date, new DateTimeZone("Europe/Paris"))->setTimezone(new DateTimeZone("UTC"));
        }
        return NULL;
    }

    private function extractEndDate(): DateTime{
        if(!empty($this->debut) && array_key_exists("durée", $this->eventData)){
            $date = DateTimeImmutable::createFromMutable($this->debut);
            sscanf($this->eventData["durée"], "%dh%d", $hour, $minute);
            return DateTime::createFromImmutable($date->add(new DateInterval(sprintf("PT%dH%dM", $hour, $minute))));
        }
        return NULL;
    }

    private function extractUEC(): string{
        if(array_key_exists("module", $this->eventData)){
            if(preg_match(self::REGEX_UEC, $this->eventData["module"], $matches)){
                return $matches['ec'];
            }        
        }
        return "";
    }

    private function extractType(): string{
        if(array_key_exists("type", $this->eventData)){
            return ($this->eventData["type"] !== "indéfini")?$this->eventData["type"]:"";    
        }
        return "";
    }

    private function extractSalle(): array{
        //if(array_key_exists("salles", $this->eventData)){
        //    return array_filter(preg_split(self::REGEX_SALLE, $this->eventData["salles"], 0));   
        //}
        if(array_key_exists("salles", $this->eventData)){
            $tab = explode('/', $this->eventData["salles"]); 
            return array_map('trim', $tab);
        }
        return array();
    }
}

//http://plans.univ-evry.fr/veomap/getDatas.php (POST)
//http://plans.univ-evry.fr/?codeLangue=FR
//Type MIME: application/x-www-form-urlencoded; charset=UTF-8
// lang: FR
// isBorne: false
// codeBorne