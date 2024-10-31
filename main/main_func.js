function mainFunc() {

  //tiene il tempo dell'ultima interazione con il sito
  this.time=new Date().getTime();

  //repGru = [macrorep][rep][gruppo] presi dalla configurazione utente al momento del caricamento
  this.contesto={
    mainLogged:"",
    mainApp:"",
    mail:"",
    configUtente:{},
    configFunzioni:{},
    repGru:{},
  };

  //regola l'apertura e la chiusura del menu dei sistemi
  this.systemMenu=false;

  this.getContesto=function() {
    return this.contesto;
  }

  this.loadContesto=function(obj) {
    for (var x in this.contesto) {
      //alert(x);
      if (x in obj) this.contesto[x]=obj[x];
    }

    for (var rep in this.contesto.configUtente.generale) {
      for (var gru in this.contesto.configUtente.generale[rep]) {
        if ( !(this.contesto.configUtente.generale[rep][gru].macroreparto in this.contesto.repGru) ) {
          this.contesto.repGru[this.contesto.configUtente.generale[rep][gru].macroreparto]={};
        }
        this.contesto.repGru[this.contesto.configUtente.generale[rep][gru].macroreparto][rep]=gru;
      }
    }
  }

  this.getMainapp=function() {
    return this.contesto.mainApp;
  }

  this.getMainLogged=function() {
    return this.contesto.mainLogged;
  }

  this.checkTime=function() {
    var t=new Date().getTime();
    //alert(t-this.time);
    //se son passate più di 4 ore (14400000)
    //se son passate più di 6 ore (21600000)
    if ( (t-this.time)>21600000 ) {
      event.stopPropagation();
      this.reload();
      return false;
    }
    else {
      this.time=new Date().getTime();
      return true;
    }
  }

  this.setWaiter=function() {
    var txt='<div style="text-align:center;">';
    txt+='<img style="width:40px;height:40px;" src="http://'+location.host+'/nebula/main/img/busy.gif" />';
    txt+='</div>';

    return txt;
  }

  ////////////////////////////////////////
  this.getGruppo=function(reparto,override) {
    //restituisce il gruppo all'interno di uno specifico reparto
    //se il valore è "" ed $override è diverso da array()
    //allora restituisce il PRIMO gruppo che trova all'interno dei MACROREPARTI inseriti in $override

    var gruppo="";
    
    for (var x in this.contesto.repGru) {
      for (var y in this.contesto.repGru[x]) {
        if (y==reparto) gruppo=this.contesto.repGru[x][y];
      }
    }

    if (gruppo=="" && override.lenght>0) {
      for (var z in override) {
        if (z in this.contesto.repGru) {
          for (var k in this.contesto.repGru[z]) {
            gruppo=this.contesto.repGru[z][k];
            break;
          }
        }
        if (gruppo!="") break;
      }
    }

    return gruppo;

  }

  ////////////////////////////////////////

  this.data_db_to_ita=function(txt) {
    return txt.substr(6,2)+"/"+txt.substr(4,2)+"/"+txt.substr(0,4);
  }

  this.data_form_to_ita=function(txt) {
    return txt.substr(8,2)+"/"+txt.substr(5,2)+"/"+txt.substr(0,4);
  }

  this.data_db_to_form=function(txt) {
    return txt.substr(0,4)+"-"+txt.substr(4,2)+"-"+txt.substr(6,2);
  }

  this.data_ita_to_db=function(d) {
    return ''+d.substr(6,4)+d.substr(3,2)+d.substr(0,2);
  }

  this.data_form_to_db=function(d) {
    return ''+d.substr(0,4)+d.substr(5,2)+d.substr(8,2);
  }

  this.leapYear=function(year) {
    var result;

    if ( (year%400) == 0){
      result = true
    }
    else if( (year%100) == 0){
      result = false
    }
    else if( (year%4) == 0){
      result = true
    }
    else{
      result = false
    }
    return result
  }

  this.moveByDays=function(theDate, days) {
    return new Date(theDate.getTime() + days*24*60*60*1000);
  }

  this.timeToMin=function(txt) {

    var ret=parseInt(txt.substr(0,2)*60);
    ret+=parseInt(txt.substr(3,2));

    return ret;
  }

  this.minToTime=function(min) {

    var ret="";

    var h=Math.floor(min/60);
    var m=min-(h*60);

    ret+=(h<10)?"0"+h:h;
    ret+=':';
    ret+=(m<10)?"0"+m:m;

    return ret;
  }

  // Funzione per concatenare DIV separti da "-" in un testo 
  this.concatDivToText=function(html) {
    // Crea un elemento div temporaneo per inserire l'HTML
    var tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;

    // Seleziona tutti i tag <div> dentro al div temporaneo
    var divs = tempDiv.querySelectorAll('div');

    var text = '';

    if (!divs || divs==='undefined' || divs.length==0) text+=tempDiv.textContent;
    else {
      // Itera su tutti i divs, concatenando il testo con un carattere di nuova riga
      for (var i = 0; i < divs.length; i++) {
        text += divs[i].textContent;
        // Aggiungi un carattere di nuova riga tranne per l'ultimo div
        if (i < divs.length - 1) {
          text += ' - ';
        }
      }
    }

    // Ritorna il testo risultante
    return text;
  }

  this.phpDate =function (format, timestamp) {
    //  discuss at: https://locutus.io/php/date/
    // original by: Carlos R. L. Rodrigues (https://www.jsfromhell.com)
    // original by: gettimeofday
    //    parts by: Peter-Paul Koch (https://www.quirksmode.org/js/beat.html)
    // improved by: Kevin van Zonneveld (https://kvz.io)
    // improved by: MeEtc (https://yass.meetcweb.com)
    // improved by: Brad Touesnard
    // improved by: Tim Wiel
    // improved by: Bryan Elliott
    // improved by: David Randall
    // improved by: Theriault (https://github.com/Theriault)
    // improved by: Theriault (https://github.com/Theriault)
    // improved by: Brett Zamir (https://brett-zamir.me)
    // improved by: Theriault (https://github.com/Theriault)
    // improved by: Thomas Beaucourt (https://www.webapp.fr)
    // improved by: JT
    // improved by: Theriault (https://github.com/Theriault)
    // improved by: Rafał Kukawski (https://blog.kukawski.pl)
    // improved by: Theriault (https://github.com/Theriault)
    //    input by: Brett Zamir (https://brett-zamir.me)
    //    input by: majak
    //    input by: Alex
    //    input by: Martin
    //    input by: Alex Wilson
    //    input by: Haravikk
    // bugfixed by: Kevin van Zonneveld (https://kvz.io)
    // bugfixed by: majak
    // bugfixed by: Kevin van Zonneveld (https://kvz.io)
    // bugfixed by: Brett Zamir (https://brett-zamir.me)
    // bugfixed by: omid (https://locutus.io/php/380:380#comment_137122)
    // bugfixed by: Chris (https://www.devotis.nl/)
    //      note 1: Uses global: locutus to store the default timezone
    //      note 1: Although the function potentially allows timezone info
    //      note 1: (see notes), it currently does not set
    //      note 1: per a timezone specified by date_default_timezone_set(). Implementers might use
    //      note 1: $locutus.currentTimezoneOffset and
    //      note 1: $locutus.currentTimezoneDST set by that function
    //      note 1: in order to adjust the dates in this function
    //      note 1: (or our other date functions!) accordingly
    //   example 1: date('H:m:s \\m \\i\\s \\m\\o\\n\\t\\h', 1062402400)
    //   returns 1: '07:09:40 m is month'
    //   example 2: date('F j, Y, g:i a', 1062462400)
    //   returns 2: 'September 2, 2003, 12:26 am'
    //   example 3: date('Y W o', 1062462400)
    //   returns 3: '2003 36 2003'
    //   example 4: var $x = date('Y m d', (new Date()).getTime() / 1000)
    //   example 4: $x = $x + ''
    //   example 4: var $result = $x.length // 2009 01 09
    //   returns 4: 10
    //   example 5: date('W', 1104534000)
    //   returns 5: '52'
    //   example 6: date('B t', 1104534000)
    //   returns 6: '999 31'
    //   example 7: date('W U', 1293750000.82); // 2010-12-31
    //   returns 7: '52 1293750000'
    //   example 8: date('W', 1293836400); // 2011-01-01
    //   returns 8: '52'
    //   example 9: date('W Y-m-d', 1293974054); // 2011-01-02
    //   returns 9: '52 2011-01-02'
    //        test: skip-1 skip-2 skip-5
    let jsdate, f
    // Keep this here (works, but for code commented-out below for file size reasons)
    // var tal= [];
    const txtWords = [
      'Sun', 'Mon', 'Tues', 'Wednes', 'Thurs', 'Fri', 'Satur',
      'January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'
    ]
    // trailing backslash -> (dropped)
    // a backslash followed by any character (including backslash) -> the character
    // empty string -> empty string
    const formatChr = /\\?(.?)/gi
    const formatChrCb = function (t, s) {
      return f[t] ? f[t]() : s
    }
    const _pad = function (n, c) {
      n = String(n)
      while (n.length < c) {
        n = '0' + n
      }
      return n
    }
    f = {
      // Day
      d: function () {
        // Day of month w/leading 0; 01..31
        return _pad(f.j(), 2)
      },
      D: function () {
        // Shorthand day name; Mon...Sun
        return f.l()
          .slice(0, 3)
      },
      j: function () {
        // Day of month; 1..31
        return jsdate.getDate()
      },
      l: function () {
        // Full day name; Monday...Sunday
        return txtWords[f.w()] + 'day'
      },
      N: function () {
        // ISO-8601 day of week; 1[Mon]..7[Sun]
        return f.w() || 7
      },
      S: function () {
        // Ordinal suffix for day of month; st, nd, rd, th
        const j = f.j()
        let i = j % 10
        if (i <= 3 && parseInt((j % 100) / 10, 10) === 1) {
          i = 0
        }
        return ['st', 'nd', 'rd'][i - 1] || 'th'
      },
      w: function () {
        // Day of week; 0[Sun]..6[Sat]
        return jsdate.getDay()
      },
      z: function () {
        // Day of year; 0..365
        const a = new Date(f.Y(), f.n() - 1, f.j())
        const b = new Date(f.Y(), 0, 1)
        return Math.round((a - b) / 864e5)
      },
      // Week
      W: function () {
        // ISO-8601 week number
        const a = new Date(f.Y(), f.n() - 1, f.j() - f.N() + 3)
        const b = new Date(a.getFullYear(), 0, 4)
        return _pad(1 + Math.round((a - b) / 864e5 / 7), 2)
      },
      // Month
      F: function () {
        // Full month name; January...December
        return txtWords[6 + f.n()]
      },
      m: function () {
        // Month w/leading 0; 01...12
        return _pad(f.n(), 2)
      },
      M: function () {
        // Shorthand month name; Jan...Dec
        return f.F()
          .slice(0, 3)
      },
      n: function () {
        // Month; 1...12
        return jsdate.getMonth() + 1
      },
      t: function () {
        // Days in month; 28...31
        return (new Date(f.Y(), f.n(), 0))
          .getDate()
      },
      // Year
      L: function () {
        // Is leap year?; 0 or 1
        const j = f.Y()
        return j % 4 === 0 & j % 100 !== 0 | j % 400 === 0
      },
      o: function () {
        // ISO-8601 year
        const n = f.n()
        const W = f.W()
        const Y = f.Y()
        return Y + (n === 12 && W < 9 ? 1 : n === 1 && W > 9 ? -1 : 0)
      },
      Y: function () {
        // Full year; e.g. 1980...2010
        return jsdate.getFullYear()
      },
      y: function () {
        // Last two digits of year; 00...99
        return f.Y()
          .toString()
          .slice(-2)
      },
      // Time
      a: function () {
        // am or pm
        return jsdate.getHours() > 11 ? 'pm' : 'am'
      },
      A: function () {
        // AM or PM
        return f.a()
          .toUpperCase()
      },
      B: function () {
        // Swatch Internet time; 000..999
        const H = jsdate.getUTCHours() * 36e2
        // Hours
        const i = jsdate.getUTCMinutes() * 60
        // Minutes
        // Seconds
        const s = jsdate.getUTCSeconds()
        return _pad(Math.floor((H + i + s + 36e2) / 86.4) % 1e3, 3)
      },
      g: function () {
        // 12-Hours; 1..12
        return f.G() % 12 || 12
      },
      G: function () {
        // 24-Hours; 0..23
        return jsdate.getHours()
      },
      h: function () {
        // 12-Hours w/leading 0; 01..12
        return _pad(f.g(), 2)
      },
      H: function () {
        // 24-Hours w/leading 0; 00..23
        return _pad(f.G(), 2)
      },
      i: function () {
        // Minutes w/leading 0; 00..59
        return _pad(jsdate.getMinutes(), 2)
      },
      s: function () {
        // Seconds w/leading 0; 00..59
        return _pad(jsdate.getSeconds(), 2)
      },
      u: function () {
        // Microseconds; 000000-999000
        return _pad(jsdate.getMilliseconds() * 1000, 6)
      },
      // Timezone
      e: function () {
        // Timezone identifier; e.g. Atlantic/Azores, ...
        // The following works, but requires inclusion of the very large
        // timezone_abbreviations_list() function.
        /*              return that.date_default_timezone_get();
         */
        const msg = 'Not supported (see source code of date() for timezone on how to add support)'
        throw new Error(msg)
      },
      I: function () {
        // DST observed?; 0 or 1
        // Compares Jan 1 minus Jan 1 UTC to Jul 1 minus Jul 1 UTC.
        // If they are not equal, then DST is observed.
        const a = new Date(f.Y(), 0)
        // Jan 1
        const c = Date.UTC(f.Y(), 0)
        // Jan 1 UTC
        const b = new Date(f.Y(), 6)
        // Jul 1
        // Jul 1 UTC
        const d = Date.UTC(f.Y(), 6)
        return ((a - c) !== (b - d)) ? 1 : 0
      },
      O: function () {
        // Difference to GMT in hour format; e.g. +0200
        const tzo = jsdate.getTimezoneOffset()
        const a = Math.abs(tzo)
        return (tzo > 0 ? '-' : '+') + _pad(Math.floor(a / 60) * 100 + a % 60, 4)
      },
      P: function () {
        // Difference to GMT w/colon; e.g. +02:00
        const O = f.O()
        return (O.substr(0, 3) + ':' + O.substr(3, 2))
      },
      T: function () {
        // The following works, but requires inclusion of the very
        // large timezone_abbreviations_list() function.
        /*              var abbr, i, os, _default;
        if (!tal.length) {
          tal = that.timezone_abbreviations_list();
        }
        if ($locutus && $locutus.default_timezone) {
          _default = $locutus.default_timezone;
          for (abbr in tal) {
            for (i = 0; i < tal[abbr].length; i++) {
              if (tal[abbr][i].timezone_id === _default) {
                return abbr.toUpperCase();
              }
            }
          }
        }
        for (abbr in tal) {
          for (i = 0; i < tal[abbr].length; i++) {
            os = -jsdate.getTimezoneOffset() * 60;
            if (tal[abbr][i].offset === os) {
              return abbr.toUpperCase();
            }
          }
        }
        */
        return 'UTC'
      },
      Z: function () {
        // Timezone offset in seconds (-43200...50400)
        return -jsdate.getTimezoneOffset() * 60
      },
      // Full Date/Time
      c: function () {
        // ISO-8601 date.
        return 'Y-m-d\\TH:i:sP'.replace(formatChr, formatChrCb)
      },
      r: function () {
        // RFC 2822
        return 'D, d M Y H:i:s O'.replace(formatChr, formatChrCb)
      },
      U: function () {
        // Seconds since UNIX epoch
        return jsdate / 1000 | 0
      }
    }
    const _date = function (format, timestamp) {
      jsdate = (timestamp === undefined
        ? new Date() // Not provided
        : (timestamp instanceof Date)
            ? new Date(timestamp) // JS Date()
            : new Date(timestamp * 1000) // UNIX timestamp (auto-convert to int)
      )
      return format.replace(formatChr, formatChrCb)
    }
    return _date(format, timestamp)
  }

  this.number_format=function(number, decimals, dec_point, thousands_sep) {
    //  discuss at: http://phpjs.org/functions/number_format/
    // original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: davook
    // improved by: Brett Zamir (http://brett-zamir.me)
    // improved by: Brett Zamir (http://brett-zamir.me)
    // improved by: Theriault
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // bugfixed by: Michael White (http://getsprink.com)
    // bugfixed by: Benjamin Lupton
    // bugfixed by: Allan Jensen (http://www.winternet.no)
    // bugfixed by: Howard Yeend
    // bugfixed by: Diogo Resende
    // bugfixed by: Rival
    // bugfixed by: Brett Zamir (http://brett-zamir.me)
    //  revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    //  revised by: Luke Smith (http://lucassmith.name)
    //    input by: Kheang Hok Chin (http://www.distantia.ca/)
    //    input by: Jay Klehr
    //    input by: Amir Habibi (http://www.residence-mixte.com/)
    //    input by: Amirouche
    //   example 1: number_format(1234.56);
    //   returns 1: '1,235'
    //   example 2: number_format(1234.56, 2, ',', ' ');
    //   returns 2: '1 234,56'
    //   example 3: number_format(1234.5678, 2, '.', '');
    //   returns 3: '1234.57'
    //   example 4: number_format(67, 2, ',', '.');
    //   returns 4: '67,00'
    //   example 5: number_format(1000);
    //   returns 5: '1,000'
    //   example 6: number_format(67.311, 2);
    //   returns 6: '67.31'
    //   example 7: number_format(1000.55, 1);
    //   returns 7: '1,000.6'
    //   example 8: number_format(67000, 5, ',', '.');
    //   returns 8: '67.000,00000'
    //   example 9: number_format(0.9, 0);
    //   returns 9: '1'
    //  example 10: number_format('1.20', 2);
    //  returns 10: '1.20'
    //  example 11: number_format('1.20', 4);
    //  returns 11: '1.2000'
    //  example 12: number_format('1.2000', 3);
    //  returns 12: '1.200'
    //  example 13: number_format('1 000,50', 2, '.', ' ');
    //  returns 13: '100 050.00'
    //  example 14: number_format(1e-8, 8, '.', '');
    //  returns 14: '0.00000001'

    number = (number + '')
      .replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
      prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
      sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
      dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
      s = '',
      toFixedFix = function(n, prec) {
        var k = Math.pow(10, prec);
        return '' + (Math.round(n * k) / k)
          .toFixed(prec);
      };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
      .split('.');
    if (s[0].length > 3) {
      s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '')
      .length < prec) {
      s[1] = s[1] || '';
      s[1] += new Array(prec - s[1].length + 1)
        .join('0');
    }
    return s.join(dec);
  }

  this.ucfirst=function(str) {
    //  discuss at: http://phpjs.org/functions/ucfirst/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // bugfixed by: Onno Marsman
    // improved by: Brett Zamir (http://brett-zamir.me)
    //   example 1: ucfirst('kevin van zonneveld');
    //   returns 1: 'Kevin van zonneveld'

    str += '';
    var f = str.charAt(0).toUpperCase();
    return f + str.substr(1);
  }

  this.strtolower=function (str) {
    //  discuss at: http://phpjs.org/functions/strtolower/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: Onno Marsman
    //   example 1: strtolower('Kevin van Zonneveld');
    //   returns 1: 'kevin van zonneveld'

    return (str + '').toLowerCase();
  }

  this.nl2br=function(str, is_xhtml) {
    //  discuss at: http://phpjs.org/functions/nl2br/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: Philip Peterson
    // improved by: Onno Marsman
    // improved by: Atli Þór
    // improved by: Brett Zamir (http://brett-zamir.me)
    // improved by: Maximusya
    // bugfixed by: Onno Marsman
    // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    //    input by: Brett Zamir (http://brett-zamir.me)
    //   example 1: nl2br('Kevin\nvan\nZonneveld');
    //   returns 1: 'Kevin<br />\nvan<br />\nZonneveld'
    //   example 2: nl2br("\nOne\nTwo\n\nThree\n", false);
    //   returns 2: '<br>\nOne<br>\nTwo<br>\n<br>\nThree<br>\n'
    //   example 3: nl2br("\nOne\nTwo\n\nThree\n", true);
    //   returns 3: '<br />\nOne<br />\nTwo<br />\n<br />\nThree<br />\n'

    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>'; // Adjust comment to avoid issue on phpjs.org display

    return (str + '')
      .replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
  }

  this.addslashes=function(string) {
    return string.replace(/\\/g, '\\\\').
        replace(/\u0008/g, '\\b').
        replace(/\t/g, '\\t').
        replace(/\n/g, '\\n').
        replace(/\f/g, '\\f').
        replace(/\r/g, '\\r').
        replace(/'/g, '\\\'').
        replace(/"/g, '\\"');
  }

  ////////////////////////////////////////////////////////////////////////////

  this.getCookie=function(nome) {
    var name = nome + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ')
            c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
  }

  this.setCookie=function(nome, valore, ggScadenza, path) {
    if (path == undefined) {
        path = "/";
    }
    var d = new Date();
    d.setTime(d.getTime() + (ggScadenza * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = nome + "=" + valore + "; " + expires + "; path=" + path;
  }

  this.delCookie=function(name) {
    //document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    var d = new Date(); //Create a date object
    d.setTime(d.getTime() - (1000 * 60 * 60 * 24)); //Set the time to the past. 1000 milliseonds = 1 second
    var expires = "expires=" + d.toGMTString(); //Compose the expirartion date
    document.cookie = name + "=" + "; " + expires + ";path=/"; //Set the cookie with name and the expiration date
  }

  this.fromBinary=function(string) {
    const codeUnits = new Uint16Array(string.length);
    for (let i = 0; i < codeUnits.length; i++) {
      codeUnits[i] = string.charCodeAt(i);
    }
    return btoa(String.fromCharCode(...new Uint8Array(codeUnits.buffer)));
  }

  this.toBinary=function(encoded) {
    const binary = atob(encoded);
    const bytes = new Uint8Array(binary.length);
    for (let i = 0; i < bytes.length; i++) {
      bytes[i] = binary.charCodeAt(i);
    }
    return String.fromCharCode(...new Uint16Array(bytes.buffer));
  }

  this.b64EncodeUnicode=function(str) {
    // first we use encodeURIComponent to get percent-encoded Unicode,
    // then we convert the percent encodings into raw bytes which
    // can be fed into btoa.
    return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g,
        function toSolidBytes(match, p1) {
            return String.fromCharCode('0x' + p1);
    }));
  }

  this.b64DecodeUnicode=function (str) {
    // Going backwards: from bytestream, to percent-encoding, to original string.
    return decodeURIComponent(atob(str).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));
  }

  this.toUTF8Array=function(str) {
    var utf8 = [];
    for (var i=0; i < str.length; i++) {
        var charcode = str.charCodeAt(i);
        if (charcode < 0x80) utf8.push(charcode);
        else if (charcode < 0x800) {
            utf8.push(0xc0 | (charcode >> 6), 
                      0x80 | (charcode & 0x3f));
        }
        else if (charcode < 0xd800 || charcode >= 0xe000) {
            utf8.push(0xe0 | (charcode >> 12), 
                      0x80 | ((charcode>>6) & 0x3f), 
                      0x80 | (charcode & 0x3f));
        }
        // surrogate pair
        else {
            i++;
            charcode = ((charcode&0x3ff)<<10)|(str.charCodeAt(i)&0x3ff)
            utf8.push(0xf0 | (charcode >>18), 
                      0x80 | ((charcode>>12) & 0x3f), 
                      0x80 | ((charcode>>6) & 0x3f), 
                      0x80 | (charcode & 0x3f));
        }
    }
    return utf8;
  }

  this.fromUTF8Array=function(data) { // array of bytes
    var str = '',
        i;

    for (i = 0; i < data.length; i++) {
        var value = data[i];

        if (value < 0x80) {
            str += String.fromCharCode(value);
        } else if (value > 0xBF && value < 0xE0) {
            str += String.fromCharCode((value & 0x1F) << 6 | data[i + 1] & 0x3F);
            i += 1;
        } else if (value > 0xDF && value < 0xF0) {
            str += String.fromCharCode((value & 0x0F) << 12 | (data[i + 1] & 0x3F) << 6 | data[i + 2] & 0x3F);
            i += 2;
        } else {
            // surrogate pair
            var charCode = ((value & 0x07) << 18 | (data[i + 1] & 0x3F) << 12 | (data[i + 2] & 0x3F) << 6 | data[i + 3] & 0x3F) - 0x010000;

            str += String.fromCharCode(charCode >> 10 | 0xD800, charCode & 0x03FF | 0xDC00); 
            i += 3;
        }
    }

    return str;
  }

  //######## 06.09.2024 #################################
  this.base64ToUtf8=function(base64String) {
    // Decodifica la stringa Base64 a una Uint8Array di byte
    let binaryArray = Uint8Array.from(atob(base64String), c => c.charCodeAt(0));

    // Usa TextDecoder per decodificare in UTF-8
    let decoder = new TextDecoder('utf-8');
    let utf8String = decoder.decode(binaryArray);

    return utf8String;
}

  this.base64ToBlob=function (base64, mimetype, slicesize) {
    if (!window.atob || !window.Uint8Array) {
        // The current browser doesn't have the atob function. Cannot continue
        return null;
    }
    mimetype = mimetype || '';
    slicesize = slicesize || 512;
    var bytechars = atob(base64);
    //bytechars = this.fromUTF8Array(this.toUTF8Array(bytechars));

    var bytearrays = [];
    for (var offset = 0; offset < bytechars.length; offset += slicesize) {
        var slice = bytechars.slice(offset, offset + slicesize);
        var bytenums = new Array(slice.length);
        for (var i = 0; i < slice.length; i++) {
            bytenums[i] = slice.charCodeAt(i);
        }
        var bytearray = new Uint8Array(bytenums);
        bytearrays[bytearrays.length] = bytearray;
    }
    return new Blob(bytearrays, {type: mimetype});
  };

  this.base64ToBlob8=function (base64, mimetype, slicesize) {
    if (!window.atob || !window.Uint8Array) {
        // The current browser doesn't have the atob function. Cannot continue
        return null;
    }
    mimetype = mimetype || '';
    slicesize = slicesize || 512;
    var bytechars = window._nebulaMain.base64ToUtf8(base64);

    var bytearrays = [];
    for (var offset = 0; offset < bytechars.length; offset += slicesize) {
        var slice = bytechars.slice(offset, offset + slicesize);
        var bytenums = new Array(slice.length);
        for (var i = 0; i < slice.length; i++) {
            bytenums[i] = slice.charCodeAt(i);
        }
        var bytearray = new Uint8Array(bytenums);
        bytearrays[bytearrays.length] = bytearray;
    }
    return new Blob(bytearrays, {type: mimetype});
  };

  //////////////////////////////////////////////////////////////////////////////////////////
  this.logout=function() {
    if ( confirm('Vuoi uscire da Nebula?') ) {
      this.reload();
    }
  }

  this.reload=function() {
    this.delCookie("nebulaLogged");
      window.location='http://'+location.host+'/nebula/';
  }

  this.showBusy=function() {
    $('#nebulaMainBusy').css('visibility','visible');
  }

  this.hideBusy=function() {
    $('#nebulaMainBusy').css('visibility','hidden');
  }

  this.openApp=function(param) {

     //verifica il tempo di inattività dall'ultima operazione
     this.checkTime();

    var url = 'http://'+location.host+'/nebula/index.php';
    var txt = '<form action="' + url + '" method="post">';

    for (var x in param) {
      txt+='<input name="'+x+'" type="hidden" value="'+this.addslashes(param[x])+'" />';
    }

    txt+='</form>';

    var form = $(txt);
    $('body').append(form);
    form.submit();

  }

  this.openGalaxy=function(galassia) {

    //se è inizializzato l'oggetto ODL chiudilo attraverso la funzione del contesto in cui si è (per il momento  ISLA AVALON)
    if (typeof window._nebulaOdl !== 'undefined') {
      window._nebulaOdl.closeOdl();
    }
    //if (typeof window["_nebulaApp_"+window._nebulaApp.getTagFunzione()] !== 'undefined') {
      /*if (window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].closeOdl() === 'function') { 
          window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].closeOdl();
      }*/
    //}

    var param = { "mainApp":galassia+":home" };

    this.openApp(param);
  }

  this.openSystem=function(galassia,sistema) {

    //alert (galassia+':'+sistema);

    var param = { "mainApp":galassia+":"+sistema };

    this.openApp(param);
  }

  //richiama una funzione da un altro posto dell'universo
  this.linkFunction=function(galassia,sistema,funzione) {

    var param = { "mainApp":galassia+":"+sistema , "linkFunk":funzione };

    this.openApp(param);

  }

  this.openSystemMenu=function() {

    if (!this.systemMenu) {
      $('#nebulaSystemMenu').show();
      $('#nebulaSystemMenuArrow').prop('src','http://'+location.host+'/nebula/main/img/whitearrowD.png');
      this.systemMenu=true;
    }

    else {
      $('#nebulaSystemMenu').hide();
      $('#nebulaSystemMenuArrow').prop('src','http://'+location.host+'/nebula/main/img/whitearrowR.png');
      this.systemMenu=false;
    }
  
  }

}