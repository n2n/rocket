import { Component, OnInit, ViewChild, ComponentFactoryResolver } from '@angular/core';
import { ZoneDirective } from "src/app/op/comp/ei/zone.directive";
import { ZoneComponent } from "src/app/ui/zone/comp/zone.component";
import { ListZoneComponent } from "src/app/ui/zone/comp/list-zone/list-zone.component";

@Component({
  selector: 'rocket-ei',
  templateUrl: './ei.component.html',
  styleUrls: ['./ei.component.css']
})
export class EiComponent implements OnInit {

    @ViewChild(ZoneDirective) zoneDirective: ZoneDirective;
    
    
    constructor(private componentFactoryResolver: ComponentFactoryResolver) { }

    ngOnInit() {
        const componentFactory = this.componentFactoryResolver.resolveComponentFactory(ListZoneComponent);
        
        const componentRef = this.zoneDirective.viewContainerRef.createComponent(componentFactory);
        
        (<ZoneComponent> componentRef.instance).data = {};
    }
}
