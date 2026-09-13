import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../../services/auth';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './login.html',
  styleUrl: './login.scss'
})
export class Login {
  email = '';
  lozinka = '';
  error = '';
  loading = false;

  constructor(public auth: AuthService) {}

  async login() {
    if (!this.email || !this.lozinka) {
      this.error = 'Unesite email i lozinku.';
      return;
    }
    this.loading = true;
    this.error = '';
    const success = await this.auth.login(this.email, this.lozinka);
    this.loading = false;
    if (!success) this.error = 'Pogrešan email ili lozinka.';
  }
}
