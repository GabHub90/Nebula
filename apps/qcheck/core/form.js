
var txt=""+this.actualScore.punteggio;

var completezza=round( (this.actualScore.risposte/this.actualScore.domande)*100 );

txt+='<span style="font-size:smaller;"> ('+completezza+')';

$('#js_chk_'+this.form_tag+'_head').html(txt);




