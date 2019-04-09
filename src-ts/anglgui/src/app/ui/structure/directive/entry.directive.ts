import { Directive, Input } from '@angular/core';
import { SiEntry } from "src/app/si/model/content/si-entry";
import { ElementRef } from "@angular/core";

@Directive({
  selector: '[rocketUiEntry]'
})
export class EntryDirective {

	@Input() siEntry: SiEntry;
	
	constructor(private elementRef: ElementRef) { }

}
