import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { FieldMessagesComponent } from './field-messages.component';

describe('FieldMessagesComponent', () => {
  let component: FieldMessagesComponent;
  let fixture: ComponentFixture<FieldMessagesComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ FieldMessagesComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(FieldMessagesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
