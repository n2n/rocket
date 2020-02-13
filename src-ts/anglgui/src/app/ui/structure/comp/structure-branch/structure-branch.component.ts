import { Component, OnInit, Input } from '@angular/core';
import { UiStructure } from '../../model/ui-structure';
import { UiContent } from '../../model/ui-content';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';

@Component({
	selector: 'rocket-ui-structure-branch',
	templateUrl: './structure-branch.component.html',
	styleUrls: ['./structure-branch.component.css']
})
export class StructureBranchComponent implements OnInit {
	@Input()
	uiStructure: UiStructure;
	@Input()
	uiContent: UiContent|null = null;
	// @Input()
	childUiStructures: UiStructure[] = [];

	childNodes = new Array<{ uiStructure?: UiStructure, tabContainer?: TabContainer }>();

	constructor() { }

	ngOnInit() {
		this.childUiStructures = this.uiStructure.getContentChildren();

		let tabContainer: TabContainer|null = null;

		for (const childUiStructure of this.childUiStructures) {
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
