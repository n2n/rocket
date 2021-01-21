import {
  Component,
  ComponentFactoryResolver,
  ElementRef,
  EventEmitter, HostListener,
  Input, OnChanges,
  OnInit,
  Output,
  ViewChild,
  ViewContainerRef
} from '@angular/core';
import {DomSanitizer, SafeHtml} from "@angular/platform-browser";

@Component({
  selector: 'rocket-iframe',
  templateUrl: './iframe.component.html',
  styleUrls: ['./iframe.component.css']
})
export class IframeComponent implements OnInit, OnChanges {
  @Input()
  public srcDoc;

  @Input()
  public formData: Map<string, string>;

  @Output()
  public formDataChange = new EventEmitter<Map<string, string>>();

  @ViewChild('iframe', {static: false}) iframe: ElementRef;

  constructor(private sanitizer: DomSanitizer) {
  }

  ngOnInit(): void {
    this.appendScriptsToIframeContent();
  }

  private appendScriptsToIframeContent() {
    const script = document.createElement('script');
    script.textContent = this.createResizerJs() + this.createFormDataJs();
    const content = this.srcDoc.concat(script.outerHTML);
    this.srcDoc = this.sanitizer.bypassSecurityTrustHtml(content);
  }

  /**
   * Update and insert formData to iframe
   */
  private createFormDataJs() {
    return 'for(var createFormDataMap=function(e){var t=new Map;console.log(e);for(var n=0;n<e.length;n++){var o=e[n];if("radio"==o.type){if(!o.checked)continue;t.set(o.attributes.name.value,o.value)}if("checkbox"==o.type){if(!o.checked){t.set(o.attributes.name.value,"");continue}t.set(o.attributes.name.value,o.value)}t.set(o.attributes.name.value,e[n].value)}return t},sendFormDataToParent=function(e){window.parent.window.postMessage(createFormDataMap(e),window.parent.location)},formElementsHtmlCollection=document.getElementsByTagName("input"),formElements=[],i=0;i<formElementsHtmlCollection.length;i++)null!=formElementsHtmlCollection[i].attributes.name&&(formElements.push(formElementsHtmlCollection[i]),formElementsHtmlCollection[i].onkeyup=function(){sendFormDataToParent(formElements)},formElementsHtmlCollection[i].onchange=function(){sendFormDataToParent(formElements)});var forms=document.getElementsByTagName("form");for(i=0;i<forms.length;i++)forms[i].onsubmit=function(){return sendFormDataToParent(formElements),!1};window.addEventListener("message",function(e){e.data instanceof Map&&e.data.forEach((e,t)=>{document.getElementsByName(t)[0].value=e})});';
  }

  /**
   *  iFrame Resizer (iframeSizer.contentWindow.min.js) - v4.2.11 - 2020-09-09
   *  Desc: Include this file in any page being loaded into an iframe
   *        to force the iframe to resize to the content size.
   *  Requires: iframeResizer.min.js on host page.
   *  Copyright: (c) 2020 David J. Bradshaw - dave@bradshaw.net
   *  License: MIT
   */
  private createResizerJs() {
    return '!function(u){if("undefined"!=typeof window){var n=!0,o=10,i="",r=0,a="",t=null,c="",s=!1,d={resize:1,click:1},l=128,f=!0,m=1,h="bodyOffset",g=h,p=!0,v="",y={},w=32,b=null,T=!1,E="[iFrameSizer]",O=E.length,S="",M={max:1,min:1,bodyScroll:1,documentElementScroll:1},I="child",N=!0,A=window.parent,C="*",z=0,k=!1,e=null,R=16,x=1,L="scroll",F=L,P=window,D=function(){re("onMessage function not defined")},j=function(){},q=function(){},H={height:function(){return re("Custom height calculation function not defined"),document.documentElement.offsetHeight},width:function(){return re("Custom width calculation function not defined"),document.body.scrollWidth}},W={},B=!1;try{var J=Object.create({},{passive:{get:function(){B=!0}}});window.addEventListener("test",ee,J),window.removeEventListener("test",ee,J)}catch(e){}var U,V,K,Q,X,Y,G=Date.now||function(){return(new Date).getTime()},Z={bodyOffset:function(){return document.body.offsetHeight+pe("marginTop")+pe("marginBottom")},offset:function(){return Z.bodyOffset()},bodyScroll:function(){return document.body.scrollHeight},custom:function(){return H.height()},documentElementOffset:function(){return document.documentElement.offsetHeight},documentElementScroll:function(){return document.documentElement.scrollHeight},max:function(){return Math.max.apply(null,ye(Z))},min:function(){return Math.min.apply(null,ye(Z))},grow:function(){return Z.max()},lowestElement:function(){return Math.max(Z.bodyOffset()||Z.documentElementOffset(),ve("bottom",be()))},taggedElement:function(){return we("bottom","data-iframe-height")}},$={bodyScroll:function(){return document.body.scrollWidth},bodyOffset:function(){return document.body.offsetWidth},custom:function(){return H.width()},documentElementScroll:function(){return document.documentElement.scrollWidth},documentElementOffset:function(){return document.documentElement.offsetWidth},scroll:function(){return Math.max($.bodyScroll(),$.documentElementScroll())},max:function(){return Math.max.apply(null,ye($))},min:function(){return Math.min.apply(null,ye($))},rightMostElement:function(){return ve("right",be())},taggedElement:function(){return we("right","data-iframe-width")}},_=(U=Te,X=null,Y=0,function(){var e=G(),t=R-(e-(Y=Y||e));return V=this,K=arguments,t<=0||R<t?(X&&(clearTimeout(X),X=null),Y=e,Q=U.apply(V,K),X||(V=K=null)):X=X||setTimeout(Ee,t),Q});te(window,"message",function(t){var n={init:function(){v=t.data,A=t.source,ae(),f=!1,setTimeout(function(){p=!1},l)},reset:function(){p?ie("Page reset ignored by init"):(ie("Page size reset by host page"),Me("resetPage"))},resize:function(){Oe("resizeParent","Parent window requested size check")},moveToAnchor:function(){y.findTarget(i())},inPageLink:function(){this.moveToAnchor()},pageInfo:function(){var e=i();ie("PageInfoFromParent called from parent: "+e),q(JSON.parse(e)),ie(" --")},message:function(){var e=i();ie("onMessage called from parent: "+e),D(JSON.parse(e)),ie(" --")}};function o(){return t.data.split("]")[1].split(":")[0]}function i(){return t.data.substr(t.data.indexOf(":")+1)}function r(){return t.data.split(":")[2]in{true:1,false:1}}function e(){var e=o();e in n?n[e]():("undefined"==typeof module||!module.exports)&&"iFrameResize"in window||"jQuery"in window&&"iFrameResize"in window.jQuery.prototype||r()||re("Unexpected message ("+t.data+")")}E===(""+t.data).substr(0,O)&&(!1===f?e():r()?n.init():ie(\'Ignored message of type "\'+o()+\'". Received before initialization.\'))}),te(window,"readystatechange",Ae),Ae()}function ee(){}function te(e,t,n,o){e.addEventListener(t,n,!!B&&(o||{}))}function ne(e){return e.charAt(0).toUpperCase()+e.slice(1)}function oe(e){return E+"["+S+"] "+e}function ie(e){T&&"object"==typeof window.console&&console.log(oe(e))}function re(e){"object"==typeof window.console&&console.warn(oe(e))}function ae(){!function(){function e(e){return"true"===e}var t=v.substr(O).split(":");S=t[0],r=u!==t[1]?Number(t[1]):r,s=u!==t[2]?e(t[2]):s,T=u!==t[3]?e(t[3]):T,w=u!==t[4]?Number(t[4]):w,n=u!==t[6]?e(t[6]):n,a=t[7],g=u!==t[8]?t[8]:g,i=t[9],c=t[10],z=u!==t[11]?Number(t[11]):z,y.enable=u!==t[12]&&e(t[12]),I=u!==t[13]?t[13]:I,F=u!==t[14]?t[14]:F}(),ie("Initialising iFrame ("+window.location.href+")"),function(){function e(e,t){return"function"==typeof e&&(ie("Setup custom "+t+"CalcMethod"),H[t]=e,e="custom"),e}"iFrameResizer"in window&&Object===window.iFrameResizer.constructor&&(function(){var e=window.iFrameResizer;ie("Reading data from page: "+JSON.stringify(e)),Object.keys(e).forEach(ue,e),D="onMessage"in e?e.onMessage:D,j="onReady"in e?e.onReady:j,C="targetOrigin"in e?e.targetOrigin:C,g="heightCalculationMethod"in e?e.heightCalculationMethod:g,F="widthCalculationMethod"in e?e.widthCalculationMethod:F}(),g=e(g,"height"),F=e(F,"width"));ie("TargetOrigin for parent set to: "+C)}(),function(){u===a&&(a=r+"px");ce("margin",function(e,t){-1!==t.indexOf("-")&&(re("Negative CSS value ignored for "+e),t="");return t}("margin",a))}(),ce("background",i),ce("padding",c),function(){var e=document.createElement("div");e.style.clear="both",e.style.display="block",e.style.height="0",document.body.appendChild(e)}(),fe(),me(),document.documentElement.style.height="",document.body.style.height="",ie(\'HTML & body height set to "auto"\'),ie("Enable public methods"),P.parentIFrame={autoResize:function(e){return!0===e&&!1===n?(n=!0,he()):!1===e&&!0===n&&(n=!1,de("remove"),null!==t&&t.disconnect(),clearInterval(b)),Ne(0,0,"autoResize",JSON.stringify(n)),n},close:function(){Ne(0,0,"close")},getId:function(){return S},getPageInfo:function(e){"function"==typeof e?(q=e,Ne(0,0,"pageInfo")):(q=function(){},Ne(0,0,"pageInfoStop"))},moveToAnchor:function(e){y.findTarget(e)},reset:function(){Ie("parentIFrame.reset")},scrollTo:function(e,t){Ne(t,e,"scrollTo")},scrollToOffset:function(e,t){Ne(t,e,"scrollToOffset")},sendMessage:function(e,t){Ne(0,0,"message",JSON.stringify(e),t)},setHeightCalculationMethod:function(e){g=e,fe()},setWidthCalculationMethod:function(e){F=e,me()},setTargetOrigin:function(e){ie("Set targetOrigin: "+e),C=e},size:function(e,t){Oe("size","parentIFrame.size("+((e||"")+(t?","+t:""))+")",e,t)}},he(),y=function(){function r(e){var t=e.getBoundingClientRect(),n={x:window.pageXOffset!==u?window.pageXOffset:document.documentElement.scrollLeft,y:window.pageYOffset!==u?window.pageYOffset:document.documentElement.scrollTop};return{x:parseInt(t.left,10)+parseInt(n.x,10),y:parseInt(t.top,10)+parseInt(n.y,10)}}function n(e){var t,n=e.split("#")[1]||e,o=decodeURIComponent(n),i=document.getElementById(o)||document.getElementsByName(o)[0];u!==i?(t=r(i),ie("Moving to in page link (#"+n+") at x: "+t.x+" y: "+t.y),Ne(t.y,t.x,"scrollToOffset")):(ie("In page link (#"+n+") not found in iFrame, so sending to parent"),Ne(0,0,"inPageLink","#"+n))}function e(){var e=window.location.hash,t=window.location.href;""!==e&&"#"!==e&&n(t)}function t(){Array.prototype.forEach.call(document.querySelectorAll(\'a[href^="#"]\'),function(e){"#"!==e.getAttribute("href")&&te(e,"click",function(e){e.preventDefault(),n(this.getAttribute("href"))})})}y.enable?Array.prototype.forEach&&document.querySelectorAll?(ie("Setting up location.hash handlers"),t(),te(window,"hashchange",e),setTimeout(e,l)):re("In page linking not fully supported in this browser! (See README.md for IE8 workaround)"):ie("In page linking not enabled");return{findTarget:n}}(),Oe("init","Init message from host page"),j()}function ue(e){var t=e.split("Callback");if(2===t.length){var n="on"+t[0].charAt(0).toUpperCase()+t[0].slice(1);this[n]=this[e],delete this[e],re("Deprecated: \'"+e+"\' has been renamed \'"+n+"\'. The old method will be removed in the next major version.")}}function ce(e,t){u!==t&&""!==t&&"null"!==t&&ie("Body "+e+\' set to "\'+(document.body.style[e]=t)+\'"\')}function se(n){var e={add:function(e){function t(){Oe(n.eventName,n.eventType)}W[e]=t,te(window,e,t,{passive:!0})},remove:function(e){var t=W[e];delete W[e],function(e,t,n){e.removeEventListener(t,n,!1)}(window,e,t)}};n.eventNames&&Array.prototype.map?(n.eventName=n.eventNames[0],n.eventNames.map(e[n.method])):e[n.method](n.eventName),ie(ne(n.method)+" event listener: "+n.eventType)}function de(e){se({method:e,eventType:"Animation Start",eventNames:["animationstart","webkitAnimationStart"]}),se({method:e,eventType:"Animation Iteration",eventNames:["animationiteration","webkitAnimationIteration"]}),se({method:e,eventType:"Animation End",eventNames:["animationend","webkitAnimationEnd"]}),se({method:e,eventType:"Input",eventName:"input"}),se({method:e,eventType:"Mouse Up",eventName:"mouseup"}),se({method:e,eventType:"Mouse Down",eventName:"mousedown"}),se({method:e,eventType:"Orientation Change",eventName:"orientationchange"}),se({method:e,eventType:"Print",eventName:["afterprint","beforeprint"]}),se({method:e,eventType:"Ready State Change",eventName:"readystatechange"}),se({method:e,eventType:"Touch Start",eventName:"touchstart"}),se({method:e,eventType:"Touch End",eventName:"touchend"}),se({method:e,eventType:"Touch Cancel",eventName:"touchcancel"}),se({method:e,eventType:"Transition Start",eventNames:["transitionstart","webkitTransitionStart","MSTransitionStart","oTransitionStart","otransitionstart"]}),se({method:e,eventType:"Transition Iteration",eventNames:["transitioniteration","webkitTransitionIteration","MSTransitionIteration","oTransitionIteration","otransitioniteration"]}),se({method:e,eventType:"Transition End",eventNames:["transitionend","webkitTransitionEnd","MSTransitionEnd","oTransitionEnd","otransitionend"]}),"child"===I&&se({method:e,eventType:"IFrame Resized",eventName:"resize"})}function le(e,t,n,o){return t!==e&&(e in n||(re(e+" is not a valid option for "+o+"CalculationMethod."),e=t),ie(o+\' calculation method set to "\'+e+\'"\')),e}function fe(){g=le(g,h,Z,"height")}function me(){F=le(F,L,$,"width")}function he(){!0===n?(de("add"),function(){var e=w<0;window.MutationObserver||window.WebKitMutationObserver?e?ge():t=function(){function t(e){function t(e){!1===e.complete&&(ie("Attach listeners to "+e.src),e.addEventListener("load",i,!1),e.addEventListener("error",r,!1),u.push(e))}"attributes"===e.type&&"src"===e.attributeName?t(e.target):"childList"===e.type&&Array.prototype.forEach.call(e.target.querySelectorAll("img"),t)}function o(e){ie("Remove listeners from "+e.src),e.removeEventListener("load",i,!1),e.removeEventListener("error",r,!1),function(e){u.splice(u.indexOf(e),1)}(e)}function n(e,t,n){o(e.target),Oe(t,n+": "+e.target.src)}function i(e){n(e,"imageLoad","Image loaded")}function r(e){n(e,"imageLoadFailed","Image load failed")}function a(e){Oe("mutationObserver","mutationObserver: "+e[0].target+" "+e[0].type),e.forEach(t)}var u=[],c=window.MutationObserver||window.WebKitMutationObserver,s=function(){var e=document.querySelector("body");return s=new c(a),ie("Create body MutationObserver"),s.observe(e,{attributes:!0,attributeOldValue:!1,characterData:!0,characterDataOldValue:!1,childList:!0,subtree:!0}),s}();return{disconnect:function(){"disconnect"in s&&(ie("Disconnect body MutationObserver"),s.disconnect(),u.forEach(o))}}}():(ie("MutationObserver not supported in this browser!"),ge())}()):ie("Auto Resize disabled")}function ge(){0!==w&&(ie("setInterval: "+w+"ms"),b=setInterval(function(){Oe("interval","setInterval: "+w)},Math.abs(w)))}function pe(e,t){var n=0;return t=t||document.body,n=null!==(n=document.defaultView.getComputedStyle(t,null))?n[e]:0,parseInt(n,o)}function ve(e,t){for(var n=t.length,o=0,i=0,r=ne(e),a=G(),u=0;u<n;u++)i<(o=t[u].getBoundingClientRect()[e]+pe("margin"+r,t[u]))&&(i=o);return a=G()-a,ie("Parsed "+n+" HTML elements"),ie("Element position calculated in "+a+"ms"),function(e){R/2<e&&ie("Event throttle increased to "+(R=2*e)+"ms")}(a),i}function ye(e){return[e.bodyOffset(),e.bodyScroll(),e.documentElementOffset(),e.documentElementScroll()]}function we(e,t){var n=document.querySelectorAll("["+t+"]");return 0===n.length&&(re("No tagged elements ("+t+") found on page"),document.querySelectorAll("body *")),ve(e,n)}function be(){return document.querySelectorAll("body *")}function Te(e,t,n,o){var i,r;function a(e,t){return!(Math.abs(e-t)<=z)}i=u!==n?n:Z[g](),r=u!==o?o:$[F](),a(m,i)||s&&a(x,r)||"init"===e?(Se(),Ne(m=i,x=r,e)):e in{init:1,interval:1,size:1}||!(g in M||s&&F in M)?e in{interval:1}||ie("No change in size detected"):Ie(t)}function Ee(){Y=G(),X=null,Q=U.apply(V,K),X||(V=K=null)}function Oe(e,t,n,o){k&&e in d?ie("Trigger event cancelled: "+e):(e in{reset:1,resetPage:1,init:1}||ie("Trigger event: "+t),"init"===e?Te(e,t,n,o):_(e,t,n,o))}function Se(){k||(k=!0,ie("Trigger event lock on")),clearTimeout(e),e=setTimeout(function(){k=!1,ie("Trigger event lock off"),ie("--")},l)}function Me(e){m=Z[g](),x=$[F](),Ne(m,x,e)}function Ie(e){var t=g;g=h,ie("Reset trigger event: "+e),Se(),Me("reset"),g=t}function Ne(e,t,n,o,i){var r;!0===N&&(u===i?i=C:ie("Message targetOrigin: "+i),ie("Sending message to host page ("+(r=S+":"+(e+":"+t)+":"+n+(u!==o?":"+o:""))+")"),A.postMessage(E+r,i))}function Ae(){"loading"!==document.readyState&&window.parent.postMessage("[iFrameResizerChild]Ready","*")}}();';
  }

  @HostListener('window:message', ['$event'])
  onMessage(event) {
    if (event.data instanceof Map) {
      this.formData = event.data;
      this.formDataChange.emit(event.data);
    }
  }

  ngOnChanges(changes) {
    if (this.iframe == undefined || changes.formData.previousValue == changes.formData.currentValue) return;
    console.log(changes);
    this.iframe.nativeElement.contentWindow.postMessage(this.formData);
  }

  onIframeLoad() {
    this.iframe.nativeElement.contentWindow.postMessage(this.formData);
  }
}
//
// var createFormDataMap = function(formElements) {
//   var formData = new Map();
//   for (var i = 0; i < formElements.length; i++) {
//     var currentElem = formElements[i];
//     if (currentElem.type == "radio") {
//       if (!currentElem.checked) {
//         continue;
//       }
//       formData.set(currentElem.attributes["name"].value, currentElem.value);
//     }
//
//     if (currentElem.type == "checkbox") {
//       if (!currentElem.checked) {
//         formData.set(currentElem.attributes["name"].value, "");
//         continue;
//       }
//       formData.set(currentElem.attributes["name"].value, currentElem.value);
//     }
//
//     formData.set(currentElem.attributes["name"].value, formElements[i].value);
//   }
//
//   return formData;
// }
//
// var sendFormDataToParent = function(formElements) {
//   window.parent.window.postMessage(createFormDataMap(formElements), window.parent.location)
// }
//
// var formElementsHtmlCollection = document.getElementsByTagName("input");
// var formElements = []
// for (var i = 0; i < formElementsHtmlCollection.length; i++) {
//   if (formElementsHtmlCollection[i].attributes["name"] == undefined) continue;
//   formElements.push(formElementsHtmlCollection[i]);
//   formElementsHtmlCollection[i].onkeyup = function () {
//     sendFormDataToParent(formElements);
//   }
//   formElementsHtmlCollection[i].onchange = function () {
//     sendFormDataToParent(formElements);
//   }
// }
//
//
// var forms = document.getElementsByTagName("form");
// for (var i = 0; i < forms.length; i++) {
//   forms[i].onsubmit = function(){
//     sendFormDataToParent(formElements);
//     return false;
//   }
// }
//
// window.addEventListener("message", function(e) {
//   if (e.data instanceof Map) {
//     var formData = e.data;
//     formData.forEach((value, key) => {
//       var formElem = document.getElementsByName(key)[0]; // change to for and check with checkbox and radio values
//       formElem.value = value;
//     });
//   }
// });
