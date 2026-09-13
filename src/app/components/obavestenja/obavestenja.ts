import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../services/api.service';
import { AuthService } from '../../services/auth';
import { Obavestenje } from '../../models/models';

@Component({ selector: 'app-obavestenja', standalone: true, imports: [CommonModule], templateUrl: './obavestenja.html', styleUrl: './obavestenja.scss' })
export class Obavestenja implements OnInit {
  obavestenja: Obavestenje[] = [];
  constructor(private api: ApiService, public auth: AuthService) {}

  ngOnInit() { this.loadData(); }

  loadData() {
    const userId = this.auth.hasRole('administrator', 'menadzer') ? undefined : this.auth.userId;
    this.api.getObavestenja(userId).subscribe(d => this.obavestenja = d);
  }

  formatTime(v: string): string {
    const d = new Date(v);
    return `${d.toLocaleDateString('sr-RS')} ${d.toLocaleTimeString('sr-RS', { hour: '2-digit', minute: '2-digit' })}`;
  }

  markRead(o: Obavestenje) {
    this.api.markObavestenjeRead(o.id).subscribe(() => { o.procitano = true; });
  }

  markAllRead() {
    this.obavestenja.filter(o => !o.procitano).forEach(o => this.markRead(o));
  }
}
