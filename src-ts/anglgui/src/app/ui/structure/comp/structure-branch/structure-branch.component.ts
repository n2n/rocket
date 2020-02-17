import { Component, OnInit, Input, OnDestroy } from '@angular/core';
import { UiStructure } from '../../model/ui-structure';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';
import { Subscription } from 'rxjs';

@Component({
	selector: 'rocket-ui-structure-branch',
	templateUrl: './structure-branch.component.html',
	styleUrls: ['./structure-branch.component.css']
})
export class StructureBranchComponent implements OnInit, OnDestroy {
	@Input()
	uiStructure: UiStructure;
	// @Input()
	// uiContent: UiContent|null = null;
	// @Input()
	// childUiStructures: UiStructure[] = [];

	private subscription: Subscription;
	childNodes = new Array<{ uiStructure?: UiStructure, tabContainer?: TabContainer }>();

	constructor() { }

	ngOnInit() {
		this.subscription = this.uiStructure.getContentChildren$().subscribe((contentUiStructures) => {
			this.buildChildNodes(contentUiStructures);
		});
	}

	ngOnDestroy() {
		this.subscription.unsubscribe();
		this.subscription = null;
	}

	private buildChildNodes(contentUiStructures: UiStructure[]) {
		this.childNodes = [];

		let tabContainer: TabContainer|null = null;
		for (const childUiStructure of contentUiStructures) {
			if (childUiStructure.type !== UiStructureType.MAIN_GROUP) {
				tabContainer = null;
				this.childNodes.push({ uiStructure: childUiStructure });
				continue;
			}

			if (tabContainer === null) {
				tabContainer = new TabContainer();
				this.childNodes.push({ tabContainer });
			}

			tabContainer.registerTab(childUiStructure);
		}
	}
}


class TabContainer {
	private tabs: UiStructure[] = [];
	private _availableTabs: UiStructure[] = [];
	private _activeTab: UiStructure|null = null;

	get availableTabs(): UiStructure[] {
		return this._availableTabs;
	}

	get activeTab(): UiStructure {
		return this._activeTab;
	}

	registerTab(uiStructure: UiStructure) {
		this.tabs.push(uiStructure);

		uiStructure.visible = false;

		uiStructure.visible$.subscribe(() => {
			if (uiStructure.visible) {
				this.tabs.filter(child => child !== uiStructure)
						.forEach((child) => { child.visible = false });
			}

			this.valActiveTab();
		});

		uiStructure.disabled$.subscribe(() => {
			this.valActiveTab();
			this._availableTabs = this.tabs.filter(child => !child.disabled);
		});

	}

	private valActiveTab() {
		this._activeTab = null;

		for (const child of this.tabs) {
			if (child.visible && !child.disabled) {
				this._activeTab = child;
				return;
			}
		}

		for (const child of this.tabs) {
			if (!child.disabled) {
				this._activeTab = child;
				return;
			}
		}
	}
}
