import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { ToastrService } from 'ngx-toastr';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-ogrenci-detay-sayfasi',
  templateUrl: './ogrenci-detay-sayfasi.component.html',
  styleUrls: ['./ogrenci-detay-sayfasi.component.scss']
})
export class OgrenciDetaySayfasiComponent implements OnInit {
  ogrenciId: number = 0;
  ogrenciForm: FormGroup;
  ogrenciBilgileri: any = null;
  isLoading: boolean = true;
  isSubmitting: boolean = false;
  selectedAvatar: string | ArrayBuffer | null = null;

  constructor(
    private route: ActivatedRoute,
    private fb: FormBuilder,
    private http: HttpClient,
    private toastr: ToastrService,
    private spinner: NgxSpinnerService
  ) {
    this.ogrenciForm = this.fb.group({
      ad: ['', Validators.required],
      soyad: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      telefon: ['', Validators.required],
      sinif: ['', Validators.required],
      dogum_tarihi: [''],
      cinsiyet: [''],
      adres: [''],
      il: [''],
      ilce: [''],
      veli_ad_soyad: [''],
      veli_telefon: [''],
      sifre: ['']
    });
  }

  ngOnInit(): void {
    this.route.params.subscribe(params => {
      this.ogrenciId = +params['id']; // Öğrenci ID'sini al
      this.loadOgrenciBilgileri();
    });
  }

  loadOgrenciBilgileri(): void {
    this.isLoading = true;
    this.spinner.show();

    this.http.get<any>(`/server/api/ogrenci_bilgileri.php?id=${this.ogrenciId}`)
      .subscribe({
        next: (response) => {
          if (response.success) {
            this.ogrenciBilgileri = response.data;

            // Form'u doldur
            this.ogrenciForm.patchValue({
              ad: this.ogrenciBilgileri.ad,
              soyad: this.ogrenciBilgileri.soyad,
              email: this.ogrenciBilgileri.email,
              telefon: this.ogrenciBilgileri.telefon,
              sinif: this.ogrenciBilgileri.sinif,
              dogum_tarihi: this.ogrenciBilgileri.detay?.dogum_tarihi || '',
              cinsiyet: this.ogrenciBilgileri.detay?.cinsiyet || '',
              adres: this.ogrenciBilgileri.detay?.adres || '',
              il: this.ogrenciBilgileri.detay?.il || '',
              ilce: this.ogrenciBilgileri.detay?.ilce || '',
              veli_ad_soyad: this.ogrenciBilgileri.detay?.veli_ad_soyad || '',
              veli_telefon: this.ogrenciBilgileri.detay?.veli_telefon || ''
            });
          } else {
            this.toastr.error('Öğrenci bilgileri yüklenemedi', 'Hata');
          }
        },
        error: (error) => {
          console.error('Öğrenci bilgileri yükleme hatası', error);
          this.toastr.error('Öğrenci bilgileri yüklenemedi', 'Hata');
        },
        complete: () => {
          this.isLoading = false;
          this.spinner.hide();
        }
      });
  }

  onFileSelected(event: any): void {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (e: any) => {
        this.selectedAvatar = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  }

  onSubmit(): void {
    if (this.ogrenciForm.invalid) {
      this.toastr.warning('Lütfen formu doğru şekilde doldurun', 'Uyarı');
      return;
    }

    this.isSubmitting = true;
    this.spinner.show();

    const formValues = this.ogrenciForm.value;
    const formData = this.prepareFormData(formValues);

    const headers = new HttpHeaders({
      'Content-Type': 'application/json'
    });

    // API'ye gönder
    this.http
      .post<any>('/server/api/ogrenci_profil.php', formData, { headers })
      .subscribe({
        next: (response) => {
          this.isSubmitting = false;
          this.spinner.hide();

          if (response.success) {
            this.toastr.success('Profil başarıyla güncellendi', 'Başarılı');
            this.loadOgrenciBilgileri(); // Yeniden yükle
          } else {
            this.toastr.error(response.message || 'Güncelleme işlemi başarısız', 'Hata');
          }
        },
        error: (error) => {
          this.isSubmitting = false;
          this.spinner.hide();
          console.error('Profil güncelleme hatası', error);
          this.toastr.error('Güncelleme işlemi başarısız', 'Hata');
        }
      });
  }

  prepareFormData(formValues: any): any {
    const data: any = {
      ogrenci_id: this.ogrenciId,
      temel_bilgiler: {
        ad: formValues.ad,
        soyad: formValues.soyad,
        email: formValues.email,
        telefon: formValues.telefon,
        sinif: formValues.sinif
      },
      detay_bilgiler: {
        dogum_tarihi: formValues.dogum_tarihi,
        cinsiyet: formValues.cinsiyet,
        adres: formValues.adres,
        il: formValues.il,
        ilce: formValues.ilce,
        veli_ad_soyad: formValues.veli_ad_soyad,
        veli_telefon: formValues.veli_telefon
      }
    };

    // Avatar ekle
    if (this.selectedAvatar) {
      data.avatar = this.selectedAvatar;
    }

    // Şifre kontrol et
    if (formValues.sifre) {
      data.temel_bilgiler.sifre = formValues.sifre;
    }

    return data;
  }
}