import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { AuthService } from '../../services/auth';
import { ServisniZahtev, Uredjaj, User } from '../../models/models';

@Component({
  selector: 'app-servisni-zahtevi',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './servisni-zahtevi.html',
  styleUrl: './servisni-zahtevi.scss'
})
export class ServisniZahtevi implements OnInit {
  zahtevi: ServisniZahtev[] = [];
  uredjaji: Uredjaj[] = [];
  serviseri: User[] = [];
  showModal = false;
  filterStatus = '';
  newZahtev = { opis_kvara: '', uredjaj_id: 0, serviser_id: null as number | null };

  constructor(private api: ApiService, public auth: AuthService) {}

  ngOnInit() {
    this.loadZahtevi();
    this.api.getUredjaji().subscribe(d => this.uredjaji = d);
    this.api.getKorisnici('serviser').subscribe(d => this.serviseri = d);
  }

  loadZahtevi() {
    if (this.auth.hasRole('klijent')) {
      this.api.getZahtevi(this.filterStatus || undefined, undefined, this.auth.userId).subscribe(d => this.zahtevi = d);
    } else if (this.auth.hasRole('serviser')) {
      this.api.getZahtevi(this.filterStatus || undefined, this.auth.userId).subscribe(d => this.zahtevi = d);
    } else {
      this.api.getZahtevi(this.filterStatus || undefined).subscribe(d => this.zahtevi = d);
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

  addZahtev() {
    if (!this.newZahtev.opis_kvara || !this.newZahtev.uredjaj_id) return;
    this.api.createZahtev(this.newZahtev).subscribe(() => {
      this.showModal = false;
      this.newZahtev = { opis_kvara: '', uredjaj_id: 0, serviser_id: null };
      this.loadZahtevi();
    });
  }

  promeniStatus(zahtev: ServisniZahtev, status: string) {
    this.api.updateZahtev(zahtev.id, { status }).subscribe(() => this.loadZahtevi());
  }
}
