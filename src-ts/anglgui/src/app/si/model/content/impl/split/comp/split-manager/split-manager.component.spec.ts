import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SplitManagerComponent } from './split-manager.component';

describe('SplitManagerComponent', () => {
  let component: SplitManagerComponent;
  let fixture: ComponentFixture<SplitManagerComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SplitManagerComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SplitManagerComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
