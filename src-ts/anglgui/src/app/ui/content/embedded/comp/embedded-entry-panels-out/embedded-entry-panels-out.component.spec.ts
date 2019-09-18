import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EmbeddedEntryPanelsOutComponent } from './embedded-entry-panels-out.component';

describe('EmbeddedEntryPanelsOutComponent', () => {
  let component: EmbeddedEntryPanelsOutComponent;
  let fixture: ComponentFixture<EmbeddedEntryPanelsOutComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EmbeddedEntryPanelsOutComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EmbeddedEntryPanelsOutComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
