import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ApiService } from '../../services/api.service';
import { AuthService } from '../../services/auth';
import { ServisniZahtev } from '../../models/models';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './dashboard.html',
  styleUrl: './dashboard.scss'
})
export class Dashboard implements OnInit {
  stats: any = {};
  recentZahtevi: ServisniZahtev[] = [];

  constructor(private api: ApiService, public auth: AuthService) {}

  ngOnInit() {
    this.api.getIzvestaj('dashboard').subscribe(data => this.stats = data);

    
    if (this.auth.hasRole('klijent')) {
      this.api.getZahtevi(undefined, undefined, this.auth.userId).subscribe(data => this.recentZahtevi = data.slice(0, 5));
    } else if (this.auth.hasRole('serviser')) {
      this.api.getZahtevi(undefined, this.auth.userId).subscribe(data => this.recentZahtevi = data.slice(0, 5));
    } else {
      this.api.getZahtevi().subscribe(data => this.recentZahtevi = data.slice(0, 5));
    }
  }

  statusClass(status: string): string {
    switch (status) {
      case 'prijavljeno': return 'badge-warning';
      case 'u radu': return 'badge-info';
      case 'rešeno': return 'badge-success';
      case 'fakturisano': return 'badge-neutral';
      default: return '';
    }
  }
}
