
class chekkoMultiForm {

    constructor(tag) {
        this.tag=tag;
        this.form={};
        //il risultato di js_chk() è 0=no errori 1=errori
        this.chk=0;
        this.chg=false;
    }

    addForm(tag) {
        //30.04.2021 fino a questo momento i campi "flag" e "chg" dei singoli form non li ho usati
        //flag          indica se il form è attivo oppure no
        //chg           indica se il form pè stato modificato
        this.form[tag]={
           "flag":1,
           "chg":0,
           "expo":{}
        }
    }

    setChg(val) {

        this.chg=val;

        if (val) {
            $('div[ckMulti_'+this.tag+'="head"').css('background-color',"#ffff0066");
            $('div[ckMulti_'+this.tag+'="txt"').html('Modulo non salvato');
        }
        else {
            $('div[ckMulti_'+this.tag+'="head"').css('background-color',"transparent");
            $('div[ckMulti_'+this.tag+'="txt"').html('');
        }

        //this.check();
   
    }

    salva() {
        
        if (!this.check()) return false;

        //#####################
        for (var x in this.form) {
            this.form[x].expo=window['_js_chk_'+x].scrivi();
            /*if (x=='ctv_FSG') {
                alert (this.form[x].expo.gradi);
            }*/
            //alert(x+' '+JSON.stringify(this.form[x].expo));
        }
        //#####################

        $('div[ckMulti_'+this.tag+'="head"').css('background-color',"transparent");
        $('div[ckMulti_'+this.tag+'="txt"').html('');
        this.chg=false;

        return this.form;
    }


    check() {

        this.chk=0;

        for (var x in this.form) {

            if (this.form[x].flag==0) continue;

            var res=window["_js_chk_"+x].js_chk();

            //alert(x+' '+res);

            if (res==1) this.chk=1;
        }

        //se ci sono stati errori
        if (this.chk==1) {
            $('div[ckMulti_'+this.tag+'="head"').css('background-color',"#ff000055");
            $('div[ckMulti_'+this.tag+'="txt"').html('Il modulo presenta errori');
            return false;
        }

        else {
            return true;  
        }

    }

}
