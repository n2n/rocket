import { Directive, Input, ElementRef, DoCheck, OnInit, OnDestroy } from '@angular/core';
import { SiEntry } from '../../content/si-entry';
import { SiModStateService } from '../model/si-mod-state.service';

@Directive({
  selector: '[rocketUiEntry]'
})
export class EntryDirective implements DoCheck, OnInit, OnDestroy {

	@Input() siEntry: SiEntry;

	private classAdded = false;

	constructor(private elementRef: ElementRef, private modState: SiModStateService) { }

	ngOnInit() {
		this.modState.registerShownEntry(this.siEntry, this);
	}

	ngOnDestroy() {
		this.modState.unregisterShownEntry(this.siEntry, this);
	}

	ngDoCheck() {
		this.chClass(this.modState.containsModEntryIdentifier(this.siEntry.identifier));
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
