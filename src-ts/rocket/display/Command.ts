namespace Rocket.Display { 
	
	export class Command {
		private jLink: Jhtml.Ui.Link;
		private _observing: boolean = false;
		
        constructor(jLink: Jhtml.Ui.Link) {
            this.jLink = jLink;
        }
        
        observe() {
            if (this._observing) return;
            
            this._observing = true;
            this.jLink.onDirective((directivePromise) => {
                this.handle(directivePromise);
            });
        }
        
        private handle(directivePromise: Promise<Jhtml.Directive>) {
            let jqElem = $(this.jLink.element);
            let iJq = jqElem.find("i");
            let orgClassAttr = iJq.attr("class");
            iJq.attr("class", "fa fa-circle-o-notch fa-spin");
            jqElem.css("cursor", "default");
            this.jLink.disabled = true;
            directivePromise.then(directive => {
                iJq.attr("class", orgClassAttr);
                this.jLink.disabled = false;
                let revt = RocketEvent.fromAdditionalData(directive.getAdditionalData());
                
                if (!revt.swapControlHtml) return;
                let jqNewElem = $(revt.swapControlHtml);
                jqElem.replaceWith(jqNewElem);
                this.jLink.dispose();
                this.jLink = Jhtml.Ui.Link.from(<HTMLAnchorElement> jqNewElem.get(0));
                this._observing = false;
                this.observe();
            });
        }
    }
	
	export class RocketEvent {
		swapControlHtml: string = null;
		
        static fromAdditionalData(data: any): RocketEvent {
            let rocketEvent = new RocketEvent();
            if (!data || !data.rocketEvent) {
                return rocketEvent;            	
            }
            
            if (data.rocketEvent.swapControlHtml) {
                rocketEvent.swapControlHtml = data.rocketEvent.swapControlHtml;
            }
            return rocketEvent;
        }
    }
}