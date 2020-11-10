import { Directive, Input, ElementRef, DoCheck, OnInit, OnDestroy } from '@angular/core';
import { SiEntry, SiEntryState } from '../../content/si-entry';
import { SiModStateService } from '../model/si-mod-state.service';

@Directive({
  selector: '[rocketUiEntry]'
})
export class EntryDirective implements DoCheck, OnInit, OnDestroy {

	@Input() siEntry: SiEntry;

	private currentlyHighlighted = false;
	private currentState: SiEntryState;

	constructor(private elementRef: ElementRef, private modState: SiModStateService) { }

	ngOnInit() {
		this.modState.registerShownEntry(this.siEntry, this);
	}

	ngOnDestroy() {
		this.modState.unregisterShownEntry(this.siEntry, this);
	}

	ngDoCheck() {
		this.chHighlightedClass(this.modState.lastModEvent
				&& this.modState.lastModEvent.containsModEntryIdentifier(this.siEntry.identifier));
		this.chStateClass(this.siEntry.state);
	}

	private chHighlightedClass(highlighted: boolean) {
		if (highlighted === this.currentlyHighlighted) {
			return;
		}

		this.currentlyHighlighted = highlighted;

		const classList = this.elementRef.nativeElement.classList;
		if (highlighted) {
			classList.add('rocket-highlighted');
		} else {
			classList.remove('rocket-highlighted');
		}
	}

	private chStateClass(state: SiEntryState) {
		if (this.currentState === state) {
			return;
		}

		const classList = this.elementRef.nativeElement.classList;
		classList.remove('rocket-reloading');

		switch (state) {
			case SiEntryState.RELOADING:
				classList.add('rocket-reloading');
				break;
		}
	}

}
