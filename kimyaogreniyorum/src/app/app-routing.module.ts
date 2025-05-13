import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { GirisSayfasiComponent } from './components/index-sayfasi/giris-kayit-islemleri-sayfasi/giris-sayfasi/giris-sayfasi.component';
import { KayitSayfasiComponent } from './components/index-sayfasi/giris-kayit-islemleri-sayfasi/kayit-sayfasi/kayit-sayfasi.component';
import { OnaySayfasiComponent } from './components/index-sayfasi/giris-kayit-islemleri-sayfasi/onay-sayfasi/onay-sayfasi.component';

const routes: Routes = [
  { path: '', component: GirisSayfasiComponent },
  { path: 'giris-sayfasi', component: GirisSayfasiComponent },
  { path: 'kayit-sayfasi', component: KayitSayfasiComponent },
  { path: 'onay-sayfasi', component: OnaySayfasiComponent },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
