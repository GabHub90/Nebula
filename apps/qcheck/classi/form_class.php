<?php
    require_once(DROOT.'/nebula/core/chekko/chekko.php');

    class qcForm extends chekko {

        protected $qcVariante="";
        protected $qcInfo=array();

        function __construct($info,$variante,$risposte) {

            parent::__construct('qc_form_'.$info['tag']);

            $this->qcInfo=$info;
            $this->qcVariante=$variante;

            //require($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/qcheck/moduli/modulo'.$this->qcVariante.'.php');

            $file=DROOT.'/nebula/apps/qcheck/moduli/'.$info['tag'].'.php';
            $separator="//#####//";
            $docTag='variante';
            $inc=mainFunc::nebulaIncludePart($file,$separator,$docTag,$this->qcVariante);
            
            try {
                $res = eval($inc);
            } catch (ParseError $e) {
                $this->log[]=$e->getMessage();
            }

            //$this->log[]=$inc;

			//#####################################################

            $this->set_closure();

            //definita da CLOSURE
            $this->qcInit();

            //if ( $r=json_decode($risposte,true) ) {

                //allinea i valori di DEFAULT
                foreach ($risposte as $k=>$v) {

                    if ( array_key_exists($k,$this->mappa) ) {

                        //se il form non è nè salvato nè aperto disabilitalo in toto
                        if ($this->qcInfo['stato']!='salvato' && $this->qcInfo['stato']!='aperto') {
                            $this->mappa[$k]['prop']['disabled']=true;
                        }

                        $this->mappa[$k]['prop']['default']=$v;
                    }
                }

                //rinnova la mappa nel builder alla luce delle modifiche
                $this->setBuilderMap($this->mappa);

                //se il form non è nè salvato nè aperto disabilita i bottoni del form
                if ($this->qcInfo['stato']!='salvato' && $this->qcInfo['stato']!='aperto') {
                    $this->setBuilderNobutton(true);
                }

            //}

            //$this->log[]=$this->mappa;

        }

        //call draw_css_base prima di tutto
        function draw_css() {
            $this->customCss();
        }

        //call draw_js_base prima di tutto
        function draw_js() {

            echo '$("#qc_form_confirm").click(function() { window._js_chk_'.$this->form_tag.'.qcChiudi();});';
            
            echo 'window._js_chk_'.$this->form_tag.'.post_check=function() {';

                echo <<<JS

                    //alert(JSON.stringify(this.actualScore));

                    var txt=""+this.actualScore.punteggio;
                    var completezza=0;

                    try {
                        completezza=Math.round( (this.actualScore.risposte/this.actualScore.domande)*100 );
                    }catch (e) {
                        completezza=0;
                    }

                    txt+='<span style="font-size:smaller;"> ( '+(isNaN(completezza)?"0":completezza)+'% )';

                    $('#js_chk_'+this.form_tag+'_head').html(txt);
JS;
            echo '};';

            echo 'window._js_chk_'.$this->form_tag.'.qcSalva=function() {';

                echo <<<JS

                    this.contesto.qcStato='salvato';
                    this.salva();
JS;
            echo '};';

            echo 'window._js_chk_'.$this->form_tag.'.qcChiudi=function() {';

                echo <<<JS

                    this.js_chk();

                    if (this.chk == 1) return;
                    
                    if ( !confirm('Confermi la chiusura del controllo?') ) return;

                    this.contesto.qcStato='chiuso';
                    this.scrivi();
JS;
            echo '};';

            echo 'window._js_chk_'.$this->form_tag.'.scrivi_proprietario=function() {';

                echo <<<JS

                    this.expo.stato=this.contesto.qcStato;
                    this.expo.punteggio=this.actualScore;

                    this.expo.esecutore=window._nebulaMain.getMainLogged();

                    //console.log(JSON.stringify(this.expo));

                    var param=this.expo;

                    $.ajax({
                        "url": 'http://'+location.host+'/nebula/apps/qcheck/core/storico_update_form.php',
                        "async": true,
                        "cache": false,
                        "data": { "param": param},
                        "type": "POST",
                        "success": function(ret) {
                            console.log(ret);
                            window._nebulaApp.ribbonExecute();
                        }
                    });
JS;  
            echo '};';


                /*
                ob_start();
                    include (DROOT.'/nebula/apps/qcheck/core/form.js');
                ob_end_flush();
                */

            $this->customJS();
        }

        function draw() {

            //echo json_encode($this->qcInfo,JSON_UNESCAPED_SLASHES);

            $this->customDraw();
            $this->draw_js_base();
        }
        

    }

?>