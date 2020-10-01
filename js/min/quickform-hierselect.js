/*
 HTML_QuickForm2: support functions for hierselect elements
 Package version 2.0.2
 http://pear.php.net/package/HTML_QuickForm2

 Copyright 2006-2014, Alexey Borzov, Bertrand Mansion
 Licensed under new BSD license
 http://opensource.org/licenses/bsd-license.php
*/
qf.elements.hierselect=function(){function k(a){return function(){setTimeout(function(){if(a.id in qf.elements.hierselect.defaults)for(var c=qf.elements.hierselect.defaults[a.id],d=a.hierselect.next,b=0;b<d.length;b++)qf.elements.hierselect.replaceOptions(document.getElementById(d[b]),qf.elements.hierselect.getOptions(a.id,c.slice(0,b+1))),qf.form.setValue(d[b],c[b+1])},1)}}function l(a){return function(){if(a.id in qf.elements.hierselect.defaults){var c=qf.elements.hierselect.defaults[a.id],d=a.hierselect.next;
qf.form.setValue(a,c[0]);for(var b=0;b<d.length;b++)qf.form.setValue(d[b],c[b+1])}}}function m(a){a=qf.events.fixEvent(a);a.target.hierselect&&0!=a.target.hierselect.next.length&&qf.elements.hierselect.cascade.call(a.target)}return{init:function(a,c){for(var d=[],b=document.getElementById(a[0]),e;a.length&&(e=a.shift());){d.push(e);var f=document.getElementById(e);f.hierselect={previous:d.concat(),next:a.concat(),callback:c};qf.events.addListener(f,"change",m)}qf.events.addListener(b.form,"reset",
k(b));qf.events.addListener(window,"load",l(b))},getValue:function(a){for(var c=[],d=0;d<a.length;d++)c.push(qf.form.getValue(a[d]));return c},replaceOptions:function(a,c){for(var d=a.options.length=0;d<c.values.length;d++){var b=a.options,e=d,f=Option,g;if(-1==String(c.texts[d]).indexOf("&"))g=c.texts[d];else{g=c.texts[d];var h=document.createElement("div");h.innerHTML=g;g=h.childNodes[0]?h.childNodes[0].nodeValue:""}b[e]=new f(g,c.values[d],!1,!1)}},getOptions:function(a,c,d){if(!(a in qf.elements.hierselect.options)||
"undefined"==typeof qf.elements.hierselect.options[a][c.length-1])return qf.elements.hierselect.missingOptions;for(var b=qf.elements.hierselect.options[a][c.length-1],e=c.concat();e.length;){var f=e.shift();if(0==e.length)return f in b||(b[f]=d?d(c,a):qf.elements.hierselect.missingOptions),b[f];f in b||(b[f]={});b=b[f]}},getAsyncCallback:function(a,c){return function(d){a in qf.elements.hierselect.options||(qf.elements.hierselect.options[a]=[]);"undefined"==typeof qf.elements.hierselect.options[a][c.length-
1]&&(qf.elements.hierselect.options[a][c.length-1]={});for(var b=qf.elements.hierselect.options[a][c.length-1],e=c.concat();e.length;){var f=e.shift();0==e.length?b[f]=d:b=f in b?b[f]:b[f]={}}b=document.getElementById(a).hierselect;e=document.getElementById(b.next[c.length-1]);qf.elements.hierselect.replaceOptions(e,d);c.length<b.next.length&&qf.elements.hierselect.cascade.call(e)}},cascade:function(){var a=qf.elements.hierselect.getValue(this.hierselect.previous);qf.elements.hierselect.replaceOptions(document.getElementById(this.hierselect.next[0]),
qf.elements.hierselect.getOptions(this.hierselect.previous[0],a,this.hierselect.callback));1<this.hierselect.next.length&&qf.elements.hierselect.cascade.call(document.getElementById(this.hierselect.next[0]))},missingOptions:{values:[""],texts:[" "]},options:{},defaults:{}}}();
