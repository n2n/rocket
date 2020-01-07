import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { SplitViewMenuModel } from '../split-view-menu-model';

@Component({
	selector: 'rocket-split-view-menu',
	templateUrl: './split-view-menu.component.html',
	styleUrls: ['./split-view-menu.component.css']
})
export class SplitViewMenuComponent implements OnInit {
	model: SplitViewMenuModel;
	menuVisible = false;

	constructor(private cdRef: ChangeDetectorRef) {
	}

	ngOnInit() {
	}

	toggleMenuVisibility() {
		this.menuVisible = !this.menuVisible;
	}

	isKeyVisible(key: string): boolean {
		return -1 < this.model.getVisibleKeys().indexOf(key);
	}

	isKeyMandatory(key: string): boolean {
		return this.isKeyVisible(key) && this.model.getVisibleKeys().length === 1;
	}

	toggleKeyVisibility(key: string) {
		const visibleKeys = this.model.getVisibleKeys();
		const i = visibleKeys.indexOf(key);
		if (i > -1) {
			visibleKeys.splice(i, 1);
		} else {
			visibleKeys.push(key);
		}
		this.model.setVisibleKeys(visibleKeys);
	}

	get activeShortLabel() {
		const visibleKeys = this.model.getVisibleKeys();
		const shortLabels = [];
		for (const splitOption of this.model.getSplitOptions()) {
			if (-1 < visibleKeys.indexOf(splitOption.key)) {
				shortLabels.push(splitOption.shortLabel);
			}
		}
		return shortLabels.join(', ');
	}
}
