import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EmbeddedEntryInFieldComponent } from './embedded-entry-in-field.component';

describe('EmbeddedEntryInFieldComponent', () => {
  let component: EmbeddedEntryInFieldComponent;
  let fixture: ComponentFixture<EmbeddedEntryInFieldComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EmbeddedEntryInFieldComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EmbeddedEntryInFieldComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
