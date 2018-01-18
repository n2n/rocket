namespace Rocket.Display {
	
	export class Toggler {
		private closeCallback: (e?: any) => any;
				
		constructor(private buttonJq: JQuery, private menuJq: JQuery) {
			menuJq.hide()
		}
		
		toggle(e?: any) {
			if (this.closeCallback) {
				this.closeCallback(e);
				return;
			}
			
			this.open();
		}
//		
//		set disabled(disabled: boolean) {
//			if (disabled) {
//				this.buttonJq.addClass("disabled");
//			} else {
//				this.buttonJq.removeClass("disabled");
//			}
//		}
//		
//		get disabled(): boolean {
//			return this.buttonJq.hasClass("disabled");
//		}
		
		close() {
			if (!this.closeCallback) return;
			
			this.closeCallback();
		}
		
		open() {
			if (this.closeCallback) return;

			this.menuJq.show();
			this.buttonJq.addClass("active");
			let bodyJq = $("body");
			
			this.closeCallback = (e?: any) => {
				if (e && e.type == "click" && this.menuJq.has(e.target).length > 0) {
					return;
				}

				bodyJq.off("click", this.closeCallback);
				this.menuJq.off("mouseleave", this.closeCallback);
				
				this.closeCallback = null;
				
				this.menuJq.hide();
				this.buttonJq.removeClass("active");
			};
			
			bodyJq.on("click", this.closeCallback);
			this.menuJq.on("mouseleave", this.closeCallback);
		}
		
		static simple(buttonJq: JQuery, menuJq: JQuery): Toggler {
			let toggler = new Toggler(buttonJq, menuJq);
			
			buttonJq.on("click", (e) => {
				e.stopImmediatePropagation();
				toggler.toggle(e);
			});
			
			return toggler;
		}
	}
}