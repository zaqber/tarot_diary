import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface ReminderSettings {
  timezone: string;
  morning_reminder_time: string;
  evening_reminder_time: string;
  is_morning_reminder_enabled: boolean;
  is_evening_reminder_enabled: boolean;
  telegram_linked: boolean;
}

export interface TelegramLinkResponse {
  deep_link: string;
  expires_at: string | null;
}

@Injectable({
  providedIn: 'root'
})
export class RemindersService {
  private base = '/api/me';

  constructor(private http: HttpClient) {}

  getSettings(): Observable<{ data: ReminderSettings; message?: string }> {
    return this.http.get<{ data: ReminderSettings; message?: string }>(`${this.base}/reminder-settings`);
  }

  updateSettings(body: Partial<ReminderSettings>): Observable<{ data: ReminderSettings; message?: string }> {
    return this.http.patch<{ data: ReminderSettings; message?: string }>(`${this.base}/reminder-settings`, body);
  }

  createTelegramLink(): Observable<{ data: TelegramLinkResponse; message?: string }> {
    return this.http.post<{ data: TelegramLinkResponse; message?: string }>(`${this.base}/telegram/link-token`, {});
  }

  unlinkTelegram(): Observable<{ data: ReminderSettings; message?: string }> {
    return this.http.post<{ data: ReminderSettings; message?: string }>(`${this.base}/telegram/unlink`, {});
  }
}
