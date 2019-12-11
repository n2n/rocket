import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SplitViewMenuComponent } from './split-view-menu.component';

describe('SplitViewMenuComponent', () => {
  let component: SplitViewMenuComponent;
  let fixture: ComponentFixture<SplitViewMenuComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SplitViewMenuComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SplitViewMenuComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
