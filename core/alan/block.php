<?php

    class alanBlock {

        protected $prefix="";

        protected $info=array(
            "E"=>false,
            "U"=>false,
            "stato"=>"KO",
            "minuti"=>0,
            "tag"=>"",
            "IDcoll"=>"",
            "forza"=>-1,
            "forzato"=>false,
            "corretto"=>false,
            "arrotondamento"=>0
        );

        function __construct($prefix,$arrotondamento) {
            $this->prefix=$prefix;
            $this->info['arrotondamento']=$arrotondamento;
        }

        function getStato() {
            return $this->info['stato'];
        }
    
        function checkU() {
            if (!$this->info['U']) return false;
            else return true;
        }
    
        function add($arr,$IDcoll) {
            if ($arr['VERSOO']=='E') $this->info['E']=$arr;
            if ($arr['VERSOO']=='U') $this->info['U']=$arr;
            
            if ($this->info['tag']=="") {
                $this->info['tag']=$arr['d'];
            }
            $this->info['IDcoll']=$IDcoll;
    
            $this->evaluate();

            $res=$this->info['minuti'];

            if ($this->info['forza']!=-1) {
                if ($this->info['forzato']) $res=0;
                else {
                    $res=$this->info['forza'];
                    $this->info['forzato']=true;
                }
            }
    
            return $res;
        }
    
        function evaluate() {

             //setta FORZA
             if ($this->info['E']) {
                if ($this->info['E']['forza_minuti']!=-1) {
                    $this->info['forza']=$this->info['E']['forza_minuti'];
                }
            }
            if ($this->info['U']) {
                if ($this->info['U']['forza_minuti']!=-1) {
                    $this->info['forza']=$this->info['U']['forza_minuti'];
                }
            }
            ////////////////////////////////
    
            if (!$this->info['E'] || !$this->info['U']) {

                if ($this->info['forza']==-1) {
                    $this->info['minuti']=0;
                    $this->info['stato']='KO';
                }
                else {
                    $this->info['minuti']=$this->info['forza'];
                    $this->info['stato']='OK';
                }
            
                return;
            }
    
            if ($this->info['U']['actualM']<$this->info['E']['actualM']) {

                if ($this->info['forza']==-1) {
                    $this->info['stato']='ERR';
                    $this->info['minuti']=0;
                    return;
                }
            }
            
            //if ($this->info['forza']!=-1) $this->info['minuti']=$this->info['forza'];
            $this->info['minuti']=$this->arrotondaBlocco($this->info['U']['actualM']-$this->info['E']['actualM']);
            $this->info['stato']='OK';

            //se "minuti" è maggiore di "intervalloMax" allora forza a ( "minuti" - "pausaMin" )
            if ($this->info['minuti']>$this->info['E']['intervalloMax']) {
                $this->info['minuti']=$this->arrotondaBlocco($this->info['minuti']-$this->info['E']['pausaMin']);
                $this->info['corretto']=true;
            }

        }

        function arrotondaBlocco($valore) {

            //ore piene
            $h=floor($valore/60);
            //resto in minuti
            $m=$valore-($h*60);

            //numero di intervalli rif contenuti nel resto
            $res=($this->info['arrotondamento']>0)?floor($m/$this->info['arrotondamento']):1;

            $m=$res*$this->info['arrotondamento'];

            return ($h*60)+$m;
        }

        function draw($delta) {

            $ids="";

            $txt='<div style="width:100%;height:18px;';
                if ($this->info['stato']=='KO' || $this->info['stato']=='ERR') $txt.='color:red;';
                else $txt.='color:black;';
            $txt.='">';

                $tt="";
                if ($this->info['E']) {
                    $tt.='<span style="vertical-align:top;">'.$this->info['E']['h'];
                        if ($this->info['E']['actualH']!="") $tt.=' ('.$this->info['E']['actualH'].')';
                    $tt.='</span>';
                    if ($this->info['stato']=='KO' || $this->info['stato']=='ERR') {
                        $tt.='<img style="position:relative;width:16px;height:16px;border: 1px solid trasparent;margin-left:3px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/alan/img/swap.png" onclick="window._'.$this->prefix.'_alan.swapVerso(\''.$this->info['E']['IDTIMBRATURA'].'\',\''.$this->info['tag'].'\',\''.$this->info['IDcoll'].'\');" />';
                    }

                    $ids.="'".$this->info['E']['IDTIMBRATURA']."',";
                }
                else $ids.="'',";

                $txt.='<div style="display:inline-block;width:25%;text-align:center;vertical-align:top;">'.$tt.'</div>';

                $tt="";
                if ($this->info['U']) {
                    $tt='<span style="vertical-align:top;">'.$this->info['U']['h'];
                        if ($this->info['U']['actualH']!="") $tt.=' ('.$this->info['U']['actualH'].')';
                    $tt.='</span>';
                    if ($this->info['stato']=='KO' || $this->info['stato']=='ERR') {
                        $tt.='<img style="position:relative;width:16px;height:16px;border: 1px solid transparent;margin-left:3px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/alan/img/swap.png" onclick="window._'.$this->prefix.'_alan.swapVerso(\''.$this->info['U']['IDTIMBRATURA'].'\',\''.$this->info['tag'].'\',\''.$this->info['IDcoll'].'\');" />';
                    }

                    $ids.="'".$this->info['U']['IDTIMBRATURA']."',";
                }
                else $ids.="'',";

                $txt.='<div style="display:inline-block;width:25%;text-align:center;vertical-align:top;">'.$tt.'</div>';

                $txt.='<div style="display:inline-block;width:15%;text-align:center;vertical-align:top;">';
                    if($this->info['stato']=='OK') {
                        if ($this->info['corretto']) {
                            $txt.='('.number_format($this->info['minuti']/60,2,'.','').')';
                        }
                        else {
                            $txt.=number_format($this->info['minuti']/60,2,'.','');
                        }
                    }
                $txt.='</div>';

                $txt.='<div style="display:inline-block;width:15%;text-align:center;vertical-align:top;">';
                    if ($this->info['forza']==-1) {
                        //se il blocco è in errore o c'è discrepanza tra calcolato e previsto
                        if ($this->info['stato']=='KO' || $delta!=0) {
                            $txt.='<img style="width:16px;height:24px;border: 1px solid transparent;transform: translate(0px,-5px) rotate(90deg);cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/alan/img/forza.png" onclick="window._'.$this->prefix.'_alan.forza('.substr($ids,0,-1).',\''.$this->info['tag'].'\',\''.$this->info['IDcoll'].'\');" />';
                        }
                    }
                    else {
                        $txt.='<img style="position:relative;width:15px;height:15px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/alan/img/escludiON.png" onclick="window._'.$this->prefix.'_alan.annullaForza('.substr($ids,0,-1).',\''.$this->info['tag'].'\',\''.$this->info['IDcoll'].'\');"/>';
                        $txt.='<span style="margin-left:3px;color:red;vertical-align:top;">'.number_format($this->info['forza']/60,2,'.','').'</span>';
                    }
                $txt.='</div>';

                $txt.='<div style="display:inline-block;width:20%;text-align:left;height:18px;">';
                    /*$txt.='<div style="display:inline-block;width:33.3%;text-align:center;vertical-align:top;box-sizing:border-box;height:18px;">';
                            $txt.='<img style="position:relative;width:15px;height:15px;border: 1px solid transparent;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/alan/img/escludiON.png" />';
                    $txt.='</div>';
                    $txt.='<div style="display:inline-block;width:33.3%;text-align:center;vertical-align:top;box-sizing:border-box;height:18px;">';
                            $txt.='<img style="width:16px;height:24px;border: 1px solid transparent;transform: translate(0px,-5px) rotate(90deg);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/alan/img/forza.png" />';
                    $txt.='</div>';*/
                $txt.='</div>';

            $txt.='</div>';

            return $txt;

        }

    }

?>