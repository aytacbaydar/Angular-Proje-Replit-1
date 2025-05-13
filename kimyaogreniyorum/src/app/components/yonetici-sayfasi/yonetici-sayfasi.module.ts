import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { OgrenciListesiSayfasiComponent } from './ogrenci-isleri-sayfasi/ogrenci-listesi-sayfasi/ogrenci-listesi-sayfasi.component';
import { OgrenciDetaySayfasiComponent } from './ogrenci-isleri-sayfasi/ogrenci-detay-sayfasi/ogrenci-detay-sayfasi.component';
import { YoneticiIndexSayfasiComponent } from './yonetici-index-sayfasi/yonetici-index-sayfasi.component';
import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { HttpClientModule } from '@angular/common/http';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { AppRoutingModule } from '../../app-routing.module';



@NgModule({
  declarations: [
    OgrenciListesiSayfasiComponent,
    OgrenciDetaySayfasiComponent,
    YoneticiIndexSayfasiComponent,
  ],
  imports: [
    CommonModule,
    AppRoutingModule,
    BrowserAnimationsModule,
    HttpClientModule,
    FormsModule,
    ReactiveFormsModule,
    RouterModule,
  ],
})
export class YoneticiSayfasiModule {}
