import { Directive, DoCheck, ElementRef, Input, OnDestroy, OnInit } from '@angular/core';
import { SiEntryState, SiValueBoundary } from '../../content/si-value-boundary';
import { SiModStateService } from '../model/si-mod-state.service';

@Directive({
	selector: '[rocketSiEntry]'
})
export class EntryDirective implements DoCheck, OnInit, OnDestroy {

	private _siValueBoundary?: SiValueBoundary;

	private currentlyHighlighted = false;
	private currentState?: SiEntryState;

	constructor(private elementRef: ElementRef, private modState: SiModStateService) { }

	ngOnInit() {
	}

	ngOnDestroy() {
		this.modState.unregisterShownEntry(this.siValueBoundary, this);
	}

	@Input()
	set siValueBoundary(siValueBoundary: SiValueBoundary) {
		if (this._siValueBoundary) {
			this.modState.unregisterShownEntry(this._siValueBoundary, this);
		}

		this._siValueBoundary = siValueBoundary;
		this.modState.registerShownEntry(siValueBoundary, this);
	}

	get siValueBoundary(): SiValueBoundary {
		return this._siValueBoundary!;
	}

	ngDoCheck() {
		this.chHighlightedClass(this.modState.lastModEvent !== null
				&& this.modState.lastModEvent.containsModEntryIdentifier(this.siValueBoundary.identifier));
		this.chStateClass(this.siValueBoundary.state);
	}



	private chHighlightedClass(highlighted: boolean) {
		if (highlighted === this.currentlyHighlighted) {
			return;
		}

		this.currentlyHighlighted = highlighted;

		const classList = this.elementRef.nativeElement.classList;
		if (highlighted) {
			classList.add('rocket-highlighed');
		} else {
			classList.remove('rocket-highlighed');
		}
	}

	private chStateClass(state: SiEntryState) {
		if (this.currentState === state) {
			return;
		}

		this.currentState = state;

		const classList = this.elementRef.nativeElement.classList;
		classList.remove('rocket-reloading');
		classList.remove('rocket-locked');
		classList.remove('rocket-outdated');
		classList.remove('rocket-removed');

		switch (state) {
			case SiEntryState.RELOADING:
				classList.add('rocket-reloading');
				break;
			case SiEntryState.LOCKED:
				classList.add('rocket-locked');
				break;
			case SiEntryState.OUTDATED:
			case SiEntryState.REPLACED:
				classList.add('rocket-outdated');
				break;
			case SiEntryState.REMOVED:
				classList.add('rocket-removed');
				break;
		}
	}

}
