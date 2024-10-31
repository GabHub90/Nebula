jQuery.jPrintArea=function(el)
{
var iframe=document.createElement('IFRAME');
var doc=null;
$(iframe).attr('style','position:absolute;width:0px;height:0px;left:-500px;top:-500px;');
document.body.appendChild(iframe);
doc=iframe.contentWindow.document;
/*var links=window.document.getElementsByTagName('link');
for(var i=0;i<links.length;i++)
if(links[i].rel.toLowerCase()=='stylesheet')*/
doc.write('<link rel="stylesheet" type="text/css" href="carb.css" media="print" />');
 
doc.write('<div class="'+$(el).attr("class")+'">'+$(el).html()+'</div>');
doc.close();

setTimeout(function() {jprintarea(iframe);},100);
//alert('Printing...');
}

function jprintarea(iframe) {
	iframe.contentWindow.focus();
	iframe.contentWindow.print();
	document.body.removeChild(iframe);
}