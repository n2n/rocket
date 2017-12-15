namespace Rocket.Display {
	export class NavGroup {
		private _id: string;
		private _navItems: NavItem[];
		private _navItemListHtmlElement: HTMLElement;
		private _titleHtmlElement: HTMLElement;

		public constructor(navItemListHtmlElement: HTMLElement, titleHtmlElement: HTMLElement, navItems: NavItem[]) {
			this.navItemListHtmlElement = navItemListHtmlElement;
			this.titleHtmlElement = titleHtmlElement;
			this._navItems = navItems;
			this._id = this.buildNavGroupId();
		}

		public open(instant: boolean = false) {
			let titleElemJquery = $(this.titleHtmlElement);
			let iconJquery = titleElemJquery.find('i');
			iconJquery.removeClass('fa-plus');
			iconJquery.addClass('fa-minus');

			let ulElemJquery = $(this.navItemListHtmlElement);
			if (instant) {
				ulElemJquery.slideDown({duration: 0});
				return;
			}

			ulElemJquery.slideDown({duration: "fast"});
		}

		public close(instant: boolean = false) {
			let titleElemJquery = $(this.titleHtmlElement);
			let iconJquery = titleElemJquery.find('i');
			iconJquery.removeClass('fa-minus');
			iconJquery.addClass('fa-plus');


			let ulElemJquery = $(this.navItemListHtmlElement);
			if (instant) {
				ulElemJquery.slideUp({duration: 0});
				return;
			}

			ulElemJquery.slideUp({duration: "fast"});
		}

		private buildNavGroupId() {
			return this._titleHtmlElement.innerText.toLowerCase().replace(" ", "-");
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

		get id(): string {
			return this._id;
		}

		set id(value: string) {
			this._id = value;
		}
	}
}