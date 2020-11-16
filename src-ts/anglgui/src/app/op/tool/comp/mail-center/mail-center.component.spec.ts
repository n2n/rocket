import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MailCenterComponent } from './mail-center.component';

describe('MailCenterComponent', () => {
  let component: MailCenterComponent;
  let fixture: ComponentFixture<MailCenterComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ MailCenterComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(MailCenterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
