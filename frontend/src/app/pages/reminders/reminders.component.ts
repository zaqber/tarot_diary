import { Component, OnInit } from '@angular/core';
import { RemindersService, ReminderSettings } from '../../services/reminders.service';

const COMMON_TIMEZONES = [
  'Asia/Taipei',
  'Asia/Tokyo',
  'Asia/Seoul',
  'Asia/Shanghai',
  'Asia/Hong_Kong',
  'Asia/Singapore',
  'UTC',
  'Europe/London',
  'America/Los_Angeles',
  'America/New_York'
];

@Component({
  selector: 'app-reminders',
  templateUrl: './reminders.component.html',
  styleUrls: ['./reminders.component.css']
})
export class RemindersComponent implements OnInit {
  private readonly baseTimezones = COMMON_TIMEZONES;

  loading = true;
  saving = false;
  errorMessage = '';
  successMessage = '';

  settings: ReminderSettings | null = null;

  timezone = 'Asia/Taipei';
  morningTime = '08:00';
  eveningTime = '20:00';
  morningEnabled = true;
  eveningEnabled = true;

  telegramLinkLoading = false;
  telegramUnlinkLoading = false;
  deepLink = '';
  linkExpiresAt = '';

  constructor(private remindersService: RemindersService) {}

  ngOnInit(): void {
    this.load();
  }

  /** 若使用者時區不在常見清單，仍顯示於下拉選單第一項 */
  get timezoneOptions(): string[] {
    const tz = this.timezone;
    if (tz && !this.baseTimezones.includes(tz)) {
      return [tz, ...this.baseTimezones];
    }
    return [...this.baseTimezones];
  }

  load(): void {
    this.loading = true;
    this.errorMessage = '';
    this.remindersService.getSettings().subscribe({
      next: res => {
        this.applySettings(res.data);
        this.loading = false;
      },
      error: () => {
        this.errorMessage = '無法載入提醒設定';
        this.loading = false;
      }
    });
  }

  private applySettings(data: ReminderSettings): void {
    this.settings = data;
    this.timezone = data.timezone || 'Asia/Taipei';
    this.morningTime = data.morning_reminder_time || '08:00';
    this.eveningTime = data.evening_reminder_time || '20:00';
    this.morningEnabled = data.is_morning_reminder_enabled;
    this.eveningEnabled = data.is_evening_reminder_enabled;
  }

  save(): void {
    this.saving = true;
    this.successMessage = '';
    this.errorMessage = '';
    this.remindersService
      .updateSettings({
        timezone: this.timezone,
        morning_reminder_time: this.morningTime,
        evening_reminder_time: this.eveningTime,
        is_morning_reminder_enabled: this.morningEnabled,
        is_evening_reminder_enabled: this.eveningEnabled
      })
      .subscribe({
        next: res => {
          this.applySettings(res.data);
          this.saving = false;
          this.successMessage = res.message || '已儲存';
        },
        error: err => {
          this.saving = false;
          this.errorMessage = err.error?.message || '儲存失敗';
        }
      });
  }

  generateTelegramLink(): void {
    this.telegramLinkLoading = true;
    this.deepLink = '';
    this.linkExpiresAt = '';
    this.errorMessage = '';
    this.remindersService.createTelegramLink().subscribe({
      next: res => {
        this.deepLink = res.data.deep_link;
        this.linkExpiresAt = res.data.expires_at || '';
        this.telegramLinkLoading = false;
      },
      error: err => {
        this.telegramLinkLoading = false;
        this.errorMessage = err.error?.message || '無法產生綁定連結';
      }
    });
  }

  unlinkTelegram(): void {
    this.telegramUnlinkLoading = true;
    this.errorMessage = '';
    this.remindersService.unlinkTelegram().subscribe({
      next: res => {
        this.applySettings(res.data);
        this.deepLink = '';
        this.telegramUnlinkLoading = false;
        this.successMessage = res.message || '已解除綁定';
      },
      error: err => {
        this.telegramUnlinkLoading = false;
        this.errorMessage = err.error?.message || '解除綁定失敗';
      }
    });
  }
}
