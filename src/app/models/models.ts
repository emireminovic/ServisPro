export type Uloga = 'administrator' | 'menadzer' | 'serviser' | 'klijent';

export interface User {
  id: number;
  naziv_firme: string;
  adresa: string;
  email: string;
  uloga: Uloga;
}

export type TipUredjaja = 'fiskalna kasa' | 'POS' | 'štampač';
export type StatusUredjaja = 'aktivno' | 'u servisu' | 'neispravno' | 'zamenjeno';

export interface Uredjaj {
  id: number;
  tip: TipUredjaja;
  serijski_broj: string;
  datum_instalacije: string;
  garancija_do: string | null;
  proizvodjac: string | null;
  verzija_softvera: string | null;
  status: StatusUredjaja;
  klijent_id: number;
  klijent_naziv?: string;
}

export type StatusZahteva = 'prijavljeno' | 'u radu' | 'rešeno' | 'fakturisano';

export interface ServisniZahtev {
  id: number;
  opis_kvara: string;
  fotografija: string | null;
  datum_prijave: string;
  status: StatusZahteva;
  trajanje_min: number;
  datum_dolaska: string | null;
  uredjaj_id: number;
  serviser_id: number | null;
  uredjaj_tip?: string;
  serijski_broj?: string;
  klijent_naziv?: string;
  serviser_naziv?: string;
}

export type StatusNaplate = 'plaćeno' | 'neplaćeno';

export interface Faktura {
  id: number;
  datum_izdavanja: string;
  iznos: number;
  materijal: number;
  putni_troskovi: number;
  radni_sati: number;
  status_naplate: StatusNaplate;
  zahtev_id: number;
  opis_kvara?: string;
  klijent_naziv?: string;
}

export interface LicencaSertifikat {
  id: number;
  broj: string;
  datum_izdavanja: string;
  datum_isteka: string;
  proizvodjac: string;
  serviser_id: number;
  serviser_naziv?: string;
  dana_do_isteka?: number;
}

export interface Poruka {
  id: number;
  sadrzaj: string;
  vreme_slanja: string;
  procitano: boolean;
  sender_id: number;
  receiver_id: number;
  sender_naziv?: string;
  receiver_naziv?: string;
}

export interface Obavestenje {
  id: number;
  sadrzaj: string;
  vreme_slanja: string;
  procitano: boolean;
  user_id: number;
  user_naziv?: string;
}

export interface GpsPodaci {
  id: number;
  lokacija: string;
  vreme: string;
  kilometraza: number;
  serviser_id: number;
  serviser_naziv?: string;
}
