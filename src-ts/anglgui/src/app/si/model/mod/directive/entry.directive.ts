import { Directive, Input, ElementRef, DoCheck } from '@angular/core';
import { SiEntry } from '../../content/si-entry';
import { SiModStateService } from '../si-mod-state.service';

@Directive({
  selector: '[rocketUiEntry]'
})
export class EntryDirective implements DoCheck {

	@Input() siEntry: SiEntry;

	private classAdded = false;

	constructor(private elementRef: ElementRef, private modState: SiModStateService) { }

	ngDoCheck() {
		this.chClass(this.modState.containsEntryIdentifier(this.siEntry.identifier));
	}

	private chClass(mod: boolean) {
		if (mod === this.classAdded) {
			return;
		}

		this.classAdded = mod;

		const classList = this.elementRef.nativeElement.classList;
		if (mod) {
			classList.add('rocket-last-mod');
		} else {
			classList.remove('rocket-last-mod');
		}
	}

}
