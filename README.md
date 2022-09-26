# EDTCrawler

EDTCrawler est un test de scrapping du site [edt.univ-evry.fr](https://edt.univ-evry.fr) ayant pour but de récupérer la liste des cours pour une classe donné et les retourner au format JSON. 

## Exemple

Pour le moment, la seule fonctionnalité de ce script est de lister l'ensemble des cours d'une classe
```
[
   {
      "debut":"2021-09-23T08:00:15+0000",
      "fin":"2021-09-23T09:30:15+0000",
      "type":"CM",
      "enseignement":"RECHOP",
      "enseignants":[
         "Prénom NOM"
      ],
      "salles":[
         "IBGBI-1-101",
         "IBGBI-1-103"
      ],
      "groupes":[
         "SFA_M1 MIAGE CFA"
      ],
      "commentaire":"",
      "effectue":true,
      "presentiel":true,
      "uec":"UEC13",
      "cumul":"15:00:00"
   }
]
```
## But
Le but de ce projet est de fournir une mini API réutilisable avec la liste des cours. Les données pourront être utilisé pour réserver les plats à l'avance, définir un statut de présence sur Slack ou programmer automatiquement l'heure d'un réveil en fonction du traffic. 

## À faire
* Ajouter des pauses entre chaque chargement de page (pour ne pas surcharger le serveur)
* Mettre en place un système de cache pour ne pas répercuter tous le traffic sur le serveur de l'université.
* Détecter lorsqu'il y a plusieurs enseignant pour un cours.
* Consolider les données des salles en tirant partie des informations disponibles sur [plans.univ-evry.fr](http://plans.univ-evry.fr) (lattitude, longitude, parking, nom du bâtiment, adresse)
* Vérifier si le crawler n'a pas été déconnecté et s'il est bien connecté car le code HTTP est toujours 200 (vérififier présence ou durée expiration cookie?, contenu de la page?, entête particulière?) 
* Récupérer dynamiquement les identifiants des bâtiments sur la page d'accueil du site pour faire le découpage des salles (découpage si espace pas suffisant, car certaines salles contiennent des espaces, ex: `1CY-AMPHI AUDIO`)
* Nettoyer/documenter/tester le code
* Consolider les données avec le fichier ICS (information moins détaillé mais présence des événements autres que les cours)
* Consolider les données avec une base de données privée contenant les coordonnées de contact de l'enseignant (ou a défaut le mail universitaire), un joli nom de la matière (capitalisé, avec accents et apostrophe), coéficient de la matière (UEC), lien vers les informations officielles.
* Consolider les données avec eCampus (ajouter un lien si la matière existe dans eCampus, le lien de visio)
* Consolider les données avec l'adresse de groupe de la classe (pour les liens des visio, en recherchant certains type de lien)
* Mettre en place une API.