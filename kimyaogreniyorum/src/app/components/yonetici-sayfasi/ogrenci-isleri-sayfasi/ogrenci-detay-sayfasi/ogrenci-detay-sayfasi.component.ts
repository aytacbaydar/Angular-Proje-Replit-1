import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-ogrenci-detay-sayfasi',
  templateUrl: './ogrenci-detay-sayfasi.component.html',
  styleUrls: ['./ogrenci-detay-sayfasi.component.scss']
})
export class OgrenciDetaySayfasiComponent implements OnInit {
  ogrenci: any = null;
  ogrenciForm: FormGroup;
  isSubmitting = false;
  showPassword = false;
  ogrenciId: number;
  avatarPreview: string | null = null;
  avatarFile: File | null = null;

  constructor(
    private http: HttpClient,
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private spinner: NgxSpinnerService,
    private toastr: ToastrService
  ) {
    this.ogrenciId = 0;
    this.ogrenciForm = this.fb.group({
      // Temel Bilgiler
      adi_soyadi: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      sifre: [''],
      rutbe: ['ogrenci'],
      aktif: [true],

      // Detay Bilgiler
      telefon: [''],
      sinif: [''],
      okul: [''],
      adres: [''],
      dogum_tarihi: [''],
      avatar: ['']
    });
  }

  ngOnInit(): void {
    this.spinner.show();
    this.route.params.subscribe(params => {
      this.ogrenciId = +params['id'];
      this.loadOgrenciData();
    });
  }

  loadOgrenciData(): void {
    this.http.get<any>(`/server/api/ogrenci_bilgileri.php?id=${this.ogrenciId}`)
      .subscribe({
        next: (response) => {
          if (response.success && response.data) {
            this.ogrenci = response.data;

            // Form'a verileri doldur
            const temelBilgiler = response.data.temel_bilgiler;
            const detayBilgiler = response.data.detay_bilgiler || {};

            this.ogrenciForm.patchValue({
              // Temel Bilgiler
              adi_soyadi: temelBilgiler.adi_soyadi,
              email: temelBilgiler.email,
              rutbe: temelBilgiler.rutbe,
              aktif: temelBilgiler.aktif === 1,

              // Detay Bilgiler
              telefon: detayBilgiler.telefon || '',
              sinif: detayBilgiler.sinif || '',
              okul: detayBilgiler.okul || '',
              adres: detayBilgiler.adres || '',
              dogum_tarihi: detayBilgiler.dogum_tarihi || ''
            });

            // Avatar önizleme
            if (detayBilgiler.avatar) {
              this.avatarPreview = detayBilgiler.avatar;
            }
          } else {
            this.toastr.error('Öğrenci bilgileri yüklenemedi', 'Hata');
          }
          this.spinner.hide();
        },
        error: (error) => {
          console.error('Öğrenci bilgileri yüklenirken hata:', error);
          this.toastr.error('Öğrenci bilgileri yüklenirken bir sorun oluştu', 'Hata');
          this.spinner.hide();
        }
      });
  }

  onSubmit(): void {
    if (this.ogrenciForm.invalid) {
      this.toastr.warning('Lütfen formu doğru şekilde doldurunuz', 'Uyarı');
      return;
    }

    this.isSubmitting = true;
    this.spinner.show();

    const formValues = this.ogrenciForm.value;
    const formData = this.prepareFormData(formValues);

    const headers = new HttpHeaders({
      'Accept': 'application/json'
    });

    // API'ye gönder
    this.http
      .post<any>('/server/api/ogrenci_profil.php', formData, { headers })
      .subscribe({
        next: (response) => {
          this.isSubmitting = false;
          this.spinner.hide();

          if (response.success) {
            this.toastr.success('Öğrenci bilgileri başarıyla güncellendi', 'Başarılı');
            this.ogrenci = response.data;
          } else {
            this.toastr.error(response.error || 'Bir hata oluştu', 'Hata');
          }
        },
        error: (error) => {
          this.isSubmitting = false;
          this.spinner.hide();
          console.error('Güncelleme hatası:', error);
          this.toastr.error('Öğrenci bilgileri güncellenirken bir hata oluştu', 'Hata');
        }
      });
  }

  onAvatarChange(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      this.avatarFile = input.files[0];

      // Dosya boyutu kontrolü (2MB)
      if (this.avatarFile.size > 2 * 1024 * 1024) {
        this.toastr.warning('Avatar boyutu 2MB\'dan küçük olmalıdır', 'Uyarı');
        this.avatarFile = null;
        return;
      }

      // Önizleme oluştur
      const reader = new FileReader();
      reader.onload = () => {
        this.avatarPreview = reader.result as string;
      };
      reader.readAsDataURL(this.avatarFile);
    }
  }

  prepareFormData(formValues: any): any {
    const data: any = {
      ogrenci_id: this.ogrenciId,
      temel_bilgiler: {},
      detay_bilgiler: {}
    };

    // Temel bilgiler
    data.temel_bilgiler.adi_soyadi = formValues.adi_soyadi;
    data.temel_bilgiler.email = formValues.email;
    data.temel_bilgiler.rutbe = formValues.rutbe;
    data.temel_bilgiler.aktif = formValues.aktif;

    // Şifre boş değilse ekle
    if (formValues.sifre) {
      data.temel_bilgiler.sifre = formValues.sifre;
    }

    // Detay bilgiler
    data.detay_bilgiler.telefon = formValues.telefon;
    data.detay_bilgiler.sinif = formValues.sinif;
    data.detay_bilgiler.okul = formValues.okul;
    data.detay_bilgiler.adres = formValues.adres;
    data.detay_bilgiler.dogum_tarihi = formValues.dogum_tarihi;

    // Avatar
    if (this.avatarPreview) {
      data.detay_bilgiler.avatar = this.avatarPreview;
    }

    return data;
  }

  togglePasswordVisibility(): void {
    this.showPassword = !this.showPassword;
  }

  navigateBack(): void {
    this.router.navigate(['/yonetici-sayfasi/ogrenci-liste-sayfasi']);
  }
}