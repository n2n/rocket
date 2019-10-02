import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { StructureBranchComponent } from './structure-branch.component';

describe('StructureBranchComponent', () => {
	let component: StructureBranchComponent;
	let fixture: ComponentFixture<StructureBranchComponent>;

	beforeEach(async(() => {
		TestBed.configureTestingModule({
			declarations: [ StructureBranchComponent ]
		})
		.compileComponents();
	}));

	beforeEach(() => {
		fixture = TestBed.createComponent(StructureBranchComponent);
		component = fixture.componentInstance;
		fixture.detectChanges();
	});

	it('should create', () => {
		expect(component).toBeTruthy();
	});
});
