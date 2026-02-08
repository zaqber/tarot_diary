import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { HomeComponent } from './pages/home/home.component';
import { NewSpreadComponent } from './pages/new-spread/new-spread.component';
import { HistoryComponent } from './pages/history/history.component';
import { AnalysisComponent } from './pages/analysis/analysis.component';
import { SettingComponent } from './pages/setting/setting.component';
import { DetailComponent } from './pages/detail/detail.component';

const routes: Routes = [
  { path: '', component: HomeComponent },
  { path: 'home', component: HomeComponent },
  { path: 'new-spread', component: NewSpreadComponent },
  { path: 'history', component: HistoryComponent },
  { path: 'analysis', component: AnalysisComponent },
  { path: 'setting', component: SettingComponent },
  { path: 'detail/:id', component: DetailComponent },
  { path: '**', redirectTo: '' }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
