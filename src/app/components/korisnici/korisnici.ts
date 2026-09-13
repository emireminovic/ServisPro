import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { AuthService } from '../../services/auth';
import { User, Uloga } from '../../models/models';

@Component({ selector: 'app-korisnici', standalone: true, imports: [CommonModule, FormsModule], templateUrl: './korisnici.html', styleUrl: './korisnici.scss' })
export class Korisnici implements OnInit {
  users: User[] = [];
  showModal = false;
  filterRole = '';
  newUser = { naziv_firme: '', adresa: '', email: '', lozinka: '', uloga: 'klijent' as Uloga };

  constructor(private api: ApiService, public auth: AuthService) {}

  ngOnInit() { this.loadData(); }

  loadData() { this.api.getKorisnici(this.filterRole || undefined).subscribe(d => this.users = d); }

  roleClass(r: string): string {
    switch (r) { case 'administrator': return 'badge-danger'; case 'menadzer': return 'badge-info'; case 'serviser': return 'badge-success'; case 'klijent': return 'badge-warning'; default: return ''; }
  }

  addUser() {
    if (!this.newUser.naziv_firme || !this.newUser.email || !this.newUser.lozinka) return;
    this.api.createKorisnik(this.newUser).subscribe(() => {
      this.showModal = false;
      this.newUser = { naziv_firme: '', adresa: '', email: '', lozinka: '', uloga: 'klijent' };
      this.loadData();
    });
  }

  deleteUser(id: number) {
    if (confirm('Da li ste sigurni?')) this.api.deleteKorisnik(id).subscribe(() => this.loadData());
  }
}
