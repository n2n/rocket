import { Component, OnInit } from '@angular/core';
import { SplitViewStateService } from '../../model/state/split-view-state.service';
import { SplitModel } from '../split-model';
import { SplitViewStateSubscription } from '../../model/state/split-view-state-subscription';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';

@Component({
	selector: 'rocket-split',
	templateUrl: './split.component.html',
	styleUrls: ['./split.component.css']
})
export class SplitComponent implements OnInit {

    model: SplitModel;
    uiStructure: UiStructure;

	private subscription: SplitViewStateSubscription;

	constructor(private viewStateService: SplitViewStateService) {
	}

	ngOnInit() {
        this.subscription = this.viewStateService.subscribe(this.model.getSplitOptions());
        


	}

	ngOnDestroy() {
		this.subscription.cancel();
    }
    
    sfd() {
        this.subscription;
    }

}
