<?php

class concerto_gnmovdet extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].GN_MOVDET";
        $this->selectMap=array(
            "num_rif_movimento",
            "num_riga",
            "cod_societa",
            "cod_magazzino",
            "cod_movimento",
            "ind_tipo_riga",
            "cod_inconveniente",
            "ind_prelevato_magazzino",
            "cod_tipo_articolo",
            "cod_articolo",
            "cod_operazione",
            "cod_varie",
            "des_riga",
            "prz_prezzo_unitario",
            "qta_pezzi",
            "prc_sconto",
            "prc_sconto_add",
            "val_importo",
            "val_importo_ivato",
            "val_costo",
            "cod_iva",
            "dat_movimento",
            "des_note",
            "des_note_prima",
            "cod_ente_riparatore",
            "cod_tipo_garanzia",
            "des_tipo_garanzia",
            "des_ope_tempario",
            "qta_tempario",
            "qta_ore_prenotazione",
            "cod_reparto_inc",
            "dat_prenotazione_inc",
            "cod_operaio_inc",
            "dat_chiusura_inc",
            "ind_inc_stato",
            "ind_operazione_esterna",
            "des_fornitore_acquisto",
            "num_lt_rif_acquisto",
            "val_lt_previsto",
            "dat_lt_prevista_consegna",
            "des_note_lt",
            "dat_inserimento",
            "cod_pacchetto",
            "cod_vw_azione",
            "cod_operaio_ricambi",
            "dat_consegna_ricambi",
            "cod_operaio_ricambi_da",
            "num_off_ordinamento",
            "GAB_venatt",
            "GAB_va_flag_group",
            "GAB_va_flag_actual",
            "rif_noleggio",
            "des_psa_commande",
        );

        $this->default=array(
            "ind_inc_stato"=>"",
            "des_psa_commande"=>""
        );

        $this->checkMap=array(
        );
        
    }

    function evaluate($tipo){
    }
}

?>