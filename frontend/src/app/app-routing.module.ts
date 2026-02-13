import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { HomeComponent } from './pages/home/home.component';
import { NewSpreadComponent } from './pages/new-spread/new-spread.component';
import { HistoryComponent } from './pages/history/history.component';
import { AnalysisComponent } from './pages/analysis/analysis.component';
import { TarotCardMgmtComponent } from './pages/tarot-card-mgmt/tarot-card-mgmt.component';
import { DetailComponent } from './pages/detail/detail.component';

const routes: Routes = [
  { path: '', component: HomeComponent },
  { path: 'home', component: HomeComponent },
  { path: 'new_spread', component: NewSpreadComponent },
  { path: 'history', component: HistoryComponent },
  { path: 'analysis', component: AnalysisComponent },
  { path: 'tarot_card_mgmt', component: TarotCardMgmtComponent },
  { path: 'detail/:id', component: DetailComponent },
  { path: '**', redirectTo: '' }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
