!function(e){var t={};function n(r){if(t[r])return t[r].exports;var i=t[r]={i:r,l:!1,exports:{}};return e[r].call(i.exports,i,i.exports,n),i.l=!0,i.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var i in e)n.d(r,i,function(t){return e[t]}.bind(null,i));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=105)}({100:function(e,t,n){"use strict";for(var r=document.getElementsByClassName("btn-spi-port"),i=0;i<r.length;i++)r[i].addEventListener("click",(function(e){document.getElementById("frm-configIqrfSpiForm-IqrfInterface").value=e.currentTarget.dataset.port}));for(var o=document.getElementsByClassName("btn-spi-pin"),s=0;s<o.length;s++)o[s].addEventListener("click",(function(e){var t=e.currentTarget.dataset;document.getElementById("frm-configIqrfSpiForm-IqrfInterface").value=t.iqrfinterface,document.getElementById("frm-configIqrfSpiForm-powerEnableGpioPin").value=t.powerenablegpiopin,document.getElementById("frm-configIqrfSpiForm-busEnableGpioPin").value=t.busenablegpiopin,document.getElementById("frm-configIqrfSpiForm-pgmSwitchGpioPin").value=t.pgmswitchgpiopin}))},101:function(e,t,n){"use strict";for(var r=document.getElementsByClassName("btn-uart-port"),i=0;i<r.length;i++)r[i].addEventListener("click",(function(e){document.getElementById("frm-configIqrfUartForm-IqrfInterface").value=e.currentTarget.dataset.port}));for(var o=document.getElementsByClassName("btn-uart-pin"),s=0;s<o.length;s++)o[s].addEventListener("click",(function(e){var t=e.currentTarget.dataset;document.getElementById("frm-configIqrfUartForm-IqrfInterface").value=t.iqrfinterface,document.getElementById("frm-configIqrfUartForm-baudRate").value=t.baudrate,document.getElementById("frm-configIqrfUartForm-powerEnableGpioPin").value=t.powerenablegpiopin,document.getElementById("frm-configIqrfUartForm-busEnableGpioPin").value=t.busenablegpiopin}))},105:function(e,t,n){"use strict";n.r(t);n(99),n(100),n(101);var r=n(35),i=n.n(r);function o(){var e,t,n=document.getElementById("frm-configSchedulerForm-cron"),r=document.getElementById("frm-configSchedulerForm-timeSpec-cronTime").value,o=r.split(" ").length;if(1===o)return e=r,(t=new Map).set("@reboot",""),t.set("@yearly","0 0 0 1 1 * *"),t.set("@annually","0 0 0 1 1 * *"),t.set("@monthly","0 0 0 1 * * *"),t.set("@weekly","0 0 0 * * 0 *"),t.set("@daily","0 0 0 * * * *"),t.set("@hourly","0 0 * * * * *"),t.set("@minutely","0 * * * * * *"),void 0===(r=t.get(e))?void(null!==n&&(n.style.visibility="hidden")):s(i.a.toString(r));if(o>4&&o<8)try{s(i.a.toString(r))}catch(e){null!==n&&(n.style.visibility="hidden"),console.error(e)}else null!==n&&(n.style.visibility="hidden")}function s(e){var t=document.getElementById("frm-configSchedulerForm-cron");if(null===t){var n=document.getElementById("frm-configSchedulerForm-timeSpec-cronTime"),r=document.createElement("span");r.id="frm-configSchedulerForm-cron",r.innerText=e,r.className="label label-info",n.insertAdjacentHTML("beforebegin",r.outerHTML)}else t.innerText=e,t.style.visibility="visible"}function a(e){var t=document.getElementById("frm-configSchedulerForm-timeSpec-cronTime"),n=document.getElementById("frm-configSchedulerForm-timeSpec-exactTime"),r=document.getElementById("frm-configSchedulerForm-timeSpec-periodic"),i=document.getElementById("frm-configSchedulerForm-timeSpec-period"),o=document.getElementById("frm-configSchedulerForm-timeSpec-startTime"),s=e.currentTarget,a=s.checked;null!==t&&(t.disabled=a),null!==n&&s===r&&(n.disabled=a),null!==r&&s===n&&(r.disabled=a),null!==i&&(i.disabled=s!==r||!a),null!==o&&(o.disabled=s!==n||!a)}var u=document.getElementById("frm-configSchedulerForm-timeSpec-cronTime"),c=document.getElementById("frm-configSchedulerForm-timeSpec-exactTime"),f=document.getElementById("frm-configSchedulerForm-timeSpec-periodic");null!==c&&c.addEventListener("change",a),null!==f&&f.addEventListener("change",a),null!==u&&(o(),u.addEventListener("keyup",(function(){o()})))},35:function(e,t,n){var r;"undefined"!=typeof self&&self,r=function(){return function(e){var t={};function n(r){if(t[r])return t[r].exports;var i=t[r]={i:r,l:!1,exports:{}};return e[r].call(i.exports,i,i.exports,n),i.l=!0,i.exports}return n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var i in e)n.d(r,i,function(t){return e[t]}.bind(null,i));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=4)}([function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.ExpressionDescriptor=void 0;var r=n(1),i=n(2),o=function(){function e(t,n){this.expression=t,this.options=n,this.expressionParts=new Array(5),e.locales[n.locale]?this.i18n=e.locales[n.locale]:(console.warn("Locale '"+n.locale+"' could not be found; falling back to 'en'."),this.i18n=e.locales.en),void 0===n.use24HourTimeFormat&&(n.use24HourTimeFormat=this.i18n.use24HourTimeFormatByDefault())}return e.toString=function(t,n){var r=void 0===n?{}:n,i=r.throwExceptionOnParseError,o=void 0===i||i,s=r.verbose,a=void 0!==s&&s,u=r.dayOfWeekStartIndexZero,c=void 0===u||u,f=r.use24HourTimeFormat,l=r.locale;return new e(t,{throwExceptionOnParseError:o,verbose:a,dayOfWeekStartIndexZero:c,use24HourTimeFormat:f,locale:void 0===l?"en":l}).getFullDescription()},e.initialize=function(t){e.specialCharacters=["/","-",",","*"],t.load(e.locales)},e.prototype.getFullDescription=function(){var e="";try{var t=new i.CronParser(this.expression,this.options.dayOfWeekStartIndexZero);this.expressionParts=t.parse();var n=this.getTimeOfDayDescription(),r=this.getDayOfMonthDescription(),o=this.getMonthDescription();e+=n+r+this.getDayOfWeekDescription()+o+this.getYearDescription(),e=(e=this.transformVerbosity(e,this.options.verbose)).charAt(0).toLocaleUpperCase()+e.substr(1)}catch(t){if(this.options.throwExceptionOnParseError)throw""+t;e=this.i18n.anErrorOccuredWhenGeneratingTheExpressionD()}return e},e.prototype.getTimeOfDayDescription=function(){var t=this.expressionParts[0],n=this.expressionParts[1],i=this.expressionParts[2],o="";if(r.StringUtilities.containsAny(n,e.specialCharacters)||r.StringUtilities.containsAny(i,e.specialCharacters)||r.StringUtilities.containsAny(t,e.specialCharacters))if(t||!(n.indexOf("-")>-1)||n.indexOf(",")>-1||n.indexOf("/")>-1||r.StringUtilities.containsAny(i,e.specialCharacters))if(!t&&i.indexOf(",")>-1&&-1==i.indexOf("-")&&-1==i.indexOf("/")&&!r.StringUtilities.containsAny(n,e.specialCharacters)){var s=i.split(",");o+=this.i18n.at();for(var a=0;a<s.length;a++)o+=" ",o+=this.formatTime(s[a],n,""),a<s.length-2&&(o+=","),a==s.length-2&&(o+=this.i18n.spaceAnd())}else{var u=this.getSecondsDescription(),c=this.getMinutesDescription(),f=this.getHoursDescription();(o+=u).length>0&&c.length>0&&(o+=", "),(o+=c).length>0&&f.length>0&&(o+=", "),o+=f}else{var l=n.split("-");o+=r.StringUtilities.format(this.i18n.everyMinuteBetweenX0AndX1(),this.formatTime(i,l[0],""),this.formatTime(i,l[1],""))}else o+=this.i18n.atSpace()+this.formatTime(i,n,t);return o},e.prototype.getSecondsDescription=function(){var e=this;return this.getSegmentDescription(this.expressionParts[0],this.i18n.everySecond(),(function(e){return e}),(function(t){return r.StringUtilities.format(e.i18n.everyX0Seconds(),t)}),(function(t){return e.i18n.secondsX0ThroughX1PastTheMinute()}),(function(t){return"0"==t?"":parseInt(t)<20?e.i18n.atX0SecondsPastTheMinute():e.i18n.atX0SecondsPastTheMinuteGt20()||e.i18n.atX0SecondsPastTheMinute()}))},e.prototype.getMinutesDescription=function(){var e=this,t=this.expressionParts[0],n=this.expressionParts[2];return this.getSegmentDescription(this.expressionParts[1],this.i18n.everyMinute(),(function(e){return e}),(function(t){return r.StringUtilities.format(e.i18n.everyX0Minutes(),t)}),(function(t){return e.i18n.minutesX0ThroughX1PastTheHour()}),(function(r){try{return"0"==r&&-1==n.indexOf("/")&&""==t?e.i18n.everyHour():parseInt(r)<20?e.i18n.atX0MinutesPastTheHour():e.i18n.atX0MinutesPastTheHourGt20()||e.i18n.atX0MinutesPastTheHour()}catch(t){return e.i18n.atX0MinutesPastTheHour()}}))},e.prototype.getHoursDescription=function(){var e=this,t=this.expressionParts[2];return this.getSegmentDescription(t,this.i18n.everyHour(),(function(t){return e.formatTime(t,"0","")}),(function(t){return r.StringUtilities.format(e.i18n.everyX0Hours(),t)}),(function(t){return e.i18n.betweenX0AndX1()}),(function(t){return e.i18n.atX0()}))},e.prototype.getDayOfWeekDescription=function(){var e=this,t=this.i18n.daysOfTheWeek();return"*"==this.expressionParts[5]?"":this.getSegmentDescription(this.expressionParts[5],this.i18n.commaEveryDay(),(function(e){var n=e;return e.indexOf("#")>-1?n=e.substr(0,e.indexOf("#")):e.indexOf("L")>-1&&(n=n.replace("L","")),t[parseInt(n)]}),(function(t){return 1==parseInt(t)?"":r.StringUtilities.format(e.i18n.commaEveryX0DaysOfTheWeek(),t)}),(function(t){return e.i18n.commaX0ThroughX1()}),(function(t){var n=null;if(t.indexOf("#")>-1){var r=null;switch(t.substring(t.indexOf("#")+1)){case"1":r=e.i18n.first();break;case"2":r=e.i18n.second();break;case"3":r=e.i18n.third();break;case"4":r=e.i18n.fourth();break;case"5":r=e.i18n.fifth()}n=e.i18n.commaOnThe()+r+e.i18n.spaceX0OfTheMonth()}else n=t.indexOf("L")>-1?e.i18n.commaOnTheLastX0OfTheMonth():"*"!=e.expressionParts[3]?e.i18n.commaAndOnX0():e.i18n.commaOnlyOnX0();return n}))},e.prototype.getMonthDescription=function(){var e=this,t=this.i18n.monthsOfTheYear();return this.getSegmentDescription(this.expressionParts[4],"",(function(e){return t[parseInt(e)-1]}),(function(t){return 1==parseInt(t)?"":r.StringUtilities.format(e.i18n.commaEveryX0Months(),t)}),(function(t){return e.i18n.commaMonthX0ThroughMonthX1()||e.i18n.commaX0ThroughX1()}),(function(t){return e.i18n.commaOnlyInMonthX0?e.i18n.commaOnlyInMonthX0():e.i18n.commaOnlyInX0()}))},e.prototype.getDayOfMonthDescription=function(){var e=this,t=null,n=this.expressionParts[3];switch(n){case"L":t=this.i18n.commaOnTheLastDayOfTheMonth();break;case"WL":case"LW":t=this.i18n.commaOnTheLastWeekdayOfTheMonth();break;default:var i=n.match(/(\d{1,2}W)|(W\d{1,2})/);if(i){var o=parseInt(i[0].replace("W","")),s=1==o?this.i18n.firstWeekday():r.StringUtilities.format(this.i18n.weekdayNearestDayX0(),o.toString());t=r.StringUtilities.format(this.i18n.commaOnTheX0OfTheMonth(),s);break}var a=n.match(/L-(\d{1,2})/);if(a){var u=a[1];t=r.StringUtilities.format(this.i18n.commaDaysBeforeTheLastDayOfTheMonth(),u);break}if("*"==n&&"*"!=this.expressionParts[5])return"";t=this.getSegmentDescription(n,this.i18n.commaEveryDay(),(function(t){return"L"==t?e.i18n.lastDay():e.i18n.dayX0?r.StringUtilities.format(e.i18n.dayX0(),t):t}),(function(t){return"1"==t?e.i18n.commaEveryDay():e.i18n.commaEveryX0Days()}),(function(t){return e.i18n.commaBetweenDayX0AndX1OfTheMonth()}),(function(t){return e.i18n.commaOnDayX0OfTheMonth()}))}return t},e.prototype.getYearDescription=function(){var e=this;return this.getSegmentDescription(this.expressionParts[6],"",(function(e){return/^\d+$/.test(e)?new Date(parseInt(e),1).getFullYear().toString():e}),(function(t){return r.StringUtilities.format(e.i18n.commaEveryX0Years(),t)}),(function(t){return e.i18n.commaYearX0ThroughYearX1()||e.i18n.commaX0ThroughX1()}),(function(t){return e.i18n.commaOnlyInYearX0?e.i18n.commaOnlyInYearX0():e.i18n.commaOnlyInX0()}))},e.prototype.getSegmentDescription=function(e,t,n,i,o,s){var a=null,u=e.indexOf("/")>-1,c=e.indexOf("-")>-1,f=e.indexOf(",")>-1;if(e)if("*"===e)a=t;else if(u||c||f)if(f){for(var l=e.split(","),p="",h=0;h<l.length;h++)if(h>0&&l.length>2&&(p+=",",h<l.length-1&&(p+=" ")),h>0&&l.length>1&&(h==l.length-1||2==l.length)&&(p+=this.i18n.spaceAnd()+" "),l[h].indexOf("/")>-1||l[h].indexOf("-")>-1){var m=l[h].indexOf("-")>-1&&-1==l[h].indexOf("/"),d=this.getSegmentDescription(l[h],t,n,i,m?this.i18n.commaX0ThroughX1:o,s);m&&(d=d.replace(", ","")),p+=d}else p+=u?this.getSegmentDescription(l[h],t,n,i,o,s):n(l[h]);a=u?p:r.StringUtilities.format(s(e),p)}else if(u){if(l=e.split("/"),a=r.StringUtilities.format(i(l[1]),l[1]),l[0].indexOf("-")>-1){var y=this.generateRangeSegmentDescription(l[0],o,n);0!=y.indexOf(", ")&&(a+=", "),a+=y}else if(-1==l[0].indexOf("*")){var g=r.StringUtilities.format(s(l[0]),n(l[0]));g=g.replace(", ",""),a+=r.StringUtilities.format(this.i18n.commaStartingX0(),g)}}else c&&(a=this.generateRangeSegmentDescription(e,o,n));else a=r.StringUtilities.format(s(e),n(e));else a="";return a},e.prototype.generateRangeSegmentDescription=function(e,t,n){var i="",o=e.split("-"),s=n(o[0]),a=n(o[1]);a=a.replace(":00",":59");var u=t(e);return i+=r.StringUtilities.format(u,s,a)},e.prototype.formatTime=function(e,t,n){var r=parseInt(e),i="",o=!1;this.options.use24HourTimeFormat||(i=(o=this.i18n.setPeriodBeforeTime&&this.i18n.setPeriodBeforeTime())?this.getPeriod(r)+" ":" "+this.getPeriod(r),r>12&&(r-=12),0===r&&(r=12));var s=t,a="";return n&&(a=":"+("00"+n).substring(n.length)),""+(o?i:"")+("00"+r.toString()).substring(r.toString().length)+":"+("00"+s.toString()).substring(s.toString().length)+a+(o?"":i)},e.prototype.transformVerbosity=function(e,t){return t||(e=(e=(e=(e=e.replace(new RegExp(", "+this.i18n.everyMinute(),"g"),"")).replace(new RegExp(", "+this.i18n.everyHour(),"g"),"")).replace(new RegExp(this.i18n.commaEveryDay(),"g"),"")).replace(/\, ?$/,"")),e},e.prototype.getPeriod=function(e){return e>=12?this.i18n.pm&&this.i18n.pm()||"PM":this.i18n.am&&this.i18n.am()||"AM"},e.locales={},e}();t.ExpressionDescriptor=o},function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.StringUtilities=void 0;var r=function(){function e(){}return e.format=function(e){for(var t=[],n=1;n<arguments.length;n++)t[n-1]=arguments[n];return e.replace(/%s/g,(function(){return t.shift()}))},e.containsAny=function(e,t){return t.some((function(t){return e.indexOf(t)>-1}))},e}();t.StringUtilities=r},function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.CronParser=void 0;var r=function(){function e(e,t){void 0===t&&(t=!0),this.expression=e,this.dayOfWeekStartIndexZero=t}return e.prototype.parse=function(){var e=this.extractParts(this.expression);return this.normalize(e),this.validate(e),e},e.prototype.extractParts=function(e){if(!this.expression)throw new Error("Expression is empty");var t=e.trim().split(/[ ]+/);if(t.length<5)throw new Error("Expression has only "+t.length+" part"+(1==t.length?"":"s")+". At least 5 parts are required.");if(5==t.length)t.unshift(""),t.push("");else if(6==t.length)/\d{4}$/.test(t[5])?t.unshift(""):t.push("");else if(t.length>7)throw new Error("Expression has "+t.length+" parts; too many!");return t},e.prototype.normalize=function(e){var t=this;if(e[3]=e[3].replace("?","*"),e[5]=e[5].replace("?","*"),e[2]=e[2].replace("?","*"),0==e[0].indexOf("0/")&&(e[0]=e[0].replace("0/","*/")),0==e[1].indexOf("0/")&&(e[1]=e[1].replace("0/","*/")),0==e[2].indexOf("0/")&&(e[2]=e[2].replace("0/","*/")),0==e[3].indexOf("1/")&&(e[3]=e[3].replace("1/","*/")),0==e[4].indexOf("1/")&&(e[4]=e[4].replace("1/","*/")),0==e[6].indexOf("1/")&&(e[6]=e[6].replace("1/","*/")),e[5]=e[5].replace(/(^\d)|([^#/\s]\d)/g,(function(e){var n=e.replace(/\D/,""),r=n;return t.dayOfWeekStartIndexZero?"7"==n&&(r="0"):r=(parseInt(n)-1).toString(),e.replace(n,r)})),"L"==e[5]&&(e[5]="6"),"?"==e[3]&&(e[3]="*"),e[3].indexOf("W")>-1&&(e[3].indexOf(",")>-1||e[3].indexOf("-")>-1))throw new Error("The 'W' character can be specified only when the day-of-month is a single day, not a range or list of days.");var n={SUN:0,MON:1,TUE:2,WED:3,THU:4,FRI:5,SAT:6};for(var r in n)e[5]=e[5].replace(new RegExp(r,"gi"),n[r].toString());var i={JAN:1,FEB:2,MAR:3,APR:4,MAY:5,JUN:6,JUL:7,AUG:8,SEP:9,OCT:10,NOV:11,DEC:12};for(var o in i)e[4]=e[4].replace(new RegExp(o,"gi"),i[o].toString());"0"==e[0]&&(e[0]=""),/\*|\-|\,|\//.test(e[2])||!/\*|\//.test(e[1])&&!/\*|\//.test(e[0])||(e[2]+="-"+e[2]);for(var s=0;s<e.length;s++)if("*/1"==e[s]&&(e[s]="*"),e[s].indexOf("/")>-1&&!/^\*|\-|\,/.test(e[s])){var a=null;switch(s){case 4:a="12";break;case 5:a="6";break;case 6:a="9999";break;default:a=null}if(null!=a){var u=e[s].split("/");e[s]=u[0]+"-"+a+"/"+u[1]}}},e.prototype.validate=function(e){this.assertNoInvalidCharacters("DOW",e[5]),this.assertNoInvalidCharacters("DOM",e[3])},e.prototype.assertNoInvalidCharacters=function(e,t){var n=t.match(/[A-KM-VX-Z]+/gi);if(n&&n.length)throw new Error(e+" part contains invalid values: '"+n.toString()+"'")},e}();t.CronParser=r},function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.en=void 0;var r=function(){function e(){}return e.prototype.atX0SecondsPastTheMinuteGt20=function(){return null},e.prototype.atX0MinutesPastTheHourGt20=function(){return null},e.prototype.commaMonthX0ThroughMonthX1=function(){return null},e.prototype.commaYearX0ThroughYearX1=function(){return null},e.prototype.use24HourTimeFormatByDefault=function(){return!1},e.prototype.anErrorOccuredWhenGeneratingTheExpressionD=function(){return"An error occured when generating the expression description.  Check the cron expression syntax."},e.prototype.everyMinute=function(){return"every minute"},e.prototype.everyHour=function(){return"every hour"},e.prototype.atSpace=function(){return"At "},e.prototype.everyMinuteBetweenX0AndX1=function(){return"Every minute between %s and %s"},e.prototype.at=function(){return"At"},e.prototype.spaceAnd=function(){return" and"},e.prototype.everySecond=function(){return"every second"},e.prototype.everyX0Seconds=function(){return"every %s seconds"},e.prototype.secondsX0ThroughX1PastTheMinute=function(){return"seconds %s through %s past the minute"},e.prototype.atX0SecondsPastTheMinute=function(){return"at %s seconds past the minute"},e.prototype.everyX0Minutes=function(){return"every %s minutes"},e.prototype.minutesX0ThroughX1PastTheHour=function(){return"minutes %s through %s past the hour"},e.prototype.atX0MinutesPastTheHour=function(){return"at %s minutes past the hour"},e.prototype.everyX0Hours=function(){return"every %s hours"},e.prototype.betweenX0AndX1=function(){return"between %s and %s"},e.prototype.atX0=function(){return"at %s"},e.prototype.commaEveryDay=function(){return", every day"},e.prototype.commaEveryX0DaysOfTheWeek=function(){return", every %s days of the week"},e.prototype.commaX0ThroughX1=function(){return", %s through %s"},e.prototype.first=function(){return"first"},e.prototype.second=function(){return"second"},e.prototype.third=function(){return"third"},e.prototype.fourth=function(){return"fourth"},e.prototype.fifth=function(){return"fifth"},e.prototype.commaOnThe=function(){return", on the "},e.prototype.spaceX0OfTheMonth=function(){return" %s of the month"},e.prototype.lastDay=function(){return"the last day"},e.prototype.commaOnTheLastX0OfTheMonth=function(){return", on the last %s of the month"},e.prototype.commaOnlyOnX0=function(){return", only on %s"},e.prototype.commaAndOnX0=function(){return", and on %s"},e.prototype.commaEveryX0Months=function(){return", every %s months"},e.prototype.commaOnlyInX0=function(){return", only in %s"},e.prototype.commaOnTheLastDayOfTheMonth=function(){return", on the last day of the month"},e.prototype.commaOnTheLastWeekdayOfTheMonth=function(){return", on the last weekday of the month"},e.prototype.commaDaysBeforeTheLastDayOfTheMonth=function(){return", %s days before the last day of the month"},e.prototype.firstWeekday=function(){return"first weekday"},e.prototype.weekdayNearestDayX0=function(){return"weekday nearest day %s"},e.prototype.commaOnTheX0OfTheMonth=function(){return", on the %s of the month"},e.prototype.commaEveryX0Days=function(){return", every %s days"},e.prototype.commaBetweenDayX0AndX1OfTheMonth=function(){return", between day %s and %s of the month"},e.prototype.commaOnDayX0OfTheMonth=function(){return", on day %s of the month"},e.prototype.commaEveryHour=function(){return", every hour"},e.prototype.commaEveryX0Years=function(){return", every %s years"},e.prototype.commaStartingX0=function(){return", starting %s"},e.prototype.daysOfTheWeek=function(){return["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"]},e.prototype.monthsOfTheYear=function(){return["January","February","March","April","May","June","July","August","September","October","November","December"]},e}();t.en=r},function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.toString=void 0;var r=n(0),i=n(5);r.ExpressionDescriptor.initialize(new i.enLocaleLoader),t.default=r.ExpressionDescriptor;var o=r.ExpressionDescriptor.toString;t.toString=o},function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.enLocaleLoader=void 0;var r=n(3),i=function(){function e(){}return e.prototype.load=function(e){e.en=new r.en},e}();t.enLocaleLoader=i}])},e.exports=r()},99:function(e,t,n){"use strict";for(var r=document.getElementsByClassName("btn-cdc-port"),i=0;i<r.length;i++)r[i].addEventListener("click",(function(e){document.getElementById("frm-configIqrfCdcForm-IqrfInterface").value=e.currentTarget.dataset.port}))}});
//# sourceMappingURL=config.bundle.js.map