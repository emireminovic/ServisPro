import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../services/api.service';
import { AuthService } from '../../services/auth';
import { GpsPodaci } from '../../models/models';

@Component({ selector: 'app-gps-pracenje', standalone: true, imports: [CommonModule], templateUrl: './gps-pracenje.html', styleUrl: './gps-pracenje.scss' })
export class GpsPracenje implements OnInit {
  gpsPodaci: GpsPodaci[] = [];
  constructor(private api: ApiService, public auth: AuthService) {}

  ngOnInit() {
    const serviserId = this.auth.hasRole('serviser') ? this.auth.userId : undefined;
    this.api.getGpsPodaci(serviserId).subscribe(d => this.gpsPodaci = d);
  }

  formatTime(v: string): string {
    const d = new Date(v);
    return `${d.toLocaleDateString('sr-RS')} ${d.toLocaleTimeString('sr-RS', { hour: '2-digit', minute: '2-digit' })}`;
  }

  get totalKm(): number { return this.gpsPodaci.reduce((s, g) => s + +g.kilometraza, 0); }
}
