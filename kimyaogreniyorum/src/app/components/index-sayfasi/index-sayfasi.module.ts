import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { GirisSayfasiComponent } from './giris-kayit-islemleri-sayfasi/giris-sayfasi/giris-sayfasi.component';
import { KayitSayfasiComponent } from './giris-kayit-islemleri-sayfasi/kayit-sayfasi/kayit-sayfasi.component';
import { AppRoutingModule } from '../../app-routing.module';
import { HttpClientModule } from '@angular/common/http';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { BrowserModule } from '@angular/platform-browser';
import { OnaySayfasiComponent } from './giris-kayit-islemleri-sayfasi/onay-sayfasi/onay-sayfasi.component';



@NgModule({
  declarations: [GirisSayfasiComponent, KayitSayfasiComponent, OnaySayfasiComponent],
  imports: [
    CommonModule,
    ReactiveFormsModule,
    HttpClientModule,
    BrowserModule,
    AppRoutingModule,
    FormsModule,
    
  ],
})
export class IndexSayfasiModule {}
