import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { AuthService } from '../../services/auth';
import { Uredjaj, User, TipUredjaja } from '../../models/models';

@Component({
  selector: 'app-uredjaji',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './uredjaji.html',
  styleUrl: './uredjaji.scss'
})
export class Uredjaji implements OnInit {
  uredjaji: Uredjaj[] = [];
  klijenti: User[] = [];
  showModal = false;
  filterTip = '';
  totalUredjaja = 0;
  aktivno = 0;
  uServisu = 0;
  neispravno = 0;
  zamenjeno = 0;
  newUredjaj = { tip: 'fiskalna kasa' as TipUredjaja, serijski_broj: '', datum_instalacije: '', garancija_do: '', proizvodjac: '', verzija_softvera: '', klijent_id: 0 };

  constructor(private api: ApiService, public auth: AuthService) {}

  ngOnInit() {
    this.loadData();
    this.api.getKorisnici('klijent').subscribe(d => this.klijenti = d);
  }

  loadData() {
    if (this.auth.hasRole('klijent')) {
      this.api.getUredjaji(this.filterTip || undefined, this.auth.userId).subscribe(d => {
        this.uredjaji = d;
        this.racunajStatistiku(d);
      });
    } else {
      this.api.getUredjaji(this.filterTip || undefined).subscribe(d => {
        this.uredjaji = d;
        this.racunajStatistiku(d);
      });
    }
  }

  racunajStatistiku(d: Uredjaj[]) {
    this.totalUredjaja = d.length;
    this.aktivno = d.filter(u => u.status === 'aktivno').length;
    this.uServisu = d.filter(u => u.status === 'u servisu').length;
    this.neispravno = d.filter(u => u.status === 'neispravno').length;
    this.zamenjeno = d.filter(u => u.status === 'zamenjeno').length;
  }

  statusClass(s: string): string {
    switch (s) {
      case 'aktivno': return 'badge-success';
      case 'u servisu': return 'badge-info';
      case 'neispravno': return 'badge-danger';
      case 'zamenjeno': return 'badge-neutral';
      default: return '';
    }
  }

  addUredjaj() {
    if (!this.newUredjaj.serijski_broj || !this.newUredjaj.klijent_id) return;
    this.api.createUredjaj(this.newUredjaj).subscribe(() => {
      this.showModal = false;
      this.newUredjaj = { tip: 'fiskalna kasa', serijski_broj: '', datum_instalacije: '', garancija_do: '', proizvodjac: '', verzija_softvera: '', klijent_id: 0 };
      this.loadData();
    });
  }
}