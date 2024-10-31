<?php

class nebulaUniverse {

    static $nebulaVersioni=array(
        "ammo:carb:home"=>"2.0",
        "maestro:incentivi:home"=>"2.0",
        "mytech:personale:incentivi"=>"1.0",
        "mytech:personale:home"=>"0.8",
        "isla:analisi:home"=>"0.8",
        "isla:home:home"=>"0.3",
        "sthor:lizard:home"=>"1.0",
        "vendor:grent:home"=>"0.4",
        "distro:home:home"=>"0.3",
        "sthor:home:pitstop"=>"0.3",
        "home:home:ticket"=>"0.3"
    );

    protected $galassie=array(
        "home"=>array(
            "home"=>array("tag"=>"Overview","loc"=>"/nebula/home/","icon"=>"home","chk"=>true,"chg"=>false),
            "panorama"=>array("tag"=>"Panorama","loc"=>"/nebula/home/","icon"=>"home","chk"=>true,"chg"=>false),
            "login"=>array("tag"=>"Login","loc"=>"/nebula/login/","icon"=>"nebula","chk"=>true,"chg"=>false)
        ),
        "isla"=>array(
            "home"=>array("tag"=>"iDesk","loc"=>"/nebula/galassie/isla/","icon"=>"isla","chk"=>false,"chg"=>false),
            "ibis"=>array("tag"=>"Ibis","loc"=>"/nebula/galassie/isla/","icon"=>"isla","chk"=>false,"chg"=>false),
            "gdm"=>array("tag"=>"GDM","loc"=>"/nebula/galassie/isla/","icon"=>"sthor","chk"=>false,"chg"=>false),
            "presenze"=>array("tag"=>"Presenze","loc"=>"/nebula/galassie/isla/","icon"=>"isla","chk"=>false,"chg"=>false),
            "analisi"=>array("tag"=>"Analisi","loc"=>"/nebula/galassie/isla/","icon"=>"isla","chk"=>false,"chg"=>false),
            "lizard"=>array("tag"=>"Query","loc"=>"/nebula/galassie/isla/","icon"=>"isla","chk"=>false,"chg"=>false)
        ),
        "mytech"=>array(
            "home"=>array("tag"=>"Workshop","loc"=>"/nebula/galassie/mytech/","icon"=>"mytech","chk"=>false,"chg"=>false),
            "gdm"=>array("tag"=>"GDM","loc"=>"/nebula/galassie/mytech/","icon"=>"sthor","chk"=>false,"chg"=>false),
            "presenze"=>array("tag"=>"Presenze","loc"=>"/nebula/galassie/mytech/","icon"=>"mytech","chk"=>false,"chg"=>false),
            "personale"=>array("tag"=>"Analisi","loc"=>"/nebula/galassie/mytech/","icon"=>"mytech","chk"=>false,"chg"=>false),
            "lizard"=>array("tag"=>"Query","loc"=>"/nebula/galassie/mytech/","icon"=>"isla","chk"=>false,"chg"=>false)
        ),
        "sthor"=>array(
            "home"=>array("tag"=>"Home","loc"=>"/nebula/galassie/sthor/","icon"=>"sthor","chk"=>false,"chg"=>false),
            "ibis"=>array("tag"=>"Ibis","loc"=>"/nebula/galassie/sthor/","icon"=>"sthor","chk"=>false,"chg"=>false),
            "gdm"=>array("tag"=>"GDM","loc"=>"/nebula/galassie/sthor/","icon"=>"sthor","chk"=>false,"chg"=>false),
            "presenze"=>array("tag"=>"Presenze","loc"=>"/nebula/galassie/sthor/","icon"=>"sthor","chk"=>false,"chg"=>false),
            "analisi"=>array("tag"=>"Analisi","loc"=>"/nebula/galassie/sthor/","icon"=>"sthor","chk"=>false,"chg"=>false),
            "lizard"=>array("tag"=>"Query","loc"=>"/nebula/galassie/sthor/","icon"=>"sthor","chk"=>false,"chg"=>false)
        ),
        "ammo"=>array(
            "home"=>array("tag"=>"Cruscotto","loc"=>"/nebula/galassie/ammo/","icon"=>"ammo","chk"=>false,"chg"=>false),
            "carb"=>array("tag"=>"Carburante","loc"=>"/nebula/galassie/ammo/","icon"=>"ammo","chk"=>false,"chg"=>false),
            "comest"=>array("tag"=>"Esterni","loc"=>"/nebula/galassie/ammo/","icon"=>"ammo","chk"=>false,"chg"=>false),
            "storico"=>array("tag"=>"Storico","loc"=>"/nebula/galassie/ammo/","icon"=>"ammo","chk"=>false,"chg"=>false),
            "lizard"=>array("tag"=>"Query","loc"=>"/nebula/galassie/ammo/","icon"=>"isla","chk"=>false,"chg"=>false),
            "cassa"=>array("tag"=>"Cassa","loc"=>"/nebula/galassie/ammo/","icon"=>"ammo","chk"=>false,"chg"=>false)
        ),
        "distro"=>array(
            "home"=>array("tag"=>"Consegne","loc"=>"/nebula/galassie/distro/","icon"=>"distro","chk"=>false,"chg"=>false)
        ),
        "vendor"=>array(
            "home"=>array("tag"=>"Salone","loc"=>"/nebula/galassie/vendor/","icon"=>"vendor","chk"=>false,"chg"=>false),
            "grent"=>array("tag"=>"Noleggio","loc"=>"/nebula/galassie/vendor/","icon"=>"vendor","chk"=>false,"chg"=>false),
            "lizard"=>array("tag"=>"Strumenti","loc"=>"/nebula/galassie/vendor/","icon"=>"vendor","chk"=>false,"chg"=>false)
        ),
        "maestro"=>array(
            "home"=>array("tag"=>"Tempo","loc"=>"/nebula/galassie/maestro/","icon"=>"maestro","chk"=>false,"chg"=>false),
            "gruppi"=>array("tag"=>"Gruppi","loc"=>"/nebula/galassie/maestro/","icon"=>"maestro","chk"=>false,"chg"=>false),
            "analisi"=>array("tag"=>"Analisi","loc"=>"/nebula/galassie/maestro/","icon"=>"maestro","chk"=>false,"chg"=>false),
            "incentivi"=>array("tag"=>"Incentivazione","loc"=>"/nebula/galassie/maestro/","icon"=>"maestro","chk"=>false,"chg"=>false),
            "lizard"=>array("tag"=>"Query","loc"=>"/nebula/galassie/maestro/","icon"=>"maestro","chk"=>false,"chg"=>false)
        )
    );

    /*protected $galassie=array(
        "home"=>array(
            "home"=>array("tag"=>"Overview","loc"=>"/nebula/home/","icon"=>"home","chk"=>true,"chg"=>false),
            "panorama"=>array("tag"=>"Panorama","loc"=>"/nebula/home/","icon"=>"home","chk"=>true,"chg"=>false),
            "login"=>array("tag"=>"Login","loc"=>"/nebula/login/","icon"=>"nebula","chk"=>true,"chg"=>false)
        ),
        "mytech"=>array(
            "home"=>array("tag"=>"Workshop","loc"=>"/nebula/galassie/mytech/","icon"=>"mytech","chk"=>false,"chg"=>false),
            "presenze"=>array("tag"=>"Presenze","loc"=>"/nebula/galassie/mytech/","icon"=>"mytech","chk"=>false,"chg"=>false),
            "personale"=>array("tag"=>"Analisi","loc"=>"/nebula/galassie/mytech/","icon"=>"mytech","chk"=>false,"chg"=>false)
        ),
        "ammo"=>array(
            "home"=>array("tag"=>"Cruscotto","loc"=>"/nebula/galassie/ammo/","icon"=>"ammo","chk"=>false,"chg"=>false),
            "carb"=>array("tag"=>"Carburante","loc"=>"/nebula/galassie/ammo/","icon"=>"ammo","chk"=>false,"chg"=>false),
            "comest"=>array("tag"=>"Esterni","loc"=>"/nebula/galassie/ammo/","icon"=>"ammo","chk"=>false,"chg"=>false),
            "cassa"=>array("tag"=>"Cassa","loc"=>"/nebula/galassie/ammo/","icon"=>"ammo","chk"=>false,"chg"=>false)
        ),
        "maestro"=>array(
            "home"=>array("tag"=>"Tempo","loc"=>"/nebula/galassie/maestro/","icon"=>"maestro","chk"=>false,"chg"=>false),
            "gruppi"=>array("tag"=>"Gruppi","loc"=>"/nebula/galassie/maestro/","icon"=>"maestro","chk"=>false,"chg"=>false),
            "analisi"=>array("tag"=>"Analisi","loc"=>"/nebula/galassie/maestro/","icon"=>"maestro","chk"=>false,"chg"=>false),
            "incentivi"=>array("tag"=>"Incentivazione","loc"=>"/nebula/galassie/maestro/","icon"=>"maestro","chk"=>false,"chg"=>false),
            "lizard"=>array("tag"=>"Query","loc"=>"/nebula/galassie/maestro/","icon"=>"maestro","chk"=>false,"chg"=>false)
        )
    );*/

    //elenco di tutte le funzioni possibili
    //------------------
    //le funzioni possono essere "menu" o "inline"
    //per specificare funzioni che vengono elencate a menu o sono lanciate da azioni nella pagina

    //Esempio:
    // isla => avalon => { "tag":"avalon" , "tipo":"menu" , "chk":true , "loc":"..." }
    // la funzione home è di default {"home" , --- , true}

    //////////////////////////////////////////////
    //eliminato 07.09.2021
    //mytech - home - "inarrivo"=>array("menuTag"=>"In arrivo" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/workset/"),
    //////////////////////////////////////////////

    protected $sistemi=array(
        "home"=>array(
            "home"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/overview/"),
                "ticket"=>array("menuTag"=>"Tickets" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/ticket/")
            ),
            "panorama"=>array(
                "home"=>array("menuTag"=>"Panorama" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/tempo/panorama/")
            ),
            "login"=>array()
        ),
        "isla"=>array(
            "home"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/idesk/"),
                "avalon"=>array("menuTag"=>"Agenda" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/avalon/"),
                "storico"=>array("menuTag"=>"Storico" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/storico/"),
                "voucher"=>array("menuTag"=>"Voucher" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/lizard/voucher/"),
                "circa"=>array("menuTag"=>"Preventivi" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/circa/")
            ),
            "ibis"=>array(
                "home"=>array("menuTag"=>"home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/ibis/"),
                "gabsms"=>array("menuTag"=>"sms" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/gabsms/")
            ),
            "gdm"=>array(
                "home"=>array("menuTag"=>"Admin" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/gdm/"),
                "gdmr"=>array("menuTag"=>"Officina" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/gdm/"),
                "gdmp"=>array("menuTag"=>"Prelievo" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/gdm/"),
                "gdms"=>array("menuTag"=>"Stoccaggio" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/gdm/"),
                "gdmq"=>array("menuTag"=>"Query" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/lizard/gdm/")
            ),
            "presenze"=>array(
                "home"=>array("menuTag"=>"Presenza" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/workset_presenza/")
            ),
            "analisi"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/c2r/"),
                "incentivi"=>array("menuTag"=>"Incentivi" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/workset_incentive/")
            ),
            "lizard"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/lizard/idesk/")
            )
        ),
        "mytech"=>array(
            "home"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/workshop/"),
                "avalon"=>array("menuTag"=>"Agenda" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/avalon/"),
                "storico"=>array("menuTag"=>"Storico" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/storico/"),
                "provo"=>array("menuTag"=>"Provo" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/qcheck/"),
            ),
            "gdm"=>array(
                "gdmr"=>array("menuTag"=>"Officina" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/gdm/")
            ),
            "presenze"=>array(
                "home"=>array("menuTag"=>"Presenza" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/workset_presenza/")
            ),
            "personale"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/personale/mytech/"),
                "incentivi"=>array("menuTag"=>"Incentivi" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/workset_incentive/")
            ),
            "lizard"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/lizard/workshop/")
            )
        ),
        "ammo"=>array(
            "home"=>array(
                "home"=>array("menuTag"=>"home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/ibis_ammo/"),
                "panorama"=>array("menuTag"=>"Presenze" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/tempo/panorama/")
            ),
            "carb"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/carb/"),
                "lizard"=>array("menuTag"=>"Archivio" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/lizard/carb/")
            ),
            "comest"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/comest/home/"),
                "comestSalvate"=>array("menuTag"=>"Salvate" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/comest/salvate/"),
                "comestAperte"=>array("menuTag"=>"Aperte" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/comest/aperte/"),
                "comestArchivio"=>array("menuTag"=>"Archivio" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/comest/archivio/")
            ),
            "storico"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/storico/"),
            ),
            "lizard"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/lizard/ammo/")
            ),
            "cassa"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/cassa/"),
            )
        ),
        "distro"=>array(
            "home"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/timeless/"),
                "ermes"=>array("menuTag"=>"Tickets" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/ticket_distro/")
            )
        ),
        "sthor"=>array(
            "home"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/sthor/"),
                "imag"=>array("menuTag"=>"iMag" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/imag/"),
                "ermes"=>array("menuTag"=>"Tickets" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/ticket_sthor/")
            ),
            "ibis"=>array(
                "home"=>array("menuTag"=>"home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/ibis/")
            ),
            "imag"=>array(
                "home"=>array("menuTag"=>"iMag" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/imag/")
            ),
            "gdm"=>array(
                "home"=>array("menuTag"=>"Admin" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/gdm/"),
                "gdmr"=>array("menuTag"=>"Officina" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/gdm/"),
                "gdmp"=>array("menuTag"=>"Prelievo" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/gdm/"),
                "gdms"=>array("menuTag"=>"Stoccaggio" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/gdm/"),
                "gdmq"=>array("menuTag"=>"Query" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/lizard/gdm/")
            ),
            "presenze"=>array(
                "home"=>array("menuTag"=>"Presenza" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/sthor_presenza/")
            ),
            "analisi"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/c2r/")
            ),
            "lizard"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/lizard/magazzino/")
            )
        ),
        "vendor"=>array(
            "home"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/vendor/")
            ),
            "grent"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/grent/")
            ),
            "lizard"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/lizard/leads/")
            )
        ),
        "maestro"=>array(
            "home"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/tempo/"),
                "formazione"=>array("menuTag"=>"Formazione" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>""),
                "brogliaccio"=>array("menuTag"=>"Brogliaccio" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/brogliaccio/"),
                "panorama"=>array("menuTag"=>"Panorama" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/tempo/panorama/")
            ),
            "gruppi"=>array(
                "home"=>array("menuTag"=>"Gruppi" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/ensamble/"),
                "schemi"=>array("menuTag"=>"Schemi" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/quartet/"),
                "passi"=>array("menuTag"=>"Passi" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"")
            ),
            "analisi"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>""),
                "phone"=>array("menuTag"=>"Phone" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/ananexi/")
            ),
            "incentivi"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/centavos/")
            ),
            "lizard"=>array(
                "home"=>array("menuTag"=>"Home" , "tipo"=>"menu" , "chk"=>true, "chg"=>false, "loc"=>"/nebula/funzioni/lizard/maestro/")
            )
        )
    );

    //mappa delle locazioni delle applicazioni
    //le applicazioni sono legate ad una funzione e potrebbero essere spostate nel tempo
    //rendendoi difficoltosa l'operazione di LINK da una galassia esterna alla funzione stessa
    //L'APPLICAZIONE PUÒ ANCHE ESISTERE IN DIVERSE POSIZIONI IN DIVERSE GALASSIE
    //BETA 23.04.2021
    protected $starMap=array(
        "qcheck"=>array(
            "mytech"=>"home:provo"
        )
    );

    function getGalassie() {
        return $this->galassie;
    }

    function getSistemi() {
        return $this->sistemi;
    }

    function getStarmap($app) {
        if( array_key_exists($app,$this->starMap) ) {
            return $this->starMap[$app];
        }
        else return false;
    }

}

?>