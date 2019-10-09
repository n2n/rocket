import { Directive, Input, ElementRef } from '@angular/core';
import { SiEntry } from '../../../content/si-entry';

@Directive({
  selector: '[rocketUiEntry]'
})
export class EntryDirective {

	@Input() siEntry: SiEntry;

	constructor(private elementRef: ElementRef) { }

}
