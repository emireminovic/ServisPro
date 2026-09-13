import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../services/api.service';
import { LicencaSertifikat } from '../../models/models';

@Component({ selector: 'app-licence', standalone: true, imports: [CommonModule], templateUrl: './licence.html', styleUrl: './licence.scss' })
export class Licence implements OnInit {
  licence: LicencaSertifikat[] = [];
  constructor(private api: ApiService) {}
  ngOnInit() { this.api.getLicence().subscribe(d => this.licence = d); }

  expiryClass(days: number): string {
    if (days < 0) return 'badge-danger';
    if (days <= 30) return 'badge-warning';
    return 'badge-success';
  }
  expiryLabel(days: number): string {
    if (days < 0) return `Istekla (pre ${Math.abs(days)} dana)`;
    if (days === 0) return 'Ističe danas';
    return `${days} dana`;
  }
}
