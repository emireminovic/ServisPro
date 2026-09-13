import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { AuthService } from '../../services/auth';
import { Poruka, User } from '../../models/models';

@Component({ selector: 'app-poruke', standalone: true, imports: [CommonModule, FormsModule], templateUrl: './poruke.html', styleUrl: './poruke.scss' })
export class Poruke implements OnInit {
  poruke: Poruka[] = [];
  otherUsers: User[] = [];
  newMessage = '';
  selectedReceiverId = 0;

  constructor(private api: ApiService, public auth: AuthService) {}

  ngOnInit() {
    this.loadPoruke();
    this.api.getKorisnici().subscribe(users => this.otherUsers = users.filter(u => u.id !== this.auth.userId));
  }

  loadPoruke() {
    this.api.getPoruke(this.auth.userId).subscribe(d => this.poruke = d);
  }

  isMyMessage(p: Poruka): boolean { return p.sender_id === this.auth.userId; }

  formatTime(vreme: string): string {
    const d = new Date(vreme);
    return `${d.toLocaleDateString('sr-RS')} ${d.toLocaleTimeString('sr-RS', { hour: '2-digit', minute: '2-digit' })}`;
  }

  send() {
    if (!this.newMessage.trim() || !this.selectedReceiverId) return;
    this.api.sendPoruka({ sadrzaj: this.newMessage, sender_id: this.auth.userId, receiver_id: this.selectedReceiverId }).subscribe(() => {
      this.newMessage = '';
      this.loadPoruke();
    });
  }
}
