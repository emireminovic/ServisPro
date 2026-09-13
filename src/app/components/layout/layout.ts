import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { AuthService } from '../../services/auth';
import { ApiService } from '../../services/api.service';
import { Login } from '../login/login';

@Component({
  selector: 'app-layout',
  standalone: true,
  imports: [CommonModule, RouterModule, Login],
  templateUrl: './layout.html',
  styleUrl: './layout.scss'
})
export class Layout {
  sidebarCollapsed = false;
  unreadCount = 0;

  allNavItems = [
    { path: '/dashboard',        icon: '📊', label: 'Kontrolna tabla',      roles: ['administrator','menadzer','serviser','klijent'] },
    { path: '/servisni-zahtevi', icon: '🔧', label: 'Servisni zahtevi',     roles: ['administrator','menadzer','serviser','klijent'] },
    { path: '/uredjaji',         icon: '🖥️', label: 'Uređaji',             roles: ['administrator','menadzer','serviser','klijent'] },
    { path: '/korisnici',        icon: '👥', label: 'Korisnici',            roles: ['administrator','menadzer'] },
    { path: '/fakture',          icon: '📄', label: 'Fakture',              roles: ['administrator','menadzer','klijent'] },
    { path: '/licence',          icon: '📜', label: 'Licence i sertifikati',roles: ['administrator','menadzer','serviser'] },
    { path: '/izvestaji',        icon: '📈', label: 'Izveštaji',            roles: ['administrator','menadzer'] },
    { path: '/poruke',           icon: '💬', label: 'Poruke',               roles: ['administrator','menadzer','serviser'] },
    { path: '/obavestenja',      icon: '🔔', label: 'Obaveštenja',          roles: ['administrator','menadzer','serviser','klijent'] },
    { path: '/gps',              icon: '📍', label: 'GPS praćenje',         roles: ['administrator','menadzer','serviser'] },
  ];

  constructor(public auth: AuthService, private api: ApiService) {}

  get navItems() {
    if (!this.auth.currentUser) return [];
    return this.allNavItems.filter(item => item.roles.includes(this.auth.currentUser!.uloga));
  }

  logout() { this.auth.logout(); }
}
