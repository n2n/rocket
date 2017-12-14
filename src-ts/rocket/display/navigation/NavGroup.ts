namespace Rocket.Display {
	export class NavGroup {
		private _navItems: NavItem[];
		private _navItemListHtmlElement: HTMLElement;
		private _titleHtmlElement: HTMLElement;

		public constructor(navItemListHtmlElement: HTMLElement, titleHtmlElement: HTMLElement, navItems: NavItem[]) {
			this.navItemListHtmlElement = navItemListHtmlElement;
			this.titleHtmlElement = titleHtmlElement;
			this._navItems = navItems;
		}

		get navItemListHtmlElement(): HTMLElement {
			return this._navItemListHtmlElement;
		}

		set navItemListHtmlElement(value: HTMLElement) {
			this._navItemListHtmlElement = value;
		}

		get navItems(): Rocket.Display.NavItem[] {
			return this._navItems;
		}

		set navItems(value: Rocket.Display.NavItem[]) {
			this._navItems = value;
		}

		get titleHtmlElement(): HTMLElement {
			return this._titleHtmlElement;
		}

		set titleHtmlElement(value: HTMLElement) {
			this._titleHtmlElement = value;
		}
	}
}