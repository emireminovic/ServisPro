import { Injectable } from '@angular/core';
import { User } from '../models/models';
import { ApiService } from './api.service';

@Injectable({ providedIn: 'root' })
export class AuthService {
  currentUser: User | null = null;

  constructor(private api: ApiService) {
    const saved = localStorage.getItem('currentUser');
    if (saved) this.currentUser = JSON.parse(saved);
  }

  login(email: string, lozinka: string): Promise<boolean> {
    return new Promise(resolve => {
      this.api.login(email, lozinka).subscribe({
        next: (res: any) => {
          this.currentUser = res.user;
          localStorage.setItem('currentUser', JSON.stringify(res.user));
          resolve(true);
        },
        error: () => resolve(false)
      });
    });
  }

  logout(): void {
    this.currentUser = null;
    localStorage.removeItem('currentUser');
  }

  isLoggedIn(): boolean {
    return this.currentUser !== null;
  }

  hasRole(...roles: string[]): boolean {
    return this.currentUser ? roles.includes(this.currentUser.uloga) : false;
  }

  get userId(): number {
    return this.currentUser?.id ?? 0;
  }
}
