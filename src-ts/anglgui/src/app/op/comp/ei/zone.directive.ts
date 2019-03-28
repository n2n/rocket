import { Directive } from '@angular/core';
import { ViewContainerRef } from "@angular/core";

@Directive({
  selector: '[rocketZone]'
})
export class ZoneDirective {

  constructor(public viewContainerRef: ViewContainerRef) { 
      
  }

}
