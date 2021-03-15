import {
  AfterViewInit,
  Component, DoCheck,
  ElementRef,
  EventEmitter, HostListener, Inject,
  Input, OnChanges,
  Output, Renderer2,
  ViewChild,
} from '@angular/core';
import {DomSanitizer} from '@angular/platform-browser';

@Component({
  selector: 'rocket-ui-iframe',
  templateUrl: './iframe.component.html',
  styleUrls: ['./iframe.component.css']
})
export class IframeComponent implements OnChanges, DoCheck {
  @Input()
  public srcDoc;

  @Input()
  public formData: Map<string, string>;

  @Output()
  public formDataChange = new EventEmitter<Map<string, string>>();

  private previousFormData: Map<string, string>;

  @ViewChild('iframe', {static: false}) iframe: ElementRef;

  constructor(private sanitizer: DomSanitizer) {}

  ngOnInit(): void {
    this.srcDoc = this.sanitizer.bypassSecurityTrustHtml(this.srcDoc);
  }

  public appendScriptsToIframeContent() {
    if (!this.iframe) return;
    const script = document.createElement('script');
    script.textContent = IframeComponent.createResizerJs() + IframeComponent.createFormDataJs();
    this.iframe.nativeElement.contentWindow.document.getElementsByTagName("body")[0].insertAdjacentElement('beforeend', script);

    this.iframe.nativeElement.contentWindow.postMessage(this.formData);
  }

  /*! iFrame Resizer (iframeSizer.contentWindow.min.js ) - v4.3.1 - 2021-01-11
   *  Desc: Force cross domain iframes to size to content.
   *  Requires: iframeResizer.contentWindow.min.js to be loaded into the target frame.
   *  Copyright: (c) 2021 David J. Bradshaw - dave@bradshaw.net
   *  License: MIT
   */
  public static createResizerJs() {
    return '!function(u){if("undefined"!=typeof window){var n=!0,o=10,i="",r=0,a="",t=null,c="",s=!1,d={resize:1,click:1},l=128,f=!0,m=1,h="bodyOffset",g=h,p=!0,v="",y={},w=32,b=null,T=!1,E=!1,O="[iFrameSizer]",S=O.length,M="",I={max:1,min:1,bodyScroll:1,documentElementScroll:1},N="child",A=!0,C=window.parent,z="*",k=0,R=!1,e=null,x=16,L=1,F="scroll",P=F,D=window,j=function(){ae("onMessage function not defined")},q=function(){},H=function(){},W={height:function(){return ae("Custom height calculation function not defined"),document.documentElement.offsetHeight},width:function(){return ae("Custom width calculation function not defined"),document.body.scrollWidth}},B={},J=!1;try{var U=Object.create({},{passive:{get:function(){J=!0}}});window.addEventListener("test",te,U),window.removeEventListener("test",te,U)}catch(e){}var V,X,Y,K,Q,G,Z=Date.now||function(){return(new Date).getTime()},$={bodyOffset:function(){return document.body.offsetHeight+ve("marginTop")+ve("marginBottom")},offset:function(){return $.bodyOffset()},bodyScroll:function(){return document.body.scrollHeight},custom:function(){return W.height()},documentElementOffset:function(){return document.documentElement.offsetHeight},documentElementScroll:function(){return document.documentElement.scrollHeight},max:function(){return Math.max.apply(null,we($))},min:function(){return Math.min.apply(null,we($))},grow:function(){return $.max()},lowestElement:function(){return Math.max($.bodyOffset()||$.documentElementOffset(),ye("bottom",Te()))},taggedElement:function(){return be("bottom","data-iframe-height")}},_={bodyScroll:function(){return document.body.scrollWidth},bodyOffset:function(){return document.body.offsetWidth},custom:function(){return W.width()},documentElementScroll:function(){return document.documentElement.scrollWidth},documentElementOffset:function(){return document.documentElement.offsetWidth},scroll:function(){return Math.max(_.bodyScroll(),_.documentElementScroll())},max:function(){return Math.max.apply(null,we(_))},min:function(){return Math.min.apply(null,we(_))},rightMostElement:function(){return ye("right",Te())},taggedElement:function(){return be("right","data-iframe-width")}},ee=(V=Ee,Q=null,G=0,function(){var e=Z(),t=x-(e-(G=G||e));return X=this,Y=arguments,t<=0||x<t?(Q&&(clearTimeout(Q),Q=null),G=e,K=V.apply(X,Y),Q||(X=Y=null)):Q=Q||setTimeout(Oe,t),K});ne(window,"message",function(t){var n={init:function(){v=t.data,C=t.source,ue(),f=!1,setTimeout(function(){p=!1},l)},reset:function(){p?re("Page reset ignored by init"):(re("Page size reset by host page"),Ie("resetPage"))},resize:function(){Se("resizeParent","Parent window requested size check")},moveToAnchor:function(){y.findTarget(i())},inPageLink:function(){this.moveToAnchor()},pageInfo:function(){var e=i();re("PageInfoFromParent called from parent: "+e),H(JSON.parse(e)),re(" --")},message:function(){var e=i();re("onMessage called from parent: "+e),j(JSON.parse(e)),re(" --")}};function o(){return t.data.split("]")[1].split(":")[0]}function i(){return t.data.substr(t.data.indexOf(":")+1)}function r(){return t.data.split(":")[2]in{true:1,false:1}}function e(){var e=o();e in n?n[e]():("undefined"==typeof module||!module.exports)&&"iFrameResize"in window||"jQuery"in window&&"iFrameResize"in window.jQuery.prototype||r()||ae("Unexpected message ("+t.data+")")}O===(""+t.data).substr(0,S)&&(!1===f?e():r()?n.init():re(\'Ignored message of type "\'+o()+\'". Received before initialization.\'))}),ne(window,"readystatechange",Ce),Ce()}function te(){}function ne(e,t,n,o){e.addEventListener(t,n,!!J&&(o||{}))}function oe(e){return e.charAt(0).toUpperCase()+e.slice(1)}function ie(e){return O+"["+M+"] "+e}function re(e){T&&"object"==typeof window.console&&console.log(ie(e))}function ae(e){"object"==typeof window.console&&console.warn(ie(e))}function ue(){!function(){function e(e){return"true"===e}var t=v.substr(S).split(":");M=t[0],r=u!==t[1]?Number(t[1]):r,s=u!==t[2]?e(t[2]):s,T=u!==t[3]?e(t[3]):T,w=u!==t[4]?Number(t[4]):w,n=u!==t[6]?e(t[6]):n,a=t[7],g=u!==t[8]?t[8]:g,i=t[9],c=t[10],k=u!==t[11]?Number(t[11]):k,y.enable=u!==t[12]&&e(t[12]),N=u!==t[13]?t[13]:N,P=u!==t[14]?t[14]:P,E=u!==t[15]?Boolean(t[15]):E}(),re("Initialising iFrame ("+window.location.href+")"),function(){function e(e,t){return"function"==typeof e&&(re("Setup custom "+t+"CalcMethod"),W[t]=e,e="custom"),e}"iFrameResizer"in window&&Object===window.iFrameResizer.constructor&&(function(){var e=window.iFrameResizer;re("Reading data from page: "+JSON.stringify(e)),Object.keys(e).forEach(ce,e),j="onMessage"in e?e.onMessage:j,q="onReady"in e?e.onReady:q,z="targetOrigin"in e?e.targetOrigin:z,g="heightCalculationMethod"in e?e.heightCalculationMethod:g,P="widthCalculationMethod"in e?e.widthCalculationMethod:P}(),g=e(g,"height"),P=e(P,"width"));re("TargetOrigin for parent set to: "+z)}(),function(){u===a&&(a=r+"px");se("margin",function(e,t){-1!==t.indexOf("-")&&(ae("Negative CSS value ignored for "+e),t="");return t}("margin",a))}(),se("background",i),se("padding",c),function(){var e=document.createElement("div");e.style.clear="both",e.style.display="block",e.style.height="0",document.body.appendChild(e)}(),me(),he(),document.documentElement.style.height="",document.body.style.height="",re(\'HTML & body height set to "auto"\'),re("Enable public methods"),D.parentIFrame={autoResize:function(e){return!0===e&&!1===n?(n=!0,ge()):!1===e&&!0===n&&(n=!1,le("remove"),null!==t&&t.disconnect(),clearInterval(b)),Ae(0,0,"autoResize",JSON.stringify(n)),n},close:function(){Ae(0,0,"close")},getId:function(){return M},getPageInfo:function(e){"function"==typeof e?(H=e,Ae(0,0,"pageInfo")):(H=function(){},Ae(0,0,"pageInfoStop"))},moveToAnchor:function(e){y.findTarget(e)},reset:function(){Ne("parentIFrame.reset")},scrollTo:function(e,t){Ae(t,e,"scrollTo")},scrollToOffset:function(e,t){Ae(t,e,"scrollToOffset")},sendMessage:function(e,t){Ae(0,0,"message",JSON.stringify(e),t)},setHeightCalculationMethod:function(e){g=e,me()},setWidthCalculationMethod:function(e){P=e,he()},setTargetOrigin:function(e){re("Set targetOrigin: "+e),z=e},size:function(e,t){Se("size","parentIFrame.size("+((e||"")+(t?","+t:""))+")",e,t)}},function(){if(!0!==E)return;function n(e){Ae(0,0,e.type,e.screenY+":"+e.screenX)}function e(e,t){re("Add event listener: "+t),ne(window.document,e,n)}e("mouseenter","Mouse Enter"),e("mouseleave","Mouse Leave")}(),ge(),y=function(){function r(e){var t=e.getBoundingClientRect(),n={x:window.pageXOffset!==u?window.pageXOffset:document.documentElement.scrollLeft,y:window.pageYOffset!==u?window.pageYOffset:document.documentElement.scrollTop};return{x:parseInt(t.left,10)+parseInt(n.x,10),y:parseInt(t.top,10)+parseInt(n.y,10)}}function n(e){var t,n=e.split("#")[1]||e,o=decodeURIComponent(n),i=document.getElementById(o)||document.getElementsByName(o)[0];u!==i?(t=r(i),re("Moving to in page link (#"+n+") at x: "+t.x+" y: "+t.y),Ae(t.y,t.x,"scrollToOffset")):(re("In page link (#"+n+") not found in iFrame, so sending to parent"),Ae(0,0,"inPageLink","#"+n))}function e(){var e=window.location.hash,t=window.location.href;""!==e&&"#"!==e&&n(t)}function t(){Array.prototype.forEach.call(document.querySelectorAll(\'a[href^="#"]\'),function(e){"#"!==e.getAttribute("href")&&ne(e,"click",function(e){e.preventDefault(),n(this.getAttribute("href"))})})}y.enable?Array.prototype.forEach&&document.querySelectorAll?(re("Setting up location.hash handlers"),t(),ne(window,"hashchange",e),setTimeout(e,l)):ae("In page linking not fully supported in this browser! (See README.md for IE8 workaround)"):re("In page linking not enabled");return{findTarget:n}}(),Se("init","Init message from host page"),q()}function ce(e){var t=e.split("Callback");if(2===t.length){var n="on"+t[0].charAt(0).toUpperCase()+t[0].slice(1);this[n]=this[e],delete this[e],ae("Deprecated: \'"+e+"\' has been renamed \'"+n+"\'. The old method will be removed in the next major version.")}}function se(e,t){u!==t&&""!==t&&"null"!==t&&re("Body "+e+\' set to "\'+(document.body.style[e]=t)+\'"\')}function de(n){var e={add:function(e){function t(){Se(n.eventName,n.eventType)}B[e]=t,ne(window,e,t,{passive:!0})},remove:function(e){var t=B[e];delete B[e],function(e,t,n){e.removeEventListener(t,n,!1)}(window,e,t)}};n.eventNames&&Array.prototype.map?(n.eventName=n.eventNames[0],n.eventNames.map(e[n.method])):e[n.method](n.eventName),re(oe(n.method)+" event listener: "+n.eventType)}function le(e){de({method:e,eventType:"Animation Start",eventNames:["animationstart","webkitAnimationStart"]}),de({method:e,eventType:"Animation Iteration",eventNames:["animationiteration","webkitAnimationIteration"]}),de({method:e,eventType:"Animation End",eventNames:["animationend","webkitAnimationEnd"]}),de({method:e,eventType:"Input",eventName:"input"}),de({method:e,eventType:"Mouse Up",eventName:"mouseup"}),de({method:e,eventType:"Mouse Down",eventName:"mousedown"}),de({method:e,eventType:"Orientation Change",eventName:"orientationchange"}),de({method:e,eventType:"Print",eventName:["afterprint","beforeprint"]}),de({method:e,eventType:"Ready State Change",eventName:"readystatechange"}),de({method:e,eventType:"Touch Start",eventName:"touchstart"}),de({method:e,eventType:"Touch End",eventName:"touchend"}),de({method:e,eventType:"Touch Cancel",eventName:"touchcancel"}),de({method:e,eventType:"Transition Start",eventNames:["transitionstart","webkitTransitionStart","MSTransitionStart","oTransitionStart","otransitionstart"]}),de({method:e,eventType:"Transition Iteration",eventNames:["transitioniteration","webkitTransitionIteration","MSTransitionIteration","oTransitionIteration","otransitioniteration"]}),de({method:e,eventType:"Transition End",eventNames:["transitionend","webkitTransitionEnd","MSTransitionEnd","oTransitionEnd","otransitionend"]}),"child"===N&&de({method:e,eventType:"IFrame Resized",eventName:"resize"})}function fe(e,t,n,o){return t!==e&&(e in n||(ae(e+" is not a valid option for "+o+"CalculationMethod."),e=t),re(o+\' calculation method set to "\'+e+\'"\')),e}function me(){g=fe(g,h,$,"height")}function he(){P=fe(P,F,_,"width")}function ge(){!0===n?(le("add"),function(){var e=w<0;window.MutationObserver||window.WebKitMutationObserver?e?pe():t=function(){function t(e){function t(e){!1===e.complete&&(re("Attach listeners to "+e.src),e.addEventListener("load",i,!1),e.addEventListener("error",r,!1),u.push(e))}"attributes"===e.type&&"src"===e.attributeName?t(e.target):"childList"===e.type&&Array.prototype.forEach.call(e.target.querySelectorAll("img"),t)}function o(e){re("Remove listeners from "+e.src),e.removeEventListener("load",i,!1),e.removeEventListener("error",r,!1),function(e){u.splice(u.indexOf(e),1)}(e)}function n(e,t,n){o(e.target),Se(t,n+": "+e.target.src)}function i(e){n(e,"imageLoad","Image loaded")}function r(e){n(e,"imageLoadFailed","Image load failed")}function a(e){Se("mutationObserver","mutationObserver: "+e[0].target+" "+e[0].type),e.forEach(t)}var u=[],c=window.MutationObserver||window.WebKitMutationObserver,s=function(){var e=document.querySelector("body");return s=new c(a),re("Create body MutationObserver"),s.observe(e,{attributes:!0,attributeOldValue:!1,characterData:!0,characterDataOldValue:!1,childList:!0,subtree:!0}),s}();return{disconnect:function(){"disconnect"in s&&(re("Disconnect body MutationObserver"),s.disconnect(),u.forEach(o))}}}():(re("MutationObserver not supported in this browser!"),pe())}()):re("Auto Resize disabled")}function pe(){0!==w&&(re("setInterval: "+w+"ms"),b=setInterval(function(){Se("interval","setInterval: "+w)},Math.abs(w)))}function ve(e,t){var n=0;return t=t||document.body,n=null!==(n=document.defaultView.getComputedStyle(t,null))?n[e]:0,parseInt(n,o)}function ye(e,t){for(var n=t.length,o=0,i=0,r=oe(e),a=Z(),u=0;u<n;u++)i<(o=t[u].getBoundingClientRect()[e]+ve("margin"+r,t[u]))&&(i=o);return a=Z()-a,re("Parsed "+n+" HTML elements"),re("Element position calculated in "+a+"ms"),function(e){x/2<e&&re("Event throttle increased to "+(x=2*e)+"ms")}(a),i}function we(e){return[e.bodyOffset(),e.bodyScroll(),e.documentElementOffset(),e.documentElementScroll()]}function be(e,t){var n=document.querySelectorAll("["+t+"]");return 0===n.length&&(ae("No tagged elements ("+t+") found on page"),document.querySelectorAll("body *")),ye(e,n)}function Te(){return document.querySelectorAll("body *")}function Ee(e,t,n,o){var i,r;function a(e,t){return!(Math.abs(e-t)<=k)}i=u!==n?n:$[g](),r=u!==o?o:_[P](),a(m,i)||s&&a(L,r)||"init"===e?(Me(),Ae(m=i,L=r,e)):e in{init:1,interval:1,size:1}||!(g in I||s&&P in I)?e in{interval:1}||re("No change in size detected"):Ne(t)}function Oe(){G=Z(),Q=null,K=V.apply(X,Y),Q||(X=Y=null)}function Se(e,t,n,o){R&&e in d?re("Trigger event cancelled: "+e):(e in{reset:1,resetPage:1,init:1}||re("Trigger event: "+t),"init"===e?Ee(e,t,n,o):ee(e,t,n,o))}function Me(){R||(R=!0,re("Trigger event lock on")),clearTimeout(e),e=setTimeout(function(){R=!1,re("Trigger event lock off"),re("--")},l)}function Ie(e){m=$[g](),L=_[P](),Ae(m,L,e)}function Ne(e){var t=g;g=h,re("Reset trigger event: "+e),Me(),Ie("reset"),g=t}function Ae(e,t,n,o,i){var r;!0===A&&(u===i?i=z:re("Message targetOrigin: "+i),re("Sending message to host page ("+(r=M+":"+(e+":"+t)+":"+n+(u!==o?":"+o:""))+")"),C.postMessage(O+r,i))}function Ce(){"loading"!==document.readyState&&window.parent.postMessage("[iFrameResizerChild]Ready","*")}}();\n';
  }

  /**
   * Update and insert formData to iframe
   */
  private static createFormDataJs() {
	  return 'for(var createFormDataMap=function(e){for(var t=new Map,n=0;n<e.length;n++){var o=e[n];if("radio"==o.type){if(!o.checked)continue;t.set(o.attributes.name.value,o.value)}if("checkbox"==o.type){if(!o.checked){t.set(o.attributes.name.value,"");continue}t.set(o.attributes.name.value,o.value)}t.set(o.attributes.name.value,e[n].value)}return t},sendFormDataToParent=function(e){window.parent.window.postMessage(createFormDataMap(e),window.parent.location)},formElementsHtmlCollection=document.querySelectorAll("input,textarea"),formElements=[],i=0;i<formElementsHtmlCollection.length;i++)null!=formElementsHtmlCollection[i].attributes.name&&(formElements.push(formElementsHtmlCollection[i]),formElementsHtmlCollection[i].onkeyup=function(){sendFormDataToParent(formElements)},formElementsHtmlCollection[i].onchange=function(){sendFormDataToParent(formElements)});var forms=document.getElementsByTagName("form");for(i=0;i<forms.length;i++)forms[i].onsubmit=function(){return sendFormDataToParent(formElements),!1};window.addEventListener("message",function(e){e.data instanceof Map&&e.data.forEach((e,t)=>{for(var n=document.getElementsByName(t),o=0;o<n.length;o++)n[o].value=e})});';
  }

  @HostListener('window:message', ['$event'])
  onMessage(event) {
    if (event.data instanceof Map) {
      this.formData = event.data;
      this.formDataChange.emit(event.data);
    }
  }

  ngOnChanges(changes) {
    if (this.iframe == undefined || changes.formData.previousValue == changes.formData.currentValue) {
      return;
    }
    this.iframe.nativeElement.contentWindow.postMessage(this.formData);
  }

  ngDoCheck(): void {
    if (this.iframe == undefined || this.previousFormData == this.formData) {
      return;
    }
    this.previousFormData = this.formData;
    this.iframe.nativeElement.contentWindow.postMessage(this.formData);
  }
}

// form data js
// ------------
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
// var formElementsHtmlCollection = document.querySelectorAll("input,textarea"); //Array.from(document.getElementsByTagName("input"));
// var formElements = []
// for (var i = 0; i < formElementsHtmlCollection.length; i++) {
//   if (formElementsHtmlCollection[i].attributes["name"] == undefined) continue;
//   formElements.push(formElementsHtmlCollection[i]);
//   formElementsHtmlCollection[i].onkeyup = function() {
//     sendFormDataToParent(formElements);
//   }
//   formElementsHtmlCollection[i].onchange = function() {
//     sendFormDataToParent(formElements);
//   }
// }
//
//
// var forms = document.getElementsByTagName("form");
// for (var i = 0; i < forms.length; i++) {
//   forms[i].onsubmit = function() {
//     sendFormDataToParent(formElements);
//     return false;
//   }
// }
//
// window.addEventListener("message", function(e) {
//   if (e.data instanceof Map) {
//     var formData = e.data;
//     formData.forEach((value, key) => {
//       var elemsByKey = document.getElementsByName(key);
//       for (var i = 0; i < elemsByKey.length; i++) {
//         elemsByKey[i].value = value;
//       }
//     });
//   }
// });
