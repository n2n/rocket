import { Component, OnInit } from '@angular/core';
import { SplitViewMenuModel } from '../split-view-menu-model';
import { NgModel } from '@angular/forms';

@Component({
	selector: 'rocket-split-view-menu',
	templateUrl: './split-view-menu.component.html',
	styleUrls: ['./split-view-menu.component.css']
})
export class SplitViewMenuComponent implements OnInit {
	model: SplitViewMenuModel;
	menuVisible = false;

	constructor() {
	}

	ngOnInit() {
	}

	toggleMenuVisibility() {
		this.menuVisible = !this.menuVisible;
	}

	isKeyVisible(key: string): boolean {
		return -1 < this.model.getVisibleKeys().indexOf(key);
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
}
