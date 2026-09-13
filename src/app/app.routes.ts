import { Routes } from '@angular/router';
import { Dashboard } from './components/dashboard/dashboard';
import { Korisnici } from './components/korisnici/korisnici';
import { Uredjaji } from './components/uredjaji/uredjaji';
import { ServisniZahtevi } from './components/servisni-zahtevi/servisni-zahtevi';
import { Fakture } from './components/fakture/fakture';
import { Licence } from './components/licence/licence';
import { Poruke } from './components/poruke/poruke';
import { Obavestenja } from './components/obavestenja/obavestenja';
import { GpsPracenje } from './components/gps-pracenje/gps-pracenje';
import { Izvestaji } from './components/izvestaji/izvestaji';

export const routes: Routes = [
  { path: '', redirectTo: 'dashboard', pathMatch: 'full' },
  { path: 'dashboard', component: Dashboard },
  { path: 'korisnici', component: Korisnici },
  { path: 'uredjaji', component: Uredjaji },
  { path: 'servisni-zahtevi', component: ServisniZahtevi },
  { path: 'fakture', component: Fakture },
  { path: 'licence', component: Licence },
  { path: 'poruke', component: Poruke },
  { path: 'obavestenja', component: Obavestenja },
  { path: 'gps', component: GpsPracenje },
  { path: 'izvestaji', component: Izvestaji },
];
