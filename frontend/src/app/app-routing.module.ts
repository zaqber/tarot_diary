import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { HomeComponent } from './pages/home/home.component';
import { NewSpreadComponent } from './pages/new-spread/new-spread.component';
import { HistoryComponent } from './pages/history/history.component';
import { AnalysisComponent } from './pages/analysis/analysis.component';
import { TarotCardMgmtComponent } from './pages/tarot-card-mgmt/tarot-card-mgmt.component';
import { DetailComponent } from './pages/detail/detail.component';
import { ReadingDetailComponent } from './pages/reading-detail/reading-detail.component';
import { LoginComponent } from './pages/login/login.component';
import { AuthCallbackComponent } from './pages/auth-callback/auth-callback.component';
import { RemindersComponent } from './pages/reminders/reminders.component';
import { AuthGuard } from './guards/auth.guard';

const routes: Routes = [
  { path: '', component: HomeComponent },
  { path: 'home', component: HomeComponent },
  { path: 'login', component: LoginComponent },
  { path: 'auth/callback', component: AuthCallbackComponent },
  { path: 'new_spread', component: NewSpreadComponent, canActivate: [AuthGuard] },
  { path: 'history', component: HistoryComponent, canActivate: [AuthGuard] },
  { path: 'reading/:id', component: ReadingDetailComponent, canActivate: [AuthGuard] },
  { path: 'analysis', component: AnalysisComponent, canActivate: [AuthGuard] },
  { path: 'reminders', component: RemindersComponent, canActivate: [AuthGuard] },
  { path: 'tarot_card_mgmt', component: TarotCardMgmtComponent, canActivate: [AuthGuard] },
  { path: 'detail/:id', component: DetailComponent, canActivate: [AuthGuard] },
  { path: '**', redirectTo: '' }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
