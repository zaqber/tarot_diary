import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';
import { FormsModule } from '@angular/forms';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { HeaderComponent } from './components/header/header.component';
import { FooterComponent } from './components/footer/footer.component';
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
import { AuthInterceptor } from './interceptors/auth.interceptor';
import { MarkdownToHtmlPipe } from './pipes/markdown-to-html.pipe';

@NgModule({
  declarations: [
    MarkdownToHtmlPipe,
    AppComponent,
    HeaderComponent,
    FooterComponent,
    HomeComponent,
    NewSpreadComponent,
    HistoryComponent,
    ReadingDetailComponent,
    AnalysisComponent,
    TarotCardMgmtComponent,
    DetailComponent,
    LoginComponent,
    AuthCallbackComponent,
    RemindersComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    HttpClientModule,
    FormsModule
  ],
  providers: [
    { provide: HTTP_INTERCEPTORS, useClass: AuthInterceptor, multi: true }
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
