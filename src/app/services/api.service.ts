import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import {
  User, Uredjaj, ServisniZahtev, Faktura,
  LicencaSertifikat, Poruka, Obavestenje, GpsPodaci
} from '../models/models';

@Injectable({ providedIn: 'root' })
export class ApiService {
  private api = environment.apiUrl;

  constructor(private http: HttpClient) {}

  // AUTH
  login(email: string, lozinka: string): Observable<any> {
    return this.http.post(`${this.api}/auth.php`, { email, lozinka });
  }

  // KORISNICI
  getKorisnici(uloga?: string): Observable<User[]> {
    let params = new HttpParams();
    if (uloga) params = params.set('uloga', uloga);
    return this.http.get<User[]>(`${this.api}/korisnici.php`, { params });
  }
  getKorisnik(id: number): Observable<User> {
    return this.http.get<User>(`${this.api}/korisnici.php?id=${id}`);
  }
  createKorisnik(data: any): Observable<any> {
    return this.http.post(`${this.api}/korisnici.php`, data);
  }
  updateKorisnik(id: number, data: any): Observable<any> {
    return this.http.put(`${this.api}/korisnici.php?id=${id}`, data);
  }
  deleteKorisnik(id: number): Observable<any> {
    return this.http.delete(`${this.api}/korisnici.php?id=${id}`);
  }

  // UREDJAJI
  getUredjaji(tip?: string, klijentId?: number): Observable<Uredjaj[]> {
    let params = new HttpParams();
    if (tip) params = params.set('tip', tip);
    if (klijentId) params = params.set('klijent_id', klijentId.toString());
    return this.http.get<Uredjaj[]>(`${this.api}/uredjaji.php`, { params });
  }
  createUredjaj(data: any): Observable<any> {
    return this.http.post(`${this.api}/uredjaji.php`, data);
  }
  updateUredjaj(id: number, data: any): Observable<any> {
    return this.http.put(`${this.api}/uredjaji.php?id=${id}`, data);
  }
  deleteUredjaj(id: number): Observable<any> {
    return this.http.delete(`${this.api}/uredjaji.php?id=${id}`);
  }

  // SERVISNI ZAHTEVI
  getZahtevi(status?: string, serviserId?: number, klijentId?: number): Observable<ServisniZahtev[]> {
    let params = new HttpParams();
    if (status) params = params.set('status', status);
    if (serviserId) params = params.set('serviser_id', serviserId.toString());
    if (klijentId) params = params.set('klijent_id', klijentId.toString());
    return this.http.get<ServisniZahtev[]>(`${this.api}/zahtevi.php`, { params });
  }
  createZahtev(data: any): Observable<any> {
    return this.http.post(`${this.api}/zahtevi.php`, data);
  }
  updateZahtev(id: number, data: any): Observable<any> {
    return this.http.put(`${this.api}/zahtevi.php?id=${id}`, data);
  }
  deleteZahtev(id: number): Observable<any> {
    return this.http.delete(`${this.api}/zahtevi.php?id=${id}`);
  }

  // FAKTURE
  getFakture(statusNaplate?: string, klijentId?: number): Observable<Faktura[]> {
    let params = new HttpParams();
    if (statusNaplate) params = params.set('status_naplate', statusNaplate);
    if (klijentId) params = params.set('klijent_id', klijentId.toString());
    return this.http.get<Faktura[]>(`${this.api}/fakture.php`, { params });
  }
  createFaktura(data: any): Observable<any> {
    return this.http.post(`${this.api}/fakture.php`, data);
  }
  updateFaktura(id: number, data: any): Observable<any> {
    return this.http.put(`${this.api}/fakture.php?id=${id}`, data);
  }

  // LICENCE
  getLicence(serviserId?: number, istice?: number): Observable<LicencaSertifikat[]> {
    let params = new HttpParams();
    if (serviserId) params = params.set('serviser_id', serviserId.toString());
    if (istice) params = params.set('istice', istice.toString());
    return this.http.get<LicencaSertifikat[]>(`${this.api}/licence.php`, { params });
  }
  createLicenca(data: any): Observable<any> {
    return this.http.post(`${this.api}/licence.php`, data);
  }
  updateLicenca(id: number, data: any): Observable<any> {
    return this.http.put(`${this.api}/licence.php?id=${id}`, data);
  }

  // PORUKE
  getPoruke(userId: number): Observable<Poruka[]> {
    return this.http.get<Poruka[]>(`${this.api}/poruke.php?user_id=${userId}`);
  }
  sendPoruka(data: any): Observable<any> {
    return this.http.post(`${this.api}/poruke.php`, data);
  }

  // OBAVESTENJA
  getObavestenja(userId?: number): Observable<Obavestenje[]> {
    let params = new HttpParams();
    if (userId) params = params.set('user_id', userId.toString());
    return this.http.get<Obavestenje[]>(`${this.api}/obavestenja.php`, { params });
  }
  markObavestenjeRead(id: number): Observable<any> {
    return this.http.put(`${this.api}/obavestenja.php?id=${id}`, {});
  }

  // GPS
  getGpsPodaci(serviserId?: number): Observable<GpsPodaci[]> {
    let params = new HttpParams();
    if (serviserId) params = params.set('serviser_id', serviserId.toString());
    return this.http.get<GpsPodaci[]>(`${this.api}/gps.php`, { params });
  }
  createGpsZapis(data: any): Observable<any> {
    return this.http.post(`${this.api}/gps.php`, data);
  }

  // IZVESTAJI
  getIzvestaj(type: string): Observable<any> {
    return this.http.get(`${this.api}/izvestaji.php?type=${type}`);
  }
}
