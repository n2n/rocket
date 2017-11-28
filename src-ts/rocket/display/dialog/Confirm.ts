namespace Rocket.Display {

	export class Confirm {
		dialog: Dialog;
		successCallback: () => any;
		cancelCallback: () => any;
		private stressWindow: StressWindow = null;
		
		constructor(msg: string, okLabel: string, cancelLabel: string) {
			this.dialog = new Dialog(msg);
			this.dialog.addButton({ label: okLabel, callback: () => { 
				this.close();
				if (this.successCallback) {
					this.successCallback();
				}
			}});
			this.dialog.addButton({ label: cancelLabel, callback: () => { 
				this.close();
				if (this.cancelCallback) {
					this.cancelCallback();
				}
			}});
		}
		
		open() {
			this.stressWindow = new StressWindow();
			this.stressWindow.open(this.dialog);
		}
		
		close() {
			if (!this.stressWindow) return;
			
			this.stressWindow.close();
			this.stressWindow = null;
		}
		
		static test(elemJq: JQuery, successCallback?: () => any): Confirm|null {
			if (!elemJq.data("rocket-confirm-msg")) return null;
			
			return Confirm.fromElem(elemJq, successCallback);
		}
		
		static fromElem(elemJq: JQuery, successCallback?: () => any): Confirm {
			let confirm = new Confirm(
					elemJq.data("rocket-confirm-msg") || "Are you sure?",
					elemJq.data("rocket-confirm-ok-label") || "Yes",
					elemJq.data("rocket-confirm-cancel-label") || "No");
			confirm.successCallback = successCallback;
			
			return confirm;
		}
	}
}