import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { GirisSayfasiComponent } from './components/index-sayfasi/giris-kayit-islemleri-sayfasi/giris-sayfasi/giris-sayfasi.component';
import { KayitSayfasiComponent } from './components/index-sayfasi/giris-kayit-islemleri-sayfasi/kayit-sayfasi/kayit-sayfasi.component';
import { OnaySayfasiComponent } from './components/index-sayfasi/giris-kayit-islemleri-sayfasi/onay-sayfasi/onay-sayfasi.component';
import { YoneticiIndexSayfasiComponent } from './components/yonetici-sayfasi/yonetici-index-sayfasi/yonetici-index-sayfasi.component';
import { OgrenciListesiSayfasiComponent } from './components/yonetici-sayfasi/ogrenci-isleri-sayfasi/ogrenci-listesi-sayfasi/ogrenci-listesi-sayfasi.component';
import { OgrenciDetaySayfasiComponent } from './components/yonetici-sayfasi/ogrenci-isleri-sayfasi/ogrenci-detay-sayfasi/ogrenci-detay-sayfasi.component';

export const routes: Routes = [
  { path: '', component: GirisSayfasiComponent },
  { path: 'giris-sayfasi', component: GirisSayfasiComponent },
  { path: 'kayit-sayfasi', component: KayitSayfasiComponent },
  { path: 'onay-sayfasi', component: OnaySayfasiComponent },

  //yönetici sayfaları
  {
    path: 'yonetici-sayfasi', component: YoneticiIndexSayfasiComponent, children: [
    {path: 'ogrenci-liste-sayfasi', component: OgrenciListesiSayfasiComponent},
    {path: 'ogrenci-detay-sayfasi/:id', component: OgrenciDetaySayfasiComponent},
  ]},
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule],
})
export class AppRoutingModule { }
