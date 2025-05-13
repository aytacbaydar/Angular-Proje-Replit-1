import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';

@Component({
  selector: 'app-onay-sayfasi',
  standalone: false,
  templateUrl: './onay-sayfasi.component.html',
  styleUrl: './onay-sayfasi.component.scss',
})
export class OnaySayfasiComponent implements OnInit {
  constructor(private router: Router) {}

  ngOnInit(): void {
    // Component initialization code
  }

  navigateToLogin(): void {
    this.router.navigate(['/giris-sayfasi']);
  }

  navigateToHome(): void {
    this.router.navigate(['/index-sayfasi']);
  }
}
