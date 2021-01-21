import {AfterViewInit, Component, ElementRef, Input, OnInit, SecurityContext, ViewChild} from '@angular/core';
import {DomSanitizer} from "@angular/platform-browser";
import {IframeComponent} from "../iframe/iframe.component";

@Component({
  selector: 'rocket-ui-url-iframe',
  templateUrl: './url-iframe.component.html',
  styleUrls: ['./url-iframe.component.css']
})
export class UrlIframeComponent implements AfterViewInit {
  @ViewChild('urlIframe') urlIframe: ElementRef;

  @Input()
  public srcUrl;

  constructor(private sanitizer: DomSanitizer) {
  }

  ngAfterViewInit() {
    this.appendScriptsToIframeContent();
  }

  private appendScriptsToIframeContent() {
    const script = document.createElement('script');
    script.textContent = IframeComponent.createResizerJs();
    this.urlIframe.nativeElement.insertAdjacentElement('beforeend', script);
  }

  sanitizedUrl() {
    return this.sanitizer.bypassSecurityTrustResourceUrl(this.srcUrl);
  }
}
