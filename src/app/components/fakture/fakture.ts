import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { AuthService } from '../../services/auth';
import { Faktura } from '../../models/models';

@Component({ selector: 'app-fakture', standalone: true, imports: [CommonModule, FormsModule], templateUrl: './fakture.html', styleUrl: './fakture.scss' })
export class Fakture implements OnInit {
  fakture: Faktura[] = [];
  filterStatus = '';

  constructor(private api: ApiService, public auth: AuthService) {}

  ngOnInit() { this.loadData(); }

  loadData() {
    const klijentId = this.auth.hasRole('klijent') ? this.auth.userId : undefined;
    this.api.getFakture(this.filterStatus || undefined, klijentId).subscribe(d => this.fakture = d);
  }

  get totalPlaceno(): number { return this.fakture.filter(f => f.status_naplate === 'plaćeno').reduce((s, f) => s + +f.iznos, 0); }
  get totalNeplaceno(): number { return this.fakture.filter(f => f.status_naplate === 'neplaćeno').reduce((s, f) => s + +f.iznos, 0); }

  toggleNaplata(f: Faktura) {
    const newStatus = f.status_naplate === 'plaćeno' ? 'neplaćeno' : 'plaćeno';
    this.api.updateFaktura(f.id, { status_naplate: newStatus }).subscribe(() => this.loadData());
  }
}
