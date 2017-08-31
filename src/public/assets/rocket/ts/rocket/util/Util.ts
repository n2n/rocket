namespace rocket.util {
	
	export class CallbackRegistry<C extends Function> {
		private callbackMap: Array<Array<C>> = new Array<Array<C>>();  
		
		public register(nature: string, callback: C) {
			if (this.callbackMap[nature] === undefined) {
				this.callbackMap[nature] = new Array<C>();
			}
			
			this.callbackMap[nature].push(callback);
		}
		
		public unregister(nature: string, callback: C) {
			if (this.callbackMap[nature] === undefined) {
				return;
			}
			
			for (var i in this.callbackMap[nature]) {
				if (this.callbackMap[nature][i] === callback) {
					this.callbackMap[nature].splice(i, 1);
					return;
				}
			}
		}
		
		public filter(nature: string): Array<C> {
			if (this.callbackMap[nature] === undefined) {
				return new Array<C>();
			}
			
			return this.callbackMap[nature];
		}
	}
	
	export class ArgUtils {
		static valIsset(arg) {
			if (arg !== null && arg !== undefined) return;
			
			throw new InvalidArgumentError("Invalid arg: " + arg);
		}
	}
	
	export class InvalidArgumentError extends Error {
	}
	
	export class IllegalStateError extends Error {
		
		static assertTrue(arg, errMsg: string = null) {
			if (arg === true) return true;
			
			throw new IllegalStateError(errMsg);
		}
	}
}