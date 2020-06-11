import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { UploadResultMessageComponent } from './upload-result-message.component';

describe('UploadResultMessageComponent', () => {
  let component: UploadResultMessageComponent;
  let fixture: ComponentFixture<UploadResultMessageComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ UploadResultMessageComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(UploadResultMessageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
