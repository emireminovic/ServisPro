import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../services/api.service';

@Component({ selector: 'app-izvestaji', standalone: true, imports: [CommonModule], templateUrl: './izvestaji.html', styleUrl: './izvestaji.scss' })
export class Izvestaji implements OnInit {
  kvaroviPoTipu: any[] = [];
  kvaroviPoMesecu: any[] = [];
  efikasnost: any[] = [];
  najcesci: any[] = [];
  finansije: any = {};
  prihodPoKlijentu: any[] = [];
  prihodPoServiseru: any[] = [];

  constructor(private api: ApiService) {}

  ngOnInit() {
    this.api.getIzvestaj('kvarovi_po_tipu').subscribe(d => this.kvaroviPoTipu = d);
    this.api.getIzvestaj('kvarovi_po_mesecu').subscribe(d => this.kvaroviPoMesecu = d);
    this.api.getIzvestaj('efikasnost_servisera').subscribe(d => this.efikasnost = d);
    this.api.getIzvestaj('najcesci_kvarovi').subscribe(d => this.najcesci = d);
    this.api.getIzvestaj('finansije').subscribe(d => this.finansije = d);
    this.api.getIzvestaj('prihod_po_klijentu').subscribe(d => this.prihodPoKlijentu = d);
    this.api.getIzvestaj('prihod_po_serviseru').subscribe(d => this.prihodPoServiseru = d);
  }
}
