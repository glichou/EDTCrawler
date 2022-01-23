<?php
namespace Lichou\Calendar;

use DateTime;
use JsonSerializable;
use DateInterval;

require 'vendor/autoload.php';

class CalendarEvent implements JsonSerializable{
    private DateTime $debut;
    private DateTime $fin;
    private string $type;
    private string $enseignement;
    private array $enseignants;
    private array $groupes;
    private string $uec;
    private array $salles;
    private bool $effectue;
    private bool $presentiel;
    private string $commentaire;
    private DateInterval $heuresCumules;

    public function __construct(DateTime $debut, DateTime $fin, string $type, string $enseignement, array $enseignants, array $salles, string $commentaire, bool $presentiel, bool $effectue, string $uec, DateInterval $heuresCumules, array $groupes){
        $this->debut = $debut;
        $this->fin = $fin;
        $this->type = $type;
        $this->enseignement = $enseignement;
        $this->enseignants = $enseignants;
        $this->groupes = $groupes;
        $this->salles = $salles;
        $this->commentaire = $commentaire;
        $this->presentiel = $presentiel;
        $this->effectue = $effectue;
        $this->uec = $uec;
        $this->heuresCumules = $heuresCumules;
    }

    public function jsonSerialize(){
        return [
            "debut" => $this->debut->format(DateTime::ISO8601),
            "fin" => $this->fin->format(DateTime::ISO8601),
            "type" => $this->type,
            "enseignement" => $this->enseignement,
            "enseignants" => $this->enseignants,
            "salles" => $this->salles,
            "groupes" => $this->groupes,
            "commentaire" => $this->commentaire,
            "effectue" => $this->effectue,
            "presentiel" => $this->presentiel,
            "uec" => $this->uec,
            "cumul" => $this->heuresCumules->format("%H:%I:%S")
        ];
    }
}