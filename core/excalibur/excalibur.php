<?php
//genera una lista di dati visualizzabile,filtrabile ed esportabile

class excalibur {

    protected $tag="";
    protected $titolo="";

    //attiva o disattiva la colonna di selezione in testa (serve per le funzioni personalizzate)
    protected $select=false;

    protected $elementi=array();
    protected $conv=array();
    protected $mappa=array();
    protected $footer=array();
    protected $backFoot=array();

    //array delle funzioni personalizzate
    protected $func=array();

    protected $datatab=true;

    function __construct($tag,$titolo) {
        
        $this->tag=$tag;
        $this->titolo=$titolo;
    }

    function build($conv,$mappa) {
        $this->conv=$conv;
        $this->mappa=$mappa;
    }

    function setDatatable($x) {
        $this->datatab=$x;
    }

    function setFooter($name,$arr) {

        $this->footer[$name]=array();

        foreach ($arr as $k=>$a) {
            if (array_key_exists($k,$this->mappa)) {
                $this->footer[$name][$k]=$a;

                if(!isset($this->backFoot[$k])) {
                    $this->backFoot[$k]=array();
                }

                $this->backFoot[$k][]=$name;
            }
        }
    }

    function add($a) {
        
        $temp=array();

        foreach ($this->conv as $db=>$m) {

            if (array_key_exists($db,$a)) {
                if (array_key_exists($m,$this->mappa)) {
                    $temp[$m]=utf8_encode(preg_replace("/\r|\n/", " ",(str_replace(['"',"'"], " ", $a[$db]))));

                    if (isset($this->backFoot[$m])) {
                        foreach ($this->backFoot[$m] as $kn=>$n) {
                            if (isset($this->footer[$n])) {
                                $r=$this->footer[$n][$m];

                                if ($r['op']=='sum') {
                                    $this->footer[$n][$m]['val']+=(float)$temp[$m];
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->elementi[]=$temp;
    }

    function getElementi() {
        return $this->elementi;
    }

    function loadFunc($arr) {

        /*
        id => (
            tag,
            call AJAX
        )
        */

        foreach ($arr as $k=>$a ) {
            $this->func[$k]=$a;
            $this->select=true;
        }
    }

    function setSelect($val) {
        $this->select=$val;
    }

    public static function init() {
        echo '<link rel="stylesheet" type="text/css" href="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/datatables/datatables.min.css">';
        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/datatables/datatables.min.js"></script>';
    } 

    function draw() {

        echo '<div style="width:100%;height:8%;">';

            echo '<div style="position:relative;display:inline-block;width:70%;font-size:13pt;font-weight:bold;vertical-align:top;">'.$this->titolo.'</div>';

            echo '<div style="position:relative;display:inline-block;width:20%;height:45px;font-size:13pt;font-weight:bold;vertical-align:top;">';

                if (count($this->func)>0) {

                    echo '<div style="width:100%;height:100%;border:1px solid black;text-align:center;padding:2px;box-sizing:border-box;">';

                        foreach ($this->func as $k=>$f) {
                            echo '<div style="width:25%;text-align:center;margin-top:2px;">';
                                echo '<div>';
                                    echo '<img style="width:22px;height:22px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/excalibur/img/fx.png" onclick="window._excalibur_'.$this->tag.'.execute(\''.$k.'\');" />';
                                echo '</div>';

                                echo '<div style="font-weight:bold;font-size:0.7em;">';
                                    echo substr($f['tag'],0,10);
                                echo '</div>';
                            echo '</div>';
                        }

                    echo '</div>';
                }
            
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:5%;vertical-align:top;text-align:right;">';
                echo '<img style="width:35px;height:30px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/excalibur/img/csv.png" onclick="window._excalibur_'.$this->tag.'.exportCsv();"/>';
            echo '</div>';

            echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/excalibur/code.js?v='.time().'"></script>';
            echo '<script type="text/javascript" >';
                echo 'window._excalibur_'.$this->tag.'=new nebulaExcalibur("'.$this->tag.'",'.($this->select?'true':'false').');';
            echo '</script>';

        echo '</div>';
        
        echo '<div style="position:relative;width:100%;height:89%;margin-top:1%;overflow:scroll;">';

            echo '<table id="excalibur_'.$this->tag.'_table" style="margin-right:30px;margin-bottom:30px;font-size:11pt;border-spacing: 0 5px;">';

                echo '<thead  id="excalibur_'.$this->tag.'_tabHead" >';

                    echo '<tr>';

                        if ($this->select) {
                            echo '<th style="border:1px solid black;padding:3px;text-align:center; position: sticky;top:0px;background-color:white;" data-val="excaliburSelect" >';
                                echo '<img style="width:25px;height:25px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/excalibur/img/toggle.png" onclick="window._excalibur_'.$this->tag.'.toggle();"/>';
                                echo '<input id="excalibur_toggle" type="hidden" value="1" />';
                            echo '</th>';
                        }

                        foreach ($this->mappa as $k=>$m) {
                            echo '<th style="border:1px solid black;padding:3px;text-align:center; position: sticky;top:0px;background-color:white;" data-val="'.$m['tag'].'" data-key="'.$k.'">'.$m['tag'].'</th>';
                        }

                    echo '</tr>';

                echo '</thead>';

                echo '<tbody  id="excalibur_'.$this->tag.'_tabBody">';
                        
                    foreach ($this->elementi as $k=>$e) {

                        echo '<tr  id="excalibur_'.$this->tag.'_tabRow_'.$k.'" style="cursor:pointer;" data-row="'.$k.'" onclick="window._excalibur_'.$this->tag.'.highlightRow(\''.$k.'\');">';

                            if ($this->select) {
                                echo '<td style="border:1px solid #888888;padding:3px;white-space: nowrap;" data-val="excaliburSelect" >';
                                    echo '<input id="excalibur_select_'.$this->tag.'_tabRow_'.$k.'" type="checkbox" checked />';
                                echo '</td>';
                            }
                        
                            foreach ($this->mappa as $km=>$m) {

                                if (array_key_exists($km,$e)) {

                                    $txt=$e[$km];

                                    if (isset($m['tipo'])) {
                                        if ($m['tipo']=='data') $txt=mainFunc::gab_todata($txt);
                                    }

                                    echo '<td style="border:1px solid #888888;padding:3px;white-space: nowrap;';
                                        if (isset($m['css'])) echo $m['css'];
                                    echo '" data-val="'.$e[$km].'" data-key="'.$km.'">'.$txt.'</td>';
                                }
                                else echo '<td style="border:1px solid #888888;"></td>';
                            }
                        
                        echo '</tr>';
                    }

                    foreach ($this->footer as $k=>$f) {

                        echo '<tr>';

                            if ($this->select) {
                                echo '<td style="border:1px solid transparent;"></td>';
                            }

                            foreach ($this->mappa as $km=>$m) {

                                if (array_key_exists($km,$f)) {
                                    echo '<td style="border:1px solid transparent;padding:3px;white-space: nowrap;font-weight:bold;';
                                        if (isset($f[$km]['css'])) echo $f[$km]['css'];
                                    echo '" >';
                                        switch ($f[$km]['op']) {
                                            case 'titolo':
                                                echo $f[$km]['val'];
                                            break;
                                            case 'sum':
                                                $d=(isset($f[$km]['dec']))?$f[$km]['dec']:0;
                                                echo number_format($f[$km]['val'],$d,'.','');
                                            break;
                                        }
                                    echo '</td>';
                                }
                                else echo '<td style="border:1px solid transparent;"></td>';
                            }

                        echo '</tr>';
                    }

                echo '</tbody>';

            echo '</table>';

        echo '</div>';

        if ($this->datatab) {
            echo '<script type="text/javascript" >';
                echo '$("#excalibur_'.$this->tag.'_table").DataTable({
                        "lengthMenu": false,
                        "pageLength": -1,
                        "lengthChange": false,
                        "layout": {
                            topStart: "search",
                            topEnd: null,
                            bottomStart: "info",
                            bottomEnd: "paging"
                        }
                    });
                ';

                echo '$(".dataTables_filter").css("float","left");'; 
            echo '</script>';
        }

    }

}

?>